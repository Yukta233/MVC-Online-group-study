<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\StudyGroup;
use App\Models\Resource;
use App\Models\ChatMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    public function index()
    {
        $stats = [
            'total_users' => User::count(),
            'total_groups' => StudyGroup::count(),
            'total_resources' => Resource::count(),
            'total_messages' => ChatMessage::count(),
        ];

        // Fetch all study rooms with owners and member counts
        $groups = StudyGroup::with('owner')->withCount('members')->orderBy('created_at', 'desc')->get();

        // Fetch all resources with group and user info
        $resources = Resource::with(['studyGroup', 'user'])->orderBy('created_at', 'desc')->get();

        // Fetch all users with their roles, excluding the current admin
        $users = User::where('id', '!=', Auth::id())->orderBy('created_at', 'desc')->get();

        return view('admin.index', compact('stats', 'groups', 'resources', 'users'));
    }

    public function updateRole(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'role' => 'required|in:student,moderator,admin',
        ]);

        $user->role = $request->role;
        $user->save();

        return back()->with('success', "User '{$user->name}' role updated to {$request->role} successfully!");
    }

    public function destroyUser($id)
    {
        $user = User::findOrFail($id);
        
        // Prevent deleting oneself
        if ($user->id === Auth::id()) {
            return back()->withErrors(['error' => 'You cannot ban yourself!']);
        }

        $user->delete();

        return back()->with('success', "User '{$user->name}' has been banned and deleted from the portal.");
    }

    public function destroyGroup($id)
    {
        $group = StudyGroup::findOrFail($id);
        $groupName = $group->name;
        $group->delete();

        return back()->with('success', "Study room '{$groupName}' and all associated chat logs, whiteboard snaps, and notes have been deleted.");
    }

    public function destroyResource($id)
    {
        $resource = Resource::findOrFail($id);
        $title = $resource->title;
        $resource->delete();

        return back()->with('success', "Resource '{$title}' has been deleted successfully.");
    }
}
