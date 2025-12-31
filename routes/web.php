<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\TutorPdfController;

/*
| Web routes
*/
Route::get('/', function () {
    return view('welcome');
});

// Login route (SPA - redirects to /login in Vue Router)
Route::get('/login', function () {
    return view('welcome');
})->name('login');

// Register route (SPA)
Route::get('/register', function () {
    return view('welcome');
})->name('register');

// Search routes (SPA)
Route::get('/search', function () {
    return view('welcome');
})->name('search');

Route::get('/tutors', function () {
    return view('welcome');
})->name('tutors');

Route::get('/tutor-jobs', function () {
    return view('welcome');
})->name('tutor-jobs');

// Dynamic SEO-friendly routes
Route::get('/{subject}-tutors-in-{city}', function () {
    return view('welcome');
})->where(['subject' => '[a-z0-9\-]+', 'city' => '[a-z0-9\-]+']);

Route::get('/{subject}-tutors', function () {
    return view('welcome');
})->where('subject', '[a-z0-9\-]+');

Route::get('/tutors-in-{city}', function () {
    return view('welcome');
})->where('city', '[a-z0-9\-]+');

// Tutor profile view routes - PUBLIC (numeric id only)
Route::get('/tutors/{id}', [\App\Http\Controllers\Tutor\ProfileController::class, 'viewProfile'])
    ->whereNumber('id')
    ->name('tutor.public.view');

// Legacy /tutor/{id}
Route::get('/tutor/{id}', [\App\Http\Controllers\Tutor\ProfileController::class, 'viewProfile'])
    ->whereNumber('id')
    ->name('tutor.public.view.legacy');

// Conversations routes (SPA)
Route::get('/conversations', function () {
    return view('welcome');
});

Route::get('/conversations/{id}', function () {
    return view('welcome');
})->whereNumber('id');

// Tutor profile pages are handled by the SPA (Vue Router)
// Serve the SPA shell without requiring session auth; APIs remain protected via JWT
Route::get('/tutor/profile/{any?}', function () {
    return view('welcome');
})->where('any', '.*');

// Tutor wallet page
Route::get('/tutor/wallet', function () {
    return view('welcome');
});

// Student pages are handled by the SPA (Vue Router)
Route::get('/student/{any?}', function () {
    return view('welcome');
})->where('any', '.*');

// Profile management route (SPA)
Route::get('/profile', function () {
    return view('welcome');
});

// Auth-related SPA routes (password reset flow)
Route::get('/forgot-password', function () {
    return view('welcome');
});
Route::get('/reset-password', function () {
    return view('welcome');
});

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('admin/tutors/{tutor}/pdf', [TutorPdfController::class, 'show'])->name('admin.tutors.pdf');
});

// Tutor Profile Routes
require __DIR__.'/tutor.php';

// Catch-all for SPA routes excluding API and some reserved prefixes
Route::get('/{any}', function () {
    return view('welcome');
})->where('any', '^(?!api|admin|storage|telescope|horizon).*$');