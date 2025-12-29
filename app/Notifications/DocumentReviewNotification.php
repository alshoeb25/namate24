<?php

namespace App\Notifications;

use App\Models\TutorDocument;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DocumentReviewNotification extends Notification implements ShouldBroadcastNow
{
    public function __construct(
        public TutorDocument $document,
        public string $status,
        public ?string $reason = null
    ) {}

    public function via($notifiable): array
    {
        $channels = ['database', 'broadcast'];
        if (!empty($notifiable->email)) {
            $channels[] = 'mail';
        }
        return $channels;
    }

    public function toArray($notifiable): array
    {
        $title = $this->status === 'approved' ? 'Document Approved' : 'Document Rejected';
        $message = $this->status === 'approved'
            ? 'Your document has been approved.'
            : ('Your document was rejected' . ($this->reason ? ": {$this->reason}" : '.'));

        return [
            'type' => 'document_review',
            'title' => $title,
            'message' => $message,
            'status' => $this->status,
            'document_id' => $this->document->id,
            'document_type' => $this->document->document_type,
            'url' => url('/tutor/documents'),
            'time' => now()->toDateTimeString(),
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }

    public function toMail($notifiable): MailMessage
    {
        $title = $this->status === 'approved' ? 'Document Approved' : 'Document Rejected';
        $mail = (new MailMessage)
            ->subject($title)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line($this->toArray($notifiable)['message'])
            ->action('View Documents', url('/tutor/profile?tab=documents'));

        return $mail;
    }
}
