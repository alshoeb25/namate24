<?php

namespace App\Notifications;

use App\Models\Tutor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TutorApprovalNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Tutor $tutor
    ) {}

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Your Tutor Profile Has Been Approved!')
            ->greeting('Hello ' . $notifiable->name)
            ->line('Congratulations! Your tutor profile has been reviewed and approved.')
            ->line('You can now start accepting student enquiries and tutoring requests.')
            ->line('Your profile is now live on the platform.')
            ->action('View Your Profile', url('/tutor/profile'))
            ->line('Thank you for joining Namate24!');
    }

    public function toDatabase($notifiable)
    {
        return [
            'title' => 'Profile Approved',
            'message' => 'Your tutor profile has been approved and is now live.',
            'type' => 'approval',
            'tutor_id' => $this->tutor->id,
        ];
    }
}
