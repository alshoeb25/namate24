<?php

namespace App\Http\Controllers\Tutor;

use App\Http\Controllers\Controller;
use App\Models\Tutor;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function __construct()
    {
        // Public view does not require auth/role; all other actions remain protected
        $this->middleware('auth')->except(['viewProfile']);
        $this->middleware('role:tutor')->except(['viewProfile']);
    }

    /**
     * Show the tutor profile dashboard
     */
    public function dashboard()
    {
        $tutor = Auth::user()->tutor;
        
        return view('tutor.profile.dashboard', [
            'tutor' => $tutor,
            'completionPercentage' => $this->calculateProfileCompletion($tutor)
        ]);
    }

    /**
     * Show personal details form
     */
    public function personalDetails()
    {
        $tutor = Auth::user()->tutor;
        $user = Auth::user();

        return view('tutor.profile.steps.personal-details', [
            'tutor' => $tutor,
            'user' => $user
        ]);
    }

    /**
     * Update personal details
     */
    public function updatePersonalDetails(Request $request)
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'required|string|max:20',
            'gender' => 'required|in:male,female,other',
            'speciality' => 'nullable|string|max:255',
            'strength' => 'nullable|string|max:1000',
            'current_role' => 'nullable|string|max:255',
            'languages' => 'nullable|string|max:500',
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
        ]);

        // Parse languages from comma-separated string to array
        $languages = [];
        if (!empty($validated['languages'])) {
            $languages = array_map('trim', explode(',', $validated['languages']));
            $languages = array_filter($languages); // Remove empty values
        }

        $user->tutor->update([
            'gender' => $validated['gender'],
            'speciality' => $validated['speciality'] ?? null,
            'strength' => $validated['strength'] ?? null,
            'current_role' => $validated['current_role'] ?? null,
            'languages' => $languages ?: null,
        ]);

        return redirect()->route('tutor.profile.dashboard')
            ->with('success', 'Personal details updated successfully!');
    }

    /**
     * Send phone OTP for verification (demo: stores OTP in DB). 
     */
    public function sendPhoneOtp(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'phone' => ['required','string','regex:/^\+?[0-9]{7,15}$/'],
        ]);

        // Update phone (unverified until OTP confirmed)
        $user->phone = $validated['phone'];

        $otp = random_int(100000, 999999);
        $user->phone_otp = (string) $otp;
        $user->phone_otp_expires_at = now()->addMinutes(10);
        $user->save();

        // In production, integrate with SMS provider (Twilio, Nexmo)
        // For development, if APP_DEBUG, flash OTP to session for testing
        if (config('app.debug')) {
            session()->flash('otp_debug', $otp);
        }

        return back()->with('success', 'OTP sent to ' . $user->phone . '. It will expire in 10 minutes.');
    }

    /**
     * Verify phone OTP
     */
    public function verifyPhoneOtp(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'otp' => 'required|digits:6'
        ]);

        if (!$user->phone_otp || !$user->phone_otp_expires_at) {
            return back()->withErrors(['otp' => 'No OTP requested.']);
        }

        if (now()->gt($user->phone_otp_expires_at)) {
            return back()->withErrors(['otp' => 'OTP has expired. Please request a new one.']);
        }

        if ($user->phone_otp !== $validated['otp']) {
            return back()->withErrors(['otp' => 'Invalid OTP.']);
        }

        // Mark verified
        $user->phone_verified_at = now();
        $user->phone_otp = null;
        $user->phone_otp_expires_at = null;
        $user->save();

        return back()->with('success', 'Phone number verified successfully!');
    }

    /**
     * Show photo upload form
     */
    public function photo()
    {
        $tutor = Auth::user()->tutor;

        return view('tutor.profile.steps.photo', [
            'tutor' => $tutor
        ]);
    }

    /**
     * Upload profile photo
     */
    public function updatePhoto(Request $request)
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $user = Auth::user();
        
        // Delete old avatar if exists
        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        $path = $request->file('photo')->store('avatars', 'public');
        $user->update(['avatar' => $path]);

        return redirect()->route('tutor.profile.dashboard')
            ->with('success', 'Profile photo updated successfully!');
    }

    /**
     * Show introductory video form
     */
    public function video()
    {
        $tutor = Auth::user()->tutor;

        return view('tutor.profile.steps.video', [
            'tutor' => $tutor
        ]);
    }

    /**
     * Upload introductory video
     */
    public function updateVideo(Request $request)
    {
        // Accept either an uploaded video OR a YouTube URL
        $request->validate([
            'video' => 'nullable|mimes:mp4,mov,avi,wmv|max:102400', // 100MB max
            'youtube_url' => 'nullable|url',
            'video_title' => 'nullable|string|max:255',
        ]);

        $tutor = Auth::user()->tutor;

        // If a YouTube URL is provided, save it and remove any uploaded video
        if ($request->filled('youtube_url')) {
            // basic youtube hostname check
            if (!preg_match('/(youtube\.com|youtu\.be)/i', $request->youtube_url)) {
                return back()->withErrors(['youtube_url' => 'Please provide a valid YouTube URL.']);
            }

            // delete stored video file if exists
            if ($tutor->introductory_video) {
                Storage::disk('public')->delete($tutor->introductory_video);
            }

            $tutor->update([
                'youtube_intro_url' => $request->youtube_url,
                'introductory_video' => null,
                'video_title' => $request->video_title ?? $tutor->video_title,
            ]);

            return redirect()->route('tutor.profile.dashboard')
                ->with('success', 'YouTube intro link saved successfully!');
        }

        // Otherwise handle file upload
        if ($request->hasFile('video')) {
            $request->validate(['video' => 'mimes:mp4,mov,avi,wmv|max:102400']);

            // Delete old video if exists
            if ($tutor->introductory_video) {
                Storage::disk('public')->delete($tutor->introductory_video);
            }

            $videoPath = $request->file('video')->store('videos/introductory', 'public');

            $tutor->update([
                'introductory_video' => $videoPath,
                'youtube_intro_url' => null,
                'video_title' => $request->video_title ?? $tutor->video_title,
            ]);

            return redirect()->route('tutor.profile.dashboard')
                ->with('success', 'Introductory video uploaded successfully!');
        }

        return back()->withErrors(['video' => 'Please provide a video file or a YouTube URL.']);
    }

    /**
     * Show subjects form
     */
    public function subjects()
    {
        $tutor = Auth::user()->tutor;
        $allSubjects = Subject::all();
        $selectedSubjects = $tutor->subjects()
            ->withPivot('level')
            ->get()
            ->keyBy('id')
            ->toArray();

        return view('tutor.profile.steps.subjects', [
            'tutor' => $tutor,
            'allSubjects' => $allSubjects,
            'selectedSubjects' => $selectedSubjects
        ]);
    }

    /**
     * Update subjects
     */
    public function updateSubjects(Request $request)
    {
        $validated = $request->validate([
            'subjects' => 'required|array|min:1',
            'subjects.*.id' => 'required|exists:subjects,id',
            'subjects.*.level' => 'required|in:beginner,intermediate,advanced',
        ]);

        $tutor = Auth::user()->tutor;
        $syncData = [];

        foreach ($validated['subjects'] as $subject) {
            $syncData[$subject['id']] = ['level' => $subject['level']];
        }

        $tutor->subjects()->sync($syncData);

        return redirect()->route('tutor.profile.dashboard')
            ->with('success', 'Subjects updated successfully!');
    }

    /**
     * Add a new subject (if not exists). Accepts a single subject name.
     */
    public function addSubject(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $name = trim($validated['name']);

        // Check case-insensitively if subject exists
        $subject = Subject::whereRaw('LOWER(name) = ?', [mb_strtolower($name)])->first();

        if (!$subject) {
            $subject = Subject::create(['name' => $name]);
            return redirect()->back()->with('success', 'Subject "' . $subject->name . '" added successfully.');
        }

        return redirect()->back()->with('info', 'Subject "' . $subject->name . '" already exists.');
    }

    /**
     * Show address form
     */
    public function address()
    {
        $tutor = Auth::user()->tutor;

        return view('tutor.profile.steps.address', [
            'tutor' => $tutor
        ]);
    }

    /**
     * Update address
     */
    public function updateAddress(Request $request)
    {
        $validated = $request->validate([
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'country' => 'required|string|max:100',
            'postal_code' => 'required|string|max:20',
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
        ]);

        Auth::user()->tutor->update($validated);

        return redirect()->route('tutor.profile.dashboard')
            ->with('success', 'Address updated successfully!');
    }

    /**
     * Show education form
     */
    public function education()
    {
        $tutor = Auth::user()->tutor;
        $educations = $tutor->educations;

        return view('tutor.profile.steps.education', [
            'tutor' => $tutor,
            'educations' => $educations
        ]);
    }

    /**
     * Store education entry
     */
    public function storeEducation(Request $request)
    {
        $validated = $request->validate([
            'degree' => 'required|string|max:255',
            'degree_type' => 'nullable|in:Secondary,Higher Secondary,Diploma,Graduation,Advanced Diploma,Post Graduation,Doctorate/PhD,Certification,Other',
            'institution' => 'required|string|max:255',
            'city' => 'nullable|string|max:120',
            'field_of_study' => 'required|string|max:255',
            'start_month' => 'nullable|in:January,February,March,April,May,June,July,August,September,October,November,December',
            'start_year' => 'required|integer|min:1950|max:' . date('Y'),
            'end_month' => 'nullable|in:January,February,March,April,May,June,July,August,September,October,November,December',
            'end_year' => 'nullable|integer|min:1950|max:' . (date('Y') + 5),
            'study_mode' => 'nullable|in:Full Time,Part Time,Correspondence / Distance Learning',
            'speciality' => 'nullable|string|max:255',
            'score' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:1000',
        ]);

        $tutor = Auth::user()->tutor;
        $educations = $tutor->educations ?? [];

        // Normalize and store education entry
        $entry = [
            'degree' => $validated['degree'],
            'degree_type' => $validated['degree_type'] ?? null,
            'institution' => $validated['institution'],
            'city' => $validated['city'] ?? null,
            'field_of_study' => $validated['field_of_study'],
            'start_month' => $validated['start_month'] ?? null,
            'start_year' => $validated['start_year'],
            'end_month' => $validated['end_month'] ?? null,
            'end_year' => $validated['end_year'] ?? null,
            'study_mode' => $validated['study_mode'] ?? null,
            'speciality' => $validated['speciality'] ?? null,
            'score' => $validated['score'] ?? null,
            'description' => $validated['description'] ?? null,
        ];

        $educations[] = $entry;

        $tutor->update(['educations' => $educations]);

        return redirect()->route('tutor.profile.education')
            ->with('success', 'Education entry added successfully!');
    }

    /**
     * Update education entry
     */
    public function updateEducation(Request $request, $index)
    {
        $validated = $request->validate([
            'degree' => 'required|string|max:255',
            'degree_type' => 'nullable|in:Secondary,Higher Secondary,Diploma,Graduation,Advanced Diploma,Post Graduation,Doctorate/PhD,Certification,Other',
            'institution' => 'required|string|max:255',
            'city' => 'nullable|string|max:120',
            'field_of_study' => 'required|string|max:255',
            'start_month' => 'nullable|in:January,February,March,April,May,June,July,August,September,October,November,December',
            'start_year' => 'required|integer|min:1950|max:' . date('Y'),
            'end_month' => 'nullable|in:January,February,March,April,May,June,July,August,September,October,November,December',
            'end_year' => 'nullable|integer|min:1950|max:' . (date('Y') + 5),
            'study_mode' => 'nullable|in:Full Time,Part Time,Correspondence / Distance Learning',
            'speciality' => 'nullable|string|max:255',
            'score' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:1000',
        ]);

        $tutor = Auth::user()->tutor;
        $educations = $tutor->educations ?? [];

        if (isset($educations[$index])) {
            $entry = [
                'degree' => $validated['degree'],
                'degree_type' => $validated['degree_type'] ?? null,
                'institution' => $validated['institution'],
                'city' => $validated['city'] ?? null,
                'field_of_study' => $validated['field_of_study'],
                'start_month' => $validated['start_month'] ?? null,
                'start_year' => $validated['start_year'],
                'end_month' => $validated['end_month'] ?? null,
                'end_year' => $validated['end_year'] ?? null,
                'study_mode' => $validated['study_mode'] ?? null,
                'speciality' => $validated['speciality'] ?? null,
                'score' => $validated['score'] ?? null,
                'description' => $validated['description'] ?? null,
            ];

            $educations[$index] = $entry;
            $tutor->update(['educations' => $educations]);
        }

        return redirect()->route('tutor.profile.education')
            ->with('success', 'Education entry updated successfully!');
    }

    /**
     * Delete education entry
     */
    public function deleteEducation($index)
    {
        $tutor = Auth::user()->tutor;
        $educations = $tutor->educations ?? [];
        
        if (isset($educations[$index])) {
            unset($educations[$index]);
            $educations = array_values($educations); // Re-index array
            $tutor->update(['educations' => $educations]);
        }

        return redirect()->route('tutor.profile.education')
            ->with('success', 'Education entry deleted successfully!');
    }

    /**
     * Show experience form
     */
    public function experience()
    {
        $tutor = Auth::user()->tutor;
        $experiences = $tutor->experiences;

        return view('tutor.profile.steps.experience', [
            'tutor' => $tutor,
            'experiences' => $experiences
        ]);
    }

    /**
     * Store experience entry
     */
    public function storeExperience(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'company' => 'required|string|max:255',
            'city' => 'nullable|string|max:120',
            'designation' => 'nullable|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'currently_working' => 'boolean',
            'association' => 'nullable|in:Full Time,Part Time,Contract',
            'roles' => 'nullable|string|max:2000',
            'description' => 'nullable|string|max:1000',
        ]);

        $tutor = Auth::user()->tutor;
        $experiences = $tutor->experiences ?? [];

        $entry = [
            'title' => $validated['title'],
            'company' => $validated['company'],
            'city' => $validated['city'] ?? null,
            'designation' => $validated['designation'] ?? null,
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'] ?? null,
            'currently_working' => !empty($validated['currently_working']),
            'association' => $validated['association'] ?? null,
            'roles' => $validated['roles'] ?? null,
            'description' => $validated['description'] ?? null,
        ];

        $experiences[] = $entry;

        $tutor->update(['experiences' => $experiences]);

        return redirect()->route('tutor.profile.experience')
            ->with('success', 'Experience entry added successfully!');
    }

    /**
     * Update experience entry
     */
    public function updateExperience(Request $request, $index)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'company' => 'required|string|max:255',
            'city' => 'nullable|string|max:120',
            'designation' => 'nullable|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'currently_working' => 'boolean',
            'association' => 'nullable|in:Full Time,Part Time,Contract',
            'roles' => 'nullable|string|max:2000',
            'description' => 'nullable|string|max:1000',
        ]);

        $tutor = Auth::user()->tutor;
        $experiences = $tutor->experiences ?? [];

        if (isset($experiences[$index])) {
            $entry = [
                'title' => $validated['title'],
                'company' => $validated['company'],
                'city' => $validated['city'] ?? null,
                'designation' => $validated['designation'] ?? null,
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'] ?? null,
                'currently_working' => !empty($validated['currently_working']),
                'association' => $validated['association'] ?? null,
                'roles' => $validated['roles'] ?? null,
                'description' => $validated['description'] ?? null,
            ];

            $experiences[$index] = $entry;
            $tutor->update(['experiences' => $experiences]);
        }

        return redirect()->route('tutor.profile.experience')
            ->with('success', 'Experience entry updated successfully!');
    }

    /**
     * Delete experience entry
     */
    public function deleteExperience($index)
    {
        $tutor = Auth::user()->tutor;
        $experiences = $tutor->experiences ?? [];
        
        if (isset($experiences[$index])) {
            unset($experiences[$index]);
            $experiences = array_values($experiences);
            $tutor->update(['experiences' => $experiences]);
        }

        return redirect()->route('tutor.profile.experience')
            ->with('success', 'Experience entry deleted successfully!');
    }

    /**
     * Show teaching details form
     */
    public function teachingDetails()
    {
        $tutor = Auth::user()->tutor;

        return view('tutor.profile.steps.teaching-details', [
            'tutor' => $tutor
        ]);
    }

    /**
     * Update teaching details
     */
    public function updateTeachingDetails(Request $request)
    {
        $validated = $request->validate([
            'experience_years' => 'required|integer|min:0|max:70',
            'price_per_hour' => 'required|numeric|min:0',
            'teaching_mode' => 'required|array',
            'teaching_mode.*' => 'in:online,offline,both',
            'availability' => 'required|string',
        ]);

        Auth::user()->tutor->update([
            'experience_years' => $validated['experience_years'],
            'price_per_hour' => $validated['price_per_hour'],
            'teaching_mode' => $validated['teaching_mode'],
            'availability' => $validated['availability'],
        ]);

        return redirect()->route('tutor.profile.dashboard')
            ->with('success', 'Teaching details updated successfully!');
    }

    /**
     * Show profile description form
     */
    public function description()
    {
        $tutor = Auth::user()->tutor;

        return view('tutor.profile.steps.description', [
            'tutor' => $tutor
        ]);
    }

    /**
     * Update profile description
     */
    public function updateDescription(Request $request)
    {
        $validated = $request->validate([
            'headline' => 'required|string|max:255',
            'about' => 'required|string|min:50|max:2000',
            'teaching_methodology' => 'required|string|max:1000',
            'do_not_share_contact' => 'nullable|boolean',
        ]);

        $tutor = Auth::user()->tutor;

        // Handle "do not share contact" preference stored in settings
        $settings = $tutor->settings ?? [];
        $noContact = !empty($validated['do_not_share_contact']);
        $settings['no_contact'] = $noContact;

        // If user chooses not to share contact details, sanitize the text fields
        if ($noContact) {
            // remove emails
            $validated['about'] = preg_replace('/[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}/', '[contact removed]', $validated['about']);
            $validated['teaching_methodology'] = preg_replace('/[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}/', '[contact removed]', $validated['teaching_methodology']);

            // remove phone-like patterns (simple heuristic)
            $validated['about'] = preg_replace('/(\+?\d[\d\s\-\(\)]{6,}\d)/', '[contact removed]', $validated['about']);
            $validated['teaching_methodology'] = preg_replace('/(\+?\d[\d\s\-\(\)]{6,}\d)/', '[contact removed]', $validated['teaching_methodology']);
        }

        $tutor->update([
            'headline' => $validated['headline'],
            'about' => $validated['about'],
            'teaching_methodology' => $validated['teaching_methodology'],
            'settings' => $settings,
        ]);

        return redirect()->route('tutor.profile.dashboard')
            ->with('success', 'Profile description updated successfully!');
    }

    /**
     * Show courses form
     */
    public function courses()
    {
        $tutor = Auth::user()->tutor;
        $courses = $tutor->courses;

        return view('tutor.profile.steps.courses', [
            'tutor' => $tutor,
            'courses' => $courses
        ]);
    }

    /**
     * Store course entry
     */
    public function storeCourse(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
            'duration' => 'required|string|max:100',
            'duration_unit' => 'nullable|in:Hours,Days,Weeks,Months,Years',
            'level' => 'required|in:beginner,intermediate,advanced',
            'price' => 'required|numeric|min:0',
            'currency' => 'required|in:USD,INR',
            'mode_of_delivery' => 'nullable|in:Online,At my institute,At student\'s home,Flexible as per the student',
            'group_size' => 'nullable|in:1 - Individual,2,3,4,5,6 - 10,11 - 20,21 - 40,41 or more',
            'certificate_provided' => 'nullable|in:Yes,No',
            'language' => 'nullable|string|max:100',
        ]);

        $tutor = Auth::user()->tutor;
        $courses = $tutor->courses ?? [];
        
        $course = [
            'title' => $validated['title'],
            'description' => $validated['description'],
            'duration' => $validated['duration'],
            'duration_unit' => $validated['duration_unit'] ?? null,
            'level' => $validated['level'],
            'price' => $validated['price'],
            'currency' => $validated['currency'],
            'mode_of_delivery' => $validated['mode_of_delivery'] ?? null,
            'group_size' => $validated['group_size'] ?? null,
            'certificate_provided' => $validated['certificate_provided'] ?? null,
            'language' => $validated['language'] ?? null,
        ];

        $courses[] = $course;
        
        $tutor->update(['courses' => $courses]);

        return redirect()->route('tutor.profile.courses')
            ->with('success', 'Course added successfully!');
    }

    /**
     * Update course entry
     */
    public function updateCourse(Request $request, $index)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
            'duration' => 'required|string|max:100',
            'duration_unit' => 'nullable|in:Hours,Days,Weeks,Months,Years',
            'level' => 'required|in:beginner,intermediate,advanced',
            'price' => 'required|numeric|min:0',
            'currency' => 'required|in:USD,INR',
            'mode_of_delivery' => 'nullable|in:Online,At my institute,At student\'s home,Flexible as per the student',
            'group_size' => 'nullable|in:1 - Individual,2,3,4,5,6 - 10,11 - 20,21 - 40,41 or more',
            'certificate_provided' => 'nullable|in:Yes,No',
            'language' => 'nullable|string|max:100',
        ]);

        $tutor = Auth::user()->tutor;
        $courses = $tutor->courses ?? [];
        
        if (isset($courses[$index])) {
            $course = [
                'title' => $validated['title'],
                'description' => $validated['description'],
                'duration' => $validated['duration'],
                'duration_unit' => $validated['duration_unit'] ?? null,
                'level' => $validated['level'],
                'price' => $validated['price'],
                'currency' => $validated['currency'],
                'mode_of_delivery' => $validated['mode_of_delivery'] ?? null,
                'group_size' => $validated['group_size'] ?? null,
                'certificate_provided' => $validated['certificate_provided'] ?? null,
                'language' => $validated['language'] ?? null,
            ];

            $courses[$index] = $course;
            $tutor->update(['courses' => $courses]);
        }

        return redirect()->route('tutor.profile.courses')
            ->with('success', 'Course updated successfully!');
    }

    /**
     * Delete course entry
     */
    public function deleteCourse($index)
    {
        $tutor = Auth::user()->tutor;
        $courses = $tutor->courses ?? [];
        
        if (isset($courses[$index])) {
            unset($courses[$index]);
            $courses = array_values($courses);
            $tutor->update(['courses' => $courses]);
        }

        return redirect()->route('tutor.profile.courses')
            ->with('success', 'Course deleted successfully!');
    }

    /**
     * Show public profile
     */
    public function viewProfile($id = null)
    {
        if ($id) {
            $tutor = Tutor::with(['user', 'subjects'])
                ->where('moderation_status', 'approved')
                ->findOrFail($id);
        } else {
            $tutor = Auth::user()->tutor;
        }

        return view('tutor.profile.view-profile', [
            'tutor' => $tutor
        ]);
    }

    /**
     * Show settings
     */
    public function settings()
    {
        $user = Auth::user();

        return view('tutor.profile.steps.settings', [
            'user' => $user
        ]);
    }

    /**
     * Update settings
     */
    public function updateSettings(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'email_notifications' => 'boolean',
            'sms_notifications' => 'boolean',
            'profile_visibility' => 'required|in:public,private',
            'language' => 'required|in:en,es,fr,de',
        ]);

        $user->update(['settings' => $validated]);

        return redirect()->route('tutor.profile.settings')
            ->with('success', 'Settings updated successfully!');
    }

    /**
     * Calculate profile completion percentage
     */
    private function calculateProfileCompletion($tutor)
    {
        $sections = [
            'personal_details' => $tutor->user && $tutor->user->name && $tutor->user->email && $tutor->user->phone,
            'photo' => $tutor->user && $tutor->user->avatar,
            'video' => $tutor->introductory_video,
            'subjects' => $tutor->subjects()->count() > 0,
            'address' => $tutor->address && $tutor->city,
            'education' => $tutor->educations && count($tutor->educations) > 0,
            'experience' => $tutor->experiences && count($tutor->experiences) > 0,
            'teaching_details' => $tutor->experience_years !== null && $tutor->price_per_hour,
            'description' => $tutor->headline && $tutor->about,
            'courses' => $tutor->courses && count($tutor->courses) > 0,
        ];

        $completed = count(array_filter($sections));
        return round(($completed / count($sections)) * 100);
    }
}
