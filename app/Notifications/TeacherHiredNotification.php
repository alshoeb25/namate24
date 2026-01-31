<?php

namespace App\Notifications;

use App\Models\StudentRequirement;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TeacherHiredNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $enquiry;
    public $student;

    /**
     * Create a new notification instance.
     */
    public function __construct(StudentRequirement $enquiry, User $student)
    {
        $this->enquiry = $enquiry;
        $this->student = $student;
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
        $subjects = implode(', ', $this->enquiry->subjects?->pluck('name')->toArray() ?? []);
        $subjectLine = $subjects !== ''
            ? $subjects
            : ($this->enquiry->subject_name ?? $this->enquiry->other_subject ?? null);
        
        return (new MailMessage)
            ->subject('🎉 You Have Been Approached!')
            ->view('emails.tutor-hired-notification', [
                'tutor' => $notifiable,
                'student' => $this->student,
                'subject' => $subjectLine,
                'level' => $this->enquiry->level ?? null,
                'learningGoals' => $this->enquiry->details ?? null,
                'approachedDate' => now()->format('M d, Y'),
                'requirementPhone' => $this->enquiry->phone ?? null,
                'requirementAlternatePhone' => $this->enquiry->alternate_phone ?? null,
                'myLearnersUrl' => url('/tutor/profile/my-learners'),
                'subjects' => $subjects,
                'requirementDescription' => $this->enquiry->details,
                'enquiryId' => $this->enquiry->id,
                'dashboardUrl' => url('/tutor/dashboard/enquiries/' . $this->enquiry->id),
            ]);
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'teacher_approached',
            'title' => 'You Have Been Approached! 🎉',
            'message' => "{$this->student->name} has approached you for {$this->enquiry->student_name}'s tutoring session.",
            'enquiry_id' => $this->enquiry->id,
            'student_id' => $this->student->id,
            'student_name' => $this->student->name,
            'student_phone' => $this->student->phone,
        ];
    }

    /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'type' => 'teacher_approached',
            'title' => 'You Have Been Approached! 🎉',
            'message' => "{$this->student->name} has approached you for {$this->enquiry->student_name}'s tutoring session.",
            'enquiry_id' => $this->enquiry->id,
            'student_id' => $this->student->id,
        ]);
    }
}
