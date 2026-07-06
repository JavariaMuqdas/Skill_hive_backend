<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Application;
use App\Models\ProjectAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class CompanyProjectController extends Controller
{
    private function companyId(Request $request): int
    {
        $company = $request->user()->company;
        abort_if(!$company, 403, 'Company profile not found for this user.');
        return $company->id;
    }

    private function decodeJsonArray($value): array
    {
        if (is_array($value)) {
            return array_values(array_filter($value, fn($v) => $v !== null && $v !== ''));
        }

        if (!$value) {
            return [];
        }

        $decoded = json_decode($value, true);

        if (is_array($decoded)) {
            return array_values(array_filter($decoded, fn($v) => $v !== null && $v !== ''));
        }

        return [];
    }

    public function index(Request $request)
    {
        $companyId = $this->companyId($request);

        $projects = Project::where('company_id', $companyId)
            ->latest()
            ->get();

        return response()->json(['data' => $projects]);
    }

    public function store(Request $request)
    {
        $companyId = $this->companyId($request);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'category' => ['required', 'string', 'max:120'],
            'description' => ['required', 'string', 'min:10'],

            'hiring_locations' => ['nullable'],
            'job_requirements' => ['nullable'],
            'employment_type' => ['required'],
            'work_mode' => ['required'],
            'working_schedule' => ['required', 'string', 'max:120'],
            'deadline_date' => ['required', 'date', 'after_or_equal:today'],


            'salary_type' => ['required', Rule::in(['hourly', 'custom'])],
            'hourly_rate' => ['nullable', 'numeric', 'min:0'],
            'salary_min' => ['nullable', 'numeric', 'min:0'],
            'salary_max' => ['nullable', 'numeric', 'min:0'],
            'salary_negotiable' => ['nullable'],
            'hiring_multiple_candidates' => ['nullable'],

            'status' => ['required', Rule::in(['draft', 'published', 'closed'])],
            'attachment' => ['nullable', 'file', 'max:5120', 'mimes:pdf,doc,docx,png,jpg,jpeg'],
        ]);

        $hiringLocations = $this->decodeJsonArray($request->input('hiring_locations'));
        $jobRequirements = $this->decodeJsonArray($request->input('job_requirements'));
        $employmentType = $this->decodeJsonArray($request->input('employment_type'));
        $workMode = $this->decodeJsonArray($request->input('work_mode'));

        if (empty($employmentType)) {
            return response()->json([
                'message' => 'Employment type is required.',
                'errors' => ['employment_type' => ['Select at least one employment type.']]
            ], 422);
        }

        if (empty($workMode)) {
            return response()->json([
                'message' => 'Work mode is required.',
                'errors' => ['work_mode' => ['Select at least one work mode.']]
            ], 422);
        }

        if ($validated['salary_type'] === 'hourly' && !$request->filled('hourly_rate')) {
            return response()->json([
                'message' => 'Hourly rate is required.',
                'errors' => ['hourly_rate' => ['Hourly rate is required for hourly salary type.']]
            ], 422);
        }

        if ($validated['salary_type'] === 'custom' && !$request->filled('salary_min') && !$request->filled('salary_max')) {
            return response()->json([
                'message' => 'Salary min or max is required.',
                'errors' => ['salary_min' => ['Enter salary min or salary max.']]
            ], 422);
        }

        if (!is_null($request->salary_min) && !is_null($request->salary_max)) {
            if ((float) $request->salary_max < (float) $request->salary_min) {
                return response()->json([
                    'message' => 'Salary max must be greater than or equal to salary min.',
                    'errors' => ['salary_max' => ['Salary max must be greater than or equal to salary min.']]
                ], 422);
            }
        }

        $path = null;
        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('project_attachments', 'public');
        }

        $project = Project::create([
            'company_id' => $companyId,

            'title' => $validated['title'],
            'category' => $validated['category'],
            'description' => $validated['description'],

            'hiring_locations' => $hiringLocations,
            'job_requirements' => $jobRequirements,
            'employment_type' => $employmentType,
            'work_mode' => $workMode,
            'working_schedule' => $validated['working_schedule'],

            'salary_type' => $validated['salary_type'],
            'hourly_rate' => $request->hourly_rate,
            'salary_min' => $request->salary_min,
            'salary_max' => $request->salary_max,
            'salary_negotiable' => filter_var($request->salary_negotiable, FILTER_VALIDATE_BOOLEAN),
            'hiring_multiple_candidates' => filter_var($request->hiring_multiple_candidates, FILTER_VALIDATE_BOOLEAN),

            'status' => $validated['status'],
            'attachment_path' => $path,

            // backward compatibility
            'city' => $hiringLocations[0] ?? null,
            'job_type' => !empty($workMode) ? strtolower($workMode[0]) : null,
            'required_skills' => $jobRequirements,
        ]);

        return response()->json([
            'message' => 'Created',
            'data' => $project
        ], 201);
    }

    public function show(Request $request, $id)
    {
        $companyId = $this->companyId($request);
        $project = Project::where('company_id', $companyId)->findOrFail($id);

        return response()->json(['data' => $project]);
    }

    public function update(Request $request, $id)
    {
        $companyId = $this->companyId($request);
        $project = Project::where('company_id', $companyId)->findOrFail($id);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'category' => ['required', 'string', 'max:120'],
            'description' => ['required', 'string', 'min:10'],

            'hiring_locations' => ['nullable'],
            'job_requirements' => ['nullable'],
            'employment_type' => ['required'],
            'work_mode' => ['required'],
            'working_schedule' => ['required', 'string', 'max:120'],
            'deadline_date' => ['required', 'date', 'after_or_equal:today'],

            'salary_type' => ['required', Rule::in(['hourly', 'custom'])],
            'hourly_rate' => ['nullable', 'numeric', 'min:0'],
            'salary_min' => ['nullable', 'numeric', 'min:0'],
            'salary_max' => ['nullable', 'numeric', 'min:0'],
            'salary_negotiable' => ['nullable'],
            'hiring_multiple_candidates' => ['nullable'],

            'status' => ['required', Rule::in(['draft', 'published', 'closed'])],
            'attachment' => ['nullable', 'file', 'max:5120', 'mimes:pdf,doc,docx,png,jpg,jpeg'],
        ]);

        $hiringLocations = $this->decodeJsonArray($request->input('hiring_locations'));
        $jobRequirements = $this->decodeJsonArray($request->input('job_requirements'));
        $employmentType = $this->decodeJsonArray($request->input('employment_type'));
        $workMode = $this->decodeJsonArray($request->input('work_mode'));

        if (empty($employmentType)) {
            return response()->json([
                'message' => 'Employment type is required.',
                'errors' => ['employment_type' => ['Select at least one employment type.']]
            ], 422);
        }

        if (empty($workMode)) {
            return response()->json([
                'message' => 'Work mode is required.',
                'errors' => ['work_mode' => ['Select at least one work mode.']]
            ], 422);
        }

        if ($validated['salary_type'] === 'hourly' && !$request->filled('hourly_rate')) {
            return response()->json([
                'message' => 'Hourly rate is required.',
                'errors' => ['hourly_rate' => ['Hourly rate is required for hourly salary type.']]
            ], 422);
        }

        if ($validated['salary_type'] === 'custom' && !$request->filled('salary_min') && !$request->filled('salary_max')) {
            return response()->json([
                'message' => 'Salary min or max is required.',
                'errors' => ['salary_min' => ['Enter salary min or salary max.']]
            ], 422);
        }

        if (!is_null($request->salary_min) && !is_null($request->salary_max)) {
            if ((float) $request->salary_max < (float) $request->salary_min) {
                return response()->json([
                    'message' => 'Salary max must be greater than or equal to salary min.',
                    'errors' => ['salary_max' => ['Salary max must be greater than or equal to salary min.']]
                ], 422);
            }
        }

        if ($request->hasFile('attachment')) {
            if ($project->attachment_path) {
                Storage::disk('public')->delete($project->attachment_path);
            }

            $project->attachment_path = $request->file('attachment')->store('project_attachments', 'public');
        }

        $project->title = $validated['title'];
        $project->category = $validated['category'];
        $project->description = $validated['description'];

        $project->hiring_locations = $hiringLocations;
        $project->job_requirements = $jobRequirements;
        $project->employment_type = $employmentType;
        $project->work_mode = $workMode;
        $project->working_schedule = $validated['working_schedule'];
        $project->deadline_date = $request->deadline_date;

        $project->salary_type = $validated['salary_type'];
        $project->hourly_rate = $request->hourly_rate;
        $project->salary_min = $request->salary_min;
        $project->salary_max = $request->salary_max;
        $project->salary_negotiable = filter_var($request->salary_negotiable, FILTER_VALIDATE_BOOLEAN);
        $project->hiring_multiple_candidates = filter_var($request->hiring_multiple_candidates, FILTER_VALIDATE_BOOLEAN);

        $project->status = $validated['status'];

        // backward compatibility
        $project->city = $hiringLocations[0] ?? null;
        $project->job_type = !empty($workMode) ? strtolower($workMode[0]) : null;
        $project->required_skills = $jobRequirements;

        $project->save();

        return response()->json([
            'message' => 'Updated',
            'data' => $project
        ]);
    }

    public function acceptStudent(Request $request, $applicationId)
    {
        $companyId = $this->companyId($request);

        // 1. Application dhoondein jo is company ki ho
        $application = Application::where('company_id', $companyId)
            ->findOrFail($applicationId);

        // 2. Application status update karein
        $application->update(['status' => 'accepted']);

        // 3. Project Assignment table mein entry check karein ya create karein
        // Is se project_assignments table mein status 'active' ho jayega
        $assignment = ProjectAssignment::firstOrCreate(
            [
                'project_id' => $application->project_id,
                'student_id' => $application->student_id,
            ],
            [
                'company_id' => $companyId,
                'status'     => 'active', // Migration ke mutabiq default bhi active hi hai
            ]
        );

        return response()->json([
            'message' => 'Student accepted and assignment created successfully!',
            'data' => $assignment
        ]);
    }
    public function status(Request $request, $id)
    {
        $companyId = $this->companyId($request);
        $project = Project::where('company_id', $companyId)->findOrFail($id);

        $validated = $request->validate([
            'status' => ['required', Rule::in(['draft', 'published', 'closed'])],
        ]);

        $project->status = $validated['status'];
        $project->save();

        return response()->json([
            'message' => 'Status updated',
            'data' => $project
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $companyId = $this->companyId($request);
        $project = Project::where('company_id', $companyId)->findOrFail($id);

        if ($project->attachment_path) {
            Storage::disk('public')->delete($project->attachment_path);
        }

        $project->delete();

        return response()->json(['message' => 'Deleted']);
    }
    //     public function getRunningJobs($companyId) 
    // {
    //     // Running jobs ki logic jo humne discuss ki thi
    //     $runningJobs = Project::where('company_id', $companyId)
    //         ->where('status', 'close')
    //         ->whereHas('applications', function($query) {
    //             $query->where('status', 'accepted');
    //         })
    //         ->with('applications.student.user')
    //         ->get();

    //     return response()->json($runningJobs);
    // }
    // public function getRunningJobs($companyId)
    // {
    //     return Project::where('company_id', $companyId)
    //         ->where('status', 'close') // Job close honi chahiye
    //         ->whereHas('applications', function ($query) {
    //             $query->where('status', 'accepted'); // Aur kam az kam ek banda accept hua ho
    //         })
    //         ->get();
    // }
    // public function getRunningJobs($companyId)
    // {
    //     // Database screenshot ke mutabiq 'project_id' aur status 'close' use ho raha hai
    //     $jobs = Project::where('company_id', $companyId)
    //         ->where('status', 'closed')
    //         ->whereHas('applications', function ($query) {
    //             $query->where('status', 'accepted');
    //         })
    //         ->get();

    //     return response()->json($jobs);
    // }
public function getRunningJobs(int $companyId)
{
    $jobs = Project::with('company')
        ->where('company_id', $companyId)
        ->where('status', 'published')
        ->whereHas('applications', function ($q) {
            $q->where('status', 'accepted');
        })
        ->get();

    return response()->json($jobs);
}
public function getClosedJobs(int $companyId)
{
    $jobs = Project::with('company')
        ->where('company_id', $companyId)
        ->where('status', 'closed')
        ->whereHas('applications', function ($q) {
            $q->where('status', 'accepted');
        })
        ->get();

    return response()->json($jobs);
}

public function getCurrentJobs(int $companyId)
{
    $jobs = Project::with('company')
        ->where('company_id', $companyId)
        ->where('status', 'published')
        ->whereHas('applications', function ($q) {
            $q->where('status', 'in_review');
        })
        ->get();

    return response()->json($jobs);
}


    public function dashboardProjects(Request $request)
    {
        $company = $request->attributes->get('company');

        $projects = Project::with(['assignments', 'applications'])
            ->where('company_id', $company->id)
            ->get();

        return response()->json($projects);
    }
}
