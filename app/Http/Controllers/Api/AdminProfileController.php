<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminProfileController extends Controller
{
    public function show(Request $request)
    {
        $admin = $request->user();

        return response()->json([
            'admin' => [
                'id' => $admin->id,
                'email' => $admin->email,
            ]
        ]);
    }

    public function update(Request $request)
    {
        $admin = $request->user();

        $validated = $request->validate([
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($admin->id),
            ],
            'password' => [
                'nullable',
                'string',
                'min:8',
                'confirmed',
            ],
        ]);

        $admin->email = $validated['email'];

        if (!empty($validated['password'])) {
            $admin->password = Hash::make($validated['password']);
        }

        $admin->save();

        return response()->json([
            'message' => 'Profile updated successfully',
            'admin' => [
                'id' => $admin->id,
                'email' => $admin->email,
            ]
        ]);
    }
}