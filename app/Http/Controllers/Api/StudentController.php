<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Exceptions\InsufficientBalanceException;
use App\Models\StudentRequirement;
use App\Models\Subject;
use App\Models\User;
use App\Services\EnquiryService;
use App\Services\LabelService;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function __construct(
        private EnquiryService $enquiryService,
        private LabelService $labelService
    ) {
    }

    /**
     * Create a new tutor request/requirement
     */
    public function requestTutor(Request $request)
    {
        $user = $request->user();
        \Log::info('StudentController::requestTutor - User initiating tutor request', [
            'user_id' => $user->id,
            'user_coins' => $user->coins,
        ]);

        // Validate the 3-section form data
        $data = $request->validate([
            // Student ID
            'student_id' => 'nullable|integer|exists:users,id',
            
            // Section 1: Basic Information - Location
            'city' => 'required|string|max:255',
            'area' => 'required|string|max:255',
            'pincode' => 'nullable|string|max:10',
            
            // Section 1: Basic Information - Contact
            'phone' => 'required|string|max:20',
            'country_code' => 'required|string|max:10',
            'alternate_phone' => 'nullable|string|max:20',
            'alternate_country_code' => 'nullable|string|max:10',
            
            // Section 2: Requirement Details - Details
            'student_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            
            // Section 2: Requirement Details - Subjects
            'subjects' => 'required|array|min:1',
            'subjects.*' => 'string',
            'other_subject' => 'nullable|string',
            
            // Section 2: Requirement Details - Class & Level
            'class' => 'nullable|string|max:255',
            'level' => 'nullable|string|max:255',
            
            // Section 2: Requirement Details - Service Type
            'service_type' => 'required|string|in:tutoring,assignment_help',
            
            // Section 3: Logistics & Preferences - Meeting Options
            'meeting_options' => 'required|array|min:1',
            'meeting_options.*' => 'string|in:online,at_my_place,travel_to_tutor',
            'travel_distance' => 'nullable|numeric|min:0|max:50',
            
            // Section 3: Logistics & Preferences - Budget
            'budget_amount' => 'required|numeric|min:0',
            'budget_type' => 'required|string|in:fixed,per_hour,per_day,per_week,per_month,per_year',
            
            // Section 3: Logistics & Preferences - Gender Preference
            'gender_preference' => 'required|string|in:no_preference,preferably_male,preferably_female,only_male,only_female',
            
            // Section 3: Logistics & Preferences - Availability
            'availability' => 'required|string|in:part_time,full_time',
            
            // Section 3: Logistics & Preferences - Languages
            'languages' => 'required|array|min:1',
            'languages.*' => 'string',
            
            // Section 3: Logistics & Preferences - Tutor Location
            'tutor_location' => 'required|string|in:all_countries,india_only',
        ]);

        // Create combined location string
        $location = $data['city'];
        if (!empty($data['area'])) {
            $location .= ', ' . $data['area'];
        }
        if (!empty($data['pincode'])) {
            $location .= ' - ' . $data['pincode'];
        }

        $subjectIds = Subject::whereIn('name', $data['subjects'])->pluck('id')->toArray();

        $payload = [
            'student_id' => $data['student_id'] ?? $user->student->id ?? null,
            'location' => $location,
            'city' => $data['city'],
            'area' => $data['area'],
            'pincode' => $data['pincode'] ?? null,
            'phone' => $data['phone'],
            'country_code' => $data['country_code'],
            'alternate_phone' => $data['alternate_phone'] ?? null,
            'alternate_country_code' => $data['alternate_country_code'] ?? null,
            'student_name' => $data['student_name'],
            'details' => $data['description'] ?? '',
            'other_subject' => $data['other_subject'] ?? null,
            'class' => $data['class'] ?? null,
            'level' => $data['level'] ?? null,
            'service_type' => $data['service_type'],
            'meeting_options' => $data['meeting_options'],
            'travel_distance' => $data['travel_distance'] ?? null,
            'budget' => $data['budget_amount'],
            'budget_type' => $data['budget_type'],
            'gender_preference' => $data['gender_preference'],
            'availability' => $data['availability'],
            'languages' => $data['languages'],
            'tutor_location_preference' => $data['tutor_location'],
            'status' => 'active',
            'post_fee' => config('enquiry.post_fee'),
            'unlock_price' => config('enquiry.unlock_fee'),
            'max_leads' => config('enquiry.max_leads'),
            'lead_status' => 'open',
            'posted_at' => now(),
        ];

        try {
            $requirement = $this->enquiryService->createForStudent($payload, $user, $subjectIds);
        } catch (InsufficientBalanceException $e) {
            return response()->json([
                'message' => 'Insufficient coins to post this enquiry. Please recharge your wallet.',
                'required' => config('enquiry.post_fee'),
                'balance' => $user->coins,
            ], 422);
        }

        return response()->json([
            'message' => 'Tutor request submitted successfully!',
            'requirement' => $requirement
        ], 201);
    }

    /**
     * Get student's requirements
     */
    public function getRequirements(Request $request)
    {
        $user = $request->user();
        $studentId = $user->student->id;
        
        $perPage = $request->input('per_page', 10); // Default 10 items per page
        
        $requirements = StudentRequirement::where('student_id', $studentId)
            ->with('subjects')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        // Transform data to include labels for better display and Elasticsearch indexing
        $requirements->getCollection()->transform(function ($requirement) {
            return $this->addLabelsToRequirement($requirement);
        });

        return response()->json($requirements);
    }

    /**
     * Add human-readable labels to requirement data
     * Makes data useful for both display and Elasticsearch indexing
     */
    private function addLabelsToRequirement($requirement)
    {
        // Use LabelService to add all labels from database
        $requirement = $this->labelService->addLabels($requirement);

        // Add lead information
        $requirement->lead_info = [
            'current' => $requirement->current_leads ?? 0,
            'max' => $requirement->max_leads ?? 0,
            'available' => ($requirement->max_leads ?? 0) - ($requirement->current_leads ?? 0),
            'percentage' => $requirement->max_leads > 0 
                ? round(($requirement->current_leads / $requirement->max_leads) * 100) 
                : 0,
        ];

        return $requirement;
    }

    /**
     * Get single requirement
     */
    public function getRequirement(Request $request, $id)
    {
        $user = $request->user();
        $studentId = $user->student->id;

        $requirement = StudentRequirement::where('student_id', $studentId)
            ->where('id', $id)
            ->with('subjects')
            ->firstOrFail();

        // Add labels for better display
        $requirement = $this->addLabelsToRequirement($requirement);

        return response()->json([
            'requirement' => $requirement
        ]);
    }

    /**
     * Update requirement
     */
    public function updateRequirement(Request $request, $id)
    {
        $user = $request->user();
        $studentId = $user->student->id ?? $user->id;

        $requirement = StudentRequirement::where('student_id', $studentId)
            ->where('id', $id)
            ->firstOrFail();

        // Validate full form data (same as create)
        $data = $request->validate([
            // Section 1: Basic Information - Location
            'city' => 'required|string|max:255',
            'area' => 'required|string|max:255',
            'pincode' => 'nullable|string|max:10',
            
            // Section 1: Basic Information - Contact
            'phone' => 'required|string|max:20',
            'country_code' => 'required|string|max:10',
            'alternate_phone' => 'nullable|string|max:20',
            'alternate_country_code' => 'nullable|string|max:10',
            
            // Section 2: Requirement Details - Details
            'student_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            
            // Section 2: Requirement Details - Subjects
            'subjects' => 'required|array|min:1',
            'subjects.*' => 'string',
            'other_subject' => 'nullable|string',
            
            // Section 2: Requirement Details - Class & Level
            'class' => 'nullable|string|max:255',
            'level' => 'nullable|string|max:255',
            
            // Section 2: Requirement Details - Service Type
            'service_type' => 'required|string|in:tutoring,assignment_help',
            
            // Section 3: Logistics & Preferences - Meeting Options
            'meeting_options' => 'required|array|min:1',
            'meeting_options.*' => 'string|in:online,at_my_place,travel_to_tutor',
            'travel_distance' => 'nullable|numeric|min:0|max:50',
            
            // Section 3: Logistics & Preferences - Budget
            'budget_amount' => 'required|numeric|min:0',
            'budget_type' => 'required|string|in:fixed,per_hour,per_day,per_week,per_month,per_year',
            
            // Section 3: Logistics & Preferences - Gender Preference
            'gender_preference' => 'required|string|in:no_preference,preferably_male,preferably_female,only_male,only_female',
            
            // Section 3: Logistics & Preferences - Availability
            'availability' => 'required|string|in:part_time,full_time',
            
            // Section 3: Logistics & Preferences - Languages
            'languages' => 'required|array|min:1',
            'languages.*' => 'string',
            
            // Section 3: Logistics & Preferences - Tutor Location
            'tutor_location' => 'required|string|in:all_countries,india_only',
            
            // Status
            'status' => 'sometimes|string|in:active,paused,closed',
        ]);

        // Create combined location string
        $location = $data['city'];
        if (!empty($data['area'])) {
            $location .= ', ' . $data['area'];
        }
        if (!empty($data['pincode'])) {
            $location .= ' - ' . $data['pincode'];
        }

        // Update the requirement
        $requirement->update([
            'location' => $location,
            'city' => $data['city'],
            'area' => $data['area'],
            'pincode' => $data['pincode'] ?? null,
            'phone' => $data['phone'],
                'country_code' => $data['country_code'],
            'alternate_phone' => $data['alternate_phone'] ?? null,
                'alternate_country_code' => $data['alternate_country_code'] ?? null,
            'student_name' => $data['student_name'],
            'details' => $data['description'] ?? '',
            'other_subject' => $data['other_subject'] ?? null,
            'class' => $data['class'] ?? null,
            'level' => $data['level'] ?? null,
            'service_type' => $data['service_type'],
            'meeting_options' => $data['meeting_options'],
            'travel_distance' => $data['travel_distance'] ?? null,
            'budget' => $data['budget_amount'],
            'budget_type' => $data['budget_type'],
            'gender_preference' => $data['gender_preference'],
            'availability' => $data['availability'],
            'languages' => $data['languages'],
            'tutor_location_preference' => $data['tutor_location'],
            'status' => $data['status'] ?? 'active',
        ]);

        // Sync subjects via pivot table
        if (!empty($data['subjects'])) {
            $subjectIds = \App\Models\Subject::whereIn('name', $data['subjects'])->pluck('id')->toArray();
            $requirement->subjects()->sync($subjectIds);
        }

        // Load relationships for response
        $requirement->load('subjects');

        return response()->json([
            'message' => 'Requirement updated successfully.',
            'requirement' => $requirement->fresh()
        ]);
    }

    /**
     * Close requirement (set status to closed)
     */
    public function closeRequirement(Request $request, $id)
    {
        $user = $request->user();
        $studentId = $user->student->id ?? $user->id;

        $requirement = StudentRequirement::where('student_id', $studentId)
            ->where('id', $id)
            ->firstOrFail();

        $requirement->update(['status' => 'closed']);

        if ($requirement->current_leads === 0 && $requirement->post_fee > 0) {
            $this->enquiryService->refundIfNoUnlocks($requirement, $user);
            $requirement->update(['lead_status' => 'cancelled']);
        }

        return response()->json([
            'message' => 'Requirement closed successfully.',
            'requirement' => $requirement->fresh()
        ]);
    }

    /**
     * Delete requirement
     */
    public function deleteRequirement(Request $request, $id)
    {
        $user = $request->user();
        $studentId = $user->student->id ?? $user->id;

        $requirement = StudentRequirement::where('student_id', $studentId)
            ->where('id', $id)
            ->firstOrFail();

        if ($requirement->current_leads === 0 && $requirement->post_fee > 0) {
            $this->enquiryService->refundIfNoUnlocks($requirement, $user);
        }

        $requirement->delete();

        return response()->json([
            'message' => 'Requirement deleted successfully.'
        ]);
    }

    /**
     * Get list of interested teachers for an enquiry
     * Returns teachers who have unlocked the enquiry
     */
    public function getInterestedTeachers(Request $request, $id)
    {
        $user = $request->user();
        $studentId = $user->student->id ?? $user->id;

        $requirement = StudentRequirement::where('student_id', $studentId)
            ->where('id', $id)
            ->firstOrFail();

        // Get all tutors who unlocked this enquiry
        $teachers = $requirement->unlockBy()
            ->with('user')
            ->get()
            ->map(function ($tutor) {
                $user = $tutor->user;

                return [
                    'id' => $tutor->id, // tutors.id
                    'name' => $user->name ?? null,
                    'email' => $user->email ?? null,
                    'phone' => $user->phone ?? null,
                    'photo' => $tutor->photo_url ?? ($user->avatar_url ?? null),
                    'rating' => $tutor->rating_avg ?? null,
                    'hourly_rate' => $tutor->price_per_hour ?? null,
                    'bio' => $tutor->about ?? null,
                    'interested_at' => $tutor->pivot?->created_at,
                    'unlock_price' => $tutor->pivot?->unlock_price,
                ];
            });

        return response()->json([
            'enquiry_id' => $requirement->id,
            'total_interested' => count($teachers),
            'teachers' => $teachers,
        ]);
    }

    /**
     * Hire a specific teacher for an enquiry
     * Marks enquiry as hired and notifies all teachers
     */
    public function hireTeacher(Request $request, $id)
    {
        $user = $request->user();
        $studentId = $user->student->id ?? $user->id;

        $data = $request->validate([
            'teacher_id' => 'required|integer|exists:tutors,id',
        ]);

        $requirement = StudentRequirement::where('student_id', $studentId)
            ->where('id', $id)
            ->firstOrFail();

        // teacher_id now references tutors.id (not users.id)
        $tutorId = $data['teacher_id'];
        $tutor = \App\Models\Tutor::with('user')->find($tutorId);
        
        if (!$tutor || !$tutor->user) {
            return response()->json([
                'message' => 'Tutor not found or account not active.',
            ], 404);
        }
        
        // Verify teacher has unlocked this enquiry
        $hasUnlocked = $requirement->unlockBy()
            ->where('tutors.id', $tutorId)
            ->exists();

        if (!$hasUnlocked) {
            return response()->json([
                'message' => 'This tutor has not expressed interest in your enquiry.',
            ], 422);
        }

        // Update enquiry status to hired
        $requirement->update([
            'status' => 'hired',
            'lead_status' => 'closed',
            // Store hired tutor as tutors.id
            'hired_teacher_id' => $tutorId,
            'hired_at' => now(),
        ]);

        // Notify hired teacher
        if ($tutor && $tutor->user) {
            $tutor->user->notify(new \App\Notifications\TeacherHiredNotification($requirement, $user));
        }

        // Notify other interested teachers that lead was taken
        $otherTutors = $requirement->unlockBy()
            ->where('tutors.id', '!=', $tutorId)
            ->with('user')
            ->get();

        foreach ($otherTutors as $otherTutor) {
            if ($otherTutor->user) {
                $otherTutor->user->notify(new \App\Notifications\LeadTakenNotification($requirement, $tutor));
            }
        }

        return response()->json([
            'message' => 'You have successfully hired ' . ($tutor->user->name ?? 'Tutor') . '!',
            'requirement' => $requirement->fresh(),
            'hired_teacher' => [
                'id' => $tutor->id,
                'name' => $tutor->user->name ?? null,
                'email' => $tutor->user->email ?? null,
                'phone' => $tutor->user->phone ?? null,
            ],
        ]);
    }

    /**
     * Get hired tutors with bookings and reviews
     * GET /api/student/hired-tutors
     */
    public function hiredTutors(Request $request)
    {
        $user = $request->user();
        $studentId = $user->student->id ?? $user->id;

        // Get hired tutors from bookings
        $bookings = \App\Models\Booking::with(['tutor', 'tutor.user', 'tutor.subjects'])
            ->where('student_id', $studentId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($booking) {
                $review = \App\Models\Review::where('booking_id', $booking->id)->first();
                return [
                    'id' => $booking->id,
                    'source' => 'booking',
                    'tutor_id' => $booking->tutor_id,
                    'tutor' => $booking->tutor,
                    'start_at' => $booking->start_at,
                    'end_at' => $booking->end_at,
                    'session_price' => $booking->session_price,
                    'status' => $booking->status,
                    'payment_status' => $booking->payment_status,
                    'created_at' => $booking->created_at,
                    'review' => $review ? [
                        'id' => $review->id,
                        'rating' => $review->rating,
                        'comment' => $review->comment,
                        'created_at' => $review->created_at,
                    ] : null,
                ];
            });

        // Get hired tutors from requirements
        $hiredFromRequirements = StudentRequirement::where('student_id', $studentId)
            ->where('status', 'hired')
            ->whereNotNull('hired_teacher_id')
            ->with(['subject', 'subjects'])
            ->orderBy('hired_at', 'desc')
            ->get()
            ->map(function ($requirement) {
                // Get the hired tutor (hired_teacher_id is tutor_id from tutors table)
                $tutor = \App\Models\Tutor::with(['user', 'subjects'])->find($requirement->hired_teacher_id);
                
                if (!$tutor || !$tutor->user) {
                    return null;
                }

                // Check if there's a review for this requirement/teacher combination
                $review = \App\Models\Review::where('student_id', $requirement->student_id)
                    ->where('tutor_id', $tutor->id)
                    ->where(function($q) use ($requirement) {
                        $q->whereNull('booking_id')
                          ->orWhere('related_requirement_id', $requirement->id);
                    })
                    ->first();

                return [
                    'id' => 'req_' . $requirement->id,
                    'source' => 'requirement',
                    'requirement_id' => $requirement->id,
                    'tutor_id' => $tutor->id,
                    'tutor' => [
                        'id' => $tutor->id,
                        'user_id' => $tutor->user_id,
                        'rating_avg' => $tutor->rating_avg,
                        'rating_count' => $tutor->rating_count,
                        'verified' => $tutor->verified,
                        'user' => [
                            'id' => $tutor->user->id,
                            'name' => $tutor->user->name,
                            'avatar_url' => $tutor->user->avatar_url,
                        ],
                        'subjects' => $tutor->subjects,
                    ],
                    'start_at' => $requirement->hired_at,
                    'end_at' => null,
                    'session_price' => $requirement->budget ?? 0,
                    'status' => 'confirmed',
                    'payment_status' => 'pending',
                    'created_at' => $requirement->hired_at,
                    'subjects_requested' => $requirement->subjects ? $requirement->subjects->pluck('name')->join(', ') : '',
                    'review' => $review ? [
                        'id' => $review->id,
                        'rating' => $review->rating,
                        'comment' => $review->comment,
                        'created_at' => $review->created_at,
                    ] : null,
                ];
            })
            ->filter(); // Remove null entries

        // Merge both collections and sort by created_at
        $allHiredTutors = $bookings->concat($hiredFromRequirements)
            ->sortByDesc('created_at')
            ->values();

        return response()->json([
            'data' => $allHiredTutors,
            'total' => $allHiredTutors->count(),
        ]);
    }
}
