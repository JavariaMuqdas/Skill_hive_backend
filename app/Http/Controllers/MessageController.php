<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MessageController extends Controller
{
    // Candidate Messaging logic
    public function send(Request $request)
    {
        return response()->json(['message' => 'Message sent successfully!']);
    }
}