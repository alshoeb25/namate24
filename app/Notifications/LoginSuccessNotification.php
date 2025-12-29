<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;

class LoginSuccessNotification extends Notification implements ShouldBroadcastNow
{

    /**
     * Create a new notification instance.
     */
    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * Single source of truth for notification data
     */
    protected function payload(): array
    {
        return [
            'title' => 'Login Successful',
            'message' => 'You have successfully logged in.',
            'type' => 'login',
            'time' => now()->toDateTimeString(),
        ];
    }
    /**
     * Stored in database
     */
    public function toArray(object $notifiable): array
    {
        return $this->payload();
    }

    /**
     * Broadcasted via Pusher
     */
    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->payload());
    }
}
