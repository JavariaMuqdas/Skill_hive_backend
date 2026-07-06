<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProjectAssignment;

class WorkController extends Controller
{
  public function studentAssignments(Request $request)
  {
    $student = $request->attributes->get('student');

    $rows = ProjectAssignment::where('student_id', $student->id)
      ->with(['project', 'company']) // optional (if you want company info on student side)
      ->orderByDesc('id')
      ->get();

    return response()->json(['assignments' => $rows]);
  }

  public function companyAssignments(Request $request)
  {
    $company = $request->attributes->get('company');

    $rows = ProjectAssignment::where('company_id', $company->id)
      ->with([
        'project',
        'student.user:id,name,email' // ✅ so UI can show student name/email
      ])
      ->orderByDesc('id')
      ->get();

    return response()->json(['assignments' => $rows]);
  }
}
