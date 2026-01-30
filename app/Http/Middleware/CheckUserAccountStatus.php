<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUserAccountStatus
{
    /**
     * Handle an incoming request.
     * Blocks access if the user account is disabled or both profiles are disabled.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth('api')->user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $user = \App\Models\User::with(['tutor', 'student'])->find($user->id);

        if (!$user) {
            return response()->json([
                'message' => 'User not found.',
            ], 401);
        }

        if ($user->is_disabled) {
            return $this->blockedResponse('Your account is disabled.', $user->disabled_reason);
        }

        $tutorBlocked = $user->tutor && $user->tutor->is_disabled;
        $studentBlocked = $user->student && $user->student->is_disabled;
        $hasBothRoles = $user->tutor && $user->student;

        if (!$hasBothRoles && $tutorBlocked) {
            return $this->blockedResponse('Your tutor profile is disabled.', $user->tutor?->disabled_reason);
        }

        if (!$hasBothRoles && $studentBlocked) {
            return $this->blockedResponse('Your student profile is disabled.', $user->student?->disabled_reason);
        }

        if ($hasBothRoles && $tutorBlocked && $studentBlocked) {
            return $this->blockedResponse('Both your tutor and student profiles are disabled.', null);
        }

        return $next($request);
    }

    private function blockedResponse(string $message, ?string $reason): Response
    {
        $contactEmail = config('mail.from.address') ?? 'support@namate24.com';
        $contactPhone = config('app.support_phone') ?? '+91-9876543210';

        return response()->json([
            'blocked' => true,
            'message' => $message,
            'reason' => $reason,
            'contact_info' => [
                'email' => $contactEmail,
                'phone' => $contactPhone,
                'message' => 'Please contact admin to enable your profile.',
            ],
        ], 403);
    }
}
