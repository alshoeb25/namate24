<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PasswordResetMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public User $user, public string $resetUrl, public string $token)
    {
    }

    public function build(): self
    {
        return $this
            ->subject('Reset Your Password')
            ->view('emails.password-reset', [
                'user' => $this->user,
                'resetUrl' => $this->resetUrl,
            ]);
    }
}
