<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class JobCategoryController extends Controller
{
    public function index()
    {
        $categories = DB::table('projects')
            ->select('category as title', DB::raw('COUNT(*) as jobs'))
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->groupBy('category')
            ->orderByDesc('jobs')
            ->limit(8)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }
}