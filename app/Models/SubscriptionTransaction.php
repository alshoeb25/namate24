<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'order_id',
        'subscription_order_id',
        'subscription_plan_id',
        'razorpay_order_id',
        'razorpay_payment_id',
        'status',
        'type',
        'amount',
        'currency',
        'description',
        'payment_method',
        'meta',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'meta' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user that owns this transaction
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the subscription order for this transaction
     */
    public function subscriptionOrder(): BelongsTo
    {
        return $this->belongsTo(SubscriptionOrder::class);
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
     * Check if transaction is successful
     */
    public function isSuccess(): bool
    {
        return in_array($this->status, ['SUCCESS', 'completed', 'paid']);
    }

    /**
     * Check if transaction is pending
     */
    public function isPending(): bool
    {
        return in_array($this->status, ['INITIATED', 'PENDING', 'processing']);
    }

    /**
     * Check if transaction failed
     */
    public function isFailed(): bool
    {
        return in_array($this->status, ['FAILED', 'cancelled', 'failed']);
    }

    /**
     * Scope: Get successful transactions
     */
    public function scopeSuccessful($query)
    {
        return $query->whereIn('status', ['SUCCESS', 'completed', 'paid']);
    }

    /**
     * Scope: Get pending transactions
     */
    public function scopePending($query)
    {
        return $query->whereIn('status', ['INITIATED', 'PENDING', 'processing']);
    }

    /**
     * Scope: Get failed transactions
     */
    public function scopeFailed($query)
    {
        return $query->whereIn('status', ['FAILED', 'cancelled', 'failed']);
    }

    /**
     * Scope: Get transactions by user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope: Get transactions by type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope: Get recent transactions
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days))
            ->orderBy('created_at', 'desc');
    }
}
