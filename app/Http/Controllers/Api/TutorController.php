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

        if ($subject = $request->query('subject_id')) {
            $query->whereHas('subjects', fn($q)=> $q->where('subjects.id', $subject));
        }

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