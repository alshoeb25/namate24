<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class HomeCacheController extends Controller
{
    /**
     * Clear cached data used on Home page
     */
    public function clear(Request $request)
    {
        Cache::forget('home.trending_subjects');
        Cache::forget('home.latest_tutors.6');
        Cache::forget('home.latest_tutors.9');
        Cache::forget('home.latest_tutors.12');

        return response()->json(['cleared' => true]);
    }
}
