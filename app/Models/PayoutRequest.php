<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayoutRequest extends Model
{
    protected $fillable = ['tutor_id','amount','status','notes'];

    public function tutor()
    {
        return $this->belongsTo(Tutor::class,'tutor_id');
    }
}