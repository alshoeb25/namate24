<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_type',
        'first_name',
        'last_name',
        'email',
        'mobile',
        'organization_name',
        'contact_person',
        'message',
        'ip_address',
        'user_agent',
    ];
}
