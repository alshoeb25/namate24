<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;

class Tutor extends Model
{
    
    protected $fillable = [
        'user_id','headline','about','experience_years','price_per_hour',
        'teaching_mode','city','lat','lng','verified','rating_avg','rating_count',
        'gender','badges','moderation_status','address','state','country','postal_code',
        'introductory_video','video_title','youtube_intro_url','teaching_methodology','educations','experiences',
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
     * Convert model to Elasticsearch-ready array.
     * (Equivalent of toSearchableArray but NOT using Scout)
     */
    public function toElasticArray(): array
    {
        $this->loadMissing('user','subjects');

        return [
            'id' => $this->id,
            'name' => $this->user->name ?? null,
            'headline' => $this->headline,
            'subject_ids' => $this->subjects->pluck('id')->toArray(),
            'subjects' => $this->subjects->pluck('name')->toArray(),
            'price_per_hour' => (float) $this->price_per_hour,
            'teaching_mode' => $this->teaching_mode,
            'city' => $this->city,
            'lat' => $this->lat,
            'lng' => $this->lng,
            'experience_years' => $this->experience_years,
            'rating_avg' => $this->rating_avg,
            'verified' => $this->verified,
            'gender' => $this->gender,
            'badges' => $this->badges ?? [],
        ];
    }
}