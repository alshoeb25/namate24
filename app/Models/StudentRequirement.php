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
        'phone','country_code','alternate_phone','student_name','location','level','service_type',
        'meeting_options','travel_distance','budget','budget_type','gender_preference',
        'availability','languages','tutor_location_preference','other_subject','status',
        // Lead/coin fields
        'post_fee','unlock_price','max_leads','current_leads','lead_status','posted_at'
    ];

    protected $casts = [
        'visible' => 'boolean',
        'meeting_options' => 'array',
        'languages' => 'array',
        'posted_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class,'student_id');
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
        return $this->belongsToMany(User::class, 'enquiry_unlocks', 'enquiry_id', 'teacher_id')
            ->withTimestamps()
            ->withPivot(['unlock_price']);
    }

    /**
     * Convert model to Elasticsearch-ready array for job/requirement searches
     */
    public function toElasticArray(): array
    {
        $this->loadMissing('student', 'subject', 'subjects');

        $lat = is_numeric($this->lat) ? (float) $this->lat : null;
        $lng = is_numeric($this->lng) ? (float) $this->lng : null;

        $subjects = $this->subjects ? $this->subjects->pluck('name')->toArray() : [];

        // Use LabelService for all labels from database
        $labelService = app(\App\Services\LabelService::class);

        $payload = [
            'id' => $this->id,
            'student_id' => $this->student_id,
            'student_name' => $this->student?->name ?? $this->student_name ?? null,
            'subject_id' => $this->subject_id,
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