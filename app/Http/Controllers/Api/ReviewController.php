<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $query = Review::with(['tutor.user', 'student'])
            ->where('moderation_status', 'approved');

        // Filter by tutor
        if ($request->has('tutor_id')) {
            $query->where('tutor_id', $request->tutor_id);
        }

        // Filter by student
        if ($request->has('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        // Filter by booking
        if ($request->has('booking_id')) {
            $query->where('booking_id', $request->booking_id);
        }

        $reviews = $query->orderByDesc('created_at')
            ->paginate($request->integer('per_page', 20));

        return response()->json($reviews);
    }

    // List reviews authored by the authenticated student (all statuses)
    public function myReviews(Request $request)
    {
        $userId = $request->user()->id;
        $reviews = Review::with(['tutor:id,user_id,headline,photo,rating_avg,rating_count', 'tutor.user:id,name,avatar'])
            ->where('student_id', $userId)
            ->orderByDesc('created_at')
            ->paginate($request->integer('per_page', 20));

        return response()->json($reviews);
    }

    public function store(Request $request, $tutorId)
    {
        $data = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string',
            'booking_id' => 'nullable|exists:bookings,id',
            'related_requirement_id' => 'nullable|exists:student_requirements,id'
        ]);
        
        $review = Review::create([
            'tutor_id' => $tutorId,
            'student_id' => $request->user()->id,
            'rating' => $data['rating'],
            'comment' => $data['comment'] ?? null,
            'booking_id' => $data['booking_id'] ?? null,
            'related_requirement_id' => $data['related_requirement_id'] ?? null,
            'moderation_status' => 'pending'
        ]);
        
        return response()->json($review, 201);
    }

    public function moderate(Request $request, Review $review)
    {
        $this->authorize('moderate', $review);
        $oldStatus = $review->moderation_status;
        $data = $request->validate([
            'moderation_status' => 'required|in:approved,rejected,pending'
        ]);
        
        $review->update(['moderation_status' => $data['moderation_status']]);
        
        // Recalculate tutor rating if status changed to/from approved
        if ($oldStatus !== $data['moderation_status'] && 
            (in_array($oldStatus, ['approved', null]) || in_array($data['moderation_status'], ['approved']))) {
            $review->tutor->updateRating();
        }
        
        return response()->json([
            'message' => 'Review moderation status updated',
            'review' => $review
        ]);
    }

    // Update a review owned by the authenticated student
    public function updateMine(Request $request, Review $review)
    {
        if ($review->student_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $data = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string'
        ]);

        $review->rating = $data['rating'];
        $review->comment = $data['comment'] ?? null;
        // Reset moderation on edit
        $review->moderation_status = 'pending';
        $review->save();

        return response()->json($review->load(['tutor:id,user_id,headline,photo,rating_avg,rating_count', 'tutor.user:id,name,avatar']));
    }
}