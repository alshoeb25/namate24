<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\StudentRequirement;

class CheckSubjectsTable extends Command
{
    protected $signature = 'check:subjects-table {req_id=29 : The requirement ID to check}';
    protected $description = 'Check student_post_subjects table for a requirement';

    public function handle()
    {
        $req_id = $this->argument('req_id');
        
        $this->info("=== CHECKING student_post_subjects TABLE ===\n");

        // Check direct query
        $subjects = \DB::table('student_post_subjects')->where('student_requirement_id', $req_id)->get();
        
        $this->line("Requirement ID: {$req_id}");
        $this->line("Found in student_post_subjects: {$subjects->count()} subjects\n");

        if ($subjects->count() > 0) {
            $this->line("Subjects in pivot table:");
            foreach ($subjects as $row) {
                $subject = \DB::table('subjects')->find($row->subject_id);
                $subjectName = $subject ? $subject->name : "ID: {$row->subject_id}";
                $this->line("  - Subject ID: {$row->subject_id} ({$subjectName})");
            }
        } else {
            $this->warn("  → No subjects found in pivot table");
        }

        $this->line("\n=== CHECKING OTHER REQUIREMENTS ===\n");
        
        // Find requirements that DO have subjects
        $reqsWithSubjects = \DB::table('student_post_subjects')
            ->groupBy('student_requirement_id')
            ->selectRaw('student_requirement_id, COUNT(*) as subject_count')
            ->orderBy('subject_count', 'desc')
            ->limit(5)
            ->get();

        if ($reqsWithSubjects->count() > 0) {
            $this->line("Top 5 requirements with most subjects:");
            foreach ($reqsWithSubjects as $row) {
                $this->line("  - Requirement #{$row->student_requirement_id}: {$row->subject_count} subjects");
            }
        } else {
            $this->warn("No requirements found with subjects!");
        }

        $this->line("\n=== CHECKING REQUIREMENT #{$req_id} FULL DATA ===\n");
        
        $requirement = StudentRequirement::find($req_id);
        if ($requirement) {
            $this->line("Student ID: {$requirement->student_id}");
            $this->line("Subject ID (single): {$requirement->subject_id}");
            $this->line("Subject Name: " . ($requirement->subject_name ?? "NULL"));
            $this->line("Other Subject: " . ($requirement->other_subject ?? "NULL"));
            $this->line("Service Type: " . ($requirement->service_type ?? "NULL"));
        }
    }
}
