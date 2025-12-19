<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class StudentRequirement extends Model
{
    protected $fillable = [
        'student_id','subject_id','budget_min','budget_max','mode',
        'details','city','area','pincode','lat','lng','desired_start','visible',
        // New fields for 3-section form
        'phone','alternate_phone','student_name','location','level','service_type',
        'meeting_options','travel_distance','budget','budget_type','gender_preference',
        'availability','languages','tutor_location_preference','other_subject','status'
    ];

    protected $casts = [
        'visible' => 'boolean',
        'meeting_options' => 'array',
        'languages' => 'array',
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
}