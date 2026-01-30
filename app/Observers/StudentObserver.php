<?php

namespace App\Observers;

use App\Models\Student;
use App\Models\StudentRequirement;
use App\Jobs\IndexRequirementJob;
use App\Jobs\RemoveRequirementFromIndexJob;
use Illuminate\Support\Facades\Log;

class StudentObserver
{
    /**
     * Handle the Student "updated" event.
     */
    public function updated(Student $student): void
    {
        if (!$student->isDirty('is_disabled')) {
            return;
        }

        $studentId = $student->id;

        if ($student->is_disabled) {
            Log::info("StudentObserver: student {$studentId} disabled - removing requirements from index");

            StudentRequirement::where('student_id', $studentId)
                ->select('id')
                ->chunkById(200, function ($requirements) {
                    foreach ($requirements as $requirement) {
                        dispatch(new RemoveRequirementFromIndexJob($requirement->id));
                    }
                });

            return;
        }

        Log::info("StudentObserver: student {$studentId} enabled - reindexing requirements");

        StudentRequirement::where('student_id', $studentId)
            ->whereIn('status', ['open', 'active'])
            ->select('id')
            ->chunkById(200, function ($requirements) {
                foreach ($requirements as $requirement) {
                    dispatch(new IndexRequirementJob($requirement->id));
                }
            });
    }
}
