<?php

namespace App\Policies;

use App\Models\CoinTransaction;
use App\Models\User;

class CoinTransactionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function view(User $user, CoinTransaction $transaction): bool
    {
        return $user->hasRole('admin');
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function update(User $user, CoinTransaction $transaction): bool
    {
        return $user->hasRole('admin');
    }

    public function delete(User $user, CoinTransaction $transaction): bool
    {
        return $user->hasRole('admin');
    }
}
