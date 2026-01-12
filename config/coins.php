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

    // Referral bonus coins
    'referral_bonus' => env('REFERRAL_BONUS', 50),

    // Free requirements count for students
    'free_requirements_count' => env('FREE_REQUIREMENTS_COUNT', 3),
];
