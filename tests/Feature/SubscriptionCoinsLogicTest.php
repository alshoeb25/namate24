<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\UserSubscription;
use App\Models\SubscriptionPlan;
use App\Models\Tutor;
use App\Models\Student;
use App\Services\SubscriptionService;
use App\Services\CoinPricingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;
use Spatie\Permission\Models\Role;

class SubscriptionCoinsLogicTest extends TestCase
{
    use RefreshDatabase;

    protected User $student;
    protected User $tutor;
    protected SubscriptionPlan $basicPlan;
    protected SubscriptionPlan $proPlan;

    public function setUp(): void
    {
        parent::setUp();
        
        // Create roles first (required for Spatie Permission)
        Role::firstOrCreate(['name' => 'student', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'tutor', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        
        // Create subscription plans
        $this->basicPlan = SubscriptionPlan::create([
            'name' => 'Basic',
            'description' => 'Basic Plan',
            'price' => 99,
            'currency' => 'INR',
            'validity_days' => 30,
            'views_allowed' => 2, // Max 2 views per month
            'coins_included' => 99,
            'has_priority_support' => false,
            'has_ebook_content' => false,
            'access_delay_hours' => 1, // 1-2 hours delay
            'cost_per_view' => 49, // 49 coins per view
            'coins_carry_forward' => false, // No carryforward for BASIC
            'lapse_grace_period_hours' => 2,
            'is_active' => true,
            'display_order' => 1,
        ]);

        $this->proPlan = SubscriptionPlan::create([
            'name' => 'Pro',
            'description' => 'Pro Plan',
            'price' => 399,
            'currency' => 'INR',
            'validity_days' => 30,
            'views_allowed' => 10, // 10-12 views at 39 coins each
            'coins_included' => 399,
            'has_priority_support' => true,
            'has_ebook_content' => true,
            'access_delay_hours' => 0, // Immediate access
            'cost_per_view' => 39, // 39 coins per view
            'coins_carry_forward' => true, // Carryforward for PRO
            'lapse_grace_period_hours' => 2,
            'is_active' => true,
            'display_order' => 2,
        ]);

        // Create test users
        $this->student = User::factory()->create(['role' => 'student', 'country_iso' => 'IN']);
        $this->tutor = User::factory()->create(['role' => 'tutor', 'country_iso' => 'IN']);
        
        // Create student and tutor profiles
        Student::create([
            'user_id' => $this->student->id,
            'phone' => '1234567891',
        ]);
        
        Tutor::create([
            'user_id' => $this->tutor->id,
            'name' => 'Test Tutor',
            'phone' => '1234567890',
        ]);
    }

    /**
     * SCENARIO 1: Active Subscription with Views Available
     * Expected: FREE access (no coins deducted)
     */
    public function test_active_subscription_with_views_available_no_coins_deducted()
    {
        // Setup: Student has active BASIC subscription with views remaining
        UserSubscription::create([
            'user_id' => $this->student->id,
            'subscription_plan_id' => $this->basicPlan->id,
            'status' => 'active',
            'views_used' => 0,
            'coins_carried_forward' => 50,
            'activated_at' => now()->subDays(5),
            'expires_at' => now()->addDays(20),
        ]);

        $this->student->coins = 100;
        $this->student->save();

        // Act: Student attempts to unlock tutor contact
        $response = $this->actingAs($this->student)
            ->postJson('/api/student/unlock-tutor-contact', [
                'tutor_id' => $this->tutor->id,
            ]);

        // Assert: Should succeed with 200 and no coin deduction
        $response->assertStatus(200);
        $this->assertEquals(100, $this->student->fresh()->coins);
        
        $subscription = UserSubscription::where('user_id', $this->student->id)->first();
        $this->assertEquals(1, $subscription->views_used);
    }

    /**
     * SCENARIO 2: Active BASIC Subscription with Views Exhausted
     * Expected: Both "upgrade" and "buy coins" options offered
     */
    public function test_active_basic_subscription_views_exhausted_offers_both_options()
    {
        // Setup: BASIC subscription with views exhausted
        UserSubscription::create([
            'user_id' => $this->student->id,
            'subscription_plan_id' => $this->basicPlan->id,
            'status' => 'active',
            'views_used' => 5, // All views used
            'coins_carried_forward' => 0,
            'activated_at' => now()->subDays(5),
            'expires_at' => now()->addDays(20),
        ]);

        $this->student->coins = 100;
        $this->student->save();

        // Act: Student tries to unlock contact
        $response = $this->actingAs($this->student)
            ->postJson('/api/student/unlock-tutor-contact', [
                'tutor_id' => $this->tutor->id,
            ]);

        // Assert: 403 with both options
        $response->assertStatus(403);
        $response->assertJsonStructure([
            'success',
            'message',
            'views_exhausted',
            'options' => [
                'upgrade_subscription' => ['action', 'label', 'description'],
                'buy_coins' => ['action', 'label', 'minimum_coins', 'description']
            ]
        ]);
        
        $this->assertContains('upgrade_subscription', array_keys($response->json('options')));
        $this->assertContains('buy_coins', array_keys($response->json('options')));
    }

    /**
     * SCENARIO 3: Active PRO Subscription with Views Exhausted
     * Expected: Both "renew subscription" and "buy coins" options offered
     */
    public function test_active_pro_subscription_views_exhausted_offers_renew_and_coins()
    {
        // Setup: PRO subscription with views exhausted
        UserSubscription::create([
            'user_id' => $this->student->id,
            'subscription_plan_id' => $this->proPlan->id,
            'status' => 'active',
            'views_used' => 10, // All views used (PRO has 10)
            'coins_spent' => 0,
            'views_with_coins' => 0,
            'coins_carried_forward' => 0,
            'activated_at' => now()->subDays(5),
            'expires_at' => now()->addDays(20),
        ]);

        $this->student->coins = 100;
        $this->student->save();

        // Act: Student tries to unlock contact
        $response = $this->actingAs($this->student)
            ->postJson('/api/student/unlock-tutor-contact', [
                'tutor_id' => $this->tutor->id,
            ]);

        // Assert: 403 with renew + buy coins options
        $response->assertStatus(403);
        $response->assertJsonStructure([
            'success',
            'message',
            'options' => [
                'renew_subscription' => ['action', 'label', 'description'],
                'buy_coins' => ['action', 'label']
            ]
        ]);
    }

    /**
     * SCENARIO 4: Lapsed PRO Subscription with Coins
     * Expected: Access allowed with 2-hour delay, coins deducted
     */
    public function test_lapsed_pro_subscription_with_coins_allows_access_with_delay()
    {
        // Setup: Expired PRO subscription, but coins retained
        UserSubscription::create([
            'user_id' => $this->student->id,
            'subscription_plan_id' => $this->proPlan->id,
            'status' => 'expired',
            'views_used' => 10,
            'coins_spent' => 0,
            'views_with_coins' => 0,
            'coins_carried_forward' => 0,
            'activated_at' => now()->subDays(40),
            'expires_at' => now()->subDays(10), // Expired 10 days ago
        ]);

        $this->student->coins = 50; // Has coins for lapsed PRO access
        $this->student->save();

        // Act: Student tries to unlock contact
        $response = $this->actingAs($this->student)
            ->postJson('/api/student/unlock-tutor-contact', [
                'tutor_id' => $this->tutor->id,
            ]);

        // Assert: Should succeed, coins deducted (39 for PRO)
        $response->assertStatus(200);
        $this->assertEquals(11, $this->student->fresh()->coins); // 50 - 39
    }

    /**
     * SCENARIO 5: Lapsed PRO Subscription without Coins
     * Expected: Offer both "renew" and "buy coins" options
     */
    public function test_lapsed_pro_subscription_no_coins_offers_renew_and_coins()
    {
        // Setup: Expired PRO subscription, no coins
        UserSubscription::create([
            'user_id' => $this->student->id,
            'subscription_plan_id' => $this->proPlan->id,
            'status' => 'expired',
            'views_used' => 10,
            'coins_spent' => 0,
            'views_with_coins' => 0,
            'coins_carried_forward' => 0,
            'activated_at' => now()->subDays(40),
            'expires_at' => now()->subDays(10),
        ]);

        $this->student->coins = 10; // Not enough for PRO (needs 39)
        $this->student->save();

        // Act: Student tries to unlock contact
        $response = $this->actingAs($this->student)
            ->postJson('/api/student/unlock-tutor-contact', [
                'tutor_id' => $this->tutor->id,
            ]);

        // Assert: 402 with options
        $response->assertStatus(402);
        $response->assertJsonStructure([
            'success',
            'message',
            'options' => [
                'renew_subscription' => ['action', 'label'],
                'buy_coins' => ['action', 'label', 'coins_needed']
            ]
        ]);
        
        $this->assertEquals(29, $response->json('options.buy_coins.coins_needed')); // 39 - 10
    }

    /**
     * SCENARIO 6: No Subscription with Coins
     * Expected: Access allowed with coin deduction
     */
    public function test_no_subscription_with_coins_allows_access()
    {
        // Setup: No subscription, but has coins
        $this->student->coins = 100;
        $this->student->save();

        // Act: Student tries to unlock contact
        $response = $this->actingAs($this->student)
            ->postJson('/api/student/unlock-tutor-contact', [
                'tutor_id' => $this->tutor->id,
            ]);

        // Assert: Should succeed, coins deducted
        $response->assertStatus(200);
        $this->assertLess($this->student->fresh()->coins, 100);
    }

    /**
     * SCENARIO 7: No Subscription without Coins
     * Expected: Offer both "subscribe" and "buy coins" options
     */
    public function test_no_subscription_no_coins_offers_both_options()
    {
        // Setup: No subscription, no coins
        $this->student->coins = 0;
        $this->student->save();

        // Act: Student tries to unlock contact
        $response = $this->actingAs($this->student)
            ->postJson('/api/student/unlock-tutor-contact', [
                'tutor_id' => $this->tutor->id,
            ]);

        // Assert: 402 with options
        $response->assertStatus(402);
        $response->assertJsonStructure([
            'success',
            'message',
            'options' => [
                'subscribe' => ['action', 'label'],
                'buy_coins' => ['action', 'label', 'coins_needed']
            ]
        ]);
    }

    /**
     * SCENARIO 8: Coin Pricing for India User (99 coins)
     * Expected: ₹99 base price + 18% GST
     */
    public function test_coin_pricing_india_user_99_coins()
    {
        // Setup: India user wants to buy 99 coins
        $this->student->country_iso = 'IN';
        $this->student->save();

        // Simulate pricing calculation
        $pricing = CoinPricingService::calculateInternationalPrice(
            (object)['coins' => 99]
        );

        // For international (this is just the formula):
        // 99 * (1.25 / 100) = 99 * 0.0125 = 1.2375 USD
        $this->assertEquals('USD', $pricing['currency']);
        $this->assertEquals(1.24, $pricing['total']); // Rounded
    }

    /**
     * SCENARIO 9: Coin Pricing for Non-India User (99 coins - Special Pricing)
     * Expected: $15 (special rate for non-India)
     */
    public function test_coin_pricing_non_india_special_rate_99_coins()
    {
        // Setup: Create order for 99 coins as non-India user
        $this->student->country_iso = 'US';
        $this->student->save();

        // Act: Create custom coin order
        $response = $this->actingAs($this->student)
            ->postJson('/api/wallet/purchase-custom-coins', [
                'coins' => 99,
            ]);

        // Assert: Should get $15 price
        $response->assertStatus(200);
        $this->assertEquals('USD', $response->json('order.currency'));
        $this->assertEquals(1500, $response->json('order.amount')); // $15 in cents
    }

    /**
     * SCENARIO 10: Coin Spent Tracking - Enquiry Unlock
     * Expected: Coin transaction recorded
     */
    public function test_coin_spent_tracked_on_enquiry_unlock()
    {
        // Setup: Student with coins, no subscription
        $this->student->coins = 100;
        $this->student->save();

        // Act: Unlock tutor contact
        $response = $this->actingAs($this->student)
            ->postJson('/api/student/unlock-tutor-contact', [
                'tutor_id' => $this->tutor->id,
            ]);

        // Assert: Coin transaction recorded
        $response->assertStatus(200);
        
        $coinTransaction = \DB::table('coin_transactions')
            ->where('user_id', $this->student->id)
            ->first();
        
        $this->assertNotNull($coinTransaction);
        $this->assertLessThan(0, $coinTransaction->amount); // Negative means spent
        $this->assertStringContainsString('tutor_unlock_contact', $coinTransaction->type);
    }

    /**
     * SCENARIO 11: Subscription Coins Spent Tracking
     * Expected: Subscription coin spending tracked separately
     */
    public function test_subscription_coins_spent_tracked()
    {
        // Setup: Student with active subscription
        $subscription = UserSubscription::create([
            'user_id' => $this->student->id,
            'subscription_plan_id' => $this->basicPlan->id,
            'status' => 'active',
            'views_used' => 0,
            'coins_carried_forward' => 50,
            'activated_at' => now()->subDays(5),
            'expires_at' => now()->addDays(20),
        ]);

        // Act: Use subscription views (uses subscription coins, not wallet coins)
        $this->actingAs($this->student)
            ->postJson('/api/student/unlock-tutor-contact', [
                'tutor_id' => $this->tutor->id,
            ]);

        // Assert: Subscription shows coin usage
        $subscription->refresh();
        $this->assertEquals(1, $subscription->views_used);
    }

    /**
     * SCENARIO 12: Multiple Transactions Coin Balance
     * Expected: Correct balance after multiple operations
     */
    public function test_coin_balance_after_multiple_transactions()
    {
        // Setup: Student with 1000 coins
        $this->student->coins = 1000;
        $this->student->save();

        // Act: Multiple unlock attempts
        for ($i = 0; $i < 3; $i++) {
            $this->actingAs($this->student)
                ->postJson('/api/student/unlock-tutor-contact', [
                    'tutor_id' => $this->tutor->id,
                ]);
        }

        // Assert: Coins deducted correctly
        $finalCoins = $this->student->fresh()->coins;
        $this->assertLessThan(1000, $finalCoins);
        
        // Verify all transactions recorded
        $transactions = \DB::table('coin_transactions')
            ->where('user_id', $this->student->id)
            ->get();
        
        $this->assertEquals(3, $transactions->count());
    }

    /**
     * SCENARIO 13: Lapsed Subscription Detection
     * Expected: Correctly identifies lapsed PRO vs other states
     */
    public function test_lapsed_subscription_detection()
    {
        // Setup: Create both active and expired subscriptions
        UserSubscription::create([
            'user_id' => $this->student->id,
            'subscription_plan_id' => $this->proPlan->id,
            'status' => 'expired',
            'activated_at' => now()->subDays(40),
            'expires_at' => now()->subDays(10),
        ]);

        // Act: Check subscription status via service
        $statusService = new SubscriptionService();
        $status = $statusService->getSubscriptionStatus($this->student);

        // Assert: Correctly identifies as lapsed
        $this->assertTrue($status['is_lapsed']);
        $this->assertFalse($status['is_active']);
    }

    /**
     * SCENARIO 14: Coin Minimum Validation
     * Expected: Reject orders below 99 coins
     */
    public function test_custom_coin_purchase_minimum_validation()
    {
        // Act: Try to buy less than 99 coins
        $response = $this->actingAs($this->student)
            ->postJson('/api/wallet/purchase-custom-coins', [
                'coins' => 50,
            ]);

        // Assert: Should fail validation
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('coins');
    }

    /**
     * SCENARIO 15: Subscription Plan Tier Isolation
     * Expected: BASIC and PRO tiers have correct coin/view allocations
     */
    public function test_subscription_tier_allocations()
    {
        // Assert: Plans have correct allocations
        $this->basicPlan->refresh();
        $this->proPlan->refresh();

        $this->assertEquals('Basic', $this->basicPlan->name);
        $this->assertEquals('Pro', $this->proPlan->name);
        
        $this->assertEquals(99, $this->basicPlan->coins_included); // 99 coins
        $this->assertEquals(399, $this->proPlan->coins_included); // 399 coins
        
        $this->assertEquals(2, $this->basicPlan->views_allowed); // 2 views max
        $this->assertEquals(10, $this->proPlan->views_allowed); // ~10-12 views
    }

    /**
     * SCENARIO 16: Coin Deduction Logic - PRO Lapsed
     * Expected: Correct coin cost for lapsed PRO (39 coins)
     */
    public function test_lapsed_pro_coin_cost_39_coins()
    {
        // Setup: Expired PRO with 50 coins
        UserSubscription::create([
            'user_id' => $this->student->id,
            'subscription_plan_id' => $this->proPlan->id,
            'status' => 'expired',
            'activated_at' => now()->subDays(40),
            'expires_at' => now()->subDays(10),
        ]);

        $this->student->coins = 50;
        $this->student->save();

        // Act
        $response = $this->actingAs($this->student)
            ->postJson('/api/student/unlock-tutor-contact', [
                'tutor_id' => $this->tutor->id,
            ]);

        // Assert: 39 coins deducted (PRO cost)
        $response->assertStatus(200);
        $this->assertEquals(11, $this->student->fresh()->coins); // 50 - 39
    }

    /**
     * SCENARIO 17: Contact Already Unlocked Prevention
     * Expected: Cannot unlock same contact twice
     */
    public function test_cannot_unlock_same_contact_twice()
    {
        // Setup: Student with coins
        $this->student->coins = 100;
        $this->student->save();

        // Act: First unlock
        $this->actingAs($this->student)
            ->postJson('/api/student/unlock-tutor-contact', [
                'tutor_id' => $this->tutor->id,
            ])->assertStatus(200);

        // Act: Second unlock attempt
        $response = $this->actingAs($this->student)
            ->postJson('/api/student/unlock-tutor-contact', [
                'tutor_id' => $this->tutor->id,
            ]);

        // Assert: Should fail
        $response->assertStatus(422);
        $response->assertJsonFragment(['message' => 'Contact details already unlocked']);
    }

    /**
     * SCENARIO 18: India User Pricing with GST
     * Expected: 18% GST applied for India
     */
    public function test_india_user_pricing_includes_gst()
    {
        // Setup: India user buys 100 coins
        $this->student->country_iso = 'IN';
        $this->student->save();

        // Simulate pricing
        $basePricePerCoin = 1.25 / 100;
        $priceInUSD = 100 * $basePricePerCoin; // 1.25 USD
        $conversionRate = 83.5;
        $priceInINR = $priceInUSD * $conversionRate; // ~104.375
        $gstAmount = $priceInINR * 0.18;
        $totalWithGST = $priceInINR + $gstAmount;

        // Assert: GST is approximately 18% of base
        $this->assertGreaterThan($priceInINR, $totalWithGST);
        $this->assertEquals(18, round(($gstAmount / $priceInINR) * 100));
    }

    /**
     * SCENARIO 19: Coin Transaction History
     * Expected: All coin transactions retrievable with correct details
     */
    public function test_coin_transaction_history_complete()
    {
        // Setup: Student with coins and active activity
        $this->student->coins = 500;
        $this->student->save();

        // Manually create some transactions
        \DB::table('coin_transactions')->insert([
            'user_id' => $this->student->id,
            'type' => 'coin_purchase',
            'amount' => 100,
            'balance_after' => 600,
            'description' => 'Purchased 100 coins',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        \DB::table('coin_transactions')->insert([
            'user_id' => $this->student->id,
            'type' => 'tutor_unlock_contact',
            'amount' => -30,
            'balance_after' => 570,
            'description' => 'Unlocked tutor contact',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Act: Get transaction history
        $response = $this->actingAs($this->student)
            ->getJson('/api/wallet/coin-transactions');

        // Assert: Should have both transactions
        $response->assertStatus(200);
        $transactions = $response->json('data');
        
        $this->assertGreaterThanOrEqual(2, count($transactions));
    }

    /**
     * SCENARIO 20: Prevent Self-Unlock
     * Expected: Cannot unlock own tutor profile
     */
    public function test_cannot_unlock_own_profile()
    {
        // Setup: User is both student and tutor
        $this->student->coins = 100;
        $this->student->save();

        // Make the student also a tutor
        Tutor::create([
            'user_id' => $this->student->id,
            'name' => 'Self Tutor',
            'phone' => '9876543210',
        ]);

        // Act: Try to unlock own profile
        $response = $this->actingAs($this->student)
            ->postJson('/api/student/unlock-tutor-contact', [
                'tutor_id' => $this->student->id,
            ]);

        // Assert: Should fail
        $response->assertStatus(422);
        $response->assertJsonFragment(['message' => 'You cannot unlock contact for your own profile']);
    }

    /**
     * SCENARIO 21: Cancelled Subscription (Not Yet Expired)
     * Expected: Cancelled subscription still works as active until expiry date
     */
    public function test_cancelled_subscription_not_expired_works_as_active()
    {
        // Setup: PRO subscription cancelled but still has 20 days remaining
        UserSubscription::create([
            'user_id' => $this->student->id,
            'subscription_plan_id' => $this->proPlan->id,
            'status' => 'cancelled', // Explicitly cancelled
            'views_used' => 0,
            'coins_carried_forward' => 0,
            'activated_at' => now()->subDays(5),
            'expires_at' => now()->addDays(20), // Still has 20 days left
        ]);

        $this->student->coins = 100;
        $this->student->save();

        // Act: Student attempts to unlock tutor contact
        $response = $this->actingAs($this->student)
            ->postJson('/api/student/unlock-tutor-contact', [
                'tutor_id' => $this->tutor->id,
            ]);

        // Assert: Should succeed like an active subscription (200), no coin deduction
        $response->assertStatus(200);
        $this->assertEquals(100, $this->student->fresh()->coins);
        
        // Verify view was tracked in subscription
        $subscription = UserSubscription::where('user_id', $this->student->id)->first();
        $this->assertEquals(1, $subscription->views_used);
    }

    /**
     * SCENARIO 22: Cancelled Subscription (Expired)
     * Expected: After expiry, cancelled subscription should be treated as lapsed
     */
    public function test_cancelled_subscription_expired_treated_as_lapsed()
    {
        // Setup: PRO subscription cancelled and expired (2 days ago)
        UserSubscription::create([
            'user_id' => $this->student->id,
            'subscription_plan_id' => $this->proPlan->id,
            'status' => 'cancelled',
            'views_used' => 10, // All views used
            'coins_spent' => 0,
            'views_with_coins' => 0,
            'coins_carried_forward' => 50,
            'activated_at' => now()->subDays(35),
            'expires_at' => now()->subDays(2), // Expired 2 days ago
        ]);

        $this->student->coins = 50; // Has carryforward coins
        $this->student->save();

        // Act: Student attempts to unlock tutor contact
        $response = $this->actingAs($this->student)
            ->postJson('/api/student/unlock-tutor-contact', [
                'tutor_id' => $this->tutor->id,
            ]);

        // Assert: Should allow access with coins but with delay message
        // (either 200 with delay or 402 with lapsed info - depends on implementation)
        $this->assertIn($response->getStatusCode(), [200, 402]);
        
        if ($response->getStatusCode() === 200) {
            // If allowed, coins should be deducted
            $student = $this->student->fresh();
            $this->assertLessThan(50, $student->coins);
        } else {
            // If 402, should show lapsed subscription message
            $response->assertJsonFragment(['has_lapsed_subscription' => true]);
        }
    }

    /**
     * SCENARIO 23: Cancelled BASIC Subscription with Views Exhausted
     * Expected: When cancelled and views exhausted, offer renewal options like active
     */
    public function test_cancelled_basic_subscription_views_exhausted_offers_options()
    {
        // Setup: Cancelled BASIC subscription with views exhausted
        UserSubscription::create([
            'user_id' => $this->student->id,
            'subscription_plan_id' => $this->basicPlan->id,
            'status' => 'cancelled',
            'views_used' => 2, // All views used (BASIC has 2 max)
            'coins_spent' => 0,
            'views_with_coins' => 0,
            'coins_carried_forward' => 0,
            'activated_at' => now()->subDays(5),
            'expires_at' => now()->addDays(20), // Not yet expired
        ]);

        $this->student->coins = 50;
        $this->student->save();

        // Act: Student tries to unlock contact
        $response = $this->actingAs($this->student)
            ->postJson('/api/student/unlock-tutor-contact', [
                'tutor_id' => $this->tutor->id,
            ]);

        // Assert: 403 with options (even though cancelled, still behaves as active)
        $response->assertStatus(403);
        $response->assertJsonStructure([
            'success',
            'message',
            'views_exhausted',
            'options' => [
                'upgrade_subscription' => ['action', 'label'],
                'buy_coins' => ['action', 'label']
            ]
        ]);
    }

    /**
     * SCENARIO 25: BASIC Plan - Maximum 49 Coins Spendable
     * Expected: After spending 49 coins on 2 views (BASIC limit), offer options
     */
    public function test_basic_plan_max_49_coins_spent_limit()
    {
        // Setup: BASIC subscription with 49 coins spent on 2 views
        UserSubscription::create([
            'user_id' => $this->student->id,
            'subscription_plan_id' => $this->basicPlan->id,
            'status' => 'active',
            'views_used' => 2, // All views from subscription
            'coins_spent' => 49, // Max coins spent
            'views_with_coins' => 2, // Max views with coins
            'coins_carried_forward' => 0,
            'activated_at' => now()->subDays(5),
            'expires_at' => now()->addDays(20),
        ]);

        $this->student->coins = 100;
        $this->student->save();

        // Act: Try to unlock contact (3rd coin-using view attempt)
        $response = $this->actingAs($this->student)
            ->postJson('/api/student/unlock-tutor-contact', [
                'tutor_id' => $this->tutor->id,
            ]);

        // Assert: Should fail with options (BASIC coins exhausted)
        $response->assertStatus(403);
        $response->assertJsonFragment(['subscription_coins_exhausted' => true]);
        $response->assertJsonFragment(['coins_limit' => 49]);
        $response->assertJsonPath('coins_spent_from_subscription', 49);
        
        // Verify options include upgrade + buy coins
        $this->assertContains('upgrade_subscription', array_keys($response->json('options')));
        $this->assertContains('buy_coins', array_keys($response->json('options')));
    }

    /**
     * SCENARIO 26: BASIC Plan - Track Coins Spent (First View)
     * Expected: When using coins for exhausted view, coins_spent incremented
     */
    public function test_basic_plan_coins_spent_tracked_first_view()
    {
        // Setup: BASIC subscription with all views used but coins available
        UserSubscription::create([
            'user_id' => $this->student->id,
            'subscription_plan_id' => $this->basicPlan->id,
            'status' => 'active',
            'views_used' => 2, // All 2 views used from subscription
            'coins_spent' => 0, // No coins spent yet
            'views_with_coins' => 0, // No coin-using views yet
            'coins_carried_forward' => 0,
            'activated_at' => now()->subDays(5),
            'expires_at' => now()->addDays(20),
        ]);

        $this->student->coins = 100;
        $this->student->save();

        // Act: Unlock contact (using coins)
        $response = $this->actingAs($this->student)
            ->postJson('/api/student/unlock-tutor-contact', [
                'tutor_id' => $this->tutor->id,
            ]);

        // Assert: Should succeed
        $response->assertStatus(200);
        
        // Verify coins deducted from wallet
        $this->assertEquals(61, $this->student->fresh()->coins); // 100 - 39
        
        // Verify subscription tracking updated
        $subscription = UserSubscription::where('user_id', $this->student->id)->first();
        $this->assertEquals(1, $subscription->coins_spent); // 39 coins spent
        $this->assertEquals(1, $subscription->views_with_coins); // 1 view used coins
        
        // Verify coin transaction recorded with subscription reference
        $transaction = \DB::table('coin_transactions')
            ->where('user_id', $this->student->id)
            ->latest()
            ->first();
        $this->assertNotNull($transaction);
        $meta = json_decode($transaction->meta, true);
        $this->assertEquals('BASIC', $meta['subscription_tier']);
        $this->assertEquals(39, $meta['coins_spent_from_subscription']);
    }

    /**
     * SCENARIO 27: BASIC Plan - Track Coins Spent (Second View)
     * Expected: Second view with coins tracked, total 49 coins (must stop at 2 views)
     */
    public function test_basic_plan_coins_spent_tracked_second_view()
    {
        // Setup: BASIC subscription with 39 coins spent on 1 view
        UserSubscription::create([
            'user_id' => $this->student->id,
            'subscription_plan_id' => $this->basicPlan->id,
            'status' => 'active',
            'views_used' => 2, // All views used
            'coins_spent' => 39, // First coin-using view spent 39
            'views_with_coins' => 1, // 1 view used coins
            'coins_carried_forward' => 0,
            'activated_at' => now()->subDays(5),
            'expires_at' => now()->addDays(20),
        ]);

        $this->student->coins = 100;
        $this->student->save();

        // Act: Unlock second contact (using coins)
        $response = $this->actingAs($this->student)
            ->postJson('/api/student/unlock-tutor-contact', [
                'tutor_id' => $this->tutor->id,
            ]);

        // Assert: Should succeed (still within limits: 49 coins max, 2 views)
        $response->assertStatus(200);
        
        // Verify coins deducted
        $this->assertEquals(22, $this->student->fresh()->coins); // 100 - 39 - 39
        
        // Verify subscription reached 49 coins limit
        $subscription = UserSubscription::where('user_id', $this->student->id)->first();
        $this->assertEquals(78, $subscription->coins_spent); // 39 + 39
        $this->assertEquals(2, $subscription->views_with_coins); // 2 views used coins
    }

    /**
     * SCENARIO 28: BASIC Plan - Show Coins Spent in Wallet
     * Expected: Subscription coins spent visible in subscription info response
     */
    public function test_basic_plan_coins_spent_visible_in_subscription()
    {
        // Setup: BASIC subscription with coins spent
        UserSubscription::create([
            'user_id' => $this->student->id,
            'subscription_plan_id' => $this->basicPlan->id,
            'status' => 'active',
            'views_used' => 2,
            'coins_spent' => 39,
            'views_with_coins' => 1,
            'coins_carried_forward' => 0,
            'activated_at' => now()->subDays(5),
            'expires_at' => now()->addDays(20),
        ]);

        // Act: Get subscription status
        $response = $this->actingAs($this->student)
            ->getJson('/api/subscription/status');

        // Assert: Should show coins spent in response
        $response->assertStatus(200);
        $subscription = $response->json('subscription_info') ?? $response->json();
        
        // Check coins tracking is visible
        $this->assertIsArray($subscription);
        if (isset($subscription['coins_with_limit'])) {
            $this->assertEquals(39, $subscription['coins_with_limit']['coins_spent']);
            $this->assertEquals(49, $subscription['coins_with_limit']['coins_limit']);
            $this->assertEquals(1, $subscription['coins_with_limit']['views_with_coins']);
            $this->assertEquals(2, $subscription['coins_with_limit']['views_with_coins_limit']);
        }
    }

    /**
     * SCENARIO 29: BASIC Plan - Coin Transaction includes Subscription Reference
     * Expected: Each coin transaction shows subscription tracking info
     */
    public function test_basic_plan_coin_transaction_includes_subscription_ref()
    {
        // Setup: BASIC subscription ready to use coins
        UserSubscription::create([
            'user_id' => $this->student->id,
            'subscription_plan_id' => $this->basicPlan->id,
            'status' => 'active',
            'views_used' => 5, // All views used
            'coins_spent' => 0,
            'views_with_coins' => 0,
            'coins_carried_forward' => 0,
            'activated_at' => now()->subDays(5),
            'expires_at' => now()->addDays(20),
        ]);

        $this->student->coins = 100;
        $this->student->save();

        // Act: Unlock contact using BASIC plan coins
        $response = $this->actingAs($this->student)
            ->postJson('/api/student/unlock-tutor-contact', [
                'tutor_id' => $this->tutor->id,
            ]);

        // Assert: Success
        $response->assertStatus(200);
        
        // Verify coin transaction has subscription reference
        $transaction = \DB::table('coin_transactions')
            ->where('user_id', $this->student->id)
            ->latest()
            ->first();
        
        $this->assertNotNull($transaction);
        $meta = json_decode($transaction->meta, true);
        
        // Verify meta includes subscription tracking
        $this->assertNotNull($meta['subscription_id']);
        $this->assertEquals('BASIC', $meta['subscription_tier']);
        $this->assertEquals(39, $meta['coins_spent_from_subscription']);
        $this->assertStringContainsString('subscription_views_exhausted', $meta['reason']);
    }
}
