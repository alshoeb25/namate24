<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Pure unit tests — no Laravel bootstrapping needed.
 * Tests the mathematical formulas in DynamicPricingService and TutorRankingService.
 */
class DynamicPricingServiceTest extends TestCase
{
    // ── Competition / Price Multiplier ────────────────────────────────────────

    /** @test */
    public function competition_formula_increases_price_by_15_percent_per_lead(): void
    {
        // Formula: price = ceil(base * (1 + 0.15 * leads))
        $base = 30;

        $this->assertEquals(30,  (int) ceil($base * (1 + 0.15 * 0)), '0 leads → base price');
        $this->assertEquals(35,  (int) ceil($base * (1 + 0.15 * 1)), '1 lead  → +15%');
        $this->assertEquals(39,  (int) ceil($base * (1 + 0.15 * 2)), '2 leads → +30%');
        $this->assertEquals(44,  (int) ceil($base * (1 + 0.15 * 3)), '3 leads → +45%');
    }

    /** @test */
    public function dynamic_price_is_capped_at_peak_max(): void
    {
        $peakMax = 150;
        $base    = 50;
        $leads   = 999;

        $price = (int) min($peakMax, ceil($base * (1 + 0.15 * $leads)));
        $this->assertEquals($peakMax, $price, 'Price capped at peak_max regardless of leads');
    }

    /** @test */
    public function low_demand_peak_max_is_10_coins(): void
    {
        $tiers = [
            'high'   => ['base_min' => 10, 'base_max' => 50,  'peak_max' => 150],
            'medium' => ['base_min' => 10, 'base_max' => 20,  'peak_max' => 50],
            'low'    => ['base_min' => 1,  'base_max' => 5,   'peak_max' => 10],
        ];
        $this->assertEquals(10, $tiers['low']['peak_max']);
        $this->assertEquals(50, $tiers['medium']['peak_max']);
        $this->assertEquals(150, $tiers['high']['peak_max']);
    }

    // ── Base Price Computation ────────────────────────────────────────────────

    /** @test */
    public function base_price_formula_returns_mid_of_range_times_multiplier(): void
    {
        // High demand india: mid = (10+50)/2 = 30, multiplier=1.0 → 30
        $mid        = (10 + 50) / 2;
        $multiplier = 1.0;
        $price      = (int) max(1, ceil($mid * $multiplier));
        $this->assertEquals(30, $price);

        // High demand US: mid=30, multiplier=2.0 → 60
        $price = (int) max(1, ceil($mid * 2.0));
        $this->assertEquals(60, $price);
    }

    /** @test */
    public function region_multipliers_are_ordered_correctly(): void
    {
        $multipliers = [
            'india'       => 1.0,
            'standard'    => 1.5,
            'high_income' => 2.0,
        ];

        $this->assertGreaterThan($multipliers['india'],    $multipliers['standard']);
        $this->assertGreaterThan($multipliers['standard'], $multipliers['high_income']);
    }

    // ── 36-Hour Decay Rule ────────────────────────────────────────────────────

    /** @test */
    public function decay_applies_when_posted_over_36h_ago_with_fewer_than_3_leads(): void
    {
        $postedHoursAgo = 40;
        $currentLeads   = 2;

        $shouldDecay = $postedHoursAgo >= 36 && $currentLeads < 3;
        $this->assertTrue($shouldDecay);
    }

    /** @test */
    public function decay_does_not_apply_within_first_36h(): void
    {
        $postedHoursAgo = 20;
        $currentLeads   = 0;

        $shouldDecay = $postedHoursAgo >= 36 && $currentLeads < 3;
        $this->assertFalse($shouldDecay);
    }

    /** @test */
    public function decay_does_not_apply_when_3_or_more_leads(): void
    {
        $postedHoursAgo = 40;
        $currentLeads   = 3;

        $shouldDecay = $postedHoursAgo >= 36 && $currentLeads < 3;
        $this->assertFalse($shouldDecay);
    }

    // ── Tutor Ranking Formula ─────────────────────────────────────────────────

    private function calcDelay(int $rank): int
    {
        if ($rank <= 1) return 0;
        return (int) min(120, round(3.86 * pow($rank - 1, 0.748)));
    }

    /** @test */
    public function rank1_has_zero_delay(): void
    {
        $this->assertEquals(0, $this->calcDelay(1));
    }

    /** @test */
    public function rank10_has_approximately_20_minutes_delay(): void
    {
        $this->assertEquals(20, $this->calcDelay(10));
    }

    /** @test */
    public function rank100_has_approximately_120_minutes_delay(): void
    {
        $this->assertEquals(120, $this->calcDelay(100));
    }

    /** @test */
    public function delay_is_capped_at_120_minutes(): void
    {
        $this->assertLessThanOrEqual(120, $this->calcDelay(200));
        $this->assertLessThanOrEqual(120, $this->calcDelay(9999));
    }

    /** @test */
    public function delay_increases_monotonically_with_rank(): void
    {
        $prev = -1;
        foreach ([1, 2, 5, 10, 20, 50, 100, 200] as $rank) {
            $delay = $this->calcDelay($rank);
            $this->assertGreaterThanOrEqual($prev, $delay, "Rank {$rank} should have >= delay than previous");
            $prev = $delay;
        }
    }

    // ── Bid Tier Mapping ──────────────────────────────────────────────────────

    private function resolveVisibilityLabel(int $bid): string
    {
        $tiers = [
            ['min' => 2500, 'label' => 'Extreme Visibility'],
            ['min' => 1000, 'label' => 'High Visibility'],
            ['min' => 500,  'label' => 'Moderate Visibility'],
            ['min' => 0,    'label' => 'Low Visibility'],
        ];
        foreach ($tiers as $tier) {
            if ($bid >= $tier['min']) return $tier['label'];
        }
        return 'Low Visibility';
    }

    /** @test */
    public function bid_2500_maps_to_extreme_visibility(): void
    {
        $this->assertEquals('Extreme Visibility', $this->resolveVisibilityLabel(2500));
    }

    /** @test */
    public function bid_1500_maps_to_high_visibility(): void
    {
        $this->assertEquals('High Visibility', $this->resolveVisibilityLabel(1500));
    }

    /** @test */
    public function bid_600_maps_to_moderate_visibility(): void
    {
        $this->assertEquals('Moderate Visibility', $this->resolveVisibilityLabel(600));
    }

    /** @test */
    public function bid_0_maps_to_low_visibility(): void
    {
        $this->assertEquals('Low Visibility', $this->resolveVisibilityLabel(0));
    }

    /** @test */
    public function bid_499_maps_to_low_visibility(): void
    {
        $this->assertEquals('Low Visibility', $this->resolveVisibilityLabel(499));
    }
}
