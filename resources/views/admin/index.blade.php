@extends('layouts.app')

@section('title', 'Admin Space - CollabSphere')

@section('content')
<div class="admin-wrapper" style="display: flex; flex-direction: column; gap: 2rem;">
    
    <!-- Admin Dashboard Header -->
    <div class="header-container">
        <div class="header-title">
            <h1><i class="fa-solid fa-user-shield" style="color: var(--accent-rose);"></i> Admin & Moderator Workspace</h1>
            <p>Monitor portal activity, manage study groups, edit user clearance clearance, and remove flagged resource files.</p>
        </div>
    </div>

    <!-- Administrative Statistics Blocks -->
    <div class="stats-grid">
        <div class="stat-card glass-panel">
            <div class="stat-icon indigo">
                <i class="fa-solid fa-users"></i>
            </div>
            <div>
                <div class="stat-number">{{ $stats['total_users'] }}</div>
                <div class="stat-label">Registered Students</div>
            </div>
        </div>
        
        <div class="stat-card glass-panel">
            <div class="stat-icon violet">
                <i class="fa-solid fa-graduation-cap"></i>
            </div>
            <div>
                <div class="stat-number">{{ $stats['total_groups'] }}</div>
                <div class="stat-label">Active Study Rooms</div>
            </div>
        </div>
        
        <div class="stat-card glass-panel">
            <div class="stat-icon emerald">
                <i class="fa-solid fa-file-lines"></i>
            </div>
            <div>
                <div class="stat-number">{{ $stats['total_resources'] }}</div>
                <div class="stat-label">Shared Assets</div>
            </div>
        </div>

        <div class="stat-card glass-panel" style="border-color: rgba(244, 63, 94, 0.2);">
            <div class="stat-icon" style="background: rgba(244, 63, 94, 0.15); color: var(--accent-rose);">
                <i class="fa-solid fa-message"></i>
            </div>
            <div>
                <div class="stat-number">{{ $stats['total_messages'] }}</div>
                <div class="stat-label">Chat Submissions</div>
            </div>
        </div>
    </div>

    <!-- Admin Panel Tabs Navigator -->
    <div class="glass-panel" style="padding: 1.5rem; display: flex; flex-direction: column; gap: 1.5rem;">
        <div style="display: flex; gap: 1rem; border-bottom: 1px solid var(--border-color); padding-bottom: 1rem;">
            <button class="tab-trigger active" onclick="switchAdminTab(event, 'usersTab')">
                <i class="fa-solid fa-user-gear"></i> Manage Users
            </button>
            <button class="tab-trigger" onclick="switchAdminTab(event, 'roomsTab')">
                <i class="fa-solid fa-folder-open"></i> Manage Study Rooms
            </button>
            <button class="tab-trigger" onclick="switchAdminTab(event, 'resourcesTab')">
                <i class="fa-solid fa-file-shield"></i> Flagged & Drive Files
            </button>
        </div>

        <!-- 1. Manage Users Section -->
        <div id="usersTab" class="admin-tab-panel active">
            <h3 style="margin-bottom: 1rem; font-size: 1.15rem; font-weight: 600;">System Accounts Directory</h3>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.9rem;">
                    <thead>
                        <tr style="border-bottom: 2px solid var(--border-color); color: var(--text-secondary); font-weight: 600;">
                            <th style="padding: 0.75rem 1rem;">Student Name</th>
                            <th style="padding: 0.75rem 1rem;">Email Address</th>
                            <th style="padding: 0.75rem 1rem;">Account Created</th>
                            <th style="padding: 0.75rem 1rem;">Portal Role</th>
                            <th style="padding: 0.75rem 1rem; text-align: right;">Moderation Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                            <tr style="border-bottom: 1px solid var(--border-color); transition: var(--transition);" onmouseover="this.style.background='rgba(255,255,255,0.01)'" onmouseout="this.style.background='none'">
                                <td style="padding: 1rem; display: flex; align-items: center; gap: 0.75rem;">
                                    <div class="user-avatar" style="width: 32px; height: 32px; font-size: 0.8rem;">
                                        {{ strtoupper(substr($user->name, 0, 2)) }}
                                    </div>
                                    <span style="font-weight: 600;">{{ $user->name }}</span>
                                </td>
                                <td style="padding: 1rem; color: var(--text-secondary);">{{ $user->email }}</td>
                                <td style="padding: 1rem; color: var(--text-muted);">{{ $user->created_at->format('M d, Y') }}</td>
                                <td style="padding: 1rem;">
                                    <form action="{{ route('admin.users.updateRole', $user->id) }}" method="POST" style="display: flex; align-items: center; gap: 0.5rem;">
                                        @csrf
                                        <select name="role" onchange="this.form.submit()" class="form-control" style="padding: 0.25rem 0.5rem; font-size: 0.8rem; border-radius: 6px; width: 120px; background: var(--bg-tertiary);">
                                            <option value="student" {{ $user->role == 'student' ? 'selected' : '' }}>Student</option>
                                            <option value="moderator" {{ $user->role == 'moderator' ? 'selected' : '' }}>Moderator</option>
                                            <option value="admin" {{ $user->role == 'admin' ? 'selected' : '' }}>Admin</option>
                                        </select>
                                    </form>
                                </td>
                                <td style="padding: 1rem; text-align: right;">
                                    <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to BAN and delete user account: {{ $user->name }}? This action is irreversible.')" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn logout-btn" style="padding: 0.35rem 0.75rem; font-size: 0.75rem; width: auto; display: inline-flex;">
                                            <i class="fa-solid fa-ban"></i> Ban & Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 2. Manage Study Rooms Section -->
        <div id="roomsTab" class="admin-tab-panel" style="display: none;">
            <h3 style="margin-bottom: 1rem; font-size: 1.15rem; font-weight: 600;">Active Collaboration Rooms</h3>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.9rem;">
                    <thead>
                        <tr style="border-bottom: 2px solid var(--border-color); color: var(--text-secondary); font-weight: 600;">
                            <th style="padding: 0.75rem 1rem;">Room Name</th>
                            <th style="padding: 0.75rem 1rem;">Subject</th>
                            <th style="padding: 0.75rem 1rem;">Room Owner</th>
                            <th style="padding: 0.75rem 1rem;">Access Code</th>
                            <th style="padding: 0.75rem 1rem;">Member Count</th>
                            <th style="padding: 0.75rem 1rem; text-align: right;">Moderation Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($groups as $group)
                            <tr style="border-bottom: 1px solid var(--border-color); transition: var(--transition);" onmouseover="this.style.background='rgba(255,255,255,0.01)'" onmouseout="this.style.background='none'">
                                <td style="padding: 1rem; font-weight: 600;">{{ $group->name }}</td>
                                <td style="padding: 1rem;">
                                    <span class="group-subject" style="margin-bottom: 0; padding: 0.15rem 0.5rem; font-size: 0.7rem;">{{ $group->subject }}</span>
                                </td>
                                <td style="padding: 1rem; color: var(--text-secondary);">{{ $group->owner ? $group->owner->name : 'N/A' }}</td>
                                <td style="padding: 1rem;"><span class="group-code-badge">{{ $group->access_code }}</span></td>
                                <td style="padding: 1rem; color: var(--text-secondary);">{{ $group->members_count }} member(s)</td>
                                <td style="padding: 1rem; text-align: right;">
                                    <form action="{{ route('admin.groups.destroy', $group->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to FORCE DELETE study room: {{ $group->name }}? All whiteboard canvas images, notes, sessions, and chat strings will be permanently purged.')" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn logout-btn" style="padding: 0.35rem 0.75rem; font-size: 0.75rem; width: auto; display: inline-flex;">
                                            <i class="fa-solid fa-trash-can"></i> Force Purge
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 3. Manage Resource Drive Files Section -->
        <div id="resourcesTab" class="admin-tab-panel" style="display: none;">
            <h3 style="margin-bottom: 1rem; font-size: 1.15rem; font-weight: 600;">Resource Drive & Shared Assets</h3>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.9rem;">
                    <thead>
                        <tr style="border-bottom: 2px solid var(--border-color); color: var(--text-secondary); font-weight: 600;">
                            <th style="padding: 0.75rem 1rem;">Resource Title</th>
                            <th style="padding: 0.75rem 1rem;">Study Room</th>
                            <th style="padding: 0.75rem 1rem;">Shared By</th>
                            <th style="padding: 0.75rem 1rem;">Type</th>
                            <th style="padding: 0.75rem 1rem;">Created At</th>
                            <th style="padding: 0.75rem 1rem; text-align: right;">Moderation Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($resources as $resource)
                            <tr style="border-bottom: 1px solid var(--border-color); transition: var(--transition);" onmouseover="this.style.background='rgba(255,255,255,0.01)'" onmouseout="this.style.background='none'">
                                <td style="padding: 1rem; font-weight: 600; display: flex; align-items: center; gap: 0.5rem;">
                                    <i class="fa-solid {{ $resource->resource_type == 'link' ? 'fa-link' : 'fa-file-pdf' }}" style="color: var(--accent-indigo);"></i>
                                    <a href="{{ $resource->url }}" target="_blank" style="text-decoration: underline; color: var(--text-primary);">{{ $resource->title }}</a>
                                </td>
                                <td style="padding: 1rem; color: var(--text-secondary);">{{ $resource->studyGroup->name }}</td>
                                <td style="padding: 1rem; color: var(--text-secondary);">{{ $resource->user ? $resource->user->name : 'N/A' }}</td>
                                <td style="padding: 1rem; text-transform: uppercase; font-size: 0.75rem; font-weight: 700; color: var(--text-muted);">
                                    {{ $resource->resource_type }}
                                </td>
                                <td style="padding: 1rem; color: var(--text-muted);">{{ $resource->created_at->format('M d, Y') }}</td>
                                <td style="padding: 1rem; text-align: right;">
                                    <form action="{{ route('admin.resources.destroy', $resource->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to force delete resource file: {{ $resource->title }}? This action is irreversible.')" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn logout-btn" style="padding: 0.35rem 0.75rem; font-size: 0.75rem; width: auto; display: inline-flex;">
                                            <i class="fa-solid fa-xmark"></i> Flag & Remove
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function switchAdminTab(event, tabId) {
    // Hide all panels
    const panels = document.querySelectorAll('.admin-tab-panel');
    panels.forEach(p => p.style.display = 'none');

    // Remove active trigger layout classes
    const triggers = document.querySelectorAll('.tab-trigger');
    triggers.forEach(t => t.classList.remove('active'));

    // Show selected panel
    const selectedPanel = document.getElementById(tabId);
    if (selectedPanel) {
        selectedPanel.style.display = 'block';
    }

    // Set trigger styling
    event.currentTarget.classList.add('active');
}
</script>
@endsection
