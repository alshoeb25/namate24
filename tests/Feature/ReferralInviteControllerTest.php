<?php

namespace Tests\Feature;

use App\Models\ReferralInvite;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ReferralInviteControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private $admin;
    private $token;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create admin user
        $this->admin = User::factory()->create([
            'role' => 'admin',
            'email' => 'admin@example.com'
        ]);
        
        // Authenticate
        $this->token = \PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth::fromUser($this->admin);
    }

    /**
     * TEST 1: Create single referral invite
     */
    public function test_create_single_referral_invite()
    {
        $response = $this->postJson('/api/admin/referral-invites', [
            'email' => 'john@example.com',
            'referred_coins' => 50,
            'send_email' => false,
        ], ['Authorization' => "Bearer $this->token"]);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Referral invite created successfully',
                'email_sent' => false,
            ]);

        $this->assertDatabaseHas('referral_invites', [
            'email' => 'john@example.com',
            'referred_coins' => 50,
            'is_used' => false,
        ]);
    }

    /**
     * TEST 2: Create invite with duplicate email should fail
     */
    public function test_create_invite_with_duplicate_email_fails()
    {
        // Create first invite
        ReferralInvite::create([
            'email' => 'john@example.com',
            'referred_coins' => 50,
        ]);

        // Try to create duplicate
        $response = $this->postJson('/api/admin/referral-invites', [
            'email' => 'john@example.com',
            'referred_coins' => 75,
        ], ['Authorization' => "Bearer $this->token"]);

        $response->assertStatus(422);
    }

    /**
     * TEST 3: Create invite with invalid email
     */
    public function test_create_invite_with_invalid_email_fails()
    {
        $response = $this->postJson('/api/admin/referral-invites', [
            'email' => 'invalid-email',
            'referred_coins' => 50,
        ], ['Authorization' => "Bearer $this->token"]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('email');
    }

    /**
     * TEST 4: Create invite with coins out of range
     */
    public function test_create_invite_with_invalid_coins_fails()
    {
        // Test 0 coins
        $response = $this->postJson('/api/admin/referral-invites', [
            'email' => 'john@example.com',
            'referred_coins' => 0,
        ], ['Authorization' => "Bearer $this->token"]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('referred_coins');

        // Test > 1000 coins
        $response = $this->postJson('/api/admin/referral-invites', [
            'email' => 'jane@example.com',
            'referred_coins' => 1001,
        ], ['Authorization' => "Bearer $this->token"]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('referred_coins');
    }

    /**
     * TEST 5: List referral invites
     */
    public function test_list_referral_invites()
    {
        // Create 3 invites
        ReferralInvite::factory()->count(3)->create();

        $response = $this->getJson('/api/admin/referral-invites', [
            'Authorization' => "Bearer $this->token"
        ]);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Referral invites retrieved successfully'])
            ->assertJsonStructure(['data' => ['data', 'current_page', 'total']]);

        $this->assertCount(3, $response['data']['data']);
    }

    /**
     * TEST 6: List with filtering by status
     */
    public function test_list_with_status_filter()
    {
        ReferralInvite::create(['email' => 'used@example.com', 'referred_coins' => 50, 'is_used' => true, 'used_at' => now()]);
        ReferralInvite::create(['email' => 'unused@example.com', 'referred_coins' => 50, 'is_used' => false]);

        // Filter by unused
        $response = $this->getJson('/api/admin/referral-invites?status=unused', [
            'Authorization' => "Bearer $this->token"
        ]);

        $response->assertStatus(200);
        $this->assertEquals(1, count($response['data']['data']));
        $this->assertEquals('unused@example.com', $response['data']['data'][0]['email']);
    }

    /**
     * TEST 7: Search by email
     */
    public function test_search_by_email()
    {
        ReferralInvite::create(['email' => 'alice@example.com', 'referred_coins' => 50]);
        ReferralInvite::create(['email' => 'bob@example.com', 'referred_coins' => 50]);

        $response = $this->getJson('/api/admin/referral-invites?search=alice', [
            'Authorization' => "Bearer $this->token"
        ]);

        $response->assertStatus(200);
        $this->assertEquals(1, count($response['data']['data']));
        $this->assertEquals('alice@example.com', $response['data']['data'][0]['email']);
    }

    /**
     * TEST 8: Get single invite
     */
    public function test_get_single_invite()
    {
        $invite = ReferralInvite::create(['email' => 'john@example.com', 'referred_coins' => 50]);

        $response = $this->getJson("/api/admin/referral-invites/{$invite->id}", [
            'Authorization' => "Bearer $this->token"
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Referral invite retrieved',
                'data' => ['email' => 'john@example.com']
            ]);
    }

    /**
     * TEST 9: Get non-existent invite
     */
    public function test_get_non_existent_invite_fails()
    {
        $response = $this->getJson("/api/admin/referral-invites/99999", [
            'Authorization' => "Bearer $this->token"
        ]);

        $response->assertStatus(404);
    }

    /**
     * TEST 10: Update invite coins (unused only)
     */
    public function test_update_invite_coins()
    {
        $invite = ReferralInvite::create(['email' => 'john@example.com', 'referred_coins' => 50]);

        $response = $this->patchJson("/api/admin/referral-invites/{$invite->id}", [
            'referred_coins' => 100,
        ], ['Authorization' => "Bearer $this->token"]);

        $response->assertStatus(200);
        $this->assertEquals(100, $invite->fresh()->referred_coins);
    }

    /**
     * TEST 11: Delete unused invite
     */
    public function test_delete_unused_invite()
    {
        $invite = ReferralInvite::create(['email' => 'john@example.com', 'referred_coins' => 50]);

        $response = $this->deleteJson("/api/admin/referral-invites/{$invite->id}", [], [
            'Authorization' => "Bearer $this->token"
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseMissing('referral_invites', ['id' => $invite->id]);
    }

    /**
     * TEST 12: Cannot delete used invite
     */
    public function test_cannot_delete_used_invite()
    {
        $invite = ReferralInvite::create([
            'email' => 'john@example.com',
            'referred_coins' => 50,
            'is_used' => true,
            'used_at' => now(),
        ]);

        $response = $this->deleteJson("/api/admin/referral-invites/{$invite->id}", [], [
            'Authorization' => "Bearer $this->token"
        ]);

        $response->assertStatus(422)
            ->assertJson(['message' => 'Cannot delete used referral invite']);

        $this->assertDatabaseHas('referral_invites', ['id' => $invite->id]);
    }

    /**
     * TEST 13: Bulk create from text
     */
    public function test_bulk_create_from_text()
    {
        $entries = "user1@example.com,50\nuser2@example.com,75\nuser3@example.com,100";

        $response = $this->postJson('/api/admin/referral-invites/bulk-create-text', [
            'entries' => $entries,
            'send_emails' => false,
        ], ['Authorization' => "Bearer $this->token"]);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Bulk create from text completed']);

        $this->assertDatabaseHas('referral_invites', ['email' => 'user1@example.com', 'referred_coins' => 50]);
        $this->assertDatabaseHas('referral_invites', ['email' => 'user2@example.com', 'referred_coins' => 75]);
        $this->assertDatabaseHas('referral_invites', ['email' => 'user3@example.com', 'referred_coins' => 100]);
    }

    /**
     * TEST 14: Bulk create with pipe separator
     */
    public function test_bulk_create_with_pipe_separator()
    {
        $entries = "user1@example.com|50\nuser2@example.com|75";

        $response = $this->postJson('/api/admin/referral-invites/bulk-create-text', [
            'entries' => $entries,
        ], ['Authorization' => "Bearer $this->token"]);

        $response->assertStatus(200);
        $this->assertEquals(2, ReferralInvite::count());
    }

    /**
     * TEST 15: Bulk create detects duplicates
     */
    public function test_bulk_create_detects_duplicates()
    {
        ReferralInvite::create(['email' => 'existing@example.com', 'referred_coins' => 50]);

        $entries = "existing@example.com,100\nuser2@example.com,75";

        $response = $this->postJson('/api/admin/referral-invites/bulk-create-text', [
            'entries' => $entries,
        ], ['Authorization' => "Bearer $this->token"]);

        $response->assertStatus(200);
        $data = $response['data'];
        
        $this->assertEquals(1, $data['success']);
        $this->assertEquals(1, $data['duplicates']);
    }

    /**
     * TEST 16: Get statistics
     */
    public function test_get_statistics()
    {
        ReferralInvite::create(['email' => 'used1@example.com', 'referred_coins' => 50, 'is_used' => true]);
        ReferralInvite::create(['email' => 'used2@example.com', 'referred_coins' => 75, 'is_used' => true]);
        ReferralInvite::create(['email' => 'unused@example.com', 'referred_coins' => 100, 'is_used' => false]);

        $response = $this->getJson('/api/admin/referral-invites/stats', [
            'Authorization' => "Bearer $this->token"
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'total' => 3,
                    'used' => 2,
                    'unused' => 1,
                    'total_coins_offered' => 225,
                    'total_coins_redeemed' => 125,
                ]
            ]);
    }

    /**
     * TEST 17: Send batch emails
     */
    public function test_send_batch_emails()
    {
        $invite1 = ReferralInvite::create(['email' => 'user1@example.com', 'referred_coins' => 50]);
        $invite2 = ReferralInvite::create(['email' => 'user2@example.com', 'referred_coins' => 50]);

        $response = $this->postJson('/api/admin/referral-invites/send-emails', [
            'invite_ids' => [$invite1->id, $invite2->id],
        ], ['Authorization' => "Bearer $this->token"]);

        $response->assertStatus(200);
        // Note: Actual email sending depends on queue setup
    }

    /**
     * TEST 18: Unauthorized user cannot access
     */
    public function test_unauthorized_user_cannot_access()
    {
        $response = $this->getJson('/api/admin/referral-invites');

        $response->assertStatus(401);
    }

    /**
     * TEST 19: Non-admin cannot access
     */
    public function test_non_admin_cannot_access()
    {
        $student = User::factory()->create(['role' => 'student']);
        $token = \PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth::fromUser($student);

        $response = $this->getJson('/api/admin/referral-invites', [
            'Authorization' => "Bearer $token"
        ]);

        $response->assertStatus(403);
    }
}
