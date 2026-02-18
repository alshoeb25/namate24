<?php

namespace App\Notifications;

use App\Models\StudentRequirement;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TeacherInterestedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $enquiry;
    public $teacher;

    /**
     * Create a new notification instance.
     */
    public function __construct(StudentRequirement $enquiry, User $teacher)
    {
        $this->enquiry = $enquiry;
        $this->teacher = $teacher;
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
        $enquiry = StudentRequirement::with('subject')->find($this->enquiry->id) ?? $this->enquiry;
        $enquiry->loadMissing('subject');

        $labelService = app(\App\Services\LabelService::class);
        $labelService->addLabels($enquiry);

        // Get subjects directly from pivot table since relationship property access has issues
        $subjectsData = \DB::table('student_post_subjects')
            ->where('student_requirement_id', $enquiry->id)
            ->join('subjects', 'student_post_subjects.subject_id', '=', 'subjects.id')
            ->pluck('subjects.name')
            ->implode(', ');
        
        $subjectLabel = !empty($subjectsData)
            ? $subjectsData
            : ($enquiry->subject_name ?? $enquiry->other_subject ?? 'Not specified');

        $meetingOptionsRaw = $enquiry->meeting_options;
        $meetingOptions = is_array($meetingOptionsRaw)
            ? implode(', ', array_map('ucfirst', $meetingOptionsRaw))
            : ($meetingOptionsRaw ? ucfirst($meetingOptionsRaw) : 'N/A');
        $meetingOptionsLabel = $enquiry->meeting_options_labels ?? $meetingOptions;
        if (is_array($meetingOptionsLabel)) {
            $meetingOptionsLabel = implode(', ', $meetingOptionsLabel);
        }

        $unlockCoins = $enquiry->unlock_price ?? config('enquiry.unlock_fee');
        $leadStatus = ($enquiry->current_leads ?? 0) . '/' . ($enquiry->max_leads ?? 0) . ' tutors';

        return (new MailMessage)
            ->subject('New Tutor Interest in Your Requirement')
            ->view('emails.teacher-interested', [
                'studentName' => $notifiable->name ?? 'Student',
                'teacherName' => $this->teacher->name ?? 'Tutor',
                'requirement' => $enquiry,
                'subjectLabel' => $subjectLabel,
                'meetingOptions' => $meetingOptions,
                'meetingOptionsLabel' => $meetingOptionsLabel,
                'budgetDisplay' => $enquiry->budget_display
                    ?? ($enquiry->budget_amount
                        ? ('₹' . $enquiry->budget_amount)
                        : null),
                'serviceTypeLabel' => $enquiry->service_type_label
                    ?? $enquiry->service_type,
                'availabilityLabel' => $enquiry->availability_label
                    ?? $enquiry->availability,
                'genderPreferenceLabel' => $enquiry->gender_preference_label
                    ?? $enquiry->gender_preference,
                'unlockCoins' => $unlockCoins,
                'leadStatus' => $leadStatus,
                'viewUrl' => url('/student/requirement-details/' . $enquiry->id),
            ]);
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        $enquiry = $this->enquiry;
        
        // Get subjects directly from pivot table since relationship property access has issues
        $subjectsData = \DB::table('student_post_subjects')
            ->where('student_requirement_id', $enquiry->id)
            ->join('subjects', 'student_post_subjects.subject_id', '=', 'subjects.id')
            ->pluck('subjects.name')
            ->implode(', ');
        
        $subjectLabel = !empty($subjectsData)
            ? $subjectsData
            : ($enquiry->subject_name ?? $enquiry->other_subject ?? 'Not specified');

        return [
            'type' => 'teacher_interested',
            'title' => 'New Teacher Interest',
            'message' => "{$this->teacher->name} is interested in your {$subjectLabel} requirement.",
            'enquiry_id' => $this->enquiry->id,
            'tutor_id' => $this->teacher->id,
            'teacher_name' => $this->teacher->name,
            'subject' => $subjectLabel,
            'unlock_price' => $this->enquiry->unlock_price ?? config('enquiry.unlock_fee'),
            'current_leads' => $this->enquiry->current_leads,
            'max_leads' => $this->enquiry->max_leads,
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
