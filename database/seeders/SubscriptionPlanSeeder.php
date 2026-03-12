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
                'name' => 'Premium Plan',
                'price' => 399.00,
                'currency' => 'INR',
                'validity_days' => 30,
                'views_allowed' => null,
                'description' => 'User can view unlimited profiles/requirements within 30 days from activation.',
                'is_active' => true,
                'display_order' => 1,
            ],
            [
                'name' => 'Basic Plan',
                'price' => 100.00,
                'currency' => 'INR',
                'validity_days' => 30,
                'views_allowed' => 5,
                'description' => 'User can view up to 5 profiles/requirements within 30 days from activation.',
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
