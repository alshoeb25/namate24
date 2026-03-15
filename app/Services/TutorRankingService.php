<?php

namespace App\Services;

use App\Models\Tutor;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Tutor Ranking & Early Access System
 *
 * Monthly bid determines rank:
 *  2,500+ coins  → Rank  1–5   (instant visibility)
 *  1,000–2,499   → Rank  6–20  (short delay)
 *  500–999       → Rank 21–50  (moderate delay)
 *  < 500         → Rank 51+    (longest delay, max 120 min)
 *
 * Early-access formula (derived from spec):
 *  delay_minutes = round(3.1 × (rank - 1)^0.748)
 *  Rank 1  →   0 min  (instant)
 *  Rank 10 →  ~20 min
 *  Rank 100→  ~120 min
 *
 * Tie-breaking:
 *  Equal bids are ranked by (tutor_id + day_of_year) % total_tied_tutors
 *  so the order rotates daily giving each tutor fair exposure.
 */
class TutorRankingService
{
    // Calibrated so: rank 1 = 0min, rank 10 ≈ 20min, rank 100 ≈ 120min
    private const EARLY_ACCESS_EXPONENT = 0.748;
    private const EARLY_ACCESS_SCALE    = 3.86;
    private const MAX_DELAY_MINUTES     = 120;

    /**
     * Recompute ranks and early_access_minutes for ALL approved, active tutors.
     * Should run monthly (or after any bulk bid change).
     */
    public function recalculateAllRankings(): int
    {
        $dayOfYear = (int) now()->format('z'); // 0-365, rotates daily

        // Load all non-disabled approved tutors, ordered by bid descending
        $tutors = Tutor::where('moderation_status', 'approved')
            ->where('is_disabled', false)
            ->orderByDesc('monthly_bid')
            ->orderBy('id') // secondary sort for stability
            ->get(['id', 'monthly_bid', 'rotation_order']);

        $rank          = 1;
        $prevBid       = null;
        $tieGroup      = collect();
        $tieGroupStart = 1;
        $updates       = [];

        // Process tutors in bid-descending order, handling ties
        foreach ($tutors as $tutor) {
            if ($tutor->monthly_bid !== $prevBid) {
                // Flush the previous tie group
                if ($tieGroup->isNotEmpty()) {
                    $updates = array_merge($updates, $this->assignTieGroup($tieGroup, $tieGroupStart, $dayOfYear));
                    $rank = $tieGroupStart + $tieGroup->count();
                }

                $tieGroup      = collect([$tutor]);
                $tieGroupStart = $rank;
                $prevBid       = $tutor->monthly_bid;
            } else {
                $tieGroup->push($tutor);
            }
        }

        // Flush final group
        if ($tieGroup->isNotEmpty()) {
            $updates = array_merge($updates, $this->assignTieGroup($tieGroup, $tieGroupStart, $dayOfYear));
        }

        // Persist in a single transaction
        DB::transaction(function () use ($updates) {
            foreach ($updates as $upd) {
                Tutor::where('id', $upd['id'])->update([
                    'rank'                 => $upd['rank'],
                    'early_access_minutes' => $upd['early_access_minutes'],
                    'rotation_order'       => $upd['rotation_order'],
                    'rank_updated_at'      => now(),
                ]);
            }
        });

        Log::info("TutorRanking: recalculated {$tutors->count()} tutors.");
        return $tutors->count();
    }

    /**
     * Update ranking for a single tutor immediately after their bid changes.
     * A full recalculation is queued for accuracy, but this gives a fast estimate.
     */
    public function updateSingleTutor(Tutor $tutor): void
    {
        // Count tutors with a higher bid to estimate rank
        $higherBidCount = Tutor::where('moderation_status', 'approved')
            ->where('is_disabled', false)
            ->where('monthly_bid', '>', $tutor->monthly_bid)
            ->count();

        $estimatedRank          = $higherBidCount + 1;
        $earlyAccessMinutes     = $this->calculateEarlyAccessMinutes($estimatedRank);

        $tutor->update([
            'rank'                 => $estimatedRank,
            'early_access_minutes' => $earlyAccessMinutes,
            'rank_updated_at'      => now(),
        ]);
    }

    /**
     * Calculate the visibility delay in minutes for a given rank.
     *
     *  delay = min(120, round(3.1 × (rank - 1)^0.748))
     */
    public function calculateEarlyAccessMinutes(int $rank): int
    {
        if ($rank <= 1) {
            return 0;
        }

        $delay = self::EARLY_ACCESS_SCALE * pow($rank - 1, self::EARLY_ACCESS_EXPONENT);
        return (int) min(self::MAX_DELAY_MINUTES, round($delay));
    }

    /**
     * Process and deduct monthly bids from all tutors at the start of a new period.
     * Tutors without sufficient coins have their bids reduced to what they can afford.
     *
     * @return array [ 'processed' => int, 'reduced' => int, 'skipped' => int ]
     */
    public function processMonthlyBids(): array
    {
        $walletService = app(WalletService::class);
        $processed = 0;
        $reduced   = 0;
        $skipped   = 0;

        Tutor::where('moderation_status', 'approved')
            ->where('is_disabled', false)
            ->where('monthly_bid', '>', 0)
            ->with('user')
            ->chunk(100, function ($tutors) use ($walletService, &$processed, &$reduced, &$skipped) {
                foreach ($tutors as $tutor) {
                    $user = $tutor->user;
                    if (!$user) {
                        $skipped++;
                        continue;
                    }

                    $bid = (int) $tutor->monthly_bid;

                    if ($user->coins >= $bid) {
                        // Full deduction
                        try {
                            $walletService->debit(
                                $user,
                                $bid,
                                'admin_debit',
                                "Monthly ranking bid — {$bid} coins",
                                ['type' => 'monthly_ranking_bid', 'tutor_id' => $tutor->id]
                            );
                            $processed++;
                        } catch (\Exception $e) {
                            Log::warning("TutorRanking: bid deduction failed for tutor {$tutor->id}: {$e->getMessage()}");
                            $skipped++;
                        }
                    } else {
                        // Reduce bid to match available coins
                        $affordable = $user->coins;
                        if ($affordable > 0) {
                            try {
                                $walletService->debit(
                                    $user,
                                    $affordable,
                                    'admin_debit',
                                    "Monthly ranking bid (reduced) — {$affordable} coins",
                                    ['type' => 'monthly_ranking_bid_reduced', 'tutor_id' => $tutor->id, 'original_bid' => $bid]
                                );
                            } catch (\Exception $e) {
                                Log::warning("TutorRanking: reduced bid deduction failed for tutor {$tutor->id}");
                            }
                        }

                        // Update bid to what was actually affordable
                        $tutor->update(['monthly_bid' => $affordable]);
                        $reduced++;
                    }
                }
            });

        // Recalculate all rankings after deductions
        $this->recalculateAllRankings();

        Log::info("TutorRanking: monthly bids processed.", compact('processed', 'reduced', 'skipped'));
        return compact('processed', 'reduced', 'skipped');
    }

    /**
     * Assign ranks within a tie group, rotating order daily.
     */
    private function assignTieGroup(Collection $group, int $startRank, int $dayOfYear): array
    {
        $count   = $group->count();
        $updates = [];

        // Sort the tie group by (id + dayOfYear) mod count for daily rotation
        $sorted = $group->sortBy(fn($t) => ($t->id + $dayOfYear) % $count);

        $offset = 0;
        foreach ($sorted as $tutor) {
            $assignedRank           = $startRank + $offset;
            $earlyAccessMinutes     = $this->calculateEarlyAccessMinutes($assignedRank);

            $updates[] = [
                'id'                   => $tutor->id,
                'rank'                 => $assignedRank,
                'early_access_minutes' => $earlyAccessMinutes,
                'rotation_order'       => $offset,
            ];

            $offset++;
        }

        return $updates;
    }
}
