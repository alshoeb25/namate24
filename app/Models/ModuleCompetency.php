<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModuleCompetency extends Model
{
    use HasFactory;

    protected $table = 'module_competencies';

    protected $fillable = [
        'module_id',
        'name',
        'description',
        'competency_type',
        'order',
    ];

    protected $casts = [
        'order' => 'integer',
    ];

    /**
     * Relationship: Competency belongs to Module
     */
    public function module(): BelongsTo
    {
        return $this->belongsTo(SubjectModule::class, 'module_id');
    }
}
