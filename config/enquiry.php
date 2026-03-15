<?php

return [
    /**
     * HYBRID ARCHITECTURE FOR ENQUIRY FEES:
     * 
     * Config stores DEFAULT/GLOBAL fees (fallback)
     * Each enquiry SNAPSHOTS the actual fee paid at posting time in DB
     * 
     * Flow:
     * 1. Config: post_fee = 10 coins
     * 2. Student posts → student_requirements.post_fee = 10 (snapshot at posting)
     * 3. Admin changes fee to 15 coins in .env
     * 4. Next enquiry posts → post_fee = 15 (new snapshot)
     * 5. Old enquiry still shows 10 coins (historical accuracy preserved)
     */

    // Global default: Coins charged when a student posts an enquiry
    'post_fee' => env('ENQUIRY_POST_FEE', 0),

    // Global default: Coins charged when a tutor unlocks/views enquiry contact
    'unlock_fee' => env('ENQUIRY_UNLOCK_FEE', 49),

    // Maximum number of tutors that can unlock and see a single enquiry
    'max_leads' => env('ENQUIRY_MAX_LEADS', 5),

    // Nationality-based pricing
    'pricing_by_nationality' => [
        // Post requirement / Unlock tutors
        'post' => [
            'indian' => env('ENQUIRY_POST_FEE_INDIAN', 0),
            'non_indian' => env('ENQUIRY_POST_FEE_NON_INDIAN', 0),
        ],
        // Tutor unlock profile / Contact unlock
        'unlock' => [
            'indian' => env('ENQUIRY_UNLOCK_FEE_INDIAN', 49),
            'non_indian' => env('ENQUIRY_UNLOCK_FEE_NON_INDIAN', 99),
        ],
    ],

    // Enquiry/Requirement lifecycle statuses
    'statuses' => [
        'active'      => 'Active - Open for tutor applications',
        'paused'      => 'Paused by student',
        'closed'      => 'Closed - All positions filled or auto-closed',
        'cancelled'   => 'Cancelled by student',
        'expired'     => 'Expired - No activity for 30+ days',
    ],

    // Lead/unlock status tracking (individual tutor interactions)
    'lead_statuses' => [
        'open'        => 'Tutor can still unlock',
        'unlocked'    => 'Tutor has unlocked the contact',
        'responded'   => 'Tutor has viewed/responded',
        'hired'       => 'Tutor was hired by student',
        'rejected'    => 'Student rejected tutor',
    ],

    // Fee management settings
    'fees' => [
        // Post enquiry cost (access via config('enquiry.fees.post'))
        'post' => env('ENQUIRY_POST_FEE', 10),
        // Unlock enquiry cost (access via config('enquiry.fees.unlock'))
        'unlock' => env('ENQUIRY_UNLOCK_FEE', 10),
        // Enable dynamic pricing based on demand/region
        'dynamic_pricing' => env('ENQUIRY_DYNAMIC_PRICING', false),
        // Partial refund if enquiry auto-closes with 0 leads (0-100%)
        'refund_percentage' => env('ENQUIRY_REFUND_PERCENTAGE', 100),
    ],

    // Transparency & Lead Management
    'transparency' => [
        // Show lead count to tutors (e.g., "2/5 teachers approached")
        'show_lead_count' => env('ENQUIRY_SHOW_LEAD_COUNT', true),
        // Show spots available (e.g., "3 spots left")
        'show_spots_available' => env('ENQUIRY_SHOW_SPOTS_AVAILABLE', true),
        // Warn tutors when lead is approaching capacity (e.g., 4/5)
        'show_capacity_warning' => env('ENQUIRY_SHOW_CAPACITY_WARNING', true),
    ],

    // Refund & Dispute Management
    'refunds' => [
        // Enable automatic refunds when enquiry cancelled with 0 unlocks
        'auto_refund_on_cancel' => env('ENQUIRY_AUTO_REFUND_CANCEL', true),
        // Enable manual refund requests from tutors (spam, fraud, no response)
        'allow_tutor_refund_requests' => env('ENQUIRY_ALLOW_TUTOR_REFUND', false),
        // Days before tutor can request refund after unlock (prevents abuse)
        'refund_request_days' => env('ENQUIRY_REFUND_REQUEST_DAYS', 7),
        // Allow student to mark enquiry as spam (triggers tutor refunds)
        'spam_reporting' => env('ENQUIRY_SPAM_REPORTING', true),
        // Coins returned to tutor if student marks as spam
        'spam_refund_percentage' => env('ENQUIRY_SPAM_REFUND_PCT', 100),
    ],

    // Enquiry lifecycle rules
    'lifecycle' => [
        // Days before auto-close if no tutor unlocks (0 = disabled)
        'auto_close_days' => env('ENQUIRY_AUTO_CLOSE_DAYS', 30),
        // Days before tutor's unlock expires (contact becomes stale)
        'unlock_validity_days' => env('ENQUIRY_UNLOCK_VALIDITY_DAYS', 7),
        // Days to keep enquiry history before archiving
        'retention_days' => env('ENQUIRY_RETENTION_DAYS', 90),
    ],

    // ── Dynamic Pricing ────────────────────────────────────────────────────────
    'dynamic_pricing' => [
        // Master switch — set ENQUIRY_DYNAMIC_PRICING=true in .env to enable
        'enabled' => env('ENQUIRY_DYNAMIC_PRICING', false),

        // Subject demand classification keywords
        'high_demand_keywords' => [
            'math', 'mathematics', 'physics', 'chemistry', 'biology', 'science',
            'coding', 'programming', 'computer', 'software', 'data', 'python',
            'javascript', 'java', 'c++', 'react', 'node', 'sql',
            'machine learning', 'ai', 'artificial intelligence',
            'engineering', 'economics', 'finance', 'accounting',
            'statistics', 'calculus', 'algebra',
        ],
        'medium_demand_keywords' => [
            'english', 'writing', 'grammar', 'music', 'piano', 'guitar', 'violin',
            'singing', 'dance', 'spanish', 'french', 'german', 'language',
            'history', 'geography', 'art', 'drawing', 'yoga', 'fitness',
            'spoken english', 'public speaking', 'communication',
        ],

        // Coin price tiers per demand level: [base_min, base_max, peak_max]
        'demand_tiers' => [
            'high'   => ['base_min' => 10, 'base_max' => 50,  'peak_max' => 150],
            'medium' => ['base_min' => 10, 'base_max' => 20,  'peak_max' => 50],
            'low'    => ['base_min' => 1,  'base_max' => 5,   'peak_max' => 10],
        ],

        // Region price multipliers (applied to base price)
        'region_multipliers' => [
            'high_income' => 2.0,   // US, UK, CA, AE, AU, SG …
            'standard'    => 1.5,   // Rest of world
            'india'       => 1.0,   // India baseline
        ],

        // Countries classified as high-income (2× multiplier)
        'high_income_countries' => ['US', 'GB', 'CA', 'AE', 'AU', 'SG', 'NZ', 'IE', 'CH', 'NO', 'SE', 'DK'],

        // Competition multiplier: each new applicant adds this fraction to price
        'competition_rate' => 0.15,

        // 36-hour decay rule
        'decay_hours'         => 36,
        'decay_min_applicants'=> 3,
    ],

    // ── Tutor Ranking / Bid System ────────────────────────────────────────────
    'ranking' => [
        // Monthly coin bid tiers → visibility level labels
        'bid_tiers' => [
            ['min' => 2500, 'label' => 'Extreme Visibility', 'rank_range' => [1, 5]],
            ['min' => 1000, 'label' => 'High Visibility',    'rank_range' => [6, 20]],
            ['min' => 500,  'label' => 'Moderate Visibility','rank_range' => [21, 50]],
            ['min' => 0,    'label' => 'Low Visibility',     'rank_range' => [51, 999]],
        ],
        // Max early-access window in minutes (Rank 1 = 0 min, Rank 100 = this value)
        'max_early_access_minutes' => 120,
    ],

    // Enable audit trail logging (tracks fee changes, status changes)
    'audit_enabled' => env('ENQUIRY_AUDIT_LOG', true),

    // Terms & Conditions enforcement for enquiry operations with nationality-based pricing
    'terms_and_conditions' => [
        'enforce_acceptance' => env('ENQUIRY_ENFORCE_TC_ACCEPTANCE', false), // Set to true to require T&C acceptance in API
        'require_for_operations' => [
            'post' => env('ENQUIRY_REQUIRE_TC_POST', false), // Require T&C for posting with nationality-based fees
            'unlock' => env('ENQUIRY_REQUIRE_TC_UNLOCK', false), // Require T&C for unlocking with nationality-based fees
        ],
        'notify_about_pricing' => env('ENQUIRY_NOTIFY_PRICING_CHANGE', true), // Notify users about nationality-based pricing
    ],
];
