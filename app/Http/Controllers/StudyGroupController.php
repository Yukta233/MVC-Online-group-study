<?php

namespace App\Http\Controllers;

use App\Models\StudyGroup;
use App\Models\StudyNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class StudyGroupController extends Controller
{
    public function create(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'subject' => 'required|string|max:255',
        ]);

        // Generate a unique 8-character access code
        do {
            $accessCode = strtoupper(Str::random(8));
        } while (StudyGroup::where('access_code', $accessCode)->exists());

        $group = StudyGroup::create([
            'name' => $request->name,
            'description' => $request->description,
            'subject' => $request->subject,
            'access_code' => $accessCode,
            'owner_id' => Auth::id(),
        ]);

        // Automatically join the owner to the group
        $group->members()->attach(Auth::id());

        // Initialize empty study notes for this group
        StudyNote::create([
            'study_group_id' => $group->id,
            'content' => "# Welcome to {$group->name} shared notepad!\n\nUse this space to collaboratively take notes, outline lecture summaries, and brainstorm study guides.",
            'last_edited_by' => Auth::id(),
        ]);

        return redirect()->route('dashboard')->with('success', 'Study Group created successfully! Share the access code: ' . $accessCode);
    }

    public function join(Request $request)
    {
        $request->validate([
            'access_code' => 'required|string',
        ]);

        $group = StudyGroup::where('access_code', strtoupper(trim($request->access_code)))->first();

        if (!$group) {
            return back()->withErrors(['access_code' => 'No study group found with this access code.']);
        }

        // Attach user if they haven't joined yet
        if (!$group->members()->where('user_id', Auth::id())->exists()) {
            $group->members()->attach(Auth::id());
        }

        return redirect()->route('groups.show', $group->id)->with('success', 'Joined study group: ' . $group->name);
    }

    public function show($id)
    {
        $group = StudyGroup::with([
            'owner',
            'members',
            'chatMessages.user',
            'resources.user',
            'studyNote.lastEditor',
            'studySessions',
            'studyTasks.assignee',
            'quizzes.creator',
            'quizzes.questions',
            'quizzes.attempts.user',
            'flashcards.creator'
        ])->findOrFail($id);

        // Ensure user is authorized to view this group
        if (!$group->members()->where('user_id', Auth::id())->exists()) {
            return redirect()->route('dashboard')->withErrors(['auth' => 'You are not a member of that study group.']);
        }

        return view('groups.show', compact('group'));
    }
}
