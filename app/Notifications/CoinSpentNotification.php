<?php

namespace App\Notifications;

use App\Models\CoinTransaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class CoinSpentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private CoinTransaction $transaction)
    {
    }

    public function via(object $notifiable): array
    {
        $channels = ['database', 'broadcast'];

        if (!empty($notifiable->email)) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toMail(object $notifiable)
    {
        $amount = abs($this->transaction->amount);
        $balance = $this->transaction->balance_after;
        $meta = $this->transaction->meta ?? [];

        return (new \Illuminate\Notifications\Messages\MailMessage)
            ->subject('Coins Spent - ' . config('app.name'))
            ->view('emails.coins-spent', [
                'userName' => $notifiable->name,
                'userEmail' => $notifiable->email,
                'transactionId' => $this->transaction->id,
                'coinsSpent' => $amount,
                'description' => $this->transaction->description,
                'transactionType' => $this->transaction->type,
                'currentBalance' => $balance,
                'walletUrl' => url(method_exists($notifiable, 'hasRole') && $notifiable->hasRole('tutor') 
                    ? '/tutor/wallet' 
                    : '/student/wallet'),
                'enquiryId' => $meta['enquiry_id'] ?? null,
                'transactionDate' => $this->transaction->created_at,
            ]);
    }

    public function toArray(object $notifiable): array
    {
        $meta = $this->transaction->meta ?? [];

        return [
            'type' => 'coin_spent',
            'title' => 'Coins Spent',
            'message' => 'You spent ' . abs($this->transaction->amount) . ' coins.',
            'transaction_id' => $this->transaction->id,
            'debit_amount' => abs($this->transaction->amount),
            'balance' => $this->transaction->balance_after,
            'category' => $this->transaction->type,
            'description' => $this->transaction->description,
            'meta' => $meta,
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}
