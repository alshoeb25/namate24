<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreditPurchase extends Model
{
    protected $fillable = [
        'wallet_id','credits_total','credits_consumed','amount_paid',
        'payment_gateway_order_id','purchased_at','expires_at','status'
    ];

    protected $casts = ['purchased_at'=>'datetime','expires_at'=>'datetime'];

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }
}