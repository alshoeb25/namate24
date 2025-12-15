<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    protected $fillable = [
        'student_id','tutor_id','start_at','end_at','session_price','status','payment_status','razorpay_order_id'
    ];

    protected $casts = ['start_at'=>'datetime','end_at'=>'datetime'];

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class,'student_id');
    }

    public function tutor(): BelongsTo
    {
        return $this->belongsTo(Tutor::class);
    }
}