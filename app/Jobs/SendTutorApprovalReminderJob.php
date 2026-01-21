<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendTutorApprovalReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private string $email,
        private string $name,
        private array $missingItems,
        private string $link
    ) {}

    public function handle(): void
    {
        $missingList = '';
        foreach ($this->missingItems as $item) {
            $missingList .= '<li style="margin-bottom:6px;">' . e($item) . '</li>';
        }

        $html = <<<HTML
            <div style="font-family: Arial, sans-serif; color: #111; line-height: 1.6;">
                <p style="font-size:16px;">Hi <strong>{$this->name}</strong>,</p>
                <p style="font-size:15px;">We reviewed your tutor profile. Please complete the following items so we can approve your account:</p>
                <ul style="padding-left:18px; font-size:15px;">{$missingList}</ul>
                <p style="margin:18px 0;">
                    <a href="{$this->link}" style="background:#2563eb; color:#fff; padding:10px 16px; text-decoration:none; border-radius:6px; font-weight:600;">Update Profile</a>
                </p>
                <p style="font-size:14px; color:#444;">If the button does not work, copy and paste this link into your browser:<br><a href="{$this->link}">{$this->link}</a></p>
                <p style="font-size:14px; color:#444;">Thank you,<br>Namate24 Team</p>
            </div>
        HTML;

        Mail::html($html, function ($message) {
            $message->to($this->email)
                ->subject('Complete your tutor profile for approval');
        });
    }
}
