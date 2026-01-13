<?php

namespace App\Filament\Traits;

use Illuminate\Database\Eloquent\Builder;

trait RoleBasedAccess
{
    /**
     * Check if user can access this resource
     */
    public static function canAccess(): bool
    {
        $user = auth()->user();

        if (!$user) {
            return false;
        }

        // Super admin can access everything
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Check resource-specific permissions
        return static::canAccessResource($user);
    }

    /**
     * Check if user has access to this resource based on permissions
     * Uses the resource's permission name to check view permission
     */
    protected static function canAccessResource($user): bool
    {
        $permissionName = 'view-' . static::getResourcePermissionName();
        return $user->can($permissionName);
    }

    /**
     * Get the resource permission name (override in resource)
     * 
     * Example: return 'tutors' maps to:
     * - view-tutors
     * - create-tutors
     * - edit-tutors
     * - delete-tutors
     */
    protected static function getResourcePermissionName(): string
    {
        return 'resource';
    }
}


