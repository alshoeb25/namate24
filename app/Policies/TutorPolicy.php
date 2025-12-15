<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Tutor;
use Illuminate\Auth\Access\Response;

class TutorPolicy
{
    public function viewAny(User $user)
    {
        return $user->hasAnyRole(['admin', 'tutor', 'student']);
    }

    public function view(User $user, Tutor $tutor)
    {
        if ($tutor->moderation_status !== 'approved' && ! $user->hasRole('admin')) {
            return Response::deny('Tutor not approved');
        }
        return Response::allow();
    }

    public function update(User $user, Tutor $tutor)
    {
        return $user->id === $tutor->user_id || $user->hasRole('admin');
    }

    public function moderate(User $user)
    {
        return $user->hasRole('admin');
    }
}