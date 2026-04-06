<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubscriptionPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Disable foreign key checks temporarily to allow truncation
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Delete existing plans to avoid duplicates
        SubscriptionPlan::truncate();
        
        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Get plans from config if available
        $plans = config('coins.subscription_plans', [
            [
                'name' => 'Pro',
                'price' => 399.00,
                'currency' => 'INR',
                'validity_days' => 30,
                'views_allowed' => 10, // 10-12 views at 39 coins each
                'coins_included' => 399, // 399 coins
                'has_priority_support' => true,
                'has_ebook_content' => true,
                'access_delay_hours' => 0, // Real-time access (immediate)
                'cost_per_view' => 39, // 39 coins per view
                'coins_carry_forward' => true, // Coins carry forward on lapse
                'lapse_grace_period_hours' => 2, // 2 hour grace period after expiry
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
                'coins_included' => 99, // 99 coins
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
        ]);

        foreach ($plans as $plan) {
            SubscriptionPlan::create($plan);
        }

        $this->command->info('Subscription plans seeded successfully!');
    }
}
