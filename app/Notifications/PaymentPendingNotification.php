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
        $meta = $this->transaction->meta ?? [];
        $currency = $this->order->currency ?? 'INR';
        $currencySymbol = $currency === 'USD' ? '$' : '₹';
        
        return (new MailMessage)
            ->subject('Payment Pending - ' . config('app.name'))
            ->view('emails.payment-pending', [
                'user' => $notifiable,
                'userName' => $notifiable->name,
                'amount' => $this->order->amount,
                'currency' => $currency,
                'currencySymbol' => $currencySymbol,
                'coins' => $this->order->coins ?? 0,
                'orderId' => $this->order->razorpay_order_id,
                'transactionId' => $this->order->razorpay_order_id,
                'initiatedDate' => $this->order->created_at->format('M d, Y h:i A'),
                'paymentMethod' => $meta['payment_method'] ?? 'Razorpay (Online)',
                'estimatedTime' => $meta['estimated_time'] ?? '10-15 minutes',
                'walletUrl' => url(method_exists($notifiable, 'hasRole') && $notifiable->hasRole('tutor')
                    ? '/tutor/wallet'
                    : '/student/wallet'),
                'transactionDate' => $this->order->created_at,
            ]);
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        $walletPath = method_exists($notifiable, 'hasRole') && $notifiable->hasRole('tutor')
            ? '/tutor/wallet'
            : '/student/wallet';

        $currency = $this->order->currency ?? 'INR';
        $currencySymbol = $currency === 'USD' ? '$' : '₹';

        return [
            'type' => 'payment_pending',
            'title' => 'Payment Pending',
            'message' => "Payment of {$currencySymbol}{$this->order->amount} is pending. We're verifying the status.",
            'order_id' => $this->order->id,
            'amount' => $this->order->amount,
            'currency' => $currency,
            'url' => url($walletPath . '?payment=pending&order=' . $this->order->id),
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}
