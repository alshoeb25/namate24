<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Wallet;
use App\Models\CreditPurchase;
use App\Services\CreditService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CreditSpendTest extends TestCase
{
    use RefreshDatabase;

    public function test_spend_credits_fifo()
    {
        $user = User::factory()->create();
        $wallet = Wallet::create(['user_id' => $user->id, 'balance'=>100]);
        $p1 = CreditPurchase::create(['wallet_id'=>$wallet->id,'credits_total'=>50,'credits_consumed'=>0,'status'=>'paid','purchased_at'=>now()->subDays(10)]);
        $p2 = CreditPurchase::create(['wallet_id'=>$wallet->id,'credits_total'=>50,'credits_consumed'=>0,'status'=>'paid','purchased_at'=>now()->subDays(5)]);

        $svc = new CreditService();
        $svc->spendCredits($wallet, 60, ['reason'=>'test']);

        $this->assertDatabaseHas('credit_purchases', ['id'=>$p1->id, 'credits_consumed' => 50]);
        $this->assertDatabaseHas('credit_purchases', ['id'=>$p2->id, 'credits_consumed' => 10]);
        $this->assertEquals(40, $wallet->fresh()->balance); // 100 - 60
    }
}