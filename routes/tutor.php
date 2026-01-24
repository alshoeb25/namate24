<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Tutor\ProfileController;

Route::middleware(['auth', 'role:tutor'])->prefix('tutor/profile')->name('tutor.profile.')->group(function () {
    // Dashboard
    Route::get('/', [ProfileController::class, 'dashboard'])->name('dashboard');

    // Personal Details
    Route::get('personal-details', [ProfileController::class, 'personalDetails'])->name('personal-details');
    Route::post('personal-details', [ProfileController::class, 'updatePersonalDetails'])->name('update-personal-details');
    // Phone OTP
    Route::post('phone/send-otp', [ProfileController::class, 'sendPhoneOtp'])->name('phone.send-otp');
    Route::post('phone/verify-otp', [ProfileController::class, 'verifyPhoneOtp'])->name('phone.verify-otp');

    // Photo
    Route::get('photo', [ProfileController::class, 'photo'])->name('photo');
    Route::post('photo', [ProfileController::class, 'updatePhoto'])->name('update-photo');

    // Video
    Route::get('video', [ProfileController::class, 'video'])->name('video');
    Route::post('video', [ProfileController::class, 'updateVideo'])->name('update-video');
    Route::delete('video', [ProfileController::class, 'deleteVideo'])->name('delete-video');

    // Subjects
    Route::get('subjects', [ProfileController::class, 'subjects'])->name('subjects');
    Route::post('subjects', [ProfileController::class, 'updateSubjects'])->name('update-subjects');
    Route::post('subjects/add', [ProfileController::class, 'addSubject'])->name('subjects.add');

    // Address
    Route::get('address', [ProfileController::class, 'address'])->name('address');
    Route::post('address', [ProfileController::class, 'updateAddress'])->name('update-address');

    // Education
    Route::get('education', [ProfileController::class, 'education'])->name('education');
    Route::post('education', [ProfileController::class, 'storeEducation'])->name('store-education');
    Route::post('education/{index}', [ProfileController::class, 'updateEducation'])->name('update-education');
    Route::delete('education/{index}', [ProfileController::class, 'deleteEducation'])->name('delete-education');

    // Experience
    Route::get('experience', [ProfileController::class, 'experience'])->name('experience');
    Route::post('experience', [ProfileController::class, 'storeExperience'])->name('store-experience');
    Route::post('experience/{index}', [ProfileController::class, 'updateExperience'])->name('update-experience');
    Route::delete('experience/{index}', [ProfileController::class, 'deleteExperience'])->name('delete-experience');

    // Teaching Details
    Route::get('teaching-details', [ProfileController::class, 'teachingDetails'])->name('teaching-details');
    Route::post('teaching-details', [ProfileController::class, 'updateTeachingDetails'])->name('update-teaching-details');

    // Profile Description
    Route::get('description', [ProfileController::class, 'description'])->name('description');
    Route::post('description', [ProfileController::class, 'updateDescription'])->name('update-description');

    // Courses
    Route::get('courses', [ProfileController::class, 'courses'])->name('courses');
    Route::post('courses', [ProfileController::class, 'storeCourse'])->name('store-course');
    Route::post('courses/{index}', [ProfileController::class, 'updateCourse'])->name('update-course');
    Route::delete('courses/{index}', [ProfileController::class, 'deleteCourse'])->name('delete-course');

    // View Profile
    Route::get('view/{id?}', [ProfileController::class, 'viewProfile'])->name('view');

    // Settings
    Route::get('settings', [ProfileController::class, 'settings'])->name('settings');
    Route::post('settings', [ProfileController::class, 'updateSettings'])->name('update-settings');
});
