<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CmsPage extends Model
{
    protected $fillable = ['slug','title','content','is_visible'];
    protected $casts = ['is_visible' => 'boolean'];
}