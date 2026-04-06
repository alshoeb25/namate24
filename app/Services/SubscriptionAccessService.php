<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserSubscription;
use Carbon\Carbon;

class SubscriptionAccessService
{
    /**
     * Check if user can access a requirement based on subscription tier access delay
     * 
     * @param User $user
     * @param Carbon $requirementCreatedAt
     * @return array ['can_access' => bool, 'delay_hours' => int, 'available_at' => Carbon|null, 'message' => string]
     */
    public function canAccessRequirement(User $user, Carbon $requirementCreatedAt): array
    {
        $subscription = $user->activeSubscription();
        
        // Case 1: Active PRO subscription - can access immediately (0 hour delay)
        if ($subscription && $subscription->isPROPlan()) {
            return [
                'can_access' => true,
                'delay_hours' => 0,
                'available_at' => null,
                'message' => 'Immediate access with Pro Plan',
            ];
        }

        // Case 2: Active BASIC subscription - apply 2-hour access delay
        if ($subscription && $subscription->isBASICPlan()) {
            $delayHours = $subscription->plan?->getAccessDelayHours() ?? 2;
            $availableAt = $requirementCreatedAt->addHours($delayHours);
            $canAccess = now()->greaterThanOrEqualTo($availableAt);

            return [
                'can_access' => $canAccess,
                'delay_hours' => $delayHours,
                'available_at' => $availableAt,
                'message' => $canAccess 
                    ? "Requirement is now accessible with Basic Plan"
                    : "Requirement will be available in " . $availableAt->diffInHours(now(), false) . " hours",
            ];
        }

        // Case 3: Lapsed PRO subscription - apply 2-hour delay but retain coins
        // PRO subscribers who let subscription lapse are still valuable
        $lapsedSubscription = $user->getMostRecentSubscription();
        if ($lapsedSubscription && $lapsedSubscription->hasExpired() && $lapsedSubscription->isPROPlan() && !$lapsedSubscription->isInGracePeriod()) {
            // Lapsed PRO, not in grace period - apply 2-hour delay
            $delayHours = 2;
            $availableAt = $requirementCreatedAt->addHours($delayHours);
            $canAccess = now()->greaterThanOrEqualTo($availableAt);

            return [
                'can_access' => $canAccess,
                'delay_hours' => $delayHours,
                'available_at' => $availableAt,
                'message' => 'Lapsed Pro subscriber (2-hour delay applies. Re-subscribe for immediate access)',
            ];
        }
        
        // Case 4: No subscription or expired basic - can access immediately (but pays coins)
        return [
            'can_access' => true,
            'delay_hours' => 0,
            'available_at' => null,
            'message' => 'Can access immediately (no active subscription)',
        ];
    }

    /**
     * Get earliest time when requirement becomes accessible for user
     * Returns null if immediately accessible
     */
    public function getAccessibleAt(User $user, Carbon $requirementCreatedAt): ?Carbon
    {
        $subscription = $user->activeSubscription();
        
        // Active PRO = immediate access
        if ($subscription && $subscription->isPROPlan()) {
            return null;
        }

        // Active BASIC = delayed access
        if ($subscription && $subscription->isBASICPlan()) {
            $delayHours = $subscription->plan?->getAccessDelayHours() ?? 2;
            return $requirementCreatedAt->addHours($delayHours);
        }

        // Lapsed PRO = delayed access (2 hours)
        $lapsedSubscription = $user->getMostRecentSubscription();
        if ($lapsedSubscription && $lapsedSubscription->hasExpired() && $lapsedSubscription->isPROPlan() && !$lapsedSubscription->isInGracePeriod()) {
            return $requirementCreatedAt->addHours(2);
        }

        // No subscription or basic expired = immediate access
        return null;
    }

    /**
     * Filter requirements based on subscription access delay
     * Returns only requirements that are currently accessible
     */
    public function filterAccessibleRequirements($requirements, User $user)
    {
        return $requirements->filter(function ($requirement) use ($user) {
            $result = $this->canAccessRequirement($user, $requirement->created_at);
            return $result['can_access'];
        });
    }

    /**
     * Add access information to requirement data
     */
    public function enrichRequirementWithAccessInfo(array $requirementData, User $user, $requirement): array
    {
        $accessInfo = $this->canAccessRequirement($user, $requirement->created_at);
        
        return array_merge($requirementData, [
            'access' => [
                'can_access' => $accessInfo['can_access'],
                'delay_hours' => $accessInfo['delay_hours'],
                'available_at' => $accessInfo['available_at']?->toIso8601String(),
                'message' => $accessInfo['message'],
            ]
        ]);
    }
}
