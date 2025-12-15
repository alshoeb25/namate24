<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubjectModule extends Model
{
    use HasFactory;

    protected $table = 'subject_modules';

    protected $fillable = [
        'subject_id',
        'name',
        'description',
        'code',
        'difficulty_level',
        'estimated_hours',
        'order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'estimated_hours' => 'integer',
        'order' => 'integer',
    ];

    /**
     * Relationship: Module belongs to Subject
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Relationship: Module has many topics
     */
    public function topics(): HasMany
    {
        return $this->hasMany(ModuleTopic::class, 'module_id')->orderBy('order');
    }

    /**
     * Relationship: Module has many competencies
     */
    public function competencies(): HasMany
    {
        return $this->hasMany(ModuleCompetency::class, 'module_id')->orderBy('order');
    }

    /**
     * Get active modules
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get modules by difficulty level
     */
    public function scopeByDifficulty($query, $level)
    {
        return $query->where('difficulty_level', $level);
    }
}
