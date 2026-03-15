<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Tutor;
use App\Models\Student;
use App\Models\Subject;
use App\Models\StudentRequirement;
use App\Models\EnquiryUnlock;
use App\Models\SubscriptionPlan;
use App\Models\UserSubscription;
use App\Services\EnquiryService;
use App\Services\DynamicPricingService;
use App\Services\TutorRankingService;
use App\Services\WalletService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

/**
 * Comprehensive test suite covering all unlock/approach scenarios:
 *
 *  A. No Subscription (coin-only)
 *     A1 — sufficient coins → unlock succeeds, coins deducted
 *     A2 — insufficient coins → 422, no unlock
 *
 *  B. Active Subscription with views remaining
 *     B1 — unlimited plan → unlock free, coins untouched
 *     B2 — limited plan (views remaining) → unlock free, view count incremented
 *
 *  C. Subscription exhausted (views_used >= views_allowed)
 *     C1 — sufficient coins, use_coins=false → 403 with choice data
 *     C2 — sufficient coins, use_coins=true  → coins deducted, unlock succeeds
 *     C3 — insufficient coins, use_coins=true → 422
 *
 *  D. Dynamic Pricing
 *     D1 — demand level classified correctly per subject
 *     D2 — region multiplier applied
 *     D3 — competition multiplier raises price per unlock
 *     D4 — 36-hour decay sets price to 0
 *     D5 — decay does NOT apply when ≥3 applicants
 *
 *  E. Tutor Ranking & Early Access
 *     E1 — rank 1 tutor sees requirements immediately
 *     E2 — high-delay tutor cannot see recently posted requirement
 *     E3 — calculateEarlyAccessMinutes matches spec (rank10=20, rank100=120)
 *     E4 — equal bids get different rotation_order (daily tiebreaking)
 *
 *  F. Auto-Refund Logic
 *     F1 — stale unlock (15d, no view, unverified phone) → refund issued
 *     F2 — stale unlock but phone present → no refund
 *     F3 — spam requirement → all tutors refunded
 *
 *  G. Student Approach Flow (coin + subscription variants)
 *     G1 — approach with subscription free
 *     G2 — approach coin deduction
 *     G3 — approach while views exhausted returns choice payload
 */
class EnquiryUnlockFlowTest extends TestCase
{
    use RefreshDatabase;

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function makeStudent(array $userAttrs = []): array
    {
        $user = User::factory()->create(array_merge([
            'coins'       => 0,
            'country_iso' => 'IN',
        ], $userAttrs));
        $user->assignRole('student');
        $student = Student::create(['user_id' => $user->id]);
        return [$user, $student];
    }

    private function makeTutor(array $userAttrs = [], array $tutorAttrs = []): array
    {
        $user = User::factory()->create(array_merge([
            'coins'       => 0,
            'country_iso' => 'IN',
        ], $userAttrs));
        $user->assignRole('tutor');
        $tutor = Tutor::create(array_merge([
            'user_id'           => $user->id,
            'moderation_status' => 'approved',
            'is_disabled'       => false,
            'monthly_bid'       => 0,
            'early_access_minutes' => 0,
        ], $tutorAttrs));
        return [$user, $tutor];
    }

    private function makeRequirement(Student $student, array $attrs = []): StudentRequirement
    {
        $subject = Subject::firstOrCreate(['name' => 'Mathematics']);
        return StudentRequirement::create(array_merge([
            'student_id'    => $student->id,
            'subject_id'    => $subject->id,
            'status'        => 'active',
            'lead_status'   => 'open',
            'current_leads' => 0,
            'max_leads'     => 5,
            'post_fee'      => 0,
            'unlock_price'  => 49,
            'posted_at'     => now(),
            'city'          => 'Mumbai',
            'phone'         => '9999999999',
            'country_code'  => '+91',
            'dynamic_price' => 0,
            'base_price'    => 0,
        ], $attrs));
    }

    private function giveSubscription(User $user, int $viewsAllowed = null, int $viewsUsed = 0): UserSubscription
    {
        $plan = SubscriptionPlan::create([
            'name'          => $viewsAllowed === null ? 'Unlimited Plan' : 'Basic Plan',
            'price'         => 399,
            'validity_days' => 30,
            'views_allowed' => $viewsAllowed,
            'is_active'     => true,
        ]);

        return UserSubscription::create([
            'user_id'              => $user->id,
            'subscription_plan_id' => $plan->id,
            'activated_at'         => now(),
            'expires_at'           => now()->addDays(30),
            'views_used'           => $viewsUsed,
            'status'               => 'active',
        ]);
    }

    private function unlockPrice(User $user): int
    {
        return $user->country_iso === 'IN'
            ? (int) config('enquiry.pricing_by_nationality.unlock.indian', 49)
            : (int) config('enquiry.pricing_by_nationality.unlock.non_indian', 99);
    }

    // =========================================================================
    // A. NO SUBSCRIPTION — coin-only path
    // =========================================================================

    /** @test */
    public function A1_unlock_succeeds_with_sufficient_coins_no_subscription(): void
    {
        [$sUser, $student]   = $this->makeStudent();
        [$tUser, $tutor]     = $this->makeTutor(['coins' => 200]);
        $req                 = $this->makeRequirement($student);
        $cost                = $this->unlockPrice($tUser); // 49 for IN

        $this->actingAs($tUser)
             ->postJson("/api/enquiry/{$req->id}/unlock")
             ->assertOk()
             ->assertJsonPath('charged', true)
             ->assertJsonPath('coins_charged', $cost);

        $this->assertEquals(200 - $cost, $tUser->fresh()->coins);
        $this->assertDatabaseHas('enquiry_unlocks', ['enquiry_id' => $req->id, 'tutor_id' => $tutor->id]);
        $this->assertEquals(1, $req->fresh()->current_leads);
    }

    /** @test */
    public function A2_unlock_fails_with_insufficient_coins_no_subscription(): void
    {
        [$sUser, $student] = $this->makeStudent();
        [$tUser, $tutor]   = $this->makeTutor(['coins' => 10]); // not enough
        $req               = $this->makeRequirement($student);

        $this->actingAs($tUser)
             ->postJson("/api/enquiry/{$req->id}/unlock")
             ->assertStatus(422)
             ->assertJsonStructure(['message', 'required', 'balance']);

        $this->assertDatabaseMissing('enquiry_unlocks', ['enquiry_id' => $req->id]);
        $this->assertEquals(10, $tUser->fresh()->coins);
    }

    // =========================================================================
    // B. ACTIVE SUBSCRIPTION — views remaining
    // =========================================================================

    /** @test */
    public function B1_unlimited_subscription_unlocks_free_without_coin_deduction(): void
    {
        [$sUser, $student] = $this->makeStudent();
        [$tUser, $tutor]   = $this->makeTutor(['coins' => 100]);
        $req               = $this->makeRequirement($student);

        $this->giveSubscription($tUser, viewsAllowed: null); // unlimited

        $this->actingAs($tUser)
             ->postJson("/api/enquiry/{$req->id}/unlock")
             ->assertOk()
             ->assertJsonPath('charged', true)
             ->assertJsonPath('coins_charged', 0)
             ->assertJsonPath('used_subscription', true);

        $this->assertEquals(100, $tUser->fresh()->coins); // coins untouched
        $this->assertDatabaseHas('enquiry_unlocks', ['enquiry_id' => $req->id, 'tutor_id' => $tutor->id]);
    }

    /** @test */
    public function B2_limited_subscription_with_views_remaining_unlocks_free_and_increments_view_count(): void
    {
        [$sUser, $student] = $this->makeStudent();
        [$tUser, $tutor]   = $this->makeTutor(['coins' => 100]);
        $req               = $this->makeRequirement($student);

        $sub = $this->giveSubscription($tUser, viewsAllowed: 5, viewsUsed: 2);

        $this->actingAs($tUser)
             ->postJson("/api/enquiry/{$req->id}/unlock")
             ->assertOk()
             ->assertJsonPath('coins_charged', 0)
             ->assertJsonPath('used_subscription', true);

        $this->assertEquals(100, $tUser->fresh()->coins);
        $this->assertEquals(3, $sub->fresh()->views_used); // incremented
    }

    // =========================================================================
    // C. SUBSCRIPTION EXHAUSTED
    // =========================================================================

    /** @test */
    public function C1_exhausted_subscription_without_use_coins_flag_returns_403_with_choice_data(): void
    {
        [$sUser, $student] = $this->makeStudent();
        [$tUser, $tutor]   = $this->makeTutor(['coins' => 200]);
        $req               = $this->makeRequirement($student);

        $this->giveSubscription($tUser, viewsAllowed: 5, viewsUsed: 5); // exhausted

        $response = $this->actingAs($tUser)
                         ->postJson("/api/enquiry/{$req->id}/unlock")
                         ->assertStatus(403);

        $response->assertJsonPath('views_exhausted', true)
                 ->assertJsonStructure(['views_exhausted', 'views_used', 'views_allowed',
                                        'coin_cost_alternative', 'coins_available', 'can_pay_with_coins']);

        $this->assertDatabaseMissing('enquiry_unlocks', ['enquiry_id' => $req->id]);
        $this->assertEquals(200, $tUser->fresh()->coins); // untouched
    }

    /** @test */
    public function C2_exhausted_subscription_with_use_coins_flag_deducts_coins_and_unlocks(): void
    {
        [$sUser, $student] = $this->makeStudent();
        [$tUser, $tutor]   = $this->makeTutor(['coins' => 200]);
        $req               = $this->makeRequirement($student);
        $cost              = $this->unlockPrice($tUser);

        $this->giveSubscription($tUser, viewsAllowed: 5, viewsUsed: 5);

        $this->actingAs($tUser)
             ->postJson("/api/enquiry/{$req->id}/unlock", ['use_coins' => true])
             ->assertOk()
             ->assertJsonPath('charged', true);

        $this->assertEquals(200 - $cost, $tUser->fresh()->coins);
        $this->assertDatabaseHas('enquiry_unlocks', ['enquiry_id' => $req->id, 'tutor_id' => $tutor->id]);
    }

    /** @test */
    public function C3_exhausted_subscription_use_coins_but_insufficient_balance_returns_422(): void
    {
        [$sUser, $student] = $this->makeStudent();
        [$tUser, $tutor]   = $this->makeTutor(['coins' => 10]); // not enough
        $req               = $this->makeRequirement($student);

        $this->giveSubscription($tUser, viewsAllowed: 5, viewsUsed: 5);

        $this->actingAs($tUser)
             ->postJson("/api/enquiry/{$req->id}/unlock", ['use_coins' => true])
             ->assertStatus(422)
             ->assertJsonStructure(['message', 'required', 'balance']);

        $this->assertDatabaseMissing('enquiry_unlocks', ['enquiry_id' => $req->id]);
    }

    // =========================================================================
    // D. DYNAMIC PRICING
    // =========================================================================

    /** @test */
    public function D1_demand_level_classified_correctly_for_high_demand_subject(): void
    {
        $service = new DynamicPricingService();

        [$sUser, $student] = $this->makeStudent();
        $req               = $this->makeRequirement($student);

        // Set a high-demand subject name via the relationship
        $subject = Subject::firstOrCreate(['name' => 'Python Programming']);
        $req->subjects()->sync([$subject->id]);
        $req->load('subjects');

        $this->assertEquals('high', $service->classifyDemandLevel($req));
    }

    /** @test */
    public function D1b_demand_level_classified_as_medium_for_music_subject(): void
    {
        $service = new DynamicPricingService();

        [$sUser, $student] = $this->makeStudent();
        $req               = $this->makeRequirement($student);
        $subject           = Subject::firstOrCreate(['name' => 'Piano']);
        $req->subjects()->sync([$subject->id]);
        $req->load('subjects');

        $this->assertEquals('medium', $service->classifyDemandLevel($req));
    }

    /** @test */
    public function D1c_demand_level_falls_back_to_low_for_unknown_subject(): void
    {
        $service = new DynamicPricingService();

        [$sUser, $student] = $this->makeStudent();
        $req               = $this->makeRequirement($student, ['other_subject' => 'Ancient Pottery']);
        $req->load('subjects', 'subject');

        $this->assertEquals('low', $service->classifyDemandLevel($req));
    }

    /** @test */
    public function D2_region_multiplier_higher_for_high_income_country(): void
    {
        $service = new DynamicPricingService();

        $indiaPrice = $service->computeBasePrice('high', 'india');
        $usPrice    = $service->computeBasePrice('high', 'high_income');

        $this->assertGreaterThan($indiaPrice, $usPrice);
        $this->assertEquals(2.0, config('enquiry.dynamic_pricing.region_multipliers.high_income'));
    }

    /** @test */
    public function D3_competition_multiplier_raises_dynamic_price_with_each_unlock(): void
    {
        config(['enquiry.fees.dynamic_pricing' => true]);
        Queue::fake();

        [$sUser, $student] = $this->makeStudent();
        [$tUser1, $tutor1] = $this->makeTutor(['coins' => 500]);
        [$tUser2, $tutor2] = $this->makeTutor(['coins' => 500]);
        $req               = $this->makeRequirement($student, [
            'demand_level'  => 'high',
            'base_price'    => 30,
            'dynamic_price' => 30,
            'region_code'   => 'india',
        ]);

        $service = new DynamicPricingService();

        // After 0 unlocks: price should stay at base
        $this->assertEquals(30, $req->dynamic_price);

        // Simulate 1 unlock → recalculate
        $req->increment('current_leads');
        $service->recalculatePrice($req->fresh());
        $priceAfter1 = $req->fresh()->dynamic_price;

        // Simulate 2nd unlock
        $req->increment('current_leads');
        $service->recalculatePrice($req->fresh());
        $priceAfter2 = $req->fresh()->dynamic_price;

        $this->assertGreaterThan(30, $priceAfter1);
        $this->assertGreaterThan($priceAfter1, $priceAfter2);
    }

    /** @test */
    public function D4_decay_rule_sets_price_to_zero_after_36h_with_fewer_than_3_applicants(): void
    {
        config(['enquiry.fees.dynamic_pricing' => true]);

        [$sUser, $student] = $this->makeStudent();
        $req               = $this->makeRequirement($student, [
            'posted_at'     => now()->subHours(40), // 40h old
            'current_leads' => 1,                   // only 1 applicant
            'dynamic_price' => 30,
            'base_price'    => 30,
            'price_decayed' => false,
        ]);

        $service = new DynamicPricingService();
        $decayed = $service->checkAndApplyDecay($req);

        $this->assertTrue($decayed);
        $this->assertEquals(0, $req->fresh()->dynamic_price);
        $this->assertTrue((bool) $req->fresh()->price_decayed);
    }

    /** @test */
    public function D5_decay_does_not_apply_when_requirement_has_3_or_more_applicants(): void
    {
        config(['enquiry.fees.dynamic_pricing' => true]);

        [$sUser, $student] = $this->makeStudent();
        $req               = $this->makeRequirement($student, [
            'posted_at'     => now()->subHours(40),
            'current_leads' => 3, // exactly at threshold
            'dynamic_price' => 30,
            'base_price'    => 30,
            'price_decayed' => false,
        ]);

        $service = new DynamicPricingService();
        $decayed = $service->checkAndApplyDecay($req);

        $this->assertFalse($decayed);
        $this->assertEquals(30, $req->fresh()->dynamic_price);
        $this->assertFalse((bool) $req->fresh()->price_decayed);
    }

    // =========================================================================
    // E. TUTOR RANKING & EARLY ACCESS
    // =========================================================================

    /** @test */
    public function E1_rank1_tutor_sees_requirements_immediately(): void
    {
        [$sUser, $student] = $this->makeStudent();
        [$tUser, $tutor]   = $this->makeTutor(['coins' => 0], [
            'monthly_bid'          => 3000,
            'early_access_minutes' => 0,  // rank 1 → 0 delay
        ]);

        // Requirement posted just now
        $req = $this->makeRequirement($student, ['posted_at' => now()]);

        $response = $this->actingAs($tUser)
                         ->getJson('/api/enquiries')
                         ->assertOk();

        $ids = collect($response->json('data'))->pluck('id')->toArray();
        $this->assertContains($req->id, $ids, 'Rank-1 tutor should see a freshly-posted requirement');
    }

    /** @test */
    public function E2_high_delay_tutor_cannot_see_recently_posted_requirement(): void
    {
        [$sUser, $student] = $this->makeStudent();
        [$tUser, $tutor]   = $this->makeTutor(['coins' => 0], [
            'monthly_bid'          => 0,
            'early_access_minutes' => 120, // 2-hour delay
        ]);

        // Requirement posted 60 minutes ago — still inside the 120-min window
        $req = $this->makeRequirement($student, ['posted_at' => now()->subMinutes(60)]);

        $response = $this->actingAs($tUser)
                         ->getJson('/api/enquiries')
                         ->assertOk();

        $ids = collect($response->json('data'))->pluck('id')->toArray();
        $this->assertNotContains($req->id, $ids, 'Low-rank tutor must not see requirement posted 60min ago when delay is 120min');
    }

    /** @test */
    public function E3_early_access_formula_matches_spec_values(): void
    {
        $service = new TutorRankingService();

        $this->assertEquals(0,   $service->calculateEarlyAccessMinutes(1),   'Rank 1 → 0 min');
        $this->assertEquals(20,  $service->calculateEarlyAccessMinutes(10),  'Rank 10 → 20 min');
        $this->assertEquals(120, $service->calculateEarlyAccessMinutes(100), 'Rank 100 → 120 min');
        $this->assertLessThanOrEqual(120, $service->calculateEarlyAccessMinutes(200), 'Never exceeds max 120 min');
    }

    /** @test */
    public function E4_equal_bid_tutors_get_different_rotation_orders(): void
    {
        // Create 3 tutors with identical bids
        foreach (range(1, 3) as $_) {
            [$u, $t] = $this->makeTutor([], ['monthly_bid' => 1000]);
        }

        $service = new TutorRankingService();
        $service->recalculateAllRankings();

        $orders = Tutor::where('monthly_bid', 1000)->pluck('rotation_order')->toArray();

        // All rotation_orders must be distinct within the group
        $this->assertCount(count($orders), array_unique($orders), 'Each tied tutor should have a unique rotation_order');
    }

    // =========================================================================
    // F. AUTO-REFUND LOGIC
    // =========================================================================

    /** @test */
    public function F1_stale_unlock_with_unverified_phone_triggers_auto_refund(): void
    {
        [$sUser, $student] = $this->makeStudent();
        [$tUser, $tutor]   = $this->makeTutor(['coins' => 0]);

        // Requirement with empty phone (unverified)
        $req = $this->makeRequirement($student, [
            'phone'        => '',    // unverified
            'country_code' => '',
        ]);

        // Unlock created 16 days ago, never viewed by student
        $unlock = EnquiryUnlock::create([
            'enquiry_id'      => $req->id,
            'tutor_id'        => $tutor->id,
            'unlock_price'    => 49,
            'student_viewed_at' => null,
            'auto_refunded'   => false,
            'created_at'      => now()->subDays(16),
            'updated_at'      => now()->subDays(16),
        ]);

        $this->artisan('enquiry:auto-refunds')->assertExitCode(0);

        $this->assertTrue((bool) $unlock->fresh()->auto_refunded);
        $this->assertEquals(49, $tUser->fresh()->coins); // refunded
    }

    /** @test */
    public function F2_stale_unlock_with_verified_phone_does_not_trigger_refund(): void
    {
        [$sUser, $student] = $this->makeStudent();
        [$tUser, $tutor]   = $this->makeTutor(['coins' => 0]);

        $req = $this->makeRequirement($student, [
            'phone'        => '9999999999', // phone present
            'country_code' => '+91',
        ]);

        $unlock = EnquiryUnlock::create([
            'enquiry_id'      => $req->id,
            'tutor_id'        => $tutor->id,
            'unlock_price'    => 49,
            'student_viewed_at' => null,
            'auto_refunded'   => false,
            'created_at'      => now()->subDays(16),
            'updated_at'      => now()->subDays(16),
        ]);

        $this->artisan('enquiry:auto-refunds')->assertExitCode(0);

        $this->assertFalse((bool) $unlock->fresh()->auto_refunded);
        $this->assertEquals(0, $tUser->fresh()->coins); // no refund
    }

    /** @test */
    public function F3_spam_requirement_triggers_bulk_refund_to_all_tutors(): void
    {
        [$sUser, $student]  = $this->makeStudent();
        [$tUser1, $tutor1]  = $this->makeTutor(['coins' => 0]);
        [$tUser2, $tutor2]  = $this->makeTutor(['coins' => 0]);

        $req = $this->makeRequirement($student, ['lead_status' => 'spam']);

        EnquiryUnlock::insert([
            ['enquiry_id' => $req->id, 'tutor_id' => $tutor1->id, 'unlock_price' => 49,
             'auto_refunded' => false, 'created_at' => now(), 'updated_at' => now()],
            ['enquiry_id' => $req->id, 'tutor_id' => $tutor2->id, 'unlock_price' => 99,
             'auto_refunded' => false, 'created_at' => now(), 'updated_at' => now()],
        ]);

        $this->artisan('enquiry:auto-refunds')->assertExitCode(0);

        $this->assertEquals(49, $tUser1->fresh()->coins);
        $this->assertEquals(99, $tUser2->fresh()->coins);
        $this->assertEquals(2, EnquiryUnlock::where('enquiry_id', $req->id)
                                             ->where('auto_refunded', true)->count());
    }

    // =========================================================================
    // G. STUDENT APPROACH FLOW
    // =========================================================================

    /** @test */
    public function G1_student_approaches_tutor_free_with_active_subscription(): void
    {
        [$sUser, $student] = $this->makeStudent(['coins' => 0]);
        [$tUser, $tutor]   = $this->makeTutor(['coins' => 100]);
        $req               = $this->makeRequirement($student);

        // Tutor unlocks first (so they appear as interested)
        EnquiryUnlock::create([
            'enquiry_id'   => $req->id,
            'tutor_id'     => $tutor->id,
            'unlock_price' => 49,
        ]);

        $this->giveSubscription($sUser, viewsAllowed: null); // unlimited

        $this->actingAs($sUser)
             ->postJson("/api/student/requirements/{$req->id}/approach-teacher", [
                 'teacher_id' => $tutor->id,
             ])
             ->assertOk();

        $this->assertEquals(0, $sUser->fresh()->coins); // no coins spent
    }

    /** @test */
    public function G2_student_approaches_tutor_and_coins_deducted(): void
    {
        [$sUser, $student] = $this->makeStudent(['coins' => 200, 'country_iso' => 'IN']);
        [$tUser, $tutor]   = $this->makeTutor(['coins' => 100]);
        $req               = $this->makeRequirement($student);

        EnquiryUnlock::create([
            'enquiry_id'   => $req->id,
            'tutor_id'     => $tutor->id,
            'unlock_price' => 49,
        ]);

        // No subscription — coins will be deducted
        $this->actingAs($sUser)
             ->postJson("/api/student/requirements/{$req->id}/approach-teacher", [
                 'teacher_id' => $tutor->id,
             ])
             ->assertOk()
             ->assertJsonStructure(['message', 'coins_deducted']);

        $this->assertLessThan(200, $sUser->fresh()->coins);
    }

    /** @test */
    public function G3_approach_with_exhausted_subscription_returns_choice_payload(): void
    {
        [$sUser, $student] = $this->makeStudent(['coins' => 200]);
        [$tUser, $tutor]   = $this->makeTutor(['coins' => 100]);
        $req               = $this->makeRequirement($student);

        EnquiryUnlock::create([
            'enquiry_id'   => $req->id,
            'tutor_id'     => $tutor->id,
            'unlock_price' => 49,
        ]);

        $this->giveSubscription($sUser, viewsAllowed: 3, viewsUsed: 3); // exhausted

        $response = $this->actingAs($sUser)
                         ->postJson("/api/student/requirements/{$req->id}/approach-teacher", [
                             'teacher_id' => $tutor->id,
                         ])
                         ->assertStatus(403);

        $response->assertJsonPath('views_exhausted', true)
                 ->assertJsonStructure(['views_exhausted', 'coin_cost_alternative',
                                        'coins_available', 'can_pay_with_coins']);
    }

    // =========================================================================
    // H. NON-INDIAN TUTOR — higher coin pricing
    // =========================================================================

    /** @test */
    public function H1_non_indian_tutor_charged_higher_unlock_price(): void
    {
        [$sUser, $student] = $this->makeStudent();
        [$tUser, $tutor]   = $this->makeTutor(['coins' => 500, 'country_iso' => 'US']);
        $req               = $this->makeRequirement($student);

        $response = $this->actingAs($tUser)
                         ->postJson("/api/enquiry/{$req->id}/unlock")
                         ->assertOk();

        $nonIndianPrice = (int) config('enquiry.pricing_by_nationality.unlock.non_indian', 99);
        $response->assertJsonPath('coins_charged', $nonIndianPrice);
        $this->assertEquals(500 - $nonIndianPrice, $tUser->fresh()->coins);
    }

    /** @test */
    public function H2_indian_tutor_charged_lower_unlock_price(): void
    {
        [$sUser, $student] = $this->makeStudent();
        [$tUser, $tutor]   = $this->makeTutor(['coins' => 500, 'country_iso' => 'IN']);
        $req               = $this->makeRequirement($student);

        $response = $this->actingAs($tUser)
                         ->postJson("/api/enquiry/{$req->id}/unlock")
                         ->assertOk();

        $indianPrice = (int) config('enquiry.pricing_by_nationality.unlock.indian', 49);
        $response->assertJsonPath('coins_charged', $indianPrice);
        $this->assertEquals(500 - $indianPrice, $tUser->fresh()->coins);
    }

    // =========================================================================
    // I. DOUBLE-UNLOCK PREVENTION
    // =========================================================================

    /** @test */
    public function I1_second_unlock_of_same_requirement_by_same_tutor_is_idempotent(): void
    {
        [$sUser, $student] = $this->makeStudent();
        [$tUser, $tutor]   = $this->makeTutor(['coins' => 500]);
        $req               = $this->makeRequirement($student);
        $cost              = $this->unlockPrice($tUser);

        // First unlock
        $this->actingAs($tUser)->postJson("/api/enquiry/{$req->id}/unlock")->assertOk();
        $coinsAfterFirst = $tUser->fresh()->coins;

        // Second unlock — should return charged=false, no extra deduction
        $this->actingAs($tUser)
             ->postJson("/api/enquiry/{$req->id}/unlock")
             ->assertOk()
             ->assertJsonPath('charged', false);

        $this->assertEquals($coinsAfterFirst, $tUser->fresh()->coins); // no extra deduction
        $this->assertEquals(1, EnquiryUnlock::where('enquiry_id', $req->id)
                                             ->where('tutor_id', $tutor->id)->count());
    }

    // =========================================================================
    // J. MAX LEADS ENFORCEMENT
    // =========================================================================

    /** @test */
    public function J1_requirement_closes_when_max_leads_reached(): void
    {
        [$sUser, $student] = $this->makeStudent();
        $req               = $this->makeRequirement($student, ['max_leads' => 2]);

        [$tUser1, $tutor1] = $this->makeTutor(['coins' => 500]);
        [$tUser2, $tutor2] = $this->makeTutor(['coins' => 500]);
        [$tUser3, $tutor3] = $this->makeTutor(['coins' => 500]);

        $this->actingAs($tUser1)->postJson("/api/enquiry/{$req->id}/unlock")->assertOk();
        $this->actingAs($tUser2)->postJson("/api/enquiry/{$req->id}/unlock")->assertOk();

        // 3rd tutor should be rejected — max reached
        $this->actingAs($tUser3)
             ->postJson("/api/enquiry/{$req->id}/unlock")
             ->assertStatus(422);

        $this->assertEquals('full', $req->fresh()->lead_status);
        $this->assertEquals(2, $req->fresh()->current_leads);
    }
}
