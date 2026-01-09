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
        
        $currency = $this->order->currency ?? 'INR';
        $currencySymbol = $currency === 'USD' ? '$' : '₹';

        return (new MailMessage)
            ->subject('Payment Successful - Coins Credited!')
            ->view('emails.payment-success', [
                'user' => $notifiable,
                'userName' => $notifiable->name,
                'amount' => $this->order->amount,
                'currency' => $currency,
                'currencySymbol' => $currencySymbol,
                'coins' => $coins,
                'coinsCredit' => $coins,
                'bonusCoins' => $bonusCoins,
                'totalCoins' => $totalCoins,
                'currentBalance' => $notifiable->coins,
                'invoiceNumber' => $this->invoice->invoice_number,
                'orderId' => $this->order->razorpay_order_id,
                'paymentId' => $this->order->razorpay_payment_id,
                'transactionId' => $this->order->razorpay_payment_id ?? $this->order->razorpay_order_id,
                'paymentDate' => $this->order->created_at->format('M d, Y h:i A'),
                'paymentMethod' => 'Razorpay (Online)',
                'dashboardUrl' => url(method_exists($notifiable, 'hasRole') && $notifiable->hasRole('tutor')
                    ? '/tutor/wallet'
                    : '/student/wallet'),
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
        $meta = $this->transaction->meta ?? [];
        $coins = $meta['coins'] ?? $this->order->coins ?? 0;
        $bonusCoins = $meta['bonus_coins'] ?? $this->order->bonus_coins ?? 0;
        
        $currency = $this->order->currency ?? 'INR';
        $currencySymbol = $currency === 'USD' ? '$' : '₹';

        $walletPath = method_exists($notifiable, 'hasRole') && $notifiable->hasRole('tutor')
            ? '/tutor/wallet'
            : '/student/wallet';

        return [
            'type' => 'payment_success',
            'title' => 'Payment Successful',
            'message' => "{$currencySymbol}{$this->order->amount} payment successful. {$coins}" . 
                ($bonusCoins > 0 ? " + {$bonusCoins} bonus" : '') . " coins credited.",
            'order_id' => $this->order->id,
            'invoice_id' => $this->invoice->id,
            'amount' => $this->order->amount,
            'currency' => $currency,
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
