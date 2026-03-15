<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TutorRankingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Tutor monthly bid / ranking management.
 *
 * GET  /api/tutor/profile/ranking-bid  — get current bid, rank, and early-access info
 * POST /api/tutor/profile/ranking-bid  — set/update monthly bid
 */
class TutorRankingController extends Controller
{
    public function __construct(private TutorRankingService $rankingService)
    {
    }

    public function show(Request $request): JsonResponse
    {
        $tutor = $request->user()->tutor;

        if (!$tutor) {
            return response()->json(['message' => 'Tutor profile not found.'], 404);
        }

        $bidTiers   = config('enquiry.ranking.bid_tiers', []);
        $currentTier = null;
        foreach ($bidTiers as $tier) {
            if ($tutor->monthly_bid >= $tier['min']) {
                $currentTier = $tier;
                break;
            }
        }

        return response()->json([
            'monthly_bid'          => $tutor->monthly_bid,
            'rank'                 => $tutor->rank,
            'early_access_minutes' => $tutor->early_access_minutes,
            'visibility_label'     => $currentTier['label'] ?? 'Low Visibility',
            'rank_updated_at'      => $tutor->rank_updated_at,
            'coin_balance'         => $request->user()->coins,
            'bid_tiers'            => $bidTiers,
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $data = $request->validate([
            'monthly_bid' => 'required|integer|min:0|max:10000',
        ]);

        $user  = $request->user();
        $tutor = $user->tutor;

        if (!$tutor) {
            return response()->json(['message' => 'Tutor profile not found.'], 404);
        }

        $newBid = (int) $data['monthly_bid'];

        // Warn if bid exceeds available coins (not blocked — coins deducted monthly)
        $coinWarning = $newBid > $user->coins
            ? "Warning: your bid ({$newBid}) exceeds current balance ({$user->coins}). It will be reduced to your balance at billing time."
            : null;

        $tutor->update(['monthly_bid' => $newBid]);

        // Immediately estimate rank so tutor sees their new position
        $this->rankingService->updateSingleTutor($tutor);
        $tutor->refresh();

        $bidTiers    = config('enquiry.ranking.bid_tiers', []);
        $currentTier = null;
        foreach ($bidTiers as $tier) {
            if ($newBid >= $tier['min']) {
                $currentTier = $tier;
                break;
            }
        }

        return response()->json([
            'message'              => 'Monthly bid updated successfully.',
            'monthly_bid'          => $tutor->monthly_bid,
            'rank'                 => $tutor->rank,
            'early_access_minutes' => $tutor->early_access_minutes,
            'visibility_label'     => $currentTier['label'] ?? 'Low Visibility',
            'coin_warning'         => $coinWarning,
        ]);
    }
}
