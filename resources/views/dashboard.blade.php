@extends('layouts.app')

@section('title', 'Dashboard - CollabSphere')

@section('content')
<div class="dashboard-wrapper">
    
    <!-- Dashboard Header -->
    <div class="header-container">
        <div class="header-title">
            <h1>Hello, {{ Auth::user()->name }}! 👋</h1>
            <p>Welcome to your study space. Ready to collaborate with your team today?</p>
        </div>
    </div>

    <!-- Quick statistics summary indicators -->
    <div class="stats-grid">
        <div class="stat-card glass-panel">
            <div class="stat-icon indigo">
                <i class="fa-solid fa-graduation-cap"></i>
            </div>
            <div>
                <div class="stat-number">{{ $stats['groups_count'] }}</div>
                <div class="stat-label">Study Groups</div>
            </div>
        </div>
        
        <div class="stat-card glass-panel">
            <div class="stat-icon violet">
                <i class="fa-solid fa-video"></i>
            </div>
            <div>
                <div class="stat-number">{{ $stats['upcoming_sessions'] }}</div>
                <div class="stat-label">Upcoming Sessions</div>
            </div>
        </div>
        
        <div class="stat-card glass-panel">
            <div class="stat-icon emerald">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <div>
                <div class="stat-number">{{ $stats['pending_tasks'] }}</div>
                <div class="stat-label">Pending Tasks</div>
            </div>
        </div>
    </div>

    <!-- Split forms: Join or Create Room -->
    <div class="dashboard-actions">
        <!-- Join Group Form -->
        <div class="action-form-card glass-panel">
            <h2><i class="fa-solid fa-right-to-bracket" style="color: var(--accent-violet);"></i> Join a Study Group</h2>
            <p style="color: var(--text-secondary); font-size: 0.85rem; margin-bottom: 1.25rem;">
                Enter a unique 8-character access code provided by your peers to instantly join their study group.
            </p>
            <form action="{{ route('groups.join') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="access_code">Access Code</label>
                    <input type="text" name="access_code" id="access_code" class="form-control" placeholder="e.g. ADS-2026" style="text-transform: uppercase;" required>
                </div>
                <button type="submit" class="btn btn-secondary">
                    <i class="fa-solid fa-user-plus"></i> Join Group
                </button>
            </form>
        </div>

        <!-- Create Group Form -->
        <div class="action-form-card glass-panel">
            <h2><i class="fa-solid fa-square-plus" style="color: var(--accent-indigo);"></i> Create Study Group</h2>
            <p style="color: var(--text-secondary); font-size: 0.85rem; margin-bottom: 1.25rem;">
                Start a fresh room, establish study subjects, and get a shareable code to invite your fellow classmates.
            </p>
            <form action="{{ route('groups.create') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="name">Group Room Name</label>
                    <input type="text" name="name" id="name" class="form-control" placeholder="e.g. Advanced Calculus Study" required>
                </div>
                
                <div class="form-group">
                    <label for="subject">Subject Course</label>
                    <input type="text" name="subject" id="subject" class="form-control" placeholder="e.g. Mathematics" required>
                </div>

                <div class="form-group">
                    <label for="description">Brief Description</label>
                    <textarea name="description" id="description" class="form-control" placeholder="What are the goals of this group?"></textarea>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-circle-check"></i> Create Room
                </button>
            </form>
        </div>
    </div>

    <!-- Triple Panel Block: Study Groups, Upcoming Sessions, and Kanban checklist -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2.5rem; align-items: start;">
        <!-- Left Side: My Groups -->
        <div>
            <h2 class="section-title"><i class="fa-solid fa-users" style="color: var(--accent-indigo);"></i> My Study Rooms</h2>
            
            @if($groups->isEmpty())
                <div class="glass-panel" style="padding: 3rem; text-align: center; color: var(--text-secondary);">
                    <i class="fa-solid fa-folder-open" style="font-size: 2.5rem; color: var(--text-muted); margin-bottom: 1rem; display: block;"></i>
                    <p style="font-weight: 500;">You haven't joined any study groups yet.</p>
                    <p style="font-size: 0.82rem; color: var(--text-muted); margin-top: 0.25rem;">Create a new one or join using an access code above!</p>
                </div>
            @else
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    @foreach($groups as $group)
                        <div class="group-card glass-panel glass-card">
                            <span class="group-subject">{{ $group->subject }}</span>
                            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                                <h3>{{ $group->name }}</h3>
                                <span class="group-code-badge">{{ $group->access_code }}</span>
                            </div>
                            <p class="group-description">{{ $group->description ?: 'No description provided.' }}</p>
                            <div class="group-meta">
                                <span><i class="fa-solid fa-user-group"></i> {{ $group->members_count }} {{ Str::plural('member', $group->members_count) }}</span>
                                <a href="{{ route('groups.show', $group->id) }}" class="btn btn-primary" style="width: auto; padding: 0.4rem 1rem; font-size: 0.8rem; border-radius: 6px;">
                                    Enter Room <i class="fa-solid fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Right Side: Scheduled Sessions & Personal Tasks -->
        <div style="display: flex; flex-direction: column; gap: 2rem;">
            <!-- Upcoming Sessions Widget -->
            <div>
                <h2 class="section-title"><i class="fa-solid fa-calendar-days" style="color: var(--accent-violet);"></i> Upcoming Live Sessions</h2>
                <div class="glass-panel" style="padding: 1.5rem; display: flex; flex-direction: column; gap: 1rem;">
                    @if($upcomingSessions->isEmpty())
                        <p style="text-align: center; color: var(--text-secondary); font-size: 0.85rem; padding: 1rem 0;">
                            No study sessions scheduled for your groups.
                        </p>
                    @else
                        @foreach($upcomingSessions as $session)
                            <div style="border-left: 3px solid var(--accent-violet); padding-left: 1rem; margin-bottom: 0.5rem;">
                                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                                    <h4 style="font-size: 0.95rem; font-weight: 600;">{{ $session->title }}</h4>
                                    <span style="font-size: 0.72rem; color: var(--accent-violet); font-weight: 600; background: rgba(139, 92, 246, 0.1); padding: 0.15rem 0.4rem; border-radius: 4px;">
                                        {{ $session->studyGroup->name }}
                                    </span>
                                </div>
                                <div style="font-size: 0.78rem; color: var(--text-secondary); margin: 0.25rem 0;">
                                    <i class="fa-solid fa-clock"></i> {{ $session->scheduled_at->format('M d, Y @ h:i A') }} ({{ $session->duration_minutes }}m)
                                </div>
                                @if($session->meeting_link)
                                    <a href="{{ $session->meeting_link }}" target="_blank" class="resource-link-btn" style="font-size: 0.78rem;">
                                        <i class="fa-solid fa-video"></i> Launch Meeting Link
                                    </a>
                                @endif
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>

            <!-- Assigned Tasks Checklist -->
            <div>
                <h2 class="section-title"><i class="fa-solid fa-clipboard-list" style="color: var(--accent-emerald);"></i> My Kanban Tasks</h2>
                <div class="glass-panel" style="padding: 1.5rem; display: flex; flex-direction: column; gap: 1rem;">
                    @if($myTasks->isEmpty())
                        <p style="text-align: center; color: var(--text-secondary); font-size: 0.85rem; padding: 1rem 0;">
                            No task cards currently assigned to you.
                        </p>
                    @else
                        @foreach($myTasks as $task)
                            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem; margin-bottom: 0.25rem;">
                                <div>
                                    <h4 style="font-size: 0.9rem; font-weight: 600;">{{ $task->title }}</h4>
                                    <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.15rem;">
                                        Room: <strong>{{ $task->studyGroup ? $task->studyGroup->name : 'Personal Task' }}</strong>
                                    </div>
                                </div>
                                <span style="font-size: 0.7rem; font-weight: 700; text-transform: uppercase; padding: 0.2rem 0.5rem; border-radius: 4px; 
                                    @if($task->status == 'todo') background: rgba(99, 102, 241, 0.15); color: var(--accent-indigo); @elseif($task->status == 'in_progress') background: rgba(245, 158, 11, 0.15); color: var(--accent-amber); @else background: rgba(16, 185, 129, 0.15); color: var(--accent-emerald); @endif">
                                    {{ str_replace('_', ' ', $task->status) }}
                                </span>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
