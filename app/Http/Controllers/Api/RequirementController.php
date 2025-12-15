<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StudentRequirement;
use Illuminate\Http\Request;

class RequirementController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'subject_id'=>'required|exists:subjects,id',
            'budget_min'=>'nullable|integer',
            'budget_max'=>'nullable|integer',
            'mode'=>'required|in:online,offline,both',
            'details'=>'nullable|string',
            'city'=>'nullable|string',
            'desired_start'=>'nullable|date'
        ]);

        $requirement = StudentRequirement::create(array_merge($data, ['student_id'=>$request->user()->id]));
        return response()->json($requirement, 201);
    }

    public function index(Request $request)
    {
        $query = StudentRequirement::query()->with('subject','student');
        if ($request->user()->hasRole('tutor')) {
            $perPage = (int)$request->query('per_page', 20);
            return response()->json($query->paginate($perPage));
        }
        abort(403);
    }

    public function show($id){ return response()->json(StudentRequirement::with('subject','student')->findOrFail($id)); }
}