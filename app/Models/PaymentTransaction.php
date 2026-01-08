<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
