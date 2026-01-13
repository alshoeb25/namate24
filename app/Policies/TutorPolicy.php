<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Tutor;
use Illuminate\Auth\Access\Response;

class TutorPolicy
{
    public function viewAny(User $user)
    {
        return $user->can('view-tutors') || $user->hasRole(['super_admin', 'tutor_admin']);
    }

    public function view(User $user, Tutor $tutor)
    {
        if ($tutor->moderation_status !== 'approved' && !$user->can('view-tutors') && !$user->hasRole(['super_admin', 'tutor_admin'])) {
            return Response::deny('Tutor not approved');
        }
        return Response::allow();
    }

    public function update(User $user, Tutor $tutor)
    {
        return $user->id === $tutor->user_id || $user->can('edit-tutors') || $user->hasRole(['super_admin', 'tutor_admin']);
    }

    public function delete(User $user, Tutor $tutor)
    {
        return $user->can('delete-tutors') || $user->hasRole(['super_admin', 'tutor_admin']);
    }

    public function moderate(User $user)
    {
        return $user->can('approve-tutors') || $user->can('reject-tutors') || $user->hasRole(['super_admin', 'tutor_admin']);
    }

    public function approveTutor(User $user)
    {
        return $user->can('approve-tutors') || $user->hasRole(['super_admin', 'tutor_admin']);
    }

    public function rejectTutor(User $user)
    {
        return $user->can('reject-tutors') || $user->hasRole(['super_admin', 'tutor_admin']);
    }
}