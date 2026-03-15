<?php

namespace App\Console\Commands;

use App\Services\TutorRankingService;
use Illuminate\Console\Command;

class RecalculateTutorRankings extends Command
{
    protected $signature   = 'tutors:recalculate-rankings {--process-bids : Also deduct monthly bids before recalculating}';
    protected $description = 'Recalculate tutor ranks and early-access windows based on monthly bids';

    public function handle(TutorRankingService $rankingService): int
    {
        if ($this->option('process-bids')) {
            $this->info('Processing monthly bid deductions…');
            $result = $rankingService->processMonthlyBids();
            $this->info("Processed: {$result['processed']}, Reduced: {$result['reduced']}, Skipped: {$result['skipped']}");
        } else {
            $this->info('Recalculating rankings…');
            $count = $rankingService->recalculateAllRankings();
            $this->info("Ranked {$count} tutors.");
        }

        return self::SUCCESS;
    }
}
