<?php

namespace App\Services;

use App\Models\Wallet;
use App\Models\CreditPurchase;
use App\Models\CreditTransaction;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;

class CreditService
{
    /**
     * Spend credits from a wallet using FIFO consumption of purchases.
     *
     * @throws Exception if insufficient credits
     */
    public function spendCredits(Wallet $wallet, int $amount, array $meta = [])
    {
        if ($wallet->balance < $amount) {
            throw new Exception('Insufficient credits');
        }

        DB::beginTransaction();
        try {
            $remaining = $amount;

            // consume from oldest paid, unexpired purchases first
            $purchases = CreditPurchase::where('wallet_id', $wallet->id)
                ->where('status', 'paid')
                ->whereColumn('credits_consumed', '<', 'credits_total')
                ->where(function ($q) {
                    $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
                })
                ->orderBy('purchased_at', 'asc')
                ->lockForUpdate()
                ->get();

            foreach ($purchases as $p) {
                $available = $p->credits_total - $p->credits_consumed;
                if ($available <= 0) continue;
                $take = min($available, $remaining);
                $p->credits_consumed += $take;
                $p->save();

                CreditTransaction::create([
                    'wallet_id' => $wallet->id,
                    'credit_purchase_id' => $p->id,
                    'amount' => -1 * $take,
                    'type' => 'spend',
                    'meta' => $meta,
                ]);

                $remaining -= $take;
                if ($remaining <= 0) break;
            }

            if ($remaining > 0) {
                // should not happen because we checked balance earlier
                throw new Exception('Unable to consume all credits');
            }

            $wallet->decrement('balance', $amount);

            DB::commit();
            return true;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Credit wallet after successful purchase.
     */
    public function creditWallet(CreditPurchase $purchase)
    {
        $wallet = $purchase->wallet;
        $wallet->increment('balance', $purchase->credits_total);
        CreditTransaction::create([
            'wallet_id' => $wallet->id,
            'credit_purchase_id' => $purchase->id,
            'amount' => $purchase->credits_total,
            'type' => 'purchase',
            'meta' => ['purchased_at' => $purchase->purchased_at],
        ]);
    }
}