<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class UpdateUserCountries extends Command
{
    protected $signature = 'user:set-countries';
    protected $description = 'Set default countries for users without country_iso';

    public function handle()
    {
        $this->info('Updating users with missing country_iso...');
        $this->line('');

        // Check current state
        $totalUsers = User::count();
        $usersWithoutCountry = User::whereNull('country_iso')->count();
        $usersWithCountry = User::whereNotNull('country_iso')->count();

        $this->line("Total users: {$totalUsers}");
        $this->line("Users with country_iso: {$usersWithCountry}");
        $this->line("Users without country_iso: {$usersWithoutCountry}");
        $this->line('');

        if ($usersWithoutCountry > 0) {
            $this->info("Setting country_iso to 'IN' for {$usersWithoutCountry} users without country info...");
            
            $updated = User::whereNull('country_iso')->update([
                'country' => 'India',
                'country_iso' => 'IN'
            ]);

            $this->info("Updated {$updated} users with India as default country");
        }

        // Show breakdown
        $inCount = User::where('country_iso', 'IN')->count();
        $otherCount = User::where('country_iso', '!=', 'IN')->count();
        $nullCount = User::whereNull('country_iso')->count();

        $this->line('');
        $this->line('Final state:');
        $this->line("  India users: {$inCount}");
        $this->line("  Other country users: {$otherCount}");
        $this->line("  Users without country: {$nullCount}");

        return 0;
    }
}
