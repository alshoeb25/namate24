<?php

namespace App\Notifications;

use App\Models\Order;
use App\Models\CoinTransaction;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class PaymentFailedNotification extends Notification implements ShouldBroadcastNow
{
    

    public $order;
    public $transaction;
    public $reason;

    /**
     * Create a new notification instance.
     */
    public function __construct(Order $order, CoinTransaction $transaction, $reason = null)
    {
        $this->order = $order;
        $this->transaction = $transaction;
        $this->reason = $reason ?? 'Payment could not be processed';
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
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
    public function toMail($notifiable): MailMessage
    {
        $walletPath = method_exists($notifiable, 'hasRole') && $notifiable->hasRole('tutor')
            ? '/tutor/wallet'
            : '/student/wallet';

        return (new MailMessage)
            ->subject('Payment Failed')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('We were unable to process your payment.')
            ->line('**Transaction Details:**')
            ->line('Amount: ₹' . number_format($this->order->amount, 2))
            ->line('Order ID: ' . $this->order->razorpay_order_id)
            ->line('Reason: ' . $this->reason)
            ->line('Don\'t worry! You can retry the payment anytime.')
            ->action('Retry Payment', url($walletPath . '?payment=failed&retry=' . $this->order->id))
            ->line('If you continue to face issues, please contact our support team.');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        $walletPath = method_exists($notifiable, 'hasRole') && $notifiable->hasRole('tutor')
            ? '/tutor/wallet'
            : '/student/wallet';

        return [
            'type' => 'payment_failed',
            'title' => 'Payment Failed',
            'message' => "Payment of ₹{$this->order->amount} failed. {$this->reason}",
            'order_id' => $this->order->id,
            'amount' => $this->order->amount,
            'reason' => $this->reason,
            'can_retry' => true,
            'url' => url($walletPath . '?payment=failed&order=' . $this->order->id),
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}
