<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'order_id',
        'razorpay_order_id',
        'razorpay_payment_id',
        'razorpay_signature',
        'amount',
        'currency',
        'type',
        'payment_method',
        'package_id',
        'coins',
        'bonus_coins',
        'status',
        'receipt',
        'razorpay_response',
        'meta',
        'metadata',
        'paid_at',
    ];

    protected $casts = [
        'razorpay_response' => 'array',
        'meta' => 'array',
        'metadata' => 'array',
        'paid_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'amount' => 'decimal:2',
    ];

    /**
     * Get the user that owns the order
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the coin package for this order
     */
    public function package(): BelongsTo
    {
        return $this->belongsTo(CoinPackage::class);
    }

    /**
     * Get payment transactions for this order
     */
    public function paymentTransactions(): HasMany
    {
        return $this->hasMany(PaymentTransaction::class);
    }

    /**
     * Check if order is paid
     */
    public function isPaid(): bool
    {
        try {
            return PaymentStatus::from($this->status)->isSuccess();
        } catch (\ValueError) {
            return $this->status === 'paid' || $this->status === 'completed';
        }
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
        try {
            return PaymentStatus::from($this->status)->isPending();
        } catch (\ValueError) {
            return in_array($this->status, ['pending', 'initiated', 'processing']);
        }
    }

    /**
     * Check if order failed
     */
    public function isFailed(): bool
    {
        try {
            return PaymentStatus::from($this->status)->isFailure();
        } catch (\ValueError) {
            return in_array($this->status, ['failed', 'cancelled']);
        }
    }
}
