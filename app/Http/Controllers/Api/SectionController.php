<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
class SectionController extends Controller
{
    public function index()
    {
        // Cache the sections for 60 minutes.
        $sections = Cache::remember('all_sections', 60 * 60, function () {
            return DB::table('sections')->select('id', 'name')->get();
        });
        
        return response()->json($sections);
    }
}