<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Level extends Model
{
    protected $fillable = ['name', 'group_name', 'value', 'order'];

    public $timestamps = false;

    /**
     * Get levels grouped by group_name
     */
    public static function getGroupedLevels()
    {
        return self::orderBy('order')->get()->groupBy('group_name');
    }
}
