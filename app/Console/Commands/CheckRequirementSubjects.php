<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\StudentRequirement;

class CheckRequirementSubjects extends Command
{
    protected $signature = 'check:requirement {id=29}';
    protected $description = 'Check requirement subject data';

    public function handle()
    {
        $req_id = $this->argument('id');
        
        $requirement = StudentRequirement::with('subjects', 'subject')->find($req_id);
        
        if (!$requirement) {
            $this->error("Requirement not found");
            return;
        }

        $this->info("=== REQUIREMENT #{$req_id} SUBJECT DATA ===");
        $this->line("ID: {$requirement->id}");
        $this->line("Subject (single relation): " . ($requirement->subject ? $requirement->subject->name : "NULL"));
        $this->line("Subject Name field: {$requirement->subject_name}");
        $this->line("Other Subject field: {$requirement->other_subject}");
        $this->line("");
        
        if ($requirement->subjects) {
            $this->line("Subjects (relationship) count: {$requirement->subjects->count()}");
            foreach ($requirement->subjects as $subject) {
                $this->line("  - {$subject->name} (ID: {$subject->id})");
            }
        } else {
            $this->line("Subjects (relationship): NOT LOADED or NULL");
        }

        $this->line("");
        $this->info("Raw database data:");
        $raw = \DB::table('student_requirements')->find($req_id);
        $this->line("  subject_id: {$raw->subject_id}");
        $this->line("  subject_name: {$raw->subject_name}");
        $this->line("  other_subject: {$raw->other_subject}");
    }
}
