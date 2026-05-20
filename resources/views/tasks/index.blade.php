@extends('layouts.app')

@section('title', 'My Tasks - CollabSphere')

@section('content')
<div class="kanban-wrapper" style="height: auto; min-height: calc(100vh - 100px);">
    <div class="header-container" style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div class="header-title">
            <h1><i class="fa-solid fa-clipboard-list" style="color: var(--accent-indigo);"></i> My Unified Tasks Board</h1>
            <p>Track, manage, and complete all items assigned to you across all your study groups.</p>
        </div>
        <button class="btn btn-primary" onclick="openModal('globalTaskModal')" style="width: auto; padding: 0.6rem 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
            <i class="fa-solid fa-circle-plus"></i> Add Task Card
        </button>
    </div>

    <!-- Triple Column Kanban Layout -->
    <div class="kanban-board" style="min-height: 500px;">
        <!-- Column 1: To Do -->
        <div class="kanban-column">
            <div class="kanban-column-header todo">
                <span>To Do</span>
                <span class="kanban-count" id="count-todo">{{ $myTasks->where('status', 'todo')->count() }}</span>
            </div>
            <div class="kanban-cards" id="column-todo">
                @forelse($myTasks->where('status', 'todo') as $task)
                    @include('tasks.global_task_card', ['task' => $task])
                @empty
                    <!-- Placeholder when empty -->
                @endforelse
            </div>
        </div>

        <!-- Column 2: In Progress -->
        <div class="kanban-column">
            <div class="kanban-column-header in_progress">
                <span>In Progress</span>
                <span class="kanban-count" id="count-in_progress">{{ $myTasks->where('status', 'in_progress')->count() }}</span>
            </div>
            <div class="kanban-cards" id="column-in_progress">
                @forelse($myTasks->where('status', 'in_progress') as $task)
                    @include('tasks.global_task_card', ['task' => $task])
                @empty
                    <!-- Placeholder when empty -->
                @endforelse
            </div>
        </div>

        <!-- Column 3: Completed -->
        <div class="kanban-column">
            <div class="kanban-column-header completed">
                <span>Completed</span>
                <span class="kanban-count" id="count-completed">{{ $myTasks->where('status', 'completed')->count() }}</span>
            </div>
            <div class="kanban-cards" id="column-completed">
                @forelse($myTasks->where('status', 'completed') as $task)
                    @include('tasks.global_task_card', ['task' => $task])
                @empty
                    <!-- Placeholder when empty -->
                @endforelse
            </div>
        </div>
    </div>

    <!-- Global Task Creation Modal Overlay -->
    <div id="globalTaskModal" class="modal-overlay" onclick="closeModalOnOutsideClick(event, 'globalTaskModal')">
        <div class="modal-container glass-panel">
            <div class="modal-header">
                <h3><i class="fa-solid fa-square-plus" style="color: var(--accent-indigo);"></i> Create Study Task</h3>
                <button class="modal-close" onclick="closeModal('globalTaskModal')">&times;</button>
            </div>
            <form action="{{ route('global.tasks.store') }}" method="POST">
                @csrf
                <div class="form-group" style="margin-bottom: 1.25rem;">
                    <label for="study_group_id">Study Group Context</label>
                    <select name="study_group_id" id="study_group_id" class="form-control" style="background: rgba(10, 11, 16, 0.9); border: 1px solid var(--border-color); color: var(--text-primary);">
                        <option value="">None (Personal Task)</option>
                        @foreach($groups as $group)
                            <option value="{{ $group->id }}">{{ $group->name }} ({{ $group->subject }})</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="form-group" style="margin-bottom: 1.25rem;">
                    <label for="task_title">Task Title</label>
                    <input type="text" name="title" id="task_title" class="form-control" placeholder="What needs to be done?" required autocomplete="off">
                </div>
                
                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label for="task_desc">Description</label>
                    <textarea name="description" id="task_desc" class="form-control" placeholder="Add details or course context..." rows="3"></textarea>
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    <i class="fa-solid fa-circle-check"></i> Add Task to Board
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Modal Control Engine
    function openModal(modalId) {
        document.getElementById(modalId).classList.add("open");
    }

    function closeModal(modalId) {
        document.getElementById(modalId).classList.remove("open");
    }

    function closeModalOnOutsideClick(event, modalId) {
        if (event.target.id === modalId) {
            closeModal(modalId);
        }
    }

    function transitionTaskStatus(selectElement, taskId) {
        const newStatus = selectElement.value;
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        selectElement.disabled = true;
        
        fetch(`{{ url('/tasks') }}/${taskId}/status`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ status: newStatus })
        })
        .then(res => res.json())
        .then(data => {
            selectElement.disabled = false;
            if (data.success) {
                const card = document.getElementById(`task-card-${taskId}`);
                const destinationColumn = document.getElementById(`column-${newStatus}`);
                destinationColumn.appendChild(card);
                updateKanbanCounters();
            }
        })
        .catch(err => {
            selectElement.disabled = false;
            console.error("Kanban update failed:", err);
        });
    }

    function updateKanbanCounters() {
        const statuses = ['todo', 'in_progress', 'completed'];
        statuses.forEach(status => {
            const column = document.getElementById(`column-${status}`);
            const count = column.querySelectorAll('.kanban-card').length;
            document.getElementById(`count-${status}`).innerText = count;
        });
    }
</script>
@endsection
