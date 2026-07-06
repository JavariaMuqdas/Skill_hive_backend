<?php

namespace App\Http\Controllers\Api;

use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Company;
use App\Models\Student;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }

        // 1. Agar login karne wala 'company' hai, toh check karein uska status
        if ($user->role === 'company') {
            $user->load('company');

            // Agar admin ne delete kiya hua ho (Jo humne pehle baat ki thi)
            if ($user->company && $user->company->status === 'deleted') {
                return response()->json([
                    'message' => 'Your account has been removed by the administrator.'
                ], 403);
            }
        } else {
            $user->load('student');
        }

        $token = $user->createToken('token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $user // Is user object ke andar ab company ka status frontend ko mil jayega
        ]);
    }

    public function register(Request $request)
    {
        $request->validate([
            'role' => ['required', Rule::in(['student', 'company'])],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],

            // student
            'degree' => ['required_if:role,student', 'string', 'max:255'],
            'skills' => ['required_if:role,student', 'string', 'max:500'],
            'phone'  => ['required_if:role,student', 'string', 'max:50'],
            'resume' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:4096'],

            // company
            'company_name' => ['required_if:role,company', 'string', 'max:255'],
            'industry' => ['required_if:role,company', 'string', 'max:255'],
            'description' => ['required_if:role,company', 'string', 'max:2000'],
            'website' => ['nullable', 'string', 'max:255'],
            'company_phone' => ['required_if:role,company', 'string', 'max:50'],
        ]);

        // 1) Create user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role, // ✅ correct
        ]);

        // 2) Create profile based on role
        if ($request->role === 'student') {
            $resumePath = null;
            if ($request->hasFile('resume')) {
                $resumePath = $request->file('resume')->store('resumes', 'public');
            }

            Student::create([
                'user_id' => $user->id,
                'phone' => $request->phone,
                'degree' => $request->degree,
                'skills' => $request->skills,
                'resume_path' => $resumePath,
            ]);
        }

        if ($request->role === 'company') {
            Company::create([
                'user_id' => $user->id,
                'company_name' => $request->company_name,
                'phone' => $request->company_phone,
                'website' => $request->website,
                'industry' => $request->industry,
                'description' => $request->description,
            ]);
        }

        // 3) Issue token
        $token = $user->createToken('web')->plainTextToken;

        // ✅ return fresh user with profile (important for redirect)
        $user->load($user->role === 'company' ? 'company' : 'student');

        return response()->json([
            'token' => $token,
            'user' => $user,
        ], 201);
    }


    public function me(Request $request)
    {
        $user = $request->user();

        if ($user->role === 'student') $user->load('student');
        if ($user->role === 'company') $user->load('company');

        return response()->json(['user' => $user]);
    }


    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json(['message' => 'Logged out']);
    }
}
