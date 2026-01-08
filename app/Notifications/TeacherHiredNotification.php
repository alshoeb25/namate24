<?php

namespace App\Notifications;

use App\Models\StudentRequirement;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TeacherHiredNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $enquiry;
    public $student;

    /**
     * Create a new notification instance.
     */
    public function __construct(StudentRequirement $enquiry, User $student)
    {
        $this->enquiry = $enquiry;
        $this->student = $student;
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
            ->subject('You Have Been Hired! 🎉')
            ->greeting('Congratulations, ' . $notifiable->name . '!')
            ->line('A student has hired you to teach them.')
            ->line('Student: ' . $this->student->name)
            ->line('Subjects: ' . implode(', ', $this->enquiry->subjects?->pluck('name')->toArray() ?? []))
            ->action('View Details', url('/tutor/dashboard/enquiries/' . $this->enquiry->id))
            ->line('Good luck with your tutoring session!');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'teacher_hired',
            'title' => 'You Have Been Hired! 🎉',
            'message' => "{$this->student->name} has hired you for {$this->enquiry->student_name}'s tutoring session.",
            'enquiry_id' => $this->enquiry->id,
            'student_id' => $this->student->id,
            'student_name' => $this->student->name,
            'student_phone' => $this->student->phone,
        ];
    }

    /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'type' => 'teacher_hired',
            'title' => 'You Have Been Hired!',
            'message' => "{$this->student->name} has hired you.",
            'enquiry_id' => $this->enquiry->id,
            'student_id' => $this->student->id,
        ]);
    }
}
