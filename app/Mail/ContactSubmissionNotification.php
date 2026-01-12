<?php

namespace App\Mail;

use App\Models\ContactSubmission;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactSubmissionNotification extends Mailable
{
    use Queueable, SerializesModels;

    public ContactSubmission $submission;

    public function __construct(ContactSubmission $submission)
    {
        $this->submission = $submission;
    }

    public function build(): self
    {
        return $this
            ->subject('New Contact Submission')
            ->view('emails.contact_submission')
            ->with([
                'submission' => $this->submission,
            ]);
    }
}
