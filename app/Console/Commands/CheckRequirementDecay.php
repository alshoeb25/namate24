<?php

namespace App\Console\Commands;

use App\Models\StudentRequirement;
use App\Services\DynamicPricingService;
use Illuminate\Console\Command;

/**
 * Hourly command: Apply the 36-hour decay rule.
 *
 * If a requirement was posted more than 36 hours ago and received
 * fewer than 3 unlocks, dynamic_price is set to 0 (free).
 */
class CheckRequirementDecay extends Command
{
    protected $signature   = 'enquiry:check-decay';
    protected $description = '36-hour decay: set dynamic_price=0 for low-competition requirements';

    public function handle(DynamicPricingService $pricingService): int
    {
        if (!config('enquiry.fees.dynamic_pricing', false)) {
            $this->info('Dynamic pricing is disabled — skipping decay check.');
            return self::SUCCESS;
        }

        // Requirements still active, not yet decayed, posted within 40h (buffer)
        $candidates = StudentRequirement::where('status', 'active')
            ->where('price_decayed', false)
            ->where('dynamic_price', '>', 0)
            ->whereNotNull('posted_at')
            ->where('posted_at', '<=', now()->subHours(36))
            ->where('current_leads', '<', 3)
            ->get();

        $decayed = 0;
        foreach ($candidates as $req) {
            $pricingService->checkAndApplyDecay($req);
            $decayed++;
        }

        $this->info("Decay check complete. {$decayed} requirements decayed to free.");
        return self::SUCCESS;
    }
}
