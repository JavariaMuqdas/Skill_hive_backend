<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MessageThread;
use App\Models\Message;
use App\Models\ProjectAssignment;

class ChatController extends Controller
{
  public function myThreads(Request $request)
  {
    $student = $request->attributes->get('student');
    $company = $request->attributes->get('company');

    if ($student) {
      $threads = MessageThread::where('student_id', $student->id)
        ->orderByDesc('id')
        ->get();
      return response()->json(['threads' => $threads]);
    }

    if ($company) {
      $threads = MessageThread::where('company_id', $company->id)
        ->orderByDesc('id')
        ->get();
      return response()->json(['threads' => $threads]);
    }

    return response()->json(['threads' => []]);
  }

  private function ensureThreadMember(Request $request, MessageThread $thread)
  {
    $student = $request->attributes->get('student');
    $company = $request->attributes->get('company');

    if ($student && (int)$thread->student_id === (int)$student->id) return true;
    if ($company && (int)$thread->company_id === (int)$company->id) return true;

    abort(403, 'Forbidden');
  }

  public function messages(Request $request, MessageThread $thread)
  {
    $this->ensureThreadMember($request, $thread);

    $msgs = Message::where('thread_id', $thread->id)
      ->orderBy('id', 'asc')
      ->get();

    return response()->json(['messages' => $msgs]);
  }

  public function send(Request $request, MessageThread $thread)
  {
    $this->ensureThreadMember($request, $thread);

    $data = $request->validate([
      'body' => 'required|string|max:2000',
    ]);

    $msg = Message::create([
      'thread_id' => $thread->id,
      'sender_user_id' => $request->user()->id,
      'body' => $data['body'],
    ]);

    return response()->json(['message' => $msg], 201);
  }
}
