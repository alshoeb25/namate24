<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Test Student Middleware Route
|--------------------------------------------------------------------------
*/

Route::get('/test-student-middleware', function () {
    $user = auth('api')->user();
    
    if (!$user) {
        return response()->json(['error' => 'No authenticated user'], 401);
    }
    
    // Load the student relationship
    $user->load('student');
    
    return response()->json([
        'user_id' => $user->id,
        'email' => $user->email,
        'has_student' => $user->student ? true : false,
        'student_id' => $user->student?->id,
        'is_disabled' => $user->student?->is_disabled ?? false,
        'disabled_reason' => $user->student?->disabled_reason,
        'middleware_bypassed' => true,
    ]);
})->middleware('auth:api');

Route::get('/test-student-with-check', function () {
    return response()->json([
        'message' => 'Middleware passed successfully',
        'user' => auth('api')->user()->only(['id', 'email']),
    ]);
})->middleware(['auth:api', 'check.student.profile']);
