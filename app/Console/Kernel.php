<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule)
    {
        // run every day at 00:05
        $schedule->command('credits:expire')->dailyAt('00:05');

        // schedule queue:restart (if using worker supervisors)
        $schedule->command('queue:restart')->daily();

        // Dynamic Pricing: 36-hour decay check (hourly)
        $schedule->command('enquiry:check-decay')->hourly();

        // Auto-refunds: stale unlocks (15d unread + unverified phone) + spam jobs
        $schedule->command('enquiry:auto-refunds')->dailyAt('02:00');

        // Tutor Rankings: process monthly bids + recalculate (1st of each month)
        $schedule->command('tutors:recalculate-rankings --process-bids')->monthlyOn(1, '00:30');

        // Tutor Rankings: daily rotation refresh for equal-bid tiebreaking
        $schedule->command('tutors:recalculate-rankings')->dailyAt('00:10');
    }
}