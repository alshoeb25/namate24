<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Coin System Configuration
    |--------------------------------------------------------------------------
    |
    | Configure coin costs and pricing for various platform features
    |
    */

    // Requirement posting fee (after first 3 free posts)
    'requirement_post_fee' => env('REQUIREMENT_POST_FEE', 10),

    // Enquiry unlock cost for tutors
    'enquiry_unlock_cost' => env('ENQUIRY_UNLOCK_COST', 5),

    // Referral rewards
    'referral_bonus' => env('REFERRAL_BONUS', 30), // Coins for referrer (who referred)
    'referral_reward' => env('REFERRAL_REWARD', 15), // Coins for referred user (who got referred)

    // Free requirements count for students
    'free_requirements_count' => env('FREE_REQUIREMENTS_COUNT', 3),

    // Cost for student to approach a teacher
    'approach_teacher_cost' => env('COIN_APPROACH_TEACHER_COST', 10),

    // Cost to unlock tutor contact details
    'contact_unlock' => env('CONTACT_UNLOCK_COINS', 50),

    // Nationality-based pricing for tutor contact unlock / profile unlock
    'pricing_by_nationality' => [
        'contact_unlock' => [
            'indian' => env('CONTACT_UNLOCK_COINS_INDIAN', 49),
            'non_indian' => env('CONTACT_UNLOCK_COINS_NON_INDIAN', 99),
        ],
        'approach_tutor' => [
            'indian' => env('APPROACH_TUTOR_COINS_INDIAN', 49),
            'non_indian' => env('APPROACH_TUTOR_COINS_NON_INDIAN', 99),
        ],
    ],

    // Terms & Conditions enforcement for coin operations
    'terms_and_conditions' => [
        'enforce_acceptance' => env('COINS_ENFORCE_TC_ACCEPTANCE', false), // Set to true to require T&C acceptance in API
        'require_for_operations' => [
            'post_requirement' => env('COINS_REQUIRE_TC_POST', false),
            'unlock_tutor' => env('COINS_REQUIRE_TC_UNLOCK', false),
            'contact_unlock' => env('COINS_REQUIRE_TC_CONTACT', false),
        ],
    ],

    // Subscription Plans Configuration
    'subscription_plans' => [
        [
            'name' => 'Pro',
            'price' => 399.00,
            'currency' => 'INR',
            'validity_days' => 30,
            'views_allowed' => 10, // 10-12 views at 39 coins each
            'coins_included' => 399,
            'has_priority_support' => true,
            'has_ebook_content' => true,
            'access_delay_hours' => 0, // Real-time/immediate access
            'cost_per_view' => 39, // 39 coins per view
            'coins_carry_forward' => true, // Coins carry forward on subscription lapse
            'lapse_grace_period_hours' => 2, // 2 hours grace period after expiry for PRO users
            'description' => 'Pro Plan: Rs. 399/month - 399 coins, ~10-12 views, real-time access, priority support, eBooks & content.',
            'is_active' => true,
            'display_order' => 1,
        ],
        [
            'name' => 'Basic',
            'price' => 99.00,
            'currency' => 'INR',
            'validity_days' => 30,
            'views_allowed' => 2, // Max 2 views
            'coins_included' => 99,
            'has_priority_support' => false,
            'has_ebook_content' => false,
            'access_delay_hours' => 1, // 1-2 hour delay (max 49 coins spendable, max 2 views with coins)
            'cost_per_view' => 49, // 49 coins per view
            'coins_carry_forward' => false, // No carryforward for BASIC
            'lapse_grace_period_hours' => 0, // No grace period for BASIC
            'description' => 'Basic Plan: Rs. 99/month - 99 coins, max 2 views, delayed access (1-2 hours), no priority support.',
            'is_active' => true,
            'display_order' => 2,
        ],
    ],

    // Subscription rules
    'subscription' => [
        'auto_expire_on_validity_end' => true, // Automatically mark as expired
        'reset_views_on_renewal' => true, // Reset view count on renewal
    ],
];
