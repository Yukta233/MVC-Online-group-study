<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use App\Models\StudyGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatMessageController extends Controller
{
    public function send(Request $request, $groupId)
    {
        $group = StudyGroup::findOrFail($groupId);

        // Ensure membership
        if (!$group->members()->where('user_id', Auth::id())->exists()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'message' => 'required|string',
        ]);

        $message = ChatMessage::create([
            'study_group_id' => $group->id,
            'user_id' => Auth::id(),
            'message' => $request->message,
        ]);

        // Load sender relationship
        $message->load('user');

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'formatted_time' => $message->created_at->format('h:i A'),
            ]);
        }

        return back();
    }

    public function poll(Request $request, $groupId)
    {
        $group = StudyGroup::findOrFail($groupId);

        // Ensure membership
        if (!$group->members()->where('user_id', Auth::id())->exists()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $lastMessageId = $request->query('last_message_id', 0);

        $newMessages = ChatMessage::where('study_group_id', $groupId)
            ->where('id', '>', $lastMessageId)
            ->with('user')
            ->orderBy('created_at', 'asc')
            ->get();

        $formatted = $newMessages->map(function ($msg) {
            return [
                'id' => $msg->id,
                'user_id' => $msg->user_id,
                'user_name' => $msg->user->name,
                'message' => $msg->message,
                'is_me' => $msg->user_id === Auth::id(),
                'formatted_time' => $msg->created_at->format('h:i A'),
            ];
        });

        return response()->json([
            'messages' => $formatted,
        ]);
    }
}
