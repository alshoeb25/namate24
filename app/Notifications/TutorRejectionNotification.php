<?php

namespace App\Notifications;

use App\Models\Tutor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TutorRejectionNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Tutor $tutor,
        public string $rejectionReason = ''
    ) {}

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $message = (new MailMessage)
            ->subject('Your Tutor Profile Review')
            ->greeting('Hello ' . $notifiable->name)
            ->line('Thank you for submitting your tutor profile for review.')
            ->line('Unfortunately, your profile was not approved at this time.');

        if ($this->rejectionReason) {
            $message->line('**Reason for Rejection:**')
                ->line($this->rejectionReason);
        }

        $message->line('Please review the feedback and update your profile accordingly before resubmitting.')
            ->action('Update Your Profile', url('/tutor/profile'))
            ->line('If you have any questions, please contact our support team.')
            ->line('Thank you!');

        return $message;
    }

    public function toDatabase($notifiable)
    {
        return [
            'title' => 'Profile Review',
            'message' => 'Your tutor profile was not approved. ' . ($this->rejectionReason ? 'Reason: ' . $this->rejectionReason : ''),
            'type' => 'rejection',
            'tutor_id' => $this->tutor->id,
            'rejection_reason' => $this->rejectionReason,
        ];
    }
}
