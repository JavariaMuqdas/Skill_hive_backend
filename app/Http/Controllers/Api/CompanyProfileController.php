<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Company;

class CompanyProfileController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();
        $company = Company::where('user_id', $user->id)->first();

        if (!$company) {
            return response()->json(['message' => 'Company profile not found'], 404);
        }

        $company->logo_url = $company->logo ? asset('storage/' . $company->logo) : null;
        return response()->json($company);
    }

    public function update(Request $request)
    {
        $company = Company::where('user_id', $request->user()->id)->firstOrFail();

        $data = $request->validate([
            'company_name' => 'required|string|max:255',
            'phone'        => 'nullable|string|max:50',
            'website'      => 'nullable|string|max:255',
            'company_email'=> 'nullable|email|max:255',
            'linkedin_url' => 'nullable|string|max:255',

            'industry'     => 'nullable|string|max:255',
            'company_size' => 'nullable|string|max:50',
            'city'         => 'nullable|string|max:100',
            'country'      => 'nullable|string|max:100',
            'founded_year' => 'nullable|integer|min:1800|max:2100',

            'description'       => 'nullable|string',
            'short_description' => 'nullable|string|max:1200',
            'overview'          => 'nullable|string',
            'why_join_us'       => 'nullable|string|max:1200',

            'total_employees'   => 'nullable|integer|min:0|max:1000000',
            'dept_it'           => 'nullable|integer|min:0|max:1000000',
            'dept_hr'           => 'nullable|integer|min:0|max:1000000',
            'dept_marketing'    => 'nullable|integer|min:0|max:1000000',
            'dept_sales'        => 'nullable|integer|min:0|max:1000000',
            'dept_finance'      => 'nullable|integer|min:0|max:1000000',
            'dept_operations'   => 'nullable|integer|min:0|max:1000000',

            'logo'         => 'nullable|image|max:2048',
        ]);

        // text update
        $company->update($data);

        // logo upload (existing logic kept)
        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('company_logos', 'public');
            $company->update(['logo' => $path]);
        }

        $company->refresh();
        $company->logo_url = $company->logo ? asset('storage/' . $company->logo) : null;

        return response()->json($company);
    }
}