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
}