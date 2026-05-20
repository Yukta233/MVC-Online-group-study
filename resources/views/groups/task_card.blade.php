<div class="kanban-card" id="task-card-{{ $task->id }}">
    <h5>{{ $task->title }}</h5>
    <p>{{ $task->description ?: 'No details provided.' }}</p>
    
    <div class="kanban-card-footer">
        <span class="kanban-assignee">
            <i class="fa-solid fa-user-circle"></i>
            {{ $task->assignee ? $task->assignee->name : 'Unassigned' }}
        </span>
        
        <div class="kanban-actions">
            <select onchange="transitionTaskStatus(this, {{ $task->id }})">
                <option value="todo" {{ $task->status == 'todo' ? 'selected' : '' }}>To Do</option>
                <option value="in_progress" {{ $task->status == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                <option value="completed" {{ $task->status == 'completed' ? 'selected' : '' }}>Completed</option>
            </select>
        </div>
    </div>
</div>
