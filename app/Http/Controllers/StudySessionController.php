<?php

namespace App\Http\Controllers;

use App\Models\StudyGroup;
use App\Models\StudySession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudySessionController extends Controller
{
    public function store(Request $request, $groupId)
    {
        $group = StudyGroup::findOrFail($groupId);

        // Ensure membership
        if (!$group->members()->where('user_id', Auth::id())->exists()) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'scheduled_at' => 'required|date|after:now',
            'duration_minutes' => 'required|integer|min:5|max:480',
            'meeting_link' => 'nullable|url|max:500',
        ]);

        StudySession::create([
            'study_group_id' => $group->id,
            'title' => $request->title,
            'description' => $request->description,
            'scheduled_at' => $request->scheduled_at,
            'duration_minutes' => $request->duration_minutes,
            'meeting_link' => $request->meeting_link,
        ]);

        return back()->with('success', 'Study Session scheduled successfully!');
    }
}
