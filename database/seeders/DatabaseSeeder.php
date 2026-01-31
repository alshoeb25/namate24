<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\SubjectSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // Core data
            SubjectSeeder::class,
            LevelSeeder::class,
            InstituteSeeder::class,
            
            // Admin system
            AdminRolesAndPermissionsSeeder::class,
            AdminUsersSeeder::class,
            
            // Coin packages
            CoinPackageSeeder::class,
            ApproachTeacherCostSeeder::class,
            
            // Field labels
            FieldLabelSeeder::class,
            
            // Sample/Featured data (optional)
            // FeaturedTutorsSeeder::class,
            RoleSeeder::class,
        ]);
    }
}
