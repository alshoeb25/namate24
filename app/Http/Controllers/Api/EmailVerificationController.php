<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class EmailVerificationController extends Controller
{
    /**
     * Send verification email with token link
     */
    public function sendVerificationEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        if ($user->email_verified_at) {
            return response()->json(['message' => 'Email already verified'], 400);
        }

        // Generate (or refresh) verification token and expiry
        $token = Str::random(64);
        $user->update([
            'email_verification_token' => $token,
            'email_verification_token_expires_at' => Carbon::now()->addHours(24),
        ]);

        // Build backend verification URL (clicking this will verify and then redirect to frontend)
        $verificationUrl = url('/api/email/verify?token=' . $token);

        try {
            Mail::send('emails.verify-email', [
                'user' => $user,
                'verificationUrl' => $verificationUrl,
            ], function ($mail) use ($user) {
                $mail->to($user->email)
                    ->subject('Verify Your Email - Namate24');
            });
        } catch (\Throwable $e) {
            Log::error('Email sending failed: ' . $e->getMessage());
        }

        return response()->json([
            'message' => 'Verification email sent. Please check your inbox.',
        ], 200);
    }

    /**
     * Verify email token
     */
    public function verifyEmail(Request $request)
    {
        $token = $request->get('token');

        if (!$token) {
            return $this->verificationResponse(false, 'Verification token missing');
        }

        $user = User::where('email_verification_token', $token)->first();

        if (!$user) {
            return $this->verificationResponse(false, 'Invalid verification token');
        }

        // Check if token expired
        if ($user->email_verification_token_expires_at && Carbon::now()->isAfter($user->email_verification_token_expires_at)) {
            return $this->verificationResponse(false, 'Verification token expired');
        }

        if ($user->email_verified_at) {
            return $this->verificationResponse(true, 'Email already verified');
        }

        // Mark email as verified
        $user->update([
            'email_verified_at' => Carbon::now(),
            'email_verification_token' => null,
            'email_verification_token_expires_at' => null,
        ]);

        return $this->verificationResponse(true, 'Email verified successfully! You can now login.', $user);
    }

    /**
     * Resend verification email
     */
    public function resendVerificationEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        if ($user->email_verified_at) {
            return response()->json(['message' => 'Email already verified'], 400);
        }

        return $this->sendVerificationEmail($request);
    }

    /**
     * Respond to verification based on client (JSON vs redirect)
     */
    private function verificationResponse(bool $success, string $message, ?User $user = null)
    {
        // If request expects JSON (API/AJAX), respond with JSON
        if (request()->expectsJson()) {
            $status = $success ? 200 : 400;
            return response()->json([
                'success' => $success,
                'message' => $message,
                'user' => $user,
            ], $status);
        }

        // Otherwise redirect to frontend with status
        $frontend = rtrim(config('app.frontend_url', '/'), '/');
        $target = $frontend . '/email-verified?status=' . ($success ? 'success' : 'failed');
        if (!$success) {
            $target .= '&reason=' . urlencode($message);
        }

        return redirect()->away($target);
    }
}
