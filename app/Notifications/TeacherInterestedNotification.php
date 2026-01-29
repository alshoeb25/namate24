<?php

namespace App\Notifications;

use App\Models\StudentRequirement;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TeacherInterestedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $enquiry;
    public $teacher;

    /**
     * Create a new notification instance.
     */
    public function __construct(StudentRequirement $enquiry, User $teacher)
    {
        $this->enquiry = $enquiry;
        $this->teacher = $teacher;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
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
    public function toMail(object $notifiable): MailMessage
    {
        $subjects = collect($this->enquiry->subjects ?? [])->pluck('name')->implode(', ');
        $subjectLabel = $subjects !== ''
            ? $subjects
            : ($this->enquiry->subject_name ?? $this->enquiry->other_subject ?? 'subjects');
        $unlockCoins = $this->enquiry->unlock_price ?? config('enquiry.unlock_fee');

        $message = (new MailMessage)
            ->subject('New Tutor Interest in Your Requirement')
            ->greeting('Hello ' . ($notifiable->name ?? 'Student') . '!')
            ->line('A tutor has unlocked your requirement and is interested in teaching you.')
            ->line('**Tutor:** ' . ($this->teacher->name ?? 'Tutor'))
            ->line('**Subjects:** ' . $subjectLabel)
            ->line('**Lead status:** ' . ($this->enquiry->current_leads ?? 0) . '/' . ($this->enquiry->max_leads ?? 0) . ' tutors');

        if (!empty($this->teacher->email)) {
            $message->line('**Tutor Email:** ' . $this->teacher->email);
        }

        if (!empty($this->teacher->phone)) {
            $message->line('**Tutor Phone:** ' . $this->teacher->phone);
        }

        if (!empty($unlockCoins)) {
            $message->line('**Unlock Coins:** ' . $unlockCoins . ' coins');
        }

        $message->action('View Interested Tutors', url('/student/requirements'))
            ->line('Please review the tutor profile and respond if interested.');

        return $message;
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        $subjects = collect($this->enquiry->subjects ?? [])->pluck('name')->implode(', ');
        $subjectLabel = $subjects !== ''
            ? $subjects
            : ($this->enquiry->subject_name ?? $this->enquiry->other_subject ?? 'subjects');

        return [
            'type' => 'teacher_interested',
            'title' => 'New Teacher Interest',
            'message' => "{$this->teacher->name} is interested in your {$subjectLabel} requirement.",
            'enquiry_id' => $this->enquiry->id,
            'tutor_id' => $this->teacher->id,
            'teacher_name' => $this->teacher->name,
            'subject' => $subjectLabel,
            'unlock_price' => $this->enquiry->unlock_price ?? config('enquiry.unlock_fee'),
            'current_leads' => $this->enquiry->current_leads,
            'max_leads' => $this->enquiry->max_leads,
        ];
    }

    /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}
