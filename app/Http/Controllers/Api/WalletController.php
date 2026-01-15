<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CoinPackage;
use App\Models\CoinTransaction;
use App\Models\Referral;
use App\Models\PaymentTransaction;
use App\Models\User;
use App\Models\Order;
use App\Models\Invoice;
use App\Jobs\CheckPendingPaymentStatus;
use App\Notifications\PaymentSuccessNotification;
use App\Notifications\PaymentFailedNotification;
use App\Notifications\PaymentPendingNotification;
use App\Services\CoinPricingService;
use App\Jobs\SendPaymentSuccessNotification;
use App\Jobs\SendPaymentFailedNotification;
use App\Jobs\SendPaymentPendingNotification;
use Illuminate\Http\Request;
use Razorpay\Api\Api as RazorpayApi;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;

class WalletController extends Controller
{
    /**
     * Get wallet with balance and recent transactions
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        // Return only latest 5 transactions for wallet overview
        $transactions = CoinTransaction::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $totalTransactions = CoinTransaction::where('user_id', $user->id)->count();

        $referralStats = [
            'total_referrals' => Referral::where('referrer_id', $user->id)->count(),
            'coins_earned' => Referral::where('referrer_id', $user->id)
                ->sum('referrer_coins'),
        ];

        // Calculate coin statistics and sync user balance to net balance
        $stats = $this->calculateAndSyncBalance($user);

        return response()->json([
            // Expose net balance as primary balance
            'balance' => $stats['net_balance'],
            'referral_code' => $user->referral_code,
            'referral_stats' => $referralStats,
            'stats' => $stats,
            'transactions' => [
                'data' => $transactions,
                'total' => $totalTransactions,
            ],
        ]);
    }

    /**
     * Get payment history with filters
     */
    public function paymentHistory(Request $request)
    {
        $user = $request->user();
        $typeFilter = $request->get('type', 'all');
        $statusFilter = strtolower($request->get('status', 'all'));
        $search = $request->get('search');
        $from = $request->get('from_date');
        $to = $request->get('to_date');
        $perPage = (int) ($request->per_page ?? 20);
        $page = (int) ($request->page ?? 1);

        // Build PaymentTransaction query (Razorpay-backed purchases)
        $pQuery = PaymentTransaction::where('user_id', $user->id);

        if ($typeFilter !== 'all') {
            if ($typeFilter === 'purchase') {
                $pQuery->where('type', 'coin_purchase');
            } else {
                // Non-purchase types not tracked as payments
                $pQuery->whereRaw('1=0');
            }
        }

        if ($statusFilter !== 'all') {
            if (in_array($statusFilter, ['success', 'completed'])) {
                $pQuery->where('status', 'SUCCESS');
            } elseif (in_array($statusFilter, ['failed', 'failure'])) {
                $pQuery->where('status', 'FAILED');
            } elseif ($statusFilter === 'pending') {
                $pQuery->where('status', 'PENDING');
            } elseif (in_array($statusFilter, ['initiated', 'init'])) {
                $pQuery->where('status', 'INITIATED');
            }
        }

        if ($search) {
            $pQuery->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('razorpay_order_id', 'like', "%{$search}%")
                  ->orWhere('razorpay_payment_id', 'like', "%{$search}%");
            });
        }

        if ($from) $pQuery->whereDate('created_at', '>=', $from);
        if ($to) $pQuery->whereDate('created_at', '<=', $to);

        $payments = $pQuery->orderBy('created_at', 'desc')->get();

        // Build CoinTransaction query for non-purchase entries (referrals, debits, admin)
        $cQuery = CoinTransaction::where('user_id', $user->id);

        if ($typeFilter !== 'all') {
            if ($typeFilter === 'debit') {
                $cQuery->where(function ($q) {
                    $q->where('amount', '<', 0)
                      ->orWhereIn('type', ['enquiry_post', 'enquiry_unlock', 'admin_debit', 'booking']);
                });
            } elseif ($typeFilter === 'credit') {
                $cQuery->where(function ($q) {
                    $q->where('amount', '>', 0)
                      ->orWhereIn('type', ['referral_bonus', 'referral_reward', 'admin_credit']);
                });
                // Exclude purchase credits (those tied to orders)
                $cQuery->where(function ($q) {
                    $q->whereNull('order_id')
                      ->orWhere('order_id', 0);
                });
            } elseif ($typeFilter === 'purchase') {
                // Show purchases from PaymentTransaction only
                $cQuery->whereRaw('1=0');
            } else {
                $cQuery->where('type', $typeFilter);
            }
        } else {
            // Exclude purchase credits by default to avoid duplication with payments
            $cQuery->where(function ($q) {
                $q->whereNull('order_id')
                  ->orWhere('order_id', 0);
            });
        }

        // Coin transactions have no status; they are only persisted when finalized

        if ($search) {
            $cQuery->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%");
            });
        }

        if ($from) $cQuery->whereDate('created_at', '>=', $from);
        if ($to) $cQuery->whereDate('created_at', '<=', $to);

        $coins = $cQuery->orderBy('created_at', 'desc')->get();

        // Normalize and merge
        $items = collect();

        foreach ($payments as $p) {
            $invoice = $p->order_id ? Invoice::where('order_id', $p->order_id)->first() : null;
            $items->push([
                'id' => $p->id,
                'source' => 'payment',
                'type' => 'purchase',
                'status' => $p->status,
                'description' => $p->description,
                'order_id' => $p->order_id,
                'razorpay_order_id' => $p->razorpay_order_id,
                'razorpay_payment_id' => $p->razorpay_payment_id,
                'amount_money' => $p->amount,
                'currency' => $p->currency,
                'coins' => ($p->coins ?? 0) + ($p->bonus_coins ?? 0),
                'invoice_id' => $invoice->id ?? null,
                'invoice_number' => $invoice->invoice_number ?? null,
                'invoice_download_url' => $invoice ? url('api/wallet/invoice/' . $invoice->id . '/download') : null,
                'created_at' => $p->created_at,
            ]);
        }

        foreach ($coins as $c) {
            $items->push([
                'id' => $c->id,
                'source' => 'coin',
                'type' => $c->type,
                'description' => $c->description,
                'order_id' => $c->order_id,
                'razorpay_order_id' => null,
                'razorpay_payment_id' => null,
                'amount_coins' => $c->amount,
                'direction' => ($c->amount ?? 0) >= 0 ? 'CREDIT' : 'DEBIT',
                'created_at' => $c->created_at,
            ]);
        }

        // Sort and paginate
        $sorted = $items->sortByDesc('created_at')->values();
        $total = $sorted->count();
        $paged = $sorted->slice(($page - 1) * $perPage, $perPage)->values();

        // Calculate statistics and sync user balance to net balance
        $stats = $this->calculateAndSyncBalance($user);

        return response()->json([
            'balance' => $stats['net_balance'],
            'stats' => $stats,
            'transactions' => [
                'data' => $paged,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'total' => $total,
                    'last_page' => (int) ceil(max(1, $total) / max(1, $perPage)),
                    'from' => $total ? (($page - 1) * $perPage + 1) : 0,
                    'to' => min($page * $perPage, $total),
                    'has_more_pages' => $page * $perPage < $total,
                    'next_page_url' => null,
                    'prev_page_url' => null,
                ],
            ],
        ]);
    }

    /**
     * PaymentTransaction-only listing with status/search filters
     */
    public function paymentTransactions(Request $request)
    {
        $user = $request->user();
        $statusFilter = strtolower($request->get('status', 'all'));
        $search = $request->get('search');
        $perPage = (int) ($request->per_page ?? 10);
        $page = (int) ($request->page ?? 1);
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDir = strtolower($request->get('sort_dir', 'desc')) === 'asc' ? 'asc' : 'desc';

        $query = PaymentTransaction::where('user_id', $user->id);

        // Normalize status filter
        if ($statusFilter !== 'all') {
            if (in_array($statusFilter, ['success', 'completed'])) {
                $query->where('status', 'SUCCESS');
            } elseif (in_array($statusFilter, ['failed', 'failure'])) {
                $query->where('status', 'FAILED');
            } elseif ($statusFilter === 'pending') {
                $query->where('status', 'PENDING');
            } elseif (in_array($statusFilter, ['initiated', 'init'])) {
                $query->where('status', 'INITIATED');
            }
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhere('razorpay_order_id', 'like', "%{$search}%")
                    ->orWhere('razorpay_payment_id', 'like', "%{$search}%")
                    ->orWhere('order_id', 'like', "%{$search}%");
            });
        }

        // Sorting: whitelist allowed fields
        $allowedSorts = ['created_at', 'amount', 'status'];
        if (!in_array($sortBy, $allowedSorts, true)) {
            $sortBy = 'created_at';
        }
        $query->orderBy($sortBy, $sortDir);

        $paginator = $query->paginate($perPage, ['*'], 'page', $page);

        // Map to API-friendly shape and attach invoice hints
        $data = $paginator->getCollection()->map(function ($p) {
            $invoice = $p->order_id ? Invoice::where('order_id', $p->order_id)->first() : null;
            return [
                'id' => $p->id,
                'type' => 'purchase',
                'status' => $p->status,
                'description' => $p->description,
                'order_id' => $p->order_id,
                'razorpay_order_id' => $p->razorpay_order_id,
                'razorpay_payment_id' => $p->razorpay_payment_id,
                'amount' => $p->amount,
                'currency' => $p->currency,
                'coins' => ($p->coins ?? 0) + ($p->bonus_coins ?? 0),
                'invoice_id' => $invoice->id ?? null,
                'invoice_number' => $invoice->invoice_number ?? null,
                'invoice_download_url' => $invoice ? url('api/wallet/invoice/' . $invoice->id . '/download') : null,
                'created_at' => $p->created_at,
            ];
        })->values();

        $successCount = PaymentTransaction::where('user_id', $user->id)->where('status', 'SUCCESS')->count();
        $failedCount = PaymentTransaction::where('user_id', $user->id)->where('status', 'FAILED')->count();
        $pendingCount = PaymentTransaction::where('user_id', $user->id)->where('status', 'PENDING')->count();
        $initiatedCount = PaymentTransaction::where('user_id', $user->id)->where('status', 'INITIATED')->count();

        return response()->json([
            'transactions' => [
                'data' => $data,
                'pagination' => [
                    'current_page' => $paginator->currentPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                    'last_page' => $paginator->lastPage(),
                    'from' => $paginator->firstItem(),
                    'to' => $paginator->lastItem(),
                    'has_more_pages' => $paginator->hasMorePages(),
                    'next_page_url' => $paginator->nextPageUrl(),
                    'prev_page_url' => $paginator->previousPageUrl(),
                ],
            ],
            'stats' => [
                'success_count' => $successCount,
                'failed_count' => $failedCount,
                'pending_count' => $pendingCount,
                'initiated_count' => $initiatedCount,
            ],
        ]);
    }

    /**
     * Get all available coin packages with dynamic pricing based on user location
     */
    public function coinPackages(Request $request)
    {
        $user = $request->user();
        
        $packages = CoinPricingService::getPackagesWithPricing($user);
        
        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'country' => $user->country,
                'country_iso' => $user->country_iso,
                'is_india' => CoinPricingService::isIndiaUser($user),
            ],
            'packages' => $packages,
        ]);
    }

    /**
     * Get coin transactions with filters, search, and pagination
     * For CoinTransaction model only (wallet debits/credits, referrals, etc.)
     */
    public function coinTransactions(Request $request)
    {
        $user = $request->user();
        $typeFilter = $request->get('type', 'all');
        $search = $request->get('search');
        $perPage = (int) ($request->per_page ?? 20);
        $page = (int) ($request->page ?? 1);

        $query = CoinTransaction::where('user_id', $user->id);

        // Type filter mapping
        if ($typeFilter !== 'all') {
            if ($typeFilter === 'purchase') {
                $query->where('type', 'purchase');
            } elseif ($typeFilter === 'post_requirement') {
                $query->where('type', 'enquiry_post');
            } elseif ($typeFilter === 'enquiry') {
                $query->where('type', 'enquiry_unlock');
            } elseif ($typeFilter === 'referrals') {
                $query->whereIn('type', ['referral_bonus', 'referral_reward']);
            } elseif ($typeFilter === 'debit') {
                $query->where('amount', '<', 0);
            } elseif ($typeFilter === 'credit') {
                $query->where('amount', '>', 0);
            } else {
                // Direct type match
                $query->where('type', $typeFilter);
            }
        }

        // Search by description, type, or amount
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('type', 'like', "%{$search}%")
                  ->orWhere('amount', 'like', "%{$search}%");
            });
        }

        $query->orderBy('created_at', 'desc');

        $paginator = $query->paginate($perPage, ['*'], 'page', $page);

        // Map to consistent format
        $data = $paginator->getCollection()->map(function ($tx) {
            return [
                'id' => $tx->id,
                'type' => $tx->type,
                'description' => $tx->description,
                'amount' => $tx->amount,
                'amount_coins' => $tx->amount,
                'balance_after' => $tx->balance_after,
                'source' => 'coin',
                'status' => 'completed',
                'meta' => $tx->meta ?? [],
                'created_at' => $tx->created_at,
            ];
        })->values();

        return response()->json([
            'transactions' => [
                'data' => $data,
                'pagination' => [
                    'current_page' => $paginator->currentPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                    'last_page' => $paginator->lastPage(),
                    'from' => $paginator->firstItem(),
                    'to' => $paginator->lastItem(),
                    'has_more_pages' => $paginator->hasMorePages(),
                    'next_page_url' => $paginator->nextPageUrl(),
                    'prev_page_url' => $paginator->previousPageUrl(),
                ],
            ],
            'stats' => [
                'total_transactions' => $paginator->total(),
                'total_earned' => CoinTransaction::where('user_id', $user->id)
                    ->where('amount', '>', 0)
                    ->sum('amount'),
                'total_spent' => abs(CoinTransaction::where('user_id', $user->id)
                    ->where('amount', '<', 0)
                    ->sum('amount')),
            ],
        ]);
    }

    /**
     * Calculate wallet stats and ensure user->coins matches net balance
     */
    private function calculateAndSyncBalance(User $user): array
    {
        // Only include confirmed SUCCESS entries in balance
        $totalEarned = CoinTransaction::where('user_id', $user->id)
            ->where('amount', '>', 0)
            ->sum('amount');

        $totalSpent = abs(CoinTransaction::where('user_id', $user->id)
            ->where('amount', '<', 0)
            ->sum('amount'));

        $netBalance = $totalEarned - $totalSpent;

        // Keep user->coins in sync with calculated net balance
        if ($user->coins !== $netBalance) {
            $user->coins = $netBalance;
            $user->save();
        }

        return [
            'current_balance' => $user->coins,
            'total_earned' => $totalEarned,
            'total_spent' => $totalSpent,
            'net_balance' => $netBalance,
            'total_purchases' => PaymentTransaction::where('user_id', $user->id)
                ->where('type', 'coin_purchase')
                ->where('status', 'SUCCESS')
                ->count(),
            'failed_payments' => PaymentTransaction::where('user_id', $user->id)
                ->where('type', 'coin_purchase')
                ->where('status', 'FAILED')
                ->count(),
            'enquiries_posted' => CoinTransaction::where('user_id', $user->id)
                ->where('type', 'enquiry_post')
                ->count(),
            'contacts_unlocked' => CoinTransaction::where('user_id', $user->id)
                ->where('type', 'enquiry_unlock')
                ->count(),
        ];
    }

    /**
     * Get all available coin packages
     */
    public function packages(Request $request)
    {
        $user = $request->user();
        
        // Return packages with location-based pricing if user is authenticated
        if ($user) {
            $packages = CoinPricingService::getPackagesWithPricing($user);
            
            return response()->json([
                'success' => true,
                'user' => [
                    'id' => $user->id,
                    'country' => $user->country,
                    'country_iso' => $user->country_iso,
                    'is_india' => CoinPricingService::isIndiaUser($user),
                ],
                'packages' => $packages,
            ]);
        }
        
        // For unauthenticated requests, return packages without pricing
        $packages = CoinPackage::active()
            ->orderBy('sort_order')
            ->orderBy('price')
            ->get();

        return response()->json([
            'success' => true,
            'packages' => $packages,
            'message' => 'Login to see location-based pricing',
        ]);
    }

    /**
     * Create Razorpay order for coin purchase
     * 
     * Flow:
     * 1. Create Order record (pending)
     * 2. Create Razorpay order
     * 3. Create Transaction record (pending)
     * 4. Return order details to frontend
     */
    public function purchaseCoins(Request $request)
    {
        $request->validate([
            'package_id' => 'required|exists:coin_packages,id',
        ]);

        $package = CoinPackage::active()->findOrFail($request->package_id);
        $user = $request->user();

        // Get dynamic pricing based on user location
        $pricingData = CoinPricingService::calculatePackagePrice($package, $user);
        
        $isIndia = $pricingData['is_india'];
        $currency = $pricingData['currency'];
        $subtotal = $pricingData['subtotal'];
        $taxAmount = $pricingData['tax_amount'];
        $totalAmount = $pricingData['total'];
        $gstRate = $pricingData['gst_rate'];

        \Log::info('Coin purchase calculation', [
            'user_id' => $user->id,
            'user_country' => $user->country,
            'user_country_iso' => $user->country_iso,
            'package_id' => $package->id,
            'package_price' => $package->price,
            'is_india' => $isIndia,
            'currency' => $currency,
            'subtotal' => $subtotal,
            'gst_rate' => $gstRate,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
        ]);

        DB::beginTransaction();

        try {
            // IDEMPOTENT: Mark any previous INITIATED/PENDING orders as FAILED
            $failedOrdersCount = Order::where('user_id', $user->id)
                ->whereIn('status', ['INITIATED',  'initiated'])
                ->update([
                    'status' => 'failed',
                    'updated_at' => now(),
                ]);

            if ($failedOrdersCount > 0) {
                PaymentTransaction::where('user_id', $user->id)
                    ->whereIn('status', ['INITIATED', 'initiated'])
                    ->update([
                        'status' => 'FAILED',
                        'updated_at' => now(),
                    ]);

                \Log::info('Previous initiated orders marked as failed', [
                    'user_id' => $user->id,
                    'failed_orders_count' => $failedOrdersCount,
                ]);
            }

            // STEP 1: Create internal order FIRST
            $receipt = 'coin_' . $user->id . '_' . time();

            $order = Order::create([
                'user_id' => $user->id,
                'amount' => $totalAmount,
                'currency' => $currency,
                'package_id' => $package->id,
                'coins' => $package->coins,
                'bonus_coins' => $package->bonus_coins,
                'status' => 'INITIATED',
                'receipt' => $receipt,
                'meta' => [
                    'package_name' => $package->name,
                    'user_country' => $user->country,
                    'user_country_iso' => $user->country_iso,
                    'pricing' => [
                        'is_india' => $isIndia,
                        'gst_rate' => $gstRate,
                        'subtotal' => $subtotal,
                        'tax_amount' => $taxAmount,
                        'total_amount' => $totalAmount,
                        'currency' => $currency,
                    ],
                ],
            ]);

            // STEP 2: Create Razorpay order
            $rzp = new RazorpayApi(
                config('services.razorpay.key'),
                config('services.razorpay.secret')
            );

            // For Razorpay: amount must be in smallest unit (paise for INR, cents for USD)
            // INR: multiply by 100 (1 rupee = 100 paise)
            // USD: multiply by 100 (1 dollar = 100 cents)
            $razorpayAmount = (int) round($totalAmount * 100);
            
            $razorpayOrder = $rzp->order->create([
                'amount' => $razorpayAmount,
                'currency' => $currency, // Use actual currency (INR or USD)
                'receipt' => $receipt,
                'payment_capture' => 1,
                'notes' => [
                    'db_order_id' => $order->id,
                    'user_id' => $user->id,
                    'package_id' => $package->id,
                    'type' => 'coin_purchase',
                    'user_country' => $user->country,
                    'user_country_iso' => $user->country_iso,
                    'is_india' => $isIndia ? 'true' : 'false',
                    'display_currency' => $currency,
                    'display_amount' => $totalAmount,
                ],
            ]);

            // Update order with Razorpay ID
            $order->update([
                'razorpay_order_id' => $razorpayOrder['id'],
            ]);

            // STEP 3: Create payment transaction (INITIATED)
            $transaction = PaymentTransaction::create([
                'user_id' => $user->id,
                'order_id' => $order->id,
                'razorpay_order_id' => $razorpayOrder['id'],
                'type' => 'coin_purchase',
                'status' => 'INITIATED',
                'amount' => $totalAmount,
                'currency' => $currency,
                'coins' => $package->coins,
                'bonus_coins' => $package->bonus_coins,
                'description' => "Coin purchase: {$package->name}",
                'meta' => [
                    'package_id' => $package->id,
                    'package_name' => $package->name,
                    'user_country' => $user->country,
                    'is_india' => $isIndia,
                    'pricing' => [
                        'is_india' => $isIndia,
                        'currency' => $currency,
                        'subtotal' => $subtotal,
                        'tax_amount' => $taxAmount,
                        'total_amount' => $totalAmount,
                    ],
                ],
            ]);

            DB::commit();
            
            // Schedule a status check job as a fallback in case payment remains pending
            CheckPendingPaymentStatus::dispatch($order->id, $transaction->id)
                ->delay(now()->addMinutes(15));

            return response()->json([
                'success' => true,
                'order' => [
                    'id' => $razorpayOrder['id'],
                    'db_order_id' => $order->id,
                    'razorpay_order_id' => $razorpayOrder['id'],
                    'amount' => $razorpayAmount,
                    'currency' => $currency,
                ],
                'transaction_id' => $transaction->id,
                'package' => [
                    'id' => $package->id,
                    'name' => $package->name,
                    'coins' => $package->coins,
                    'bonus_coins' => $package->bonus_coins,
                    'price' => $package->price,
                ],
                'user' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'country' => $user->country,
                    'country_iso' => $user->country_iso,
                ],
                'pricing' => [
                    'is_india' => $isIndia,
                    'currency' => $currency,
                    'currency_symbol' => $pricingData['currency_symbol'],
                    'gst_rate' => $gstRate,
                    'gst_percentage' => $pricingData['gst_percentage'],
                    'subtotal' => $subtotal,
                    'tax_amount' => $taxAmount,
                    'total' => $totalAmount,
                    'display_price' => $pricingData['display_price'],
                    'description' => $pricingData['description'],
                ],
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            \Log::error('Coin purchase failed', [
                'user_id' => $user->id,
                'package_id' => $package->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to create order',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred',
            ], 500);
        }
    }

    /**
     * Determine if user should be taxed as India resident.
     * Checks: user.country_code, tutor.country, student.country_code.
     */
    private function isIndianUser(User $user): bool
    {
        $cc = strtoupper((string) ($user->country_code ?? ''));
        if ($cc === 'IN' || $cc === 'IND' || $cc === '91') {
            return true;
        }

        $tutor = $user->tutor;
        if ($tutor && $tutor->country) {
            if (strtoupper(trim($tutor->country)) === 'INDIA') {
                return true;
            }
        }

        $student = $user->student;
        if ($student) {
            $scc = strtoupper((string) ($student->country_code ?? ''));
            if ($scc === 'IN' || $scc === 'IND' || $scc === '91') {
                return true;
            }
        }
        // If no country information is available anywhere, default to India
        $hasAnyCountry = false;
        if (!empty($cc)) { $hasAnyCountry = true; }
        if ($tutor && !empty($tutor->country)) { $hasAnyCountry = true; }
        if ($student && !empty($student->country_code)) { $hasAnyCountry = true; }

        if (!$hasAnyCountry) {
            return true;
        }

        return false;
    }

    /**
     * Verify Razorpay payment and credit coins
     * 
     * Flow:
     * 1. Verify payment signature
     * 2. Update Order record (completed)
     * 3. Update Transaction record (completed + amount)
     * 4. Add coins to user wallet
     * 5. Return success with new balance
     */
    public function verifyPayment(Request $request)
    {
        $data = $request->validate([
            'transaction_id' => 'required|exists:payment_transactions,id',
            'razorpay_payment_id' => 'required|string',
            'razorpay_order_id' => 'required|string',
            'razorpay_signature' => 'required|string',
        ]);
        // The transaction here is a PaymentTransaction
        $paymentTx = PaymentTransaction::findOrFail($data['transaction_id']);
        $user = $request->user();

        // Verify transaction belongs to user
        if ($paymentTx->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        // Check if already processed (SUCCESS)
        if ($paymentTx->status === 'SUCCESS') {
            return response()->json([
                'success' => true,
                'message' => 'Payment already processed',
                'balance' => $user->coins,
                'coins_added' => 0,
            ]);
        }

        // If transaction is FAILED, don't allow verification - must retry with new order
        if ($paymentTx->status === 'FAILED') {
            return response()->json([
                'success' => false,
                'message' => 'This payment has failed. Please retry with a new transaction.',
            ], 422);
        }

        // Only INITIATED or PENDING transactions can be verified
        if (!in_array($paymentTx->status, ['INITIATED', 'PENDING'])) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid transaction status for verification',
            ], 422);
        }

        // Step 1: Verify Razorpay signature
        $payload = $data['razorpay_order_id'] . '|' . $data['razorpay_payment_id'];
        $expectedSig = hash_hmac('sha256', $payload, config('services.razorpay.secret'));

        if (!hash_equals($expectedSig, $data['razorpay_signature'])) {
            // Update payment transaction as FAILED
            $meta = $paymentTx->meta ?? [];
            $paymentTx->update([
                'meta' => array_merge($meta, [
                    'status' => 'failed',
                    'failure_reason' => 'Invalid signature',
                    'failed_at' => now()->toIso8601String(),
                ]),
                'status' => 'FAILED',
            ]);

            // Update order as failed
            $failedOrder = Order::where('razorpay_order_id', $data['razorpay_order_id'])->first();
            if ($failedOrder) {
                $failedOrder->update([
                    'status' => 'failed',
                    'meta' => array_merge($failedOrder->meta ?? [], [
                        'failure_reason' => 'Invalid payment signature',
                        'failed_at' => now()->toIso8601String(),
                    ]),
                ]);
            }

            // Send failure notification via job
            SendPaymentFailedNotification::dispatch($user, $failedOrder ?? new Order(), $paymentTx, 'Invalid signature');

            \Log::warning('Payment signature verification failed', [
                'user_id' => $user->id,
                'order_id' => $data['razorpay_order_id'],
                'payment_id' => $data['razorpay_payment_id'],
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Invalid payment signature. Payment rejected.',
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Get order from database
            $order = Order::where('razorpay_order_id', $data['razorpay_order_id'])
                ->where('user_id', $user->id)
                ->firstOrFail();

            // Step 2: Update Order record
            $order->update([
                'razorpay_payment_id' => $data['razorpay_payment_id'],
                'razorpay_signature' => $data['razorpay_signature'],
                'status' => 'completed',
                'paid_at' => now(),
            ]);

            // Get coins from payment meta
            $meta = $paymentTx->meta ?? [];
            $coins = $paymentTx->coins ?? ($meta['coins'] ?? ($order->coins ?? 0));
            $bonusCoins = $paymentTx->bonus_coins ?? ($meta['bonus_coins'] ?? ($order->bonus_coins ?? 0));

            // If we only have a total coin count, treat it as base coins with zero bonus
            $totalCoins = $coins + $bonusCoins;

            // Step 3: Mark payment transaction completed
            $paymentTx->update([
                'razorpay_payment_id' => $data['razorpay_payment_id'],
                'status' => 'SUCCESS',
                'meta' => array_merge($meta, [
                    'status' => 'completed',
                    'razorpay_payment_id' => $data['razorpay_payment_id'],
                    'razorpay_signature' => $data['razorpay_signature'],
                ]),
            ]);

            // Idempotency guard: if a success coin transaction already exists, skip crediting again
            // Idempotent coin credit and transaction create
            $lockedUser = User::where('id', $user->id)->lockForUpdate()->first();
            
            // Get currency from payment transaction or order
            $transactionCurrency = $paymentTx->currency ?? $order->currency ?? 'INR';
            $transactionAmount = $paymentTx->amount ?? $order->amount ?? 0;
            
            $coinTx = CoinTransaction::firstOrCreate(
                [
                    'user_id' => $lockedUser->id,
                    'order_id' => (string) $order->id,
                    'type' => 'purchase',
                ],
                [
                    'amount' => $totalCoins,
                    'balance_after' => 0,
                    'payment_id' => $data['razorpay_payment_id'],
                    'description' => ($meta['package_name'] ?? 'Coin purchase') . ": {$coins} coins" . ($bonusCoins > 0 ? " + {$bonusCoins} bonus" : ''),
                    'meta' => array_merge($meta, [
                        'status' => 'completed',
                        'currency' => $transactionCurrency,
                        'paid_amount' => $transactionAmount,
                    ]),
                ]
            );

            if ($coinTx->wasRecentlyCreated) {
                $lockedUser->increment('coins', $totalCoins);
                $coinTx->balance_after = $lockedUser->coins;
                $coinTx->save();
            }

                // Step 6: Create invoice and generate PDF
                $invoice = $this->createInvoice($order, $coinTx, $user);

                // Step 7: Send success notification via job (async)
                SendPaymentSuccessNotification::dispatch($user, $order, $coinTx, $invoice);

            DB::commit();

            \Log::info('Payment completed successfully', [
                'user_id' => $user->id,
                'transaction_id' => $paymentTx->id,
                'order_id' => $order->id,
                'razorpay_order_id' => $data['razorpay_order_id'],
                'payment_id' => $data['razorpay_payment_id'],
                'coins_added' => $totalCoins,
                    'invoice_id' => $invoice->id,
            ]);

                // Step 7: Return response
            // Refresh user to get latest coin balance
            $user->refresh();
            
            return response()->json([
                'success' => true,
                'message' => 'Payment successful! Coins credited to your wallet.',
                'coins_added' => $totalCoins,
                'coins_breakdown' => [
                    'base_coins' => $coins,
                    'bonus_coins' => $bonusCoins,
                ],
                'balance' => $user->coins,
                'currency' => $transactionCurrency,
                'transaction' => [
                    'id' => $coinTx->id,
                    'status' => 'completed',
                    'amount' => $coinTx->amount,
                    'updated_at' => $coinTx->updated_at,
                ],
                'order' => [
                    'id' => $order->id,
                    'razorpay_order_id' => $order->razorpay_order_id,
                    'razorpay_payment_id' => $order->razorpay_payment_id,
                    'amount' => $order->amount,
                    'currency' => $order->currency,
                    'status' => $order->status,
                    'paid_at' => $order->paid_at,
                ],
                    'invoice' => [
                        'id' => $invoice->id,
                        'invoice_number' => $invoice->invoice_number,
                        'pdf_url' => $invoice->pdf_url,
                    ],
            ], 200);

        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('Payment verification failed', [
                'user_id' => $user->id,
                'order_id' => $data['razorpay_order_id'],
                'payment_id' => $data['razorpay_payment_id'],
                'error' => $e->getMessage(),
            ]);

            // Mark payment transaction as FAILED
            $meta = $paymentTx->meta ?? [];
            $paymentTx->update([
                'meta' => array_merge($meta, [
                    'status' => 'failed',
                    'failure_reason' => 'Server error: ' . $e->getMessage(),
                ]),
                'status' => 'FAILED',
            ]);

            // Mark order as failed
            $order = Order::where('razorpay_order_id', $data['razorpay_order_id'])->first();
            if ($order) {
                $order->update([
                    'status' => 'failed',
                    'meta' => array_merge($order->meta ?? [], [
                        'failure_reason' => 'Payment verification error',
                        'failed_at' => now()->toIso8601String(),
                    ]),
                ]);
                // Send failure notification via job
                SendPaymentFailedNotification::dispatch($user, $order, $paymentTx, 'Server error');
            }

            return response()->json([
                'success' => false,
                'message' => 'Payment verification failed. Please contact support.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Download a simple HTML receipt for a wallet order
     */
    public function downloadReceipt(Request $request, $orderId)
    {
        $user = $request->user();

        $order = Order::where(function ($q) use ($orderId) {
                $q->where('id', $orderId)
                    ->orWhere('razorpay_order_id', $orderId);
            })
            ->where('user_id', $user->id)
            ->first();

        if (!$order) {
            return response()->json([
                'message' => 'Order not found',
            ], 404);
        }

        $transaction = CoinTransaction::where('order_id', $order->id)
            ->where('user_id', $user->id)
            ->latest()
            ->first();

        $coins = $transaction->meta['coins'] ?? $order->coins ?? $transaction->coins ?? 0;
        $bonus = $transaction->meta['bonus_coins'] ?? $order->bonus_coins ?? 0;
        $totalCoins = $coins + $bonus;

        $html = view('receipts.wallet', [
            'order' => $order,
            'transaction' => $transaction,
            'user' => $user,
            'coins' => $coins,
            'bonus' => $bonus,
            'totalCoins' => $totalCoins,
        ])->render();

        // Use DOMPDF for PDF generation
        try {
            $pdf = \PDF::loadHTML($html)
                ->setPaper('a4')
                ->setOption('margin-top', 0)
                ->setOption('margin-bottom', 0)
                ->setOption('margin-left', 0)
                ->setOption('margin-right', 0);

            return $pdf->download('receipt-' . $order->id . '.pdf');
        } catch (\Exception $e) {
            // Fallback to HTML if PDF generation fails
            \Log::warning('PDF generation failed for order ' . $order->id . ': ' . $e->getMessage());
            return response($html, 200, [
                'Content-Type' => 'text/html; charset=UTF-8',
                'Content-Disposition' => 'inline; filename="wallet-receipt-' . $order->id . '.html"'
            ]);
        }
    }

    /**
     * Get referral information
     */
    public function getReferralInfo(Request $request)
    {
        $user = $request->user();

        // Generate referral code if not exists
        if (!$user->referral_code) {
            $user->update([
                'referral_code' => $this->generateReferralCode()
            ]);
        }

        $referrals = Referral::where('referrer_id', $user->id)
            ->with('referred:id,name,email,created_at')
            ->orderBy('created_at', 'desc')
            ->get();

        $stats = [
            'total_referrals' => $referrals->count(),
            'total_coins_earned' => $referrals->sum('referrer_coins'),
            'referral_code' => $user->referral_code,
            'referral_link' => config('app.frontend_url') . '/register?ref=' . $user->referral_code,
        ];

        return response()->json([
            'stats' => $stats,
            'referrals' => $referrals,
        ]);
    }

    /**
     * Apply referral code during registration/first time
     */
    public function applyReferralCode(Request $request)
    {
        $request->validate([
            'referral_code' => 'required|string|exists:users,referral_code',
        ]);

        $user = $request->user();

        // Check if user already used a referral
        if ($user->referred_by) {
            return response()->json([
                'message' => 'You have already used a referral code.'
            ], 422);
        }

        $referrer = User::where('referral_code', $request->referral_code)->first();

        if (!$referrer) {
            return response()->json(['message' => 'Invalid referral code'], 422);
        }

        if ($referrer->id === $user->id) {
            return response()->json(['message' => 'You cannot use your own referral code'], 422);
        }

        DB::beginTransaction();
        try {
            $referrerCoins = 50; // Coins for referrer
            $referredCoins = 25; // Coins for new user

            // Credit coins to referrer
            $referrer->increment('coins', $referrerCoins);
            
            // Credit coins to referred user
            $user->increment('coins', $referredCoins);
            $user->update(['referred_by' => $referrer->id]);

            // Create referral record
            $referral = Referral::create([
                'referrer_id' => $referrer->id,
                'referred_id' => $user->id,
                'referrer_coins' => $referrerCoins,
                'referred_coins' => $referredCoins,
                'reward_given' => true,
                'reward_given_at' => now(),
            ]);

            // Create transaction records
            CoinTransaction::create([
                'user_id' => $referrer->id,
                'type' => 'referral_reward',
                'amount' => $referrerCoins,
                'balance_after' => $referrer->coins,
                'description' => "Referral reward for {$user->name}",
                'meta' => ['referred_user_id' => $user->id],
            ]);

            CoinTransaction::create([
                'user_id' => $user->id,
                'type' => 'referral_bonus',
                'amount' => $referredCoins,
                'balance_after' => $user->coins,
                'description' => "Welcome bonus for using {$referrer->name}'s referral code",
                'meta' => ['referrer_user_id' => $referrer->id],
            ]);

            DB::commit();

            return response()->json([
                'message' => "Referral applied! You earned {$referredCoins} coins.",
                'coins_earned' => $referredCoins,
                'balance' => $user->coins,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('Referral application failed: ' . $e->getMessage());
            return response()->json(['error' => 'Could not apply referral code'], 500);
        }
    }

    /**
     * Handle Razorpay payment callback
     */
    public function paymentCallback(Request $request)
    {
        \Log::info('Payment callback received', $request->all());

        $paymentId = $request->razorpay_payment_id;
        $orderId = $request->razorpay_order_id;

        if (!$paymentId || !$orderId) {
            return redirect()->back()->with('error', 'Invalid payment response');
        }

        // Find payment transaction by Razorpay order id
        $transaction = PaymentTransaction::where('razorpay_order_id', $orderId)->first();

        if (!$transaction) {
            return redirect()->back()->with('error', 'Transaction not found');
        }

        $user = User::find($transaction->user_id);
        $redirectUrl = $user->hasRole('tutor') 
            ? url('/tutor/wallet?payment=processing')
            : url('/student/wallet?payment=processing');

        return redirect($redirectUrl);
    }

    /**
     * Handle Razorpay webhook for payment status updates
     */
    public function webhook(Request $request)
    {
        \Log::info('Razorpay webhook received', $request->all());

        // Verify webhook signature
        $webhookSecret = config('services.razorpay.webhook_secret');
        $webhookSignature = $request->header('X-Razorpay-Signature');
        $webhookBody = $request->getContent();

        if ($webhookSecret) {
            $expectedSignature = hash_hmac('sha256', $webhookBody, $webhookSecret);
            if (!hash_equals($expectedSignature, $webhookSignature)) {
                \Log::warning('Invalid webhook signature');
                return response()->json(['error' => 'Invalid signature'], 400);
            }
        }

        $event = $request->event;
        $payload = $request->payload;

        try {
            switch ($event) {
                case 'payment.captured':
                    $this->handlePaymentCaptured($payload['payment']['entity']);
                    break;

                case 'payment.failed':
                    $this->handlePaymentFailed($payload['payment']['entity']);
                    break;

                case 'order.paid':
                    $this->handleOrderPaid($payload['order']['entity']);
                    break;

                default:
                    \Log::info('Unhandled webhook event: ' . $event);
            }

            return response()->json(['status' => 'success']);
        } catch (\Throwable $e) {
            \Log::error('Webhook processing failed: ' . $e->getMessage());
            return response()->json(['error' => 'Processing failed'], 500);
        }
    }

    /**
     * Handle payment captured event
     */
    private function handlePaymentCaptured($payment)
    {
        $orderId = $payment['order_id'];
        $paymentId = $payment['id'];

        // Match by Razorpay order id on payment transactions
        $paymentTx = PaymentTransaction::where('razorpay_order_id', $orderId)->first();

        if (!$paymentTx) {
            \Log::warning('Transaction not found for order: ' . $orderId);
            return;
        }

        // Skip if already processed
        if ($paymentTx->razorpay_payment_id || $paymentTx->status === 'SUCCESS') {
            \Log::info('Payment already processed: ' . $paymentId);
            return;
        }

        DB::beginTransaction();
        try {
            $user = User::find($paymentTx->user_id);
            $meta = $paymentTx->meta ?? [];
            $coins = $paymentTx->coins ?? ($meta['coins'] ?? 0);
            $bonus = $paymentTx->bonus_coins ?? ($meta['bonus_coins'] ?? 0);
            $totalCoins = $coins + $bonus;

            // Idempotent coin credit + transaction create under lock
            $lockedUser = User::where('id', $user->id)->lockForUpdate()->first();

            // Mark payment transaction SUCCESS
            $paymentTx->update([
                'razorpay_payment_id' => $paymentId,
                'status' => 'SUCCESS',
                'meta' => array_merge($meta, [
                    'status' => 'completed',
                    'captured_at' => now(),
                    'payment_method' => $payment['method'] ?? null,
                    'payment_email' => $payment['email'] ?? null,
                ]),
            ]);

                // Update order status
                $order = Order::where('razorpay_order_id', $orderId)->first();
                if ($order) {
                    $order->update([
                        'status' => 'completed',
                        'razorpay_payment_id' => $paymentId,
                        'paid_at' => now(),
                    ]);

                    // Create coin transaction and invoice (idempotent)
                    $coinTx = CoinTransaction::firstOrCreate(
                        [
                            'user_id' => $lockedUser->id,
                            'order_id' => (string) $order->id,
                            'type' => 'purchase',
                        ],
                        [
                            'amount' => $totalCoins,
                            'balance_after' => 0,
                            'payment_id' => $paymentId,
                            'description' => ($meta['package_name'] ?? 'Coin purchase') . " - {$coins} coins" . ($bonus > 0 ? " + {$bonus} bonus" : ''),
                            'meta' => array_merge($meta, ['status' => 'completed']),
                        ]
                    );
                    if ($coinTx->wasRecentlyCreated) {
                        $lockedUser->increment('coins', $totalCoins);
                        $coinTx->balance_after = $lockedUser->coins;
                        $coinTx->save();
                    }

                    $invoice = $this->createInvoice($order, $coinTx, $user);

                    // Send notification via job
                    SendPaymentSuccessNotification::dispatch($user, $order, $coinTx, $invoice);
                }

            DB::commit();
            \Log::info('Payment captured successfully', ['payment_tx_id' => $paymentTx->id]);
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('Failed to process captured payment: ' . $e->getMessage());
        }
    }

    /**
     * Handle payment failed event
     */
    private function handlePaymentFailed($payment)
    {
        $orderId = $payment['order_id'] ?? null;
        $paymentId = $payment['id'];

        if (!$orderId) {
            \Log::warning('No order ID in failed payment: ' . $paymentId);
            return;
        }

        $paymentTx = PaymentTransaction::where('razorpay_order_id', $orderId)->first();

        if (!$paymentTx) {
            \Log::warning('Transaction not found for failed payment: ' . $orderId);
            return;
        }

        $meta = $paymentTx->meta ?? [];
        $paymentTx->update([
            'razorpay_payment_id' => $paymentId,
            'meta' => array_merge($meta, [
                'status' => 'failed',
                'failed_at' => now(),
                'error_code' => $payment['error_code'] ?? null,
                'error_description' => $payment['error_description'] ?? null,
                'failure_reason' => $payment['error_reason'] ?? 'Unknown',
            ]),
            'status' => 'FAILED',
        ]);

            // Update order and send notification
            $order = Order::where('razorpay_order_id', $orderId)->first();
            if ($order) {
                $order->update(['status' => 'failed']);
            
                $user = User::find($paymentTx->user_id);
                if ($user) {
                    $reason = $payment['error_description'] ?? $payment['error_reason'] ?? 'Payment failed';
                    SendPaymentFailedNotification::dispatch($user, $order, $paymentTx, $reason);
                }
            }

        \Log::info('Payment failed', [
            'transaction_id' => $paymentTx->id,
            'reason' => $payment['error_description'] ?? 'Unknown',
        ]);
    }

    /**
     * Handle order paid event
     */
    private function handleOrderPaid($order)
    {
        $orderId = $order['id'];
        
        $paymentTx = PaymentTransaction::where('razorpay_order_id', $orderId)->first();

        if (!$paymentTx) {
            \Log::warning('Transaction not found for paid order: ' . $orderId);
            return;
        }

        $meta = $paymentTx->meta ?? [];
        $paymentTx->update([
            'meta' => array_merge($meta, [
                'order_status' => 'paid',
                'paid_at' => now(),
            ]),
        ]);

        \Log::info('Order marked as paid', ['transaction_id' => $paymentTx->id]);
    }

    /**
     * Get payment order status
     */
    public function getOrderStatus(Request $request, $orderId)
    {
        $user = $request->user();
        
        $transaction = PaymentTransaction::where(function ($q) use ($orderId) {
                    $q->where('order_id', $orderId)
                      ->orWhere('razorpay_order_id', $orderId);
                })
                ->where('user_id', $user->id)
                ->first();

        if (!$transaction) {
            return response()->json(['error' => 'Order not found'], 404);
        }

        $meta = $transaction->meta ?? [];
        
        return response()->json([
            'order_id' => $transaction->order_id,
            'payment_id' => $transaction->razorpay_payment_id,
            'status' => $transaction->status ?? ($meta['status'] ?? 'pending'),
            'amount' => $transaction->amount,
            'description' => $transaction->description,
            'created_at' => $transaction->created_at,
            'updated_at' => $transaction->updated_at,
            'meta' => $meta,
        ]);
    }

    /**
     * Cancel pending payment
     */
    public function cancelPayment(Request $request, $orderId)
    {
        $user = $request->user();
        
        $transaction = PaymentTransaction::where('order_id', $orderId)
            ->where('user_id', $user->id)
            ->first();

        if (!$transaction) {
            return response()->json(['error' => 'Order not found'], 404);
        }

        $meta = $transaction->meta ?? [];

        if (isset($meta['status']) && !in_array($meta['status'], ['pending', 'initiated', 'INITIATED'])) {
            return response()->json(['error' => 'Cannot cancel this payment'], 400);
        }

        $reason = $request->input('reason', 'Cancelled by user');

        $transaction->update([
            'status' => 'FAILED',
            'meta' => array_merge($meta, [
                'status' => 'failed',
                'failure_reason' => $reason,
                'cancelled_at' => now(),
            ]),
        ]);

        // Mark order as failed too
        Order::where('id', $orderId)
            ->orWhere('razorpay_order_id', $orderId)
            ->update(['status' => 'failed']);

        return response()->json([
            'message' => 'Payment cancelled successfully',
            'order_id' => $orderId,
        ]);
    }

    /**
     * Mark payment as failed when Razorpay gateway returns failure
     */
    public function markPaymentFailed(Request $request)
    {
        $user = $request->user();
        
        $orderId = $request->input('order_id');
        $transactionId = $request->input('transaction_id');
        $razorpayError = $request->input('razorpay_error', 'GATEWAY_ERROR');
        $errorDescription = $request->input('error_description', 'Payment gateway error');
        $errorReason = $request->input('error_reason', 'unknown');

        try {
            DB::beginTransaction();

            // Find and update payment transaction
            $transaction = PaymentTransaction::where('user_id', $user->id);
            
            if ($transactionId) {
                $transaction = $transaction->where('id', $transactionId)->first();
            } else {
                $transaction = $transaction->where('order_id', $orderId)->first();
            }

            if (!$transaction) {
                return response()->json(['error' => 'Transaction not found'], 404);
            }

            // Check if already processed
            if (in_array($transaction->status, ['SUCCESS', 'FAILED', 'COMPLETED'])) {
                return response()->json([
                    'message' => 'Payment already processed',
                    'status' => $transaction->status
                ], 422);
            }

            // Update transaction as failed
            $meta = $transaction->meta ?? [];
            $transaction->update([
                'status' => 'FAILED',
                'meta' => array_merge($meta, [
                    'status' => 'failed',
                    'failure_reason' => $errorDescription,
                    'razorpay_error' => $razorpayError,
                    'error_reason' => $errorReason,
                    'failed_at' => now()->toDateTimeString(),
                    'failed_via_gateway' => true,
                ]),
            ]);

            // Update associated order as failed
            $order = Order::where('id', $orderId)
                ->orWhere('razorpay_order_id', $orderId)
                ->first();

            if ($order) {
                $orderMeta = $order->meta ?? [];
                $order->update([
                    'status' => 'failed',
                    'meta' => array_merge($orderMeta, [
                        'failure_reason' => $errorDescription,
                        'razorpay_error' => $razorpayError,
                        'failed_at' => now()->toDateTimeString(),
                    ]),
                ]);
            }

            DB::commit();

            \Log::info('Payment marked as failed via gateway', [
                'order_id' => $orderId,
                'transaction_id' => $transactionId,
                'user_id' => $user->id,
                'error_code' => $razorpayError,
                'error_description' => $errorDescription,
            ]);

            return response()->json([
                'message' => 'Payment marked as failed',
                'order_id' => $orderId,
                'transaction_id' => $transaction->id,
                'status' => 'failed'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Failed to mark payment as failed', [
                'order_id' => $orderId,
                'transaction_id' => $transactionId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'Failed to process payment failure',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle payment cancellation when user dismisses payment modal
     */
    public function cancelledPayment(Request $request)
    {
        $user = $request->user();
        
        $orderId = $request->input('order_id');
        $transactionId = $request->input('transaction_id');
        $reason = $request->input('reason', 'user_dismissed');

        try {
            DB::beginTransaction();

            // Find and update payment transaction
            $transaction = PaymentTransaction::where('user_id', $user->id);
            
            if ($transactionId) {
                $transaction = $transaction->where('id', $transactionId)->first();
            } else {
                $transaction = $transaction->where('order_id', $orderId)->first();
            }

            if (!$transaction) {
                return response()->json(['error' => 'Transaction not found'], 404);
            }

            // Check if already processed
            if (in_array($transaction->status, ['SUCCESS', 'FAILED', 'COMPLETED', 'CANCELLED'])) {
                return response()->json([
                    'message' => 'Payment already processed',
                    'status' => $transaction->status
                ], 422);
            }

            // Update transaction as cancelled
            $meta = $transaction->meta ?? [];
            $transaction->update([
                'status' => 'CANCELLED',
                'meta' => array_merge($meta, [
                    'status' => 'cancelled',
                    'cancellation_reason' => $reason,
                    'cancelled_at' => now()->toDateTimeString(),
                    'cancelled_by_user' => true,
                ]),
            ]);

            // Update associated order as cancelled
            $order = Order::where('id', $orderId)
                ->orWhere('razorpay_order_id', $orderId)
                ->first();

            if ($order) {
                $orderMeta = $order->meta ?? [];
                $order->update([
                    'status' => 'cancelled',
                    'meta' => array_merge($orderMeta, [
                        'cancellation_reason' => $reason,
                        'cancelled_at' => now()->toDateTimeString(),
                        'cancelled_by_user' => true,
                    ]),
                ]);
            }

            DB::commit();

            \Log::info('Payment cancelled by user', [
                'order_id' => $orderId,
                'transaction_id' => $transactionId,
                'user_id' => $user->id,
                'reason' => $reason,
            ]);

            return response()->json([
                'message' => 'Payment cancelled successfully',
                'order_id' => $orderId,
                'transaction_id' => $transaction->id,
                'status' => 'cancelled'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Failed to cancel payment', [
                'order_id' => $orderId,
                'transaction_id' => $transactionId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'Failed to process payment cancellation',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate unique referral code
     */
    private function generateReferralCode(): string
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (User::where('referral_code', $code)->exists());

        return $code;
    }

    /**
     * Create invoice and generate PDF
     */
    private function createInvoice($order, $transaction, $user)
    {
        // Check if invoice already exists
        $existingInvoice = Invoice::where('order_id', $order->id)->first();
        if ($existingInvoice) {
            return $existingInvoice;
        }

        // Create invoice record
        $invoice = Invoice::create([
            'user_id' => $user->id,
            'order_id' => $order->id,
            'invoice_number' => Invoice::generateInvoiceNumber(),
            'amount' => $order->amount,
            'currency' => $order->currency ?? 'INR',
            'coins' => $order->coins ?? 0,
            'bonus_coins' => $order->bonus_coins ?? 0,
            'razorpay_order_id' => $order->razorpay_order_id,
            'razorpay_payment_id' => $order->razorpay_payment_id,
            'status' => 'paid',
            'issued_at' => now(),
            'meta' => [
                'package_name' => $transaction->meta['package_name'] ?? 'Coin Purchase',
                'transaction_id' => $transaction->id,
            ],
        ]);

        // Generate PDF
        try {
            $pdf = Pdf::loadView('invoices.coin-purchase', [
                'invoice' => $invoice,
                'order' => $order,
                'user' => $user,
                'transaction' => $transaction,
            ]);

            $filename = 'invoices/' . $invoice->invoice_number . '.pdf';
            $path = storage_path('app/public/' . $filename);
            
            // Ensure directory exists
            if (!file_exists(dirname($path))) {
                mkdir(dirname($path), 0755, true);
            }

            $pdf->save($path);

            $invoice->update(['pdf_path' => $filename]);

            \Log::info('Invoice PDF generated', [
                'invoice_id' => $invoice->id,
                'pdf_path' => $filename,
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to generate invoice PDF', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
            ]);
        }

        return $invoice;
    }

    /**
     * Retry failed payment - creates new order and transaction
     * IMPORTANT: This does NOT update the failed order/transaction.
     * It creates completely new order and transaction records.
     */
    public function retryPayment(Request $request, $orderId)
    {
        $user = $request->user();

        // Find the failed or initiated order that needs retry
        $failedOrder = Order::where('id', $orderId)
            ->where('user_id', $user->id)
            ->whereIn('status', ['failed', 'FAILED', 'initiated', 'INITIATED', 'pending', 'PENDING'])
            ->first();

        if (!$failedOrder) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found or cannot be retried',
            ], 404);
        }

        // Mark the old failed order as explicitly failed if not already
        if (!in_array($failedOrder->status, ['failed', 'FAILED'])) {
            $failedOrder->update(['status' => 'failed']);
        }

        // Mark old payment transaction as failed
        PaymentTransaction::where('order_id', $failedOrder->id)
            ->whereIn('status', ['INITIATED', 'PENDING'])
            ->update(['status' => 'FAILED']);

        // Get the package
        $package = CoinPackage::find($failedOrder->package_id);
        if (!$package || !$package->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Package no longer available',
            ], 404);
        }

        DB::beginTransaction();

        try {
            // Create new order (copy from failed order)
            $receipt = 'coin_retry_' . $user->id . '_' . time();

            // Use CoinPricingService for dynamic pricing
            $pricingData = CoinPricingService::calculatePackagePrice($package, $user);
            
            $isIndia = $pricingData['is_india'];
            $currency = $pricingData['currency'];
            $subtotal = $pricingData['subtotal'];
            $taxAmount = $pricingData['tax_amount'];
            $totalAmount = $pricingData['total'];
            $gstRate = $pricingData['gst_rate'];

            \Log::info('Retry payment pricing', [
                'user_id' => $user->id,
                'country_iso' => $user->country_iso,
                'is_india' => $isIndia,
                'currency' => $currency,
                'total' => $totalAmount,
            ]);

            // Create new order copying data from failed order
            $newOrder = Order::create([
                'user_id' => $user->id,
                'amount' => $totalAmount,
                'currency' => $currency,
                'package_id' => $package->id,
                'coins' => $package->coins,
                'bonus_coins' => $package->bonus_coins,
                'status' => 'INITIATED',
                'receipt' => $receipt,
                'meta' => [
                    'retry_of_order' => $failedOrder->id,
                    'original_order_created_at' => $failedOrder->created_at->toIso8601String(),
                    'package_name' => $package->name,
                    'user_country' => $user->country,
                    'user_country_iso' => $user->country_iso,
                    'pricing' => [
                        'is_india' => $isIndia,
                        'gst_rate' => $gstRate,
                        'subtotal' => $subtotal,
                        'tax_amount' => $taxAmount,
                        'total_amount' => $totalAmount,
                        'currency' => $currency,
                    ],
                ],
            ]);

            // Create Razorpay order
            $rzp = new RazorpayApi(
                config('services.razorpay.key'),
                config('services.razorpay.secret')
            );

            $razorpayOrder = $rzp->order->create([
                'amount' => (int) round($totalAmount * 100),
                'currency' => $currency,
                'receipt' => $receipt,
                'payment_capture' => 1,
                'notes' => [
                    'db_order_id' => $newOrder->id,
                    'user_id' => $user->id,
                    'package_id' => $package->id,
                    'type' => 'coin_purchase_retry',
                    'original_order_id' => $failedOrder->id,
                    'user_country_iso' => $user->country_iso,
                    'display_currency' => $currency,
                    'display_amount' => $totalAmount,
                ],
            ]);

            // Update order with Razorpay ID
            $newOrder->update([
                'razorpay_order_id' => $razorpayOrder['id'],
            ]);

            // Create new payment transaction (INITIATED)
            $transaction = PaymentTransaction::create([
                'user_id' => $user->id,
                'order_id' => $newOrder->id,
                'razorpay_order_id' => $razorpayOrder['id'],
                'type' => 'coin_purchase',
                'status' => 'INITIATED',
                'amount' => $totalAmount,
                'currency' => $currency,
                'coins' => $package->coins,
                'bonus_coins' => $package->bonus_coins,
                'description' => "Coin purchase (Retry): {$package->name}",
                'meta' => [
                    'package_id' => $package->id,
                    'package_name' => $package->name,
                    'user_country' => $user->country,
                    'is_india' => $isIndia,
                    'status' => 'INITIATED',
                    'retry_of_order' => $failedOrder->id,
                    'original_transaction_id' => PaymentTransaction::where('order_id', $failedOrder->id)->value('id'),
                    'pricing' => [
                        'is_india' => $isIndia,
                        'currency' => $currency,
                        'subtotal' => $subtotal,
                        'tax_amount' => $taxAmount,
                        'gst_rate' => $gstRate,
                        'total_amount' => $totalAmount,
                    ],
                ],
            ]);

            DB::commit();

            \Log::info('Payment retry order created', [
                'user_id' => $user->id,
                'original_order_id' => $failedOrder->id,
                'new_order_id' => $newOrder->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'New order created for retry',
                'order' => [
                    'id' => $razorpayOrder['id'],
                    'db_order_id' => $newOrder->id,
                    'razorpay_order_id' => $razorpayOrder['id'],
                    'amount' => intval($totalAmount * 100),
                    'currency' => $currency,
                    'gst_amount' => $taxAmount,
                    'gst_rate' => $isIndia ? $gstRate : 0,
                ],
                'transaction_id' => $transaction->id,
                'package' => [
                    'id' => $package->id,
                    'name' => $package->name,
                    'coins' => $package->coins,
                    'bonus_coins' => $package->bonus_coins,
                    'price' => $package->price,
                ],
                'pricing' => [
                    'is_india' => $isIndia,
                    'gst_rate' => $isIndia ? $gstRate : 0,
                    'subtotal_inr' => $subtotal,
                    'tax_amount_inr' => $taxAmount,
                    'total' => $totalAmount,
                    'currency' => $currency,
                ],
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            \Log::error('Payment retry failed', [
                'user_id' => $user->id,
                'original_order_id' => $failedOrder->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to create retry order',
            ], 500);
        }
    }

    /**
     * Check pending payment status and schedule job if needed
     */
    public function checkPendingPayment(Request $request, $orderId)
    {
        $user = $request->user();

        $order = Order::where('id', $orderId)
            ->orWhere('razorpay_order_id', $orderId)
            ->where('user_id', $user->id)
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found',
            ], 404);
        }

        $transaction = PaymentTransaction::where('order_id', $order->id)->first();

        if (!$transaction) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction not found',
            ], 404);
        }

        // Check current status
        if ($order->status === 'completed') {
            return response()->json([
                'success' => true,
                'status' => 'completed',
                'message' => 'Payment already completed',
                'order' => $order,
                'transaction' => $transaction,
            ]);
        }

        if ($order->status === 'failed') {
            return response()->json([
                'success' => false,
                'status' => 'failed',
                'message' => 'Payment failed',
                'order' => $order,
                'transaction' => $transaction,
                'can_retry' => true,
            ]);
        }

        // If pending, check if we should dispatch the status check job
        $createdAt = $order->created_at;
        $minutesSinceCreation = now()->diffInMinutes($createdAt);

        if ($minutesSinceCreation >= 15) {
            // Dispatch job immediately to check status
            CheckPendingPaymentStatus::dispatch($order->id, $transaction->id);

            return response()->json([
                'success' => true,
                'status' => 'checking',
                'message' => 'Payment status check initiated. Please wait a moment.',
            ]);
        } else {
            // Schedule job for later
            $delayMinutes = 15 - $minutesSinceCreation;
            CheckPendingPaymentStatus::dispatch($order->id, $transaction->id)
                ->delay(now()->addMinutes($delayMinutes));

            // Send pending notification via job
            SendPaymentPendingNotification::dispatch($user, $order, $transaction);

            return response()->json([
                'success' => true,
                'status' => 'pending',
                'message' => "Payment pending. Status will be checked in {$delayMinutes} minutes.",
                'order' => $order,
                'transaction' => $transaction,
                'check_after_minutes' => $delayMinutes,
            ]);
        }
    }

    /**
     * Download invoice PDF
     */
    public function downloadInvoice(Request $request, $invoiceId)
    {
        $user = $request->user();

        $invoice = Invoice::where('id', $invoiceId)
            ->orWhere('invoice_number', $invoiceId)
            ->where('user_id', $user->id)
            ->with(['order', 'user'])
            ->first();

        if (!$invoice) {
            return response()->json([
                'message' => 'Invoice not found',
            ], 404);
        }

        // If PDF already exists, return it
        if ($invoice->pdf_path && file_exists(storage_path('app/public/' . $invoice->pdf_path))) {
            return response()->download(storage_path('app/public/' . $invoice->pdf_path));
        }

        // Otherwise, generate PDF on the fly
        try {
            $order = $invoice->order;
            $transaction = CoinTransaction::where('order_id', $order->id)->first();

            $pdf = Pdf::loadView('invoices.coin-purchase', [
                'invoice' => $invoice,
                'order' => $order,
                'user' => $user,
                'transaction' => $transaction,
            ]);

            return $pdf->download('invoice-' . $invoice->invoice_number . '.pdf');
        } catch (\Exception $e) {
            \Log::error('Failed to generate invoice PDF on download', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Failed to generate invoice PDF',
            ], 500);
        }
    }

    /**
     * View invoice in browser (HTML)
     */
    public function viewInvoice(Request $request, $invoiceId)
    {
        $user = $request->user();

        $invoice = Invoice::where('id', $invoiceId)
            ->orWhere('invoice_number', $invoiceId)
            ->where('user_id', $user->id)
            ->with(['order', 'user'])
            ->first();

        if (!$invoice) {
            abort(404, 'Invoice not found');
        }

        $order = $invoice->order;
        $transaction = CoinTransaction::where('order_id', $order->id)->first();

        return view('invoices.coin-purchase', [
            'invoice' => $invoice,
            'order' => $order,
            'user' => $user,
            'transaction' => $transaction,
        ]);
    }

    /**
     * Get invoice details
     */
    public function getInvoice(Request $request, $invoiceId)
    {
        $user = $request->user();

        $invoice = Invoice::where('id', $invoiceId)
            ->orWhere('invoice_number', $invoiceId)
            ->where('user_id', $user->id)
            ->with([
                'order', 
                'user:id,name,email,phone,address,city,area,country,country_code'
            ])
            ->first();

        if (!$invoice) {
            return response()->json([
                'message' => 'Invoice not found',
            ], 404);
        }

        return response()->json([
            'invoice' => $invoice,
            'download_url' => url('api/wallet/invoice/' . $invoice->id . '/download'),
        ]);
    }

    /**
     * Get all user invoices
     */
    public function getInvoices(Request $request)
    {
        $user = $request->user();

        $invoices = Invoice::where('user_id', $user->id)
            ->with(['order'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($invoices);
    }

    /**
     * Register device token for FCM notifications
     */
    public function registerDevice(Request $request)
    {
        $data = $request->validate([
            'fcm_token' => 'required|string',
            'device' => 'nullable|string',
            'platform' => 'nullable|string',
        ]);

        $user = $request->user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }
        
        $user->update(['fcm_token' => $data['fcm_token']]);

        \Log::info('FCM token registered', [
            'user_id' => $user->id,
            'platform' => $data['platform'] ?? null,
            'device' => $data['device'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Device registered for notifications',
            'device' => [
                'platform' => $data['platform'] ?? null,
                'name' => $data['device'] ?? null,
            ],
        ]);
    }
}