<?php

namespace App\Notifications;

use App\Models\StudentRequirement;
use App\Models\Tutor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LeadTakenNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $enquiry;
    public $hiredTeacher;

    /**
     * Create a new notification instance.
     */
    public function __construct(StudentRequirement $enquiry, Tutor $hiredTeacher)
    {
        $this->enquiry = $enquiry;
        $this->hiredTeacher = $hiredTeacher;
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
        return (new MailMessage)
            ->subject('Lead Update: Position Filled')
            ->greeting('Hello, ' . $notifiable->name . '!')
            ->line('The tutoring lead you were interested in has been filled.')
            ->line('Student: ' . $this->enquiry->student_name)
            ->line('Approached Teacher: ' . ($this->approachedTeacher->user->name ?? 'Tutor'))
            ->line('Your coins have been returned to your account since you did not get this lead.')
            ->action('View Other Opportunities', url('/tutor-jobs'))
            ->line('Keep an eye out for more opportunities!');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'lead_taken',
            'title' => 'Lead Position Filled',
            'message' => "The lead for {$this->enquiry->student_name} has been filled by " . ($this->approachedTeacher->user->name ?? 'a tutor') . '.',
            'enquiry_id' => $this->enquiry->id,
            'approached_teacher_id' => $this->approachedTeacher->id,
            'approached_teacher_name' => $this->approachedTeacher->user->name ?? null,
        ];
    }

    /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'type' => 'lead_taken',
            'title' => 'Lead Position Filled',
            'message' => "The lead for {$this->enquiry->student_name} has been filled.",
            'enquiry_id' => $this->enquiry->id,
            'approached_teacher_id' => $this->approachedTeacher->id,
        ]);
    }
}
