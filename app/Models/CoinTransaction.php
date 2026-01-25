<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoinTransaction extends Model
{
    protected $fillable = [
        'user_id',
        'added_by_admin_id',
        'type',
        'amount',
        'balance_after',
        'description',
        'payment_id',
        'order_id',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
        'created_at' => 'datetime',
    ];

    protected $appends = ['encrypted_id'];

    /**
     * Get encrypted ID for display
     */
    public function getEncryptedIdAttribute(): string
    {
        return 'TXN' . strtoupper(substr(md5($this->id . config('app.key')), 0, 8));
    }

    /**
     * Get the user that owns the transaction
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the admin who added this transaction (if manually added)
     */
    public function addedByAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by_admin_id');
    }
}
