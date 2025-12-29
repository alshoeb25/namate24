<?php

namespace App\Notifications;

use App\Models\Order;
use App\Models\CoinTransaction;
use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class PaymentSuccessNotification extends Notification implements ShouldBroadcastNow
{

    public $order;
    public $transaction;
    public $invoice;

    /**
     * Create a new notification instance.
     */
    public function __construct(Order $order, CoinTransaction $transaction, Invoice $invoice)
    {
        $this->order = $order;
        $this->transaction = $transaction;
        $this->invoice = $invoice;
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
        $coins = $meta['coins'] ?? $this->order->coins ?? 0;
        $bonusCoins = $meta['bonus_coins'] ?? $this->order->bonus_coins ?? 0;
        $totalCoins = $coins + $bonusCoins;

        $walletPath = method_exists($notifiable, 'hasRole') && $notifiable->hasRole('tutor')
            ? '/tutor/wallet'
            : '/student/wallet';

        return (new MailMessage)
            ->subject('Payment Successful - Coins Credited!')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Your payment has been successfully processed.')
            ->line('**Transaction Details:**')
            ->line('Amount Paid: ₹' . number_format($this->order->amount, 2))
            ->line('Coins Credited: ' . $coins . ($bonusCoins > 0 ? " + {$bonusCoins} bonus coins" : ''))
            ->line('Total Coins: ' . $totalCoins)
            ->line('New Balance: ' . $notifiable->coins . ' coins')
            ->line('Invoice Number: ' . $this->invoice->invoice_number)
            ->line('Order ID: ' . $this->order->razorpay_order_id)
            ->line('Payment ID: ' . $this->order->razorpay_payment_id)
            ->action('View Transaction', url($walletPath . '?payment=success&order=' . $this->order->id))
            ->line('Thank you for your purchase!');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        $meta = $this->transaction->meta ?? [];
        $coins = $meta['coins'] ?? $this->order->coins ?? 0;
        $bonusCoins = $meta['bonus_coins'] ?? $this->order->bonus_coins ?? 0;

        $walletPath = method_exists($notifiable, 'hasRole') && $notifiable->hasRole('tutor')
            ? '/tutor/wallet'
            : '/student/wallet';

        return [
            'type' => 'payment_success',
            'title' => 'Payment Successful',
            'message' => "₹{$this->order->amount} payment successful. {$coins}" . 
                ($bonusCoins > 0 ? " + {$bonusCoins} bonus" : '') . " coins credited.",
            'order_id' => $this->order->id,
            'invoice_id' => $this->invoice->id,
            'amount' => $this->order->amount,
            'coins' => $coins + $bonusCoins,
            'balance' => $notifiable->coins,
            'time' => now()->toDateTimeString(),
            'url' => url($walletPath . '?payment=success&order=' . $this->order->id),
        ];
    }

    /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}
