<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\SubscriptionPlan;
use App\Models\UserSubscription;
use App\Models\Order;
use App\Services\SubscriptionService;
use App\Services\CoinPricingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SubscriptionController extends Controller
{
    protected $subscriptionService;
    protected $coinPricingService;

    public function __construct(SubscriptionService $subscriptionService, CoinPricingService $coinPricingService)
    {
        $this->subscriptionService = $subscriptionService;
        $this->coinPricingService = $coinPricingService;
    }

    /**
     * Get all active subscription plans
     * GET /api/subscriptions/plans
     */
    public function getPlans()
    {
        try {
            $user = Auth::user();
            $plans = $this->subscriptionService->getActivePlans($user);

            return response()->json([
                'success' => true,
                'data' => $plans,
                'user_country' => $user ? $user->country_iso : null,
                'is_india_user' => $user ? (strtoupper($user->country_iso ?? '') === 'IN') : null,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching subscription plans: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch subscription plans',
            ], 500);
        }
    }

    /**
     * Get user's current subscription status
     * GET /api/subscriptions/status
     */
    public function getStatus()
    {
        try {
            $user = Auth::user();
            $status = $this->subscriptionService->getSubscriptionStatus($user);

            return response()->json([
                'success' => true,
                'data' => $status,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching subscription status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch subscription status',
            ], 500);
        }
    }

    /**
     * Create subscription order (initiate payment)
     * POST /api/subscriptions/purchase
     */
    public function purchaseSubscription(Request $request)
    {
        try {
            $request->validate([
                'plan_id' => 'required|exists:subscription_plans,id',
            ]);

            $user = Auth::user();
            $planId = $request->plan_id;

            $plan = SubscriptionPlan::findOrFail($planId);

            if (!$plan->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'This subscription plan is not available',
                ], 400);
            }

            // Check for existing active subscription
            $existingSubscription = UserSubscription::where('user_id', $user->id)
                ->where('status', 'active')
                ->where('expires_at', '>', now())
                ->first();

            if ($existingSubscription) {
                // Check if this is an upgrade
                $existingPlanType = $existingSubscription->getPlanType();
                $newPlanType = $plan->getPlanType(); // Use the model method directly
                
                // Allow upgrade from BASIC to PRO only
                $isUpgrade = ($existingPlanType === 'BASIC' && $newPlanType === 'PRO');
                
                if (!$isUpgrade) {
                    // Block downgrades or same plan purchases
                    return response()->json([
                        'success' => false,
                        'message' => $existingPlanType === $newPlanType 
                            ? 'You already have this subscription plan'
                            : 'You cannot downgrade your subscription. Please wait for it to expire or contact support.',
                        'current_subscription' => [
                            'plan_name' => $existingSubscription->plan->name,
                            'plan_type' => $existingPlanType,
                            'expires_at' => $existingSubscription->expires_at->format('Y-m-d H:i:s'),
                        ],
                    ], 400);
                }
                // If upgrade: continue to purchase flow
            }

            // Create order
            $orderData = $this->subscriptionService->createSubscriptionOrder($user, $planId);

            // Get localized pricing
            $isIndia = strtoupper($user->country_iso ?? '') === 'IN';
            $plans = $this->subscriptionService->getActivePlans($user);
            $pricingPlan = $plans->firstWhere('id', $planId);

            // Get Razorpay key
            $razorpayKey = config('services.razorpay.key');

            // Calculate Razorpay amount (convert to smallest units) INCLUDING GST
            // pricingPlan['price'] already includes GST for India users
            $totalAmount = (float)$pricingPlan['price'];
            $amount = (int)($totalAmount * 100); // Convert to paise/cents

            return response()->json([
                'success' => true,
                'message' => 'Order created successfully',
                'data' => [
                    'order_id' => $orderData['order_id'],
                    'razorpay_order_id' => $orderData['razorpay_order_id'], // Use the actual Razorpay order ID
                    'amount' => $amount, // Total amount in paise/cents including GST
                    'currency' => $pricingPlan['currency'],
                    'razorpay_key' => $razorpayKey,
                    'plan_name' => $orderData['plan_name'],
                    'plan_details' => [
                        'base_price' => $pricingPlan['base_price'],
                        'gst_amount' => $pricingPlan['gst_amount'] ?? 0,
                        'gst_rate' => $pricingPlan['gst'],
                        'total_price' => $totalAmount, // Total price = base + GST
                        'display_price' => $pricingPlan['display_price'],
                        'currency' => $pricingPlan['currency'],
                        'validity_days' => $plan->validity_days,
                        'views_allowed' => $plan->views_allowed,
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            $errorDetails = [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'user_id' => Auth::id(),
                'plan_id' => $planId ?? null,
            ];
            Log::error('Error creating subscription order: ' . json_encode($errorDetails));
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create subscription order',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Verify subscription payment and activate
     * POST /api/subscriptions/verify-payment
     */
    public function verifyPayment(Request $request)
    {
        try {
            $request->validate([
                'order_id' => 'required|integer',
                'razorpay_payment_id' => 'required|string',
                'razorpay_order_id' => 'required|string',
                'razorpay_signature' => 'required|string',
                'payment_status' => 'nullable|string|in:success,failed,pending,captured',
            ]);

            $user = Auth::user();
            $order = Order::where('id', $request->order_id)
                ->where('user_id', $user->id)
                ->firstOrFail();

            if ($order->type !== 'subscription') {
                return response()->json([
                    'success' => false,
                    'message' => 'This order is not a subscription order',
                ], 400);
            }

            // Determine payment status from request
            $paymentStatus = strtolower($request->input('payment_status', 'success'));

            // Handle different payment statuses
            if ($paymentStatus === 'failed') {
                $result = $this->subscriptionService->handlePaymentFailed(
                    $user,
                    $order,
                    $request->input('error_message', 'Payment processing failed'),
                    $request->input('error_reason', 'unknown')
                );

                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                    'status' => 'failed',
                    'order_id' => $order->id,
                    'can_retry' => true,
                ], 400);
            }

            if ($paymentStatus === 'pending') {
                $result = $this->subscriptionService->handlePaymentPending($user, $order);

                return response()->json([
                    'success' => true,
                    'message' => $result['message'],
                    'status' => 'pending',
                    'order_id' => $order->id,
                ]);
            }

            // For success or captured status
            if ($paymentStatus === 'success' || $paymentStatus === 'captured') {
                // Verify signature for successful payments
                $expectedSignature = hash_hmac(
                    'sha256',
                    $order->id . '|' . $request->razorpay_payment_id,
                    config('services.razorpay.secret')
                );

                $subscription = $this->subscriptionService->verifyAndActivateSubscription(
                    $user,
                    $order,
                    $request->razorpay_payment_id,
                    $request->razorpay_order_id,
                    $request->razorpay_signature
                );

                return response()->json([
                    'success' => true,
                    'message' => 'Subscription activated successfully',
                    'data' => $subscription,
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Unknown payment status',
            ], 400);

        } catch (\Exception $e) {
            Log::error('Error verifying subscription payment: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to verify subscription payment',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Check if user can view content (with subscription or coins)
     * POST /api/subscriptions/check-view
     */
    public function checkView(Request $request)
    {
        try {
            $request->validate([
                'viewable_type' => 'required|string|in:tutor,requirement',
                'viewable_id' => 'required|integer',
            ]);

            $user = Auth::user();

            // First check subscription
            $canViewViaSubscription = $this->subscriptionService->canUserView($user);

            if ($canViewViaSubscription) {
                return response()->json([
                    'success' => true,
                    'can_view' => true,
                    'access_type' => 'subscription',
                    'subscription_status' => $this->subscriptionService->getSubscriptionStatus($user),
                ]);
            }

            // Fall back to coin-based access
            $costConfig = config('coins.pricing_by_nationality');
            $unlockType = $request->viewable_type === 'tutor' ? 'contact_unlock' : 'contact_unlock';
            $requiredCoins = $this->coinPricingService->getCoinCostForUser(
                $user,
                $unlockType
            );

            $canViewViaCoins = $user->coins >= $requiredCoins;

            return response()->json([
                'success' => true,
                'can_view' => $canViewViaCoins,
                'access_type' => 'coins',
                'coins_balance' => $user->coins,
                'required_coins' => $requiredCoins,
            ]);
        } catch (\Exception $e) {
            Log::error('Error checking view permission: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to check view permission',
            ], 500);
        }
    }

    /**
     * Get subscription history
     * GET /api/subscriptions/history
     */
    public function getHistory()
    {
        try {
            $user = Auth::user();
            $history = $this->subscriptionService->getSubscriptionHistory($user);

            return response()->json([
                'success' => true,
                'data' => $history,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching subscription history: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch subscription history',
            ], 500);
        }
    }

    /**
     * Cancel subscription
     * POST /api/subscriptions/cancel
     */
    public function cancelSubscription(Request $request)
    {
        try {
            $user = Auth::user();
            $reason = $request->input('reason', 'User requested cancellation');

            $cancelled = $this->subscriptionService->cancelActiveSubscription($user, $reason);

            if (!$cancelled) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active subscription to cancel',
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => 'Subscription cancelled successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Error cancelling subscription: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel subscription',
            ], 500);
        }
    }

    /**
     * Mark subscription payment as failed
     * POST /api/subscriptions/payment-failed
     */
    public function markPaymentFailed(Request $request)
    {
        try {
            $request->validate([
                'order_id' => 'required|integer',
                'error_message' => 'nullable|string',
                'error_reason' => 'nullable|string',
                'razorpay_error' => 'nullable|string',
            ]);

            $user = Auth::user();
            $order = Order::where('id', $request->order_id)
                ->where('user_id', $user->id)
                ->firstOrFail();

            $result = $this->subscriptionService->markPaymentFailed(
                $user,
                $order,
                $request->input('error_message', 'Payment processing failed'),
                $request->input('error_reason', 'unknown')
            );

            return response()->json($result, $result['success'] === false ? 400 : 200);
        } catch (\Exception $e) {
            Log::error('Error marking subscription payment as failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark payment as failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Check pending subscription payment status
     * GET /api/subscriptions/order/{orderId}/check-pending
     */
    public function checkPendingPayment(Request $request, $orderId)
    {
        try {
            $user = Auth::user();
            $order = Order::where('id', $orderId)
                ->orWhere('razorpay_order_id', $orderId)
                ->where('user_id', $user->id)
                ->firstOrFail();

            $result = $this->subscriptionService->checkPendingPayment($user, $order);

            return response()->json([
                'success' => $result['success'],
                'status' => $result['status'],
                'message' => $result['message'],
                'order_id' => $order->id,
                'data' => $result['subscription'] ?? null,
                'can_retry' => $result['can_retry'] ?? false,
                'can_check_status' => $result['can_check_status'] ?? false,
            ]);
        } catch (\Exception $e) {
            Log::error('Error checking pending subscription payment: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to check payment status',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

}

