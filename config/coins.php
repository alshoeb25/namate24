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
];
