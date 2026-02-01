<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Tutor extends Model
{
    
    protected $fillable = [
        'user_id','headline','about','description','do_not_share_contact','experience_years','price_per_hour',
        'teaching_mode','city','area','phone','whatsapp_number','country_code','lat','lng','verified','rating_avg','rating_count',
        'gender','badges','moderation_status','is_disabled','disabled_reason','disabled_by','disabled_at','address','state','country','postal_code',
        'introductory_video','video_title','youtube_intro_url','video_approval_status','video_rejection_reason','teaching_methodology','educations','experiences',
        'speciality','strength','current_role',
        'courses','availability','settings',
        'charge_type','min_fee','max_fee','fee_notes',
        'experience_total_years','experience_teaching_years','experience_online_years',
        'travel_willing','travel_distance_km','online_available','has_digital_pen','helps_homework','employed_full_time',
        'opportunities','languages'
    ];

    protected $appends = ['photo_url'];

    protected $casts = [
        'verified' => 'boolean',
        'badges' => 'array',
        'lat' => 'float',
        'lng' => 'float',
        'rating_avg' => 'float',
        'teaching_mode' => 'array',
        'educations' => 'array',
        'experiences' => 'array',
        'courses' => 'array',
        'settings' => 'array',
        'opportunities' => 'array',
        'languages' => 'array',
        'travel_willing' => 'boolean',
        'online_available' => 'boolean',
        'has_digital_pen' => 'boolean',
        'helps_homework' => 'boolean',
        'employed_full_time' => 'boolean',
        'is_disabled' => 'boolean',
        'do_not_share_contact' => 'boolean',
        'disabled_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'tutor_subject')
            ->withPivot('from_level_id', 'to_level_id')
            ->withTimestamps();
    }

    public function documents(): HasMany
    {
        return $this->hasMany(TutorDocument::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'tutor_id');
    }

    public function approvedReviews(): HasMany
    {
        return $this->hasMany(Review::class, 'tutor_id')
            ->where('moderation_status', 'approved')
            ->orderByDesc('created_at');
    }

    public function moderationActions(): HasMany
    {
        return $this->hasMany(TutorModerationAction::class)
            ->orderByDesc('created_at');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function disabledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'disabled_by');
    }

    public function getPhotoUrlAttribute()
    {
        if ($this->photo) {
            return asset('storage/' . $this->photo);
        }
        
        // Default photo - use user's name for personalized placeholder
        $userName = $this->user->name ?? 'Tutor';
        return 'https://ui-avatars.com/api/?name=' . urlencode($userName) . '&size=400&background=9333ea&color=ffffff';
    }

    /**
     * Calculate and update rating_avg and rating_count based on approved reviews
     */
    public function updateRating(): void
    {
        $approvedReviews = $this->reviews()
            ->where('moderation_status', 'approved')
            ->get();

        if ($approvedReviews->isEmpty()) {
            $this->update([
                'rating_avg' => null,
                'rating_count' => 0,
            ]);
            return;
        }

        $avg = $approvedReviews->avg('rating');
        $count = $approvedReviews->count();

        $this->update([
            'rating_avg' => round($avg, 2),
            'rating_count' => $count,
        ]);
    }

    /**
     * Convert model to Elasticsearch-ready array.
     * (Equivalent of toSearchableArray but NOT using Scout)
     */
    public function toElasticArray(): array
    {
        $this->loadMissing('user','subjects');

        $lat = is_numeric($this->lat) ? (float) $this->lat : null;
        $lng = is_numeric($this->lng) ? (float) $this->lng : null;

        $payload = [
            'id' => $this->id,
            'name' => $this->user->name ?? null,
            'headline' => $this->headline,
            'subject_ids' => $this->subjects->pluck('id')->toArray(),
            'subjects' => $this->subjects->pluck('name')->toArray(),
            'price_per_hour' => (float) $this->price_per_hour,
            'teaching_mode' => $this->teaching_mode,
            'city' => $this->city,
            'state' => $this->state,
            'area' => $this->area,
            'address' => $this->address,
            'country' => $this->country,
            'postal_code' => $this->postal_code,
            'experience_years' => $this->experience_years,
            'experience_total_years' => $this->experience_total_years,
            'rating_avg' => $this->rating_avg,
            'rating_count' => $this->rating_count,
            'verified' => $this->verified,
            'gender' => $this->gender,
            'badges' => $this->badges ?? [],
            'online_available' => $this->online_available,
            'travel_willing' => $this->travel_willing,
            'travel_distance_km' => $this->travel_distance_km,
            'moderation_status' => $this->moderation_status,
            'is_disabled' => (bool) $this->is_disabled,
            'user_is_disabled' => (bool) ($this->user?->is_disabled ?? false),
        ];

        // Only set geo fields when both coordinates are valid numbers
        if (!is_null($lat) && !is_null($lng)) {
            $payload['location'] = ['lat' => $lat, 'lon' => $lng];
            $payload['lat'] = $lat;
            $payload['lng'] = $lng;
        }

        return $payload;
    }
}