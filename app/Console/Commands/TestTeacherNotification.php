<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\StudentRequirement;
use App\Models\User;
use App\Notifications\TeacherInterestedNotification;
use Illuminate\Support\Facades\DB;

class TestTeacherNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:teacher-notification {--last}';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Test TeacherInterestedNotification - view last notification or send test';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->option('last')) {
            $this->viewLastNotification();
        } else {
            $this->sendTestNotification();
        }
    }

    private function viewLastNotification()
    {
        $this->info('=== LAST 5 TEACHER INTERESTED NOTIFICATIONS ===');
        
        $notifications = DB::table('notifications')
            ->where('type', 'like', '%TeacherInterested%')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        if ($notifications->isEmpty()) {
            $this->warn('No TeacherInterestedNotification found in database.');
            return;
        }

        foreach ($notifications as $notification) {
            $this->line('');
            $this->info("Notification ID: {$notification->id}");
            $this->line("Created: {$notification->created_at}");
            $this->line("For User: {$notification->notifiable_id}");
            
            $data = json_decode($notification->data);
            $this->line('Data:');
            $this->line(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            $this->line(str_repeat("-", 80));
        }
    }

    private function sendTestNotification()
    {
        // Use existing requirements from the logs
        $studentId = 13;
        $teacherId = 18;
        $requirementId = 29;

        $student = User::find($studentId);
        $teacher = User::find($teacherId);
        $requirement = StudentRequirement::find($requirementId);

        if (!$student || !$teacher || !$requirement) {
            $this->error("Required data not found:");
            $this->error("  Student (ID: $studentId): " . ($student ? "OK" : "MISSING"));
            $this->error("  Teacher (ID: $teacherId): " . ($teacher ? "OK" : "MISSING"));
            $this->error("  Requirement (ID: $requirementId): " . ($requirement ? "OK" : "MISSING"));
            return;
        }

        $this->info("Sending TeacherInterestedNotification test...");
        $this->line("  Student: {$student->name} ({$student->email})");
        $this->line("  Teacher: {$teacher->name} ({$teacher->email})");
        $this->line("  Requirement: #{$requirement->id}");

        try {
            // Create notification instance
            $notification = new TeacherInterestedNotification($requirement, $teacher);
            
            // Send synchronously to database (bypass queue)
            $student->notify($notification);
            
            $this->info('✓ Notification sent and queued!');
            $this->line('Processing queue...');
            
            // Process the queue
            $process = \Symfony\Component\Process\Process::fromShellCommandline(
                'php artisan queue:work redis --max-jobs=1 --stop-when-empty --timeout=10',
                base_path()
            );
            $process->run();
            
            // Show the notification that was just created
            $this->line('');
            $this->info('Showing the last notification:');
            $this->viewLastNotification();
        } catch (\Exception $e) {
            $this->error('Error sending notification: ' . $e->getMessage());
        }
    }
}
