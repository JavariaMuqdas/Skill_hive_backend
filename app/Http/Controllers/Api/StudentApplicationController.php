<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Project;
use Illuminate\Http\Request;

class StudentApplicationController extends Controller
{
  public function apply(Request $request)
  {
    $student = $request->attributes->get('student');

    $data = $request->validate([
      'project_id' => 'required|exists:projects,id',
      'cover_letter' => 'nullable|string',
    ]);

    $project = Project::findOrFail($data['project_id']);
    if ($project->status !== 'published') {
      return response()->json(['message' => 'Project not open'], 400);
    }

    // prevent duplicate (unique key will also protect)
    $exists = Application::where('project_id', $project->id)
      ->where('student_id', $student->id)
      ->exists();

    if ($exists) return response()->json(['message' => 'Already applied'], 409);

    $app = Application::create([
      'project_id' => $project->id,
      'company_id' => $project->company_id,
      'student_id' => $student->id,
      'cover_letter' => $data['cover_letter'] ?? null,
      'status' => 'applied',
    ]);

    return response()->json(['application' => $app], 201);
  }

  public function myApplications(Request $request)
  {
    $student = $request->attributes->get('student');

    $apps = Application::with(['project.company'])
  ->where('student_id', $student->id)
  ->orderByDesc('id')
  ->get();

    return response()->json(['applications' => $apps]);
  }

  // In your ApplicantController (or equivalent)

public function getApplicants()
{
    $applications = Application::with(['student.user', 'project']) // Eager load the user relation
        ->whereIn('status', ['applied', 'in-review', 'accepted', 'rejected'])
        ->get();

    return response()->json($applications);
}
}
