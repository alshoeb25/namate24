<?php

namespace App\Console\Commands;

use App\Models\EnquiryUnlock;
use App\Services\WalletService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Daily command: Auto-refund tutors whose unlock is 15+ days old,
 * the student never viewed it, and the student's phone is unverified.
 *
 * Also handles spam/scam enquiry bulk refunds.
 */
class ProcessUnlockAutoRefunds extends Command
{
    protected $signature   = 'enquiry:auto-refunds {--dry-run : Show what would be refunded without acting}';
    protected $description = 'Issue auto-refunds for stale unlocks (15d unread + unverified phone) and spam jobs';

    public function handle(WalletService $walletService): int
    {
        $dryRun = $this->option('dry-run');

        // ── 1. Stale unlock refunds (15 days unread + phone unverified) ──────
        $this->processStaleUnlocks($walletService, $dryRun);

        // ── 2. Spam/scam bulk refunds ─────────────────────────────────────────
        $this->processSpamRefunds($walletService, $dryRun);

        return self::SUCCESS;
    }

    private function processStaleUnlocks(WalletService $walletService, bool $dryRun): void
    {
        $cutoff = now()->subDays(15);

        // Unlocks older than 15 days, not yet auto-refunded, student never viewed
        $staleUnlocks = EnquiryUnlock::where('created_at', '<=', $cutoff)
            ->where('auto_refunded', false)
            ->whereNull('student_viewed_at')
            ->with(['tutor.user', 'enquiry.student'])
            ->get();

        $refunded = 0;
        $skipped  = 0;

        foreach ($staleUnlocks as $unlock) {
            $enquiry = $unlock->enquiry;
            $tutor   = $unlock->tutor;

            if (!$enquiry || !$tutor || !$tutor->user) {
                $skipped++;
                continue;
            }

            // Check: student phone is unverified (empty or null)
            $phoneUnverified = empty($enquiry->phone) || empty($enquiry->country_code);

            if (!$phoneUnverified) {
                // Phone is present/verified — no refund
                $skipped++;
                continue;
            }

            $refundAmount = $unlock->unlock_price;
            if ($refundAmount <= 0) {
                $skipped++;
                continue;
            }

            if ($dryRun) {
                $this->line("[DRY RUN] Would refund {$refundAmount} coins to tutor #{$tutor->id} for unlock #{$unlock->id}");
                $refunded++;
                continue;
            }

            DB::transaction(function () use ($unlock, $tutor, $refundAmount, $walletService) {
                $walletService->credit(
                    $tutor->user,
                    $refundAmount,
                    'refund',
                    "Auto-refund: enquiry #{$unlock->enquiry_id} unread for 15 days with unverified phone",
                    ['type' => 'auto_refund_stale', 'unlock_id' => $unlock->id, 'enquiry_id' => $unlock->enquiry_id]
                );

                $unlock->update([
                    'auto_refunded'    => true,
                    'auto_refunded_at' => now(),
                ]);
            });

            Log::info("AutoRefund: refunded {$refundAmount} coins to tutor #{$tutor->id} (unlock #{$unlock->id})");
            $refunded++;
        }

        $this->info("Stale unlock refunds: {$refunded} refunded, {$skipped} skipped." . ($dryRun ? ' (dry run)' : ''));
    }

    private function processSpamRefunds(WalletService $walletService, bool $dryRun): void
    {
        if (!config('enquiry.refunds.spam_reporting', true)) {
            return;
        }

        $spamPct = (int) config('enquiry.refunds.spam_refund_percentage', 100);

        // Find requirements marked as spam/scam that still have unrefunded unlocks
        $spamRequirements = \App\Models\StudentRequirement::where('lead_status', 'spam')
            ->orWhere('lead_status', 'scam')
            ->with(['unlocks' => fn($q) => $q->where('auto_refunded', false)->with('tutor.user')])
            ->get();

        $refunded = 0;

        foreach ($spamRequirements as $req) {
            foreach ($req->unlocks as $unlock) {
                if (!$unlock->tutor?->user) {
                    continue;
                }

                $refundAmount = (int) round($unlock->unlock_price * $spamPct / 100);
                if ($refundAmount <= 0) {
                    continue;
                }

                if ($dryRun) {
                    $this->line("[DRY RUN] Spam refund {$refundAmount} coins to tutor #{$unlock->tutor_id} for req #{$req->id}");
                    $refunded++;
                    continue;
                }

                DB::transaction(function () use ($unlock, $refundAmount, $walletService, $req) {
                    $walletService->credit(
                        $unlock->tutor->user,
                        $refundAmount,
                        'refund',
                        "Spam/scam refund: enquiry #{$req->id} confirmed as {$req->lead_status}",
                        ['type' => 'spam_refund', 'unlock_id' => $unlock->id, 'enquiry_id' => $req->id]
                    );

                    $unlock->update([
                        'auto_refunded'    => true,
                        'auto_refunded_at' => now(),
                    ]);
                });

                $refunded++;
            }
        }

        $this->info("Spam refunds: {$refunded} issued." . ($dryRun ? ' (dry run)' : ''));
    }
}
