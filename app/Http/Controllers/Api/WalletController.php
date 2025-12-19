<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CoinPackage;
use App\Models\CoinTransaction;
use App\Models\Referral;
use App\Models\User;
use Illuminate\Http\Request;
use Razorpay\Api\Api as RazorpayApi;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WalletController extends Controller
{
    /**
     * Get wallet with balance and recent transactions
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        $transactions = CoinTransaction::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $referralStats = [
            'total_referrals' => Referral::where('referrer_id', $user->id)->count(),
            'coins_earned' => Referral::where('referrer_id', $user->id)
                ->sum('referrer_coins'),
        ];

        return response()->json([
            'balance' => $user->coins,
            'referral_code' => $user->referral_code,
            'referral_stats' => $referralStats,
            'transactions' => $transactions,
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

        $transactions = $query->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 20);

        // Calculate statistics
        $stats = [
            'total_spent' => CoinTransaction::where('user_id', $user->id)
                ->where('type', 'purchase')
                ->whereJsonContains('meta->status', 'completed')
                ->sum('amount'),
            'total_earned' => CoinTransaction::where('user_id', $user->id)
                ->whereIn('type', ['referral_bonus', 'referral_reward', 'admin_credit'])
                ->sum('amount'),
            'total_purchases' => CoinTransaction::where('user_id', $user->id)
                ->where('type', 'purchase')
                ->whereJsonContains('meta->status', 'completed')
                ->count(),
            'failed_payments' => CoinTransaction::where('user_id', $user->id)
                ->where('type', 'purchase')
                ->whereJsonContains('meta->status', 'failed')
                ->count(),
        ];

        return response()->json([
            'transactions' => $transactions,
            'stats' => $stats,
        ]);
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
            // Create Razorpay order
            $rzp = new RazorpayApi(
                config('services.razorpay.key'), 
                config('services.razorpay.secret')
            );
            
            $order = $rzp->order->create([
                'amount' => intval($package->price * 100), // Convert to paise
                'currency' => 'INR',
                'receipt' => 'coin_' . $user->id . '_' . time(),
                'payment_capture' => 1,
                'notes' => [
                    'user_id' => $user->id,
                    'package_id' => $package->id,
                    'type' => 'coin_purchase',
                ],
            ]);

            // Create pending transaction record
            $transaction = CoinTransaction::create([
                'user_id' => $user->id,
                'type' => 'purchase',
                'amount' => 0, // Will be updated after payment
                'balance_after' => $user->coins,
                'description' => "Purchase {$package->name} (Pending)",
                'order_id' => $order['id'],
                'meta' => [
                    'package_id' => $package->id,
                    'package_name' => $package->name,
                    'coins' => $package->coins,
                    'bonus_coins' => $package->bonus_coins,
                    'price' => $package->price,
                    'status' => 'pending',
                ],
            ]);

            DB::commit();

            $isProduction = app()->environment('production');
            $baseUrl = $isProduction ? config('app.url') : url('/');

            return response()->json([
                'order' => $order,
                'transaction_id' => $transaction->id,
                'package' => $package,
                'user' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                ],
                'callback_url' => $baseUrl . '/api/wallet/payment-callback',
                'redirect' => [
                    'success_url' => $user->hasRole('tutor') 
                        ? url('/tutor/wallet?payment=success')
                        : url('/student/wallet?payment=success'),
                    'cancel_url' => $user->hasRole('tutor')
                        ? url('/tutor/wallet?payment=cancelled')
                        : url('/student/wallet?payment=cancelled'),
                ],
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('Coin purchase failed: ' . $e->getMessage());
            return response()->json(['error' => 'Could not create purchase order'], 500);
        }
    }

    /**
     * Verify Razorpay payment and credit coins
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

        // Ensure transaction belongs to user
        if ($transaction->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Check if already processed
        if ($transaction->payment_id) {
            return response()->json([
                'message' => 'Payment already processed',
                'balance' => $user->coins
            ]);
        }

        // Verify Razorpay signature
        $payload = $data['razorpay_order_id'] . '|' . $data['razorpay_payment_id'];
        $expectedSig = hash_hmac('sha256', $payload, config('services.razorpay.secret'));

        if (!hash_equals($expectedSig, $data['razorpay_signature'])) {
            // Mark transaction as failed
            $meta = $transaction->meta;
            $transaction->update([
                'meta' => array_merge($meta, [
                    'status' => 'failed',
                    'failure_reason' => 'Invalid signature',
                ]),
            ]);
            return response()->json(['message' => 'Invalid payment signature'], 422);
        }

        DB::beginTransaction();
        try {
            $meta = $transaction->meta;
            $totalCoins = $meta['coins'] + $meta['bonus_coins'];

            // Credit coins to user
            $user->increment('coins', $totalCoins);

            // Update transaction
            $transaction->update([
                'amount' => $totalCoins,
                'balance_after' => $user->coins,
                'payment_id' => $data['razorpay_payment_id'],
                'description' => "Purchased {$meta['package_name']} - {$meta['coins']} coins" . 
                    ($meta['bonus_coins'] > 0 ? " + {$meta['bonus_coins']} bonus" : ""),
                'meta' => array_merge($meta, ['status' => 'completed']),
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Payment successful! Coins credited.',
                'coins_added' => $totalCoins,
                'balance' => $user->coins,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('Payment verification failed: ' . $e->getMessage());
            return response()->json(['error' => 'Could not process payment'], 500);
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

        $meta = $transaction->meta;

        if (isset($meta['status']) && $meta['status'] !== 'pending') {
            return response()->json(['error' => 'Cannot cancel this payment'], 400);
        }

        $transaction->update([
            'meta' => array_merge($meta, [
                'status' => 'cancelled',
                'cancelled_at' => now(),
            ]),
        ]);

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
}