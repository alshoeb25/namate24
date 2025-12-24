<?php

namespace App\Services;

use App\Exceptions\InsufficientBalanceException;
use App\Models\CoinTransaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class WalletService
{
    public function debit(User $user, int $amount, string $type, ?string $description = null, array $meta = []): CoinTransaction
    {
        if ($amount <= 0) {
            throw new InvalidArgumentException('Debit amount must be greater than zero.');
        }

        return DB::transaction(function () use ($user, $amount, $type, $description, $meta) {
            $lockedUser = User::where('id', $user->id)->lockForUpdate()->first();

            if ($lockedUser->coins < $amount) {
                throw new InsufficientBalanceException('Insufficient coins.');
            }

            $lockedUser->decrement('coins', $amount);

            return CoinTransaction::create([
                'user_id' => $lockedUser->id,
                'type' => $type,
                'amount' => -$amount,
                'balance_after' => $lockedUser->coins,
                'description' => $description,
                'meta' => $meta,
            ]);
        });
    }

    public function credit(User $user, int $amount, string $type, ?string $description = null, array $meta = []): CoinTransaction
    {
        if ($amount <= 0) {
            throw new InvalidArgumentException('Credit amount must be greater than zero.');
        }

        return DB::transaction(function () use ($user, $amount, $type, $description, $meta) {
            $lockedUser = User::where('id', $user->id)->lockForUpdate()->first();

            $lockedUser->increment('coins', $amount);

            return CoinTransaction::create([
                'user_id' => $lockedUser->id,
                'type' => $type,
                'amount' => $amount,
                'balance_after' => $lockedUser->coins,
                'description' => $description,
                'meta' => $meta,
            ]);
        });
    }
}
