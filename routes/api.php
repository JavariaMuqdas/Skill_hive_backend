<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AdminCompanyController;
use App\Http\Controllers\Api\RecommendationController;
use App\Http\Controllers\Api\CompanyProfileController;
use App\Http\Controllers\Api\CompanyProjectController;
use App\Http\Controllers\Api\StudentProjectController;
use App\Http\Controllers\Api\StudentApplicationController;
use App\Http\Controllers\Api\StudentResumeController;
use App\Http\Controllers\Api\CompanyApplicationsController;
use App\Http\Controllers\Api\CompanyJobsController;
use App\Http\Controllers\Api\StudentProfileController;
use App\Http\Controllers\Api\WorkController;
use App\Http\Controllers\Api\TrackingController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\JobCategoryController;
use App\Http\Controllers\Api\CategoryJobController;
use App\Http\Controllers\Api\AdminProfileController;

use App\Http\Controllers\StudentCompanyController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\StudentDashboardController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\DashboardController;

use App\Http\Middleware\CheckCompanyStatus;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);
});

// Landing/Public APIs
Route::get('/job-categories', [JobCategoryController::class, 'index']);
Route::get('/category-jobs', [CategoryJobController::class, 'index']);
Route::get('/recent-jobs', [CategoryJobController::class, 'recentJobs']);
Route::get('/jobs-list', [CategoryJobController::class, 'jobsList']);
Route::get('/platform-stats', [CategoryJobController::class, 'platformStats']);
Route::get('/top-companies', [CategoryJobController::class, 'topCompanies']);
Route::get('/company-detail/{id}', [CategoryJobController::class, 'companyDetail']);

// Email / Schedule Routes
Route::post('/send-interview-email', [CompanyApplicationsController::class, 'sendInterviewEmail']);
Route::post('/send-selection-email', [CompanyApplicationsController::class, 'sendSelectionEmail']);
Route::post('/save-interview-schedule', [CompanyApplicationsController::class, 'saveInterviewSchedule']);
Route::post('/update-scores', [CompanyApplicationsController::class, 'updateScores']);

Route::get('/company/dashboard-projects', [CompanyApplicationsController::class, 'dashboardProjects']);
Route::get('/company/{id}/running-jobs', [CompanyProjectController::class, 'getRunningJobs']);
Route::get('/company/{id}/closed-jobs', [CompanyProjectController::class, 'getClosedJobs']);
Route::get('/company/{id}/current-jobs', [CompanyProjectController::class, 'getCurrentJobs']);

/*
|--------------------------------------------------------------------------
| Protected Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {

    Route::prefix('auth')->group(function () {
        Route::get('me', [AuthController::class, 'me']);
        Route::post('logout', [AuthController::class, 'logout']);
    });

    /*
    |--------------------------------------------------------------------------
    | Admin Routes
    |--------------------------------------------------------------------------
    */

    Route::get('/admin/dashboard-stats', [AdminDashboardController::class, 'index']);
    Route::get('/admin/companies', [AdminCompanyController::class, 'index']);
    Route::get('/admin/users', [AdminCompanyController::class, 'usersIndex']);
    Route::get('/admin/jobs', [AdminCompanyController::class, 'jobsIndex']);
    Route::put('/admin/companies/{id}/block', [AdminCompanyController::class, 'blockCompany']);
    Route::put('/admin/companies/{id}/approve', [AdminCompanyController::class, 'approveCompany']);
    Route::delete('/admin/companies/{id}', [AdminCompanyController::class, 'destroy']);
    Route::get('/admin/companies/{id}/jobs', [CompanyController::class, 'getCompanyJobs']);

    Route::get('/admin/profile', [AdminProfileController::class, 'show']);
    Route::put('/admin/profile', [AdminProfileController::class, 'update']);

    /*
    |--------------------------------------------------------------------------
    | Company General Protected Routes
    |--------------------------------------------------------------------------
    */

    Route::get('/company/profile', [CompanyController::class, 'getProfile']);

    Route::middleware([CheckCompanyStatus::class])->group(function () {
        Route::post('/jobs/create', [CompanyJobsController::class, 'store']);
        Route::put('/jobs/edit/{id}', [CompanyJobsController::class, 'update']);
        Route::post('/messages/send', [MessageController::class, 'send']);

        Route::get('/companies/applicants', [CompanyApplicationsController::class, 'applicants']);
        Route::post('/applications/{application}/accept', [CompanyApplicationsController::class, 'accept']);
        Route::post('/applications/{application}/reject', [CompanyApplicationsController::class, 'reject']);
        Route::put('/applications/{applicationId}/status', [CompanyApplicationsController::class, 'updateStatus']);
    });

    /*
    |--------------------------------------------------------------------------
    | Company Only Routes
    |--------------------------------------------------------------------------
    */

    Route::middleware('company')->prefix('company')->group(function () {

        Route::get('profile', [CompanyProfileController::class, 'show']);
        Route::post('profile', [CompanyProfileController::class, 'update']);

        // Category dropdown route
        Route::get('job-categories', [CategoryJobController::class, 'categories']);

        // Company jobs
        Route::get('jobs', [CompanyJobsController::class, 'index']);
        Route::post('jobs', [CompanyJobsController::class, 'store']);
        Route::put('jobs/{job}', [CompanyJobsController::class, 'update']);
        Route::delete('jobs/{job}', [CompanyJobsController::class, 'destroy']);

        // Projects
        Route::get('{companyId}/running-jobs', [CompanyProjectController::class, 'getRunningJobs']);
        Route::get('projects', [CompanyProjectController::class, 'index']);
        Route::post('projects', [CompanyProjectController::class, 'store']);
        Route::get('projects/{project}', [CompanyProjectController::class, 'show']);
        Route::put('projects/{project}', [CompanyProjectController::class, 'update']);
        Route::patch('projects/{project}/status', [CompanyProjectController::class, 'status']);
        Route::delete('projects/{project}', [CompanyProjectController::class, 'destroy']);

        // Tracking
        Route::get('projects/{project}/tracking', [TrackingController::class, 'index']);
        Route::post('projects/{project}/tracking', [TrackingController::class, 'store']);

        // Applicants
        Route::get('applicants', [CompanyApplicationsController::class, 'applicants']);
        Route::post('applications/{application}/accept', [CompanyApplicationsController::class, 'accept']);
        Route::post('applications/{application}/reject', [CompanyApplicationsController::class, 'reject']);
        Route::patch('applications/{application}/status', [CompanyApplicationsController::class, 'updateStatus']);
        Route::get('students/{student}/resume-preview', [CompanyApplicationsController::class, 'resumePreview']);

        Route::get('recommended-students', [RecommendationController::class, 'recommendedStudents']);
        Route::get('assignments', [WorkController::class, 'companyAssignments']);
    });

    /*
    |--------------------------------------------------------------------------
    | Student Routes
    |--------------------------------------------------------------------------
    */

    Route::get('/student/dashboard-stats', [DashboardController::class, 'studentStats']);

    Route::middleware('student')->prefix('student')->group(function () {
        Route::get('dashboard-stats', [StudentDashboardController::class, 'index']);

        Route::get('projects', [StudentProjectController::class, 'index']);
        Route::get('projects/{project}', [StudentProjectController::class, 'show']);
        Route::get('projects/{project}/tracking', [TrackingController::class, 'studentIndex']);

        Route::get('me', [StudentProfileController::class, 'me']);

        Route::get('resume', [StudentResumeController::class, 'show']);
        Route::post('resume/base', [StudentResumeController::class, 'saveBase']);
        Route::post('resume/education', [StudentResumeController::class, 'addEducation']);
        Route::put('resume/education/{id}', [StudentResumeController::class, 'updateEducation']);
        Route::delete('resume/education/{id}', [StudentResumeController::class, 'deleteEducation']);

        Route::post('resume/experience', [StudentResumeController::class, 'addExperience']);
        Route::delete('resume/experience/{id}', [StudentResumeController::class, 'deleteExperience']);

        Route::post('resume/skill', [StudentResumeController::class, 'addSkill']);
        Route::delete('resume/skill/{id}', [StudentResumeController::class, 'deleteSkill']);

        Route::post('resume/project', [StudentResumeController::class, 'addProject']);
        Route::delete('resume/project/{id}', [StudentResumeController::class, 'deleteProject']);

        Route::post('resume/language', [StudentResumeController::class, 'addLanguage']);
        Route::delete('resume/language/{id}', [StudentResumeController::class, 'deleteLanguage']);

        Route::post('apply', [StudentApplicationController::class, 'apply']);
        Route::get('applications', [StudentApplicationController::class, 'myApplications']);

        Route::get('assignments', [WorkController::class, 'studentAssignments']);

        Route::get('companies', [CompanyController::class, 'index']);
        Route::get('companies/{id}', [StudentCompanyController::class, 'show']);
    });

    /*
    |--------------------------------------------------------------------------
    | Shared Chat Routes
    |--------------------------------------------------------------------------
    */

    Route::get('threads', [ChatController::class, 'myThreads']);
    Route::get('threads/{thread}/messages', [ChatController::class, 'messages']);
    Route::post('threads/{thread}/messages', [ChatController::class, 'send']);
});