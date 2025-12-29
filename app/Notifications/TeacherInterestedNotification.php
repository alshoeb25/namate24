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
            ->subject('New Teacher Interest in Your Requirement')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('A teacher is interested in your tuition requirement.')
            ->line('Teacher: ' . $this->teacher->name)
            ->line('Subject: ' . $this->enquiry->subject)
            ->action('View Details', url('/student/requirements/' . $this->enquiry->id))
            ->line('Thank you for using our platform!');
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
            'teacher_id' => $this->teacher->id,
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
