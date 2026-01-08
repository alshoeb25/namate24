<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = ['booking_id','student_id','tutor_id','rating','comment','moderation_status','related_requirement_id','is_hidden'];

    protected $casts = [
        'is_hidden' => 'boolean',
    ];

    public function tutor()
    {
        return $this->belongsTo(Tutor::class);
    }

    public function student()
    {
        return $this->belongsTo(User::class,'student_id');
    }
}