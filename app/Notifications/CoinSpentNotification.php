<?php

namespace App\Notifications;

use App\Models\CoinTransaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
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

    public function toMail(object $notifiable): MailMessage
    {
        $amount = abs($this->transaction->amount);
        $type = $this->transaction->type;
        $description = $this->transaction->description;
        $balance = $this->transaction->balance_after;

        $mail = (new MailMessage)
            ->subject('Coins Spent')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('You spent ' . $amount . ' coins.')
            ->line('Type: ' . $type)
            ->line('Description: ' . ($description ?: 'Coin debit'))
            ->line('New Balance: ' . $balance . ' coins');

        $meta = $this->transaction->meta ?? [];
        if (!empty($meta['enquiry_id'])) {
            $mail->line('Enquiry ID: ' . $meta['enquiry_id']);
        }

        return $mail
            ->action('View Wallet', url('/wallet'))
            ->line('If this wasn\'t you, please contact support.');
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
