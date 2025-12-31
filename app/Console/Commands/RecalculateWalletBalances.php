<?php

namespace App\Console\Commands;

use App\Models\CoinTransaction;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RecalculateWalletBalances extends Command
{
    protected $signature = 'wallet:recalculate {--user= : Specific user ID to recalculate}';
    protected $description = 'Recalculate wallet balances based on transaction history';

    public function handle()
    {
        $this->info('Starting wallet balance recalculation...');

        $userIdFilter = $this->option('user');
        
        $query = User::query();
        if ($userIdFilter) {
            $query->where('id', $userIdFilter);
        }
        
        $users = $query->get();
        $this->info("Processing {$users->count()} users...");

        foreach ($users as $user) {
            $this->recalculateUserBalance($user);
        }

        $this->info('✓ Balance recalculation complete!');
    }

    private function recalculateUserBalance(User $user)
    {
        DB::transaction(function () use ($user) {
            // Get all transactions for this user ordered by creation time
            $transactions = CoinTransaction::where('user_id', $user->id)
                ->orderBy('created_at', 'asc')
                ->orderBy('id', 'asc')
                ->get();

            if ($transactions->isEmpty()) {
                $this->warn("User {$user->id} ({$user->name}) has no transactions");
                // Set user balance to 0 if no transactions
                $user->update(['coins' => 0]);
                return;
            }

            $runningBalance = 0;
            $this->info("Processing user {$user->id} ({$user->name}) - {$transactions->count()} transactions");

            foreach ($transactions as $transaction) {
                $runningBalance += $transaction->amount;
                
                // Update balance_after if it's incorrect
                if ($transaction->balance_after != $runningBalance) {
                    $oldBalance = $transaction->balance_after;
                    $transaction->balance_after = $runningBalance;
                    $transaction->save();
                    
                    $this->line("  Fixed transaction {$transaction->id}: {$oldBalance} → {$runningBalance}");
                }
            }

            // Update user's current balance
            if ($user->coins != $runningBalance) {
                $oldUserBalance = $user->coins;
                $user->coins = $runningBalance;
                $user->save();
                
                $this->info("  Updated user balance: {$oldUserBalance} → {$runningBalance}");
            } else {
                $this->line("  User balance already correct: {$runningBalance}");
            }
        });
    }
}
