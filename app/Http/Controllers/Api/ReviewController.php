<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request, $tutorId)
    {
        $data = $request->validate(['rating'=>'required|integer|min:1|max:5','comment'=>'nullable|string','booking_id'=>'nullable|exists:bookings,id']);
        $review = Review::create([
            'tutor_id'=>$tutorId,
            'student_id'=>$request->user()->id,
            'rating'=>$data['rating'],
            'comment'=>$data['comment'] ?? null,
            'booking_id'=>$data['booking_id'] ?? null,
            'moderation_status'=>'pending'
        ]);
        return response()->json($review,201);
    }

    public function moderate(Request $request, Review $review)
    {
        $this->authorize('moderate', $review);
        $request->validate(['moderation_status'=>'required|in:approved,rejected']);
        $review->update($request->only('moderation_status'));
        return response()->json($review);
    }
}