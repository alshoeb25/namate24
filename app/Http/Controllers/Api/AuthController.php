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
use Illuminate\Support\Facades\Log;
use App\Notifications\LoginSuccessNotification;
use App\Models\UserActivity;
use App\Jobs\RecordLoginActivity;
use App\Jobs\RecordLogoutActivity;

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
            'referral_code' => 'nullable|string|exists:users,referral_code',
        ]);

        // Generate email verification token if email is provided
        $emailVerificationToken = null;
        if ($data['email']) {
            $emailVerificationToken = Str::random(64);
        }

        // Generate unique referral code for new user
        $userReferralCode = $this->generateReferralCode();

        // Get IP address and detect country
        $ip = $request->ip();
        $countryData = $this->detectCountry($ip);

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'] ?? null,
            'phone'    => $data['phone'] ?? null,
            'password' => Hash::make($data['password']),
            'role'     => $data['role'],
            'email_verification_token' => $emailVerificationToken,
            'email_verification_token_expires_at' => $data['email'] ? Carbon::now()->addHours(24) : null,
            'referral_code' => $userReferralCode,
            'country_code' => $countryData['country_code'],
            'country' => $countryData['country'],
            'country_iso' => $countryData['country_iso'],
            'coins' => 0, // Initialize with 0 coins
        ]);

        // Sync role with Spatie roles table
        $user->syncRoles([$data['role']]);

        // Create wallet
        $user->wallet()->create();

        // Create tutor profile if role is tutor
        if ($data['role'] === 'tutor') {
            Tutor::create(['user_id' => $user->id]);
        }

        // Create student profile if role is student
        if ($data['role'] === 'student') {
            \App\Models\Student::create(['user_id' => $user->id]);
        }

        // Process referral code if provided
        $referralReward = null;
        if (!empty($data['referral_code'])) {
            $referralReward = $this->processReferral($user, $data['referral_code']);
        }

        // Send verification email if email provided
        if ($data['email']) {
            $this->sendVerificationEmail($user);
        }

        $message = $data['email'] ? 'Registration successful! Please check your email to verify your account.' : 'Registration successful!';
        if ($referralReward) {
            $message .= " You earned {$referralReward['coins']} coins from the referral!";
        }

        return response()->json([
            'message' => $message,
            'user'    => $user->load('roles'),
            'roles'   => $user->getRoleNames(),
            'email_sent' => (bool) $data['email'],
            'referral_applied' => !empty($data['referral_code']),
            'referral_reward' => $referralReward,
            'coins' => $user->coins,
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

        $user = auth('api')->user()
            ->load(['roles', 'tutor', 'student', 'wallet'])
            ->append('avatar_url');

        // Auto-detect and set country if not defined
        $this->autoDetectCountry($user, $request);

        // Check if email is not verified; resend verification link automatically
        if ($user->email && !$user->email_verified_at) {
            $this->sendVerificationEmail($user);
            return response()->json([
                'message' => 'Please verify your email before logging in. We have sent a fresh verification link to your email.',
                'email_verified' => false,
                'user' => $user,
                'email_sent' => true,
            ], 403);
        }

        // Dispatch job to record login activity asynchronously
        dispatch(new RecordLoginActivity($user->id, $request->ip(), $request->userAgent()));

        $redirectUrl = $this->getRedirectUrl($user);

        // Save notification in DB/mail
        $user->notify(new LoginSuccessNotification());

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
        // Ensure token is present and valid (24h)
        if (!$user->email_verification_token || ($user->email_verification_token_expires_at && Carbon::now()->greaterThan($user->email_verification_token_expires_at))) {
            $user->update([
                'email_verification_token' => Str::random(64),
                'email_verification_token_expires_at' => Carbon::now()->addHours(24),
            ]);
            $user->refresh();
        }

        $verificationUrl = url('/api/email/verify?token=' . $user->email_verification_token);

        try {
            Mail::send('emails.verify-email', [
                'user' => $user,
                'verificationUrl' => $verificationUrl,
            ], function ($mail) use ($user) {
                $mail->to($user->email)
                    ->subject('Verify Your Email - Namate24');
            });
        } catch (\Throwable $e) {
            \Log::error('Email sending failed: ' . $e->getMessage());
        }
    }

    /**
     * Get redirect URL based on user role
     */
    private function getRedirectUrl(User $user): string
    {
        // Admin always goes to admin dashboard
        if ($user->role === 'admin') {
            return route('filament.admin.pages.dashboard');
        }

        // If user has tutor relationship
        if ($user->tutor) {
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
        }

        // If user has student relationship, go to student dashboard
        if ($user->student) {
            return '/student/dashboard';
        }

        // Default home
        return '/';
    }

    public function logout()
    {
        $user = auth('api')->user();
        
        if ($user) {
            // Dispatch job to record logout activity asynchronously
            dispatch(new RecordLogoutActivity($user->id));
        }
        
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

    /**
     * Validate referral code
     */
    public function validateReferralCode(Request $request)
    {
        $request->validate([
            'referral_code' => 'required|string',
        ]);

        $referrer = User::where('referral_code', $request->referral_code)->first();

        if (!$referrer) {
            return response()->json([
                'valid' => false,
                'message' => 'Invalid referral code',
            ], 404);
        }

        return response()->json([
            'valid' => true,
            'message' => 'Valid referral code',
            'referrer' => [
                'name' => $referrer->name,
                'referral_code' => $referrer->referral_code,
            ],
            'reward' => [
                'referrer_coins' => 50,
                'referred_coins' => 25,
            ],
        ]);
    }

    /**
     * Generate unique referral code
     */
    private function generateReferralCode(): string
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (User::where('referral_code', $code)->exists());

        return $code;
    }

    /**
     * Process referral code during registration
     */
    private function processReferral(User $newUser, string $referralCode): ?array
    {
        $referrer = User::where('referral_code', $referralCode)->first();

        if (!$referrer || $referrer->id === $newUser->id) {
            return null;
        }

        try {
            \DB::beginTransaction();

            $referrerCoins = 50; // Coins for referrer
            $referredCoins = 25; // Coins for new user

            // Credit coins to referrer
            $referrer->increment('coins', $referrerCoins);
            
            // Credit coins to new user
            $newUser->increment('coins', $referredCoins);
            $newUser->update(['referred_by' => $referrer->id]);

            // Create referral record
            \App\Models\Referral::create([
                'referrer_id' => $referrer->id,
                'referred_id' => $newUser->id,
                'referrer_coins' => $referrerCoins,
                'referred_coins' => $referredCoins,
                'reward_given' => true,
                'reward_given_at' => now(),
            ]);

            // Create transaction records
            \App\Models\CoinTransaction::create([
                'user_id' => $referrer->id,
                'type' => 'referral_reward',
                'amount' => $referrerCoins,
                'balance_after' => $referrer->coins,
                'description' => "Referral reward for {$newUser->name}",
                'meta' => ['referred_user_id' => $newUser->id],
            ]);

            \App\Models\CoinTransaction::create([
                'user_id' => $newUser->id,
                'type' => 'referral_bonus',
                'amount' => $referredCoins,
                'balance_after' => $newUser->coins,
                'description' => "Welcome bonus for using {$referrer->name}'s referral code",
                'meta' => ['referrer_user_id' => $referrer->id],
            ]);

            \DB::commit();

            return [
                'coins' => $referredCoins,
                'referrer_name' => $referrer->name,
            ];
        } catch (\Throwable $e) {
            \DB::rollBack();
            \Log::error('Referral processing failed during registration: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Auto-detect and set user's country from IP if not defined
     */
    private function autoDetectCountry(User $user, Request $request): void
    {
        // Check if user already has country information
        if (!empty($user->country_code)) {
            return;
        }

        // Check tutor/student tables for existing country info
        if ($user->tutor && !empty($user->tutor->country)) {
            return;
        }
        if ($user->student && !empty($user->student->country_code)) {
            return;
        }

        // Get IP address
        $ip = $request->ip();
        
        // Skip local/private IPs
        if ($this->isLocalIp($ip)) {
            // Default to India for local development
            $user->update(['country_code' => 'IN']);
            return;
        }

        try {
            // Use ip-api.com for geolocation (free, no API key needed)
            $response = @file_get_contents("http://ip-api.com/json/{$ip}?fields=status,countryCode");
            
            if ($response) {
                $data = json_decode($response, true);
                
                if (isset($data['status']) && $data['status'] === 'success') {
                    $countryCode = $data['countryCode'] ?? null;
                    
                    // Set country code if detected
                    if ($countryCode) {
                        $user->update(['country_code' => $countryCode]);
                        \Log::info('Auto-detected country for user', [
                            'user_id' => $user->id,
                            'country_code' => $countryCode,
                            'ip' => $ip
                        ]);
                        return;
                    }
                }
            }
        } catch (\Throwable $e) {
            \Log::warning('Country detection failed: ' . $e->getMessage());
        }

        // Fallback: Default to India if detection fails
        $user->update(['country_code' => 'IN']);
        \Log::info('Defaulted to India for user', ['user_id' => $user->id]);
    }

    /**
     * Check if IP is local/private
     */
    private function isLocalIp(string $ip): bool
    {
        return in_array($ip, ['127.0.0.1', '::1', 'localhost']) 
            || filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false;
    }

    /**
     * Detect country from IP address with full details
     */
    private function detectCountry(string $ip): array
    {
        $default = [
            'country_code' => 'IN',
            'country' => 'India',
            'country_iso' => 'IN',
        ];

        // Skip local/private IPs
        if ($this->isLocalIp($ip)) {
            return $default;
        }

        try {
            // Use ip-api.com for geolocation (free, no API key needed)
            $response = @file_get_contents("http://ip-api.com/json/{$ip}?fields=status,country,countryCode");
            
            if ($response) {
                $data = json_decode($response, true);
                
                if (isset($data['status']) && $data['status'] === 'success') {
                    $countryCode = $data['countryCode'] ?? null;
                    $country = $data['country'] ?? null;
                    
                    if ($countryCode && $country) {
                        return [
                            'country_code' => strtoupper($countryCode),
                            'country' => $country,
                            'country_iso' => strtoupper($countryCode),
                        ];
                    }
                }
            }
        } catch (\Throwable $e) {
            \Log::warning('Country detection failed: ' . $e->getMessage());
        }

        return $default;
    }

    /**
     * Record user login activity
     */

    /**
     * Get user activities (login/logout history)
     */
    public function getUserActivities(Request $request)
    {
        $user = auth('api')->user();
        
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $limit = $request->get('limit', 50);
        $page = $request->get('page', 1);
        $days = $request->get('days', 30);

        $activities = UserActivity::forUser($user->id)
            ->recent($days)
            ->orderBy('login_time', 'desc')
            ->paginate($limit);

        return response()->json([
            'success' => true,
            'data' => $activities,
        ]);
    }

    /**
     * Get current active session
     */
    public function getCurrentActivity(Request $request)
    {
        $user = auth('api')->user();
        
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $activity = UserActivity::forUser($user->id)
            ->active()
            ->latest('login_time')
            ->first();

        if (!$activity) {
            return response()->json([
                'success' => false,
                'message' => 'No active session',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $activity,
        ]);
    }
}

