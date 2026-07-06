<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\Job;

class CompanyJobsController extends Controller
{
    public function index(Request $request)
    {
        $company = Company::where('user_id', $request->user()->id)->firstOrFail();
        return response()->json($company->jobs()->latest()->get());
    }

    public function store(Request $request)
    {
        $company = Company::where('user_id', $request->user()->id)->firstOrFail();

        // 🚫 Check if the company is blocked
        if ($company->status === 'blocked') {
            return response()->json([
                'message' => 'Action restricted. Your company account has been blocked by the administrator.'
            ], 403);
        }

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'job_type' => 'required|in:Full-time,Part-time,Internship,Remote',
            'salary' => 'nullable|string|max:255',
            'posted_date' => 'nullable|date',
            'is_active' => 'nullable|boolean',
        ]);

        $job = $company->jobs()->create($data);
        return response()->json($job, 201);
    }

    public function update(Request $request, Job $job)
    {
        $company = Company::where('user_id', $request->user()->id)->firstOrFail();
        
        if ($job->company_id !== $company->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        // 🚫 Check if the company is blocked
        if ($company->status === 'blocked') {
            return response()->json([
                'message' => 'Action restricted. Your company account has been blocked by the administrator.'
            ], 403);
        }

        $data = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'location' => 'nullable|string|max:255',
            'job_type' => 'sometimes|required|in:Full-time,Part-time,Internship,Remote',
            'salary' => 'nullable|string|max:255',
            'posted_date' => 'nullable|date',
            'is_active' => 'nullable|boolean',
        ]);

        $job->update($data);
        return response()->json($job);
    }

    public function destroy(Request $request, Job $job)
    {
        $company = Company::where('user_id', $request->user()->id)->firstOrFail();
        if ($job->company_id !== $company->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $job->delete();
        return response()->json(['message' => 'Deleted']);
    }
}