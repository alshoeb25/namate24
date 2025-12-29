<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Symfony\Component\HttpFoundation\Response;

class AdminJwtBridge
{
    /**
     * Bridge an incoming admin JWT to the web guard session for Filament.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Already authenticated via session.
        if (Auth::check()) {
            if (!Auth::user()->hasRole('admin')) {
                abort(403, 'Unauthorized. Admin access required.');
            }

            return $next($request);
        }

        $token = $request->bearerToken();

        // If no JWT is present, let Filament's normal auth middleware handle it (login screen).
        if (!$token) {
            return $next($request);
        }

        try {
            $user = JWTAuth::setToken($token)->authenticate();
        } catch (TokenExpiredException|TokenInvalidException $e) {
            // Token not usable; fall back to normal login flow.
            return $next($request);
        } catch (\Throwable $e) {
            return $next($request);
        }

        if (!$user) {
            return $next($request);
        }

        if (!$user->hasRole('admin')) {
            abort(403, 'Unauthorized. Admin access required.');
        }

        // Log into the web guard so Filament sees an authenticated admin session.
        Auth::guard('web')->login($user);

        return $next($request);
    }
}
