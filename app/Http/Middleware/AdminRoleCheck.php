<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminRoleCheck
{
    /**
     * Check if authenticated user has admin role.
     */
    public function handle(Request $request, Closure $next)
    {
        // Filament's Authenticate middleware has already run
        // If user is authenticated but not admin, abort with 403
        if (auth()->check() && !auth()->user()->hasRole('admin')) {
            abort(403, 'You do not have permission to access the admin panel.');
        }

        return $next($request);
    }
}
