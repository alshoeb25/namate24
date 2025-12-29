<?php

namespace App\Filament\Auth;

use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Illuminate\Contracts\Auth\Authenticatable;

class AdminAuthGuard
{
    /**
     * Check if user can access the admin panel.
     */
    public static function can(Authenticatable $user): bool
    {
        return $user && $user->hasRole('admin');
    }
}
