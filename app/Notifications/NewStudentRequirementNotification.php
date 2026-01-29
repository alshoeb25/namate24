<?php

namespace App\Notifications;

use App\Models\StudentRequirement;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewStudentRequirementNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $requirement;

    /**
     * Create a new notification instance.
     */
    public function __construct(StudentRequirement $requirement)
    {
        $this->requirement = $requirement;
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
        $subjects = collect($this->requirement->subjects ?? [])->pluck('name')->implode(', ');
        $meetingOptionsRaw = $this->requirement->meeting_options;
        $meetingOptions = is_array($meetingOptionsRaw)
            ? implode(', ', array_map('ucfirst', $meetingOptionsRaw))
            : ($meetingOptionsRaw ? ucfirst($meetingOptionsRaw) : 'N/A');

        return (new MailMessage)
            ->subject('New Student Requirement Available')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('A new student requirement has been posted that matches your profile.')
            ->line('**Student:** ' . $this->requirement->student_name)
            ->line('**Subjects:** ' . $subjects)
            ->line('**Location:** ' . $this->requirement->area . ', ' . $this->requirement->city)
            ->line('**Meeting Options:** ' . $meetingOptions)
            ->line('**Budget:** ₹' . $this->requirement->budget_amount . ' per ' . $this->requirement->budget_type)
            ->action('View Requirement', url('/tutor/requirements/' . $this->requirement->id))
            ->line('Respond quickly to increase your chances of being hired!');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        $subjects = collect($this->requirement->subjects ?? [])->pluck('name')->implode(', ');
        $subjectsLabel = $subjects !== '' ? $subjects : 'subjects';
        
        return [
            'type' => 'new_student_requirement',
            'title' => 'New Student Requirement',
            'message' => "New {$subjectsLabel} requirement posted in {$this->requirement->area}, {$this->requirement->city}",
            'requirement_id' => $this->requirement->id,
            'student_name' => $this->requirement->student_name,
            'subjects' => $subjects,
            'city' => $this->requirement->city,
            'area' => $this->requirement->area,
            'meeting_options' => $this->requirement->meeting_options,
            'budget_amount' => $this->requirement->budget_amount,
            'budget_type' => $this->requirement->budget_type,
            'gender_preference' => $this->requirement->gender_preference,
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
