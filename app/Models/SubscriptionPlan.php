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
        'coins_included',
        'has_priority_support',
        'has_backend_team_support',
        'has_ebook_content',
        'access_delay_hours',
        'cost_per_view',
        'coins_carry_forward',
        'lapse_grace_period_hours',
        'description',
        'is_active',
        'display_order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'validity_days' => 'integer',
        'views_allowed' => 'integer',
        'coins_included' => 'integer',
        'has_priority_support' => 'boolean',
        'has_backend_team_support' => 'boolean',
        'has_ebook_content' => 'boolean',
        'access_delay_hours' => 'integer',
        'cost_per_view' => 'integer',
        'coins_carry_forward' => 'boolean',
        'lapse_grace_period_hours' => 'integer',
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
     * Get plan type (PRO, BASIC, etc)
     */
    public function getPlanType(): string
    {
        $planName = strtoupper($this->name);
        if (strpos($planName, 'PRO') === 0) {
            return 'PRO';
        }
        if (strpos($planName, 'BASIC') === 0) {
            return 'BASIC';
        }
        if (strpos($planName, 'PREMIUM') === 0) {
            return 'PRO'; // Treat Premium as PRO
        }
        return 'OTHER';
    }

    /**
     * Check if this is a PRO plan
     */
    public function isPROPlan(): bool
    {
        return $this->getPlanType() === 'PRO';
    }

    /**
     * Check if this is a BASIC plan
     */
    public function isBASICPlan(): bool
    {
        return $this->getPlanType() === 'BASIC';
    }

    /**
     * Get requirement access delay in hours
     */
    public function getAccessDelayHours(): int
    {
        return $this->access_delay_hours ?? 0;
    }

    /**
     * Check if has priority support
     */
    public function hasPrioritySupport(): bool
    {
        return $this->has_priority_support ?? false;
    }

    /**
     * Check if has eBook content access
     */
    public function hasEbookContent(): bool
    {
        return $this->has_ebook_content ?? false;
    }

    /**
     * Check if coins carry forward on subscription lapse
     */
    public function carriesForwardCoins(): bool
    {
        return $this->coins_carry_forward ?? false;
    }

    /**
     * Get grace period in hours after subscription expires
     */
    public function getLapseGracePeriodHours(): int
    {
        return $this->lapse_grace_period_hours ?? 2;
    }

    /**
     * Relationship: user subscriptions
     */
    public function userSubscriptions()
    {
        return $this->hasMany(UserSubscription::class);
    }
}
