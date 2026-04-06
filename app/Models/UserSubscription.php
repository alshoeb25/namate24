<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class UserSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'subscription_plan_id',
        'order_id',
        'activated_at',
        'expires_at',
        'grace_period_expires_at',
        'views_used',
        'coins_spent',
        'views_with_coins',
        'coins_carried_forward',
        'status',
        'cancellation_reason',
        'cancelled_at',
    ];

    protected $casts = [
        'activated_at' => 'datetime',
        'expires_at' => 'datetime',
        'grace_period_expires_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'views_used' => 'integer',
        'coins_spent' => 'integer',
        'views_with_coins' => 'integer',
        'coins_carried_forward' => 'integer',
    ];

    /**
     * Scope: Get active subscriptions
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where('expires_at', '>', now());
    }

    /**
     * Scope: Get user's current active subscription
     */
    public function scopeForCurrentUser($query)
    {
        return $query->where('user_id', auth()->id())
            ->where('status', 'active')
            ->where('expires_at', '>', now());
    }

    /**
     * Check if subscription is currently active
     */
    public function isActive(): bool
    {
        return $this->status === 'active' && $this->expires_at->isFuture();
    }

    /**
     * Check if subscription has expired
     */
    public function hasExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Get remaining days in subscription
     */
    public function getRemainingDays(): int
    {
        if ($this->hasExpired()) {
            return 0;
        }
        return (int) $this->expires_at->diffInDays(now());
    }

    /**
     * Get remaining views for limited subscriptions
     */
    public function getRemainingViews(): ?int
    {
        if (!$this->plan || $this->plan->hasLimitedViews() === false) {
            return null; // Unlimited views
        }
        $allowed = $this->plan->views_allowed;
        return max(0, $allowed - $this->views_used);
    }

    /**
     * Check if user can view with this subscription
     */
    public function canView(): bool
    {
        if (!$this->isActive()) {
            return false;
        }

        $remaining = $this->getRemainingViews();
        if ($remaining === null) {
            // Unlimited views
            return true;
        }

        return $remaining > 0;
    }

    /**
     * Get the plan type (PRO, BASIC, etc.)
     */
    public function getPlanType(): string
    {
        if (!$this->plan) {
            return 'unknown';
        }
        $planName = strtoupper($this->plan->name);
        if (strpos($planName, 'PRO') === 0) {
            return 'PRO';
        }
        if (strpos($planName, 'BASIC') === 0) {
            return 'BASIC';
        }
        return 'OTHER';
    }

    /**
     * Check if this is a PRO subscription
     */
    public function isPROPlan(): bool
    {
        return $this->getPlanType() === 'PRO';
    }

    /**
     * Check if this is a BASIC subscription
     */
    public function isBASICPlan(): bool
    {
        return $this->getPlanType() === 'BASIC';
    }

    /**
     * Increment view count with validation
     */
    public function incrementViewCount(): bool
    {
        if (!$this->canView()) {
            return false;
        }

        $this->increment('views_used');
        return true;
    }

    /**
     * Check if subscription has expired
     */
    public function isExpired(): bool
    {
        return $this->hasExpired();
    }

    /**
     * Check if user is in grace period (after expiry but within grace period)
     */
    public function isInGracePeriod(): bool
    {
        if (!$this->hasExpired()) {
            return false; // Not expired yet
        }

        if (!$this->grace_period_expires_at) {
            return false; // No grace period configured
        }

        return now()->lessThan($this->grace_period_expires_at);
    }

    /**
     * Calculate when grace period expires
     */
    public function getGracePeriodExpiresAt(): ?Carbon
    {
        if (!$this->hasExpired() || !$this->plan) {
            return null;
        }

        if ($this->grace_period_expires_at) {
            return $this->grace_period_expires_at;
        }

        // Calculate based on plan configuration
        $gracePeriodHours = $this->plan->getLapseGracePeriodHours();
        return $this->expires_at->addHours($gracePeriodHours);
    }

    /**
     * Handle subscription lapse - carry forward coins if applicable
     */
    public function handleLapse(): void
    {
        if (!$this->plan || !$this->plan->carriesForwardCoins()) {
            return; // Plan doesn't support carryforward
        }

        if (!$this->user) {
            return; // No user relationship
        }

        // Get remaining coins in this subscription
        // (This could be tracked via coin transactions if need be)
        // For now, we'll just mark it as lapsed with potential carryforward
        
        $gracePeriodExpires = $this->getGracePeriodExpiresAt();
        
        $this->update([
            'grace_period_expires_at' => $gracePeriodExpires,
            'coins_carried_forward' => $this->user->coins, // Track current wallet balance
        ]);
    }

    /**
     * Calculate base amount to charge (without coins)
     */
    public function getChargeableAmount(): float
    {
        return (float) $this->plan->price;
    }

    /**
     * Relationships
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function plan()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }

    /**
     * Alias for plan relationship
     */
    public function subscriptionPlan()
    {
        return $this->plan();
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function viewLogs()
    {
        return $this->hasMany(SubscriptionViewLog::class);
    }

    /**
     * BASIC Plan Coin Limits:
     * - Maximum 49 coins can be spent (out of 50 included)
     * - Maximum 2 views can use coins
     * - After that, must upgrade or buy more coins
     */

    /**
     * Check if BASIC plan has coins available
     */
    public function hasCoinsAvailable(): bool
    {
        if (!$this->isBASICPlan()) {
            return true; // Non-BASIC plans don't have coin limits
        }

        // Check if reached max coins spent (49)
        if ($this->coins_spent >= 49) {
            return false;
        }

        // Check if reached max views with coins (2)
        if ($this->views_with_coins >= 2) {
            return false;
        }

        return true;
    }

    /**
     * Get remaining coins for BASIC plan
     */
    public function getRemainingCoinsForBASIC(): int
    {
        if (!$this->isBASICPlan()) {
            return 0;
        }

        return max(0, 49 - $this->coins_spent);
    }

    /**
     * Get remaining views with coins for BASIC plan
     */
    public function getRemainingViewsWithCoinsForBASIC(): int
    {
        if (!$this->isBASICPlan()) {
            return 0;
        }

        return max(0, 2 - $this->views_with_coins);
    }

    /**
     * Add coins spent from BASIC plan
     */
    public function addCoinsSpent(int $amount): bool
    {
        if (!$this->isBASICPlan()) {
            return false; // Only BASIC has limits
        }

        if (!$this->hasCoinsAvailable()) {
            return false; // No coins available
        }

        // Check if adding this amount exceeds limit
        if (($this->coins_spent + $amount) > 49) {
            return false; // Would exceed 49 coin limit
        }

        $this->increment('coins_spent', $amount);
        return true;
    }

    /**
     * Increment view count that used coins for BASIC plan
     */
    public function incrementViewWithCoins(): bool
    {
        if (!$this->isBASICPlan()) {
            return false;
        }

        if ($this->views_with_coins >= 2) {
            return false; // Already used max 2 views with coins
        }

        $this->increment('views_with_coins');
        return true;
    }

    /**
     * Get coin spending status for BASIC plan
     */
    public function getCoinsSpendingStatus(): array
    {
        return [
            'is_basic' => $this->isBASICPlan(),
            'coins_spent' => $this->coins_spent,
            'coins_remaining' => $this->getRemainingCoinsForBASIC(),
            'coins_limit' => 49,
            'views_with_coins' => $this->views_with_coins,
            'views_limit' => 2,
            'coins_available' => $this->hasCoinsAvailable(),
        ];
    }
}
