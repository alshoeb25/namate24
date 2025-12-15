<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CmsPage;
use Illuminate\Http\Request;

class CmsPageController extends Controller
{
    public function show($slug)
    {
        $page = CmsPage::where('slug', $slug)->firstOrFail();
        if (! $page->is_visible) abort(404);
        return response()->json($page);
    }

    public function index() { return response()->json(CmsPage::all()); }
}