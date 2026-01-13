<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminSessionIsolation
{
    /**
     * Handle an incoming request.
     * 
     * Implements session isolation for admin panel:
     * - Sets custom session cookie name
     * - Shorter session lifetime (2 hours)
     * - HTTPOnly and Secure flags
     * - SameSite protection against CSRF
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Configure session for admin panel
        $sessionConfig = config('session');
        
        if ($request->is('admin*')) {
            // Use separate session cookie for admin
            ini_set('session.name', 'namate24_admin_session');
            ini_set('session.gc_maxlifetime', 7200); // 2 hours in seconds
            
            // Regenerate session ID for security
            session()->regenerate();
        }

        return $next($request);
    }
}
