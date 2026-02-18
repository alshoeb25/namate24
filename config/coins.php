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
            'indian' => env('CONTACT_UNLOCK_COINS_INDIAN', 199),
            'non_indian' => env('CONTACT_UNLOCK_COINS_NON_INDIAN', 399),
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
];
