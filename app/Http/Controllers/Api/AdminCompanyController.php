<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company; // 👈 Ensure karein Company model imported ho
use App\Models\User; // 👈 Ensure karein Company model imported ho
use App\Models\Project; // 👈 Ensure karein Company model imported ho
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminCompanyController extends Controller
{
    // for companies information
    public function index(): JsonResponse
    {
        try {
            // Pehle yahan User::where('role', 'company')->get() chal rha tha, usay is line se badlein:
            $companies = Company::orderBy('created_at', 'desc')->get();

            return response()->json($companies, 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch companies data',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    // for users information
    public function usersIndex(): JsonResponse
    {
        try {
            // Database se saare users unki registration date (latest first) ke mutabik fetch karega
            $users = User::orderBy('created_at', 'desc')->get();

            return response()->json($users, 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch users data',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    // for block company
    // AdminCompanyController.php

    public function blockCompany(Request $request, int $id)
    {
        // Validation
        $request->validate([
            'reason' => 'required|string',
            'note' => 'nullable|string'
        ]);

        $company = Company::findOrFail($id);

        // Database updates
        $company->status = 'blocked';

        // Reason aur additional note ko mila kar save karna
        if ($request->note) {
            $company->block_reason = $request->reason . " (" . $request->note . ")";
        } else {
            $company->block_reason = $request->reason;
        }

        $company->save(); // Database mein save ho gaya

        return response()->json([
            'success' => true,
            'message' => 'Company has been blocked in the database.',
            'company' => $company
        ]);
    }
    // public function block(Request $request, $id)
    // {
    //     $company = Company::findOrFail($id);

    //     $company->status = 'blocked';
    //     $company->block_reason = $request->reason;
    //     $company->block_note = $request->note;
    //     $company->save();

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Company blocked successfully.'
    //     ]);
    // }


    public function jobsIndex(Request $request): JsonResponse
    {
        try {
            // 1. Frontend se bheja gaya 'status' query parameter catch karein (default 'all' hoga)
            $status = $request->query('status', 'all');

            // 2. Base query build karein relationship ke sath
            $query = Project::with('company');

            // 3. Agar filter 'all' ke ilawa kuch aur hai, toh database level par status check karein
            if ($status !== 'all') {
                $query->where('status', $status);
            }

            // 4. Filtered data ko latest sorting ke sath fetch karein
            $jobs = $query->orderBy('created_at', 'desc')->get();

            // 5. Data ko frontend ke mutabik structure karein
            $formattedJobs = $jobs->map(function ($job) {
                return [
                    'id' => $job->id,
                    'title' => $job->title,
                    'category' => $job->category,
                    'location' => $job->city, // Database column name use karein
                    'job_type' => $job->job_type,
                    'salary_type' => $job->salary_type,
                    'salary_min' => $job->salary_min,     // Ye add karein
                    'salary_max' => $job->salary_max,
                    'hourly_rate' => $job->hourly_rate,   // Ye add karein
                    'deadline' => $job->deadline,         // Ye add karein
                    'description' => $job->description,   // Ye add karein
                    'required_skills' => $job->required_skills,
                    'applications' => 0,
                    'status' => $job->status ?? 'Active',
                    'created_at' => $job->created_at ? $job->created_at->toIso8601String() : now()->toIso8601String()
                ];
            });

            return response()->json($formattedJobs, 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch jobs data',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function approveCompany(int $id)
    {
        try {
            $company = Company::findOrFail($id);

            // Status ko approved pr update krna
            $company->status = 'approved';
            $company->save();

            return response()->json([
                'success' => true,
                'message' => 'Company approved successfully!'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong or company not found.'
            ], 500);
        }
    }
    public function destroy(int $id)
    {
        try {
            $company = Company::find($id);

            if (!$company) {
                return response()->json([
                    'success' => false,
                    'message' => 'Company not found.'
                ], 404);
            }

            // Database se actual delete nahi karna, bas status badalna hai
            $company->status = 'deleted';
            $company->save();

            return response()->json([
                'success' => true,
                'message' => 'Company removed from website successfully.'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
