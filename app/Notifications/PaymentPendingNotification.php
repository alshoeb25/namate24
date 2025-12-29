<?php

namespace App\Notifications;

use App\Models\Order;
use App\Models\CoinTransaction;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class PaymentPendingNotification extends Notification implements ShouldBroadcastNow
{
    

    public $order;
    public $transaction;

    /**
     * Create a new notification instance.
     */
    public function __construct(Order $order, CoinTransaction $transaction)
    {
        $this->order = $order;
        $this->transaction = $transaction;
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
            ->subject('Payment Pending - We\'re Checking Status')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Your payment is currently pending.')
            ->line('**Transaction Details:**')
            ->line('Amount: ₹' . number_format($this->order->amount, 2))
            ->line('Order ID: ' . $this->order->razorpay_order_id)
            ->line('We are verifying your payment status with the payment gateway.')
            ->line('This usually takes 10-15 minutes. We will notify you once the payment is confirmed.')
            ->line('If your payment was successful, coins will be automatically credited to your wallet.')
            ->action('Check Status', url($walletPath . '?payment=pending&order=' . $this->order->id))
            ->line('If you have any concerns, please contact our support team.');
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
            'type' => 'payment_pending',
            'title' => 'Payment Pending',
            'message' => "Payment of ₹{$this->order->amount} is pending. We're verifying the status.",
            'order_id' => $this->order->id,
            'amount' => $this->order->amount,
            'url' => url($walletPath . '?payment=pending&order=' . $this->order->id),
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}
