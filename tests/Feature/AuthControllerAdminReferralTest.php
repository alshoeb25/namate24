<?php

namespace Tests\Feature;

use App\Models\ReferralInvite;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthControllerAdminReferralTest extends TestCase
{
    use RefreshDatabase;

    /**
     * TEST 1: User registration with admin referral invite
     */
    public function test_user_registration_with_admin_referral()
    {
        // Create admin referral invite
        ReferralInvite::create([
            'email' => 'john@example.com',
            'referred_coins' => 50,
            'is_used' => false,
        ]);

        // Register user with matching email
        $response = $this->postJson('/api/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '1234567890',
            'password' => 'password123',
            'role' => 'student',
        ]);

        $response->assertStatus(201)
            ->assertJson(['message' => 'Registration successful! Please check your email to verify your account.'])
            ->assertJsonStructure(['admin_referral']);

        // Check user received coins
        $user = User::where('email', 'john@example.com')->first();
        $this->assertEquals(50, $user->coins);

        // Check invite is marked as used
        $invite = ReferralInvite::where('email', 'john@example.com')->first();
        $this->assertTrue($invite->is_used);
    }

    /**
     * TEST 2: User registration without matching admin referral
     */
    public function test_user_registration_without_admin_referral()
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'phone' => '0987654321',
            'password' => 'password123',
            'role' => 'tutor',
        ]);

        $response->assertStatus(201);

        // User should have 0 coins
        $user = User::where('email', 'jane@example.com')->first();
        $this->assertEquals(0, $user->coins);
    }

    /**
     * TEST 3: Admin referral invite is only used once
     */
    public function test_admin_referral_invite_used_only_once()
    {
        ReferralInvite::create([
            'email' => 'shared@example.com',
            'referred_coins' => 75,
            'is_used' => false,
        ]);

        // First registration
        $response1 = $this->postJson('/api/register', [
            'name' => 'User One',
            'email' => 'shared@example.com',
            'phone' => '1111111111',
            'password' => 'password123',
            'role' => 'student',
        ]);

        $response1->assertStatus(201);
        $user1 = User::where('email', 'shared@example.com')->first();
        $this->assertEquals(75, $user1->coins);

        // Verify invite is marked as used
        $invite = ReferralInvite::where('email', 'shared@example.com')->first();
        $this->assertTrue($invite->is_used);
    }

    /**
     * TEST 4: Standard referral takes precedence over admin referral
     */
    public function test_standard_referral_takes_precedence()
    {
        // Create referrer user
        $referrer = User::factory()->create([
            'role' => 'student',
            'coins' => 0,
        ]);

        // Create admin invite for same email
        ReferralInvite::create([
            'email' => 'referred@example.com',
            'referred_coins' => 100,
            'is_used' => false,
        ]);

        // Register with standard referral code
        $response = $this->postJson('/api/register', [
            'name' => 'Referred User',
            'email' => 'referred@example.com',
            'phone' => '1234567890',
            'password' => 'password123',
            'role' => 'student',
            'referral_code' => $referrer->referral_code,
        ]);

        $response->assertStatus(201);

        // Should have 15 coins from standard referral, NOT 100 from admin invite
        $user = User::where('email', 'referred@example.com')->first();
        $this->assertEquals(15, $user->coins);

        // Admin invite should NOT be marked as used
        $invite = ReferralInvite::where('email', 'referred@example.com')->first();
        $this->assertFalse($invite->is_used);
    }

    /**
     * TEST 5: Admin referral coins logged in transactions
     */
    public function test_admin_referral_logged_in_coin_transactions()
    {
        ReferralInvite::create([
            'email' => 'john@example.com',
            'referred_coins' => 50,
        ]);

        $this->postJson('/api/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '1234567890',
            'password' => 'password123',
            'role' => 'student',
        ]);

        $user = User::where('email', 'john@example.com')->first();

        // Check coin transaction exists
        $this->assertDatabaseHas('coin_transactions', [
            'user_id' => $user->id,
            'type' => 'admin_referral_bonus',
            'amount' => 50,
        ]);
    }

    /**
     * TEST 6: Referral record created with null referrer_id for admin
     */
    public function test_referral_record_created_for_admin()
    {
        ReferralInvite::create([
            'email' => 'john@example.com',
            'referred_coins' => 50,
        ]);

        $this->postJson('/api/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '1234567890',
            'password' => 'password123',
            'role' => 'student',
        ]);

        $user = User::where('email', 'john@example.com')->first();

        // Check referral record exists with null referrer_id
        $this->assertDatabaseHas('referrals', [
            'referrer_id' => null,
            'referred_id' => $user->id,
            'referred_coins' => 50,
            'reward_given' => true,
        ]);
    }

    /**
     * TEST 7: Email case sensitivity in matching
     */
    public function test_email_case_sensitivity_in_matching()
    {
        ReferralInvite::create([
            'email' => 'John@Example.Com',
            'referred_coins' => 50,
        ]);

        // Try with different case
        $response = $this->postJson('/api/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '1234567890',
            'password' => 'password123',
            'role' => 'student',
        ]);

        // Should NOT match (emails are case-sensitive in database)
        $user = User::where('email', 'john@example.com')->first();
        $this->assertEquals(0, $user->coins);
    }

    /**
     * TEST 8: Response includes admin_referral data
     */
    public function test_response_includes_admin_referral_data()
    {
        ReferralInvite::create([
            'email' => 'john@example.com',
            'referred_coins' => 50,
        ]);

        $response = $this->postJson('/api/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '1234567890',
            'password' => 'password123',
            'role' => 'student',
        ]);

        $response->assertJsonStructure(['admin_referral' => ['coins', 'source']])
            ->assertJson([
                'admin_referral' => [
                    'coins' => 50,
                    'source' => 'admin',
                ]
            ]);
    }
}
