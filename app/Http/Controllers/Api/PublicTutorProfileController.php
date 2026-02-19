<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PublicTutorProfileController extends Controller
{
    /**
     * Check if user has unlocked contact for a specific tutor
     */
    public function checkContactAccess(Request $request, $tutorId)
    {
        $user = $request->user();
        $studentId = $request->query('student_id');

        if (!$studentId) {
            // Fallback to user's student profile
            $student = $user->student;
            if (!$student) {
                return response()->json([
                    'has_access' => false,
                    'can_review' => false,
                    'message' => 'Student profile not found'
                ], 404);
            }
            $studentId = $student->id;
        } else {
            // Verify the student belongs to the authenticated user
            $student = DB::table('students')->where('id', $studentId)->where('user_id', $user->id)->first();
            if (!$student) {
                return response()->json([
                    'has_access' => false,
                    'can_review' => false,
                    'message' => 'Student profile not found or does not belong to you'
                ], 403);
            }
        }

        // Check if student has contacted this tutor (via student_tutor_contacts table)
        $hasContact = DB::table('student_tutor_contacts')
            ->where('student_id', $studentId)
            ->where('tutor_id', $tutorId)
            ->exists();

        return response()->json([
            'has_access' => $hasContact,
            'can_review' => $hasContact,
        ]);
    }

    /**
    * Unlock tutor contact details (deduct coins from env)
     */
    public function unlockTutorContact(Request $request)
    {
        $request->validate([
            'tutor_id' => 'required|integer|exists:tutors,id',
            'student_id' => 'required|integer|exists:students,id'
        ]);

        $user = $request->user();
        $tutorId = $request->tutor_id;
        $studentId = $request->student_id;

        // Verify the student belongs to the authenticated user
        $student = DB::table('students')->where('id', $studentId)->where('user_id', $user->id)->first();
        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student profile not found or does not belong to you'
            ], 403);
        }

        // Prevent tutors from contacting themselves
        if ($user->tutor->id === $tutorId) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot unlock contact for your own profile'
            ], 422);
        }

        // Check if already unlocked
        $existingContact = DB::table('student_tutor_contacts')
            ->where('student_id', $studentId)
            ->where('tutor_id', $tutorId)
            ->first();

        if ($existingContact) {
            return response()->json([
                'success' => false,
                'message' => 'Contact details already unlocked for this tutor'
            ], 422);
        }

        // Fetch tutor details
        $tutor = DB::table('tutors')->where('id', $tutorId)->first();
        if (!$tutor) {
            return response()->json([
                'success' => false,
                'message' => 'Tutor not found'
            ], 404);
        }

        // Get tutor's user for name
        $tutorUser = DB::table('users')->where('id', $tutor->user_id)->first();

        // Check coin balance - Use nationality-based pricing (student's nationality)
        $requiredCoins = \App\Services\CoinPricingService::getCoinCost($user, 'contact_unlock');
        if ($user->coins < $requiredCoins) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient coins. You need ' . $requiredCoins . ' coins to unlock contact details.',
                'required' => $requiredCoins,
                'current_balance' => $user->coins
            ], 402);
        }

        try {
            DB::beginTransaction();

            // Deduct coins
            $user->coins -= $requiredCoins;
            $user->save();

            // Record transaction
            DB::table('coin_transactions')->insert([
                'user_id' => $user->id,
                'added_by_admin_id' => null,
                'type' => 'tutor_unlock_contact',
                'amount' => -$requiredCoins,
                'balance_after' => $user->coins,
                'description' => "Unlocked contact details for tutor: " . ($tutorUser->name ?? 'Unknown'),
                'payment_id' => null,
                'order_id' => null,
                'meta' => json_encode([
                    'tutor_id' => $tutorId,
                    'student_id' => $studentId
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Create contact record
            DB::table('student_tutor_contacts')->insert([
                'student_id' => $studentId,
                'tutor_id' => $tutorId,
                'coins_spent' => $requiredCoins,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            Log::info('Student unlocked tutor contact', [
                'student_id' => $studentId,
                'tutor_id' => $tutorId,
                'coins_spent' => $requiredCoins,
                'remaining_balance' => $user->coins
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Contact details unlocked successfully',
                'remaining_balance' => $user->coins,
                'coins_spent' => $requiredCoins
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error unlocking tutor contact', [
                'error' => $e->getMessage(),
                'student_id' => $studentId,
                'tutor_id' => $tutorId
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to unlock contact. Please try again.'
            ], 500);
        }
    }

    /**
     * Submit a review for a tutor
     */
    public function submitReview(Request $request)
    {
        $request->validate([
            'tutor_id' => 'required|integer|exists:tutors,id',
            'student_id' => 'required|integer|exists:students,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|max:1000'
        ]);

        $user = $request->user();
        $tutorId = $request->tutor_id;
        $studentId = $request->student_id;

        // Verify the student belongs to the authenticated user
        $student = DB::table('students')->where('id', $studentId)->where('user_id', $user->id)->first();
        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student profile not found or does not belong to you'
            ], 403);
        }

        // Prevent tutors from reviewing themselves
        if (($user->tutor?->id) === (int) $tutorId) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot review your own profile'
            ], 422);
        }

        // Check if student has contacted this tutor
        $hasContact = DB::table('student_tutor_contacts')
            ->where('student_id', $studentId)
            ->where('tutor_id', $tutorId)
            ->exists();

        if (!$hasContact) {
            return response()->json([
                'success' => false,
                'message' => 'You can only review tutors whose contact you have unlocked.'
            ], 403);
        }

        // Check if already reviewed
        $existingReview = DB::table('tutor_reviews')
            ->where('student_id', $studentId)
            ->where('tutor_id', $tutorId)
            ->first();

        if ($existingReview) {
            return response()->json([
                'success' => true,
                'message' => 'You have already reviewed this tutor.',
                'review' => [
                    'id' => $existingReview->id,
                    'rating' => $existingReview->rating,
                    'comment' => $existingReview->comment,
                    'status' => $existingReview->status ?? 'pending',
                    'created_at' => $existingReview->created_at
                ]
            ]);
        }

        try {
            // Insert review
            $reviewId = DB::table('tutor_reviews')->insertGetId([
                'tutor_id' => $tutorId,
                'student_id' => $studentId,
                'rating' => $request->rating,
                'comment' => $request->comment,
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Fetch the inserted review
            $review = DB::table('tutor_reviews')->where('id', $reviewId)->first();

            Log::info('Student submitted tutor review', [
                'student_id' => $studentId,
                'tutor_id' => $tutorId,
                'rating' => $request->rating
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Review submitted successfully. It will be visible after admin approval.',
                'review' => [
                    'id' => $review->id,
                    'rating' => $review->rating,
                    'comment' => $review->comment,
                    'status' => $review->status,
                    'created_at' => $review->created_at
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error submitting review', [
                'error' => $e->getMessage(),
                'student_id' => $studentId,
                'tutor_id' => $tutorId
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to submit review. Please try again.'
            ], 500);
        }
    }

    /**
     * Get coin balance
     */
    public function getCoinBalance(Request $request)
    {
        $user = $request->user();
        
        return response()->json([
            'balance' => $user->coins ?? 0
        ]);
    }

    /**
     * Get contact unlock coins based on authenticated student's nationality
     * (Protected route - requires authentication)
     */
    public function getContactUnlockCoins(Request $request, $tutorId = null)
    {
        $user = $request->user(); // Always authenticated - cannot be null
        
        // Contact unlock pricing is based on STUDENT's nationality (who is spending coins)
        $requiredCoins = \App\Services\CoinPricingService::getCoinCost($user, 'contact_unlock');
        $nationalityInfo = \App\Services\CoinPricingService::getNationalityInfo($user);
        
        Log::info('Contact unlock coins calculated', [
            'user_id' => $user->id,
            'country_iso' => $user->country_iso,
            'is_indian' => $nationalityInfo['is_indian'],
            'required_coins' => $requiredCoins
        ]);
        
        return response()->json([
            'contact_unlock_coins' => $requiredCoins,
            'student_country' => $user->country_iso,
            'student_is_indian' => $nationalityInfo['is_indian'],
            'pricing' => [
                'indian' => config('coins.pricing_by_nationality.contact_unlock.indian', 49),
                'non_indian' => config('coins.pricing_by_nationality.contact_unlock.non_indian', 99),
            ]
        ]);
    }

    /**
     * Get all contacted tutors (tutors whose contact has been unlocked)
     */
    public function getContactedTutors(Request $request)
    {
        $user = $request->user();
        $student = $user->student;

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student profile not found',
                'tutors' => []
            ], 404);
        }

        // Get all tutors that this student has contacted
        $contactedTutors = DB::table('student_tutor_contacts as stc')
            ->join('tutors as t', 'stc.tutor_id', '=', 't.id')
            ->join('users as u', 't.user_id', '=', 'u.id')
            ->where('stc.student_id', $student->id)
            ->select([
                't.id as tutor_id',
                't.user_id',
                't.headline',
                't.about',
                't.price_per_hour',
                't.rating_avg',
                't.rating_count',
                't.photo',
                'u.name',
                'u.email',
                'u.phone',
                'u.city',
                'stc.created_at as contacted_at',
                'stc.coins_spent'
            ])
            ->orderBy('stc.created_at', 'desc')
            ->get();

        // Get reviews for these tutors
        $tutorIds = $contactedTutors->pluck('tutor_id')->toArray();
        $reviews = DB::table('tutor_reviews')
            ->where('student_id', $student->id)
            ->whereIn('tutor_id', $tutorIds)
            ->get()
            ->keyBy('tutor_id');

        // Attach review info to each tutor
        $tutorsWithReviews = $contactedTutors->map(function ($tutor) use ($reviews) {
            $review = $reviews->get($tutor->tutor_id);
            
            return [
                'id' => $tutor->tutor_id,
                'user_id' => $tutor->user_id,
                'name' => $tutor->name,
                'headline' => $tutor->headline,
                'about' => $tutor->about,
                'price_per_hour' => $tutor->price_per_hour,
                'rating_avg' => $tutor->rating_avg,
                'rating_count' => $tutor->rating_count,
                'photo_url' => $tutor->photo ? asset('storage/' . $tutor->photo) : null,
                'city' => $tutor->city,
                'email' => $tutor->email,
                'phone' => $tutor->phone,
                'contacted_at' => $tutor->contacted_at,
                'coins_spent' => $tutor->coins_spent,
                'has_reviewed' => $review ? true : false,
                'review' => $review ? [
                    'id' => $review->id,
                    'rating' => $review->rating,
                    'comment' => $review->comment,
                    'status' => $review->status ?? 'pending',
                    'created_at' => $review->created_at
                ] : null
            ];
        });

        return response()->json([
            'success' => true,
            'tutors' => $tutorsWithReviews
        ]);
    }

    /**
     * Get user's review for a specific tutor
     */
    public function getReview(Request $request, $tutorId)
    {
        $user = $request->user();
        $studentId = $request->query('student_id');

        if (!$studentId && $user->student) {
            $studentId = $user->student->id;
        }

        if (!$studentId) {
            return response()->json([
                'success' => false,
                'message' => 'Student profile not found'
            ], 404);
        }

        $review = DB::table('tutor_reviews')
            ->where('student_id', $studentId)
            ->where('tutor_id', $tutorId)
            ->first();

        if (!$review) {
            return response()->json([
                'success' => false,
                'message' => 'Review not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'review' => [
                'id' => $review->id,
                'rating' => $review->rating,
                'comment' => $review->comment,
                'status' => $review->status ?? 'pending',
                'created_at' => $review->created_at,
                'updated_at' => $review->updated_at
            ]
        ]);
    }

    /**
     * Update review (only if not approved)
     */
    public function updateReview(Request $request, $reviewId)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|max:1000'
        ]);

        $user = $request->user();

        // Get review
        $review = DB::table('tutor_reviews')->where('id', $reviewId)->first();

        if (!$review) {
            return response()->json([
                'success' => false,
                'message' => 'Review not found'
            ], 404);
        }

        // Verify ownership
        $student = DB::table('students')->where('id', $review->student_id)->where('user_id', $user->id)->first();
        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to edit this review'
            ], 403);
        }

        // Check if review is approved
        if ($review->status === 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot edit approved reviews. Please contact support if you need to make changes.'
            ], 422);
        }

        try {
            // Update review
            DB::table('tutor_reviews')
                ->where('id', $reviewId)
                ->update([
                    'rating' => $request->rating,
                    'comment' => $request->comment,
                    'updated_at' => now()
                ]);

            // Fetch updated review
            $updatedReview = DB::table('tutor_reviews')->where('id', $reviewId)->first();

            Log::info('Student updated tutor review', [
                'review_id' => $reviewId,
                'student_id' => $review->student_id,
                'tutor_id' => $review->tutor_id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Review updated successfully',
                'review' => [
                    'id' => $updatedReview->id,
                    'rating' => $updatedReview->rating,
                    'comment' => $updatedReview->comment,
                    'status' => $updatedReview->status,
                    'created_at' => $updatedReview->created_at,
                    'updated_at' => $updatedReview->updated_at
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating review', [
                'error' => $e->getMessage(),
                'review_id' => $reviewId
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update review. Please try again.'
            ], 500);
        }
    }
}
