<?php

namespace App\Jobs;

use App\Models\ReferralInvite;
use App\Mail\ReferralInvitationMail;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendReferralInviteEmail
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public ReferralInvite $referralInvite,
        public bool $isRetry = false
    ) {
        // Remove queue configuration for synchronous execution
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Refresh to get latest data
            $this->referralInvite->refresh();

            // Send the email synchronously
            Mail::to($this->referralInvite->email)
                ->send(new ReferralInvitationMail($this->referralInvite));

            // Mark as sent on success
            $this->referralInvite->update([
                'email_status' => 'sent',
                'email_error' => null,
            ]);

            Log::info("Referral invite email sent to {$this->referralInvite->email}");
        } catch (\Throwable $e) {
            // Mark as failed with error message
            $this->referralInvite->update([
                'email_status' => 'failed',
                'email_error' => substr($e->getMessage(), 0, 255),
            ]);

            Log::error("Failed to send referral invite email to {$this->referralInvite->email}: " . $e->getMessage());
        }
    }


    /**
     * Get the tags that should be assigned to the job.
     */
    public function tags(): array
    {
        return ['referral-invite', 'email', $this->referralInvite->email];
    }
}
