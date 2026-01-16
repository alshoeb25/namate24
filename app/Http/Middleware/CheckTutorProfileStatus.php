<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckTutorProfileStatus
{
    /**
     * Handle an incoming request for tutor routes.
     * Checks if tutor profile is disabled and returns blocked message.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get the user from the JWT token
        $user = auth('api')->user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        // Explicitly reload the user from the database with all relationships
        // This ensures we get the latest data, not stale cache
        $user = \App\Models\User::with(['tutor'])->find($user->id);

        if (!$user) {
            return response()->json([
                'message' => 'User not found.',
            ], 401);
        }

        \Log::info('CheckTutorProfileStatus', [
            'user_id' => $user->id,
            'email' => $user->email,
            'has_tutor' => $user->tutor ? 'YES' : 'NO',
            'tutor_id' => $user->tutor?->id,
            'is_disabled' => $user->tutor?->is_disabled ?? 'N/A',
            'disabled_reason' => $user->tutor?->disabled_reason ?? 'N/A',
        ]);

        // Check if user has a tutor profile
        if (!$user->tutor) {
            return response()->json([
                'message' => 'You do not have a tutor profile.',
                'redirect_url' => '/enroll-tutor',
            ], 403);
        }

        // Check if tutor profile is disabled
        if ($user->tutor->is_disabled) {
            $contactEmail = config('mail.from.address') ?? 'support@namate24.com';
            $contactPhone = config('app.support_phone') ?? '+91-9876543210';

            \Log::warning('TUTOR PROFILE BLOCKED', [
                'user_id' => $user->id,
                'email' => $user->email,
                'tutor_id' => $user->tutor->id,
                'reason' => $user->tutor->disabled_reason,
            ]);

            return response()->json([
                'blocked' => true,
                'profile' => 'tutor',
                'message' => 'Your tutor profile is disabled.',
                'reason' => $user->tutor->disabled_reason,
                'contact_info' => [
                    'email' => $contactEmail,
                    'phone' => $contactPhone,
                    'message' => 'Please contact admin to enable your profile.',
                ],
                'disabled_at' => $user->tutor->disabled_at?->format('Y-m-d H:i:s'),
            ], 403);
        }

        \Log::info('Tutor profile ACTIVE - Access allowed', ['user_id' => $user->id]);
        return $next($request);
    }
}
