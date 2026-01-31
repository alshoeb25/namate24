<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudentRequirement extends Model
{
    protected $fillable = [
        'student_id','subject_id','budget_min','budget_max','mode',
        'details','city','area','pincode','lat','lng','desired_start','visible',
        // New fields for 3-section form
        'phone','country_code','alternate_phone','student_name','location','class','level','service_type',
        'meeting_options','travel_distance','budget','budget_type','gender_preference',
        'availability','languages','tutor_location_preference','other_subject','status',
        // Lead/coin fields
        'post_fee','unlock_price','max_leads','current_leads','lead_status','posted_at',
        // Teachers view tracking
        'teachers_viewed_at','teachers_view_coins'
    ];

    protected $casts = [
        'visible' => 'boolean',
        'meeting_options' => 'array',
        'languages' => 'array',
        'posted_at' => 'datetime',
        'hired_at' => 'datetime',
        'teachers_viewed_at' => 'datetime',
    ];

    protected $appends = ['subject_name', 'subject_names'];

    /**
     * Get the subject name from the subject relationship
     */
    public function getSubjectNameAttribute()
    {
        // Check if already set as attribute (from LabelService)
        if (isset($this->attributes['subject_name'])) {
            return $this->attributes['subject_name'];
        }
        
        // Try to get from single subject relationship
        if ($this->relationLoaded('subject') && $this->subject) {
            return $this->subject->name;
        }
        
        // Fallback: try to get from subjects relationship (first one)
        if ($this->relationLoaded('subjects') && $this->subjects && $this->subjects->isNotEmpty()) {
            return $this->subjects->first()->name;
        }
        
        // Last resort: check if other_subject field has data
        if (!empty($this->other_subject)) {
            return $this->other_subject;
        }
        
        return null;
    }

    /**
     * Get subject names from subjects relationship
     */
    public function getSubjectNamesAttribute()
    {
        // Check if already set as attribute (from LabelService)
        if (isset($this->attributes['subject_names'])) {
            return $this->attributes['subject_names'];
        }
        
        // Otherwise get from relationship
        if ($this->relationLoaded('subjects') && $this->subjects) {
            return $this->subjects->pluck('name')->toArray();
        }
        
        return [];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Subjects relationship via pivot table
     */
    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'student_post_subjects', 'student_requirement_id', 'subject_id');
    }

    public function unlocks(): HasMany
    {
        return $this->hasMany(EnquiryUnlock::class, 'enquiry_id');
    }

    public function unlockedBy(): BelongsToMany
    {
        return $this->unlockBy();
    }

    /**
     * Tutors who unlocked this requirement (via enquiry_unlocks.teacher_id -> tutors.id)
     */
    public function unlockBy(): BelongsToMany
    {
        return $this->belongsToMany(Tutor::class, 'enquiry_unlocks', 'enquiry_id', 'tutor_id')
            ->withTimestamps()
            ->withPivot(['unlock_price']);
    }

    /**
     * Tutors approached for this requirement (via student_requirement_approached_tutors table)
     */
    public function approachedTutors(): BelongsToMany
    {
        return $this->belongsToMany(
            Tutor::class,
            'student_requirement_approached_tutors',
            'student_requirement_id',
            'tutor_id'
        )
        ->withPivot(['coins_spent', 'created_at'])
        ->withTimestamps()
        ->orderByDesc('student_requirement_approached_tutors.created_at');
    }

    /**
     * Convert model to Elasticsearch-ready array for job/requirement searches
     */
    public function toElasticArray(): array
    {
        $this->loadMissing('student', 'student.user', 'subject', 'subjects');

        $lat = is_numeric($this->lat) ? (float) $this->lat : null;
        $lng = is_numeric($this->lng) ? (float) $this->lng : null;

        $subjects = $this->subjects ? $this->subjects->pluck('name')->toArray() : [];
        $subjectIds = $this->subjects ? $this->subjects->pluck('id')->toArray() : [];

        // Use LabelService for all labels from database
        $labelService = app(\App\Services\LabelService::class);

        $payload = [
            'id' => $this->id,
            'student_id' => $this->student_id,
            'student_name' => $this->student?->name ?? $this->student_name ?? null,
            'subject_id' => $this->subject_id,
            'subject_ids' => $subjectIds,
            'subjects' => $subjects,
            'subject_name' => $this->subject?->name ?? null,
            'budget_min' => (float) ($this->budget_min ?? 0),
            'budget_max' => (float) ($this->budget_max ?? 0),
            'budget' => $this->budget,
            'budget_type' => $this->budget_type,
            'budget_type_label' => FieldLabel::getLabel('budget_type', $this->budget_type) ?? $this->budget_type,
            'mode' => $this->mode,
            'service_type' => $this->service_type,
            'service_type_label' => FieldLabel::getLabel('service_type', $this->service_type) ?? $this->service_type,
            'city' => $this->city,
            'state' => $this->state ?? null,
            'area' => $this->area,
            'location' => $this->location,
            'location_display' => $this->location ?: ($this->city . ($this->area ? ', ' . $this->area : '')),
            'pincode' => $this->pincode,
            'details' => $this->details,
            'class' => $this->class,
            'gender_preference' => $this->gender_preference,
            'gender_preference_label' => FieldLabel::getLabel('gender_preference', $this->gender_preference) ?? $this->gender_preference,
            'level' => $this->level,
            'availability' => $this->availability,
            'availability_label' => FieldLabel::getLabel('availability', $this->availability) ?? $this->availability,
            'languages' => $this->languages ?? [],
            'meeting_options' => $this->meeting_options ?? [],
            'meeting_options_labels' => is_array($this->meeting_options) 
                ? array_map(fn($opt) => FieldLabel::getLabel('meeting_options', $opt) ?? $opt, $this->meeting_options)
                : [],
            'tutor_location_preference' => $this->tutor_location_preference,
            'travel_distance' => $this->travel_distance,
            'visible' => $this->visible ?? null,
            'status' => $this->status,
            'lead_status' => $this->lead_status,
            'current_leads' => $this->current_leads ?? 0,
            'max_leads' => $this->max_leads ?? 0,
            'spots_available' => ($this->max_leads ?? 0) - ($this->current_leads ?? 0),
            'posted_at' => $this->posted_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'student_is_disabled' => (bool) ($this->student?->is_disabled ?? false),
            'student_user_is_disabled' => (bool) ($this->student?->user?->is_disabled ?? false),
        ];

        // Add budget display
        if ($this->budget && $payload['budget_type_label']) {
            $payload['budget_display'] = '₹' . number_format($this->budget, 0) . ' ' . $payload['budget_type_label'];
        }

        if (!is_null($lat) && !is_null($lng)) {
            $payload['location_geo'] = ['lat' => $lat, 'lon' => $lng];
            $payload['lat'] = $lat;
            $payload['lng'] = $lng;
        }

        return $payload;
    }

    // End of class
}