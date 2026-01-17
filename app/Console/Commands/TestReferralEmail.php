<?php

namespace App\Console\Commands;

use App\Models\ReferralInvite;
use App\Models\ReferralCode;
use App\Jobs\SendReferralInviteEmail;
use Illuminate\Console\Command;

class TestReferralEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:referral-email {--email=test@example.com}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test referral invite email sending';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->option('email');

        $this->info("Testing referral invite email sending...");
        $this->info("Email: {$email}");

        try {
            // Get or create a referral code
            $code = ReferralCode::where('used', false)->first();
            
            if (!$code) {
                $this->error('No unused referral code found!');
                return 1;
            }

            $this->info("Using referral code: {$code->referral_code}");

            // Create or get referral invite
            $invite = ReferralInvite::firstOrCreate(
                ['email' => $email],
                [
                    'referral_code_id' => $code->id,
                    'referred_coins' => $code->coins,
                    'email_status' => 'pending',
                ]
            );

            $this->info("Referral invite created/found: ID {$invite->id}");
            $this->info("Current status: {$invite->email_status}");

            // Set to pending and dispatch
            $invite->update(['email_status' => 'pending', 'email_error' => null]);
            
            $this->info("Dispatching email job...");
            SendReferralInviteEmail::dispatch($invite);

            $this->info("✓ Email job dispatched successfully!");
            $this->info("Check queue: php artisan queue:work --queue=emails");
            $this->info("Check logs: storage/logs/laravel.log");

            return 0;
        } catch (\Throwable $e) {
            $this->error("Error: {$e->getMessage()}");
            $this->error($e->getTraceAsString());
            return 1;
        }
    }
}
