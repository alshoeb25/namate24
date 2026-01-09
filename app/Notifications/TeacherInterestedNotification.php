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
        return (new MailMessage)
            ->subject('New Tutor Interest in Your Requirement')
            ->view('emails.new-enquiry', [
                'studentName' => $notifiable->name,
                'tutorName' => $this->teacher->name,
                'tutorPhone' => $this->teacher->phone,
                'tutorEmail' => $this->teacher->email,
                'tutorRating' => $this->teacher->rating ?? 4.5,
                'subject' => $this->enquiry->subject,
                'description' => $this->enquiry->description,
                'enquiryId' => $this->enquiry->id,
                'currentLeads' => $this->enquiry->current_leads ?? 0,
                'maxLeads' => $this->enquiry->max_leads ?? 5,
                'requirementUrl' => url('/student/requirements/' . $this->enquiry->id),
            ]);
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'teacher_interested',
            'title' => 'New Teacher Interest',
            'message' => "{$this->teacher->name} is interested in your {$this->enquiry->subject} requirement.",
            'enquiry_id' => $this->enquiry->id,
            'tutor_id' => $this->teacher->id,
            'teacher_name' => $this->teacher->name,
            'subject' => $this->enquiry->subject,
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
