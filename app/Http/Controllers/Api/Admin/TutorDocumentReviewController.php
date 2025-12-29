<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\TutorDocument;
use App\Models\AdminActionLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Redis;
use App\Notifications\DocumentReviewNotification;

class TutorDocumentReviewController extends Controller
{
    /**
     * List pending (or filter by status) tutor documents
     */
    public function index(Request $request)
    {
        $status = $request->query('status', 'pending');

        $query = TutorDocument::query();
        if ($status) {
            $query->where('verification_status', $status);
        }

        $docs = $query->with(['tutor.user'])
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 20));

        return response()->json($docs);
    }

    /**
     * Approve a document
     */
    public function approve(Request $request, TutorDocument $document)
    {
        $admin = $request->user();

        $document->update([
            'verification_status' => 'approved',
            'rejection_reason' => null,
            'verified_by' => $admin->id,
            'verified_at' => now(),
        ]);

        if ($document->tutor && $document->tutor->user) {
            $document->tutor->user->notify(
                new DocumentReviewNotification($document, 'approved')
            );
            // Update Redis tutor verification summary
            $userId = $document->tutor->user->id;
            \Illuminate\Support\Facades\Redis::hmset("user:{$userId}:verification", 'docs', 'verified', 'updated_at', now()->toISOString());
        }

        AdminActionLog::create([
            'admin_id' => $admin->id,
            'action_type' => 'document_approve',
            'subject_type' => TutorDocument::class,
            'subject_id' => $document->id,
            'metadata' => [
                'tutor_id' => $document->tutor_id,
                'document_type' => $document->document_type,
            ],
        ]);

        Redis::rpush('admin:actions', json_encode([
            'ts' => now()->toISOString(),
            'admin_id' => $admin->id,
            'type' => 'document_approve',
            'subject' => ['type' => 'TutorDocument', 'id' => $document->id],
        ]));

        return response()->json(['success' => true]);
    }

    /**
     * Reject a document
     */
    public function reject(Request $request, TutorDocument $document)
    {
        $admin = $request->user();

        $data = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $document->update([
            'verification_status' => 'rejected',
            'rejection_reason' => $data['reason'],
            'verified_by' => $admin->id,
            'verified_at' => now(),
        ]);

        if ($document->tutor && $document->tutor->user) {
            $document->tutor->user->notify(
                new DocumentReviewNotification($document, 'rejected', $data['reason'])
            );
            // Update Redis tutor verification summary
            $userId = $document->tutor->user->id;
            \Illuminate\Support\Facades\Redis::hmset("user:{$userId}:verification", 'docs', 'unverified', 'updated_at', now()->toISOString());
        }

        AdminActionLog::create([
            'admin_id' => $admin->id,
            'action_type' => 'document_reject',
            'subject_type' => TutorDocument::class,
            'subject_id' => $document->id,
            'metadata' => [
                'tutor_id' => $document->tutor_id,
                'reason' => $data['reason'],
                'document_type' => $document->document_type,
            ],
        ]);

        Redis::rpush('admin:actions', json_encode([
            'ts' => now()->toISOString(),
            'admin_id' => $admin->id,
            'type' => 'document_reject',
            'subject' => ['type' => 'TutorDocument', 'id' => $document->id],
        ]));

        return response()->json(['success' => true]);
    }
}
