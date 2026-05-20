<?php

namespace App\Http\Controllers;

use App\Models\StudyGroup;
use App\Models\StudyTask;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudyTaskController extends Controller
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
            'assignee_id' => 'nullable|exists:users,id',
        ]);

        // Verify if assignee is a member of this study group
        if ($request->filled('assignee_id')) {
            if (!$group->members()->where('user_id', $request->assignee_id)->exists()) {
                return back()->withErrors(['assignee_id' => 'Selected assignee must be a member of this study group.']);
            }
        }

        StudyTask::create([
            'study_group_id' => $group->id,
            'title' => $request->title,
            'description' => $request->description,
            'status' => 'todo',
            'assignee_id' => $request->assignee_id,
        ]);

        return back()->with('success', 'Task created successfully!');
    }

    public function storeGlobal(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'study_group_id' => 'nullable|exists:study_groups,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $groupId = null;
        if ($request->filled('study_group_id')) {
            $group = StudyGroup::findOrFail($request->study_group_id);

            // Ensure user belongs to this study group
            if (!$group->members()->where('user_id', $user->id)->exists()) {
                abort(403, 'Unauthorized action.');
            }
            $groupId = $group->id;
        }

        StudyTask::create([
            'study_group_id' => $groupId,
            'title' => $request->title,
            'description' => $request->description,
            'status' => 'todo',
            'assignee_id' => $user->id, // Automatically assign to self on My Tasks
        ]);

        return back()->with('success', 'Task added to your board successfully!');
    }

    public function updateStatus(Request $request, $id)
    {
        $task = StudyTask::with('studyGroup')->findOrFail($id);

        // Ensure membership in study group if task is associated with one
        if ($task->study_group_id) {
            if (!$task->studyGroup->members()->where('user_id', Auth::id())->exists()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
        } else {
            // For personal tasks, ensure the task belongs to the user
            if ($task->assignee_id !== Auth::id()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
        }

        $request->validate([
            'status' => 'required|in:todo,in_progress,completed',
        ]);

        $task->update([
            'status' => $request->status,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Task status updated.',
            'task_id' => $task->id,
            'new_status' => $task->status,
        ]);
    }
}
