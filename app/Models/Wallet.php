<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Wallet extends Model
{
    protected $fillable = ['user_id','balance'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function credit_purchases(): HasMany
    {
        return $this->hasMany(CreditPurchase::class);
    }

    public function credit_transactions(): HasMany
    {
        return $this->hasMany(CreditTransaction::class);
    }
}