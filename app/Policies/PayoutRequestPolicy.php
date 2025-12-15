<?php

namespace App\Policies;

use App\Models\User;
use App\Models\PayoutRequest;

class PayoutRequestPolicy
{
    public function viewAny(User $user)
    {
        return $user->hasRole('admin');
    }

    public function create(User $user)
    {
        return $user->hasRole('tutor');
    }

    public function update(User $user, PayoutRequest $payout)
    {
        return $user->hasRole('admin');
    }
}