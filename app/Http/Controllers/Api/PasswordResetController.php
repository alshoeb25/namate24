<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\PasswordResetMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    /**
     * Send a password reset link if the email exists.
     */
    public function sendResetLink(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $data['email'])->first();

        if (!$user) {
            return response()->json([
                'message' => 'We could not find an account with that email.',
            ], 404);
        }

        $token = Password::createToken($user);

        $resetUrl = rtrim(config('app.frontend_url'), '/') . '/reset-password?token=' . urlencode($token) . '&email=' . urlencode($user->email);

        try {
            Mail::to($user->email)->queue(new PasswordResetMail($user, $resetUrl, $token));
        } catch (\Throwable $e) {
            Log::error('Password reset email failed', [
                'email' => $user->email,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Unable to send reset email right now. Please try again shortly.',
            ], 500);
        }

        return response()->json([
            'message' => 'Password reset email sent. Please check your inbox.',
        ]);
    }

    /**
     * Reset the password using the provided token.
     */
    public function reset(Request $request)
    {
        $data = $request->validate([
            'token' => 'required|string',
            'email' => 'required|email',
            'password' => 'required|min:6|confirmed',
        ]);

        $status = Password::reset(
            $data,
            function (User $user) use ($data) {
                $user->forceFill([
                    'password' => Hash::make($data['password']),
                    'remember_token' => Str::random(60),
                ])->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json([
                'message' => 'Password reset successful. You can now log in.',
            ]);
        }

        $httpStatus = $status === Password::INVALID_USER ? 404 : 422;

        return response()->json([
            'message' => __($status),
        ], $httpStatus);
    }
}
