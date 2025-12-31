<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CoinPackage;
use App\Models\CoinTransaction;
use App\Models\Referral;
use App\Models\User;
use App\Models\Order;
use App\Models\Invoice;
use App\Jobs\CheckPendingPaymentStatus;
use App\Notifications\PaymentSuccessNotification;
use App\Notifications\PaymentFailedNotification;
use App\Notifications\PaymentPendingNotification;
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
        
        $perPage = $request->per_page ?? 20;
        $transactions = CoinTransaction::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

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
                'data' => $transactions->items(),
                'pagination' => [
                    'current_page' => $transactions->currentPage(),
                    'per_page' => $transactions->perPage(),
                    'total' => $transactions->total(),
                    'last_page' => $transactions->lastPage(),
                    'from' => $transactions->firstItem(),
                    'to' => $transactions->lastItem(),
                    'has_more_pages' => $transactions->hasMorePages(),
                    'next_page_url' => $transactions->nextPageUrl(),
                    'prev_page_url' => $transactions->previousPageUrl(),
                ],
            ],
        ]);
    }

    /**
     * Get payment history with filters
     */
    public function paymentHistory(Request $request)
    {
        $user = $request->user();
        
        $query = CoinTransaction::where('user_id', $user->id);

        // Filter by type
        if ($request->has('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->whereJsonContains('meta->status', $request->status);
        }

        // Search by description or payment ID
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhere('payment_id', 'like', "%{$search}%")
                    ->orWhere('order_id', 'like', "%{$search}%");
            });
        }

        // Date range filter
        if ($request->has('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->has('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $perPage = $request->per_page ?? 20;
        $transactions = $query->orderBy('created_at', 'desc')
            ->paginate($perPage);

        // Calculate statistics and sync user balance to net balance
        $stats = $this->calculateAndSyncBalance($user);

        return response()->json([
            'balance' => $stats['net_balance'],
            'stats' => $stats,
            'transactions' => [
                'data' => $transactions->items(),
                'pagination' => [
                    'current_page' => $transactions->currentPage(),
                    'per_page' => $transactions->perPage(),
                    'total' => $transactions->total(),
                    'last_page' => $transactions->lastPage(),
                    'from' => $transactions->firstItem(),
                    'to' => $transactions->lastItem(),
                    'has_more_pages' => $transactions->hasMorePages(),
                    'next_page_url' => $transactions->nextPageUrl(),
                    'prev_page_url' => $transactions->previousPageUrl(),
                ],
            ],
        ]);
    }

    /**
     * Calculate wallet stats and ensure user->coins matches net balance
     */
    private function calculateAndSyncBalance(User $user): array
    {
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
            'total_purchases' => CoinTransaction::where('user_id', $user->id)
                ->where('type', 'purchase')
                ->whereJsonContains('meta->status', 'completed')
                ->count(),
            'failed_payments' => CoinTransaction::where('user_id', $user->id)
                ->where('type', 'purchase')
                ->whereJsonContains('meta->status', 'failed')
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
    public function packages()
    {
        $packages = CoinPackage::active()
            ->orderBy('sort_order')
            ->orderBy('price')
            ->get();

        return response()->json($packages);
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

        DB::beginTransaction();

        try {
            // STEP 1: Create internal order FIRST
            $receipt = 'coin_' . $user->id . '_' . time();

            $order = Order::create([
                'user_id' => $user->id,
                'amount' => $package->price,
                'currency' => 'INR',
                'package_id' => $package->id,
                'coins' => $package->coins,
                'bonus_coins' => $package->bonus_coins,
                'status' => 'PENDING',
                'receipt' => $receipt,
            ]);

            // STEP 2: Create Razorpay order
            $rzp = new RazorpayApi(
                config('services.razorpay.key'),
                config('services.razorpay.secret')
            );

            $razorpayOrder = $rzp->order->create([
                'amount' => (int) ($package->price * 100),
                'currency' => 'INR',
                'receipt' => $receipt,
                'payment_capture' => 1,
                'notes' => [
                    'db_order_id' => $order->id,
                    'user_id' => $user->id,
                    'package_id' => $package->id,
                    'type' => 'coin_purchase',
                ],
            ]);

            // Update order with Razorpay ID
            $order->update([
                'razorpay_order_id' => $razorpayOrder['id'],
            ]);

            // STEP 3: Create transaction (INITIATED)
            $transaction = CoinTransaction::create([
                'user_id' => $user->id,
                'order_id' => $order->id,
                'razorpay_order_id' => $razorpayOrder['id'],
                'type' => 'CREDIT',
                'amount' => $package->price,
                'coins' => $package->coins + $package->bonus_coins,
                'balance_after' => $user->coins,
                'status' => 'INITIATED',
                'description' => "Coin purchase: {$package->name}",
                'meta' => [
                    'package_id' => $package->id,
                    'package_name' => $package->name,
                    // Persist coin breakdown so verification can rely on it later
                    'coins' => $package->coins,
                    'bonus_coins' => $package->bonus_coins,
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
                    'amount' => intval($package->price * 100),
                    'currency' => 'INR',
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
                ],
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            \Log::error('Coin purchase failed', [
                'user_id' => $user->id,
                'package_id' => $package->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to create order',
            ], 500);
        }
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
            'transaction_id' => 'required|exists:coin_transactions,id',
            'razorpay_payment_id' => 'required|string',
            'razorpay_order_id' => 'required|string',
            'razorpay_signature' => 'required|string',
        ]);

        $transaction = CoinTransaction::findOrFail($data['transaction_id']);
        $user = $request->user();

        // Verify transaction belongs to user
        if ($transaction->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        // Check if already processed
        if ($transaction->payment_id) {
            return response()->json([
                'success' => true,
                'message' => 'Payment already processed',
                'balance' => $user->coins,
                'coins_added' => 0,
            ]);
        }

        // Step 1: Verify Razorpay signature
        $payload = $data['razorpay_order_id'] . '|' . $data['razorpay_payment_id'];
        $expectedSig = hash_hmac('sha256', $payload, config('services.razorpay.secret'));

        if (!hash_equals($expectedSig, $data['razorpay_signature'])) {
            // Update transaction as failed
            $meta = $transaction->meta;
            $transaction->update([
                'meta' => array_merge($meta, [
                    'status' => 'failed',
                    'failure_reason' => 'Invalid signature',
                ]),
            ]);

            // Update order as failed
            $failedOrder = Order::where('razorpay_order_id', $data['razorpay_order_id'])->first();
            if ($failedOrder) {
                $failedOrder->update(['status' => 'failed']);
            }

            // Send failure notification + push
            $user->notify(new PaymentFailedNotification($failedOrder ?? new Order(), $transaction, 'Invalid signature'));

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

            // Get coins from meta
            $meta = $transaction->meta ?? [];
            $coins = $meta['coins']
                ?? ($order->coins ?? null)
                ?? $transaction->coins
                ?? 0;
            $bonusCoins = $meta['bonus_coins']
                ?? ($order->bonus_coins ?? null)
                ?? 0;

            // If we only have a total coin count, treat it as base coins with zero bonus
            $totalCoins = $coins + $bonusCoins;

            // Step 3: Update the pending transaction to completed
            $transaction->update([
                'amount' => $totalCoins,
                'balance_after' => $user->coins + $totalCoins,
                'payment_id' => $data['razorpay_payment_id'],
                'description' => "{$meta['package_name']}: {$coins} coins" . 
                    ($bonusCoins > 0 ? " + {$bonusCoins} bonus" : ""),
                'meta' => array_merge($meta, [
                    'status' => 'completed',
                    'razorpay_payment_id' => $data['razorpay_payment_id'],
                    'razorpay_signature' => $data['razorpay_signature'],
                ]),
            ]);

            // Step 4: Add coins to user wallet
            $user->increment('coins', $totalCoins);
            $user->refresh();

                // Step 5: Create invoice and generate PDF
                $invoice = $this->createInvoice($order, $transaction, $user);

                // Step 6: Send success notification + push
                $user->notify(new PaymentSuccessNotification($order, $transaction, $invoice));

            DB::commit();

            \Log::info('Payment completed successfully', [
                'user_id' => $user->id,
                'transaction_id' => $transaction->id,
                'order_id' => $order->id,
                'razorpay_order_id' => $data['razorpay_order_id'],
                'payment_id' => $data['razorpay_payment_id'],
                'coins_added' => $totalCoins,
                    'invoice_id' => $invoice->id,
            ]);

                // Step 7: Return response
            return response()->json([
                'success' => true,
                'message' => 'Payment successful! Coins credited to your wallet.',
                'coins_added' => $totalCoins,
                'coins_breakdown' => [
                    'base_coins' => $coins,
                    'bonus_coins' => $bonusCoins,
                ],
                'balance' => $user->coins,
                'transaction' => [
                    'id' => $transaction->id,
                    'status' => 'completed',
                    'amount' => $transaction->amount,
                    'updated_at' => $transaction->updated_at,
                ],
                'order' => [
                    'id' => $order->id,
                    'razorpay_order_id' => $order->razorpay_order_id,
                    'razorpay_payment_id' => $order->razorpay_payment_id,
                    'amount' => $order->amount,
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

            // Mark transaction as failed
            $meta = $transaction->meta;
            $transaction->update([
                'meta' => array_merge($meta, [
                    'status' => 'failed',
                    'failure_reason' => 'Server error: ' . $e->getMessage(),
                ]),
            ]);

                // Send failure notification
                $order = Order::where('razorpay_order_id', $data['razorpay_order_id'])->first();
                if ($order) {
                    $user->notify(new PaymentFailedNotification($order, $transaction, 'Server error'));
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

        // Find transaction by order ID
        $transaction = CoinTransaction::where('order_id', $orderId)->first();

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

        $transaction = CoinTransaction::where('order_id', $orderId)->first();

        if (!$transaction) {
            \Log::warning('Transaction not found for order: ' . $orderId);
            return;
        }

        // Skip if already processed
        if ($transaction->payment_id) {
            \Log::info('Payment already processed: ' . $paymentId);
            return;
        }

        DB::beginTransaction();
        try {
            $user = User::find($transaction->user_id);
            $meta = $transaction->meta;
            $totalCoins = $meta['coins'] + $meta['bonus_coins'];

            // Credit coins to user
            $user->increment('coins', $totalCoins);

            // Update transaction
            $transaction->update([
                'amount' => $totalCoins,
                'balance_after' => $user->coins,
                'payment_id' => $paymentId,
                'description' => "Purchased {$meta['package_name']} - {$meta['coins']} coins" . 
                    ($meta['bonus_coins'] > 0 ? " + {$meta['bonus_coins']} bonus" : ""),
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

                    // Create invoice
                    $invoice = $this->createInvoice($order, $transaction, $user);

                    // Send notification
                    $user->notify(new PaymentSuccessNotification($order, $transaction, $invoice));
                }

            DB::commit();
            \Log::info('Payment captured successfully', ['transaction_id' => $transaction->id]);
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

        $transaction = CoinTransaction::where('order_id', $orderId)->first();

        if (!$transaction) {
            \Log::warning('Transaction not found for failed payment: ' . $orderId);
            return;
        }

        $meta = $transaction->meta;
        $transaction->update([
            'payment_id' => $paymentId,
            'meta' => array_merge($meta, [
                'status' => 'failed',
                'failed_at' => now(),
                'error_code' => $payment['error_code'] ?? null,
                'error_description' => $payment['error_description'] ?? null,
                'failure_reason' => $payment['error_reason'] ?? 'Unknown',
            ]),
        ]);

            // Update order and send notification
            $order = Order::where('razorpay_order_id', $orderId)->first();
            if ($order) {
                $order->update(['status' => 'failed']);
            
                $user = User::find($transaction->user_id);
                if ($user) {
                    $reason = $payment['error_description'] ?? $payment['error_reason'] ?? 'Payment failed';
                    $user->notify(new PaymentFailedNotification($order, $transaction, $reason));
                }
            }

        \Log::info('Payment failed', [
            'transaction_id' => $transaction->id,
            'reason' => $payment['error_description'] ?? 'Unknown',
        ]);
    }

    /**
     * Handle order paid event
     */
    private function handleOrderPaid($order)
    {
        $orderId = $order['id'];
        
        $transaction = CoinTransaction::where('order_id', $orderId)->first();

        if (!$transaction) {
            \Log::warning('Transaction not found for paid order: ' . $orderId);
            return;
        }

        $meta = $transaction->meta;
        $transaction->update([
            'meta' => array_merge($meta, [
                'order_status' => 'paid',
                'paid_at' => now(),
            ]),
        ]);

        \Log::info('Order marked as paid', ['transaction_id' => $transaction->id]);
    }

    /**
     * Get payment order status
     */
    public function getOrderStatus(Request $request, $orderId)
    {
        $user = $request->user();
        
        $transaction = CoinTransaction::where('order_id', $orderId)
            ->where('user_id', $user->id)
            ->first();

        if (!$transaction) {
            return response()->json(['error' => 'Order not found'], 404);
        }

        $meta = $transaction->meta;
        
        return response()->json([
            'order_id' => $transaction->order_id,
            'payment_id' => $transaction->payment_id,
            'status' => $meta['status'] ?? 'pending',
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
        
        $transaction = CoinTransaction::where('order_id', $orderId)
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
     */
    public function retryPayment(Request $request, $orderId)
    {
        $user = $request->user();

        // Find the failed order
        $failedOrder = Order::where('id', $orderId)
            ->where('user_id', $user->id)
            ->where('status', 'failed')
            ->first();

        if (!$failedOrder) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found or cannot be retried',
            ], 404);
        }

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
            // Create new order
            $receipt = 'coin_retry_' . $user->id . '_' . time();

            $newOrder = Order::create([
                'user_id' => $user->id,
                'amount' => $package->price,
                'currency' => 'INR',
                'package_id' => $package->id,
                'coins' => $package->coins,
                'bonus_coins' => $package->bonus_coins,
                'status' => 'PENDING',
                'receipt' => $receipt,
                'meta' => [
                    'retry_of_order' => $failedOrder->id,
                ],
            ]);

            // Create Razorpay order
            $rzp = new RazorpayApi(
                config('services.razorpay.key'),
                config('services.razorpay.secret')
            );

            $razorpayOrder = $rzp->order->create([
                'amount' => (int) ($package->price * 100),
                'currency' => 'INR',
                'receipt' => $receipt,
                'payment_capture' => 1,
                'notes' => [
                    'db_order_id' => $newOrder->id,
                    'user_id' => $user->id,
                    'package_id' => $package->id,
                    'type' => 'coin_purchase_retry',
                    'original_order_id' => $failedOrder->id,
                ],
            ]);

            // Update order with Razorpay ID
            $newOrder->update([
                'razorpay_order_id' => $razorpayOrder['id'],
            ]);

            // Create new transaction
            $transaction = CoinTransaction::create([
                'user_id' => $user->id,
                'order_id' => $newOrder->id,
                'razorpay_order_id' => $razorpayOrder['id'],
                'type' => 'CREDIT',
                'amount' => $package->price,
                'coins' => $package->coins + $package->bonus_coins,
                'balance_after' => $user->coins,
                'description' => "Coin purchase (Retry): {$package->name}",
                'meta' => [
                    'package_id' => $package->id,
                    'package_name' => $package->name,
                    'coins' => $package->coins,
                    'bonus_coins' => $package->bonus_coins,
                    'status' => 'INITIATED',
                    'retry_of_order' => $failedOrder->id,
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
                    'amount' => intval($package->price * 100),
                    'currency' => 'INR',
                ],
                'transaction_id' => $transaction->id,
                'package' => [
                    'id' => $package->id,
                    'name' => $package->name,
                    'coins' => $package->coins,
                    'bonus_coins' => $package->bonus_coins,
                    'price' => $package->price,
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

        $transaction = CoinTransaction::where('order_id', $order->id)->first();

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

            // Send pending notification
            $user->notify(new PaymentPendingNotification($order, $transaction));

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
     * Get invoice details
     */
    public function getInvoice(Request $request, $invoiceId)
    {
        $user = $request->user();

        $invoice = Invoice::where('id', $invoiceId)
            ->orWhere('invoice_number', $invoiceId)
            ->where('user_id', $user->id)
            ->with(['order'])
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