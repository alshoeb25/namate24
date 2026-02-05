<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SubjectController extends Controller
{
    public function index()
    {
        $subjects = Cache::remember('home.trending_subjects', now()->addMinutes(10), function () {
            return Subject::all();
        });

        return response()->json($subjects);
    }

    public function search(Request $request)
    {
        $query = $request->input('search', '');
        $limit = $request->input('limit', 10);

        if (strlen($query) < 2) {
            return response()->json(['data' => []]);
        }

        $subjects = Subject::where('name', 'LIKE', '%' . $query . '%')
            ->limit($limit)
            ->get()
            ->map(function ($subject) {
                return [
                    'id' => $subject->id,
                    'name' => $subject->name,
                    'slug' => $subject->slug ?? strtolower(str_replace(' ', '-', $subject->name)),
                    'category' => $subject->category ?? null
                ];
            });

        return response()->json(['data' => $subjects]);
    }

    // Admin endpoints (protected by role middleware)
    public function store(Request $r)
    {
        $s = Subject::create($r->validate(['name'=>'required','slug'=>'required']));
        Cache::forget('home.trending_subjects');
        return response()->json($s, 201);
    }

    public function update(Request $r, Subject $subject)
    {
        $subject->update($r->all());
        Cache::forget('home.trending_subjects');
        return response()->json($subject);
    }

    public function destroy(Subject $subject)
    {
        $subject->delete();
        Cache::forget('home.trending_subjects');
        return response()->json(['deleted' => true]);
    }
}