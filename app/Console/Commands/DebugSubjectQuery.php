<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\StudentRequirement;
use App\Models\Subject;
use Illuminate\Support\Facades\DB;

class DebugSubjectQuery extends Command
{
    protected $signature = 'debug:subject-query';
    protected $description = 'Debug the subjects relationship query';

    public function handle()
    {
        $req_id = 29;

        $this->info("=== DEBUGGING SUBJECT QUERY ===\n");

        // Enable query logging
        DB::enableQueryLog();

        $this->line("Loading requirement with subjects...\n");
        
        $requirement = StudentRequirement::with('subjects')->find($req_id);

        $queries = DB::getQueryLog();
        
        foreach ($queries as $query) {
            $this->line("SQL: {$query['query']}");
            if (!empty($query['bindings'])) {
                $this->line("Bindings: " . json_encode($query['bindings']));
            }
            $this->line("Time: {$query['time']}ms\n");
        }

        if ($requirement) {
            $this->line("Requirement found: #{$requirement->id}");
            $this->line("Subjects property exists: " . (property_exists($requirement, 'subjects') ? "YES" : "NO"));
            $this->line("Subjects is null: " . ($requirement->subjects === null ? "YES" : "NO"));
            $this->line("Subjects type: " . (is_object($requirement->subjects) ? get_class($requirement->subjects) : gettype($requirement->subjects)));
            
            if ($requirement->subjects) {
                $this->line("Subjects count: " . $requirement->subjects->count());
                foreach ($requirement->subjects as $subject) {
                    $this->line("  - {$subject->name}");
                }
            }
        }

        // Test direct query
        $this->line("\n=== DIRECT QUERY TEST ===\n");
        DB::enableQueryLog();
        
        $subjects = DB::table('student_post_subjects')
            ->where('student_requirement_id', $req_id)
            ->join('subjects', 'student_post_subjects.subject_id', '=', 'subjects.id')
            ->select('subjects.*')
            ->get();

        $queries = DB::getQueryLog();
        
        foreach ($queries as $query) {
            $this->line("SQL: {$query['query']}");
        }
        
        $this->line("Found subjects: {$subjects->count()}");
        foreach ($subjects as $s) {
            $this->line("  - {$s->name}");
        }

        // Test Eloquent query
        $this->line("\n=== ELOQUENT SUBJECTS() METHOD TEST ===\n");
        DB::enableQueryLog();
        
        $req2 = StudentRequirement::find($req_id);
        $subjectsFromMethod = $req2->subjects();
        
        $this->line("Query object created: YES");
        $this->line("About to get results...\n");
        
        $results = $subjectsFromMethod->get();
        
        $queries = DB::getQueryLog();
        
        foreach ($queries as $query) {
            $this->line("SQL: {$query['query']}");
            if (!empty($query['bindings'])) {
                $this->line("Bindings: " . implode(", ", $query['bindings']));
            }
        }
        
        $this->line("\nResults: {$results->count()} subjects");
        foreach ($results as $s) {
            $this->line("  - {$s->name}");
        }
    }
}
