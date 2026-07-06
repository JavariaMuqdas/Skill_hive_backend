<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Project;
use App\Models\Application; 
use App\Models\Student;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;

class StudentDashboardController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            // 1. Overview Counts
            $totalCompanies = Company::count();
            $totalJobs = Project::count();
            
            $userId = Auth::id(); 
            $student = Student::where('user_id', $userId)->first();
            $totalAppliedJobs = $student ? Application::where('student_id', $student->id)->count() : 0;

            // Helper function to avoid repeating the map array structure
            $formatProject = function($project) {
    return [
        'id' => $project->id,
        'title' => $project->title,
        'location' => $project->city ?? $project->location ?? 'Not specified',
        'category' => $project->category,
        'company_name' => $project->company?->company_name ?? 'Unknown Company',
    ];
};

            // 2. CURRENT JOBS: Project status = 'published' AND Application status = 'in review'
            $currentJobs = Project::with(['company:id,company_name'])
                ->where('status', 'published')
                ->whereHas('applications', function($query) {
                    $query->where('status', 'in_review');
                })
                ->get()
                ->map($formatProject);

            // 3. RUNNING JOBS: Project status = 'published' AND Application status = 'accepted'
            $runningJobs = Project::with(['company:id,company_name'])
                ->where('status', 'published')
                ->whereHas('applications', function($query) {
                    $query->where('status', 'accepted');
                })
                ->get()
                ->map($formatProject);

            // 4. CLOSED JOBS: Project status = 'closed' AND Application status = 'accepted'
            $closedJobs = Project::with(['company:id,company_name'])
                ->where('status', 'closed')
                ->whereHas('applications', function($query) {
                    $query->where('status', 'accepted');
                })
                ->get()
                ->map($formatProject);

            // Final Combined Response for Student Dashboard
            return response()->json([
                'totalCompanies' => $totalCompanies,
                'totalJobs' => $totalJobs,
                'totalAppliedJobs' => $totalAppliedJobs,
                'currentJobs' => $currentJobs,
                'runningJobs' => $runningJobs,
                'closedJobs' => $closedJobs
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error_message' => 'Error: ' . $e->getMessage(),
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