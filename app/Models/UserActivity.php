<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserActivity extends Model
{
    protected $fillable = [
        'user_id',
        'ip_address',
        'country',
        'country_iso',
        'login_time',
        'logout_time',
        'user_agent',
    ];

    protected $casts = [
        'login_time' => 'datetime',
        'logout_time' => 'datetime',
    ];

    /**
     * Get the user that owns this activity
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to get active sessions (logged in but not logged out)
     */
    public function scopeActive($query)
    {
        return $query->whereNull('logout_time');
    }

    /**
     * Scope to get sessions for a specific user
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to get recent sessions
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('login_time', '>=', now()->subDays($days));
    }
}
