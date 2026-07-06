<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Project;
use App\Models\Application;
use Illuminate\Http\JsonResponse;

class AdminDashboardController extends Controller
{
    public function index(): JsonResponse
    {
        try {

            $totalCompanies = Company::count();
            $totalJobs = Project::count();
            $totalAppliedJobs = Application::count();

            $formatProject = function ($project) {
                return [
                    'id' => $project->id,
                    'title' => $project->title,
                    'location' => $project->location,
                    'category' => $project->category,
                    'company_name' => $project->company
                        ? $project->company->name
                        : 'N/A',
                ];
            };

            // Current Jobs
            $currentJobs = Project::with('company')
                ->where('status', 'published')
                ->whereHas('applications', function ($query) {
                    $query->where('status', 'in_review');
                })
                ->latest()
                ->get()
                ->map($formatProject);

            // Running Jobs
            $runningJobs = Project::with('company')
                ->where('status', 'published')
                ->whereHas('applications', function ($query) {
                    $query->where('status', 'accepted');
                })
                ->latest()
                ->get()
                ->map($formatProject);

            // Closed Jobs
            $closedJobs = Project::with('company')
                ->where('status', 'closed')
                ->whereHas('applications', function ($query) {
                    $query->where('status', 'accepted');
                })
                ->latest()
                ->get()
                ->map($formatProject);

            return response()->json([
                'totalCompanies' => $totalCompanies,
                'totalJobs' => $totalJobs,
                'totalAppliedJobs' => $totalAppliedJobs,
                'currentJobs' => $currentJobs,
                'runningJobs' => $runningJobs,
                'closedJobs' => $closedJobs,
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'message' => $e->getMessage(),
                'totalCompanies' => 0,
                'totalJobs' => 0,
                'totalAppliedJobs' => 0,
                'currentJobs' => [],
                'runningJobs' => [],
                'closedJobs' => []
            ], 500);
        }
    }
}