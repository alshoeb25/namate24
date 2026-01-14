<?php

namespace App\Jobs;

use App\Mail\ContactSubmissionNotification;
use App\Models\ContactSubmission;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendContactSubmissionEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $contactSubmission;

    public function __construct(ContactSubmission $contactSubmission)
    {
        $this->contactSubmission = $contactSubmission;
    }

    public function handle()
    {
        try {
            $adminEmail = config('mail.admin_address', env('ADMIN_EMAIL', config('mail.from.address')));
            
            if ($adminEmail) {
                Log::info('Sending contact submission email', [
                    'submission_id' => $this->contactSubmission->id,
                    'to' => $adminEmail,
                ]);

                Mail::to($adminEmail)->send(new ContactSubmissionNotification($this->contactSubmission));

                Log::info('Contact submission email sent successfully', [
                    'submission_id' => $this->contactSubmission->id,
                ]);
            } else {
                Log::warning('Admin email not configured for contact submission', [
                    'submission_id' => $this->contactSubmission->id,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to send contact submission email', [
                'submission_id' => $this->contactSubmission->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
