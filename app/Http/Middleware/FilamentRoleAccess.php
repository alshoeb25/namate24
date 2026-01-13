<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FilamentRoleAccess
{
    /**
     * Handle an incoming request.
     * 
     * Applies multiple security checks:
     * 1. Email verification required
     * 2. Blocks API/JWT users from admin panel
     * 3. Permission-based access control
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        // If not authenticated, let Filament's auth middleware handle it
        if (!$user) {
            return $next($request);
        }

        // Security Check 1: Require verified email
        if (!$user->hasVerifiedEmail()) {
            abort(403, 'Email must be verified to access admin panel');
        }

        // Security Check 2: Prevent API/JWT users from accessing admin
        // This prevents token-based access to admin panel
        if ($request->expectsJson()) {
            abort(403, 'API requests cannot access admin panel');
        }

        // Security Check 3: Check if user has dashboard permission
        // This is permission-based, not role-based, so it scales better
        if (!$user->can('view-dashboard')) {
            abort(403, 'Unauthorized access to admin panel');
        }

        return $next($request);
    }
}
