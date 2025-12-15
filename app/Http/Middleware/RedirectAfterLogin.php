<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectAfterLogin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            
            // If user is tutor and trying to access home, check profile completion
            if ($user->role === 'tutor' && $request->path() === '/') {
                $tutor = $user->tutor;
                // Basic completion calculation (mirrors ProfileController::calculateProfileCompletion)
                $sections = [
                    'personal_details' => $user && $user->name && $user->email && $user->phone,
                    'photo' => $user && $user->avatar,
                    'video' => $tutor && ($tutor->introductory_video || $tutor->youtube_intro_url),
                    'subjects' => $tutor && $tutor->subjects()->count() > 0,
                    'address' => $tutor && ($tutor->address || $tutor->city),
                    'education' => $tutor && $tutor->educations && count($tutor->educations) > 0,
                    'experience' => $tutor && $tutor->experiences && count($tutor->experiences) > 0,
                    'teaching_details' => $tutor && $tutor->experience_years !== null && $tutor->price_per_hour,
                    'description' => $tutor && $tutor->headline && $tutor->about,
                    'courses' => $tutor && $tutor->courses && count($tutor->courses) > 0,
                ];

                $completed = count(array_filter($sections));
                $percentage = round(($completed / count($sections)) * 100);

                if ($percentage < 100) {
                    // Send tutor to personal details step to continue completing profile
                    return redirect()->route('tutor.profile.personal-details');
                }

                return redirect()->route('tutor.profile.dashboard');
            }
            
            // If user is admin and trying to access home, redirect to admin panel
            if ($user->role === 'admin' && $request->path() === '/') {
                return redirect('/admin');
            }
        }

        return $next($request);
    }
}
