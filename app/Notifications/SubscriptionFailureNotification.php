<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Channels\MailChannel;
use Illuminate\Notifications\Notification;
use App\Models\User;
use App\Models\Order;

class SubscriptionFailureNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $user;
    protected $order;
    protected $errorMessage;
    protected $errorReason;

    public function __construct(User $user, Order $order, string $errorMessage = 'Payment processing failed', string $errorReason = 'unknown')
    {
        $this->user = $user;
        $this->order = $order;
        $this->errorMessage = $errorMessage;
        $this->errorReason = $errorReason;
    }

    public function via($notifiable)
    {
        return ['mail', 'database', 'broadcast'];
    }

    public function toMail($notifiable)
    {
        return (new \Illuminate\Notifications\Messages\MailMessage)
            ->subject('Subscription Payment Failed')
            ->greeting('Hello ' . $this->user->first_name . '!')
            ->line('Unfortunately, your subscription payment could not be processed.')
            ->line('**Error:** ' . $this->errorMessage)
            ->line('**Order ID:** ' . $this->order->id)
            ->action('Retry Payment', config('app.frontend_url') . '/subscriptions?retry=' . $this->order->id)
            ->line('If you continue to experience issues, please contact our support team.')
            ->salutation('Best regards,
Namate24 Team');
    }

    public function toDatabase($notifiable)
    {
        return [
            'title' => 'Subscription Payment Failed',
            'message' => 'Your subscription payment for ' . $this->order->metadata['plan_name'] . ' failed to process.',
            'type' => 'subscription_failure',
            'order_id' => $this->order->id,
            'amount' => $this->order->amount,
            'currency' => $this->order->currency,
            'error_message' => $this->errorMessage,
            'error_reason' => $this->errorReason,
            'action_url' => '/subscriptions?retry=' . $this->order->id,
            'action_text' => 'Retry Payment',
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new \Illuminate\Notifications\Messages\BroadcastMessage([
            'title' => 'Subscription Payment Failed',
            'message' => 'Your subscription payment for ' . $this->order->metadata['plan_name'] . ' failed to process.',
            'type' => 'subscription_failure',
            'order_id' => $this->order->id,
            'amount' => $this->order->amount,
            'currency' => $this->order->currency,
            'error_message' => $this->errorMessage,
            'error_reason' => $this->errorReason,
            'can_retry' => true,
        ]);
    }
}
