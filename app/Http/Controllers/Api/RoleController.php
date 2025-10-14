<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class RoleController extends Controller
{
        public function index()
    {
        // Cache the roles for 60 minutes.
        $roles = Cache::remember('all_roles', 60 * 60, function () {
            return DB::table('roles')->select('id', 'name')->get();
        });

        return response()->json($roles);
    }
}
