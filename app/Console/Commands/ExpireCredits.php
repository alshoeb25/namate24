<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CreditPurchase;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ExpireCredits extends Command
{
    protected $signature = 'credits:expire';
    protected $description = 'Expire credit purchases that have passed expiry and adjust wallet balances';

    public function handle()
    {
        $now = Carbon::now();
        $expired = CreditPurchase::where('status', 'paid')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', $now)
            ->whereRaw('credits_total > credits_consumed')
            ->get();

        $count = 0;
        foreach ($expired as $purchase) {
            DB::transaction(function () use ($purchase, &$count) {
                $remaining = $purchase->credits_total - $purchase->credits_consumed;
                if ($remaining <= 0) return;
                $wallet = $purchase->wallet;
                $wallet->decrement('balance', $remaining);
                $purchase->credits_consumed = $purchase->credits_total;
                $purchase->save();

                $wallet->credit_transactions()->create([
                    'credit_purchase_id' => $purchase->id,
                    'amount' => -1 * $remaining,
                    'type' => 'expire',
                    'meta' => ['reason' => 'expiry'],
                ]);
                $count++;
            });
        }

        $this->info("Processed $count expired purchases.");
    }
}