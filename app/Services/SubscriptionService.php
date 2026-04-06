<?php

namespace App\Services;

use App\Models\User;
use App\Models\SubscriptionPlan;
use App\Models\UserSubscription;
use App\Models\SubscriptionViewLog;
use App\Models\SubscriptionOrder;
use App\Models\SubscriptionTransaction;
use App\Models\Order;
use App\Models\Invoice;
use App\Models\PaymentTransaction;
use App\Enums\PaymentStatus;
use App\Jobs\SendSubscriptionSuccessNotification;
use App\Jobs\SendSubscriptionFailureNotification;
use App\Jobs\SendSubscriptionPendingNotification;
use Razorpay\Api\Api as RazorpayApi;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SubscriptionService
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }
    /**
     * Get active subscription plans with localized pricing
     */
    public function getActivePlans(User $user = null)
    {
        $plans = SubscriptionPlan::getActiveSubscriptions();
        
        // Detect if user is from India
        $isIndiaUser = $user ? $this->isIndiaUser($user) : false;

        $result = $plans->map(function ($plan) use ($user, $isIndiaUser) {
            $pricing = $this->getPlanPricingForUser($plan, $isIndiaUser);

            return [
                'id' => $plan->id,
                'name' => $plan->name,
                'base_price' => $plan->price,
                'price' => $pricing['price'],
                'display_price' => $pricing['display_price'],
                'currency' => $pricing['currency'],
                'gst' => $pricing['gst'],
                'gst_amount' => $pricing['gst_amount'] ?? 0,
                'validity_days' => $plan->validity_days,
                'validity_text' => $this->getValidityText($plan->validity_days),
                'views_allowed' => $plan->views_allowed,
                'views_text' => $this->getViewsText($plan->views_allowed),
                'coins_included' => $plan->coins_included,
                'coins_included_text' => $plan->coins_included . ' coins included',
                'cost_per_view' => $plan->cost_per_view,
                'cost_per_view_text' => 'Minimum ' . $plan->cost_per_view . ' coins per view',
                'coins_carry_forward' => $plan->coins_carry_forward,
                'coins_carry_forward_text' => $plan->coins_carry_forward ? 'Coins carry forward' : 'Coins do not carry forward',
                'access_delay_hours' => $plan->access_delay_hours,
                'access_delay_text' => $plan->access_delay_hours > 0 ? $plan->access_delay_hours . '-2 hour delay' : 'Instant access',
                'unlimited_views' => $plan->views_allowed === null,
                'description' => $plan->description,
                'display_order' => $plan->display_order,
                'is_india' => $isIndiaUser,
            ];
        });

        return $result;
    }

    /**
     * Get user's current active subscription
     */
    public function getUserActiveSubscription(User $user)
    {
        $subscription = UserSubscription::where('user_id', $user->id)
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->with('plan', 'order')
            ->first();

        if (!$subscription) {
            return null;
        }

        return $this->formatSubscriptionResponse($subscription);
    }

    /**
     * Get user's cancelled subscription that hasn't expired yet
     * Cancelled subscriptions continue to work until their expiry date
     */
    public function getCancelledButActiveSubscription(User $user)
    {
        $subscription = UserSubscription::where('user_id', $user->id)
            ->where('status', 'cancelled')
            ->where('expires_at', '>', now())
            ->with('plan', 'order')
            ->first();

        if (!$subscription) {
            return null;
        }

        return $this->formatSubscriptionResponse($subscription);
    }

    /**
     * Create subscription by purchasing a plan via Razorpay
     */
    public function createSubscriptionOrder(User $user, int $planId)
    {
        $plan = SubscriptionPlan::findOrFail($planId);

        if (!$plan->is_active) {
            throw new \Exception('This subscription plan is not available.');
        }

        // Cancel existing active subscription if any
        $this->cancelActiveSubscription($user, 'New subscription purchased');

        // Get pricing with GST
        $isIndia = $this->isIndiaUser($user);
        $pricing = $this->getPlanPricingForUser($plan, $isIndia);
        $totalAmount = $pricing['price']; // This includes GST for India users

        // STEP 1: Create order via PaymentService with total amount (including GST)
        $order = $this->paymentService->createPaymentOrder($user, [
            'order_id' => 'ORDER_SUB_' . time(),
            'amount' => $totalAmount,
            'currency' => $pricing['currency'],
            'type' => 'subscription',
            'payment_method' => 'razorpay',
            'metadata' => [
                'subscription_plan_id' => $planId,
                'plan_name' => $plan->name,
                'validity_days' => $plan->validity_days,
                'views_allowed' => $plan->views_allowed,
                'base_price' => $pricing['base_price'],
                'gst_amount' => $pricing['gst_amount'] ?? 0,
                'gst_rate' => $pricing['gst'] ?? 0,
                'total_amount' => $totalAmount,
            ],
        ]);

        // STEP 2: CREATE RAZORPAY ORDER
        $razorpayAmount = (int)($totalAmount * 100); // Convert to paise/cents
        
        $razorpayApi = new RazorpayApi(
            config('services.razorpay.key'),
            config('services.razorpay.secret')
        );

        try {
            $razorpayOrder = $razorpayApi->order->create([
                'amount' => $razorpayAmount,
                'currency' => $pricing['currency'],
                'receipt' => 'sub_' . $order->id . '_' . time(),
                'payment_capture' => 1,
                'notes' => [
                    'db_order_id' => $order->id,
                    'user_id' => $user->id,
                    'subscription_plan_id' => $planId,
                    'plan_name' => $plan->name,
                    'type' => 'subscription',
                    'is_india' => $isIndia ? 'true' : 'false',
                ],
            ]);

            // Update order with Razorpay order ID
            $order->update([
                'razorpay_order_id' => $razorpayOrder['id'],
            ]);
        } catch (\Exception $e) {
            // Log Razorpay API error but don't fail - order was created
            \Log::error('Razorpay order creation failed for subscription', [
                'order_id' => $order->id,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            // Re-throw to inform the controller
            throw new \Exception('Failed to create Razorpay order: ' . $e->getMessage());
        }

        // STEP 3: Create payment transaction (INITIATED status)
        $this->paymentService->createPaymentTransaction(
            $user,
            $order,
            'subscription_purchase',
            [
                'description' => "Subscription purchase: {$plan->name}",
                'razorpay_order_id' => $razorpayOrder['id'],
                'metadata' => [
                    'subscription_plan_id' => $planId,
                    'plan_name' => $plan->name,
                    'validity_days' => $plan->validity_days,
                    'views_allowed' => $plan->views_allowed,
                    'price' => $plan->price,
                    'currency' => $plan->currency ?? 'INR',
                ],
            ]
        );

        return [
            'order_id' => $order->id,
            'razorpay_order_id' => $razorpayOrder['id'],
            'amount' => $razorpayAmount,
            'currency' => $pricing['currency'],
            'plan_id' => $planId,
            'plan_name' => $plan->name,
        ];
    }

    /**
     * Verify subscription payment and activate subscription
     */
    public function verifyAndActivateSubscription(
        User $user,
        Order $order,
        string $razorpayPaymentId,
        string $razorpayOrderId,
        string $razorpaySignature
    )
    {
        if ($order->type !== 'subscription') {
            throw new \Exception('This order is not a subscription order.');
        }

        if ($order->status === 'completed') {
            // Already processed
            return $this->getUserActiveSubscription($user);
        }

        $planId = $order->metadata['subscription_plan_id'] ?? null;
        $plan = SubscriptionPlan::findOrFail($planId);

        // Track if this is an upgrade for logging
        $isUpgrade = false;
        $oldPlanName = null;
        $oldCoinsSpent = 0;
        $oldCoinsAllowed = 0;

        // Check for existing active subscription (upgrade scenario)
        $existingSubscription = UserSubscription::where('user_id', $user->id)
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->first();

        // If upgrading from BASIC to PRO
        if ($existingSubscription) {
            $existingPlanType = $existingSubscription->getPlanType();
            $newPlanType = $plan->getPlanType(); // Use the model method directly
            
            if ($existingPlanType === 'BASIC' && $newPlanType === 'PRO') {
                // ✅ UPGRADE FLOW: Mark old subscription as history, new subscription gets full fresh benefits
                $isUpgrade = true;
                $oldPlanName = $existingSubscription->plan->name;
                $oldCoinsSpent = $existingSubscription->coins_spent ?? 0;
                $oldCoinsAllowed = $existingSubscription->plan->coins_included ?? 0;
                
                // Mark old subscription as history (inactive)
                $existingSubscription->update([
                    'status' => 'history',
                    'expires_at' => now(), // Immediately inactive
                    'upgraded_to_subscription_id' => null, // Will be set after new subscription created
                ]);
                
                // Log the upgrade
                \Log::info('Subscription upgraded', [
                    'user_id' => $user->id,
                    'from_plan' => $oldPlanName,
                    'to_plan' => $plan->name,
                    'old_coins_allowed' => $oldCoinsAllowed,
                    'old_coins_spent' => $oldCoinsSpent,
                    'message' => 'Upgrading to new plan with full fresh benefits (no carryover)',
                ]);
            }
        }

        // Create new UserSubscription record
        $activatedAt = now();
        $expiresAt = $this->calculateSubscriptionRenewalTime($plan);

        $subscription = UserSubscription::create([
            'user_id' => $user->id,
            'subscription_plan_id' => $plan->id,
            'order_id' => $order->id,
            'activated_at' => $activatedAt,
            'expires_at' => $expiresAt,
            'views_used' => 0, // Fresh start with no views used
            'status' => 'active',
        ]);

        // If this was an upgrade, link old subscription as history
        if ($isUpgrade) {
            $existingSubscription->update(['upgraded_to_subscription_id' => $subscription->id]);
        }

        // Update order status using PaymentService
        $this->paymentService->updateOrderPaymentStatus(
            $order,
            PaymentStatus::SUCCESS,
            [
                'razorpay_payment_id' => $razorpayPaymentId,
                'razorpay_order_id' => $razorpayOrderId,
                'razorpay_signature' => $razorpaySignature,
            ]
        );

        // Update payment transaction status to SUCCESS
        $transaction = $order->paymentTransactions()->first();
        if ($transaction) {
            $this->paymentService->updateTransactionPaymentStatus(
                $transaction,
                PaymentStatus::SUCCESS,
                [
                    'razorpay_payment_id' => $razorpayPaymentId,
                    'razorpay_order_id' => $razorpayOrderId,
                ]
            );
        }

        // ✅ CREDIT COINS BASED ON PLAN CONFIGURATION
        // Use coins_included field from the plan for flexibility
        $coinsToCredit = $plan->coins_included ?? 0;
        $isPROPlan = $plan->isPROPlan();

        // Credit coins to user if applicable
        if ($coinsToCredit > 0) {
            $user->increment('coins', $coinsToCredit);
            $user->refresh();
        }

        // Log transaction in coin system for tracking
        $upgradeText = $isUpgrade ? " (UPGRADED from {$oldPlanName})" : "";
        $coinDescription = $coinsToCredit > 0 
            ? " - {$coinsToCredit} coins credited"
            : " (view-based only)";
        
        $description = "Subscription purchased: {$plan->name} (₹{$plan->price}) for {$plan->validity_days} days{$coinDescription}{$upgradeText}";
        
        \App\Models\CoinTransaction::create([
            'user_id' => $user->id,
            'type' => 'subscription_purchase',
            'amount' => $coinsToCredit,
            'balance_after' => $user->coins,
            'description' => $description,
            'order_id' => $order->id,
            'meta' => [
                'subscription_id' => $subscription->id,
                'plan_name' => $plan->name,
                'plan_id' => $plan->id,
                'is_pro_plan' => $isPROPlan,
                'validity_days' => $plan->validity_days,
                'views_allowed' => $plan->views_allowed,
                'coin_spent' => 0, // Fresh start - no coins spent yet
                'is_upgrade' => $isUpgrade,
                'upgraded_from_plan' => $isUpgrade ? $oldPlanName : null,
                'old_plan_coins_allowed' => $isUpgrade ? $oldCoinsAllowed : null,
                'old_plan_coins_spent' => $isUpgrade ? $oldCoinsSpent : null,
            ],
        ]);

        // Create subscription invoice
        $invoice = $this->createSubscriptionInvoice($order, $subscription, $user);

        // Send success notification, email, and in-app notification via job
        SendSubscriptionSuccessNotification::dispatch($user, $order, $subscription, $invoice);

        return $this->formatSubscriptionResponse($subscription);
    }

    /**
     * Create invoice for subscription purchase
     */
    private function createSubscriptionInvoice($order, $subscription, $user)
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
            'status' => 'paid',
            'issued_at' => now(),
            'meta' => [
                'type' => 'subscription',
                'subscription_id' => $subscription->id,
                'plan_name' => $order->metadata['plan_name'] ?? 'Subscription',
                'validity_days' => $order->metadata['validity_days'] ?? null,
                'views_allowed' => $order->metadata['views_allowed'] ?? null,
            ],
        ]);

        return $invoice;
    }

    /**
     * Calculate next subscription renewal time
     * 
     * @param SubscriptionPlan $plan
     * @return \Carbon\Carbon
     */
    private function calculateSubscriptionRenewalTime(SubscriptionPlan $plan)
    {
        $validityDays = $plan->validity_days ?? 365;
        return now()->addDays($validityDays);
    }


    /**
     * Log a view for subscription tracking
     */
    public function logView(User $user, $viewable, string $ipAddress = null, string $userAgent = null)
    {
        $subscription = $user->activeSubscription();

        // If no active subscription, allow view (user must have bought access separately)
        if (!$subscription) {
            return ['allowed' => true, 'reason' => 'no_subscription'];
        }

        // Check if subscription allows more views
        if (!$subscription->canView()) {
            return [
                'allowed' => false,
                'reason' => 'views_exhausted',
                'message' => 'You have exhausted your view limit for this subscription.',
            ];
        }

        // Log the view
        SubscriptionViewLog::create([
            'user_id' => $user->id,
            'user_subscription_id' => $subscription->id,
            'viewable_id' => $viewable->id,
            'viewable_type' => get_class($viewable),
            'action_type' => 'generic_view',
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'viewed_at' => now(),
        ]);

        // Increment view count
        $subscription->incrementViewCount();

        return [
            'allowed' => true,
            'reason' => 'view_logged',
            'remaining_views' => $subscription->getRemainingViews(),
        ];
    }

    /**
     * Check if user can view a resource
     */
    public function canUserView(User $user, $viewable = null): bool
    {
        $subscription = $user->activeSubscription();

        if (!$subscription) {
            // No subscription - normally handled by contact unlock coins
            return false;
        }

        return $subscription->canView();
    }

    /**
     * Get subscription status for user
     */
    public function getSubscriptionStatus(User $user)
    {
        $subscription = $this->getUserActiveSubscription($user);

        // Check for cancelled subscription that hasn't expired yet
        if (!$subscription) {
            $cancelledSubscription = $this->getCancelledButActiveSubscription($user);
            if ($cancelledSubscription) {
                $subscription = $cancelledSubscription;
            }
        }

        if (!$subscription) {
            // Check for lapsed subscription (coins carry forward case)
            $lapsedSubscription = $user->getMostRecentSubscription();
            
            if ($lapsedSubscription && $lapsedSubscription->hasExpired() && $lapsedSubscription->isPROPlan()) {
                $isIndia = $this->isIndiaUser($user);
                $daysSinceExpiry = now()->diffInDays($lapsedSubscription->expires_at);
                
                return [
                    'has_active_subscription' => false,
                    'has_lapsed_subscription' => true,
                    'message' => 'Your subscription has expired',
                    'plan_name' => $lapsedSubscription->plan->name,
                    'expired_at' => $lapsedSubscription->expires_at,
                    'days_since_expiry' => $daysSinceExpiry,
                    'remaining_coins' => $user->coins,
                    'coins_display' => $this->formatCoins($user->coins, $isIndia),
                    'currency' => $isIndia ? 'INR' : 'USD',
                    'view_cost_with_coins' => 39, // PRO plan cost when using coins
                    'can_view_with_coins' => $user->coins >= 39,
                    'delay_hours' => 2,
                    'delay_message' => 'Your subscription has expired. You can still view requirements with your remaining coins, but with a 2-hour delay. Re-subscribe for immediate access.'
                ];
            }
            
            // No subscription and no lapsed subscription
            return [
                'has_active_subscription' => false,
                'has_lapsed_subscription' => false,
                'message' => 'No active subscription',
            ];
        }

        // Get pricing information for the subscription
        $isIndia = $this->isIndiaUser($user);
        $plan = SubscriptionPlan::find($subscription['plan_id']);
        $pricing = $this->getPlanPricingForUser($plan, $isIndia);

        return [
            'has_active_subscription' => true,
            'has_lapsed_subscription' => false,
            'plan_name' => $subscription['plan_name'],
            'plan_id' => $subscription['plan_id'],
            'views_allowed' => $subscription['views_allowed'],
            'views_used' => $subscription['views_used'],
            'remaining_views' => $subscription['remaining_views'],
            'unlimited_views' => $subscription['views_allowed'] === null,
            'views_text' => $this->getViewsText($subscription['views_allowed']),
            'coins_included' => $plan->coins_included,
            'coins_included_text' => $plan->coins_included . ' coins included',
            'cost_per_view' => $plan->cost_per_view,
            'cost_per_view_text' => 'Minimum ' . $plan->cost_per_view . ' coins per view',
            'coins_carry_forward' => $plan->coins_carry_forward,
            'coins_carry_forward_text' => $plan->coins_carry_forward ? 'Coins carry forward' : 'Coins do not carry forward',
            'access_delay_hours' => $plan->access_delay_hours,
            'access_delay_text' => $plan->access_delay_hours > 0 ? $plan->access_delay_hours . '-2 hour delay' : 'Instant access',
            'remaining_days' => $subscription['remaining_days'],
            'expires_at' => $subscription['expires_at'],
            'activated_at' => $subscription['activated_at'],
            'validity_days' => $subscription['validity_days'],
            'validity_text' => $this->getValidityText($subscription['validity_days']),
            'price' => $pricing['price'],
            'base_price' => $pricing['base_price'],
            'display_price' => $pricing['display_price'],
            'currency' => $pricing['currency'],
            'gst_amount' => $pricing['gst_amount'] ?? 0,
            'gst_rate' => $pricing['gst'],
        ];
    }
    
    /**
     * Format coins display with currency
     */
    private function formatCoins($coins, $isIndia = true)
    {
        $prefix = $isIndia ? '₹' : '$';
        $value = $coins * 1; // Assuming 1 coin = 1 unit of currency
        return $prefix . number_format($value, 0);
    }

    /**
     * Renew an expiring subscription
     */
    public function renewSubscription(User $user, int $planId)
    {
        $activeSubscription = UserSubscription::where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        if ($activeSubscription && !$activeSubscription->hasExpired()) {
            // Extend the current subscription
            return $this->extendSubscription($user, $activeSubscription, $planId);
        }

        // Create new subscription
        return $this->createSubscriptionOrder($user, $planId);
    }

    /**
     * Extend an active subscription
     */
    private function extendSubscription(User $user, UserSubscription $subscription, int $newPlanId)
    {
        $newPlan = SubscriptionPlan::findOrFail($newPlanId);
        $newExpiresAt = $subscription->expires_at->copy()->addDays($newPlan->validity_days);

        $subscription->update([
            'subscription_plan_id' => $newPlan->id,
            'expires_at' => $newExpiresAt,
            'views_used' => 0, // Reset view counter for new plan
        ]);

        return $this->formatSubscriptionResponse($subscription);
    }

    /**
     * Cancel user's active subscription
     */
    public function cancelActiveSubscription(User $user, string $reason = null)
    {
        $subscription = UserSubscription::where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        if (!$subscription) {
            return false;
        }

        $subscription->update([
            'status' => 'cancelled',
            'cancellation_reason' => $reason,
            'cancelled_at' => now(),
        ]);

        return true;
    }

    /**
     * Get user's subscription history
     */
    public function getSubscriptionHistory(User $user, int $limit = 10)
    {
        $subscriptions = UserSubscription::where('user_id', $user->id)
            ->orderBy('activated_at', 'desc')
            ->limit($limit)
            ->get();

        $isIndia = $this->isIndiaUser($user);

        return $subscriptions->map(function ($subscription) use ($isIndia) {
            $plan = $subscription->plan;
            $pricing = $this->getPlanPricingForUser($plan, $isIndia);

            return [
                'id' => $subscription->id,
                'plan_name' => $plan->name,
                'price' => $pricing['price'],
                'display_price' => $pricing['display_price'],
                'currency' => $pricing['currency'],
                'is_india_user' => $isIndia,
                'gst_amount' => $pricing['gst_amount'] ?? 0,
                'gst_rate' => $pricing['gst'],
                'views_allowed' => $plan->views_allowed,
                'views_used' => $subscription->views_used,
                'unlimited_views' => $plan->views_allowed === null,
                'views_text' => $this->getViewsText($plan->views_allowed),
                'coins_included' => $plan->coins_included,
                'coins_included_text' => $plan->coins_included . ' coins included',
                'cost_per_view' => $plan->cost_per_view,
                'cost_per_view_text' => 'Minimum ' . $plan->cost_per_view . ' coins per view',
                'coins_carry_forward' => $plan->coins_carry_forward,
                'coins_carry_forward_text' => $plan->coins_carry_forward ? 'Coins carry forward' : 'Coins do not carry forward',
                'access_delay_hours' => $plan->access_delay_hours,
                'access_delay_text' => $plan->access_delay_hours > 0 ? $plan->access_delay_hours . '-2 hour delay' : 'Instant access',
                'validity_days' => $plan->validity_days,
                'validity_text' => $this->getValidityText($plan->validity_days),
                'activated_at' => $subscription->activated_at->format('Y-m-d H:i:s'),
                'expires_at' => $subscription->expires_at->format('Y-m-d H:i:s'),
                'status' => $subscription->status,
            ];
        })->toArray();
    }

    /**
     * Automatically expire subscriptions that have passed validity
     */
    public function expireSubscriptions()
    {
        $expired = UserSubscription::where('status', 'active')
            ->where('expires_at', '<=', now())
            ->update([
                'status' => 'expired',
            ]);

        return $expired;
    }



    /**
     * Get validity text for display
     */
    private function getValidityText(int $days): string
    {
        if ($days === 30) {
            return '1 Month';
        } elseif ($days === 365) {
            return '1 Year';
        } elseif ($days === 7) {
            return '1 Week';
        }
        return "{$days} Days";
    }

    /**
     * Get views text for display
     */
    private function getViewsText(?int $views): string
    {
        if ($views === null) {
            return 'Unlimited';
        }
        return "{$views} Views";
    }

    /**
     * Check if user is from India based on country_iso
     */
    private function isIndiaUser(User $user): bool
    {
        return strtoupper($user->country_iso ?? '') === 'IN';
    }

    /**
     * Get pricing for a plan based on user location
     */
    private function getPlanPricingForUser(SubscriptionPlan $plan, bool $isIndia)
    {
        if ($isIndia) {
            // India pricing: INR with 18% GST
            $basePrice = (float) $plan->price;
            $gstAmount = $basePrice * 0.18;
            $totalPrice = $basePrice + $gstAmount;

            return [
                'price' => $totalPrice,
                'display_price' => number_format($totalPrice, 2),
                'currency' => 'INR',
                'gst' => 18,
                'gst_amount' => $gstAmount,
                'base_price' => $basePrice,
            ];
        } else {
            // Foreign pricing: USD (fixed rates)
            $usdPrice = $this->getUSDPriceForPlan($plan);

            return [
                'price' => $usdPrice,
                'display_price' => number_format($usdPrice, 2),
                'currency' => 'USD',
                'gst' => 0,
                'gst_amount' => 0,
                'base_price' => $usdPrice,
            ];
        }
    }

    /**
     * Get USD price based on plan ID
     */
    private function getUSDPriceForPlan(SubscriptionPlan $plan): float
    {
        // Plan 1: Premium (₹399) = $60
        // Plan 2: Basic (₹100) = $15
        if ($plan->id === 1) {
            return 60.00;
        } elseif ($plan->id === 2) {
            return 15.00;
        }

        // Default conversion for other plans: ₹1 = $0.12
        return round($plan->price * 0.12, 2);
    }

    /**
     * Format subscription response with localized pricing
     */
    private function formatSubscriptionResponse(UserSubscription $subscription, User $user = null)
    {
        $isIndia = $user ? $this->isIndiaUser($user) : (strtoupper($subscription->user->country_iso ?? '') === 'IN');
        $pricing = $this->getPlanPricingForUser($subscription->plan, $isIndia);

        return [
            'id' => $subscription->id,
            'plan_id' => $subscription->subscription_plan_id,
            'plan_name' => $subscription->plan->name,
            'views_allowed' => $subscription->plan->views_allowed,
            'views_used' => $subscription->views_used,
            'remaining_views' => $subscription->getRemainingViews(),
            'unlimited_views' => $subscription->plan->views_allowed === null,
            'views_text' => $this->getViewsText($subscription->plan->views_allowed),
            'coins_included' => $subscription->plan->coins_included,
            'coins_included_text' => $subscription->plan->coins_included . ' coins included',
            'cost_per_view' => $subscription->plan->cost_per_view,
            'cost_per_view_text' => 'Minimum ' . $subscription->plan->cost_per_view . ' coins per view',
            'coins_carry_forward' => $subscription->plan->coins_carry_forward,
            'coins_carry_forward_text' => $subscription->plan->coins_carry_forward ? 'Coins carry forward' : 'Coins do not carry forward',
            'access_delay_hours' => $subscription->plan->access_delay_hours,
            'access_delay_text' => $subscription->plan->access_delay_hours > 0 ? $subscription->plan->access_delay_hours . '-2 hour delay' : 'Instant access',
            'activated_at' => $subscription->activated_at->format('Y-m-d H:i:s'),
            'expires_at' => $subscription->expires_at->format('Y-m-d H:i:s'),
            'remaining_days' => $subscription->getRemainingDays(),
            'status' => $subscription->status,
            'price' => $pricing['price'],
            'base_price' => $pricing['base_price'],
            'display_price' => $pricing['display_price'],
            'currency' => $pricing['currency'],
            'gst_amount' => $pricing['gst_amount'] ?? 0,
            'gst_rate' => $pricing['gst'],
            'validity_days' => $subscription->plan->validity_days,
            'validity_text' => $this->getValidityText($subscription->plan->validity_days),
        ];
    }

    /**
     * Handle failed subscription payment
     */
    public function handlePaymentFailed(
        User $user,
        Order $order,
        string $errorMessage = 'Payment processing failed',
        string $errorReason = 'unknown'
    )
    {
        try {
            DB::beginTransaction();

            // Update order status to failed
            $this->paymentService->updateOrderPaymentStatus(
                $order,
                PaymentStatus::FAILED,
                [
                    'failure_reason' => $errorMessage,
                    'error_reason' => $errorReason,
                    'failed_at' => now()->toDateTimeString(),
                ]
            );

            // Update payment transaction status
            $transaction = $order->paymentTransactions()->first();
            if ($transaction) {
                $this->paymentService->updateTransactionPaymentStatus(
                    $transaction,
                    PaymentStatus::FAILED,
                    [
                        'failure_reason' => $errorMessage,
                        'error_reason' => $errorReason,
                        'failed_at' => now()->toDateTimeString(),
                    ]
                );
            }

            // Also update new SubscriptionOrder and SubscriptionTransaction if they exist
            $subscriptionOrder = SubscriptionOrder::where('order_id', $order->id)->first();
            if ($subscriptionOrder) {
                $subscriptionOrder->update([
                    'status' => 'FAILED',
                    'meta' => array_merge($subscriptionOrder->meta ?? [], [
                        'failure_reason' => $errorMessage,
                        'error_reason' => $errorReason,
                        'failed_at' => now()->toDateTimeString(),
                        'retryable' => true,
                    ]),
                ]);

                // Update associated subscription transactions
                SubscriptionTransaction::where('subscription_order_id', $subscriptionOrder->id)
                    ->update([
                        'status' => 'FAILED',
                        'meta' => DB::raw("JSON_SET(COALESCE(meta, '{}'), '$.failure_reason', '" . addslashes($errorMessage) . "')")
                    ]);
            }

            DB::commit();

            // Send failure notification via job
            SendSubscriptionFailureNotification::dispatch(
                $user,
                $order,
                $errorMessage,
                $errorReason
            );

            Log::info('Subscription payment marked as failed', [
                'user_id' => $user->id,
                'order_id' => $order->id,
                'subscription_order_id' => $subscriptionOrder->id ?? null,
                'error_message' => $errorMessage,
                'error_reason' => $errorReason,
            ]);

            return [
                'success' => false,
                'message' => 'Payment failed: ' . $errorMessage,
                'status' => 'failed',
                'order_id' => $order->id,
                'subscription_order_id' => $subscriptionOrder->id ?? null,
                'can_retry' => true,
                'error_reason' => $errorReason,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error handling payment failure', [
                'user_id' => $user->id,
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Handle pending subscription payment
     */
    public function handlePaymentPending(
        User $user,
        Order $order
    )
    {
        try {
            DB::beginTransaction();

            // Keep order in pending status (handled by PaymentService)
            $transaction = $order->paymentTransactions()->first();
            if ($transaction && $transaction->status !== 'PENDING') {
                $this->paymentService->updateTransactionPaymentStatus(
                    $transaction,
                    PaymentStatus::PENDING,
                    [
                        'pending_at' => now()->toDateTimeString(),
                    ]
                );
            }

            // Also update new SubscriptionOrder and SubscriptionTransaction if they exist
            $subscriptionOrder = SubscriptionOrder::where('order_id', $order->id)->first();
            if ($subscriptionOrder && $subscriptionOrder->status !== 'PENDING') {
                $subscriptionOrder->update([
                    'status' => 'PENDING',
                    'meta' => array_merge($subscriptionOrder->meta ?? [], [
                        'pending_at' => now()->toDateTimeString(),
                    ]),
                ]);

                // Update associated subscription transactions
                SubscriptionTransaction::where('subscription_order_id', $subscriptionOrder->id)
                    ->where('status', '!=', 'PENDING')
                    ->update(['status' => 'PENDING']);
            }

            DB::commit();

            // Send pending notification via job
            SendSubscriptionPendingNotification::dispatch($user, $order, $transaction ?? null);

            Log::info('Subscription payment marked as pending', [
                'user_id' => $user->id,
                'order_id' => $order->id,
                'subscription_order_id' => $subscriptionOrder->id ?? null,
            ]);

            return [
                'success' => true,
                'message' => 'Payment is being processed',
                'status' => 'pending',
                'order_id' => $order->id,
                'can_check_status' => true,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error handling pending payment', [
                'user_id' => $user->id,
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Mark subscription payment as failed (called from controller/webhook when payment fails)
     */
    public function markPaymentFailed(
        User $user,
        Order $order,
        string $errorMessage = 'Payment processing failed',
        string $errorReason = 'unknown'
    )
    {
        if ($order->type !== 'subscription') {
            throw new \Exception('This order is not a subscription order.');
        }

        // Check if already processed
        if (in_array($order->status, ['completed', 'failed', 'cancelled'])) {
            return [
                'message' => 'Payment already processed',
                'status' => $order->status,
            ];
        }

        return $this->handlePaymentFailed($user, $order, $errorMessage, $errorReason);
    }

    /**
     * Check pending payment status
     */
    public function checkPendingPayment(User $user, Order $order)
    {
        if ($order->type !== 'subscription') {
            throw new \Exception('This order is not a subscription order.');
        }

        // If already completed, return success
        if ($order->status === 'completed') {
            $subscription = UserSubscription::where('order_id', $order->id)->first();
            return [
                'success' => true,
                'status' => 'completed',
                'message' => 'Payment completed',
                'subscription' => $subscription ? $this->formatSubscriptionResponse($subscription) : null,
            ];
        }

        // If failed, allow retry
        if ($order->status === 'failed') {
            return [
                'success' => false,
                'status' => 'failed',
                'message' => 'Payment failed. Please retry.',
                'can_retry' => true,
            ];
        }

        // If still pending
        $createdAt = $order->created_at;
        $minutesSinceCreation = now()->diffInMinutes($createdAt);

        if ($minutesSinceCreation >= 15) {
            // After 15 minutes, payment should have been processed
            // This might indicate an issue
            return [
                'success' => true,
                'status' => 'pending',
                'message' => 'Payment still pending. Please contact support if this persists.',
                'can_check_status' => true,
            ];
        }

        return [
            'success' => true,
            'status' => 'pending',
            'message' => 'Payment is being processed. Please wait.',
            'can_check_status' => true,
        ];
    }
}
