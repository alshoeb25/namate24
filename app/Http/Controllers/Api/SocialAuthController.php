<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Tutor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use App\Helpers\CountryHelper;

class SocialAuthController extends Controller
{
    /**
     * Handle Google OAuth login/signup
     */
    public function googleCallback(Request $request)
    {
        $validated = $request->validate([
            'access_token' => 'required|string',
            'role' => 'nullable|in:student,tutor',
            'email' => 'required|email',
            'name' => 'required|string',
            'picture' => 'nullable|string',
        ]);

        try {
            // Use provided email, name, and picture from the validated token
            $googleUser = [
                'email' => $validated['email'],
                'name' => $validated['name'],
                'picture' => $validated['picture'] ?? null,
            ];

            // Check if user exists by email
            $user = User::where('email', $googleUser['email'])->first();

            if ($user) {
                // User exists - login
                $token = JWTAuth::fromUser($user);
                
                // Update avatar if not set
                if (!$user->avatar && isset($googleUser['picture'])) {
                    $user->update(['avatar' => $googleUser['picture']]);
                }

                // Mark email as verified
                if (!$user->email_verified_at) {
                    $user->update(['email_verified_at' => now()]);
                }

                // Ensure country_code is in dial code format (e.g., +91)
                if (empty($user->country_code) || !str_starts_with($user->country_code, '+')) {
                    $dial = CountryHelper::isoToDialCode(strtoupper((string)($user->country_iso ?? 'IN')));
                    $user->update(['country_code' => $dial]);
                }

            } else {
                // User doesn't exist - create new user
                $role = $validated['role'] ?? 'student';
                
                // Detect country from IP
                $ip = $request->ip();
                $countryData = $this->detectCountry($ip);
                
                $user = User::create([
                    'name' => $googleUser['name'] ?? $googleUser['email'],
                    'email' => $googleUser['email'],
                    'avatar' => $googleUser['picture'] ?? null,
                    'role' => $role,
                    'email_verified_at' => now(), // Auto-verify for Google users
                    'password' => null, // No password for OAuth users
                    'country_code' => $countryData['country_code'],
                    'country' => $countryData['country'],
                    'country_iso' => $countryData['country_iso'],
                ]);

                // Sync role with Spatie
                $user->syncRoles([$role]);

                // Create wallet
                $user->wallet()->create();

                // Create tutor profile if role is tutor
                if ($role === 'tutor') {
                    Tutor::create(['user_id' => $user->id]);
                }

                $token = JWTAuth::fromUser($user);
            }

            $redirectUrl = $this->getRedirectUrl($user);

            return response()->json([
                'user' => $user->load('roles'),
                'roles' => $user->getRoleNames(),
                'token' => $token,
                'token_type' => 'bearer',
                'expires_in' => auth('api')->factory()->getTTL() * 60,
                'redirect_url' => $redirectUrl,
                'is_new_user' => !$user->wasRecentlyCreated,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Google authentication failed',
                'error' => $e->getMessage()
            ], 500);
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
                return '/tutor/profile/personal-details';
            }

            return '/tutor/profile';
        } elseif ($user->role === 'admin') {
            return '/admin';
        }

        return '/';
    }

    /**
     * Detect country from IP address
     */
    private function detectCountry(string $ip): array
    {
        $default = CountryHelper::getCountryData('IN', 'India');

        // Skip local/private IPs
        if ($this->isLocalIp($ip)) {
            return $default;
        }

        try {
            $response = @file_get_contents("http://ip-api.com/json/{$ip}?fields=status,country,countryCode");
            
            if ($response) {
                $data = json_decode($response, true);
                
                if (isset($data['status']) && $data['status'] === 'success') {
                    $isoCode = $data['countryCode'] ?? null;
                    $country = $data['country'] ?? null;
                    
                    if ($isoCode && $country) {
                        return CountryHelper::getCountryData($isoCode, $country);
                    }
                }
            }
        } catch (\Throwable $e) {
            \Log::warning('Country detection failed: ' . $e->getMessage());
        }

        return $default;
    }

    /**
     * Check if IP is local/private
     */
    private function isLocalIp(string $ip): bool
    {
        return in_array($ip, ['127.0.0.1', '::1']) || 
               str_starts_with($ip, '192.168.') || 
               str_starts_with($ip, '10.') || 
               preg_match('/^172\.(1[6-9]|2[0-9]|3[0-1])\./', $ip);
    }
}
