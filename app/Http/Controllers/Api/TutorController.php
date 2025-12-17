<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tutor;
use Illuminate\Http\Request;

class TutorController extends Controller
{
    public function index(Request $request)
    {
        $query = Tutor::with('user','subjects')->where('moderation_status','approved');

        // Subject search: prioritize subject_id, fallback to subject_search_name or subject
        if ($subjectId = $request->input('subject_id')) {
            $query->whereHas('subjects', fn($q) => $q->where('subjects.id', $subjectId));
        } elseif ($subjectSearchName = $request->input('subject_search_name')) {
            $query->whereHas('subjects', fn($q) => $q->where('subjects.name', 'LIKE', '%' . $subjectSearchName . '%'));
        } elseif ($subject = $request->input('subject')) {
            $query->whereHas('subjects', fn($q) => $q->where('subjects.name', 'LIKE', '%' . $subject . '%'));
        }

        // Location search
        if ($location = $request->input('location')) {
            $query->where(function($q) use ($location) {
                $q->where('city', 'LIKE', '%' . $location . '%')
                  ->orWhere('state', 'LIKE', '%' . $location . '%')
                  ->orWhere('zip_code', 'LIKE', '%' . $location . '%');
            });
        }

        // Filters
        if ($request->input('online') === 'true') {
            $query->where('online_available', true);
        }

        if ($request->input('home') === 'true') {
            $query->where(function($q) {
                $q->where('teaching_mode', 'LIKE', '%home%')
                  ->orWhere('teaching_mode', 'LIKE', '%offline%');
            });
        }

        if ($request->input('verified') === 'true') {
            $query->where('verified', true);
        }

        // Experience filter
        if ($experience = $request->input('experience')) {
            if (strpos($experience, '+') !== false) {
                // "5+" means 5 or more
                $min = (int)str_replace('+', '', $experience);
                $query->where('experience_total_years', '>=', $min);
            } elseif (strpos($experience, '-') !== false) {
                // "3-5" means between 3 and 5
                [$min, $max] = explode('-', $experience);
                $query->whereBetween('experience_total_years', [(int)$min, (int)$max]);
            }
        }

        // Price range filter
        if ($priceRange = $request->input('price_range')) {
            if (strpos($priceRange, '+') !== false) {
                // "1000+" means 1000 or more
                $min = (int)str_replace('+', '', $priceRange);
                $query->where('price_per_hour', '>=', $min);
            } elseif (strpos($priceRange, '-') !== false) {
                // "500-1000" means between 500 and 1000
                [$min, $max] = explode('-', $priceRange);
                $query->whereBetween('price_per_hour', [(int)$min, (int)$max]);
            }
        }

        // Legacy filters for backward compatibility
        if ($mode = $request->query('mode')) $query->where('teaching_mode', $mode);
        if ($city = $request->query('city')) $query->where('city', $city);
        if ($min_price = $request->query('min_price')) $query->where('price_per_hour', '>=', $min_price);
        if ($max_price = $request->query('max_price')) $query->where('price_per_hour', '<=', $max_price);

        $perPage = (int)$request->query('per_page', 20);
        $results = $query->paginate($perPage);

        return response()->json($results);
    }

    public function show($id)
    {
        $tutor = Tutor::with('user','subjects')->findOrFail($id);
        if ($tutor->moderation_status !== 'approved') {
            return response()->json(['message'=>'Tutor not available'], 404);
        }
        return response()->json($tutor);
    }

    public function store(Request $request)
    {
        $request->validate([
            'headline'=>'required|string',
            'about'=>'nullable|string',
            'experience_years'=>'nullable|integer',
            'price_per_hour'=>'nullable|numeric',
            'teaching_mode'=>'nullable|in:online,offline,both',
            'city'=>'nullable|string',
        ]);

        $user = $request->user();
        if (! $user->hasRole('tutor')) {
            $user->assignRole('tutor');
        }

        $tutor = $user->tutor()->updateOrCreate([], $request->only(['headline','about','experience_years','price_per_hour','teaching_mode','city']));
        $tutor->update(['moderation_status'=>'pending']);

        return response()->json($tutor, 201);
    }
}