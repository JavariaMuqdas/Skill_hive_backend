<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CertificateController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        return Certificate::where('user_id', $userId)
            ->orderByDesc('id')
            ->get()
            ->map(function ($c) {
                // ✅ return only required fields
                return [
                    'id' => $c->id,
                    'title' => $c->title,
                    'issuer' => $c->issuer,
                    'date' => optional($c->issue_date)->format('Y-m-d'),
                    'image_url' => $c->image_url,
                ];
            });
    }

    public function store(Request $request)
    {
        $userId = Auth::id();

        $data = $request->validate([
            'title'  => ['required', 'string', 'max:255'],
            'issuer' => ['nullable', 'string', 'max:255'],
            'date'   => ['nullable', 'date'], // frontend sends "date"
            'image'  => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $cert = new Certificate();
        $cert->user_id = $userId;
        $cert->title = $data['title'];
        $cert->issuer = $data['issuer'] ?? null;
        $cert->issue_date = $data['date'] ?? null; // ✅ map date -> issue_date

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('certificates', 'public');
            $cert->image_path = $path;
        }

        $cert->save();

        return response()->json([
            'id' => $cert->id,
            'title' => $cert->title,
            'issuer' => $cert->issuer,
            'date' => optional($cert->issue_date)->format('Y-m-d'),
            'image_url' => $cert->image_url,
        ], 201);
    }

    public function destroy($id)
    {
        $userId = Auth::id();

        $cert = Certificate::where('user_id', $userId)->findOrFail($id);

        if ($cert->image_path) {
            Storage::disk('public')->delete($cert->image_path);
        }

        $cert->delete();

        return response()->json(['message' => 'Deleted']);
    }
}