<?php

namespace App\Notifications;

use App\Models\TutorRefundRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RefundRejectedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private TutorRefundRequest $refundRequest)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast', 'mail'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'refund_rejected',
            'title' => '⚠️ Refund Request Denied',
            'message' => "Your refund request for enquiry #{$this->refundRequest->enquiry_id} has been reviewed and denied.",
            'action_url' => '/teacher/refunds',
            'refund_request_id' => $this->refundRequest->id,
            'enquiry_id' => $this->refundRequest->enquiry_id,
            'reason_for_request' => $this->refundRequest->getReasonLabel(),
            'admin_notes' => $this->refundRequest->admin_notes,
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new \Illuminate\Notifications\Messages\BroadcastMessage([
            'type' => 'refund_rejected',
            'title' => '⚠️ Refund Denied',
        ]);
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Refund Request Status - Denied')
            ->greeting('Hello, ' . $notifiable->name . '!')
            ->line('Your refund request has been reviewed.')
            ->line('**Status:** Not Approved')
            ->line('**Reason Requested:** ' . $this->refundRequest->getReasonLabel())
            ->when($this->refundRequest->admin_notes, function ($message) {
                return $message->line('**Admin Notes:** ' . $this->refundRequest->admin_notes);
            })
            ->line('If you believe this decision is incorrect, please contact our support team.')
            ->action('View Request Details', url('/teacher/refunds/' . $this->refundRequest->id))
            ->line('Thank you for using Namate24!');
    }
}
