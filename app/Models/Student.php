<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;

class Student extends Model
{
    use Notifiable;
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
        'is_disabled',
        'disabled_reason',
        'disabled_by',
        'disabled_at',
    ];

    protected $casts = [
        'preferred_subjects' => 'array',
        'lat' => 'float',
        'lng' => 'float',
        'is_disabled' => 'boolean',
        'disabled_at' => 'datetime',
    ];

    /**
     * Get the user that owns the student profile
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function disabledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'disabled_by');
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
