<?php

namespace App\Http\Controllers;

use App\Models\Company;

class CompanyController extends Controller
{
    // GET all companies (for student dashboard)
    public function index()
    {
        $companies = Company::with('user')->latest()->get();

        return response()->json([
            "companies" => $companies
        ]);
    }
public function getCompanyJobs(int $id)
{
    // 1. Debugging: check karein ke kya query sahi chal rahi hai
    $jobs = \App\Models\Project::where('company_id', $id)->get();
    
    // 2. Agar $jobs empty hai, to check karein ke kya database mein us company_id ke jobs hain
    if ($jobs->isEmpty()) {
        return response()->json(['message' => 'No jobs found', 'debug_id' => $id], 200);
    }

    return response()->json($jobs, 200);
}
}
