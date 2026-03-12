<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'subscription_plan_id',
        'order_id',
        'razorpay_order_id',
        'razorpay_payment_id',
        'razorpay_signature',
        'amount',
        'currency',
        'status',
        'payment_method',
        'receipt',
        'razorpay_response',
        'meta',
        'paid_at',
    ];

    protected $casts = [
        'razorpay_response' => 'array',
        'meta' => 'array',
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user that owns this order
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the subscription plan
     */
    public function subscriptionPlan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class);
    }

    /**
     * Get the order if linked
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get subscription transactions for this order
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(SubscriptionTransaction::class);
    }

    /**
     * Check if order is paid
     */
    public function isPaid(): bool
    {
        return in_array($this->status, ['paid', 'completed', 'SUCCESS']);
    }

    /**
     * Check if order is completed
     */
    public function isCompleted(): bool
    {
        return $this->isPaid();
    }

    /**
     * Check if order is pending
     */
    public function isPending(): bool
    {
        return in_array($this->status, ['pending', 'initiated', 'INITIATED', 'PENDING']);
    }

    /**
     * Check if order failed
     */
    public function isFailed(): bool
    {
        return in_array($this->status, ['failed', 'FAILED', 'cancelled']);
    }

    /**
     * Scope: Get paid orders
     */
    public function scopePaid($query)
    {
        return $query->whereIn('status', ['paid', 'completed', 'SUCCESS']);
    }

    /**
     * Scope: Get pending orders
     */
    public function scopePending($query)
    {
        return $query->whereIn('status', ['pending', 'initiated', 'INITIATED', 'PENDING']);
    }

    /**
     * Scope: Get failed orders
     */
    public function scopeFailed($query)
    {
        return $query->whereIn('status', ['failed', 'FAILED', 'cancelled']);
    }

    /**
     * Scope: Get orders by user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope: Get recent orders
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days))
            ->orderBy('created_at', 'desc');
    }

    /**
     * Get the total amount for this order
     */
    public function getTotal(): float
    {
        return (float) $this->amount;
    }

    /**
     * Get the currency symbol
     */
    public function getCurrencySymbol(): string
    {
        return $this->currency === 'INR' ? '₹' : '$';
    }
}
