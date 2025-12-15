<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentRequirement extends Model
{
    protected $fillable = [
        'student_id','subject_id','budget_min','budget_max','mode',
        'details','city','lat','lng','desired_start','visible'
    ];

    protected $casts = ['visible' => 'boolean'];

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class,'student_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }
}