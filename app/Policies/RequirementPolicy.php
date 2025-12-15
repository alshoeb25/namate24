<?php

namespace App\Policies;

use App\Models\User;
use App\Models\StudentRequirement;

class RequirementPolicy
{
    public function viewAny(User $user)
    {
        return $user->hasRole('tutor');
    }

    public function create(User $user)
    {
        return $user->hasRole('student');
    }

    public function view(User $user, StudentRequirement $requirement)
    {
        // students can view their own; tutors can view leads if permitted
        return $user->id === $requirement->student_id || $user->hasRole('tutor') || $user->hasRole('admin');
    }
}