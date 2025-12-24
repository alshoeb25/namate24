<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    protected $fillable = [
        'user_id',
        'grade_level',
        'learning_goals',
        'preferred_subjects',
        'budget_range',
        'phone',
        'country_code',
        'city',
        'area',
        'address',
        'lat',
        'lng',
    ];

    protected $casts = [
        'preferred_subjects' => 'array',
        'lat' => 'float',
        'lng' => 'float',
    ];

    /**
     * Get the user that owns the student profile
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the requirements for the student
     */
    public function requirements(): HasMany
    {
        return $this->hasMany(StudentRequirement::class, 'student_id', 'user_id');
    }

    /**
     * Get the bookings for the student
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'student_id', 'user_id');
    }
}
