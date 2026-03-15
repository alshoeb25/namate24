<?php

namespace App\Jobs;

use App\Models\StudentRequirement;
use App\Services\DynamicPricingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Triggered whenever a tutor unlocks a requirement.
 * Recalculates the dynamic_price based on new competition level.
 */
class UpdateRequirementDynamicPrice implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 30;

    public function __construct(public readonly int $requirementId)
    {
    }

    public function handle(DynamicPricingService $pricingService): void
    {
        if (!config('enquiry.fees.dynamic_pricing', false)) {
            return; // Feature flag off — skip
        }

        $req = StudentRequirement::find($this->requirementId);

        if (!$req) {
            Log::warning("UpdateRequirementDynamicPrice: requirement {$this->requirementId} not found.");
            return;
        }

        $req->load(['subject', 'subjects']);
        $pricingService->recalculatePrice($req);
    }

    public function tags(): array
    {
        return ['dynamic-pricing', "requirement:{$this->requirementId}"];
    }
}
