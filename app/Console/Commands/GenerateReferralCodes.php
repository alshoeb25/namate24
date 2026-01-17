<?php

namespace App\Console\Commands;

use App\Models\ReferralCode;
use Illuminate\Console\Command;

class GenerateReferralCodes extends Command
{
    protected $signature = 'referral:generate-codes {count=5 : Number of codes to generate}';
    protected $description = 'Generate sample referral codes for testing';

    public function handle()
    {
        $count = (int) $this->argument('count');
        $types = ['welcome', 'promotion', 'fest'];
        $codeTypes = ['admin', 'user'];

        for ($i = 0; $i < $count; $i++) {
            ReferralCode::create([
                'referral_code' => ReferralCode::generateCode(),
                'type' => $codeTypes[array_rand($codeTypes)],
                'referral_type' => $types[array_rand($types)],
                'coins' => rand(10, 100),
                'max_count' => rand(0, 1) ? rand(5, 50) : null,
                'expiry' => rand(0, 1) ? now()->addDays(rand(7, 90)) : null,
            ]);
        }

        $this->info("Generated $count referral codes successfully!");
    }
}
