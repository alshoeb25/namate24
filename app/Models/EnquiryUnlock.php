<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EnquiryUnlock extends Model
{
    protected $fillable = [
        'enquiry_id',
        'tutor_id',
        'unlock_price',
    ];

    public function enquiry(): BelongsTo
    {
        return $this->belongsTo(StudentRequirement::class, 'enquiry_id');
    }

    public function tutor(): BelongsTo
    {
        return $this->belongsTo(Tutor::class, 'tutor_id');
    }

    // Backward-compatible alias
    public function teacher(): BelongsTo
    {
        return $this->tutor();
    }
}
