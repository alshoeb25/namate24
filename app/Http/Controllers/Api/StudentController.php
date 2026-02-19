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
            // post_fee will be calculated by EnquiryService using CoinPricingService for nationality-based pricing
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

        $requirement->loadMissing([
            'subjects',
            'subject',
            'unlocks.tutor.user',
            'unlocks.tutor.subjects',
            'approachedTutors.user',
            'approachedTutors.subjects',
        ]);

        // Add labels for better display
        $requirement = $this->addLabelsToRequirement($requirement);

        $history = [];
        $history[] = [
            'type' => 'created',
            'label' => 'Requirement Created',
            'date' => $requirement->created_at,
        ];

        $unlocks = $requirement->unlocks?->map(function ($unlock) {
            $tutor = $unlock->tutor;
            $tutorUser = $tutor?->user;

            return [
                'type' => 'unlock',
                'label' => 'Tutor Unlocked',
                'date' => $unlock->created_at,
                'tutor' => $tutor ? [
                    'id' => $tutor->id,
                    'user_id' => $tutor->user_id,
                    'name' => $tutorUser?->name,
                    'email' => $tutorUser?->email,
                    'phone' => $tutorUser?->phone,
                    'photo' => $tutor->photo_url ?? $tutorUser?->avatar_url,
                    'subjects' => $tutor->subjects?->pluck('name')->values()->all() ?? [],
                    'rating' => $tutor->rating_avg,
                ] : null,
                'unlock_price' => $unlock->unlock_price,
            ];
        })->values()->all() ?? [];

        $history = array_merge($history, $unlocks);

        // Add all approached tutors from the dedicated table
        $approachedTutorsData = \DB::table('student_requirement_approached_tutors')
            ->where('student_requirement_id', $requirement->id)
            ->orderBy('created_at', 'asc')
            ->get();

        foreach ($approachedTutorsData as $approached) {
            $approachedTutor = \App\Models\Tutor::with('user')->find($approached->tutor_id);
            if ($approachedTutor && $approachedTutor->user) {
                $approachedUser = $approachedTutor->user;
                $history[] = [
                    'type' => 'approached',
                    'label' => 'Tutor Approached',
                    'date' => $approached->created_at,
                    'tutor' => [
                        'id' => $approachedTutor->id,
                        'user_id' => $approachedTutor->user_id,
                        'name' => $approachedUser?->name,
                        'email' => $approachedUser?->email,
                        'phone' => $approachedUser?->phone,
                        'photo' => $approachedTutor->photo_url ?? $approachedUser?->avatar_url,
                        'subjects' => $approachedTutor->subjects?->pluck('name')->values()->all() ?? [],
                        'rating' => $approachedTutor->rating_avg,
                    ],
                    'coins_spent' => $approached->coins_spent,
                ];
            }
        }

        usort($history, function ($a, $b) {
            return strtotime($a['date'] ?? 0) <=> strtotime($b['date'] ?? 0);
        });

        return response()->json([
            'requirement' => $requirement,
            'history' => $history,
        ]);
    }

    /**
     * Get single requirement details (alias endpoint)
     */
    public function getRequirementDetails(Request $request, $id)
    {
        return $this->getRequirement($request, $id);
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
        // Remove from Elasticsearch index immediately
        dispatch(new \App\Jobs\RemoveRequirementFromIndexJob($requirement->id));

        $refundAmount = 0;
        $freePostRestored = false;

        if ($requirement->current_leads === 0) {
            if ($requirement->post_fee > 0) {
                $refund = $this->enquiryService->refundIfNoUnlocks($requirement, $user);
                $refundAmount = (int)($refund['refunded_amount'] ?? 0);
                $requirement->update(['lead_status' => 'cancelled']);
            } else {
                // Free post requirement: no coin refund, restore free-post eligibility
                $requirement->update(['lead_status' => 'cancelled']);
                $freePostRestored = true;
            }
        }

        return response()->json([
            'message' => 'Requirement closed successfully.',
            'requirement' => $requirement->fresh(),
            'refund_amount' => $refundAmount,
            'free_post_restored' => $freePostRestored,
            'current_balance' => $user->fresh()->coins,
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
        // Ensure Elasticsearch removal on delete
        dispatch(new \App\Jobs\RemoveRequirementFromIndexJob($requirement->id));

        return response()->json([
            'message' => 'Requirement deleted successfully.'
        ]);
    }

    /**
     * Get list of interested teachers for an enquiry
     * Returns teachers who have unlocked the enquiry
     * Contact details are hidden until student approaches the teacher
     */
    public function getInterestedTeachers(Request $request, $id)
    {
        $user = $request->user();
        $student = $user->student;
        
        if (!$student) {
            return response()->json(['message' => 'Student profile not found.'], 404);
        }

        $requirement = StudentRequirement::where('student_id', $student->id)
            ->where('id', $id)
            ->firstOrFail();

        // Get list of all approached tutor IDs from dedicated table
        $approachedTutorIds = \DB::table('student_requirement_approached_tutors')
            ->where('student_requirement_id', $requirement->id)
            ->where('student_id', $student->id)
            ->pluck('tutor_id')
            ->toArray();
        
        \Log::info('Checking approached tutors', [
            'requirement_id' => $requirement->id,
            'student_id' => $student->id,
            'user_id' => $user->id,
            'approached_tutor_ids' => $approachedTutorIds,
        ]);

        // Get all tutors who unlocked this enquiry
        $teachers = $requirement->unlockBy()
            ->with('user')
            ->get()
            ->map(function ($tutor) use ($requirement, $approachedTutorIds) {
                $user = $tutor->user;
                
                // Show contact details if this tutor has been approached
                $hasApproached = in_array($tutor->id, $approachedTutorIds);
                
                \Log::info('Teacher mapping', [
                    'tutor_id' => $tutor->id,
                    'approached_tutor_ids' => $approachedTutorIds,
                    'hasApproached' => $hasApproached,
                    'email' => $user->email ?? null,
                    'phone' => $user->phone ?? null,
                ]);

                return [
                    'id' => $tutor->id, // tutors.id
                    'name' => $user->name ?? null,
                    'email' => $hasApproached ? ($user->email ?? null) : null,
                    'phone' => $hasApproached ? ($user->phone ?? null) : null,
                    'photo' => $tutor->photo_url ?? ($user->avatar_url ?? null),
                    'rating' => $tutor->rating_avg ?? null,
                    'hourly_rate' => $tutor->price_per_hour ?? null,
                    'bio' => $tutor->about ?? null,
                    'interested_at' => $tutor->pivot?->created_at,
                    'unlock_price' => $tutor->pivot?->unlock_price,
                    'has_approached' => $hasApproached,
                ];
            });

        return response()->json([
            'enquiry_id' => $requirement->id,
            'total_interested' => count($teachers),
            'teachers' => $teachers,
            'approach_coin_cost' => \App\Services\CoinPricingService::getCoinCost($user, 'approach_tutor'),
            'nationality' => \App\Services\CoinPricingService::getNationalityInfo($user)['nationality'],
            'pricing_details' => [
                'indian' => config('coins.pricing_by_nationality.approach_tutor.indian', 49),
                'non_indian' => config('coins.pricing_by_nationality.approach_tutor.non_indian', 99),
            ]
        ]);
    }

    /**
     * Approach a specific teacher for an enquiry
     * Deducts coins and marks enquiry as approached
     * Returns teacher contact details after successful approach
     */
    public function approachTeacher(Request $request, $id)
    {
        $user = $request->user();
        $student = $user->student;
        
        if (!$student) {
            return response()->json(['message' => 'Student profile not found.'], 404);
        }

        $data = $request->validate([
            'teacher_id' => 'required|integer|exists:tutors,id',
        ]);

        $requirement = StudentRequirement::where('student_id', $student->id)
            ->where('id', $id)
            ->firstOrFail();

        // teacher_id now references tutors.id (not users.id)
        $tutorId = $data['teacher_id'];
        
        // Check if already approached this specific tutor using dedicated table
        $alreadyApproached = \DB::table('student_requirement_approached_tutors')
            ->where('student_requirement_id', $requirement->id)
            ->where('tutor_id', $tutorId)
            ->where('student_id', $student->id)
            ->exists();
            
        if ($alreadyApproached) {
            return response()->json([
                'message' => 'You have already approached this tutor for this requirement.',
            ], 422);
        }

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

        // Get approach cost using nationality-based pricing
        $approachCost = \App\Services\CoinPricingService::getCoinCost($user, 'approach_tutor');
        
        // Check if user has enough coins
        if ($user->coins < $approachCost) {
            return response()->json([
                'message' => 'Insufficient coins to approach teacher. You need ' . $approachCost . ' coins.',
                'required_coins' => $approachCost,
                'current_balance' => $user->coins,
            ], 402);
        }
        
        // Deduct coins
        $user->decrement('coins', $approachCost);
        
        // Record transaction
        \App\Models\CoinTransaction::create([
            'user_id' => $user->id,
            'type' => 'tutor_approach',
            'amount' => -$approachCost,
            'description' => 'Approached teacher ' . ($tutor->user->name ?? 'Tutor') . ' for requirement #' . $requirement->id,
            'balance_after' => $user->fresh()->coins,
            'meta' => json_encode([
                'tutor_id' => $tutorId,
                'requirement_id' => $requirement->id,
            ]),
        ]);

        // Save approached tutor record
        \DB::table('student_requirement_approached_tutors')->insert([
            'student_requirement_id' => $requirement->id,
            'tutor_id' => $tutorId,
            'student_id' => $student->id,
            'coins_spent' => $approachCost,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Update enquiry status to approached on first approach
        if ($requirement->status !== 'approached') {
            $requirement->update(['status' => 'approached']);
        }

        $requirement->loadMissing('subjects', 'subject');

        // Notify approached teacher
        if ($tutor && $tutor->user) {
            $tutor->user->notify(new \App\Notifications\TeacherHiredNotification($requirement, $user));
        }

        // // Notify other interested teachers that lead was taken
        // $otherTutors = $requirement->unlockBy()
        //     ->where('tutors.id', '!=', $tutorId)
        //     ->with('user')
        //     ->get();

        // foreach ($otherTutors as $otherTutor) {
        //     if ($otherTutor->user) {
        //         $otherTutor->user->notify(new \App\Notifications\LeadTakenNotification($requirement, $tutor));
        //     }
        // }

        return response()->json([
            'message' => 'You have successfully approached ' . ($tutor->user->name ?? 'Tutor') . ' for ' . $approachCost . ' coins!',
            'requirement' => $requirement->fresh(),
            'coins_deducted' => $approachCost,
            'current_balance' => $user->fresh()->coins,
            'approached_teacher' => [
                'id' => $tutor->id,
                'name' => $tutor->user->name ?? null,
                'email' => $tutor->user->email ?? null,
                'phone' => $tutor->user->phone ?? null,
            ],
        ]);
    }

    /**
     * Get approached tutors with bookings and reviews
     * GET /api/student/approached-tutors
     */
    public function approachedTutors(Request $request)
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

        // Get approached tutors from student_requirement_approached_tutors table
        $approachedFromRequirements = \DB::table('student_requirement_approached_tutors')
            ->where('student_id', $studentId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($approached) {
                $requirement = StudentRequirement::with(['subject', 'subjects'])
                    ->find($approached->student_requirement_id);
                
                if (!$requirement) {
                    return null;
                }
                
                // Get the approached tutor
                $tutor = \App\Models\Tutor::with(['user', 'subjects'])->find($approached->tutor_id);
                
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

                $subjectNames = $requirement->subjects ? $requirement->subjects->pluck('name')->filter()->values()->all() : [];
                $subjectFallback = $requirement->subject?->name
                    ?? $requirement->subject_name
                    ?? $requirement->other_subject
                    ?? '';
                $subjectsRequested = !empty($subjectNames)
                    ? implode(', ', $subjectNames)
                    : $subjectFallback;

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
                    'start_at' => $approached->created_at,
                    'end_at' => null,
                    'session_price' => $requirement->budget ?? 0,
                    'session_price_display' => $requirement->budget_display ?? null,
                    'status' => 'approached',
                    'payment_status' => 'pending',
                    'created_at' => $approached->created_at,
                    'coins_spent' => $approached->coins_spent,
                    'subjects_requested' => $subjectsRequested,
                    'requirement_details' => $requirement->details,
                    'requirement_city' => $requirement->city,
                    'requirement_area' => $requirement->area,
                    'requirement_location' => $requirement->location,
                    'review' => $review ? [
                        'id' => $review->id,
                        'rating' => $review->rating,
                        'comment' => $review->comment,
                        'created_at' => $review->created_at,
                    ] : null,
                ];
            })
            ->filter(); // Remove null entries

        // Get tutors contacted via profile (not through requirements)
        $contactedTutors = \DB::table('student_tutor_contacts')
            ->where('student_id', $studentId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($contact) {
                $tutor = \App\Models\Tutor::with(['user', 'subjects'])->find($contact->tutor_id);
                
                if (!$tutor || !$tutor->user) {
                    return null;
                }

                // Check if there's a review for this tutor
                $review = \DB::table('tutor_reviews')
                    ->where('student_id', $contact->student_id)
                    ->where('tutor_id', $tutor->id)
                    ->first();

                return [
                    'id' => 'contact_' . $contact->id,
                    'source' => 'profile_contact',
                    'contact_id' => $contact->id,
                    'tutor_id' => $tutor->id,
                    'tutor' => [
                        'id' => $tutor->id,
                        'user_id' => $tutor->user_id,
                        'rating_avg' => $tutor->rating_avg,
                        'rating_count' => $tutor->rating_count,
                        'verified' => $tutor->verified,
                        'photo_url' => $tutor->photo_url,
                        'headline' => $tutor->headline,
                        'city' => $tutor->city,
                        'price_per_hour' => $tutor->price_per_hour,
                        'user' => [
                            'id' => $tutor->user->id,
                            'name' => $tutor->user->name,
                            'avatar_url' => $tutor->user->avatar_url,
                            'phone' => $tutor->user->phone,
                            'email' => $tutor->user->email,
                        ],
                        'subjects' => $tutor->subjects,
                    ],
                    'start_at' => $contact->created_at,
                    'end_at' => null,
                    'session_price' => $tutor->price_per_hour ?? 0,
                    'session_price_display' => $tutor->price_per_hour ? '₹' . $tutor->price_per_hour . '/hr' : null,
                    'status' => 'contacted',
                    'payment_status' => 'pending',
                    'created_at' => $contact->created_at,
                    'coins_spent' => $contact->coins_spent,
                    'subjects_requested' => $tutor->subjects->pluck('name')->implode(', '),
                    'review' => $review ? [
                        'id' => $review->id,
                        'rating' => $review->rating,
                        'comment' => $review->comment,
                        'created_at' => $review->created_at,
                    ] : null,
                ];
            })
            ->filter(); // Remove null entries

        // Merge all collections and sort by created_at
        $allApproachedTutors = $bookings->concat($approachedFromRequirements)->concat($contactedTutors)
            ->sortByDesc('created_at')
            ->values();

        return response()->json([
            'data' => $allApproachedTutors,
            'total' => $allApproachedTutors->count(),
        ]);
    }

    /**
     * Check if student has unlocked contact for a specific tutor
     */
    public function checkContactAccess(Request $request, $tutorId)
    {
        $user = $request->user();
        $student = $user->student;

        if (!$student) {
            return response()->json([
                'has_access' => false,
                'can_review' => false,
                'message' => 'Student profile not found'
            ], 404);
        }

        // Check if student has contacted this tutor (via student_tutor_contacts table)
        $hasContact = \DB::table('student_tutor_contacts')
            ->where('student_id', $student->id)
            ->where('tutor_id', $tutorId)
            ->exists();

        return response()->json([
            'has_access' => $hasContact,
            'can_review' => $hasContact,
        ]);
    }

    /**
     * Unlock tutor contact details (deduct 50 coins)
     */
    public function unlockTutorContact(Request $request)
    {
        $request->validate([
            'tutor_id' => 'required|integer|exists:users,id'
        ]);

        $user = $request->user();
        $student = $user->student;
        $tutorId = $request->tutor_id;

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student profile not found'
            ], 404);
        }

        // Prevent tutors from contacting themselves
        if ($user->id === $tutorId) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot unlock contact for your own profile'
            ], 422);
        }

        // Check if already unlocked
        $existingContact = \DB::table('student_tutor_contacts')
            ->where('student_id', $student->id)
            ->where('tutor_id', $tutorId)
            ->first();

        if ($existingContact) {
            return response()->json([
                'success' => false,
                'message' => 'Contact details already unlocked for this tutor'
            ], 422);
        }

        // Check if tutor exists
        $tutor = User::find($tutorId);
        if (!$tutor || $tutor->role !== 'tutor') {
            return response()->json([
                'success' => false,
                'message' => 'Tutor not found'
            ], 404);
        }

        // Check coin balance - Use nationality-based pricing (student's nationality)
        $requiredCoins = \App\Services\CoinPricingService::getCoinCost($user, 'contact_unlock');
        if ($user->coins < $requiredCoins) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient coins. You need ' . $requiredCoins . ' coins to unlock contact details.',
                'required' => $requiredCoins,
                'current_balance' => $user->coins
            ], 402);
        }

        try {
            \DB::beginTransaction();

            // Deduct coins
            $user->coins -= $requiredCoins;
            $user->save();

            // Record transaction
            \DB::table('coin_transactions')->insert([
                'user_id' => $user->id,
                'amount' => -$requiredCoins,
                'type' => 'tutor_unlock_contact',
                'description' => "Unlocked contact details for tutor: {$tutor->name}",
                'balance_after' => $user->coins,
                'meta' => json_encode([
                    'tutor_id' => $tutorId,
                    'student_id' => $user->student->id,
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Create contact record
            \DB::table('student_tutor_contacts')->insert([
                'student_id' => $student->id,
                'tutor_id' => $tutorId,
                'coins_spent' => $requiredCoins,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            \DB::commit();

            \Log::info('Student unlocked tutor contact', [
                'student_id' => $student->id,
                'tutor_id' => $tutorId,
                'coins_spent' => $requiredCoins,
                'remaining_balance' => $user->coins
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Contact details unlocked successfully',
                'remaining_balance' => $user->coins,
                'coins_spent' => $requiredCoins
            ]);

        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Error unlocking tutor contact', [
                'error' => $e->getMessage(),
                'student_id' => $student->id,
                'tutor_id' => $tutorId
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to unlock contact. Please try again.'
            ], 500);
        }
    }

    /**
     * Submit a review for a tutor
     */
    public function submitReview(Request $request)
    {
        $request->validate([
            'tutor_id' => 'required|integer|exists:users,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|max:1000'
        ]);

        $user = $request->user();
        $student = $user->student;
        $tutorId = $request->tutor_id;

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student profile not found'
            ], 404);
        }

        // Prevent tutors from reviewing themselves
        if ($user->id === $tutorId) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot review your own profile'
            ], 422);
        }

        // Check if student has contacted this tutor
        $hasContact = \DB::table('student_tutor_contacts')
            ->where('student_id', $student->id)
            ->where('tutor_id', $tutorId)
            ->exists();

        if (!$hasContact) {
            return response()->json([
                'success' => false,
                'message' => 'You can only review tutors whose contact you have unlocked.'
            ], 403);
        }

        // Check if already reviewed
        $existingReview = \DB::table('tutor_reviews')
            ->where('student_id', $student->id)
            ->where('tutor_id', $tutorId)
            ->first();

        if ($existingReview) {
            return response()->json([
                'success' => false,
                'message' => 'You have already reviewed this tutor.'
            ], 422);
        }

        try {
            // Insert review
            \DB::table('tutor_reviews')->insert([
                'tutor_id' => $tutorId,
                'student_id' => $student->id,
                'rating' => $request->rating,
                'comment' => $request->comment,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            \Log::info('Student submitted tutor review', [
                'student_id' => $student->id,
                'tutor_id' => $tutorId,
                'rating' => $request->rating
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Review submitted successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error submitting review', [
                'error' => $e->getMessage(),
                'student_id' => $student->id,
                'tutor_id' => $tutorId
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to submit review. Please try again.'
            ], 500);
        }
    }

    /**
     * Get coin balance
     */
    public function getCoinBalance(Request $request)
    {
        $user = $request->user();
        
        return response()->json([
            'balance' => $user->coins ?? 0
        ]);
    }
}
