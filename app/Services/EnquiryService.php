<?php

namespace App\Services;

use App\Exceptions\InsufficientBalanceException;
use App\Models\EnquiryUnlock;
use App\Models\StudentRequirement;
use App\Models\User;
use Illuminate\Support\Facades\DB;
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

        $ownerId = $data['student_id'] ?? $student->students->id;

        return DB::transaction(function () use ($data, $ownerId, $subjectIds, $postFee, $unlockPrice, $maxLeads) {
            if ($postFee > 0) {
                $this->walletService->debit(
                    User::findOrFail($ownerId),
                    $postFee,
                    'enquiry_post',
                    'Posted a new enquiry',
                    ['reason' => 'enquiry_post']
                );
            }

            $enquiry = StudentRequirement::create(array_merge($data, [
                'student_id' => $ownerId,
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

            if (in_array($lockedEnquiry->lead_status, ['full', 'closed', 'cancelled'], true) ||
                $lockedEnquiry->current_leads >= $lockedEnquiry->max_leads) {
                throw new RuntimeException('Lead closed. Maximum teachers reached.');
            }

            $existing = EnquiryUnlock::where('enquiry_id', $lockedEnquiry->id)
                ->where('teacher_id', $lockedTutor->id)
                ->lockForUpdate()
                ->first();

            if ($existing) {
                return [$lockedEnquiry->fresh(), $existing, false];
            }

            $unlockPrice = (int)($lockedEnquiry->unlock_price ?? config('enquiry.unlock_fee', 0));

            if ($unlockPrice > 0) {
                $this->walletService->debit(
                    $lockedTutor,
                    $unlockPrice,
                    'enquiry_unlock',
                    'Unlocked enquiry #' . $lockedEnquiry->id,
                    [
                        'enquiry_id' => $lockedEnquiry->id,
                        'student_id' => $lockedEnquiry->student->id,
                    ]
                );
            }

            $unlock = EnquiryUnlock::create([
                'enquiry_id' => $lockedEnquiry->id,
                'teacher_id' => $lockedTutor->id,
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

        // Log notification event (can be extended to send emails/SMS)
        \Log::info('New teacher interest', [
            'enquiry_id' => $enquiry->id,
            'student_id' => $student->id,
            'teacher_id' => $teacher->id,
            'teacher_name' => $teacher->name,
            'current_leads' => $enquiry->current_leads,
            'max_leads' => $enquiry->max_leads,
        ]);

        // TODO: Send actual notification (email, SMS, push notification)
        // Example: $student->notify(new TeacherInterestedNotification($enquiry, $teacher));
    }

    /**
     * Notify student when enquiry reaches lead cap
     */
    private function notifyStudentLeadsFull(StudentRequirement $enquiry): void
    {
        $student = $enquiry->student;
        if (!$student) return;

        \Log::info('Enquiry leads full', [
            'enquiry_id' => $enquiry->id,
            'student_id' => $student->id,
            'max_leads_reached' => $enquiry->max_leads,
        ]);

        // TODO: Send actual notification
        // Example: $student->notify(new EnquiryFullNotification($enquiry));
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
