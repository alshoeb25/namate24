<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

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

        // Generate verification token
        $token = Str::random(64);
        $user->update([
            'email_verification_token' => $token,
            'email_verification_token_expires_at' => Carbon::now()->addHours(24),
        ]);

        // Send verification email
        $verificationUrl = url('/verify-email?token=' . $token);
        
        // Send email (using simple approach - you can integrate Laravel Mailable class)
        $subject = 'Verify Your Email - Namate24';
        $message = "
            <p>Hello {$user->name},</p>
            <p>Please verify your email address by clicking the link below:</p>
            <p><a href='{$verificationUrl}' style='background-color: #ec4899; color: white; padding: 10px 20px; text-decoration: none; border-radius: 20px;'>Verify Email</a></p>
            <p>This link expires in 24 hours.</p>
            <p>If you didn't sign up for Namate24, please ignore this email.</p>
        ";

        // Simple mail sending (Laravel default)
        try {
            Mail::html($message, function ($mail) use ($user, $subject) {
                $mail->to($user->email)
                     ->subject($subject);
            });
        } catch (\Exception $e) {
            // Log error but don't fail - in development this might not work
            \Log::error('Email sending failed: ' . $e->getMessage());
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
        $request->validate([
            'token' => 'required|string',
        ]);

        $user = User::where('email_verification_token', $request->token)->first();

        if (!$user) {
            return response()->json(['message' => 'Invalid verification token'], 404);
        }

        // Check if token expired
        if ($user->email_verification_token_expires_at && Carbon::now()->isAfter($user->email_verification_token_expires_at)) {
            return response()->json(['message' => 'Verification token expired'], 400);
        }

        if ($user->email_verified_at) {
            return response()->json(['message' => 'Email already verified'], 400);
        }

        // Mark email as verified
        $user->update([
            'email_verified_at' => Carbon::now(),
            'email_verification_token' => null,
            'email_verification_token_expires_at' => null,
        ]);

        return response()->json([
            'message' => 'Email verified successfully! You can now login.',
            'user' => $user,
        ], 200);
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
}
