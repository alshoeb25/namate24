<?php

namespace App\Services;

use App\Exceptions\InsufficientBalanceException;
use App\Notifications\CoinSpentNotification;
use App\Models\EnquiryUnlock;
use App\Models\StudentRequirement;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class EnquiryService
{
    public function __construct(private WalletService $walletService)
    {
    }

    public function createForStudent(array $data, User $student, array $subjectIds = []): StudentRequirement
    {
        $postFee = (int)($data['post_fee'] ?? config('enquiry.post_fee', 0));
        $unlockPrice = (int)($data['unlock_price'] ?? config('enquiry.unlock_fee', 0));
        $maxLeads = (int)($data['max_leads'] ?? config('enquiry.max_leads', 5));

        $studentId = $data['student_id'] ?? $student->student->id;

        return DB::transaction(function () use ($data, $student, $studentId, $subjectIds, $postFee, $unlockPrice, $maxLeads) {

            if ($postFee > 0) {
                try {
                    $transaction = $this->walletService->debit(
                        $student,
                        $postFee,
                        'enquiry_post',
                        'Posted a new enquiry',
                        ['reason' => 'enquiry_post']
                    );

                    $student->notify(new CoinSpentNotification($transaction));
                } catch (\Exception $e) {
                    throw $e;
                }
            }

            $enquiry = StudentRequirement::create(array_merge($data, [
                'student_id' => $studentId,
                'post_fee' => $postFee,
                'unlock_price' => $unlockPrice,
                'max_leads' => $maxLeads,
                'current_leads' => 0,
                'lead_status' => 'open',
                'posted_at' => now(),
            ]));

            if (!empty($subjectIds)) {
                $enquiry->subjects()->sync($subjectIds);
            }

            return $enquiry->fresh(['subjects']);
        });
    }

    public function unlockForTutor(StudentRequirement $enquiry, User $tutor): array
    {
        return DB::transaction(function () use ($enquiry, $tutor) {
            $lockedEnquiry = StudentRequirement::whereKey($enquiry->id)->lockForUpdate()->firstOrFail();
            $lockedTutor = User::whereKey($tutor->id)->lockForUpdate()->firstOrFail();

            // Get tutor ID from relationship
            $tutorId = $lockedTutor->tutor ? $lockedTutor->tutor->id : null;
            if (!$tutorId) {
                throw new RuntimeException('User is not registered as a tutor.');
            }

            if (in_array($lockedEnquiry->lead_status, ['full', 'closed', 'cancelled'], true) ||
                $lockedEnquiry->current_leads >= $lockedEnquiry->max_leads) {
                throw new RuntimeException('Lead closed. Maximum teachers reached.');
            }

            $existing = EnquiryUnlock::where('enquiry_id', $lockedEnquiry->id)
                ->where('teacher_id', $tutorId)
                ->lockForUpdate()
                ->first();

            if ($existing) {
                return [$lockedEnquiry->fresh(), $existing, false];
            }

            $unlockPrice = (int)($lockedEnquiry->unlock_price ?? config('enquiry.unlock_fee', 0));


            if ($unlockPrice > 0) {
                try {
                    $transaction = $this->walletService->debit(
                        $lockedTutor,
                        $unlockPrice,
                        'enquiry_unlock',
                        'Unlocked enquiry #' . $lockedEnquiry->id,
                        [
                            'enquiry_id' => $lockedEnquiry->id,
                            'student_id' => $lockedEnquiry->student_id,
                        ]
                    );

                    $lockedTutor->notify(new CoinSpentNotification($transaction));
                } catch (\Exception $e) {
                    throw $e;
                }
            }

            $unlock = EnquiryUnlock::create([
                'enquiry_id' => $lockedEnquiry->id,
                'teacher_id' => $tutorId,
                'unlock_price' => $unlockPrice,
            ]);

            $lockedEnquiry->increment('current_leads');

            // Notify student of new teacher interest
            $this->notifyStudentOfNewLead($lockedEnquiry, $lockedTutor);

            if ($lockedEnquiry->current_leads >= $lockedEnquiry->max_leads) {
                $lockedEnquiry->update(['lead_status' => 'full', 'status' => 'closed']);
                // Notify student that enquiry is now full
                $this->notifyStudentLeadsFull($lockedEnquiry);
            }

            return [$lockedEnquiry->fresh(), $unlock, true];
        });
    }

    /**
     * Notify student when a teacher unlocks their enquiry
     */
    private function notifyStudentOfNewLead(StudentRequirement $enquiry, User $teacher): void
    {
        $student = $enquiry->student;
        if (!$student) return;

        // Send notification (queued and broadcast)
        $student->notify(new \App\Notifications\TeacherInterestedNotification($enquiry, $teacher));
    }

    /**
     * Notify student when enquiry reaches lead cap
     */
    private function notifyStudentLeadsFull(StudentRequirement $enquiry): void
    {
        $student = $enquiry->student;
        if (!$student) return;

        // Send notification (queued and broadcast)
        $student->notify(new \App\Notifications\EnquiryFullNotification($enquiry));
    }

    public function refundIfNoUnlocks(StudentRequirement $enquiry, User $student): ?array
    {
        if ($enquiry->current_leads > 0 || $enquiry->post_fee <= 0) {
            return null;
        }

        $transaction = $this->walletService->credit(
            $student,
            (int)$enquiry->post_fee,
            'enquiry_refund',
            'Refund for cancelled enquiry #' . $enquiry->id,
            ['enquiry_id' => $enquiry->id]
        );

        return ['transaction' => $transaction, 'refunded_amount' => $enquiry->post_fee];
    }
}
