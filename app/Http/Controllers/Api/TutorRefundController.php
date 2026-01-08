<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TutorRefundRequest;
use App\Models\StudentRequirement;
use App\Models\EnquiryUnlock;
use App\Services\WalletService;
use App\Notifications\RefundApprovedNotification;
use App\Notifications\RefundRejectedNotification;
use Illuminate\Http\Request;

class TutorRefundController extends Controller
{
    public function __construct(private WalletService $walletService)
    {
    }

    /**
     * List refund requests for authenticated tutor
     * GET /api/tutor/refunds
     */
    public function myRefunds(Request $request)
    {
        $tutor = $request->user();
        
        $refunds = TutorRefundRequest::where('tutor_id', $tutor->id)
            ->with(['enquiry:id,student_id,student_name,city,subject_id', 'enquiry.student'])
            ->orderByDesc('requested_at')
            ->paginate(20);

        return response()->json([
            'message' => 'Refund requests retrieved',
            'data' => $refunds,
        ]);
    }

    /**
     * Request refund for spam/no-response enquiry
     * POST /api/enquiry/{id}/request-refund
     * 
     * Body:
     * {
     *   "reason": "spam|no_response|wrong_details|other",
     *   "notes": "Why I'm requesting refund..."
     * }
     */
    public function requestRefund(Request $request, $enquiryId)
    {
        $tutor = $request->user();
        
        // Validate input
        $data = $request->validate([
            'reason' => 'required|in:spam,no_response,wrong_details,other',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Get enquiry
        $enquiry = StudentRequirement::findOrFail($enquiryId);

        // Verify tutor unlocked this enquiry
        $unlock = EnquiryUnlock::where('enquiry_id', $enquiryId)
            ->where('tutor_id', $tutor->id)
            ->first();

        if (!$unlock) {
            return response()->json([
                'message' => 'You have not unlocked this enquiry',
            ], 403);
        }

        // Check if refund request already exists
        $existing = TutorRefundRequest::where('tutor_id', $tutor->id)
            ->where('enquiry_id', $enquiryId)
            ->whereIn('status', ['pending', 'approved'])
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'You already have a pending or approved refund request for this enquiry',
                'request_id' => $existing->id,
                'status' => $existing->status,
            ], 422);
        }

        // Create refund request
        $refundRequest = TutorRefundRequest::create([
            'tutor_id' => $tutor->id,
            'enquiry_id' => $enquiryId,
            'unlock_id' => $unlock->id,
            'reason' => $data['reason'],
            'notes' => $data['notes'] ?? null,
            'refund_amount' => $enquiry->unlock_price, // Refund the unlock cost
            'status' => TutorRefundRequest::STATUS_PENDING,
        ]);

        return response()->json([
            'message' => 'Refund request submitted successfully',
            'refund_request' => [
                'id' => $refundRequest->id,
                'reason' => $refundRequest->reason,
                'reason_label' => $refundRequest->getReasonLabel(),
                'amount' => $refundRequest->refund_amount,
                'status' => $refundRequest->status,
                'requested_at' => $refundRequest->requested_at,
            ],
        ], 201);
    }

    /**
     * Get refund request details
     * GET /api/tutor/refunds/{id}
     */
    public function getRefund(Request $request, $refundId)
    {
        $tutor = $request->user();
        
        $refund = TutorRefundRequest::where('tutor_id', $tutor->id)
            ->with(['enquiry:id,student_id,student_name,city,subject_id', 'enquiry.student'])
            ->findOrFail($refundId);

        return response()->json([
            'message' => 'Refund request retrieved',
            'data' => $refund,
        ]);
    }

    /**
     * Admin: List all pending refund requests
     * GET /api/admin/refunds/pending
     */
    public function pendingRefunds(Request $request)
    {
        // Admin authorization check
        if (!$request->user()->hasRole('admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $refunds = TutorRefundRequest::with([
            'tutor:id,name,email',
            'enquiry:id,student_id,student_name,city,subject_id',
            'enquiry.student:id,name,email',
        ])
            ->pending()
            ->orderByDesc('requested_at')
            ->paginate(20);

        return response()->json([
            'message' => 'Pending refund requests retrieved',
            'data' => $refunds,
            'stats' => [
                'total_pending' => TutorRefundRequest::where('status', TutorRefundRequest::STATUS_PENDING)->count(),
                'total_amount' => TutorRefundRequest::where('status', TutorRefundRequest::STATUS_PENDING)->sum('refund_amount'),
            ],
        ]);
    }

    /**
     * Admin: Approve refund request and credit tutor
     * POST /api/admin/refunds/{id}/approve
     */
    public function approveRefund(Request $request, $refundId)
    {
        // Admin authorization check
        if (!$request->user()->hasRole('admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $data = $request->validate([
            'notes' => 'nullable|string|max:500',
        ]);

        $refund = TutorRefundRequest::findOrFail($refundId);

        if (!$refund->canApprove()) {
            return response()->json([
                'message' => 'This refund request cannot be approved (status: ' . $refund->status . ')',
            ], 422);
        }

        try {
            // Credit tutor's wallet
            $transaction = $this->walletService->credit(
                $refund->tutor,
                $refund->refund_amount,
                'tutor_refund',
                'Refund for enquiry #' . $refund->enquiry_id . ' - ' . $refund->getReasonLabel(),
                [
                    'refund_request_id' => $refund->id,
                    'reason' => $refund->reason,
                    'enquiry_id' => $refund->enquiry_id,
                ]
            );

            // Update refund request
            $refund->update([
                'status' => TutorRefundRequest::STATUS_PROCESSED,
                'reviewed_at' => now(),
                'processed_at' => now(),
                'reviewed_by' => $request->user()->id,
                'admin_notes' => $data['notes'] ?? null,
            ]);

            // Notify tutor
            $refund->tutor->notify(new RefundApprovedNotification($refund, $transaction));

            return response()->json([
                'message' => 'Refund approved and credited',
                'refund' => [
                    'id' => $refund->id,
                    'amount' => $refund->refund_amount,
                    'status' => $refund->status,
                    'tutor_coins' => $refund->tutor->coins,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error processing refund: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Admin: Reject refund request
     * POST /api/admin/refunds/{id}/reject
     */
    public function rejectRefund(Request $request, $refundId)
    {
        // Admin authorization check
        if (!$request->user()->hasRole('admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $data = $request->validate([
            'notes' => 'required|string|max:500',
        ]);

        $refund = TutorRefundRequest::findOrFail($refundId);

        if (!$refund->canReject()) {
            return response()->json([
                'message' => 'This refund request cannot be rejected (status: ' . $refund->status . ')',
            ], 422);
        }

        // Update refund request
        $refund->update([
            'status' => TutorRefundRequest::STATUS_REJECTED,
            'reviewed_at' => now(),
            'reviewed_by' => $request->user()->id,
            'admin_notes' => $data['notes'],
        ]);

        // Notify tutor
        $refund->tutor->notify(new RefundRejectedNotification($refund));

        return response()->json([
            'message' => 'Refund rejected',
            'refund' => [
                'id' => $refund->id,
                'status' => $refund->status,
            ],
        ]);
    }

    /**
     * Admin: Get refund statistics
     * GET /api/admin/refunds/stats
     */
    public function refundStats(Request $request)
    {
        if (!$request->user()->hasRole('admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $totalPending = TutorRefundRequest::pending()->count();
        $totalApproved = TutorRefundRequest::approved()->count();
        $totalRejected = TutorRefundRequest::where('status', 'rejected')->count();

        return response()->json([
            'stats' => [
                'pending' => [
                    'count' => $totalPending,
                    'amount' => TutorRefundRequest::pending()->sum('refund_amount'),
                ],
                'approved' => [
                    'count' => $totalApproved,
                    'amount' => TutorRefundRequest::approved()->sum('refund_amount'),
                ],
                'rejected' => [
                    'count' => $totalRejected,
                    'amount' => TutorRefundRequest::where('status', 'rejected')->sum('refund_amount'),
                ],
                'processed' => [
                    'count' => TutorRefundRequest::where('status', 'processed')->count(),
                    'amount' => TutorRefundRequest::where('status', 'processed')->sum('refund_amount'),
                ],
            ],
        ]);
    }
}
