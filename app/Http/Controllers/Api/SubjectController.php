<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    public function index() { return response()->json(Subject::all()); }

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
    public function store(Request $r) { $s = Subject::create($r->validate(['name'=>'required','slug'=>'required'])); return response()->json($s,201); }
    public function update(Request $r, Subject $subject) { $subject->update($r->all()); return response()->json($subject); }
    public function destroy(Subject $subject) { $subject->delete(); return response()->json(['deleted'=>true]); }
}