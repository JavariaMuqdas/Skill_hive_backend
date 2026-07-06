<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\ProjectAssignment;
use App\Models\TrackingUpdate;

class TrackingController extends Controller
{
  private function ensureMember(Request $request, Project $project)
  {
    $student = $request->attributes->get('student');
    $company = $request->attributes->get('company');

    // ✅ student member check (must be assigned)
    if ($student) {
      $ok = ProjectAssignment::where('project_id', $project->id)
        ->where('student_id', $student->id)
        ->exists();

      if (!$ok) abort(403, 'Not assigned to this project');

      return ['role' => 'student', 'student' => $student];
    }

    // ✅ company member check (must own this project)
    if ($company) {
      if ((int)$project->company_id !== (int)$company->id) abort(403, 'Forbidden');
      return ['role' => 'company', 'company' => $company];
    }

    abort(403, 'Forbidden');
  }

  /**
   * ✅ GET tracking:
   * - Student: sees tracking for himself on this project
   * - Company: can pass ?student_id= to view specific student's tracking
   */
  public function index(Request $request, Project $project)
  {
    $member = $this->ensureMember($request, $project);

    $studentId = null;

    // student view → own tracking
    if ($member['role'] === 'student') {
      $studentId = $member['student']->id;
    }

    // company view → must specify student_id OR fallback to first assignment
    if ($member['role'] === 'company') {
      $studentId = $request->query('student_id');

      if ($studentId) {
        $exists = ProjectAssignment::where('project_id', $project->id)
          ->where('student_id', $studentId)
          ->where('company_id', $project->company_id)
          ->exists();

        if (!$exists) return response()->json(['tracking' => []]);
      } else {
        $assignment = ProjectAssignment::where('project_id', $project->id)
          ->where('company_id', $project->company_id)
          ->orderByDesc('id')
          ->first();

        if (!$assignment) return response()->json(['tracking' => []]);
        $studentId = $assignment->student_id;
      }
    }

    $updates = TrackingUpdate::where('project_id', $project->id)
      ->where('company_id', $project->company_id)
      ->where('student_id', $studentId)
      ->orderByDesc('id')
      ->get();

    return response()->json(['tracking' => $updates]);
  }

  /**
   * ✅ Student route points here:
   * GET api/student/projects/{project}/tracking
   * Fix: this method was missing.
   */
  public function studentIndex(Request $request, Project $project)
  {
    return $this->index($request, $project);
  }

  /**
   * ✅ POST tracking:
   * Company only (route middleware 'company')
   * Requires student_id so we store update for the correct student
   */
  public function store(Request $request, Project $project)
  {
    $member = $this->ensureMember($request, $project);

    // just in case, extra safety
    if ($member['role'] !== 'company') abort(403, 'Company only');

    $data = $request->validate([
      'student_id' => 'required|integer',
      'title' => 'required|string|max:120',
      'note' => 'nullable|string',
      'progress' => 'nullable|integer|min:0|max:100',
    ]);

    // ✅ must have an assignment between project + company + student
    $assignment = ProjectAssignment::where('project_id', $project->id)
      ->where('company_id', $project->company_id)
      ->where('student_id', $data['student_id'])
      ->first();

    if (!$assignment) {
      return response()->json(['message' => 'No assignment found for this student'], 400);
    }

    $update = TrackingUpdate::create([
      'project_id' => $project->id,
      'company_id' => $project->company_id,
      'student_id' => $assignment->student_id,
      'title' => $data['title'],
      'note' => $data['note'] ?? null,
      'progress' => $data['progress'] ?? 0,
      'created_by_user_id' => $request->user()->id,
    ]);

    // ✅ optional: keep response key consistent for frontend
    return response()->json([
      'message' => 'Tracking update created',
      'tracking' => $update
    ], 201);
  }
}
