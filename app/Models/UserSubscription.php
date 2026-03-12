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
        'views_used',
        'status',
        'cancellation_reason',
        'cancelled_at',
    ];

    protected $casts = [
        'activated_at' => 'datetime',
        'expires_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'views_used' => 'integer',
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
}
