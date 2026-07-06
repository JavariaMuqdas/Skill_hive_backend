<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Student;

class StudentOnly
{
  public function handle($request, Closure $next)
  {
    $user = $request->user();
    if (!$user) return response()->json(['message'=>'Unauthenticated'], 401);

    $student = Student::where('user_id', $user->id)->first();
    if (!$student) return response()->json(['message'=>'Student only'], 403);

    // attach student to request for easy use
    $request->attributes->set('student', $student);

    return $next($request);
  }
}
