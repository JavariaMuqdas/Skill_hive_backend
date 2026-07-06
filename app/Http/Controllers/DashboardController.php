<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Project;
use App\Models\Application; 
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function studentStats()
    {
        try {
            // 1. Existing Counts (Jo pehle kiye the)
            $totalCompanies = Company::count();
            $totalJobs = Project::count();
            $userId = Auth::id(); 
            $student = \App\Models\Student::where('user_id', $userId)->first();
            $totalAppliedJobs = $student ? Application::where('student_id', $student->id)->count() : 0;

            // 2. CURRENT JOBS: Projects (published) AND Applications (in_review)
            $currentJobs = DB::table('projects')
                ->join('applications', 'projects.id', '=', 'applications.project_id')
                ->select('projects.*', 'applications.status as application_status')
                ->where('projects.status', 'published')
                ->where('applications.status', 'in_review')
                ->limit(4) // Sirf 4 jobs dikhani hain
                ->get();

            // 3. RUNNING JOBS: Projects (published) AND Applications (accepted)
            $runningJobs = DB::table('projects')
                ->join('applications', 'projects.id', '=', 'applications.project_id')
                ->select('projects.*', 'applications.status as application_status')
                ->where('projects.status', 'published')
                ->where('applications.status', 'accepted')
                ->limit(4)
                ->get();

            // 4. CLOSED JOBS: Projects (closed) AND Applications (accepted)
            $closedJobs = DB::table('projects')
                ->join('applications', 'projects.id', '=', 'applications.project_id')
                ->select('projects.*', 'applications.status as application_status')
                ->where('projects.status', 'closed')
                ->where('applications.status', 'accepted')
                ->limit(4)
                ->get();

            // Final Combined Response
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