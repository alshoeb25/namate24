<?php

namespace App\Notifications;

use App\Models\StudentRequirement;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EnquiryFullNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $enquiry;

    /**
     * Create a new notification instance.
     */
    public function __construct(StudentRequirement $enquiry)
    {
        $this->enquiry = $enquiry;
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
            ->subject('Your Requirement Has Received Maximum Leads')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Your tuition requirement has now received the maximum number of teacher responses.')
            ->line('Subject: ' . $this->enquiry->subject)
            ->line('Total Leads: ' . $this->enquiry->current_leads . ' / ' . $this->enquiry->max_leads)
            ->action('View Interested Teachers', url('/student/requirements/' . $this->enquiry->id))
            ->line('You can now review and select the best teacher for your needs.');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'enquiry_full',
            'title' => 'Requirement Leads Full',
            'message' => "Your {$this->enquiry->subject} requirement has received {$this->enquiry->max_leads} teacher responses.",
            'enquiry_id' => $this->enquiry->id,
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
