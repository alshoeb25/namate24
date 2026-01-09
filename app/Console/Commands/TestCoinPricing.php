<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\CoinPackage;
use App\Services\CoinPricingService;

class TestCoinPricing extends Command
{
    protected $signature = 'test:coin-pricing';
    protected $description = 'Test coin pricing service with sample users';

    public function handle()
    {
        $this->info('Testing Coin Pricing Service...');
        $this->line('');

        // Test 1: India User
        $this->info('Test 1: India User (country_iso = IN)');
        $this->line('─────────────────────────────────────');
        
        $indiaUser = User::whereNotNull('country_iso')->where('country_iso', 'IN')->first();
        if (!$indiaUser) {
            $this->warn('No India user found. Creating test user...');
            $indiaUser = User::create([
                'name' => 'Test India User',
                'email' => 'test-india-' . time() . '@example.com',
                'password' => bcrypt('password'),
                'country' => 'India',
                'country_iso' => 'IN',
            ]);
        }

        $package = CoinPackage::active()->first();
        if (!$package) {
            $this->error('No active coin packages found!');
            return 1;
        }

        $pricing = CoinPricingService::calculatePackagePrice($package, $indiaUser);
        
        $this->line("User: {$indiaUser->name}");
        $this->line("Country: {$indiaUser->country} ({$indiaUser->country_iso})");
        $this->line("Package: {$package->name} ({$package->coins} coins)");
        $this->line("Is India: " . ($pricing['is_india'] ? 'Yes' : 'No'));
        $this->line("Currency: {$pricing['currency']}");
        $this->line("Subtotal: {$pricing['currency_symbol']}{$pricing['subtotal']}");
        $this->line("GST Rate: {$pricing['gst_percentage']}");
        $this->line("Tax Amount: {$pricing['currency_symbol']}{$pricing['tax_amount']}");
        $this->line("Total: {$pricing['display_price']}");
        $this->line('');

        // Test 2: International User
        $this->info('Test 2: International User (country_iso = US)');
        $this->line('─────────────────────────────────────');
        
        $usUser = User::whereNotNull('country_iso')->where('country_iso', 'US')->first();
        if (!$usUser) {
            $this->warn('No USA user found. Creating test user...');
            $usUser = User::create([
                'name' => 'Test USA User',
                'email' => 'test-usa-' . time() . '@example.com',
                'password' => bcrypt('password'),
                'country' => 'United States',
                'country_iso' => 'US',
            ]);
        }

        $pricing = CoinPricingService::calculatePackagePrice($package, $usUser);
        
        $this->line("User: {$usUser->name}");
        $this->line("Country: {$usUser->country} ({$usUser->country_iso})");
        $this->line("Package: {$package->name} ({$package->coins} coins)");
        $this->line("Is India: " . ($pricing['is_india'] ? 'Yes' : 'No'));
        $this->line("Currency: {$pricing['currency']}");
        $this->line("Subtotal: {$pricing['currency_symbol']}{$pricing['subtotal']}");
        $this->line("GST Rate: {$pricing['gst_percentage']}");
        $this->line("Tax Amount: {$pricing['currency_symbol']}{$pricing['tax_amount']}");
        $this->line("Total: {$pricing['display_price']}");
        $this->line('');

        // Test 3: Check isIndiaUser function
        $this->info('Test 3: isIndiaUser Function');
        $this->line('─────────────────────────────────────');
        
        $this->line("isIndiaUser(\$indiaUser): " . (CoinPricingService::isIndiaUser($indiaUser) ? 'true' : 'false'));
        $this->line("isIndiaUser(\$usUser): " . (CoinPricingService::isIndiaUser($usUser) ? 'true' : 'false'));
        $this->line('');

        // Test 4: Get all packages with pricing
        $this->info('Test 4: All Packages with Pricing (India User)');
        $this->line('─────────────────────────────────────');
        
        $packages = CoinPricingService::getPackagesWithPricing($indiaUser);
        foreach ($packages as $pkg) {
            $this->line("{$pkg['name']}: {$pkg['display_price']}");
        }
        $this->line('');

        $this->info('All tests completed successfully!');
        return 0;
    }
}
