<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'order_id',
        'razorpay_order_id',
        'razorpay_payment_id',
        'status',
        'amount',
        'currency',
        'coins',
        'bonus_coins',
        'type',
        'description',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
        'amount' => 'decimal:2',
        'coins' => 'integer',
        'bonus_coins' => 'integer',
    ];

    /**
     * Get the user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the order
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
        try {
            return PaymentStatus::from($this->status)->isSuccess();
        } catch (\ValueError) {
            return $this->status === 'SUCCESS' || $this->status === 'completed';
        }
    }

    /**
     * Check if transaction is pending
     */
    public function isPending(): bool
    {
        try {
            return PaymentStatus::from($this->status)->isPending();
        } catch (\ValueError) {
            return in_array($this->status, ['INITIATED', 'PENDING', 'PROCESSING']);
        }
    }

    /**
     * Check if transaction failed
     */
    public function isFailed(): bool
    {
        try {
            return PaymentStatus::from($this->status)->isFailure();
        } catch (\ValueError) {
            return in_array($this->status, ['FAILED', 'CANCELLED']);
        }
    }
}
