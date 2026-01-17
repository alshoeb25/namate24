<?php

namespace Tests\Unit;

use App\Models\ReferralInvite;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReferralInviteModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * TEST 1: Model can be created with valid data
     */
    public function test_referral_invite_can_be_created()
    {
        $invite = ReferralInvite::create([
            'email' => 'john@example.com',
            'referred_coins' => 50,
        ]);

        $this->assertInstanceOf(ReferralInvite::class, $invite);
        $this->assertEquals('john@example.com', $invite->email);
        $this->assertEquals(50, $invite->referred_coins);
        $this->assertFalse($invite->is_used);
        $this->assertNull($invite->used_at);
    }

    /**
     * TEST 2: is_used is cast to boolean
     */
    public function test_is_used_is_cast_to_boolean()
    {
        $invite = ReferralInvite::create([
            'email' => 'john@example.com',
            'referred_coins' => 50,
            'is_used' => true,
        ]);

        $this->assertTrue($invite->is_used);
        $this->assertIsBool($invite->is_used);
    }

    /**
     * TEST 3: used_at is cast to datetime
     */
    public function test_used_at_is_cast_to_datetime()
    {
        $now = now();
        
        $invite = ReferralInvite::create([
            'email' => 'john@example.com',
            'referred_coins' => 50,
            'is_used' => true,
            'used_at' => $now,
        ]);

        $this->assertNotNull($invite->used_at);
        $this->assertTrue($invite->used_at instanceof \Carbon\Carbon);
    }

    /**
     * TEST 4: Email is unique
     */
    public function test_email_is_unique()
    {
        ReferralInvite::create([
            'email' => 'john@example.com',
            'referred_coins' => 50,
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        ReferralInvite::create([
            'email' => 'john@example.com',
            'referred_coins' => 75,
        ]);
    }

    /**
     * TEST 5: Model has correct table name
     */
    public function test_model_has_correct_table_name()
    {
        $invite = new ReferralInvite();
        $this->assertEquals('referral_invites', $invite->getTable());
    }

    /**
     * TEST 6: Fillable attributes are correct
     */
    public function test_fillable_attributes_are_correct()
    {
        $invite = new ReferralInvite();
        $fillable = $invite->getFillable();

        $this->assertContains('email', $fillable);
        $this->assertContains('referred_coins', $fillable);
        $this->assertContains('is_used', $fillable);
        $this->assertContains('used_at', $fillable);
    }

    /**
     * TEST 7: Timestamps are automatically managed
     */
    public function test_timestamps_are_managed()
    {
        $invite = ReferralInvite::create([
            'email' => 'john@example.com',
            'referred_coins' => 50,
        ]);

        $this->assertNotNull($invite->created_at);
        $this->assertNotNull($invite->updated_at);
        $this->assertTrue($invite->created_at instanceof \Carbon\Carbon);
    }

    /**
     * TEST 8: Referred coins default to 0
     */
    public function test_referred_coins_default_to_zero()
    {
        $invite = ReferralInvite::create([
            'email' => 'john@example.com',
            'referred_coins' => 0,
        ]);

        $this->assertEquals(0, $invite->referred_coins);
    }

    /**
     * TEST 9: Query scopes work correctly
     */
    public function test_can_query_by_email()
    {
        ReferralInvite::create(['email' => 'john@example.com', 'referred_coins' => 50]);
        
        $invite = ReferralInvite::where('email', 'john@example.com')->first();
        
        $this->assertNotNull($invite);
        $this->assertEquals('john@example.com', $invite->email);
    }

    /**
     * TEST 10: Can query unused invites
     */
    public function test_can_query_unused_invites()
    {
        ReferralInvite::create(['email' => 'used@example.com', 'referred_coins' => 50, 'is_used' => true]);
        ReferralInvite::create(['email' => 'unused@example.com', 'referred_coins' => 50, 'is_used' => false]);

        $unused = ReferralInvite::where('is_used', false)->get();
        
        $this->assertEquals(1, $unused->count());
        $this->assertEquals('unused@example.com', $unused->first()->email);
    }
}
