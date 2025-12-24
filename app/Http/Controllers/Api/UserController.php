<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Tutor;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    /**
     * Get authenticated user with relationships
     */
    public function getUser(Request $request)
    {
        $user = $request->user()->load(['tutor', 'student', 'wallet', 'roles']);
        
        return response()->json($user);
    }

    /**
     * Enroll user as teacher/tutor
     */
    public function enrollAsTeacher(Request $request)
    {
        $user = $request->user();

        // Check if already enrolled as tutor
        if ($user->tutor) {
            return response()->json([
                'message' => 'You are already enrolled as a teacher.',
                'tutor' => $user->tutor
            ], 400);
        }

        // Create tutor record
        $tutor = Tutor::create([
            'user_id' => $user->id,
        ]);

        // Assign tutor role if not already assigned
        if (!$user->hasRole('tutor')) {
            $user->assignRole('tutor');
        }

        return response()->json([
            'message' => 'Successfully enrolled as teacher!',
            'tutor' => $tutor
        ], 201);
    }

    /**
     * Enroll user as student
     */
    public function enrollAsStudent(Request $request)
    {
        $user = $request->user();

        // Check if already enrolled as student
        if ($user->student) {
            return response()->json([
                'message' => 'You are already enrolled as a student.',
                'student' => $user->student
            ], 400);
        }

        // Create student record
        $student = Student::create([
            'user_id' => $user->id,
        ]);

        // Assign student role if not already assigned
        if (!$user->hasRole('student')) {
            $user->assignRole('student');
        }

        return response()->json([
            'message' => 'Successfully enrolled as student!',
            'student' => $student
        ], 201);
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'phone' => 'sometimes|string|unique:users,phone,' . $user->id,
               'country_code' => 'sometimes|string|max:10',
        ]);

        // If email is being changed, generate verification token
        if (isset($data['email']) && $data['email'] !== $user->email) {
            $data['email_verification_token'] = Str::random(64);
            $data['email_verification_token_expires_at'] = Carbon::now()->addHours(24);
            $data['email_verified_at'] = null;

            // Send verification email
            $this->sendVerificationEmail($user, $data['email'], $data['email_verification_token']);
            
            $emailChanged = true;
        }

        $user->update($data);

        return response()->json([
            'message' => isset($emailChanged) ? 'Profile updated. Please verify your new email.' : 'Profile updated successfully.',
            'user' => $user->fresh()->load(['tutor', 'student', 'wallet', 'roles']),
            'email_verification_required' => isset($emailChanged)
        ]);
    }

    /**
     * Upload profile photo
     */
    public function uploadPhoto(Request $request)
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user = $request->user();

        // Delete old photo if exists
        if ($user->avatar && !str_starts_with($user->avatar, 'http')) {
            Storage::disk('public')->delete($user->avatar);
        }

        // Store new photo
        $path = $request->file('photo')->store('avatars', 'public');

        $user->update(['avatar' => $path]);

        return response()->json([
            'message' => 'Photo uploaded successfully.',
            'avatar' => $path,
            'avatar_url' => Storage::url($path)
        ]);
    }

    /**
     * Send phone OTP
     */
    public function sendPhoneOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|unique:users,phone,' . $request->user()->id,
        ]);

        $user = $request->user();
        $otp = rand(100000, 999999);

        $user->update([
            'phone_otp' => $otp,
            'phone_otp_expires_at' => Carbon::now()->addMinutes(10),
        ]);

        // TODO: Send OTP via SMS service (Twilio, MSG91, etc.)
        // For now, return OTP in response (only for development)
        return response()->json([
            'message' => 'OTP sent successfully.',
            'otp' => config('app.debug') ? $otp : null, // Only show in debug mode
        ]);
    }

    /**
     * Verify phone OTP
     */
    public function verifyPhoneOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'otp' => 'required|string|size:6',
        ]);

        $user = $request->user();

        if ($user->phone_otp !== $request->otp) {
            return response()->json([
                'message' => 'Invalid OTP.'
            ], 422);
        }

        if (Carbon::now()->greaterThan($user->phone_otp_expires_at)) {
            return response()->json([
                'message' => 'OTP has expired.'
            ], 422);
        }

        $user->update([
            'phone' => $request->phone,
            'phone_verified_at' => Carbon::now(),
            'phone_otp' => null,
            'phone_otp_expires_at' => null,
        ]);

        return response()->json([
            'message' => 'Phone verified successfully.',
            'user' => $user->fresh()->load(['tutor', 'student', 'wallet', 'roles'])
        ]);
    }

    /**
     * Send email verification (public endpoint)
     */
    public function sendEmailVerification(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = $request->user();
        $email = $request->email;

        // Generate new token
        $token = Str::random(64);
        
        $user->update([
            'email_verification_token' => $token,
            'email_verification_token_expires_at' => Carbon::now()->addHours(24),
        ]);

        // Send verification email
        $this->sendVerificationEmail($user, $email, $token);

        return response()->json([
            'message' => 'Verification email sent successfully.',
        ]);
    }

    /**
     * Send email verification (private helper)
     */
    private function sendVerificationEmail(User $user, string $email, string $token)
    {
        $verificationUrl = config('app.frontend_url') . '/verify-email?token=' . $token;
        
        $subject = 'Verify Your New Email - Namate24';
        $message = "
            <p>Hello {$user->name},</p>
            <p>You have requested to change your email address.</p>
            <p>Please verify your new email address by clicking the link below:</p>
            <p><a href='{$verificationUrl}' style='background-color: #ec4899; color: white; padding: 10px 20px; text-decoration: none; border-radius: 20px; display: inline-block;'>Verify Email</a></p>
            <p>This link expires in 24 hours.</p>
            <p>If you didn't request this change, please contact support immediately.</p>
        ";

        try {
            Mail::html($message, function ($mail) use ($email, $subject) {
                $mail->to($email)
                     ->subject($subject);
            });
        } catch (\Exception $e) {
            \Log::error('Email sending failed: ' . $e->getMessage());
        }
    }

    /**
     * Update user location
     */
    public function updateLocation(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'city' => 'required|string|max:255',
            'area' => 'required|string|max:255',
            'address' => 'nullable|string|max:500',
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
        ]);

        // Update tutor profile if exists
        if ($user->tutor) {
            $user->tutor->update([
                'city' => $data['city'],
                'area' => $data['area'],
                'address' => $data['address'] ?? null,
                'lat' => $data['lat'] ?? null,
                'lng' => $data['lng'] ?? null,
            ]);
        }

        // Update student profile if exists
        if ($user->student) {
            $user->student->update([
                'city' => $data['city'],
                'area' => $data['area'],
                'address' => $data['address'] ?? null,
                'lat' => $data['lat'] ?? null,
                'lng' => $data['lng'] ?? null,
            ]);
        }

        return response()->json([
            'message' => 'Location updated successfully.',
            'user' => $user->fresh()->load(['tutor', 'student', 'wallet', 'roles'])
        ]);
    }
}
