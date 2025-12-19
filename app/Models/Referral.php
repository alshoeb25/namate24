<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Referral extends Model
{
    protected $fillable = [
        'referrer_id',
        'referred_id',
        'referrer_coins',
        'referred_coins',
        'reward_given',
        'reward_given_at',
    ];

    protected $casts = [
        'reward_given' => 'boolean',
        'reward_given_at' => 'datetime',
    ];

    /**
     * Get the user who referred
     */
    public function referrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    /**
     * Get the user who got referred
     */
    public function referred(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_id');
    }
}
