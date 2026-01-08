<?php

namespace App\Notifications;

use App\Models\TutorRefundRequest;
use App\Models\WalletTransaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RefundApprovedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private TutorRefundRequest $refundRequest,
        private WalletTransaction $transaction
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast', 'mail'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'refund_approved',
            'title' => '💰 Refund Approved',
            'message' => "Your refund request has been approved! {$this->refundRequest->refund_amount} coins credited to your wallet.",
            'action_url' => '/teacher/wallet',
            'refund_request_id' => $this->refundRequest->id,
            'enquiry_id' => $this->refundRequest->enquiry_id,
            'amount' => $this->refundRequest->refund_amount,
            'reason' => $this->refundRequest->getReasonLabel(),
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new \Illuminate\Notifications\Messages\BroadcastMessage([
            'type' => 'refund_approved',
            'title' => '💰 Refund Approved',
            'amount' => $this->refundRequest->refund_amount,
        ]);
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Refund Approved - ' . $this->refundRequest->refund_amount . ' Coins Credited')
            ->greeting('Great news, ' . $notifiable->name . '!')
            ->line('Your refund request has been approved by our team.')
            ->line('**Amount:** ' . $this->refundRequest->refund_amount . ' coins')
            ->line('**Reason:** ' . $this->refundRequest->getReasonLabel())
            ->line('**Enquiry:** ' . ($this->refundRequest->enquiry?->student_name ?? 'N/A'))
            ->line('The coins have been credited to your wallet and are ready to use.')
            ->action('View Wallet', url('/teacher/wallet'))
            ->line('Thank you for your understanding!');
    }
}
