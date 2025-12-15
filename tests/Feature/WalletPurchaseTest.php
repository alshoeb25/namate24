<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\CreditPackage;
use Laravel\Sanctum\Sanctum;

class WalletPurchaseTest extends TestCase
{
    public function test_tutor_can_create_purchase_order()
    {
        $user = User::factory()->create();
        $user->assignRole('tutor');
        Sanctum::actingAs($user, ['*']);

        $package = CreditPackage::first() ?? CreditPackage::factory()->create(['credits'=>50,'price'=>499]);

        $res = $this->postJson('/api/wallet/buy', ['package_id' => $package->id]);
        $res->assertStatus(200);
        $res->assertJsonStructure(['order', 'purchase_id']);
    }
}