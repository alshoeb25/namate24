<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReferralCode extends Model
{
    protected $fillable = [
        'referral_code',
        'type',
        'coins',
        'used',
        'referral_type',
        'expiry',
        'max_count',
    ];

    protected $casts = [
        'used' => 'boolean',
        'coins' => 'integer',
        'expiry' => 'datetime',
        'max_count' => 'integer',
    ];

    /**
     * Get the referral invites associated with this code
     */
    public function referralInvites(): HasMany
    {
        return $this->hasMany(ReferralInvite::class);
    }

    /**
     * Generate a unique referral code
     */
    public static function generateCode(): string
    {
        do {
            $code = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
        } while (self::where('referral_code', $code)->exists());

        return $code;
    }

    /**
     * Scope to get available (unused) codes
     */
    public function scopeAvailable($query)
    {
        return $query->where('used', false);
    }

    /**
     * Mark code as used
     */
    public function markAsUsed(): void
    {
        $this->update(['used' => true]);
    }

    /**
     * Check if the code is still valid (not expired and has redemptions remaining)
     */
    public function isValid(): bool
    {
        // Check if already used
        if ($this->used) {
            return false;
        }

        // Check if expired
        if ($this->expiry && $this->expiry->isPast()) {
            return false;
        }

        // Check if max_count exceeded
        if ($this->max_count) {
            $usageCount = $this->referralInvites()->where('is_used', true)->count();
            if ($usageCount >= $this->max_count) {
                return false;
            }
        }

        return true;
    }
}
