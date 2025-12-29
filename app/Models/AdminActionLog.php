<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminActionLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id',
        'action_type',
        'subject_type',
        'subject_id',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];
}
