<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ApproachTeacherCostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Note: Approach teacher cost is configured in config/coins.php
     * and can be overridden in .env using COIN_APPROACH_TEACHER_COST
     * 
     * Default: 10 coins
     * 
     * To change: Add COIN_APPROACH_TEACHER_COST=15 to .env file
     */
    public function run(): void
    {
        // Configuration is handled via config/coins.php and .env
        // No database seeding needed
        $this->command->info('Approach teacher cost is configured in .env (COIN_APPROACH_TEACHER_COST)');
        $this->command->info('Current value: ' . config('coins.approach_teacher_cost', 10) . ' coins');
    }
}
