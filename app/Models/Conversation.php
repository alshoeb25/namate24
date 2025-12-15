<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    protected $fillable = ['related_requirement_id','last_message_at'];

    protected $casts = ['last_message_at' => 'datetime'];
}