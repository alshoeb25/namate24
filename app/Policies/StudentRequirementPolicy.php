<?php

namespace App\Policies;

use App\Models\StudentRequirement;
use App\Models\User;

class StudentRequirementPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function view(User $user, StudentRequirement $requirement): bool
    {
        return $user->hasRole('admin');
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function update(User $user, StudentRequirement $requirement): bool
    {
        return $user->hasRole('admin');
    }

    public function delete(User $user, StudentRequirement $requirement): bool
    {
        return $user->hasRole('admin');
    }
}
