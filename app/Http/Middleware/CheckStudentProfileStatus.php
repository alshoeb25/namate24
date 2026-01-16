<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckStudentProfileStatus
{
    /**
     * Handle an incoming request for student routes.
     * Checks if student profile is disabled and returns blocked message.
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
        $user = \App\Models\User::with(['student'])->find($user->id);

        if (!$user) {
            return response()->json([
                'message' => 'User not found.',
            ], 401);
        }

        \Log::info('CheckStudentProfileStatus', [
            'user_id' => $user->id,
            'email' => $user->email,
            'has_student' => $user->student ? 'YES' : 'NO',
            'student_id' => $user->student?->id,
            'is_disabled' => $user->student?->is_disabled ?? 'N/A',
            'disabled_reason' => $user->student?->disabled_reason ?? 'N/A',
        ]);

        // Check if user has a student profile
        if (!$user->student) {
            return response()->json([
                'message' => 'You do not have a student profile.',
                'redirect_url' => '/enroll-student',
            ], 403);
        }

        // Check if student profile is disabled
        if ($user->student->is_disabled) {
            $contactEmail = config('mail.from.address') ?? 'support@namate24.com';
            $contactPhone = config('app.support_phone') ?? '+91-9876543210';

            \Log::warning('STUDENT PROFILE BLOCKED', [
                'user_id' => $user->id,
                'email' => $user->email,
                'student_id' => $user->student->id,
                'reason' => $user->student->disabled_reason,
            ]);

            return response()->json([
                'blocked' => true,
                'profile' => 'student',
                'message' => 'Your student profile is disabled.',
                'reason' => $user->student->disabled_reason,
                'contact_info' => [
                    'email' => $contactEmail,
                    'phone' => $contactPhone,
                    'message' => 'Please contact admin to enable your profile.',
                ],
                'disabled_at' => $user->student->disabled_at?->format('Y-m-d H:i:s'),
            ], 403);
        }

        \Log::info('Student profile ACTIVE - Access allowed', ['user_id' => $user->id]);
        return $next($request);
    }
}
