<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Tutor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'email' => 'nullable|email|unique:users,email',
            'phone' => 'nullable|unique:users,phone',
            'password' => 'required|min:6',
            'role' => ['required', Rule::in(['student', 'tutor', 'admin'])],
        ]);

        // Generate email verification token if email is provided
        $emailVerificationToken = null;
        if ($data['email']) {
            $emailVerificationToken = Str::random(64);
        }

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'] ?? null,
            'phone'    => $data['phone'] ?? null,
            'password' => Hash::make($data['password']),
            'role'     => $data['role'],
            'email_verification_token' => $emailVerificationToken,
            'email_verification_token_expires_at' => $data['email'] ? Carbon::now()->addHours(24) : null,
        ]);

        // Sync role with Spatie roles table
        $user->syncRoles([$data['role']]);

        // Create wallet
        $user->wallet()->create();

        // Create tutor profile if role is tutor
        if ($data['role'] === 'tutor') {
            Tutor::create(['user_id' => $user->id]);
        }

        // Send verification email if email provided
        if ($data['email']) {
            $this->sendVerificationEmail($user);
        }

        return response()->json([
            'message' => $data['email'] ? 'Registration successful! Please check your email to verify your account.' : 'Registration successful!',
            'user'    => $user->load('roles'),
            'roles'   => $user->getRoleNames(),
            'email_sent' => (bool) $data['email'],
        ], 201);
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email'    => 'required_without:phone',
            'phone'    => 'required_without:email',
            'password' => 'required',
        ]);

        $credentials = [
            isset($data['email']) ? 'email' : 'phone' => $data['email'] ?? $data['phone'],
            'password' => $data['password'],
        ];

        if (! $token = auth('api')->attempt($credentials)) {
            return response()->json(['message' => 'Invalid credentials'], 422);
        }

        $user = auth('api')->user()->load('roles');

        // Check if email is not verified
        if ($user->email && !$user->email_verified_at) {
            return response()->json([
                'message' => 'Please verify your email before logging in.',
                'email_verified' => false,
                'user' => $user,
            ], 403);
        }

        $redirectUrl = $this->getRedirectUrl($user);

        return response()->json([
            'user'        => $user,
            'roles'       => $user->getRoleNames(),
            'token'       => $token,
            'token_type'  => 'bearer',
            'expires_in'  => auth('api')->factory()->getTTL() * 60,
            'redirect_url' => $redirectUrl,
            'email_verified' => true,
        ]);
    }

    /**
     * Send verification email
     */
    private function sendVerificationEmail(User $user)
    {
        if (!$user->email) {
            return;
        }

        $verificationUrl = config('app.frontend_url') . '/verify-email?token=' . $user->email_verification_token;
        
        $subject = 'Verify Your Email - Namate24';
        $message = "
            <p>Hello {$user->name},</p>
            <p>Thank you for signing up for Namate24!</p>
            <p>Please verify your email address by clicking the link below:</p>
            <p><a href='{$verificationUrl}' style='background-color: #ec4899; color: white; padding: 10px 20px; text-decoration: none; border-radius: 20px; display: inline-block;'>Verify Email</a></p>
            <p>This link expires in 24 hours.</p>
            <p>If you didn't sign up for Namate24, please ignore this email.</p>
        ";

        try {
            Mail::html($message, function ($mail) use ($user, $subject) {
                $mail->to($user->email)
                     ->subject($subject);
            });
        } catch (\Exception $e) {
            \Log::error('Email sending failed: ' . $e->getMessage());
        }
    }

    /**
     * Get redirect URL based on user role
     */
    private function getRedirectUrl(User $user): string
    {
        if ($user->role === 'tutor') {
            $tutor = $user->tutor;
            $sections = [
                'personal_details' => $user && $user->name && $user->email && $user->phone,
                'photo' => $user && $user->avatar,
                'video' => $tutor && ($tutor->introductory_video || $tutor->youtube_intro_url),
                'subjects' => $tutor && $tutor->subjects()->count() > 0,
                'address' => $tutor && ($tutor->address || $tutor->city),
                'education' => $tutor && $tutor->educations && count($tutor->educations) > 0,
                'experience' => $tutor && $tutor->experiences && count($tutor->experiences) > 0,
                'teaching_details' => $tutor && $tutor->experience_years !== null && $tutor->price_per_hour,
                'description' => $tutor && $tutor->headline && $tutor->about,
                'courses' => $tutor && $tutor->courses && count($tutor->courses) > 0,
            ];

            $completed = count(array_filter($sections));
            $percentage = round(($completed / count($sections)) * 100);

            if ($percentage < 100) {
                return route('tutor.profile.personal-details');
            }

            return route('tutor.profile.dashboard');
        } elseif ($user->role === 'admin') {
            return route('filament.admin.pages.dashboard');
        }

        return '/';
    }

    public function logout()
    {
        auth('api')->logout();
        return response()->json(['message' => 'Logged out']);
    }

    public function refresh()
    {
        return response()->json([
            'token'      => auth('api')->refresh(),
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
        ]);
    }
}

