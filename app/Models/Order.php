<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'razorpay_order_id',
        'razorpay_payment_id',
        'razorpay_signature',
        'amount',
        'currency',
        'package_id',
        'coins',
        'bonus_coins',
        'status',
        'receipt',
        'razorpay_response',
        'meta',
        'paid_at',
    ];

    protected $casts = [
        'razorpay_response' => 'array',
        'meta' => 'array',
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
     * Check if order is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if order is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if order failed
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }
}
