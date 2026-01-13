<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Review;

class ReviewPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view-reviews') || $user->hasRole(['super_admin', 'reviews_admin']);
    }

    public function view(User $user, Review $review): bool
    {
        return $user->can('view-reviews') || $user->hasRole(['super_admin', 'reviews_admin']);
    }

    public function moderate(User $user)
    {
        return $user->hasRole(['super_admin', 'reviews_admin']) || $user->can('approve-reviews') || $user->can('reject-reviews');
    }

    public function create(User $user)
    {
        return $user->hasRole('student');
    }

    public function approve(User $user)
    {
        return $user->can('approve-reviews') || $user->hasRole(['super_admin', 'reviews_admin']);
    }

    public function reject(User $user)
    {
        return $user->can('reject-reviews') || $user->hasRole(['super_admin', 'reviews_admin']);
    }

    public function delete(User $user, Review $review)
    {
        return $user->can('delete-reviews') || $user->hasRole(['super_admin', 'reviews_admin']);
    }
}