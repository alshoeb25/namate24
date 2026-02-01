<?php

namespace App\Models;

use App\Models\Student;
use App\Models\Tutor;
use App\Models\User;
use App\Services\ElasticService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TutorReview extends Model
{
    protected $table = 'tutor_reviews';

    protected $fillable = [
        'tutor_id',
        'student_id',
        'rating',
        'comment',
        'status',
    ];

    protected $casts = [
        'rating' => 'integer',
    ];

    public function tutor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tutor_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public static function updateTutorRating(int $tutorId): void
    {
        $stats = DB::table('tutor_reviews')
            ->where('tutor_id', $tutorId)
            ->where('status', 'approved')
            ->selectRaw('AVG(rating) as avg_rating, COUNT(*) as review_count')
            ->first();

        $tutor = Tutor::query()
            ->where('id', $tutorId)
            ->orWhere('user_id', $tutorId)
            ->first();

        if (!$tutor) {
            return;
        }

        $avg = $stats?->avg_rating;
        $count = (int) ($stats?->review_count ?? 0);

        $tutor->update([
            'rating_avg' => $count > 0 ? round((float) $avg, 2) : null,
            'rating_count' => $count,
        ]);

        if ($tutor->moderation_status !== 'approved' || $tutor->is_disabled) {
            return;
        }

        try {
            $tutor->loadMissing('user', 'subjects');
            $elasticService = app(ElasticService::class);
            $client = $elasticService->client();
            $client->index([
                'index' => 'tutors',
                'id' => $tutor->id,
                'body' => $tutor->toElasticArray(),
                'refresh' => true,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to reindex tutor after review update: ' . $e->getMessage(), [
                'tutor_id' => $tutor->id,
            ]);
        }
    }
}
