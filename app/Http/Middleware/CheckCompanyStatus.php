<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Company;
use Symfony\Component\HttpFoundation\Response;

class CheckCompanyStatus
{
    public function handle(Request $request, Closure $next): Response
    {
        // Logged in user ki company dhoondein
        $company = Company::where('user_id', $request->user()->id)->first();

        // 🚫 Agar company blocked hai toh aage kisi bhi API ko hit na karne dein
        if ($company && $company->status === 'blocked') {
            return response()->json([
                'message' => 'Action restricted. Your company account has been blocked by the administrator.'
            ], 403);
        }

        return $next($request);
    }
}