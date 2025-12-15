<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string|null  ...$guards
     */
    public function handle(Request $request, Closure $next, ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                // Redirect authenticated users based on role
                $user = Auth::guard($guard)->user();
                
                if ($user && $user->role === 'tutor') {
                    return redirect('/tutor/profile');
                } elseif ($user && $user->role === 'admin') {
                    return redirect('/admin');
                }
                
                return redirect('/');
            }
        }

        return $next($request);
    }
}
