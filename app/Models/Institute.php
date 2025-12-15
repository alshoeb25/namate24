<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Institute extends Model
{
    protected $fillable = [
        'ugc_code',
        'name',
        'type',
        'state',
        'city',
        'region',
        'website',
        'email',
        'contact',
        'is_ugc_approved',
        'status'
    ];
}

