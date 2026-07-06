<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use App\Models\Project;
class StudentCompanyController extends Controller
{
    public function show($id)
    {
        $company = Company::findOrFail($id);

        $company->published_jobs_count = Project::where('company_id', $company->id)
            ->where('status', 'published')
            ->count();

        return response()->json($company);
    }
}
