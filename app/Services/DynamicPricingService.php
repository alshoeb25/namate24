<?php

namespace App\Services;

use App\Models\StudentRequirement;
use Illuminate\Support\Facades\Log;

/**
 * Dynamic Coin Pricing for Student Requirements
 *
 * Pricing is driven by three factors:
 *  1. Subject demand level  (high / medium / low)
 *  2. Student location region  (high-income / standard / India)
 *  3. Competition multiplier  (grows with each new applicant)
 *
 * 36-Hour Decay Rule:
 *  If fewer than 3 tutors applied within the first 36 hours, cost drops to 0.
 */
class DynamicPricingService
{
    // ── Demand level definitions ─────────────────────────────────────────────
    // [ base_min, base_max, peak_max ]  coins
    private const DEMAND_LEVELS = [
        'high'   => ['base_min' => 10, 'base_max' => 50,  'peak_max' => 150],
        'medium' => ['base_min' => 10, 'base_max' => 20,  'peak_max' => 50],
        'low'    => ['base_min' => 1,  'base_max' => 5,   'peak_max' => 10],
    ];

    // ── Region multipliers ───────────────────────────────────────────────────
    private const REGION_MULTIPLIERS = [
        'high_income' => 2.0,   // USA, UK, Canada, UAE, Australia, Singapore
        'standard'    => 1.5,   // Rest of world
        'india'       => 1.0,   // India baseline
    ];

    // High-income country codes
    private const HIGH_INCOME_COUNTRIES = ['US', 'GB', 'CA', 'AE', 'AU', 'SG', 'NZ', 'IE', 'CH', 'NO', 'SE', 'DK'];

    // ── Competition multiplier ───────────────────────────────────────────────
    // Each applicant adds 15% to the base price
    private const COMPETITION_RATE = 0.15;

    // ── Decay rule ────────────────────────────────────────────────────────────
    private const DECAY_HOURS         = 36;
    private const DECAY_MIN_APPLICANTS = 3;

    /**
     * Calculate and persist the base price for a freshly created requirement.
     * Called once at posting time.
     */
    public function initializePrice(StudentRequirement $req): void
    {
        $demandLevel = $this->classifyDemandLevel($req);
        $regionCode  = $this->detectRegionCode($req->country_code ?? $req->region_code);
        $basePrice   = $this->computeBasePrice($demandLevel, $regionCode);

        $req->update([
            'demand_level'      => $demandLevel,
            'region_code'       => $regionCode,
            'base_price'        => $basePrice,
            'dynamic_price'     => $basePrice,  // starts at base
            'last_price_update' => now(),
        ]);

        Log::info('DynamicPricing: initialized', [
            'requirement_id' => $req->id,
            'demand_level'   => $demandLevel,
            'region_code'    => $regionCode,
            'base_price'     => $basePrice,
        ]);
    }

    /**
     * Recalculate dynamic_price based on current competition.
     * Called every time a new tutor unlocks the requirement.
     */
    public function recalculatePrice(StudentRequirement $req): void
    {
        // Reload fresh data
        $req->refresh();

        // Check decay first
        if ($this->checkAndApplyDecay($req)) {
            return; // Decayed to 0 — no further calculation needed
        }

        $basePrice   = $req->base_price ?: $this->computeBasePrice(
            $req->demand_level ?? $this->classifyDemandLevel($req),
            $req->region_code  ?? $this->detectRegionCode($req->country_code)
        );

        $leads       = (int) $req->current_leads;
        $demandLevel = $req->demand_level ?? 'low';
        $peakMax     = self::DEMAND_LEVELS[$demandLevel]['peak_max'] ?? 150;

        // Competition multiplier: each lead adds COMPETITION_RATE to price
        $competitionFactor = 1 + (self::COMPETITION_RATE * $leads);
        $newPrice          = (int) min($peakMax, ceil($basePrice * $competitionFactor));

        $req->update([
            'dynamic_price'     => $newPrice,
            'last_price_update' => now(),
        ]);

        Log::info('DynamicPricing: recalculated', [
            'requirement_id'    => $req->id,
            'leads'             => $leads,
            'base_price'        => $basePrice,
            'competition_factor'=> $competitionFactor,
            'new_dynamic_price' => $newPrice,
        ]);
    }

    /**
     * Check if the 36-hour decay rule applies and set price to 0 if so.
     * Returns true if decay was applied.
     */
    public function checkAndApplyDecay(StudentRequirement $req): bool
    {
        // Already decayed
        if ($req->price_decayed) {
            return true;
        }

        // Only apply decay within the first 36 hours
        if (!$req->posted_at || $req->posted_at->diffInHours(now()) > self::DECAY_HOURS) {
            $req->update(['decay_checked_at' => now()]);
            return false;
        }

        // If fewer than DECAY_MIN_APPLICANTS have applied → price goes to 0
        if ((int) $req->current_leads < self::DECAY_MIN_APPLICANTS) {
            $req->update([
                'dynamic_price'   => 0,
                'price_decayed'   => true,
                'decay_checked_at'=> now(),
            ]);

            Log::info('DynamicPricing: 36h decay rule applied — price set to 0', [
                'requirement_id' => $req->id,
                'leads'          => $req->current_leads,
                'hours_since_post'=> $req->posted_at->diffInHours(now()),
            ]);

            return true;
        }

        $req->update(['decay_checked_at' => now()]);
        return false;
    }

    /**
     * Classify a requirement's subject into high / medium / low demand.
     */
    public function classifyDemandLevel(StudentRequirement $req): string
    {
        $subjectNames = [];

        if ($req->relationLoaded('subjects') && $req->subjects->isNotEmpty()) {
            $subjectNames = $req->subjects->pluck('name')->map(fn($n) => strtolower($n))->toArray();
        } elseif ($req->relationLoaded('subject') && $req->subject) {
            $subjectNames[] = strtolower($req->subject->name);
        } elseif (!empty($req->other_subject)) {
            $subjectNames[] = strtolower($req->other_subject);
        }

        $combined = implode(' ', $subjectNames);

        // High-demand keywords
        $highKeywords = config('enquiry.dynamic_pricing.high_demand_keywords', [
            'math', 'mathematics', 'physics', 'chemistry', 'biology', 'science',
            'coding', 'programming', 'computer', 'software', 'data', 'python',
            'javascript', 'java', 'c++', 'react', 'node', 'sql', 'machine learning',
            'ai', 'artificial intelligence', 'engineering', 'economics', 'finance',
            'accounting', 'statistics', 'calculus', 'algebra',
        ]);

        // Medium-demand keywords
        $mediumKeywords = config('enquiry.dynamic_pricing.medium_demand_keywords', [
            'english', 'writing', 'grammar', 'music', 'piano', 'guitar', 'violin',
            'singing', 'dance', 'spanish', 'french', 'german', 'language', 'history',
            'geography', 'art', 'drawing', 'yoga', 'fitness', 'spoken english',
            'public speaking', 'communication',
        ]);

        foreach ($highKeywords as $keyword) {
            if (str_contains($combined, $keyword)) {
                return 'high';
            }
        }

        foreach ($mediumKeywords as $keyword) {
            if (str_contains($combined, $keyword)) {
                return 'medium';
            }
        }

        return 'low';
    }

    /**
     * Map a country code to a region identifier used in pricing.
     */
    public function detectRegionCode(?string $countryCode): string
    {
        if (!$countryCode) {
            return 'standard';
        }

        $upper = strtoupper($countryCode);

        if ($upper === 'IN') {
            return 'india';
        }

        if (in_array($upper, self::HIGH_INCOME_COUNTRIES, true)) {
            return 'high_income';
        }

        return 'standard';
    }

    /**
     * Compute the base price from demand level and region.
     */
    public function computeBasePrice(string $demandLevel, string $regionCode): int
    {
        $level      = self::DEMAND_LEVELS[$demandLevel] ?? self::DEMAND_LEVELS['low'];
        $multiplier = self::REGION_MULTIPLIERS[$regionCode] ?? 1.0;

        // Mid-point of the base range, scaled by region multiplier
        $midBase = ($level['base_min'] + $level['base_max']) / 2;

        return (int) max(1, ceil($midBase * $multiplier));
    }

    /**
     * Get the effective unlock price for a tutor approaching a requirement.
     * Falls back to configured nationality price if dynamic pricing not yet set.
     */
    public function getEffectiveUnlockPrice(StudentRequirement $req, string $tutorCountryIso): int
    {
        // If dynamic pricing is enabled and the requirement has a dynamic price
        if (config('enquiry.fees.dynamic_pricing', false) && $req->dynamic_price > 0) {
            return $req->dynamic_price;
        }

        // Fallback to nationality-based static pricing
        $isIndia = strtoupper($tutorCountryIso) === 'IN';
        return $isIndia
            ? (int) config('enquiry.pricing_by_nationality.unlock.indian', 49)
            : (int) config('enquiry.pricing_by_nationality.unlock.non_indian', 99);
    }
}
