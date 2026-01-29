<?php

namespace App\Notifications;

use App\Models\StudentRequirement;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

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

        $subjects = collect($requirement->subjects ?? [])->pluck('name')->implode(', ');
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
        $subjects = collect($this->requirement->subjects ?? [])->pluck('name')->implode(', ');
        $subjectsLabel = $subjects !== '' ? $subjects : 'subjects';
        
        return [
            'type' => 'new_student_requirement',
            'title' => 'New Student Requirement',
            'message' => "New {$subjectsLabel} requirement posted in {$this->requirement->area}, {$this->requirement->city}",
            'requirement_id' => $this->requirement->id,
            'student_name' => $this->requirement->student_name,
            'subjects' => $subjects,
            'city' => $this->requirement->city,
            'area' => $this->requirement->area,
            'meeting_options' => $this->requirement->meeting_options,
            'budget_amount' => $this->requirement->budget_amount,
            'budget_type' => $this->requirement->budget_type,
            'gender_preference' => $this->requirement->gender_preference,
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
