<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Exceptions\InsufficientBalanceException;
use App\Models\StudentRequirement;
use App\Models\Subject;
use App\Models\User;
use App\Services\EnquiryService;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function __construct(private EnquiryService $enquiryService)
    {
    }

    /**
     * Create a new tutor request/requirement
     */
    public function requestTutor(Request $request)
    {
        $user = $request->user();

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
            
            // Section 2: Requirement Details - Level
            'level' => 'required|string',
            
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
            'student_id' => $data['student_id'] ?? $user->students->id,
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
            'level' => $data['level'],
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
        $requirements = StudentRequirement::where('student_id', $studentId)
            ->with('subjects')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'requirements' => $requirements
        ]);
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
            
            // Section 2: Requirement Details - Level
            'level' => 'required|string',
            
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
            'level' => $data['level'],
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
}
