<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Company;

class CompanyOnly
{
  public function handle($request, Closure $next)
  {
    $user = $request->user();
    if (!$user) return response()->json(['message'=>'Unauthenticated'], 401);

    $company = Company::where('user_id', $user->id)->first();
    if (!$company) return response()->json(['message'=>'Company only'], 403);

    $request->attributes->set('company', $company);

    return $next($request);
  }
}
