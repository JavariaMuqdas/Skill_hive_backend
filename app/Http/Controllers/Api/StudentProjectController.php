<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;
use App\Models\Application;

class StudentProjectController extends Controller
{
    // public function index(Request $request)
    // {
    //     $q = Project::query();

    //     // Allow both published and active
    //     $q->whereIn('status', ['published', 'active']);

    //     if ($request->filled('company_id')) {
    //         $q->where('company_id', $request->company_id);
    //     }

    //     return response()->json($q->latest()->get());
    // }
    public function index(Request $request)
    {
        $q = Project::with('company:id,company_name')
            ->whereIn('status', ['published', 'active']);

        if ($request->filled('company_id')) {
            $q->where('company_id', $request->company_id);
        }

        return response()->json($q->latest()->get());
    }

    // public function show(Request $request, Project $project)
    // {
    //     if (!in_array($project->status, ['published', 'active'])) {
    //         return response()->json(['message' => 'Not available'], 404);
    //     }

    //     $project->load('company:id,company_name,phone,website,industry,description,logo');

    //     // 1. Get the logged-in student's ID (or fall back to user id)
    //     // Agar aapka student modal alag relation mein h to use: $request->user()->student->id;
    //     $studentId = $request->user() ? $request->user()->id : null;

    //     // 2. Check if an application exists with any of your allowed statuses
    //     $isApplied = false;
    //     if ($studentId) {
    //         $isApplied = Application::where('project_id', $project->id)
    //             ->where('student_id', $studentId)
    //             ->whereIn('status', ['applied', 'accepted', 'rejected', 'in_review', 'interview']) // Apke saare statuses yahan check ho rhe hain
    //             ->exists(); 
    //     }

    //     // 3. Force boolean conversion and append to the model
    //     $project->is_applied = (bool) $isApplied;

    //     return response()->json(['project' => $project]);
    // }
    // public function show(Request $request, Project $project)
    // {
    //     if (!in_array($project->status, ['published', 'active'])) {
    //         return response()->json(['message' => 'Not available'], 404);
    //     }

    //     $project->load('company:id,company_name,phone,website,industry,description,logo');

    //     // 1. Get the logged-in User
    //     $user = $request->user();
    //     $studentId = null;

    //     if ($user) {
    //         // AGAR aapka Student model User model se linked hai (e.g., $user->student), to yeh lagayein:
    //         if (method_exists($user, 'student') || isset($user->student)) {
    //             $studentId = $user->student ? $user->student->id : $user->id;
    //         } else {
    //             // Agar users aur students table ki ID same hai ya direct link hai:
    //             $studentId = $user->id;
    //         }
    //     }

    //     // 2. Check if ANY application record exists
    //     $isApplied = false;
    //     if ($studentId) {
    //         $isApplied = Application::where('project_id', $project->id)
    //             ->where('student_id', $studentId)
    //             ->whereIn('status', ['applied', 'accepted', 'rejected', 'in_review'])
    //             ->exists();
    //     }

    //     // 3. IMPORTANT: Transform model to array and append dynamically so it strictly sends in JSON
    //     $projectArray = $project->toArray();
    //     $projectArray['is_applied'] = (bool) $isApplied;

    //     return response()->json(['project' => $projectArray]);
    // }
    public function show(Request $request, Project $project)
    {
        if (!in_array($project->status, ['published', 'active'])) {
            return response()->json(['message' => 'Not available'], 404);
        }

        $project->load('company:id,company_name,phone,website,industry,description,logo');

        // 1. Resolve the correct ID for the student tracking match
        $user = $request->user();
        $studentId = null;

        if ($user) {
            // If your User model maps a relationship to a Student profile model
            if (isset($user->student) && $user->student) {
                $studentId = $user->student->id;
            } else {
                // Fallback assumption: user_id is the student_id directly
                $studentId = $user->id;
            }
        }

        // 2. Check the database against all specified statuses from the Applications table
        $isApplied = false;
        if ($studentId) {
            $isApplied = Application::where('project_id', $project->id)
                ->where('student_id', $studentId)
                ->whereIn('status', ['applied', 'accepted', 'rejected', 'in_review', 'interview'])
                ->exists();
        }

        // 3. Convert the Eloquent model instance to an array to forcefully append the flag
        $projectArray = $project->toArray();
        $projectArray['is_applied'] = (bool) $isApplied;

        return response()->json(['project' => $projectArray]);
    }
}
