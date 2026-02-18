<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\StudentRequirement;
use App\Notifications\TeacherInterestedNotification;

class DebugNotificationSubjects extends Command
{
    protected $signature = 'debug:notification-subjects';
    protected $description = 'Debug why subjects are not showing in notification';

    public function handle()
    {
        $req_id = 29;
        $notifiable_id = 13;
        $teacher_id = 18;

        $this->info("=== DEBUGGING NOTIFICATION SUBJECTS ===\n");

        // Test 1: Direct database query
        $this->line("TEST 1: Direct database query");
        $subjects_db = \DB::table('student_post_subjects')
            ->where('student_requirement_id', $req_id)
            ->pluck('subject_id')
            ->toArray();
        $this->line("  Subjects in student_post_subjects: " . json_encode($subjects_db));

        // Test 2: Load with relationship
        $this->line("\nTEST 2: Load requirement with subjects relationship");
        $req = StudentRequirement::with('subjects')->find($req_id);
        $this->line("  Requirement loaded: " . ($req ? "YES" : "NO"));
        if ($req) {
            $this->line("  Subjects collection: " . ($req->subjects ? "LOADED" : "NULL"));
            if ($req->subjects) {
                $this->line("  Subject count: " . $req->subjects->count());
                foreach ($req->subjects as $s) {
                    $this->line("    - {$s->name} (ID: {$s->id})");
                }
            }
            
            // Test the pluck operation
            $collected = collect($req->subjects ?? [])->pluck('name')->implode(', ');
            $this->line("  Plucked names: '{$collected}'");
        }

        // Test 3: loadMissing
        $this->line("\nTEST 3: Using loadMissing");
        $req2 = StudentRequirement::find($req_id);
        $req2->loadMissing('subjects');
        $this->line("  After loadMissing:");
        $this->line("  Subjects collection: " . ($req2->subjects ? "LOADED" : "NULL"));
        if ($req2->subjects) {
            $this->line("  Subject count: " . $req2->subjects->count());
            foreach ($req2->subjects as $s) {
                $this->line("    - {$s->name} (ID: {$s->id})");
            }
        }

        // Test 4: Fresh query
        $this->line("\nTEST 4: Fresh database fetch with eager load");
        $req3 = StudentRequirement::with(['subjects'])->find($req_id);
        $this->line("  Fresh fetch - subjects count: " . ($req3->subjects ? $req3->subjects->count() : "NULL/0"));
        
        // Test 5: The actual notification code
        $this->line("\nTEST 5: Simulating notification code");
        $enquiry = StudentRequirement::with('subjects', 'subject')->find($req_id) ?? StudentRequirement::find($req_id);
        $enquiry->loadMissing('subjects', 'subject');
        
        $subjects = collect($enquiry->subjects ?? [])->pluck('name')->implode(', ');
        $this->line("  Subjects from notification code: '{$subjects}'");
        $this->line("  Is empty: " . (empty($subjects) ? "YES" : "NO"));
        
        if (empty($subjects)) {
            $this->warn("  → FALLING BACK TO: subject_name={$enquiry->subject_name}, other_subject={$enquiry->other_subject}");
        }
    }
}
