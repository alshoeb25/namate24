<?php

namespace App\Services;

use App\Exceptions\InsufficientBalanceException;
use App\Jobs\NotifyTutorsOfNewRequirement;
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
        // Use nationality-based pricing for post fee
        $postFee = (int)($data['post_fee'] ?? \App\Services\CoinPricingService::getCoinCost($student, 'post_requirement'));
        
        // Use nationality-based pricing for unlock price (tutor will pay when unlocking)
        // This is set now but tutors may have different pricing
        $unlockPrice = (int)($data['unlock_price'] ?? config('enquiry.unlock_fee', 199));
        $maxLeads = (int)($data['max_leads'] ?? config('enquiry.max_leads', 5));

        $studentId = $data['student_id'] ?? $student->student->id;

        return DB::transaction(function () use ($data, $student, $studentId, $subjectIds, $postFee, $unlockPrice, $maxLeads) {

            // Check if this is a free post (first 3 requirements)
            $requirementCount = StudentRequirement::where('student_id', $studentId)
                ->whereNot(function ($query) {
                    $query->where('post_fee', '<=', 0)
                        ->where('lead_status', 'cancelled');
                })
                ->count();
            $freeCount = config('coins.free_requirements_count', 3);
            $isFreePost = $requirementCount < $freeCount;
            
            // Only charge coins if NOT a free post
            if (!$isFreePost && $postFee > 0) {
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
                'post_fee' => $isFreePost ? 0 : $postFee,
                'unlock_price' => $unlockPrice,
                'max_leads' => $maxLeads,
                'current_leads' => 0,
                'lead_status' => 'open',
                'posted_at' => now(),
            ]));

            if (!empty($subjectIds)) {
                $enquiry->subjects()->sync($subjectIds);
            }

            // Reload requirement with subjects for notification
            $enquiry = $enquiry->fresh(['subjects']);

            Log::info('New enquiry posted, dispatching tutor notifications', [
                'requirement_id' => $enquiry->id,
                'subject_ids' => $subjectIds,
            ]);

            // Dispatch job to send notifications to matching tutors
            NotifyTutorsOfNewRequirement::dispatch($enquiry, $subjectIds);

            return $enquiry;
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
                ->where('tutor_id', $tutorId)
                ->lockForUpdate()
                ->first();

            if ($existing) {
                return [$lockedEnquiry->fresh(), $existing, false];
            }

            // Use nationality-based pricing for requirement unlock (49/99 based on tutor's nationality)
            // Note: Requirements unlock pricing matches post pricing tier, not tutor profile pricing
            $isIndia = $lockedTutor->country_iso === 'IN';
            $unlockPrice = (int)($isIndia 
                ? config('enquiry.pricing_by_nationality.post.indian', 49)
                : config('enquiry.pricing_by_nationality.post.non_indian', 99));


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
                'tutor_id' => $tutorId,
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
        $studentUser = $enquiry->student?->user;
        if (!$studentUser || empty($studentUser->email)) {
            return;
        }

        \App\Jobs\SendTeacherInterestedEmail::dispatch(
            $enquiry->id,
            $teacher->id,
            $studentUser->id,
            $studentUser->email
        );
    }

    /**
     * Notify student when enquiry reaches lead cap
     */
    private function notifyStudentLeadsFull(StudentRequirement $enquiry): void
    {
        $studentUser = $enquiry->student?->user;
        if ($studentUser) {
            // Send notification (queued and broadcast)
            $studentUser->notify(new \App\Notifications\EnquiryFullNotification($enquiry));
            return;
        }

        $student = $enquiry->student;
        if (!$student) return;

        // Fallback if student user is unavailable
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
