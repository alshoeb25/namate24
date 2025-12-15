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
    }
}