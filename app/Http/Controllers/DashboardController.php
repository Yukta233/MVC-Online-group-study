<?php

namespace App\Http\Controllers;

use App\Models\StudySession;
use App\Models\StudyTask;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Retrieve user study groups
        $groups = $user->studyGroups()->withCount('members')->get();
        $groupIds = $groups->pluck('id');

        // Compile statistics
        $stats = [
            'groups_count' => $groups->count(),
            'upcoming_sessions' => StudySession::whereIn('study_group_id', $groupIds)
                ->where('scheduled_at', '>=', now())
                ->count(),
            'pending_tasks' => StudyTask::where('assignee_id', $user->id)
                ->where('status', '!=', 'completed')
                ->count(),
        ];

        // Fetch upcoming 5 sessions across all joined groups
        $upcomingSessions = StudySession::whereIn('study_group_id', $groupIds)
            ->where('scheduled_at', '>=', now())
            ->with('studyGroup')
            ->orderBy('scheduled_at', 'asc')
            ->take(5)
            ->get();

        // Fetch user tasks
        $myTasks = StudyTask::where('assignee_id', $user->id)
            ->with('studyGroup')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('dashboard', compact('groups', 'stats', 'upcomingSessions', 'myTasks'));
    }

    public function tasks()
    {
        $user = Auth::user();
        
        $myTasks = StudyTask::where('assignee_id', $user->id)
            ->with('studyGroup')
            ->orderBy('created_at', 'desc')
            ->get();

        // Fetch joined groups to populate the modal select dropdown
        $groups = $user->studyGroups;

        return view('tasks.index', compact('myTasks', 'groups'));
    }

    public function sessions()
    {
        $user = Auth::user();
        $groupIds = $user->studyGroups->pluck('id');

        $upcomingSessions = StudySession::whereIn('study_group_id', $groupIds)
            ->where('scheduled_at', '>=', now())
            ->with('studyGroup')
            ->orderBy('scheduled_at', 'asc')
            ->get();

        return view('sessions.index', compact('upcomingSessions'));
    }

    public function search(Request $request)
    {
        $query = trim($request->get('q', ''));
        if (strlen($query) < 2) {
            return response()->json(['results' => []]);
        }

        $user = Auth::user();
        $groupIds = $user->studyGroups->pluck('id');

        // 1. Search Groups (joined)
        $groups = $user->studyGroups()
            ->where(function($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('subject', 'like', "%{$query}%");
            })
            ->take(5)
            ->get();

        // 2. Search Resources inside joined groups
        $resources = \App\Models\Resource::whereIn('study_group_id', $groupIds)
            ->where(function($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%");
            })
            ->with('studyGroup')
            ->take(5)
            ->get();

        // 3. Search Kanban Tasks assigned to user or in joined groups
        $tasks = StudyTask::where(function($q) use ($groupIds, $user) {
                $q->whereIn('study_group_id', $groupIds)
                  ->orWhere('assignee_id', $user->id);
            })
            ->where(function($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%");
            })
            ->with('studyGroup')
            ->take(5)
            ->get();

        $results = [];

        foreach ($groups as $g) {
            $results[] = [
                'type' => 'Study Room',
                'title' => $g->name,
                'subtitle' => $g->subject,
                'url' => route('groups.show', $g->id),
                'icon' => 'fa-graduation-cap'
            ];
        }

        foreach ($resources as $r) {
            $results[] = [
                'type' => 'Resource',
                'title' => $r->title,
                'subtitle' => "Drive file inside " . $r->studyGroup->name,
                'url' => route('groups.show', $r->study_group_id),
                'icon' => $r->resource_type == 'link' ? 'fa-link' : 'fa-file-pdf'
            ];
        }

        foreach ($tasks as $t) {
            $results[] = [
                'type' => 'Task',
                'title' => $t->title,
                'subtitle' => "Kanban status: " . strtoupper($t->status) . " inside " . ($t->studyGroup ? $t->studyGroup->name : 'Personal Task'),
                'url' => $t->study_group_id ? route('groups.show', $t->study_group_id) : route('global.tasks'),
                'icon' => 'fa-clipboard-list'
            ];
        }

        return response()->json(['results' => $results]);
    }

    public function notifications(Request $request)
    {
        $user = Auth::user();
        $groupIds = $user->studyGroups->pluck('id');

        // 1. Retrieve study sessions created in the last 7 days
        $sessions = StudySession::whereIn('study_group_id', $groupIds)
            ->where('created_at', '>=', now()->subDays(7))
            ->with('studyGroup')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => 'session_' . $item->id,
                    'type' => 'Session Scheduled',
                    'title' => $item->title,
                    'message' => "A new live session has been scheduled in " . $item->studyGroup->name,
                    'time' => $item->created_at->diffForHumans(),
                    'created_at' => $item->created_at->toIso8601String(),
                    'url' => route('global.sessions'),
                    'icon' => 'fa-calendar-days'
                ];
            });

        // 2. Retrieve resources uploaded in the last 7 days
        $resources = \App\Models\Resource::whereIn('study_group_id', $groupIds)
            ->where('created_at', '>=', now()->subDays(7))
            ->with('studyGroup', 'user')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => 'resource_' . $item->id,
                    'type' => 'Resource Shared',
                    'title' => $item->title,
                    'message' => ($item->user ? $item->user->name : 'Someone') . " uploaded a resource in " . $item->studyGroup->name,
                    'time' => $item->created_at->diffForHumans(),
                    'created_at' => $item->created_at->toIso8601String(),
                    'url' => route('groups.show', $item->study_group_id),
                    'icon' => 'fa-file-lines'
                ];
            });

        // 3. Retrieve quizzes created in the last 7 days
        $quizzes = \App\Models\Quiz::whereIn('study_group_id', $groupIds)
            ->where('created_at', '>=', now()->subDays(7))
            ->with('studyGroup')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => 'quiz_' . $item->id,
                    'type' => 'Quiz Created',
                    'title' => $item->title,
                    'message' => "A new quiz has been published in " . $item->studyGroup->name,
                    'time' => $item->created_at->diffForHumans(),
                    'created_at' => $item->created_at->toIso8601String(),
                    'url' => route('groups.show', $item->study_group_id),
                    'icon' => 'fa-brain'
                ];
            });

        // 4. Retrieve tasks assigned to user in the last 7 days
        $tasks = StudyTask::where('assignee_id', $user->id)
            ->where('created_at', '>=', now()->subDays(7))
            ->with('studyGroup')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => 'task_' . $item->id,
                    'type' => 'Task Assigned',
                    'title' => $item->title,
                    'message' => "You have been assigned a task: " . $item->title . " in " . $item->studyGroup->name,
                    'time' => $item->created_at->diffForHumans(),
                    'created_at' => $item->created_at->toIso8601String(),
                    'url' => route('groups.show', $item->study_group_id),
                    'icon' => 'fa-clipboard-list'
                ];
            });

        // Merge all, sort by created_at desc, and limit to 15 items
        $notifications = $sessions->concat($resources)->concat($quizzes)->concat($tasks)
            ->sortByDesc('created_at')
            ->values()
            ->take(15);

        return response()->json([
            'notifications' => $notifications,
            'count' => $notifications->count(),
        ]);
    }
}

