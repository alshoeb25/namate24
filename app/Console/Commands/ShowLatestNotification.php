<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ShowLatestNotification extends Command
{
    protected $signature = 'show:latest-notification';
    protected $description = 'Show the latest notification';

    public function handle()
    {
        $latest = \DB::table('notifications')
            ->where('type', 'like', '%TeacherInterested%')
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$latest) {
            $this->error('No notifications found');
            return;
        }

        $this->info("Latest Notification:");
        $this->line("ID: {$latest->id}");
        $this->line("Created: {$latest->created_at}");
        
        $data = json_decode($latest->data);
        $this->line("\nData:");
        $this->line(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }
}
