<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\StudentRequirement;
use App\Models\User;
use App\Notifications\TeacherInterestedNotification;

class TestNotificationData extends Command
{
    protected $signature = 'test:notification-data';
    protected $description = 'Test the notification toArray() method directly';

    public function handle()
    {
        $requirement = StudentRequirement::find(29);
        $student = User::find(13);
        $teacher = User::find(18);

        if (!$requirement || !$student || !$teacher) {
            $this->error("Required data not found");
            return;
        }

        $this->info("=== TESTING NOTIFICATION DATA ===\n");

        $notification = new TeacherInterestedNotification($requirement, $teacher);
        
        $this->line("Requirement: #{$requirement->id}");
        $this->line("Student: {$student->name}");
        $this->line("Teacher: {$teacher->name}");

        try {
            // Test toArray
            $data = $notification->toArray($student);
            
            $this->line("\n✓ toArray() executed successfully\n");
            $this->line("Notification Data:");
            $this->line(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            
            // Check subject field
            $this->line("\n=== SUBJECT FIELD ===");
            if ($data['subject'] === 'Algorithms') {
                $this->info("✓ Subject correctly shows: '{$data['subject']}'");
            } elseif ($data['subject'] === 'Not specified') {
                $this->warn("⚠ Subject shows: '{$data['subject']}' (fallback - no subjects in database)");
            } else {
                $this->error("✗ Subject shows: '{$data['subject']}' (unexpected value)");
            }
        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
            $this->line($e->getTraceAsString());
        }
    }
}
