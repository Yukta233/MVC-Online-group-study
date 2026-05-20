<div class="kanban-card" id="task-card-{{ $task->id }}" style="margin-bottom: 0.5rem;">
    <span style="font-size: 0.72rem; color: {{ $task->studyGroup ? 'var(--accent-indigo)' : 'var(--accent-emerald)' }}; background: {{ $task->studyGroup ? 'rgba(99, 102, 241, 0.1)' : 'rgba(16, 185, 129, 0.1)' }}; padding: 0.15rem 0.4rem; border-radius: 4px; display: inline-block; margin-bottom: 0.5rem;">
        {{ $task->studyGroup ? $task->studyGroup->name : 'Personal Task' }}
    </span>
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
