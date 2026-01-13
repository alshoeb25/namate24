<?php

namespace App\Policies;

use App\Models\Student;
use App\Models\User;

class StudentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view-students') || $user->hasRole(['super_admin', 'student_admin', 'enquiries_admin', 'reviews_admin']);
    }

    public function view(User $user, Student $student): bool
    {
        return $user->hasRole(['super_admin', 'student_admin', 'enquiries_admin']) || $user->can('view-students') || $user->id === $student->user_id;
    }

    public function create(User $user): bool
    {
        return $user->can('create-students') || $user->hasRole(['super_admin', 'student_admin']);
    }

    public function update(User $user, Student $student): bool
    {
        return $user->hasRole(['super_admin', 'student_admin']) || $user->can('edit-students') || $user->id === $student->user_id;
    }

    public function delete(User $user, Student $student): bool
    {
        return $user->can('delete-students') || $user->hasRole(['super_admin', 'student_admin']);
    }
}
