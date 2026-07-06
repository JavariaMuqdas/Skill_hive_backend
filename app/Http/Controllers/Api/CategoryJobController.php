<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CategoryJobController extends Controller
{
    public function index(Request $request)
    {
        try {
            $category = trim($request->query('category', ''));
            $keyword = trim($request->query('keyword', ''));
            $city = trim($request->query('city', ''));
            $jobType = trim($request->query('job_type', ''));

            $query = DB::table('projects')
                ->select(
                    'id',
                    'company_id',
                    'title',
                    'category',
                    'city',
                    'job_type',
                    'required_skills',
                    'salary_min',
                    'salary_max',
                    'deadline',
                    'description',
                    'attachment_path',
                    'status'
                )
                ->whereNotNull('category')
                ->where('category', '!=', '');

            if ($category !== '') {
                $query->whereRaw('LOWER(TRIM(category)) = ?', [strtolower($category)]);
            }

            if ($keyword !== '') {
                $query->where(function ($q) use ($keyword) {
                    $q->where('title', 'LIKE', "%{$keyword}%")
                        ->orWhere('category', 'LIKE', "%{$keyword}%")
                        ->orWhere('required_skills', 'LIKE', "%{$keyword}%")
                        ->orWhere('description', 'LIKE', "%{$keyword}%");
                });
            }

            if ($city !== '') {
                $query->where('city', 'LIKE', "%{$city}%");
            }

            if ($jobType !== '') {
                $query->whereRaw('LOWER(TRIM(job_type)) = ?', [strtolower($jobType)]);
            }

            $jobs = $query->orderByDesc('id')->get();

            return response()->json([
                'success' => true,
                'category' => $category,
                'count' => $jobs->count(),
                'data' => $jobs
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Server error',
                'error' => $e->getMessage(),
                'data' => []
            ], 500);
        }
    }
    public function recentJobs()
    {
        try {
            $uniqueJobIds = DB::table('projects')
                ->selectRaw('MIN(id) as id')
                ->whereNotNull('title')
                ->groupBy('title', 'category', 'city', 'job_type')
                ->orderByRaw('MIN(id) ASC')
                ->pluck('id')
                ->take(9);

            $jobs = DB::table('projects')
                ->whereIn('id', $uniqueJobIds)
                ->orderBy('id', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $jobs
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    //     public function jobsList(Request $request)
    // {
    //     $perPage = 10;

    //     $uniqueJobIds = DB::table('projects')
    //         ->selectRaw('MIN(id) as id')
    //         ->whereNotNull('title')
    //         ->groupBy('title', 'category', 'city', 'job_type', 'company_id');

    //     $query = DB::table('projects')
    //         ->whereIn('id', $uniqueJobIds);

    //     if ($request->filled('keyword')) {
    //         $keyword = $request->keyword;

    //         $query->where(function ($q) use ($keyword) {
    //             $q->where('title', 'LIKE', "%{$keyword}%")
    //               ->orWhere('category', 'LIKE', "%{$keyword}%")
    //               ->orWhere('city', 'LIKE', "%{$keyword}%")
    //               ->orWhere('description', 'LIKE', "%{$keyword}%")
    //               ->orWhere('required_skills', 'LIKE', "%{$keyword}%");
    //         });
    //     }

    //     if ($request->filled('location')) {
    //         $query->where('city', 'LIKE', "%{$request->location}%");
    //     }

    //     if ($request->filled('category')) {
    //         $query->where('category', $request->category);
    //     }

    //     if ($request->filled('job_type')) {
    //         $query->where('job_type', $request->job_type);
    //     }

    //     if ($request->filled('company_id')) {
    //         $query->where('company_id', $request->company_id)
    //               ->where('status', 'publish');
    //     }

    //     $jobs = $query
    //         ->orderBy('id', 'asc')
    //         ->paginate($perPage);

    //     return response()->json([
    //         'success' => true,
    //         'data' => $jobs
    //     ]);
    // }
    public function jobsList(Request $request)
    {
        try {
            $perPage = 10;

            $query = DB::table('projects')
                ->whereNotNull('title');

            if ($request->filled('company_id')) {
                $query->where('company_id', $request->company_id)
                    ->whereIn('status', ['publish', 'published', 'active']);
            }

            if ($request->filled('keyword')) {
                $keyword = $request->keyword;

                $query->where(function ($q) use ($keyword) {
                    $q->where('title', 'LIKE', "%{$keyword}%")
                        ->orWhere('category', 'LIKE', "%{$keyword}%")
                        ->orWhere('city', 'LIKE', "%{$keyword}%")
                        ->orWhere('description', 'LIKE', "%{$keyword}%")
                        ->orWhere('required_skills', 'LIKE', "%{$keyword}%");
                });
            }

            if ($request->filled('location')) {
                $query->where('city', 'LIKE', "%{$request->location}%");
            }

            if ($request->filled('category')) {
                $query->where('category', $request->category);
            }

            if ($request->filled('job_type')) {
                $query->where('job_type', $request->job_type);
            }

            $jobs = $query
                ->orderBy('id', 'asc')
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $jobs
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function platformStats()
    {
        try {
            $studentsCount = DB::table('users')
                ->where('role', 'student')
                ->count();

            $companiesCount = DB::table('companies')
                ->count();

            $jobsCount = DB::table('projects')
                ->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'active_users' => max($studentsCount - 2, 0),
                    'companies' => max($companiesCount - 2, 0),
                    'jobs_posted' => max($jobsCount - 2, 0),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    // public function topCompanies()
    // {
    //     try {
    //         $companies = DB::table('companies')
    //             ->select(
    //                 'companies.id',
    //                 'companies.company_name',
    //                 'companies.city',
    //                 'companies.country',
    //                 'companies.industry',
    //                 'companies.company_email',
    //                 DB::raw("COUNT(projects.id) as open_jobs")
    //             )
    //             ->leftJoin('projects', function ($join) {
    //                 $join->on('companies.id', '=', 'projects.company_id')
    //                     ->where('projects.status', '=', 'published');
    //             })
    //             ->groupBy(
    //                 'companies.id',
    //                 'companies.company_name',
    //                 'companies.city',
    //                 'companies.country',
    //                 'companies.industry',
    //                 'companies.company_email'
    //             )
    //             ->orderBy('companies.id', 'asc')
    //             ->limit(4)
    //             ->get();

    //         return response()->json([
    //             'success' => true,
    //             'data' => $companies
    //         ]);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => $e->getMessage()
    //         ], 500);
    //     }
    // }
    public function topCompanies()
    {
        try {
            $companies = DB::table('companies')
                ->select(
                    'companies.id',
                    'companies.company_name',
                    'companies.city',
                    'companies.country',
                    'companies.logo',
                    DB::raw("COUNT(projects.id) as open_jobs")
                )
                ->leftJoin('projects', function ($join) {
                    $join->on('companies.id', '=', 'projects.company_id')
                        ->where('projects.status', '=', 'published');
                })
                ->groupBy(
                    'companies.id',
                    'companies.company_name',
                    'companies.city',
                    'companies.country',
                    'companies.logo'
                )
                ->orderBy('companies.id', 'asc')
                ->limit(4)
                ->get()
                ->map(function ($company) {
                    $company->logo_url = !empty($company->logo)
                        ? asset('storage/' . $company->logo)
                        : null;

                    return $company;
                });

            return response()->json([
                'success' => true,
                'data' => $companies
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }


    public function companyDetail(int $id)
    {
        try {
            $company = DB::table('companies')
                ->where('id', $id)
                ->first();

            if (!$company) {
                return response()->json([
                    'success' => false,
                    'message' => 'Company not found'
                ], 404);
            }

            $openJobs = DB::table('projects')
                ->where('company_id', $id)
                ->where('status', 'published')
                ->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'company' => $company,
                    'open_jobs' => $openJobs
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function categories()
{
    try {
        $categories = DB::table('projects')
            ->select('category')
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->orderBy('category', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $categories
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
            'data' => []
        ], 500);
    }
}
}
