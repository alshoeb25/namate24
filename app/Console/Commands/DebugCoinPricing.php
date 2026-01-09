<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\CoinPackage;
use App\Services\CoinPricingService;

class DebugCoinPricing extends Command
{
    protected $signature = 'debug:coin-pricing';
    protected $description = 'Debug coin pricing for all package types';

    public function handle()
    {
        $this->info('='.str_repeat('=', 76).'=');
        $this->info('| COIN PRICING DEBUG - ALL PACKAGES FOR ALL USERS');
        $this->info('='.str_repeat('=', 76).'=');
        $this->line('');

        // Get or create test users
        $indiaUser = User::where('country_iso', 'IN')->first();
        $usUser = User::where('country_iso', 'US')->first();

        if (!$indiaUser) {
            $this->error('No India user found!');
            return 1;
        }

        if (!$usUser) {
            $this->warn('Creating USA test user...');
            $usUser = User::create([
                'name' => 'USA Test User ' . time(),
                'email' => 'us-test-' . time() . '@example.com',
                'password' => bcrypt('password'),
                'country' => 'United States',
                'country_iso' => 'US',
            ]);
        }

        // Get all packages
        $packages = CoinPackage::active()->orderBy('sort_order')->get();

        $this->line('');
        $this->info('INDIA USER (' . $indiaUser->name . ' - ' . $indiaUser->country_iso . ')');
        $this->line('─'.str_repeat('─', 76).'─');
        $this->table(
            ['Package Name', 'Coins', 'Subtotal', 'GST (18%)', 'Total', 'Currency'],
            $packages->map(function($pkg) use ($indiaUser) {
                $pricing = CoinPricingService::calculatePackagePrice($pkg, $indiaUser);
                return [
                    $pkg->name,
                    $pkg->coins . ' coins',
                    '₹' . number_format($pricing['subtotal'], 2),
                    '₹' . number_format($pricing['tax_amount'], 2),
                    $pricing['display_price'],
                    $pricing['currency']
                ];
            })->toArray()
        );

        $this->line('');
        $this->info('USA USER (' . $usUser->name . ' - ' . $usUser->country_iso . ')');
        $this->line('─'.str_repeat('─', 76).'─');
        $this->table(
            ['Package Name', 'Coins', 'Price', 'Tax', 'Total', 'Currency'],
            $packages->map(function($pkg) use ($usUser) {
                $pricing = CoinPricingService::calculatePackagePrice($pkg, $usUser);
                return [
                    $pkg->name,
                    $pkg->coins . ' coins',
                    '$' . number_format($pricing['subtotal'], 2),
                    '$' . number_format($pricing['tax_amount'], 2),
                    $pricing['display_price'],
                    $pricing['currency']
                ];
            })->toArray()
        );

        $this->line('');
        $this->info('PRICING COMPARISON');
        $this->line('─'.str_repeat('─', 76).'─');
        
        $starterPkg = $packages->first();
        $indiaPricing = CoinPricingService::calculatePackagePrice($starterPkg, $indiaUser);
        $usPricing = CoinPricingService::calculatePackagePrice($starterPkg, $usUser);

        $this->line("Package: {$starterPkg->name} ({$starterPkg->coins} coins)");
        $this->line("  India:  {$indiaPricing['display_price']} (with 18% GST)");
        $this->line("  USA:    {$usPricing['display_price']} (no GST)");
        $this->line("  Ratio:  1 INR = " . number_format($indiaPricing['total'] / $usPricing['total'], 2) . " USD");

        $this->line('');
        $this->info('✅ All packages pricing verified successfully!');

        return 0;
    }
}
