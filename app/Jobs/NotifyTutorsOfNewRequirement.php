<?php

namespace App\Jobs;

use App\Models\StudentRequirement;
use App\Models\Tutor;
use App\Notifications\NewStudentRequirementNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class NotifyTutorsOfNewRequirement implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $requirement;
    public $subjectIds;

    /**
     * Create a new job instance.
     */
    public function __construct(StudentRequirement $requirement, array $subjectIds = [])
    {
        $this->requirement = $requirement;
        $this->subjectIds = $subjectIds;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $this->requirement->loadMissing('subjects');

            $subjectIds = $this->subjectIds;
            if (empty($subjectIds)) {
                $subjectIds = $this->requirement->subjects
                    ? $this->requirement->subjects->pluck('id')->toArray()
                    : [];
            }

            if (empty($subjectIds)) {
                Log::info('No subjects found for requirement; skipping tutor notifications', [
                    'requirement_id' => $this->requirement->id,
                ]);
                return;
            }

            // Get tutors with matching subjects and user accounts
            $tutors = Tutor::with('user')
                ->whereHas('user')
                ->whereHas('subjects', function ($query) use ($subjectIds) {
                    $query->whereIn('subjects.id', $subjectIds);
                })
                ->get();

            // Send notification to each tutor
            $notifiedCount = 0;
            foreach ($tutors as $tutor) {
                if ($tutor->user) {
                    try {
                        $tutor->user->notify(new NewStudentRequirementNotification($this->requirement));
                        $notifiedCount++;
                    } catch (\Exception $e) {
                        Log::warning('Failed to notify single tutor', [
                            'tutor_id' => $tutor->id,
                            'user_id' => $tutor->user_id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            }
   
        } catch (\Exception $e) {
            Log::error('Failed to send tutor notifications', [
                'requirement_id' => $this->requirement->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Re-throw to allow retry mechanism
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('NotifyTutorsOfNewRequirement job failed', [
            'requirement_id' => $this->requirement->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
