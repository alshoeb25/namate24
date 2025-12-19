<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\CoinPackage;

class CoinPackageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $packages = [
            [
                'name' => 'Starter Pack',
                'coins' => 100,
                'price' => 99.00,
                'bonus_coins' => 0,
                'description' => 'Perfect for trying out the platform',
                'is_popular' => false,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Basic Pack',
                'coins' => 250,
                'price' => 249.00,
                'bonus_coins' => 10,
                'description' => 'Get 10 bonus coins',
                'is_popular' => false,
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Popular Pack',
                'coins' => 500,
                'price' => 499.00,
                'bonus_coins' => 50,
                'description' => 'Most popular! Get 50 bonus coins',
                'is_popular' => true,
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Value Pack',
                'coins' => 1000,
                'price' => 999.00,
                'bonus_coins' => 150,
                'description' => 'Best value! Get 150 bonus coins',
                'is_popular' => false,
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'Premium Pack',
                'coins' => 2500,
                'price' => 2499.00,
                'bonus_coins' => 500,
                'description' => 'Premium users! Get 500 bonus coins',
                'is_popular' => false,
                'is_active' => true,
                'sort_order' => 5,
            ],
            [
                'name' => 'Ultimate Pack',
                'coins' => 5000,
                'price' => 4999.00,
                'bonus_coins' => 1500,
                'description' => 'Ultimate deal! Get 1500 bonus coins',
                'is_popular' => false,
                'is_active' => true,
                'sort_order' => 6,
            ],
        ];

        foreach ($packages as $package) {
            CoinPackage::updateOrCreate(
                ['name' => $package['name']],
                $package
            );
        }
    }
}
