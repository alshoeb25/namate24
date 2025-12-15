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

// Tutor profile view route (SPA)
Route::get('/tutor/{id}', function () {
    return view('welcome');
})->whereNumber('id');

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

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('admin/tutors/{tutor}/pdf', [TutorPdfController::class, 'show'])->name('admin.tutors.pdf');
});

// Tutor Profile Routes
require __DIR__.'/tutor.php';