<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use App\Models\User;
use App\Models\Order;

class SubscriptionPendingNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $user;
    protected $order;

    public function __construct(User $user, Order $order)
    {
        $this->user = $user;
        $this->order = $order;
    }

    public function via($notifiable)
    {
        return ['mail', 'database', 'broadcast'];
    }

    public function toMail($notifiable)
    {
        return (new \Illuminate\Notifications\Messages\MailMessage)
            ->subject('Subscription Payment Pending')
            ->greeting('Hello ' . $this->user->first_name . '!')
            ->line('Your subscription payment is currently being processed.')
            ->line('**Plan:** ' . $this->order->metadata['plan_name'])
            ->line('**Amount:** ' . $this->order->currency . ' ' . number_format($this->order->amount / 100, 2))
            ->line('**Order ID:** ' . $this->order->id)
            ->line('We will notify you once the payment is completed. This usually takes a few minutes.')
            ->action('Check Status', config('app.frontend_url') . '/subscriptions/status')
            ->salutation('Best regards,
Namate24 Team');
    }

    public function toDatabase($notifiable)
    {
        return [
            'title' => 'Subscription Payment Pending',
            'message' => 'Your payment for ' . $this->order->metadata['plan_name'] . ' is being processed.',
            'type' => 'subscription_pending',
            'order_id' => $this->order->id,
            'amount' => $this->order->amount,
            'currency' => $this->order->currency,
            'action_url' => '/subscriptions/status',
            'action_text' => 'Check Status',
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new \Illuminate\Notifications\Messages\BroadcastMessage([
            'title' => 'Subscription Payment Pending',
            'message' => 'Your payment for ' . $this->order->metadata['plan_name'] . ' is being processed.',
            'type' => 'subscription_pending',
            'order_id' => $this->order->id,
            'amount' => $this->order->amount,
            'currency' => $this->order->currency,
        ]);
    }
}
