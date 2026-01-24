<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\InsufficientBalanceException;
use App\Http\Controllers\Controller;
use App\Models\EnquiryUnlock;
use App\Models\StudentRequirement;
use App\Services\EnquiryService;
use App\Services\LabelService;
use Illuminate\Http\Request;
use RuntimeException;

class EnquiryController extends Controller
{
    public function __construct(
        private EnquiryService $enquiryService,
        private LabelService $labelService
    ) {
    }

    public function config()
    {
        return response()->json([
            'post_fee' => config('enquiry.post_fee'),
            'unlock_fee' => config('enquiry.unlock_fee'),
            'max_leads' => config('enquiry.max_leads'),
            'refund_percentage' => config('enquiry.refund_percentage'),
            'show_lead_count' => config('enquiry.show_lead_count', true),
        ]);
    }

    public function index(Request $request)
    {
        $user = $request->user();

        if (!$user->hasRole('tutor')) {
            return response()->json(['message' => 'Only tutors can view enquiries'], 403);
        }

        $query = StudentRequirement::query()
            ->where('status', 'active')
            ->where(function ($q) {
                $q->where('lead_status', 'open')->orWhereNull('lead_status');
            })
            ->whereColumn('current_leads', '<', 'max_leads')
            ->with(['subject', 'subjects'])
            ->withExists([
                'unlocks as has_unlocked' => function ($q) use ($user) {
                    $tutorId = $user->tutor ? $user->tutor->id : null;
                    if ($tutorId) {
                        $q->where('tutor_id', $tutorId);
                    }
                }
            ]);

        // Filter by subject_id (single subject dropdown)
        if ($subjectId = $request->query('subject_id')) {
            $query->where(function ($q) use ($subjectId) {
                // Check if subject_id matches
                $q->where('subject_id', $subjectId)
                // OR check in the many-to-many subjects relationship
                ->orWhereHas('subjects', function ($sq) use ($subjectId) {
                    $sq->where('subjects.id', $subjectId);
                });
            });
        }
        
        // Filter by subject name (text search)
        if ($subject = $request->query('subject')) {
            $query->where(function ($q) use ($subject) {
                // Check in the many-to-many subjects relationship
                $q->whereHas('subjects', function ($sq) use ($subject) {
                    $sq->where('name', 'like', '%' . $subject . '%');
                })
                // OR check in the single subject relationship
                ->orWhereHas('subject', function ($sq) use ($subject) {
                    $sq->where('name', 'like', '%' . $subject . '%');
                });
            });
        }

        if ($city = $request->query('location')) {
            $query->where('city', 'like', '%' . $city . '%');
        }

        $enquiries = $query
            ->orderByDesc('created_at')
            ->paginate($request->integer('per_page', 20));

        $enquiries->getCollection()->transform(function (StudentRequirement $enquiry) {
            // Add labels for display and Elasticsearch
            $enquiry = $this->addLabelsToEnquiry($enquiry);
            
            // Extract and add subject_name and subject_names
            if ($enquiry->relationLoaded('subject') && $enquiry->subject) {
                $enquiry->subject_name = $enquiry->subject->name;
            }
            
            if ($enquiry->relationLoaded('subjects') && $enquiry->subjects && $enquiry->subjects->isNotEmpty()) {
                $enquiry->subject_names = $enquiry->subjects->pluck('name')->toArray();
                // If no single subject, use first from subjects
                if (!isset($enquiry->subject_name)) {
                    $enquiry->subject_name = $enquiry->subject_names[0] ?? null;
                }
            }
            
            if (!$enquiry->has_unlocked) {
                $enquiry->makeHidden(['phone', 'alternate_phone']);
            }
            
            // Add lead transparency info
            $enquiry->setAttribute('lead_info', [
                'current_leads' => $enquiry->current_leads,
                'max_leads' => $enquiry->max_leads,
                'spots_available' => $enquiry->max_leads - $enquiry->current_leads,
                'is_full' => $enquiry->current_leads >= $enquiry->max_leads,
            ]);
            
            return $enquiry;
        });

        return response()->json($enquiries);
    }

    public function show(Request $request, StudentRequirement $enquiry)
    {
        $user = $request->user();
        $enquiry->load('subjects');

        $tutorId = $user->tutor ? $user->tutor->id : null;
        $hasUnlocked = $tutorId ? EnquiryUnlock::where('enquiry_id', $enquiry->id)
            ->where('tutor_id', $tutorId)
            ->exists() : false;

        $studentId = $user->student ? $user->student->id : null;
        $isOwner = $studentId && $enquiry->student_id === $studentId;

        if (!$isOwner && !$hasUnlocked && !$user->hasRole('tutor')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Add labels for display
        $enquiry = $this->addLabelsToEnquiry($enquiry);
        
        $payload = $enquiry->toArray();
        $payload['has_unlocked'] = $hasUnlocked;

        if (!$isOwner && !$hasUnlocked) {
            unset($payload['phone'], $payload['alternate_phone']);
        }

        return response()->json(['enquiry' => $payload]);
    }

    public function unlock(Request $request, StudentRequirement $enquiry)
    {
        $user = $request->user();

        if (!$user->hasRole('tutor')) {
            return response()->json(['message' => 'Only tutors can unlock enquiries'], 403);
        }

        try {
            [$freshEnquiry, $unlock, $charged] = $this->enquiryService->unlockForTutor($enquiry, $user);
        } catch (InsufficientBalanceException $e) {
            return response()->json([
                'message' => 'Insufficient coins. Please recharge to unlock this enquiry.',
                'required' => $enquiry->unlock_price ?? config('enquiry.unlock_fee'),
                'balance' => $user->coins,
            ], 422);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        $freshEnquiry->load('subjects');
        
        // Add labels for display
        $freshEnquiry = $this->addLabelsToEnquiry($freshEnquiry);

        // Add lead transparency info
        $leadInfo = [
            'current_leads' => $freshEnquiry->current_leads,
            'max_leads' => $freshEnquiry->max_leads,
            'spots_available' => $freshEnquiry->max_leads - $freshEnquiry->current_leads,
            'is_full' => $freshEnquiry->current_leads >= $freshEnquiry->max_leads,
        ];

        return response()->json([
            'message' => $charged ? 'Contact unlocked successfully.' : 'Already unlocked.',
            'enquiry' => array_merge($freshEnquiry->toArray(), [
                'has_unlocked' => true,
                'lead_info' => $leadInfo,
            ]),
            'unlock' => $unlock,
            'charged' => $charged,
        ]);
    }

    /**
     * Add human-readable labels to enquiry data
     * Makes data useful for both display and Elasticsearch indexing
     */
    private function addLabelsToEnquiry($enquiry)
    {
        // Use LabelService to add all labels from database
        return $this->labelService->addLabels($enquiry);
    }
}
