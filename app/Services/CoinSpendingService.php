<?php

namespace App\Services;

use App\Models\User;
use App\Models\CoinTransaction;
use App\Models\SubscriptionViewLog;
use Illuminate\Support\Facades\DB;

class CoinSpendingService
{
    /**
     * Check if user can perform an action and deduct coins if applicable
     * 
     * @param User $user
     * @param string $actionType - 'requirement_view', 'tutor_unlock_contact', 'profile_unlock'
     * @param array $meta - Additional metadata (requirement_id, tutor_id, etc)
     * @return array - ['success' => bool, 'message' => string, 'data' => array, 'status_code' => int]
     */
    public function checkAndDeductCoins(User $user, string $actionType, array $meta = []): array
    {
        // Check if user has active subscription
        $activeSubscription = $user->activeSubscription();
        
        // Check for lapsed PRO subscription (coins retained after lapse)
        $lapsedSubscription = null;
        if (!$activeSubscription) {
            $mostRecentSubscription = $user->getMostRecentSubscription();
            if ($mostRecentSubscription && $mostRecentSubscription->hasExpired() && $mostRecentSubscription->isPROPlan()) {
                $lapsedSubscription = $mostRecentSubscription;
            }
        }

        $isIndia = CoinPricingService::isIndiaUser($user);
        $currencySymbol = $isIndia ? '₹' : '$';

        // ===== CASE 1: Has active subscription with views available =====
        if ($activeSubscription && $activeSubscription->canView()) {
            return $this->handleSubscriptionWithViews($user, $activeSubscription, $actionType, $meta);
        }

        // ===== CASE 2: Has subscription but views exhausted =====
        if ($activeSubscription && !$activeSubscription->canView()) {
            return $this->handleSubscriptionViewsExhausted($user, $activeSubscription, $actionType);
        }

        // ===== CASE 3: Lapsed PRO subscription =====
        if ($lapsedSubscription) {
            return $this->handleLapsedSubscription($user, $lapsedSubscription, $actionType, $meta);
        }

        // ===== CASE 4: No subscription - require coins =====
        return $this->handleNoSubscription($user, $actionType, $meta);
    }

    /**
     * Case 1: User has active subscription with available views
     */
    private function handleSubscriptionWithViews(User $user, $activeSubscription, string $actionType, array $meta): array
    {
        $viewCost = $activeSubscription->plan->cost_per_view ?? 39;
        $isPROTier = $activeSubscription->plan && $activeSubscription->plan->tier === 'PRO';
        $isBASICTier = $activeSubscription->plan && $activeSubscription->plan->tier === 'BASIC';

        // For BASIC plan, check coin spending limits
        if ($isBASICTier) {
            $coinsRemaining = $activeSubscription->getRemainingCoinsForBASIC();
            $viewsRemaining = $activeSubscription->getRemainingViewsWithCoinsForBASIC();

            if ($viewCost > $coinsRemaining || $viewsRemaining <= 0) {
                // BASIC plan limits reached
                $isIndia = CoinPricingService::isIndiaUser($user);
                $currencySymbol = $isIndia ? '₹' : '$';

                return [
                    'success' => false,
                    'message' => 'You have reached your coin spending limit for this subscription. Upgrade to PRO or buy more coins.',
                    'coins_with_limit' => [
                        'coins_spent' => $activeSubscription->coins_spent,
                        'coins_limit' => 49,
                        'coins_remaining' => $coinsRemaining,
                        'views_with_coins' => $activeSubscription->views_with_coins,
                        'views_with_coins_limit' => 2,
                        'views_remaining_for_coins' => $viewsRemaining,
                    ],
                    'options' => [
                        'upgrade_subscription' => [
                            'action' => 'upgrade_subscription',
                            'label' => 'Upgrade to PRO',
                            'description' => 'Get unlimited coins and views',
                        ],
                        'buy_coins' => [
                            'action' => 'buy_coins',
                            'label' => 'Buy More Coins',
                            'minimum_coins' => 99,
                            'cost' => $currencySymbol . '99',
                            'description' => 'Buy coins to continue',
                        ]
                    ],
                    'status_code' => 402,
                ];
            }
        }

        // Deduct coins from user
        DB::transaction(function () use ($user, $viewCost, $activeSubscription, $actionType, $meta) {
            $user->decrement('coins', $viewCost);

            // For BASIC plan, track coin spending
            if ($activeSubscription->plan && $activeSubscription->plan->tier === 'BASIC') {
                $activeSubscription->addCoinsSpent($viewCost);
                $activeSubscription->incrementViewWithCoins();
            }

            // Log the view
            SubscriptionViewLog::create([
                'user_id' => $user->id,
                'user_subscription_id' => $activeSubscription->id,
                'viewable_id' => $meta['viewable_id'] ?? null,
                'viewable_type' => $meta['viewable_type'] ?? $actionType,
                'action_type' => $this->getActionType($actionType),
                'viewed_at' => now(),
            ]);

            // Increment view count on subscription
            $activeSubscription->incrementViewCount();

            // Record coin transaction
            $meta['coin_value'] = $viewCost;
            CoinTransaction::create([
                'user_id' => $user->id,
                'type' => $actionType,
                'amount' => -$viewCost,
                'balance_after' => $user->fresh()->coins,
                'description' => $this->getTransactionDescription($actionType, $meta),
                'meta' => json_encode($meta),
            ]);
        });

        return [
            'success' => true,
            'message' => 'Access granted',
            'coins_deducted' => $viewCost,
            'balance_after' => $user->fresh()->coins,
            'status_code' => 200,
        ];
    }

    /**
     * Case 2: User has subscription but views exhausted
     */
    private function handleSubscriptionViewsExhausted(User $user, $activeSubscription, string $actionType): array
    {
        $isIndia = CoinPricingService::isIndiaUser($user);
        $currencySymbol = $isIndia ? '₹' : '$';
        $isPROTier = $activeSubscription->plan && $activeSubscription->plan->tier === 'PRO';

        if ($isPROTier) {
            return [
                'success' => false,
                'message' => "You've used all ({$activeSubscription->views_used}/{$activeSubscription->plan->views_allowed}) subscription views. Renew PRO or buy coins to continue.",
                'views_exhausted' => true,
                'views_used' => $activeSubscription->views_used,
                'views_allowed' => $activeSubscription->plan->views_allowed,
                'subscription_info' => [
                    'plan_name' => $activeSubscription->plan->name ?? null,
                    'tier' => 'PRO',
                    'expires_at' => $activeSubscription->expires_at,
                ],
                'options' => [
                    'renew_subscription' => [
                        'action' => 'renew_subscription',
                        'label' => 'Renew PRO Subscription',
                        'description' => 'Get 399 fresh coins and ~10-12 views',
                    ],
                    'buy_coins' => [
                        'action' => 'buy_coins',
                        'label' => 'Buy Coins',
                        'minimum_coins' => 99,
                        'cost' => $currencySymbol . '99',
                        'description' => 'Buy coins to continue viewing',
                    ]
                ],
                'status_code' => 403,
            ];
        } else {
            // BASIC tier
            return [
                'success' => false,
                'message' => "You've used all ({$activeSubscription->views_used}/{$activeSubscription->plan->views_allowed}) subscription views. Upgrade to PRO or buy coins to continue.",
                'views_exhausted' => true,
                'views_used' => $activeSubscription->views_used,
                'views_allowed' => $activeSubscription->plan->views_allowed,
                'subscription_info' => [
                    'plan_name' => $activeSubscription->plan->name ?? null,
                    'tier' => 'BASIC',
                    'expires_at' => $activeSubscription->expires_at,
                ],
                'options' => [
                    'upgrade_subscription' => [
                        'action' => 'upgrade_subscription',
                        'label' => 'Upgrade to PRO',
                        'description' => 'Get unlimited views and real-time access',
                    ],
                    'buy_coins' => [
                        'action' => 'buy_coins',
                        'label' => 'Buy Coins',
                        'minimum_coins' => 99,
                        'cost' => $currencySymbol . '99',
                        'description' => 'Buy coins to continue viewing',
                    ]
                ],
                'status_code' => 403,
            ];
        }
    }

    /**
     * Case 3: Lapsed PRO subscription - can still use coins with 2-hour delay
     */
    private function handleLapsedSubscription(User $user, $lapsedSubscription, string $actionType, array $meta): array
    {
        $requiredCoins = 39; // PRO plan cost for lapsed users

        if ($user->coins < $requiredCoins) {
            $isIndia = CoinPricingService::isIndiaUser($user);
            $currencySymbol = $isIndia ? '₹' : '$';
            $coinsNeeded = $requiredCoins - $user->coins;

            return [
                'success' => false,
                'message' => 'Insufficient coins. Your Pro subscription has expired, and you need ' . $requiredCoins . ' coins to continue.',
                'required' => $requiredCoins,
                'current_balance' => $user->coins,
                'coins_needed' => $coinsNeeded,
                'subscription_expired' => true,
                'options' => [
                    'renew_subscription' => [
                        'action' => 'renew_subscription',
                        'label' => 'Re-Subscribe to PRO',
                        'description' => 'Regain immediate access and get 399 fresh coins',
                    ],
                    'buy_coins' => [
                        'action' => 'buy_coins',
                        'label' => 'Buy Coins',
                        'minimum_coins' => $coinsNeeded,
                        'cost' => $currencySymbol . $coinsNeeded,
                        'description' => 'Buy ' . $coinsNeeded . ' coins to continue with 2-hour delay',
                    ]
                ],
                'status_code' => 402,
            ];
        }

        // Deduct coins for lapsed PRO subscriber
        DB::transaction(function () use ($user, $requiredCoins, $lapsedSubscription, $actionType, $meta) {
            $user->decrement('coins', $requiredCoins);

            // Log the view as lapsed subscriber
            SubscriptionViewLog::create([
                'user_id' => $user->id,
                'user_subscription_id' => $lapsedSubscription->id,
                'viewable_id' => $meta['viewable_id'] ?? null,
                'viewable_type' => $meta['viewable_type'] ?? $actionType,
                'action_type' => $this->getActionType($actionType, '_lapsed'),
                'viewed_at' => now(),
            ]);

            // Record coin transaction
            $description = $this->getTransactionDescription($actionType, $meta) . ' (Lapsed Pro subscription - 2-hour delay applied)';
            $meta['reason'] = 'lapsed_pro_subscription';

            CoinTransaction::create([
                'user_id' => $user->id,
                'type' => $actionType,
                'amount' => -$requiredCoins,
                'balance_after' => $user->fresh()->coins,
                'description' => $description,
                'meta' => json_encode($meta),
            ]);
        });

        return [
            'success' => true,
            'message' => 'Access granted with 2-hour delay (lapsed subscription)',
            'coins_deducted' => $requiredCoins,
            'balance_after' => $user->fresh()->coins,
            'delay_hours' => 2,
            'status_code' => 200,
        ];
    }

    /**
     * Case 4: No subscription - require coins
     */
    private function handleNoSubscription(User $user, string $actionType, array $meta = []): array
    {
        $requiredCoins = $this->getCoinCostForActionType($user, $actionType);
        $isIndia = CoinPricingService::isIndiaUser($user);
        $currencySymbol = $isIndia ? '₹' : '$';

        if ($user->coins < $requiredCoins) {
            $coinsNeeded = $requiredCoins - $user->coins;

            return [
                'success' => false,
                'message' => 'Insufficient coins. You need ' . $requiredCoins . ' coins to ' . $this->getActionDescription($actionType) . '.',
                'required' => $requiredCoins,
                'current_balance' => $user->coins,
                'coins_needed' => $coinsNeeded,
                'options' => [
                    'subscribe' => [
                        'action' => 'subscribe',
                        'label' => 'Subscribe to PRO',
                        'description' => 'Get unlimited access and 399 coins',
                    ],
                    'buy_coins' => [
                        'action' => 'buy_coins',
                        'label' => 'Buy Coins',
                        'minimum_coins' => $coinsNeeded,
                        'cost' => $currencySymbol . $coinsNeeded,
                        'description' => 'Buy ' . $coinsNeeded . ' coins to ' . $this->getActionDescription($actionType),
                    ]
                ],
                'status_code' => 402,
            ];
        }

        // Deduct coins
        DB::transaction(function () use ($user, $requiredCoins, $actionType, $meta) {
            $user->decrement('coins', $requiredCoins);

            // Record transaction
            $meta['coin_value'] = $requiredCoins;
            CoinTransaction::create([
                'user_id' => $user->id,
                'type' => $actionType,
                'amount' => -$requiredCoins,
                'balance_after' => $user->fresh()->coins,
                'description' => 'Coins spent for ' . $this->getActionDescription($actionType),
                'meta' => json_encode($meta),
            ]);
        });

        return [
            'success' => true,
            'message' => 'Access granted',
            'coins_deducted' => $requiredCoins,
            'balance_after' => $user->fresh()->coins,
            'status_code' => 200,
        ];
    }

    /**
     * Get coin cost for action type based on user nationality
     */
    private function getCoinCostForActionType(User $user, string $actionType): int
    {
        $isIndia = CoinPricingService::isIndiaUser($user);

        return match ($actionType) {
            'requirement_view' => $isIndia ? 49 : 99,
            'tutor_unlock_contact' => $isIndia ? 49 : 99,
            'profile_unlock' => $isIndia ? 49 : 99,
            default => 99,
        };
    }

    /**
     * Get human-readable action description
     */
    private function getActionDescription(string $actionType): string
    {
        return match ($actionType) {
            'requirement_view' => 'view this requirement',
            'tutor_unlock_contact' => 'unlock tutor contact details',
            'profile_unlock' => 'view tutor profile',
            default => 'perform this action',
        };
    }

    /**
     * Get transaction description
     */
    private function getTransactionDescription(string $actionType, array $meta): string
    {
        return match ($actionType) {
            'requirement_view' => 'Viewed requirement #' . ($meta['requirement_id'] ?? 'N/A'),
            'tutor_unlock_contact' => 'Unlocked contact details for tutor #' . ($meta['tutor_id'] ?? 'N/A'),
            'profile_unlock' => 'Unlocked profile for tutor #' . ($meta['tutor_id'] ?? 'N/A'),
            default => 'Coins spent for ' . $this->getActionDescription($actionType),
        };
    }

    /**
     * Get action type for view logging
     */
    private function getActionType(string $actionType, string $suffix = ''): string
    {
        return match ($actionType) {
            'requirement_view' => 'tutor_requirement_view' . $suffix,
            'tutor_unlock_contact' => 'student_contact_unlock' . $suffix,
            'profile_unlock' => 'student_profile_unlock' . $suffix,
            default => 'generic_action' . $suffix,
        };
    }
}
