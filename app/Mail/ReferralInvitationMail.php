<?php

namespace App\Mail;

use App\Models\ReferralInvite;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReferralInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public ReferralInvite $invite;

    public function __construct(ReferralInvite $invite)
    {
        $this->invite = $invite;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your NaMate24 Invitation - Get ' . $this->invite->referred_coins . ' Free Coins',
        );
    }

    public function content(): Content
    {
        $referralCode = $this->invite->referralCode->referral_code ?? '';
        $frontendUrl = config('app.frontend_url', config('app.url'));
        
        return new Content(
            view: 'emails.referral-invitation',
            with: [
                'referralInvite' => $this->invite,
                'email' => $this->invite->email,
                'coins' => $this->invite->referred_coins,
                'referralCode' => $referralCode,
                'referralLink' => $frontendUrl . '/register?ref=' . $referralCode,
                'appName' => config('app.name'),
                'appUrl' => config('app.url'),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
