<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StudentProfileController extends Controller
{
  public function me(Request $request)
  {
    $student = $request->attributes->get('student');
    $student->load('user:id,name,email');

    return response()->json(['student' => $student]);
  }
}
