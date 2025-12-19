<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class CoinPackage extends Model
{
    protected $fillable = [
        'name',
        'coins',
        'price',
        'bonus_coins',
        'description',
        'is_popular',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_popular' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get total coins including bonus
     */
    public function getTotalCoinsAttribute(): int
    {
        return $this->coins + $this->bonus_coins;
    }

    /**
     * Scope for active packages
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for popular packages
     */
    public function scopePopular(Builder $query): Builder
    {
        return $query->where('is_popular', true);
    }
}
