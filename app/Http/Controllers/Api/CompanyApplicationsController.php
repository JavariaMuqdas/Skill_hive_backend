<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\ProjectAssignment;
use App\Models\MessageThread;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\InterviewInvitation;
use App\Mail\SelectionConfirmation;

class CompanyApplicationsController extends Controller
{
  public function applicants(Request $request)
  {
    $company = $request->attributes->get('company');

    // 🚫 Check if the company is blocked
    if ($company && $company->status === 'blocked') {
        return response()->json([
            'message' => 'Action restricted. Your company account has been blocked by the administrator.'
        ], 403);
    }

    $query = Application::with([
      'project',
      'student',
      'student.user:id,name,email',
      'student.resumeSkills'
    ])
      ->where('company_id', $company->id);

    if ($request->filled('jobId')) {
      $query->where('project_id', $request->jobId);
    }

    $apps = $query->orderByDesc('id')->get();

    return response()->json([
      'applications' => $apps
    ]);
  }

  public function accept(Request $request, Application $application)
  {
    $company = $request->attributes->get('company');

    if ((int)$application->company_id !== (int)$company->id) {
      return response()->json(['message' => 'Forbidden'], 403);
    }

    // 🚫 Check if the company is blocked
    if ($company && $company->status === 'blocked') {
        return response()->json([
            'message' => 'Action restricted. Your company account has been blocked by the administrator.'
        ], 403);
    }

    // ✅ Create/ensure assignment
    $assignment = ProjectAssignment::firstOrCreate(
      [
        'project_id' => $application->project_id,
        'company_id' => $application->company_id,
        'student_id' => $application->student_id,
      ],
      ['status' => 'active']
    );

    // ✅ Update application status
    $application->update(['status' => 'accepted']);

    // ✅ Auto create chat thread for this project-company-student
    $thread = MessageThread::firstOrCreate([
      'project_id' => $application->project_id,
      'company_id' => $application->company_id,
      'student_id' => $application->student_id,
    ]);

    return response()->json([
      'message' => 'Accepted',
      'assignment' => $assignment,
      'thread' => $thread,
    ]);
  }

  public function reject(Request $request, Application $application)
  {
    $company = $request->attributes->get('company');

    if ((int)$application->company_id !== (int)$company->id) {
      return response()->json(['message' => 'Forbidden'], 403);
    }

    // 🚫 Check if the company is blocked
    if ($company && $company->status === 'blocked') {
        return response()->json([
            'message' => 'Action restricted. Your company account has been blocked by the administrator.'
        ], 403);
    }

    $application->update(['status' => 'rejected']);

    return response()->json(['message' => 'Rejected']);
  }

  public function resumePreview($studentId)
  {
    $student = \App\Models\Student::with([
      'user:id,name,email',
      'resumeBase',
      'resumeEducations',
      'resumeExperiences',
      'resumeSkills',
      'resumeProjects',
      'resumeLanguages',
    ])->findOrFail($studentId);

    return response()->json([
      'student' => $student,
      'resume' => $student->resumeBase,
      'educations' => $student->resumeEducations ?? [],
      'experiences' => $student->resumeExperiences ?? [],
      'skills' => $student->resumeSkills ?? [],
      'projects' => $student->resumeProjects ?? [],
      'languages' => $student->resumeLanguages ?? [],
    ]);
  }

  public function updateStatus(Request $request, $applicationId)
  {
    $request->validate([
      'status' => 'required|in:in_review,accepted,rejected',
    ]);

    $application = Application::findOrFail($applicationId);
    $company = $request->attributes->get('company');

    // 🚫 Check if the company is blocked
    if ($company && $company->status === 'blocked') {
        return response()->json([
            'message' => 'Action restricted. Your company account has been blocked by the administrator.'
        ], 403);
    }

    $application->status = $request->status;
    $application->save();

    return response()->json([
      'message' => 'Application status updated successfully.',
      'application' => $application,
    ]);
  }

  public function saveInterviewMarks(Request $request, $id)
  {
    $app = Application::findOrFail($id);
    $company = $request->attributes->get('company');

    // 🚫 Check if the company is blocked
    if ($company && $company->status === 'blocked') {
        return response()->json([
            'message' => 'Action restricted. Your company account has been blocked by the administrator.'
        ], 403);
    }

    $interviewMarks = $request->marks;
    $skillMatch = $app->skill_match_percentage; 

    $finalScore = ($skillMatch + $interviewMarks) / 2;

    $app->update([
      'interview_marks' => $interviewMarks,
      'final_score' => $finalScore,
      'remarks' => $request->remarks
    ]);

    return response()->json(['message' => 'Marks and Final Score saved!']);
  }

  public function sendInterviewEmail(Request $request)
  {
    $application = Application::with('company')->findOrFail($request->applicationId);
    $company = $request->attributes->get('company');

    // 🚫 Check if the company is blocked
    if ($company && $company->status === 'blocked') {
        return response()->json([
            'message' => 'Action restricted. Your company account has been blocked by the administrator.'
        ], 403);
    }

    if (!$application->interview_date || !$application->interview_time) {
      return response()->json([
        'message' => 'Please save interview date and time first.'
      ], 422);
    }

    $details = [
      'name' => $request->name,
      'jobTitle' => $request->jobTitle,
      'interviewDate' => $application->interview_date,
      'interviewTime' => $application->interview_time,
      'companyName' => $application->company->company_name ?? 'Company',
      'location' => 'Online',
      'interviewerName' => 'HR Team',
      'hrName' => 'HR Manager',
    ];

    Mail::to($request->email)->send(new InterviewInvitation($details));

    $application->status = 'in_review';
    $application->remarks = 'Interview invitation sent';
    $application->save();

    return response()->json(['message' => 'Email sent successfully'], 200);
  }

  public function sendSelectionEmail(Request $request)
  {
    try {
      $application = Application::with('company')->findOrFail($request->applicationId);
      $company = $request->attributes->get('company');

      // 🚫 Check if the company is blocked
      if ($company && $company->status === 'blocked') {
          return response()->json([
              'message' => 'Action restricted. Your company account has been blocked by the administrator.'
          ], 403);
      }

      $companyName = $application->company->company_name ?? 'Your Company';

      Mail::to($request->email)->send(
        new SelectionConfirmation(
          $request->name,
          $request->jobTitle,
          $companyName
        )
      );

      $application->status = 'accepted';
      $application->remarks = 'Selection email sent';
      $application->save();

      return response()->json(['message' => 'Success'], 200);

    } catch (\Exception $e) {
      return response()->json(['error' => $e->getMessage()], 500);
    }
  }

  public function updateScores(Request $request)
  {
    $app = Application::find($request->applicationId);
    $company = $request->attributes->get('company');

    // 🚫 Check if the company is blocked
    if ($company && $company->status === 'blocked') {
        return response()->json([
            'message' => 'Action restricted. Your company account has been blocked by the administrator.'
        ], 403);
    }

    if ($app) {
      $app->update([
        'interview_marks' => $request->interview_marks,
        'final_score' => $request->final_score, 
        'remarks' => 'Marks and Final Score updated'
      ]);
      return response()->json(['status' => 'success']);
    }
  }

  public function saveInterviewSchedule(Request $request)
  {
    $request->validate([
      'applicationId' => 'required|exists:applications,id',
      'interviewDate' => 'required|date',
      'interviewTime' => 'required',
    ]);

    $application = Application::findOrFail($request->applicationId);
    $company = $request->attributes->get('company');

    // 🚫 Check if the company is blocked
    if ($company && $company->status === 'blocked') {
        return response()->json([
            'message' => 'Action restricted. Your company account has been blocked by the administrator.'
        ], 403);
    }

    $application->interview_date = $request->interviewDate;
    $application->interview_time = $request->interviewTime;
    $application->save();

    return response()->json([
      'message' => 'Schedule saved successfully',
      'application' => $application
    ], 200);
  }
}