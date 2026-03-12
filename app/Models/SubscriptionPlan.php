<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'price',
        'currency',
        'validity_days',
        'views_allowed',
        'description',
        'is_active',
        'display_order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'validity_days' => 'integer',
        'views_allowed' => 'integer',
        'is_active' => 'boolean',
        'display_order' => 'integer',
    ];

    /**
     * Get all active subscriptions ordered by display order
     */
    public static function getActiveSubscriptions()
    {
        return self::where('is_active', true)
            ->orderBy('display_order', 'asc')
            ->get();
    }

    /**
     * Check if plan has limited views
     */
    public function hasLimitedViews(): bool
    {
        return $this->views_allowed !== null;
    }

    /**
     * Get views allowed for this plan
     */
    public function getViewsAllowed(): ?int
    {
        return $this->views_allowed;
    }

    /**
     * Relationship: user subscriptions
     */
    public function userSubscriptions()
    {
        return $this->hasMany(UserSubscription::class);
    }
}
