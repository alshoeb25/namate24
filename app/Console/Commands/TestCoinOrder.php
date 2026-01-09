<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\CoinPackage;
use App\Services\CoinPricingService;
use App\Http\Controllers\Api\WalletController;
use Illuminate\Http\Request;

class TestCoinOrder extends Command
{
    protected $signature = 'test:coin-order {user_id?}';
    protected $description = 'Test coin order creation with currency';

    public function handle()
    {
        $this->info('='.str_repeat('=', 76).'=');
        $this->info('| COIN ORDER CREATION TEST - VERIFY CURRENCY IN ORDERS & TRANSACTIONS');
        $this->info('='.str_repeat('=', 76).'=');
        $this->line('');

        // Get test users
        $indiaUser = User::where('country_iso', 'IN')->first();
        $usUser = User::where('country_iso', 'US')->first();

        if (!$indiaUser || !$usUser) {
            $this->error('Need both India and USA test users!');
            return 1;
        }

        // Get first package
        $package = CoinPackage::active()->first();
        if (!$package) {
            $this->error('No active coin packages found!');
            return 1;
        }

        $this->line('');
        $this->info('TEST PACKAGE: ' . $package->name . ' (' . $package->coins . ' coins)');
        $this->line('─'.str_repeat('─', 76).'─');

        // Test India User
        $this->testUser($indiaUser, $package);
        
        $this->line('');
        
        // Test USA User
        $this->testUser($usUser, $package);

        $this->line('');
        $this->info('✅ Currency test complete! Check that:');
        $this->info('  • India user: currency=INR, amount in paise');
        $this->info('  • USA user: currency=USD, amount in cents');
        $this->line('');

        return 0;
    }

    private function testUser($user, $package)
    {
        $this->line('');
        $this->info(strtoupper($user->country ?? 'Unknown') . ' USER: ' . $user->name);
        $this->line('─'.str_repeat('─', 76).'─');

        // Calculate pricing
        $pricing = CoinPricingService::calculatePackagePrice($package, $user);
        
        $this->table(
            ['Field', 'Value'],
            [
                ['User ID', $user->id],
                ['Country', $user->country],
                ['Country ISO', $user->country_iso],
                ['Is India', $pricing['is_india'] ? 'Yes' : 'No'],
                ['Currency', $pricing['currency']],
                ['Subtotal', number_format($pricing['subtotal'], 2)],
                ['GST Rate', $pricing['gst_rate'] . '%'],
                ['Tax Amount', number_format($pricing['tax_amount'], 2)],
                ['Total', number_format($pricing['total'], 2)],
                ['Razorpay Amount', '(' . number_format($pricing['total'] * 100, 0) . ' smallest units)'],
            ]
        );

        $this->line('');
        $this->info('Expected Razorpay Order:');
        $this->line("  amount: " . round($pricing['total'] * 100));
        $this->line("  currency: " . $pricing['currency']);
        $this->line("  (This is what user will see in checkout)");
    }
}
