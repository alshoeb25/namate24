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
            'name' => 'Premium Plan',
            'price' => 399.00,
            'currency' => 'INR',
            'validity_days' => 30,
            'views_allowed' => null, // Unlimited views
            'description' => 'User can view unlimited profiles/requirements within 30 days from activation.',
            'is_active' => true,
            'display_order' => 1,
        ],
        [
            'name' => 'Basic Plan',
            'price' => 100.00,
            'currency' => 'INR',
            'validity_days' => 30,
            'views_allowed' => 5, // Limited to 5 views
            'description' => 'User can view up to 5 profiles/requirements within 30 days from activation.',
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
