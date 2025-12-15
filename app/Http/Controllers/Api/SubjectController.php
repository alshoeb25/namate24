<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    public function index() { return response()->json(Subject::all()); }

    // Admin endpoints (protected by role middleware)
    public function store(Request $r) { $s = Subject::create($r->validate(['name'=>'required','slug'=>'required'])); return response()->json($s,201); }
    public function update(Request $r, Subject $subject) { $subject->update($r->all()); return response()->json($subject); }
    public function destroy(Subject $subject) { $subject->delete(); return response()->json(['deleted'=>true]); }
}