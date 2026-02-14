<?php

namespace App\Notifications;

use App\Models\StudentRequirement;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class NewStudentRequirementNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $requirement;

    /**
     * Create a new notification instance.
     */
    public function __construct(StudentRequirement $requirement)
    {
        $this->requirement = $requirement;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        $channels = ['database', 'broadcast'];

        if (!empty($notifiable->email)) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $requirement = StudentRequirement::with('subjects', 'subject')->find($this->requirement->id);
        if (!$requirement) {
            $requirement = $this->requirement;
            $requirement->loadMissing('subjects', 'subject');
        }

        $labelService = app(\App\Services\LabelService::class);
        $labelService->addLabels($requirement);

        $subjectsList = collect($requirement->subjects ?? [])->pluck('name')->filter()->values()->all();
        if (empty($subjectsList)) {
            $subjectsList = DB::table('student_post_subjects')
                ->join('subjects', 'subjects.id', '=', 'student_post_subjects.subject_id')
                ->where('student_post_subjects.student_requirement_id', $requirement->id)
                ->orderBy('subjects.name')
                ->pluck('subjects.name')
                ->filter()
                ->values()
                ->all();
        }
        $subjects = implode(', ', $subjectsList);
        $subjectLabel = $subjects !== ''
            ? $subjects
            : ($requirement->subject_name
                ?? $requirement->other_subject
                ?? 'N/A');
        $meetingOptionsRaw = $requirement->meeting_options;
        $meetingOptions = is_array($meetingOptionsRaw)
            ? implode(', ', array_map('ucfirst', $meetingOptionsRaw))
            : ($meetingOptionsRaw ? ucfirst($meetingOptionsRaw) : 'N/A');
        $meetingOptionsLabel = $requirement->meeting_options_labels
            ?? $meetingOptions;
        if (is_array($meetingOptionsLabel)) {
            $meetingOptionsLabel = implode(', ', $meetingOptionsLabel);
        }

        return (new MailMessage)
            ->subject('New Student Requirement Available')
            ->view('emails.new-requirement-tutor', [
                'tutorName' => $notifiable->name,
                'tutorEmail' => $notifiable->email,
                'requirement' => $requirement,
                'subjects' => $subjects,
                'subjectLabel' => $subjectLabel,
                'meetingOptions' => $meetingOptions,
                'meetingOptionsLabel' => $meetingOptionsLabel,
                'budgetDisplay' => $requirement->budget_display
                    ?? ($requirement->budget_amount
                        ? ('₹' . $requirement->budget_amount)
                        : null),
                'serviceTypeLabel' => $requirement->service_type_label
                    ?? $requirement->service_type,
                'availabilityLabel' => $requirement->availability_label
                    ?? $requirement->availability,
                'genderPreferenceLabel' => $requirement->gender_preference_label
                    ?? $requirement->gender_preference,
                'viewUrl' => url('/requirement/' . $requirement->id),
            ]);
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        $requirement = $this->requirement;
        $requirement->loadMissing('subjects', 'subject');

        $subjectsList = collect($requirement->subjects ?? [])->pluck('name')->filter()->values()->all();
        if (empty($subjectsList)) {
            $subjectsList = DB::table('student_post_subjects')
                ->join('subjects', 'subjects.id', '=', 'student_post_subjects.subject_id')
                ->where('student_post_subjects.student_requirement_id', $requirement->id)
                ->orderBy('subjects.name')
                ->pluck('subjects.name')
                ->filter()
                ->values()
                ->all();
        }
        $subjects = implode(', ', $subjectsList);
        $subjectsLabel = $subjects !== '' ? $subjects : 'subjects';
        
        return [
            'type' => 'new_student_requirement',
            'title' => 'New Student Requirement',
            'message' => "New {$subjectsLabel} requirement posted in {$requirement->area}, {$requirement->city}",
            'requirement_id' => $requirement->id,
            'student_name' => $requirement->student_name,
            'subjects' => $subjects,
            'city' => $requirement->city,
            'area' => $requirement->area,
            'meeting_options' => $requirement->meeting_options,
            'budget_amount' => $requirement->budget_amount,
            'budget_type' => $requirement->budget_type,
            'gender_preference' => $requirement->gender_preference,
        ];
    }

    /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}
