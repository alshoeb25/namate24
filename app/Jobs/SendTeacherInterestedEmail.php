<?php

namespace App\Jobs;

use App\Models\StudentRequirement;
use App\Models\User;
use App\Notifications\TeacherInterestedNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class SendTeacherInterestedEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $enquiryId,
        public int $teacherId,
        public string $studentEmail
    ) {
    }

    public function handle(): void
    {
        if (empty($this->studentEmail)) {
            return;
        }

        $enquiry = StudentRequirement::with('subjects')->find($this->enquiryId);
        $teacher = User::find($this->teacherId);

        if (!$enquiry || !$teacher) {
            Log::warning('Teacher interest email skipped - missing data', [
                'enquiry_id' => $this->enquiryId,
                'teacher_id' => $this->teacherId,
            ]);
            return;
        }

        Log::info('Queue: sending teacher interest email', [
            'enquiry_id' => $this->enquiryId,
            'teacher_id' => $this->teacherId,
            'to' => $this->studentEmail,
        ]);

        try {
            Notification::route('mail', $this->studentEmail)
                ->notifyNow(new TeacherInterestedNotification($enquiry, $teacher));

            Log::info('Teacher interest email sent', [
                'enquiry_id' => $this->enquiryId,
                'teacher_id' => $this->teacherId,
                'to' => $this->studentEmail,
            ]);
        } catch (\Throwable $e) {
            Log::error('Teacher interest email failed', [
                'enquiry_id' => $this->enquiryId,
                'teacher_id' => $this->teacherId,
                'to' => $this->studentEmail,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
