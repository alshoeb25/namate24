<?php

namespace App\Notifications;

use App\Models\Order;
use App\Models\PaymentTransaction;
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
    public function __construct(Order $order, PaymentTransaction $transaction, $reason = null)
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
        $currency = $this->order->currency ?? 'INR';
        $currencySymbol = $currency === 'USD' ? '$' : '₹';
        
        return (new MailMessage)
            ->subject('Payment Failed - ' . config('app.name'))
            ->view('emails.payment-failed', [
                'user' => $notifiable,
                'userName' => $notifiable->name,
                'amount' => $this->order->amount,
                'currency' => $currency,
                'currencySymbol' => $currencySymbol,
                'coins' => $this->order->coins ?? 0,
                'orderId' => $this->order->razorpay_order_id,
                'transactionId' => $this->transaction->razorpay_payment_id ?? $this->order->razorpay_order_id,
                'attemptDate' => $this->order->created_at->format('M d, Y h:i A'),
                'reason' => $this->reason,
                'errorMessage' => $this->reason,
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
            'type' => 'payment_failed',
            'title' => 'Payment Failed',
            'message' => "Payment of {$currencySymbol}{$this->order->amount} failed. {$this->reason}",
            'order_id' => $this->order->id,
            'amount' => $this->order->amount,
            'currency' => $currency,
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
