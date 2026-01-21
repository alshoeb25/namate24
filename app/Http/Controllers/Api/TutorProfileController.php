<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Institute;
use App\Models\Level;
use App\Models\Subject;
use App\Models\Tutor;
use App\Models\User;
use App\Services\WhatsAppService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TutorProfileController extends Controller
{
    /**
     * Get authenticated tutor profile instance
     */
    protected function getTutor(): Tutor
    {
        $user = Auth::user();
        
        // Create tutor profile if it doesn't exist
        if (!$user->tutor) {
            $user->tutor()->create([
                'hourly_rate' => 0,
                'years_of_experience' => 0,
                'is_available' => true,
            ]);
            $user->load('tutor');
        }
        
        return $user->tutor;
    }

    /**
     * Get Personal Details
     */
    public function getPersonalDetails(Request $request): JsonResponse
    {
        $tutor = $this->getTutor();

        return response()->json([
            'tutor' => $tutor->only([
                'id', 'user_id', 'headline', 'current_role', 'speciality', 'strength', 'youtube_url',
                'phone_verified', 'phone_otp', 'phone_otp_expires_at','gender', 'languages'
            ]),
            'user' => Auth::user()->only(['id', 'name', 'email', 'phone']),
        ]);
    }

    /**
     * Update Personal Details
     */
    public function updatePersonalDetails(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'headline' => 'nullable|string|max:255',
            'current_role' => 'nullable|string|max:100',
            'speciality' => 'nullable|string|max:100',
            'gender' => 'nullable|string|in:Male,Female,Other,Prefer not to say',
            'strength' => 'nullable|string',
            'youtube_url' => 'nullable|url',
            'languages' => 'nullable|array',
            'languages.*' => 'string|max:100',
        ]);

        $tutor = $this->getTutor();
        $tutor->update($validated);

        return response()->json(['message' => 'Personal details updated', 'tutor' => $tutor]);
    }

    /**
     * Send Phone OTP via WhatsApp
     */
    public function sendPhoneOtp(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'phone' => 'required|string|regex:/^[0-9+\-\s()]+$/',
            'country_code' => 'nullable|string|regex:/^\+[0-9]{1,3}$/',
            'method' => 'nullable|in:whatsapp,sms', // Default: whatsapp
        ]);

        $method = $validated['method'] ?? 'whatsapp';
        $phone = WhatsAppService::formatPhone($validated['phone']);
        $countryCode = $validated['country_code'] ?? '+91';

        // Generate OTP (6 digits)
        $otp = random_int(100000, 999999);
        $expiresAt = now()->addMinutes(10);

        Auth::user()->update([
            'phone' => $phone,
            'country_code' => $countryCode,
            'phone_otp' => $otp,
            'phone_otp_expires_at' => $expiresAt,
        ]);

        // Send OTP via WhatsApp or SMS
        if ($method === 'whatsapp') {
            $whatsappService = new WhatsAppService();
            $result = $whatsappService->sendOTP($phone, (string)$otp);
            
            if (!$result['success']) {
                return response()->json([
                    'message' => 'Failed to send WhatsApp OTP. Try SMS method.',
                    'error' => $result['error'] ?? 'Unknown error',
                ], 422);
            }
            
            return response()->json([
                'message' => 'OTP sent to WhatsApp',
                'phone' => $phone,
                'country_code' => $countryCode,
                'method' => 'whatsapp',
            ]);
        }

        // Fallback to SMS (implement your SMS provider)
        // SmsService::send($phone, "Your Namate24 OTP: $otp");

        return response()->json([
            'message' => 'OTP sent via SMS',
            'phone' => $phone,
            'country_code' => $countryCode,
            'method' => 'sms',
        ]);
    }

    /**
     * Save Phone Number (without OTP)
     */
    public function savePhone(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'phone' => 'required|string|regex:/^[0-9+\-\s()]+$/',
            'country_code' => 'nullable|string|regex:/^\+[0-9]{1,3}$/',
        ]);

        $phone = WhatsAppService::formatPhone($validated['phone']);
        $countryCode = $validated['country_code'] ?? '+91';

        Auth::user()->update([
            'phone' => $phone,
            'country_code' => $countryCode,
            'phone_verified' => true,
            'phone_otp' => null,
            'phone_otp_expires_at' => null,
        ]);

        return response()->json([
            'message' => 'Phone number saved successfully',
            'phone' => $phone,
            'country_code' => $countryCode,
        ]);
    }

    /**
     * Verify Phone OTP
     */
    public function verifyPhoneOtp(Request $request): JsonResponse
    {
        $validated = $request->validate(['otp' => 'required|string']);
        $user = Auth::user();

        if (!$user->phone_otp || $user->phone_otp !== $validated['otp']) {
            return response()->json(['message' => 'Invalid OTP'], 422);
        }

        if (now()->isAfter($user->phone_otp_expires_at)) {
            return response()->json(['message' => 'OTP expired'], 422);
        }

        $user->update([
            'phone_verified' => true,
            'phone_otp' => null,
            'phone_otp_expires_at' => null,
        ]);

        return response()->json(['message' => 'Phone verified']);
    }

    /**
     * Get Photo
     */
    public function getPhoto(Request $request): JsonResponse
    {
        $tutor = $this->getTutor();
        return response()->json(['photo' => $tutor->photo_url]);
    }

    /**
     * Update Photo
     */
    public function updatePhoto(Request $request): JsonResponse
    {
        $validated = $request->validate(['photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048']);

        $tutor = $this->getTutor();
        if ($request->hasFile('photo')) {
            // 🔥 delete old photo (optional but recommended)
            if ($tutor->photo && Storage::disk('public')->exists($tutor->photo)) {
                Storage::disk('public')->delete($tutor->photo);
            }

            // ✅ store new photo
            $path = $request->file('photo')->store('tutors', 'public');

            $tutor->photo = $path;
            
        }
        $tutor->save();
    
        return response()->json(['message' => 'Photo updated', 'photo_url' => $tutor->photo_url]);
    }

    /**
     * Get Video
     */
    public function getVideo(Request $request): JsonResponse
    {
        $tutor = $this->getTutor();
        return response()->json(['video' => $tutor->video_url]);
    }

    /**
     * Update Video
     */
    public function updateVideo(Request $request): JsonResponse
    {
        $validated = $request->validate(['video' => 'required|mimes:mp4,avi,mov,wmv|max:50000']);

        $tutor = $this->getTutor();
        if ($request->hasFile('video')) {
            $path = $request->file('video')->store('tutors/videos', 'public');
            $tutor->update(['video_url' => $path]);
        }

        return response()->json(['message' => 'Video updated', 'video_url' => $tutor->video_url]);
    }

    /**
     * Get All Available Subjects
     */
    public function getAllSubjects(Request $request): JsonResponse
    {
        $subjects = Subject::orderBy('name')->get();
        return response()->json(['subjects' => $subjects]);
    }

    /**
     * Get All Available Levels
     */
    public function getAllLevels(Request $request): JsonResponse
    {
        $levels = Level::getGroupedLevels();
        return response()->json(['levels' => $levels]);
    }

    /**
     * Get Tutor's Subjects with Levels
     */
    public function getSubjects(Request $request): JsonResponse
    {
        $tutor = $this->getTutor();
        $subjects = $tutor->subjects->map(function ($subject) {
            return [
                'id' => $subject->id,
                'name' => $subject->name,
                'from_level_id' => $subject->pivot->from_level_id,
                'to_level_id' => $subject->pivot->to_level_id,
            ];
        });
        return response()->json(['subjects' => $subjects]);
    }

    /**
     * Remove Subject
     */
    public function removeSubject(Request $request, $subjectId): JsonResponse
    {
        $tutor = $this->getTutor();
        $tutor->subjects()->detach($subjectId);
        
        $tutor->load('subjects');
        $subjects = $tutor->subjects->map(function ($subject) {
            return [
                'id' => $subject->id,
                'name' => $subject->name,
                'from_level_id' => $subject->pivot->from_level_id,
                'to_level_id' => $subject->pivot->to_level_id,
            ];
        });

        return response()->json([
            'message' => 'Subject removed',
            'subjects' => $subjects,
        ]);
    }

    /**
     * Update existing subject levels for the tutor
     */
    public function updateSubject(Request $request, $subjectId): JsonResponse
    {
        $validated = $request->validate([
            'from_level_id' => 'required|integer|exists:levels,id',
            'to_level_id' => 'required|integer|exists:levels,id',
        ]);

        $tutor = $this->getTutor();

        // Ensure subject is already attached to tutor
        if (!$tutor->subjects->contains($subjectId)) {
            return response()->json(['message' => 'Subject not found for tutor'], 404);
        }

        // Validate level relationship: same group and from < to
        $from = Level::find($validated['from_level_id']);
        $to = Level::find($validated['to_level_id']);
        if (!$from || !$to) {
            return response()->json(['message' => 'Invalid levels provided'], 422);
        }
        if ($from->group_name !== $to->group_name) {
            return response()->json(['message' => 'Levels must be from the same group'], 422);
        }
        if ($from->value >= $to->value) {
            return response()->json(['message' => 'Lowest level must be less than highest level'], 422);
        }

        $tutor->subjects()->updateExistingPivot($subjectId, [
            'from_level_id' => $validated['from_level_id'],
            'to_level_id' => $validated['to_level_id'],
        ]);

        $tutor->load('subjects');
        $subjects = $tutor->subjects->map(function ($subject) {
            return [
                'id' => $subject->id,
                'name' => $subject->name,
                'from_level_id' => $subject->pivot->from_level_id,
                'to_level_id' => $subject->pivot->to_level_id,
            ];
        });

        return response()->json([
            'message' => 'Subject updated',
            'subjects' => $subjects,
        ]);
    }

    /**
     * Add Subject with Levels
     */
    public function addSubject(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'subject_id' => 'required|integer|exists:subjects,id',
            'from_level_id' => 'required|integer|exists:levels,id',
            'to_level_id' => 'required|integer|exists:levels,id',
        ]);

        $tutor = $this->getTutor();
        
        // Check if subject already exists for this tutor
        if ($tutor->subjects->contains($validated['subject_id'])) {
            return response()->json(['message' => 'Subject already added'], 422);
        }

        $tutor->subjects()->attach($validated['subject_id'], [
            'from_level_id' => $validated['from_level_id'],
            'to_level_id' => $validated['to_level_id'],
        ]);

        $tutor->load('subjects');
        $subjects = $tutor->subjects->map(function ($subject) {
            return [
                'id' => $subject->id,
                'name' => $subject->name,
                'from_level_id' => $subject->pivot->from_level_id,
                'to_level_id' => $subject->pivot->to_level_id,
            ];
        });

        return response()->json([
            'message' => 'Subject added',
            'subjects' => $subjects,
        ]);
    }

    /**
     * Create New Subject
     */
    public function createSubject(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:subjects,name',
        ]);

        $subject = Subject::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
        ]);

        return response()->json([
            'message' => 'Subject created',
            'subject' => $subject,
        ]);
    }

    /**
     * Get Address
     */
    public function getAddress(Request $request): JsonResponse
    {
        $tutor = $this->getTutor();
        return response()->json($tutor->only(['address', 'city', 'state', 'zip_code', 'country']));
    }

    /**
     * Update Address
     */
    public function updateAddress(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'address' => 'nullable|string',
            'city' => 'nullable|string',
            'state' => 'nullable|string',
            'zip_code' => 'nullable|string',
            'country' => 'nullable|string',
        ]);

        $tutor = $this->getTutor();
        $tutor->update($validated);

        return response()->json(['message' => 'Address updated', 'tutor' => $tutor]);
    }

    /**
     * Search Institutes (Autocomplete)
     */
    public function searchInstitutes(Request $request): JsonResponse
    {
        $query = $request->get('q', '');
        
        $institutes = Institute::where('name', 'LIKE', "%{$query}%")
            ->orWhere('city', 'LIKE', "%{$query}%")
            ->orderBy('name', 'asc')
            ->limit(50)
            ->get(['id', 'name', 'city']);
        
        return response()->json(['institutes' => $institutes]);
    }

    /**
     * Get Degree Types
     */
    public function getDegreeTypes(Request $request): JsonResponse
    {
        $degreeTypes = [
            ['id' => 1, 'name' => 'Secondary'],
            ['id' => 2, 'name' => 'Higher Secondary'],
            ['id' => 3, 'name' => 'Diploma'],
            ['id' => 4, 'name' => 'Graduation'],
            ['id' => 5, 'name' => 'Advanced Diploma'],
            ['id' => 6, 'name' => 'Post Graduation'],
            ['id' => 7, 'name' => 'Doctorate/PhD'],
            ['id' => 8, 'name' => 'Certification'],
            ['id' => 0, 'name' => 'Other'],
        ];
        
        return response()->json(['degree_types' => $degreeTypes]);
    }

    /**
     * Get Education
     */
    public function getEducation(Request $request): JsonResponse
    {
        $tutor = $this->getTutor();
        return response()->json(['educations' => $tutor->educations]);
    }

    /**
     * Store Education
     */
    public function storeEducation(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'degree_type_id' => 'required|integer',
            'degree_type' => 'required|string',
            'degree_name' => 'nullable|string',
            'institute_id' => 'nullable|integer|exists:institutes,id',
            'institution' => 'required|string',
            'city' => 'nullable|string',
            'study_mode' => 'nullable|string|in:Full Time,Part Time,Distance/Correspondence',
            'speciality' => 'nullable|string',
            'score' => 'nullable|string',
            'start_month' => 'required|integer|min:1|max:12',
            'start_year' => 'required|integer',
            'end_month' => 'nullable|integer|min:1|max:12',
            'end_year' => 'nullable|integer',
            'is_ongoing' => 'nullable|boolean',
        ]);

        // Get institute details if institute_id is provided
        if (isset($validated['institute_id']) && $validated['institute_id']) {
            $institute = Institute::find($validated['institute_id']);
            if ($institute) {
                $validated['institution'] = $institute->name;
                $validated['city'] = $institute->city;
            }
        }

        $tutor = $this->getTutor();
        $educations = $tutor->educations ?? [];
        $educations[] = $validated;
        $tutor->update(['educations' => $educations]);

        return response()->json(['message' => 'Education added', 'educations' => $tutor->educations]);
    }

    /**
     * Update Education_id' => 'required|integer',
            'degree_type' => 'required|string',
            'degree_name' => 'nullable|string',
            'institute_id' => 'nullable|integer|exists:institutes,id',
            'institution' => 'required|string',
            'city' => 'nullable|string',
            'study_mode' => 'nullable|string|in:Full Time,Part Time,Distance/Correspondence',
            'speciality' => 'nullable|string',
            'score' => 'nullable|string',
            'start_month' => 'required|integer|min:1|max:12',
            'start_year' => 'required|integer',
            'end_month' => 'nullable|integer|min:1|max:12',
            'end_year' => 'nullable|integer',
            'is_ongoing' => 'nullable|boolean',
        ]);

        // Get institute details if institute_id is provided
        if (isset($validated['institute_id']) && $validated['institute_id']) {
            $institute = Institute::find($validated['institute_id']);
            if ($institute) {
                $validated['institution'] = $institute->name;
                $validated['city'] = $institute->city;
            }

        // Get institute name if institute_id is provided
        if (isset($validated['institute_id'])) {
            $institute = Institute::find($validated['institute_id']);
            $validated['institution'] = $institute ? $institute->name : '';
        }

        $tutor = $this->getTutor();
        $educations = $tutor->educations ?? [];
        if (isset($educations[$index])) {
            $educations[$index] = $validated;
            $tutor->update(['educations' => $educations]);
        }

        return response()->json(['message' => 'Education updated', 'educations' => $tutor->educations]);
    }

    /**
     * Delete Education
     */
    public function deleteEducation(Request $request, $index): JsonResponse
    {
        $tutor = $this->getTutor();
        $educations = $tutor->educations ?? [];
        if (isset($educations[$index])) {
            array_splice($educations, $index, 1);
            $tutor->update(['educations' => $educations]);
        }

        return response()->json(['message' => 'Education deleted', 'educations' => $tutor->educations]);
    }

    /**
     * Get Experience
     */
    public function getExperience(Request $request): JsonResponse
    {
        $tutor = $this->getTutor();
        return response()->json(['experiences' => $tutor->experiences]);
    }

    /**
     * Store Experience
     */
    public function storeExperience(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'organization' => 'required|string',
            'designation' => 'required|string',
            // association used as employment type (Full Time/Part Time)
            'association' => 'required|string|in:Full Time,Part Time',
            'city' => 'nullable|string',
            'roles' => 'nullable|string',
            'start_month' => 'required|integer|min:1|max:12',
            'start_year' => 'required|integer',
            'end_month' => 'nullable|integer|min:1|max:12',
            'end_year' => 'nullable|integer',
            'is_current' => 'nullable|boolean',
        ]);

        $tutor = $this->getTutor();
        $experiences = $tutor->experiences ?? [];
        $experiences[] = $validated;
        $tutor->update(['experiences' => $experiences]);

        return response()->json(['message' => 'Experience added', 'experiences' => $tutor->experiences]);
    }

    /**
     * Update Experience
     */
    public function updateExperience(Request $request, $index): JsonResponse
    {
        $validated = $request->validate([
            'organization' => 'required|string',
            'designation' => 'required|string',
            'association' => 'required|string|in:Full Time,Part Time',
            'city' => 'nullable|string',
            'roles' => 'nullable|string',
            'start_month' => 'required|integer|min:1|max:12',
            'start_year' => 'required|integer',
            'end_month' => 'nullable|integer|min:1|max:12',
            'end_year' => 'nullable|integer',
            'is_current' => 'nullable|boolean',
        ]);

        $tutor = $this->getTutor();
        $experiences = $tutor->experiences ?? [];
        if (isset($experiences[$index])) {
            $experiences[$index] = $validated;
            $tutor->update(['experiences' => $experiences]);
        }

        return response()->json(['message' => 'Experience updated', 'experiences' => $tutor->experiences]);
    }

    /**
     * Delete Experience
     */
    public function deleteExperience(Request $request, $index): JsonResponse
    {
        $tutor = $this->getTutor();
        $experiences = $tutor->experiences ?? [];
        if (isset($experiences[$index])) {
            array_splice($experiences, $index, 1);
            $tutor->update(['experiences' => $experiences]);
        }

        return response()->json(['message' => 'Experience deleted', 'experiences' => $tutor->experiences]);
    }

    /**
     * Get Teaching Details
     */
    public function getTeachingDetails(Request $request): JsonResponse
    {
        $tutor = $this->getTutor();
        return response()->json([
            'charge_type' => $tutor->charge_type,
            'min_fee' => $tutor->min_fee,
            'max_fee' => $tutor->max_fee,
            'fee_notes' => $tutor->fee_notes,
            'teaching_style' => $tutor->teaching_style,
            'class_types' => $tutor->class_types ?? [],
            'rate_per_hour' => $tutor->rate_per_hour,
            'experience_years' => $tutor->experience_years,
            'experience_total_years' => $tutor->experience_total_years,
            'experience_teaching_years' => $tutor->experience_teaching_years,
            'experience_online_years' => $tutor->experience_online_years,
            'session_duration' => $tutor->session_duration,
            'travel_willing' => $tutor->travel_willing,
            'travel_distance_km' => $tutor->travel_distance_km,
            'online_available' => $tutor->online_available,
            'has_digital_pen' => $tutor->has_digital_pen,
            'helps_homework' => $tutor->helps_homework,
            'employed_full_time' => $tutor->employed_full_time,
            'opportunities' => $tutor->opportunities ?? [],
            'languages' => $tutor->languages ?? [],
        ]);
    }

    /**
     * Update Teaching Details
     */
    public function updateTeachingDetails(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'charge_type' => 'required|string|in:Hourly,Monthly',
            'min_fee' => 'required|numeric|min:0',
            'max_fee' => 'required|numeric|min:0',
            'fee_notes' => 'nullable|string',
            'teaching_style' => 'nullable|string',
            'class_types' => 'nullable|array',
            'class_types.*' => 'string',
            'rate_per_hour' => 'nullable|numeric|min:0',
            'experience_years' => 'nullable|integer|min:0',
            'experience_total_years' => 'required|integer|min:0',
            'experience_teaching_years' => 'required|integer|min:0',
            'experience_online_years' => 'required|integer|min:0',
            'session_duration' => 'nullable|integer|min:0',
            'travel_willing' => 'required|boolean',
            'travel_distance_km' => 'nullable|integer|min:0',
            'online_available' => 'required|boolean',
            'has_digital_pen' => 'required|boolean',
            'helps_homework' => 'required|boolean',
            'employed_full_time' => 'required|boolean',
            'opportunities' => 'nullable|array',
            'opportunities.*' => 'string|in:Part Time,Full Time,Both (Part Time & Full Time)',
            'languages' => 'nullable|array',
            'languages.*' => 'string|max:50',
        ]);

        if ($validated['min_fee'] > $validated['max_fee']) {
            return response()->json(['message' => 'Maximum fee must be greater than or equal to minimum fee'], 422);
        }

        // keep legacy experience_years in sync with teaching experience
        $validated['experience_years'] = $validated['experience_teaching_years'];

        $tutor = $this->getTutor();
        $tutor->update($validated);

        return response()->json(['message' => 'Teaching details updated', 'tutor' => $tutor]);
    }

    /**
     * Get Description
     */
    public function getDescription(Request $request): JsonResponse
    {
        $tutor = $this->getTutor();
        return response()->json([
            'description' => $tutor->description,
            'do_not_share_contact' => $tutor->do_not_share_contact,
        ]);
    }

    /**
     * Update Description
     */
    public function updateDescription(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'description' => 'nullable|string',
            'do_not_share_contact' => 'nullable|boolean',
        ]);

        $tutor = $this->getTutor();
        $tutor->update($validated);

        return response()->json(['message' => 'Description updated', 'tutor' => $tutor]);
    }

    /**
     * Get Courses
     */
    public function getCourses(Request $request): JsonResponse
    {
        $tutor = $this->getTutor();
        return response()->json(['courses' => $tutor->courses]);
    }

    /**
     * Store Course
     */
    public function storeCourse(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration' => 'nullable|numeric',
            'duration_unit' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'currency' => 'required|string',
            'mode_of_delivery' => 'required|string',
            'group_size' => 'nullable|string',
            'certificate' => 'nullable|string',
            'languages' => 'nullable|array',
            'languages.*' => 'string',
        ]);

        $tutor = $this->getTutor();
        $courses = $tutor->courses ?? [];
        
        // Add ID for tracking
        $validated['id'] = uniqid('course_', true);
        
        $courses[] = $validated;
        $tutor->update(['courses' => $courses]);

        return response()->json([
            'message' => 'Course added successfully!',
            'courses' => $tutor->fresh()->courses
        ]);
    }

    /**
     * Update Course
     */
    public function updateCourse(Request $request, $id): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration' => 'nullable|numeric',
            'duration_unit' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'currency' => 'required|string',
            'mode_of_delivery' => 'required|string',
            'group_size' => 'nullable|string',
            'certificate' => 'nullable|string',
            'languages' => 'nullable|array',
            'languages.*' => 'string',
        ]);

        $tutor = $this->getTutor();
        $courses = $tutor->courses ?? [];
        
        // Find course by ID or index
        $courseIndex = null;
        foreach ($courses as $index => $course) {
            if ((isset($course['id']) && $course['id'] === $id) || $index == $id) {
                $courseIndex = $index;
                break;
            }
        }
        
        if ($courseIndex !== null) {
            // Preserve the ID if it exists
            if (isset($courses[$courseIndex]['id'])) {
                $validated['id'] = $courses[$courseIndex]['id'];
            }
            $courses[$courseIndex] = $validated;
            $tutor->update(['courses' => $courses]);
            
            return response()->json([
                'message' => 'Course updated successfully!',
                'courses' => $tutor->fresh()->courses
            ]);
        }

        return response()->json(['message' => 'Course not found'], 404);
    }

    /**
     * Delete Course
     */
    public function deleteCourse(Request $request, $id): JsonResponse
    {
        $tutor = $this->getTutor();
        $courses = $tutor->courses ?? [];
        
        // Find course by ID or index
        $courseIndex = null;
        foreach ($courses as $index => $course) {
            if ((isset($course['id']) && $course['id'] === $id) || $index == $id) {
                $courseIndex = $index;
                break;
            }
        }
        
        if ($courseIndex !== null) {
            array_splice($courses, $courseIndex, 1);
            $tutor->update(['courses' => $courses]);
            
            return response()->json([
                'message' => 'Course deleted successfully!',
                'courses' => $tutor->fresh()->courses
            ]);
        }

        return response()->json(['message' => 'Course not found'], 404);
    }

    /**
     * Get Profile (Public View)
     */
    public function viewProfile(Request $request, $id = null): JsonResponse
    {
        $tutorId = $id ?? $this->getTutor()->id;
        $tutor = Tutor::with('subjects')->findOrFail($tutorId);

        // Privacy handling: sanitize if do_not_share_contact is true
        $profileData = $tutor->toArray();
        if ($tutor->do_not_share_contact) {
            $profileData['user'] = $tutor->user->only(['name', 'id']);
        } else {
            $profileData['user'] = $tutor->user->only(['name', 'id', 'email', 'phone']);
        }

        return response()->json($profileData);
    }

    /**
     * Get Settings
     */
    public function getSettings(Request $request): JsonResponse
    {
        $tutor = $this->getTutor();
        return response()->json([
            'do_not_share_contact' => $tutor->do_not_share_contact,
            'notification_preferences' => $tutor->notification_preferences ?? [],
        ]);
    }

    /**
     * Update Settings
     */
    public function updateSettings(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'do_not_share_contact' => 'nullable|boolean',
            'notification_preferences' => 'nullable|array',
        ]);

        $tutor = $this->getTutor();
        $tutor->update($validated);

        return response()->json(['message' => 'Settings updated', 'tutor' => $tutor]);
    }

    /**
     * Get WhatsApp Chat Link for Tutor
     */
    public function getWhatsAppLink($tutorId): JsonResponse
    {
        $tutor = Tutor::with('user')->findOrFail($tutorId);

        // Check privacy settings
        if ($tutor->do_not_share_contact) {
            return response()->json([
                'message' => 'Tutor has disabled contact sharing',
                'available' => false,
            ], 403);
        }

        $phone = $tutor->whatsapp_number ?? $tutor->user->phone;
        
        if (!$phone) {
            return response()->json([
                'message' => 'WhatsApp number not available',
                'available' => false,
            ], 404);
        }

        $message = "Hi {$tutor->user->name}, I found your profile on Namate24. I'm interested in your tutoring services.";
        
        return response()->json([
            'available' => true,
            'chat_link' => WhatsAppService::getChatLink($phone, $message),
            'api_link' => WhatsAppService::getApiLink($phone, $message),
            'phone' => WhatsAppService::formatPhone($phone),
        ]);
    }

    /**
     * Get Company WhatsApp Contact
     */
    public function getCompanyWhatsApp(): JsonResponse
    {
        $companyPhone = config('services.whatsapp.company_number');
        
        if (!$companyPhone) {
            return response()->json([
                'message' => 'Company WhatsApp not configured',
                'available' => false,
            ], 404);
        }

        $message = "Hi, I need help with Namate24 platform.";

        return response()->json([
            'available' => true,
            'chat_link' => WhatsAppService::getChatLink($companyPhone, $message),
            'api_link' => WhatsAppService::getApiLink($companyPhone, $message),
            'phone' => WhatsAppService::formatPhone($companyPhone),
        ]);
    }
}
