<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModuleTopic extends Model
{
    use HasFactory;

    protected $table = 'module_topics';

    protected $fillable = [
        'module_id',
        'title',
        'description',
        'code',
        'order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
    ];

    /**
     * Relationship: Topic belongs to Module
     */
    public function module(): BelongsTo
    {
        return $this->belongsTo(SubjectModule::class, 'module_id');
    }

    /**
     * Get active topics
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
