@extends('layouts.app')

@section('title') {{ $group->name }} - Study Workspace @endsection

@section('sidebar_menu')
    <li style="margin-top: 1.5rem;">
        <div class="sidebar-section-title">Active Study Room</div>
        <div style="background: rgba(255, 255, 255, 0.03); border: 1px solid var(--border-color); padding: 1rem; border-radius: var(--radius-sm); margin-bottom: 1.5rem;">
            <div style="font-size: 0.8rem; color: var(--text-secondary); font-weight: 600;">Access Code</div>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 0.35rem;">
                <span id="accessCodeText" style="font-family: monospace; font-size: 1.05rem; font-weight: 700; color: var(--accent-violet);">{{ $group->access_code }}</span>
                <button onclick="copyAccessCode()" class="resource-delete-btn" style="color: var(--accent-indigo);" title="Copy Code">
                    <i class="fa-solid fa-copy" id="copyIcon"></i>
                </button>
            </div>
        </div>
    </li>
    
    <li>
        <div class="sidebar-section-title">Room Members ({{ $group->members->count() }})</div>
        <div class="members-list">
            @foreach($group->members as $member)
                <div class="member-item">
                    <div class="member-avatar">
                        {{ strtoupper(substr($member->name, 0, 2)) }}
                    </div>
                    <div class="member-name">{{ $member->name }}</div>
                    @if($member->id === $group->owner_id)
                        <span class="member-owner-badge">Host</span>
                    @endif
                </div>
            @endforeach
        </div>
    </li>
@endsection

@section('content')
<div class="workspace-main">
    
    <!-- Workspace Room Header -->
    <div style="margin-bottom: 1.5rem;">
        <span class="group-subject">{{ $group->subject }}</span>
        <h1 style="font-size: 1.85rem; font-weight: 700; margin-top: 0.25rem;">{{ $group->name }}</h1>
        <p style="color: var(--text-secondary); font-size: 0.92rem; margin-top: 0.35rem;">{{ $group->description ?: 'Welcome to your collaborative team room.' }}</p>
    </div>

    <!-- Tabbed Navigation Bar -->
    <div class="workspace-tabs">
        <button class="tab-trigger active" onclick="switchTab(event, 'chat')">
            <i class="fa-solid fa-comments"></i> Chat Room
        </button>
        <button class="tab-trigger" onclick="switchTab(event, 'resources')">
            <i class="fa-solid fa-folder-open"></i> Resources Drive
        </button>
        <button class="tab-trigger" onclick="switchTab(event, 'notes')">
            <i class="fa-solid fa-file-pen"></i> Shared Notepad
        </button>
        <button class="tab-trigger" onclick="switchTab(event, 'tasks')">
            <i class="fa-solid fa-clipboard-list"></i> Kanban Tasks
        </button>
        <button class="tab-trigger" onclick="switchTab(event, 'video')">
            <i class="fa-solid fa-video"></i> Live Call
        </button>
        <button class="tab-trigger" onclick="switchTab(event, 'quizzes')">
            <i class="fa-solid fa-graduation-cap"></i> Quizzes & Cards
        </button>
        <button class="tab-trigger" onclick="switchTab(event, 'analytics')">
            <i class="fa-solid fa-chart-line"></i> Analytics
        </button>
    </div>

    <!-- Workspace Tab Panels -->
    <div class="tab-content">
        
        <!-- PANEL 1: Chat Room -->
        <div id="panel-chat" class="tab-panel active">
            <div class="chat-wrapper">
                <div class="chat-history" id="chatHistory">
                    @if($group->chatMessages->isEmpty())
                        <div id="noChatAlert" style="text-align: center; color: var(--text-secondary); padding: 3rem 0;">
                            <i class="fa-solid fa-comments" style="font-size: 2.5rem; color: var(--text-muted); margin-bottom: 1rem; display: block;"></i>
                            <p style="font-weight: 500;">Welcome to the chat room! Say hello to your group members.</p>
                        </div>
                    @else
                        @foreach($group->chatMessages as $msg)
                            <div class="chat-bubble-container {{ $msg->user_id === Auth::id() ? 'me' : '' }}" data-msg-id="{{ $msg->id }}">
                                <div>
                                    <span class="chat-sender">{{ $msg->user->name }}</span>
                                    <div class="chat-bubble">
                                        {{ $msg->message }}
                                    </div>
                                    <span class="chat-time">{{ now('Asia/Kolkata')->format('h:i A') }}</span>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
                
                <div class="chat-input-bar">
                    <form id="chatForm" class="chat-form" onsubmit="sendChatMessage(event)">
                        @csrf
                        <input type="text" id="chatInput" class="form-control chat-input" placeholder="Type a message..." required autocomplete="off">
                        <button type="submit" class="btn btn-primary chat-submit-btn">
                            <i class="fa-solid fa-paper-plane"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- PANEL 2: Resource Drive -->
        <div id="panel-resources" class="tab-panel">
            <div class="drive-wrapper">
                <div class="drive-header">
                    <h3 style="font-size: 1.2rem; font-weight: 600;"><i class="fa-solid fa-folder-open" style="color: var(--accent-indigo);"></i> Shared Resources Guide</h3>
                    <button class="btn btn-primary" onclick="openModal('resourceModal')" style="width: auto; padding: 0.5rem 1.25rem;">
                        <i class="fa-solid fa-plus"></i> Share Resource
                    </button>
                </div>

                @if($group->resources->isEmpty())
                    <div class="glass-panel" style="padding: 4rem; text-align: center; color: var(--text-secondary);">
                        <i class="fa-solid fa-link" style="font-size: 2.5rem; color: var(--text-muted); margin-bottom: 1rem; display: block;"></i>
                        <p style="font-weight: 500;">No shared resources inside this room yet.</p>
                        <p style="font-size: 0.82rem; color: var(--text-muted); margin-top: 0.25rem;">Upload slides, guides, or external URLs above!</p>
                    </div>
                @else
                    <div class="resources-list">
                        @foreach($group->resources as $res)
                            <div class="glass-panel glass-card resource-card">
                                <div class="resource-top">
                                    <span class="resource-badge">{{ $res->resource_type }}</span>
                                    <h4>
                                        @if($res->resource_type == 'link')
                                            <i class="fa-solid fa-link"></i>
                                        @else
                                            <i class="fa-solid fa-file-pdf"></i>
                                        @endif
                                        {{ $res->title }}
                                    </h4>
                                    <p class="resource-desc">{{ $res->description ?: 'No description provided.' }}</p>
                                </div>
                                <div class="resource-footer">
                                    <span>Uploaded by {{ $res->user->name }}</span>
                                    <div style="display: flex; gap: 0.75rem; align-items: center;">
                                        <a href="{{ $res->url }}" target="_blank" class="resource-link-btn">
                                            Open <i class="fa-solid fa-external-link"></i>
                                        </a>
                                        @if($res->user_id === Auth::id() || $group->owner_id === Auth::id())
                                            <form action="{{ route('resources.destroy', $res->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this resource?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="resource-delete-btn" title="Delete Resource">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- PANEL 3: Shared Notes Notepad -->
        <div id="panel-notes" class="tab-panel">
            <div class="notes-wrapper">
                <div class="notes-header">
                    <h3 style="font-size: 1.2rem; font-weight: 600;"><i class="fa-solid fa-file-pen" style="color: var(--accent-indigo);"></i> Collaborative Notepad</h3>
                    <div class="notes-status" id="noteSaveStatus">
                        @if($group->studyNote)
                            Last saved at {{ $group->studyNote->updated_at->format('h:i A') }} (Edited by {{ $group->studyNote->lastEditor->name }})
                        @else
                            No edits made yet.
                        @endif
                    </div>
                </div>
                
                <textarea id="notesArea" class="notes-editor" oninput="triggerNoteAutoSave()" placeholder="Type notes here... Auto-saves automatically! Supports Markdown format.">{{ $group->studyNote ? $group->studyNote->content : '' }}</textarea>
            </div>
        </div>

        <!-- PANEL 4: Kanban Tasks Board -->
        <div id="panel-tasks" class="tab-panel">
            <div class="kanban-wrapper">
                <div class="kanban-header">
                    <h3 style="font-size: 1.2rem; font-weight: 600;"><i class="fa-solid fa-clipboard-list" style="color: var(--accent-indigo);"></i> Study Tasks Board</h3>
                    <button class="btn btn-primary" onclick="openModal('taskModal')" style="width: auto; padding: 0.5rem 1.25rem;">
                        <i class="fa-solid fa-plus"></i> Add Card
                    </button>
                </div>

                <div class="kanban-board">
                    <!-- Column 1: To Do -->
                    <div class="kanban-column">
                        <div class="kanban-column-header todo">
                            <span>To Do</span>
                            <span class="kanban-count" id="count-todo">{{ $group->studyTasks->where('status', 'todo')->count() }}</span>
                        </div>
                        <div class="kanban-cards" id="column-todo">
                            @foreach($group->studyTasks->where('status', 'todo') as $task)
                                @include('groups.task_card', ['task' => $task])
                            @endforeach
                        </div>
                    </div>

                    <!-- Column 2: In Progress -->
                    <div class="kanban-column">
                        <div class="kanban-column-header in_progress">
                            <span>In Progress</span>
                            <span class="kanban-count" id="count-in_progress">{{ $group->studyTasks->where('status', 'in_progress')->count() }}</span>
                        </div>
                        <div class="kanban-cards" id="column-in_progress">
                            @foreach($group->studyTasks->where('status', 'in_progress') as $task)
                                @include('groups.task_card', ['task' => $task])
                            @endforeach
                        </div>
                    </div>

                    <!-- Column 3: Completed -->
                    <div class="kanban-column">
                        <div class="kanban-column-header completed">
                            <span>Completed</span>
                            <span class="kanban-count" id="count-completed">{{ $group->studyTasks->where('status', 'completed')->count() }}</span>
                        </div>
                        <div class="kanban-cards" id="column-completed">
                            @foreach($group->studyTasks->where('status', 'completed') as $task)
                                @include('groups.task_card', ['task' => $task])
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

<!-- PANEL 6: Live Video Call (Jitsi Meet WebRTC) -->
        <div id="panel-video" class="tab-panel">
            <div class="glass-panel" style="padding: 2.25rem; text-align: center; margin-bottom: 1.5rem;">
                <div style="display: inline-flex; width: 60px; height: 60px; border-radius: 50%; background: rgba(139, 92, 246, 0.1); color: var(--accent-violet); align-items: center; justify-content: center; font-size: 1.75rem; margin-bottom: 1rem;">
                    <i class="fa-solid fa-video"></i>
                </div>
                <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 0.5rem; color: var(--text-primary);">WebRTC Live Video Call</h3>
                <p style="color: var(--text-secondary); font-size: 0.88rem; max-width: 500px; margin: 0 auto 1.5rem auto; line-height: 1.5;">
                    Launch a high-definition study call with fellow classmates. Includes full screen sharing, webcam controls, real-time typing chat, and zero setup!
                </p>
                <div style="display: flex; justify-content: center; gap: 1rem;">
                    <button class="btn btn-primary" id="startCallBtn" onclick="initializeJitsiCall()" style="width: auto; padding: 0.6rem 2rem; font-weight: 600;">
                        <i class="fa-solid fa-phone"></i> Start / Join Live Call
                    </button>
                    <button class="btn btn-danger" id="endCallBtn" onclick="terminateJitsiCall()" style="width: auto; padding: 0.6rem 2rem; font-weight: 600; display: none;">
                        <i class="fa-solid fa-phone-slash"></i> Disconnect / Leave
                    </button>
                </div>
            </div>
            
            <div id="jitsiContainerWrapper" style="display: none; border-radius: 12px; overflow: hidden; border: 1px solid var(--border-color); background: #000; box-shadow: 0 12px 40px 0 rgba(0, 0, 0, 0.5);">
                <div id="jitsiIframeContainer" style="height: 600px; width: 100%;"></div>
            </div>
        </div>



        <!-- PANEL 9: Academy Hub (Quizzes, Flashcards & Leaderboard) -->
        <div id="panel-quizzes" class="tab-panel">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; align-items: start; margin-bottom: 1.5rem;">
                
                <!-- COLUMN A: Collaborative Quizzes -->
                <div class="glass-panel" style="padding: 1.5rem; border: 1px solid var(--border-color); display: flex; flex-direction: column; gap: 1.25rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem;">
                        <div>
                            <h3 style="font-size: 1.25rem; font-weight: 700; color: var(--accent-indigo);"><i class="fa-solid fa-circle-question"></i> Quiz Center</h3>
                            <p style="color: var(--text-secondary); font-size: 0.8rem; margin-top: 0.15rem;">Challenge your skills and test comprehension.</p>
                        </div>
                        <button class="btn btn-primary" onclick="openModal('createQuizModal')" style="width: auto; padding: 0.45rem 1rem; font-size: 0.75rem; font-weight: 600;">
                            <i class="fa-solid fa-plus"></i> Create Quiz
                        </button>
                    </div>

                    @if($group->quizzes->isEmpty())
                        <div style="text-align: center; padding: 3rem 0; color: var(--text-secondary);">
                            <i class="fa-solid fa-receipt" style="font-size: 2.25rem; color: var(--text-muted); margin-bottom: 0.75rem; display: block;"></i>
                            <p style="font-weight: 500; font-size: 0.9rem;">No quizzes created yet.</p>
                            <p style="font-size: 0.78rem; color: var(--text-muted); margin-top: 0.15rem;">Click Create Quiz to publish the first challenge!</p>
                        </div>
                    @else
                        <div style="display: flex; flex-direction: column; gap: 0.85rem; max-height: 400px; overflow-y: auto; padding-right: 0.25rem;">
                            @foreach($group->quizzes as $quiz)
                                <div class="glass-panel" style="padding: 1rem; background: rgba(255, 255, 255, 0.02); border: 1px solid rgba(255, 255, 255, 0.05); border-radius: 8px; display: flex; justify-content: space-between; align-items: center;">
                                    <div>
                                        <h4 style="font-size: 0.95rem; font-weight: 600; color: var(--text-primary);">{{ $quiz->title }}</h4>
                                        <p style="color: var(--text-muted); font-size: 0.78rem; margin-top: 0.15rem; max-width: 260px; text-overflow: ellipsis; overflow: hidden; white-space: nowrap;">{{ $quiz->description ?: 'No description.' }}</p>
                                        <div style="display: flex; gap: 0.75rem; font-size: 0.75rem; color: var(--text-secondary); margin-top: 0.4rem; align-items: center;">
                                            <span><i class="fa-solid fa-list-check" style="color: var(--accent-violet);"></i> {{ $quiz->questions->count() }} Questions</span>
                                            <span><i class="fa-solid fa-clock" style="color: var(--accent-amber);"></i> {{ $quiz->time_limit_minutes }}m</span>
                                        </div>
                                    </div>
                                    <div>
                                        <button class="btn btn-primary" onclick="startQuizTaking({{ $quiz->id }}, '{{ addslashes($quiz->title) }}', {{ $quiz->time_limit_minutes }}, {{ json_encode($quiz->questions) }})" style="padding: 0.45rem 1rem; font-size: 0.78rem; border-radius: 6px; width: auto; font-weight: 600;">
                                            <i class="fa-solid fa-play"></i> Start
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- COLUMN B: Interactive Flashcards -->
                <div class="glass-panel" style="padding: 1.5rem; border: 1px solid var(--border-color); display: flex; flex-direction: column; gap: 1.25rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem;">
                        <div>
                            <h3 style="font-size: 1.25rem; font-weight: 700; color: var(--accent-emerald);"><i class="fa-solid fa-clone"></i> Study Flashcards</h3>
                            <p style="color: var(--text-secondary); font-size: 0.8rem; margin-top: 0.15rem;">Flip terms and memorize core definitions.</p>
                        </div>
                        <button class="btn btn-primary" onclick="openModal('createCardModal')" style="width: auto; padding: 0.45rem 1rem; font-size: 0.75rem; font-weight: 600;">
                            <i class="fa-solid fa-plus"></i> Add Card
                        </button>
                    </div>

                    @if($group->flashcards->isEmpty())
                        <div style="text-align: center; padding: 3rem 0; color: var(--text-secondary);">
                            <i class="fa-solid fa-clone" style="font-size: 2.25rem; color: var(--text-muted); margin-bottom: 0.75rem; display: block;"></i>
                            <p style="font-weight: 500; font-size: 0.9rem;">No flashcards in deck.</p>
                            <p style="font-size: 0.78rem; color: var(--text-muted); margin-top: 0.15rem;">Click Add Card to build your memory deck!</p>
                        </div>
                    @else
                        <!-- 3D Flipping Flashcard Workspace -->
                        <div style="display: flex; flex-direction: column; align-items: center; gap: 1rem;">
                            <!-- Card Container -->
                            <div class="flashcard-scene" onclick="flipFlashcard()" style="perspective: 1000px; width: 100%; max-width: 320px; height: 200px; cursor: pointer;">
                                <div class="flashcard-inner" id="flashcardInner" style="position: relative; width: 100%; height: 100%; text-align: center; transition: transform 0.6s cubic-bezier(0.4, 0, 0.2, 1); transform-style: preserve-3d;">
                                    <!-- Front Face -->
                                    <div class="flashcard-front glass-panel" style="position: absolute; width: 100%; height: 100%; backface-visibility: hidden; display: flex; flex-direction: column; justify-content: center; align-items: center; padding: 1.5rem; background: linear-gradient(135deg, rgba(139, 92, 246, 0.1), rgba(10, 11, 16, 0.9)); border: 1px solid rgba(139, 92, 246, 0.25); border-radius: 12px; box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37); box-sizing: border-box;">
                                        <span style="font-size: 0.7rem; color: var(--accent-violet); text-transform: uppercase; font-weight: 700; letter-spacing: 0.1em; margin-bottom: 0.75rem;">Question / Term</span>
                                        <p id="cardQuestionText" style="font-size: 1.1rem; font-weight: 600; line-height: 1.4; color: var(--text-primary); margin: 0;"></p>
                                        <span style="font-size: 0.68rem; color: var(--text-muted); margin-top: 1rem;"><i class="fa-solid fa-arrows-rotate"></i> Click card to flip</span>
                                    </div>
                                    <!-- Back Face -->
                                    <div class="flashcard-back glass-panel" style="position: absolute; width: 100%; height: 100%; backface-visibility: hidden; display: flex; flex-direction: column; justify-content: center; align-items: center; padding: 1.5rem; background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(10, 11, 16, 0.9)); border: 1px solid rgba(16, 185, 129, 0.25); border-radius: 12px; transform: rotateY(180deg); box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37); box-sizing: border-box;">
                                        <span style="font-size: 0.7rem; color: var(--accent-emerald); text-transform: uppercase; font-weight: 700; letter-spacing: 0.1em; margin-bottom: 0.75rem;">Answer / Definition</span>
                                        <p id="cardAnswerText" style="font-size: 1.1rem; font-weight: 600; line-height: 1.4; color: var(--text-primary); margin: 0;"></p>
                                        <span style="font-size: 0.68rem; color: var(--text-muted); margin-top: 1rem;"><i class="fa-solid fa-arrows-rotate"></i> Click to flip back</span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Deck Navigation -->
                            <div style="display: flex; gap: 1rem; align-items: center;">
                                <button class="btn" onclick="prevFlashcard()" style="width: auto; padding: 0.4rem 1rem; border-radius: 6px; background: rgba(255, 255, 255, 0.03); border: 1px solid var(--border-color); cursor: pointer;"><i class="fa-solid fa-chevron-left"></i> Prev</button>
                                <span style="font-size: 0.8rem; font-family: monospace; color: var(--text-muted);" id="deckCounter">1 of 1</span>
                                <button class="btn" onclick="nextFlashcard()" style="width: auto; padding: 0.4rem 1rem; border-radius: 6px; background: rgba(255, 255, 255, 0.03); border: 1px solid var(--border-color); cursor: pointer;">Next <i class="fa-solid fa-chevron-right"></i></button>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Gamified Study Leaderboard -->
            <div class="glass-panel" style="padding: 1.5rem; border: 1px solid var(--border-color);">
                <h3 style="font-size: 1.25rem; font-weight: 700; color: var(--accent-amber); border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem; margin-bottom: 1rem;"><i class="fa-solid fa-trophy"></i> Study Room Scoreboard</h3>
                
                @php
                    // Collect all attempts in this group and group them by user to calculate leader rankings!
                    $quizIds = $group->quizzes->pluck('id');
                    $attempts = App\Models\QuizAttempt::whereIn('quiz_id', $quizIds)->with('user')->get();
                    
                    $rankings = $attempts->groupBy('user_id')->map(function($userAttempts) {
                        $totalScore = $userAttempts->sum('score');
                        $totalQuestions = $userAttempts->sum('total_questions');
                        $userName = $userAttempts->first()->user->name;
                        return [
                            'name' => $userName,
                            'points' => $totalScore,
                            'total' => $totalQuestions,
                            'accuracy' => $totalQuestions > 0 ? round(($totalScore / $totalQuestions) * 100) : 0,
                            'quizzes_taken' => $userAttempts->count()
                        ];
                    })->sortByDesc('points');
                @endphp

                @if($rankings->isEmpty())
                    <div style="text-align: center; color: var(--text-secondary); padding: 2rem 0;">
                        <i class="fa-solid fa-award" style="font-size: 2rem; color: var(--text-muted); margin-bottom: 0.5rem; display: block;"></i>
                        <p style="font-size: 0.85rem;">No scoreboard rankings generated yet. Complete quizzes to climb the leaderboard!</p>
                    </div>
                @else
                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse; font-size: 0.85rem; text-align: left;">
                            <thead>
                                <tr style="border-bottom: 1px solid var(--border-color); color: var(--text-muted);">
                                    <th style="padding: 0.75rem 0.5rem;">Rank</th>
                                    <th style="padding: 0.75rem 1rem;">Student Name</th>
                                    <th style="padding: 0.75rem 1rem; text-align: center;">Quizzes Taken</th>
                                    <th style="padding: 0.75rem 1rem; text-align: center;">Total Correct</th>
                                    <th style="padding: 0.75rem 1rem; text-align: center;">Avg Accuracy</th>
                                    <th style="padding: 0.75rem 0.5rem; text-align: right;">Status Badge</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $rank = 1; @endphp
                                @foreach($rankings as $rankRow)
                                    <tr style="border-bottom: 1px solid rgba(255, 255, 255, 0.03); transition: background 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.01)'" onmouseout="this.style.background='transparent'">
                                        <td style="padding: 0.85rem 0.5rem; font-weight: 700;">
                                            @if($rank === 1)
                                                <span style="color: #fbbf24; font-size: 1rem;"><i class="fa-solid fa-crown"></i> 1st</span>
                                            @elseif($rank === 2)
                                                <span style="color: #cbd5e1;">2nd</span>
                                            @elseif($rank === 3)
                                                <span style="color: #b45309;">3rd</span>
                                            @else
                                                #{{ $rank }}
                                            @endif
                                        </td>
                                        <td style="padding: 0.85rem 1rem; font-weight: 600; color: var(--text-primary)">{{ $rankRow['name'] }}</td>
                                        <td style="padding: 0.85rem 1rem; text-align: center;">{{ $rankRow['quizzes_taken'] }}</td>
                                        <td style="padding: 0.85rem 1rem; text-align: center; color: var(--accent-indigo); font-weight: 700;">{{ $rankRow['points'] }} / {{ $rankRow['total'] }}</td>
                                        <td style="padding: 0.85rem 1rem; text-align: center;">
                                            <span style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.2); color: #34d399; padding: 0.2rem 0.5rem; border-radius: 4px; font-family: monospace;">{{ $rankRow['accuracy'] }}%</span>
                                        </td>
                                        <td style="padding: 0.85rem 0.5rem; text-align: right;">
                                            @if($rankRow['accuracy'] >= 90)
                                                <span style="font-size: 0.72rem; background: linear-gradient(135deg, #f59e0b, #ef4444); color: #fff; padding: 0.25rem 0.6rem; border-radius: 12px; font-weight: 700; box-shadow: 0 0 10px rgba(245, 158, 11, 0.3);">Grandmaster</span>
                                            @elseif($rankRow['accuracy'] >= 75)
                                                <span style="font-size: 0.72rem; background: linear-gradient(135deg, #6366f1, #8b5cf6); color: #fff; padding: 0.25rem 0.6rem; border-radius: 12px; font-weight: 700;">Scholar</span>
                                            @else
                                                <span style="font-size: 0.72rem; background: rgba(255,255,255,0.05); border: 1px solid var(--border-color); color: var(--text-secondary); padding: 0.25rem 0.6rem; border-radius: 12px;">Apprentice</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @php $rank++; @endphp
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <!-- PANEL 10: Room Analytics (Chart.js Dashboard) -->
        <div id="panel-analytics" class="tab-panel">
            <div class="glass-panel" style="padding: 1.5rem; margin-bottom: 1.5rem; border: 1px solid var(--border-color);">
                <h3 style="font-size: 1.25rem; font-weight: 700; color: var(--accent-indigo);"><i class="fa-solid fa-chart-line"></i> Room Analytics & Engagement</h3>
                <p style="color: var(--text-secondary); font-size: 0.82rem; margin-top: 0.25rem;">Monitor student contributions, task progression, quiz accuracy, and general room activity.</p>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem; margin-bottom: 1.5rem;">
                <!-- Summary Card 1 -->
                <div class="glass-panel" style="padding: 1.25rem; display: flex; align-items: center; gap: 1rem; border: 1px solid var(--border-color); background: rgba(255,255,255,0.01);">
                    <div style="width: 48px; height: 48px; border-radius: 8px; background: rgba(99, 102, 241, 0.1); color: var(--accent-indigo); display: flex; align-items: center; justify-content: center; font-size: 1.35rem;">
                        <i class="fa-solid fa-clipboard-check"></i>
                    </div>
                    <div>
                        <span style="font-size: 0.75rem; color: var(--text-muted); display: block; text-transform: uppercase; font-weight: 600;">Total Tasks</span>
                        <span style="font-size: 1.5rem; font-weight: 700; color: var(--text-primary);">{{ $group->studyTasks->count() }}</span>
                    </div>
                </div>

                <!-- Summary Card 2 -->
                <div class="glass-panel" style="padding: 1.25rem; display: flex; align-items: center; gap: 1rem; border: 1px solid var(--border-color); background: rgba(255,255,255,0.01);">
                    <div style="width: 48px; height: 48px; border-radius: 8px; background: rgba(16, 185, 129, 0.1); color: var(--accent-emerald); display: flex; align-items: center; justify-content: center; font-size: 1.35rem;">
                        <i class="fa-solid fa-folder-open"></i>
                    </div>
                    <div>
                        <span style="font-size: 0.75rem; color: var(--text-muted); display: block; text-transform: uppercase; font-weight: 600;">Resources Drive</span>
                        <span style="font-size: 1.5rem; font-weight: 700; color: var(--text-primary);">{{ $group->resources->count() }}</span>
                    </div>
                </div>

                <!-- Summary Card 3 -->
                <div class="glass-panel" style="padding: 1.25rem; display: flex; align-items: center; gap: 1rem; border: 1px solid var(--border-color); background: rgba(255,255,255,0.01);">
                    <div style="width: 48px; height: 48px; border-radius: 8px; background: rgba(245, 158, 11, 0.1); color: var(--accent-amber); display: flex; align-items: center; justify-content: center; font-size: 1.35rem;">
                        <i class="fa-solid fa-graduation-cap"></i>
                    </div>
                    <div>
                        <span style="font-size: 0.75rem; color: var(--text-muted); display: block; text-transform: uppercase; font-weight: 600;">Quizzes Published</span>
                        <span style="font-size: 1.5rem; font-weight: 700; color: var(--text-primary);">{{ $group->quizzes->count() }}</span>
                    </div>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                <!-- Chart 1: Tasks Completion -->
                <div class="glass-panel" style="padding: 1.5rem; border: 1px solid var(--border-color); display: flex; flex-direction: column; align-items: center; height: 350px;">
                    <h4 style="font-size: 0.95rem; font-weight: 600; color: var(--text-primary); margin-bottom: 1rem; align-self: flex-start;"><i class="fa-solid fa-chart-pie" style="color: var(--accent-indigo);"></i> Kanban Task Distribution</h4>
                    <div style="position: relative; width: 100%; height: 260px; display: flex; justify-content: center;">
                        <canvas id="tasksDoughnutChart"></canvas>
                    </div>
                </div>

                <!-- Chart 2: Room Resources & Engagement -->
                <div class="glass-panel" style="padding: 1.5rem; border: 1px solid var(--border-color); display: flex; flex-direction: column; align-items: center; height: 350px;">
                    <h4 style="font-size: 0.95rem; font-weight: 600; color: var(--text-primary); margin-bottom: 1rem; align-self: flex-start;"><i class="fa-solid fa-chart-pie" style="color: var(--accent-emerald);"></i> Collaborative Resource Assets</h4>
                    <div style="position: relative; width: 100%; height: 260px; display: flex; justify-content: center;">
                        <canvas id="engagementRadarChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Student Scoreboard Analytics -->
            <div class="glass-panel" style="padding: 1.5rem; border: 1px solid var(--border-color);">
                <h4 style="font-size: 0.95rem; font-weight: 600; color: var(--text-primary); margin-bottom: 1rem;"><i class="fa-solid fa-trophy" style="color: var(--accent-amber);"></i> Student Assessment Performance</h4>
                <div style="position: relative; width: 100%; height: 300px;">
                    <canvas id="studentScoresBarChart"></canvas>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- ============================================== -->
<!-- DIALOG MODALS OVERLAYS -->
<!-- ============================================== -->

<!-- 1. Resource Upload Modal -->
<div id="resourceModal" class="modal-overlay" onclick="closeModalOnOutsideClick(event, 'resourceModal')">
    <div class="modal-container glass-panel">
        <div class="modal-header">
            <h3>Share Study Resource</h3>
            <button class="modal-close" onclick="closeModal('resourceModal')">&times;</button>
        </div>
        <form action="{{ route('resources.store', $group->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label for="res_title">Resource Title</label>
                <input type="text" name="title" id="res_title" class="form-control" placeholder="e.g. Reactions Mechanism Cheat Sheet" required>
            </div>
            
            <div class="form-group">
                <label for="res_file">Upload File (from PC / Device)</label>
                <input type="file" name="file" id="res_file" class="form-control" style="padding: 0.4rem 0.75rem;">
            </div>

            <div style="text-align: center; margin: 0.75rem 0; color: var(--text-muted); font-size: 0.8rem; font-weight: 500;">— OR —</div>

            <div class="form-group">
                <label for="res_url">URL / Document Link</label>
                <input type="url" name="url" id="res_url" class="form-control" placeholder="https://example.com/notes.pdf">
            </div>

            <div class="form-group">
                <label for="res_type">Resource Type</label>
                <select name="resource_type" id="res_type" class="form-control">
                    <option value="link">Website Link</option>
                    <option value="document">PDF / Document Guide</option>
                </select>
            </div>

            <div class="form-group">
                <label for="res_desc">Brief Description</label>
                <textarea name="description" id="res_desc" class="form-control" placeholder="Explain what this file helps with..."></textarea>
            </div>

            <button type="submit" class="btn btn-primary">Share Reference</button>
        </form>
    </div>
</div>

<!-- 2. Kanban Task Creation Modal -->
<div id="taskModal" class="modal-overlay" onclick="closeModalOnOutsideClick(event, 'taskModal')">
    <div class="modal-container glass-panel">
        <div class="modal-header">
            <h3>Add Study Task Card</h3>
            <button class="modal-close" onclick="closeModal('taskModal')">&times;</button>
        </div>
        <form action="{{ route('tasks.store', $group->id) }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="task_title">Task Title</label>
                <input type="text" name="title" id="task_title" class="form-control" placeholder="e.g. Implement DFS search in python" required>
            </div>
            
            <div class="form-group">
                <label for="task_desc">Task Description</label>
                <textarea name="description" id="task_desc" class="form-control" placeholder="What are the details of this action item?"></textarea>
            </div>

            <div class="form-group">
                <label for="task_assignee">Assignee (Room Member)</label>
                <select name="assignee_id" id="task_assignee" class="form-control">
                    <option value="">-- Unassigned (Self-select) --</option>
                    @foreach($group->members as $member)
                        <option value="{{ $member->id }}">{{ $member->name }}</option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Add Card</button>
        </form>
    </div>
</div>



<!-- 5. Add Flashcard Modal -->
<div id="createCardModal" class="modal-overlay" onclick="closeModalOnOutsideClick(event, 'createCardModal')">
    <div class="modal-container glass-panel">
        <div class="modal-header">
            <h3>Add Flashcard to Deck</h3>
            <button class="modal-close" onclick="closeModal('createCardModal')">&times;</button>
        </div>
        <form action="{{ route('flashcards.store', $group->id) }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="fc_question">Question / Term</label>
                <input type="text" name="question" id="fc_question" class="form-control" placeholder="e.g. Mitochondria" required>
            </div>
            <div class="form-group">
                <label for="fc_answer">Answer / Definition</label>
                <textarea name="answer" id="fc_answer" class="form-control" placeholder="e.g. Powerhouse of the cell, generates chemical energy ATP." required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Add Flashcard</button>
        </form>
    </div>
</div>

<!-- 6. Create Quiz Modal -->
<div id="createQuizModal" class="modal-overlay" onclick="closeModalOnOutsideClick(event, 'createQuizModal')">
    <div class="modal-container glass-panel" style="max-width: 600px; max-height: 85vh; overflow-y: auto;">
        <div class="modal-header">
            <h3>Create Assessment Quiz</h3>
            <button class="modal-close" onclick="closeModal('createQuizModal')">&times;</button>
        </div>
        <form action="{{ route('quizzes.store', $group->id) }}" method="POST" id="createQuizForm">
            @csrf
            <div class="form-group">
                <label for="quiz_title">Quiz Title</label>
                <input type="text" name="title" id="quiz_title" class="form-control" placeholder="e.g. Chapter 4: Organic Chemistry Review" required>
            </div>
            
            <div class="form-group">
                <label for="quiz_desc">Description (Optional)</label>
                <textarea name="description" id="quiz_desc" class="form-control" placeholder="e.g. Test your understanding of electrophilic substitution reactions..."></textarea>
            </div>

            <div class="form-group">
                <label for="quiz_time">Time Limit (Minutes)</label>
                <input type="number" name="time_limit_minutes" id="quiz_time" class="form-control" value="10" min="1" max="180" required>
            </div>

            <div style="border-top: 1px solid var(--border-color); padding-top: 1rem; margin-top: 1rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                    <h4 style="font-size: 1rem; font-weight: 600; color: var(--accent-indigo);">Questions Block</h4>
                    <button type="button" class="btn" onclick="addQuestionField()" style="width: auto; padding: 0.35rem 0.75rem; font-size: 0.75rem; background: rgba(99, 102, 241, 0.1); border: 1px solid var(--accent-indigo); color: #818cf8;">
                        <i class="fa-solid fa-plus"></i> Add Question
                    </button>
                </div>

                <div id="quizQuestionsContainer" style="display: flex; flex-direction: column; gap: 1.25rem;">
                    <!-- Question items will be inserted dynamically by JS -->
                </div>
            </div>

            <button type="submit" class="btn btn-primary" style="margin-top: 1.5rem;">Create Quiz</button>
        </form>
    </div>
</div>

<!-- 7. Active Quiz Taking Modal -->
<div id="quizTakingModal" class="modal-overlay" style="z-index: 10000;">
    <div class="modal-container glass-panel" style="max-width: 550px; text-align: left;">
        <div class="modal-header" style="border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 0.75rem; margin-bottom: 1rem;">
            <div>
                <h3 id="activeQuizTitle" style="font-size: 1.25rem; font-weight: 700; color: var(--accent-indigo);">Interactive Assessment</h3>
                <span id="quizQuestionTracker" style="font-size: 0.75rem; color: var(--text-muted); font-family: monospace;">Question 1 of 1</span>
            </div>
            <div style="text-align: right;">
                <div id="quizTimerText" style="font-family: monospace; font-size: 1.1rem; font-weight: 700; color: var(--accent-amber); display: flex; align-items: center; gap: 0.35rem;">
                    <i class="fa-solid fa-clock fa-spin"></i> <span id="quizTimeRemaining">00:00</span>
                </div>
            </div>
        </div>

        <!-- Progress bar indicator -->
        <div style="background: rgba(255,255,255,0.05); height: 4px; border-radius: 2px; width: 100%; margin-bottom: 1.5rem; overflow: hidden; position: relative;">
            <div id="quizProgressBar" style="background: linear-gradient(90deg, var(--accent-indigo), var(--accent-violet)); height: 100%; width: 0%; transition: width 0.3s ease;"></div>
        </div>

        <div id="quizTakingViewport" style="min-height: 180px;">
            <!-- Quiz taking slides generated dynamically in JS -->
        </div>

        <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid rgba(255,255,255,0.05); padding-top: 1rem; margin-top: 1.5rem;">
            <button type="button" class="btn" id="quizPrevBtn" onclick="prevQuizQuestion()" style="width: auto; padding: 0.45rem 1.25rem; font-size: 0.8rem; background: rgba(255,255,255,0.03); border: 1px solid var(--border-color); border-radius: 6px;"><i class="fa-solid fa-chevron-left"></i> Back</button>
            <button type="button" class="btn btn-primary" id="quizNextBtn" onclick="nextQuizQuestion()" style="width: auto; padding: 0.45rem 1.5rem; font-size: 0.8rem; font-weight: 600; border-radius: 6px;">Next <i class="fa-solid fa-chevron-right"></i></button>
            <button type="button" class="btn btn-success" id="quizSubmitBtn" onclick="submitQuizAnswers()" style="width: auto; padding: 0.45rem 1.5rem; font-size: 0.8rem; font-weight: 600; border-radius: 6px; display: none;"><i class="fa-solid fa-check-double"></i> Submit Quiz</button>
        </div>
    </div>
</div>

<!-- 8. Quiz Result Score Overlay -->
<div id="quizResultModal" class="modal-overlay" style="z-index: 10001;" onclick="closeModalOnOutsideClick(event, 'quizResultModal')">
    <div class="modal-container glass-panel" style="max-width: 420px; text-align: center; padding: 2rem;">
        <div style="font-size: 3rem; color: var(--accent-emerald); margin-bottom: 1rem;" id="quizResultIcon">
            <i class="fa-solid fa-circle-check"></i>
        </div>
        <h3 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.5rem;" id="quizResultHeading">Quiz Completed!</h3>
        <p style="color: var(--text-secondary); font-size: 0.88rem; line-height: 1.5; margin-bottom: 1.5rem;" id="quizResultDescription">
            Excellent job completing the quiz. Let's see how your rank holds up!
        </p>

        <div style="background: rgba(255, 255, 255, 0.03); border: 1px solid var(--border-color); border-radius: 8px; padding: 1.25rem; margin-bottom: 1.5rem; display: flex; justify-content: space-around; align-items: center;">
            <div>
                <span style="font-size: 0.72rem; color: var(--text-muted); display: block; text-transform: uppercase;">Correct Points</span>
                <span style="font-size: 1.6rem; font-weight: 700; color: var(--accent-indigo);" id="resultCorrectScore">5 / 5</span>
            </div>
            <div style="border-left: 1px solid var(--border-color); height: 40px;"></div>
            <div>
                <span style="font-size: 0.72rem; color: var(--text-muted); display: block; text-transform: uppercase;">Accuracy</span>
                <span style="font-size: 1.6rem; font-weight: 700; color: var(--accent-emerald);" id="resultAccuracyPct">100%</span>
            </div>
        </div>

        <button class="btn btn-primary" onclick="closeModal('quizResultModal'); window.location.reload();">Close & Refresh Scoreboard</button>
    </div>
</div>

@endsection

@section('scripts')
<script>
    // Tab switching engine
    function switchTab(evt, tabName) {
        // Remove active class from all triggers
        const triggers = document.getElementsByClassName("tab-trigger");
        for (let i = 0; i < triggers.length; i++) {
            triggers[i].classList.remove("active");
        }
        
        // Hide all panels
        const panels = document.getElementsByClassName("tab-panel");
        for (let i = 0; i < panels.length; i++) {
            panels[i].classList.remove("active");
        }
        
        // Add active class to clicked trigger and show respective panel
        evt.currentTarget.classList.add("active");
        document.getElementById("panel-" + tabName).classList.add("active");
        
        // Custom actions when opening certain tabs
        if (tabName === 'chat') {
            scrollChatToBottom();

        } else if (tabName === 'analytics') {
            setTimeout(() => {
                initAnalyticsCharts();
            }, 100);
        }
    }

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

    // Copy Access Code Tool
    function copyAccessCode() {
        const text = document.getElementById("accessCodeText").innerText;
        navigator.clipboard.writeText(text).then(() => {
            const icon = document.getElementById("copyIcon");
            icon.classList.remove("fa-copy");
            icon.classList.add("fa-check");
            icon.style.color = "var(--accent-emerald)";
            
            setTimeout(() => {
                icon.classList.remove("fa-check");
                icon.classList.add("fa-copy");
                icon.style.color = "";
            }, 2000);
        });
    }

    // ==============================================
    // CHAT ROOM DYNAMICS (AJAX & LONG-POLLING)
    // ==============================================
    
    // Auto-scroll chat window
    function scrollChatToBottom() {
        const chatHistory = document.getElementById("chatHistory");
        chatHistory.scrollTop = chatHistory.scrollHeight;
    }
    
    // Initial chat scroll
    window.onload = function() {
        scrollChatToBottom();
    };

    // Track last seen message ID to poll additions
    let lastMessageId = 0;
    const msgContainers = document.querySelectorAll('.chat-bubble-container');
    if (msgContainers.length > 0) {
        lastMessageId = parseInt(msgContainers[msgContainers.length - 1].getAttribute('data-msg-id'));
    }

    // Post Chat Messages via AJAX
    function sendChatMessage(e) {
        e.preventDefault();
        const input = document.getElementById("chatInput");
        const message = input.value.trim();
        if (!message) return;
        
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        fetch("{{ route('chat.send', $group->id) }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ message: message })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                input.value = "";
                
                // Hide empty room alert if present
                const alertNode = document.getElementById("noChatAlert");
                if (alertNode) alertNode.remove();
                
                // Append bubble
                appendChatMessage({
                    id: data.message.id,
                    user_name: data.message.user.name,
                    message: data.message.message,
                    is_me: true,
                    formatted_time: data.formatted_time
                });
                
                // Track latest ID
                if (data.message.id > lastMessageId) {
                    lastMessageId = data.message.id;
                }
            }
        })
        .catch(err => console.error("Error sending message:", err));
    }

    // Append chat bubbles dynamically
    function appendChatMessage(msg) {
        const container = document.createElement('div');
        container.className = `chat-bubble-container ${msg.is_me ? 'me' : ''}`;
        container.setAttribute('data-msg-id', msg.id);
        
        container.innerHTML = `
            <div>
                <span class="chat-sender">${msg.user_name}</span>
                <div class="chat-bubble">
                    ${escapeHtml(msg.message)}
                </div>
                <span class="chat-time">${msg.formatted_time}</span>
            </div>
        `;
        
        const chatHistory = document.getElementById("chatHistory");
        chatHistory.appendChild(container);
        scrollChatToBottom();
    }

    // Helper utility to sanitize tags
    function escapeHtml(str) {
        return str.replace(/&/g, "&amp;")
                  .replace(/</g, "&lt;")
                  .replace(/>/g, "&gt;")
                  .replace(/"/g, "&quot;")
                  .replace(/'/g, "&#039;");
    }

    // Chat Room Poller (Poll database every 3 seconds for new peer lines)
    setInterval(function() {
        // Only poll if Chat tab is currently active
        const chatTabActive = document.querySelector('button[onclick*="chat"]').classList.contains('active');
        if (!chatTabActive) return;

        fetch(`{{ url('/groups/' . $group->id . '/chat/poll') }}?last_message_id=${lastMessageId}`)
        .then(res => res.json())
        .then(data => {
            if (data.messages && data.messages.length > 0) {
                // Hide empty room alert
                const alertNode = document.getElementById("noChatAlert");
                if (alertNode) alertNode.remove();

                data.messages.forEach(msg => {
                    // Only append if not already drawn in our DOM
                    if (!document.querySelector(`.chat-bubble-container[data-msg-id="${msg.id}"]`)) {
                        appendChatMessage(msg);
                    }
                    if (msg.id > lastMessageId) {
                        lastMessageId = msg.id;
                    }
                });
            }
        })
        .catch(err => console.error("Polling error:", err));
    }, 3000);

    // ==============================================
    // COLLABORATIVE NOTEPAD DEBOUNCED AUTOSAVE
    // ==============================================
    let autoSaveTimeout = null;

    function triggerNoteAutoSave() {
        const textContent = document.getElementById("notesArea").value;
        const statusNode = document.getElementById("noteSaveStatus");
        statusNode.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving changes in background...';

        // Clear existing debounce timers
        clearTimeout(autoSaveTimeout);

        // Schedule new post save in 1000ms
        autoSaveTimeout = setTimeout(() => {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            fetch("{{ route('notes.update', $group->id) }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ content: textContent })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    statusNode.innerHTML = `Last saved at ${data.updated_at} (Edited by ${data.last_edited_by_name})`;
                }
            })
            .catch(err => {
                console.error("Autosave failed:", err);
                statusNode.innerText = "Saving failed. Re-connecting...";
            });
        }, 1000);
    }

    // ==============================================
    // KANBAN STATUS TRANSITIONS VIA AJAX
    // ==============================================
    function transitionTaskStatus(selectElement, taskId) {
        const newStatus = selectElement.value;
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        // Display spinner during AJAX update
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
                // Find card DOM
                const card = document.getElementById(`task-card-${taskId}`);
                
                // Relocate card dynamically into the correct column DOM
                const destinationColumn = document.getElementById(`column-${newStatus}`);
                destinationColumn.appendChild(card);
                
                // Update column counters
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
            const count = column.children.length;
            document.getElementById(`count-${status}`).innerText = count;
        });
    }
</script>

<!-- Load Jitsi Meet External API Script -->
<script src="https://meet.jit.si/external_api.js"></script>
<script>
    // ==============================================
    // EMBEDDED JITSI MEET WEBRTC CALL ENGINE
    // ==============================================
    let jitsiApi = null;

    function initializeJitsiCall() {
        const domain = "meet.jit.si";
        // Create a unique, URL-friendly room ID scoped by course access code and ID hash
        const roomUniqueName = "CollabSphere_" + "{{ $group->access_code }}_" + "{{ md5($group->id) }}";
        
        document.getElementById("jitsiContainerWrapper").style.display = "block";
        document.getElementById("startCallBtn").style.display = "none";
        document.getElementById("endCallBtn").style.display = "inline-flex";

        const options = {
            roomName: roomUniqueName,
            width: "100%",
            height: 600,
            parentNode: document.querySelector('#jitsiIframeContainer'),
            userInfo: {
                displayName: "{{ Auth::user()->name }}",
                email: "{{ Auth::user()->email }}"
            },
            configOverwrite: {
                startWithAudioMuted: true,
                startWithVideoMuted: true,
                enableWelcomePage: false,
                prejoinPageEnabled: false
            },
            interfaceConfigOverwrite: {
                TOOLBAR_BUTTONS: [
                    'microphone', 'camera', 'closedcaptions', 'desktop', 'embedmeeting', 'fullscreen',
                    'fodeviceselection', 'hangup', 'profile', 'chat', 'recording',
                    'livestreaming', 'etherpad', 'sharedvideo', 'settings', 'raisehand',
                    'videoquality', 'filmstrip', 'invite', 'feedback', 'stats', 'shortcuts',
                    'tileview', 'videobackgroundblur', 'download', 'help', 'mute-everyone',
                    'security'
                ]
            }
        };

        jitsiApi = new JitsiMeetExternalAPI(domain, options);
        
        // Listen for conference hangup trigger to clean up state
        jitsiApi.addEventListener('videoConferenceLeft', () => {
            terminateJitsiCall();
        });
    }

    function terminateJitsiCall() {
        if (jitsiApi) {
            jitsiApi.dispose();
            jitsiApi = null;
        }
        
        document.getElementById("jitsiIframeContainer").innerHTML = "";
        document.getElementById("jitsiContainerWrapper").style.display = "none";
        document.getElementById("startCallBtn").style.display = "inline-flex";
        document.getElementById("endCallBtn").style.display = "none";
    }
</script>

<!-- Load Chart.js Library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    // ==============================================
    // FLASHCARD VIEWING/FLIPPING SLIDES ENGINE
    // ==============================================
    let currentCardIndex = 0;
    const flashcardsDeck = @json($group->flashcards);

    function updateFlashcardUI() {
        if (!flashcardsDeck || flashcardsDeck.length === 0) return;
        
        // Reset flip rotation back to front
        const inner = document.getElementById("flashcardInner");
        if (inner) {
            inner.style.transform = "rotateY(0deg)";
        }
        
        const card = flashcardsDeck[currentCardIndex];
        document.getElementById("cardQuestionText").innerText = card.question;
        document.getElementById("cardAnswerText").innerText = card.answer;
        document.getElementById("deckCounter").innerText = `${currentCardIndex + 1} of ${flashcardsDeck.length}`;
    }

    function flipFlashcard() {
        const inner = document.getElementById("flashcardInner");
        if (inner) {
            if (inner.style.transform === "rotateY(180deg)") {
                inner.style.transform = "rotateY(0deg)";
            } else {
                inner.style.transform = "rotateY(180deg)";
            }
        }
    }

    function prevFlashcard() {
        if (!flashcardsDeck || flashcardsDeck.length <= 1) return;
        currentCardIndex = (currentCardIndex - 1 + flashcardsDeck.length) % flashcardsDeck.length;
        updateFlashcardUI();
    }

    function nextFlashcard() {
        if (!flashcardsDeck || flashcardsDeck.length <= 1) return;
        currentCardIndex = (currentCardIndex + 1) % flashcardsDeck.length;
        updateFlashcardUI();
    }

    // ==============================================
    // DYNAMIC QUIZ BUILDER FIELDS CREATOR
    // ==============================================
    let questionCount = 0;

    function addQuestionField() {
        questionCount++;
        const container = document.getElementById("quizQuestionsContainer");
        const qBlock = document.createElement("div");
        qBlock.className = "glass-panel";
        qBlock.id = `q-block-${questionCount}`;
        qBlock.style.padding = "1rem";
        qBlock.style.border = "1px solid rgba(255,255,255,0.06)";
        qBlock.style.background = "rgba(255,255,255,0.01)";
        qBlock.style.position = "relative";

        qBlock.innerHTML = `
            <button type="button" onclick="removeQuestionField(${questionCount})" style="position: absolute; top: 0.75rem; right: 0.75rem; background: none; border: none; color: #f87171; cursor: pointer; font-size: 1.25rem; line-height: 1;" title="Remove Question">&times;</button>
            <h5 style="margin: 0 0 0.75rem 0; font-size: 0.8rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Question #${questionCount}</h5>
            
            <div class="form-group" style="margin-bottom: 0.75rem;">
                <label style="font-size: 0.75rem; color: var(--text-secondary); display: block; margin-bottom: 0.25rem;">Question Text</label>
                <input type="text" name="questions[${questionCount}][question_text]" class="form-control" placeholder="e.g. Which of the following is an electrophile?" required style="font-size: 0.85rem; padding: 0.45rem 0.75rem;">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; margin-bottom: 0.75rem;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label style="font-size: 0.72rem; color: var(--text-muted); display: block; margin-bottom: 0.15rem;">Option A</label>
                    <input type="text" name="questions[${questionCount}][options][0]" class="form-control" placeholder="Option A" required style="font-size: 0.8rem; padding: 0.35rem 0.6rem;">
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label style="font-size: 0.72rem; color: var(--text-muted); display: block; margin-bottom: 0.15rem;">Option B</label>
                    <input type="text" name="questions[${questionCount}][options][1]" class="form-control" placeholder="Option B" required style="font-size: 0.8rem; padding: 0.35rem 0.6rem;">
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label style="font-size: 0.72rem; color: var(--text-muted); display: block; margin-bottom: 0.15rem;">Option C</label>
                    <input type="text" name="questions[${questionCount}][options][2]" class="form-control" placeholder="Option C" required style="font-size: 0.8rem; padding: 0.35rem 0.6rem;">
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label style="font-size: 0.72rem; color: var(--text-muted); display: block; margin-bottom: 0.15rem;">Option D</label>
                    <input type="text" name="questions[${questionCount}][options][3]" class="form-control" placeholder="Option D" required style="font-size: 0.8rem; padding: 0.35rem 0.6rem;">
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 0;">
                <label style="font-size: 0.75rem; color: var(--text-secondary); display: block; margin-bottom: 0.25rem;">Correct Answer Key</label>
                <select name="questions[${questionCount}][correct_option]" class="form-control" required style="font-size: 0.8rem; padding: 0.35rem 0.6rem; height: auto;">
                    <option value="0">Option A</option>
                    <option value="1">Option B</option>
                    <option value="2">Option C</option>
                    <option value="3">Option D</option>
                </select>
            </div>
        `;
        container.appendChild(qBlock);
    }

    function removeQuestionField(id) {
        const block = document.getElementById(`q-block-${id}`);
        if (block) block.remove();
    }

    // ==============================================
    // ACTIVE QUIZ TIMER & TAKING ENGINE (AJAX)
    // ==============================================
    let activeQuizQuestions = [];
    let activeQuizId = null;
    let quizCurrentSlide = 0;
    let quizAnswers = {};
    let quizTimerInterval = null;
    let quizTimeLeftSeconds = 0;
    let quizElapsedSeconds = 0;

    function startQuizTaking(quizId, title, limitMinutes, questions) {
        activeQuizId = quizId;
        activeQuizQuestions = questions;
        quizCurrentSlide = 0;
        quizAnswers = {};
        quizElapsedSeconds = 0;
        quizTimeLeftSeconds = limitMinutes * 60;

        document.getElementById("activeQuizTitle").innerText = title;
        
        renderQuizTakingSlide();
        updateQuizNavigationButtons();

        openModal("quizTakingModal");

        if (quizTimerInterval) clearInterval(quizTimerInterval);
        updateTimerDisplay();
        quizTimerInterval = setInterval(() => {
            quizTimeLeftSeconds--;
            quizElapsedSeconds++;
            updateTimerDisplay();

            if (quizTimeLeftSeconds <= 0) {
                clearInterval(quizTimerInterval);
                alert("Time limit reached! Auto-submitting your quiz.");
                submitQuizAnswers();
            }
        }, 1000);
    }

    function updateTimerDisplay() {
        const m = Math.floor(quizTimeLeftSeconds / 60);
        const s = quizTimeLeftSeconds % 60;
        const timerText = `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
        document.getElementById("quizTimeRemaining").innerText = timerText;

        const timerBlock = document.getElementById("quizTimerText");
        if (quizTimeLeftSeconds < 60) {
            timerBlock.style.color = "#ef4444";
            timerBlock.style.textShadow = "0 0 10px rgba(239, 68, 68, 0.4)";
        } else {
            timerBlock.style.color = "var(--accent-amber)";
            timerBlock.style.textShadow = "none";
        }
    }

    function renderQuizTakingSlide() {
        const question = activeQuizQuestions[quizCurrentSlide];
        const trackerText = `Question ${quizCurrentSlide + 1} of ${activeQuizQuestions.length}`;
        document.getElementById("quizQuestionTracker").innerText = trackerText;

        const progressPct = ((quizCurrentSlide + 1) / activeQuizQuestions.length) * 100;
        document.getElementById("quizProgressBar").style.width = `${progressPct}%`;

        // Render viewport with premium styles
        const viewport = document.getElementById("quizTakingViewport");
        viewport.innerHTML = `
            <div style="font-size: 1.05rem; font-weight: 600; line-height: 1.4; color: var(--text-primary); margin-bottom: 1.25rem;">
                ${escapeHtml(question.question_text)}
            </div>
            <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                ${question.options.map((option, idx) => {
                    const isSelected = quizAnswers[question.id] === idx;
                    return `
                        <div class="glass-panel choice-card ${isSelected ? 'selected' : ''}" onclick="selectQuizOption(${question.id}, ${idx}, this)" style="padding: 0.85rem 1.25rem; border: 1px solid ${isSelected ? 'var(--accent-indigo)' : 'rgba(255,255,255,0.06)'}; border-radius: 8px; cursor: pointer; transition: all 0.2s; background: ${isSelected ? 'rgba(99, 102, 241, 0.08)' : 'rgba(255,255,255,0.01)'};">
                            <span style="font-family: monospace; font-weight: 700; color: ${isSelected ? 'var(--accent-indigo)' : 'var(--text-muted)'}; margin-right: 0.5rem;">${String.fromCharCode(65 + idx)}.</span>
                            <span style="color: ${isSelected ? '#fff' : 'var(--text-secondary)'}; font-weight: 500;">${escapeHtml(option)}</span>
                        </div>
                    `;
                }).join('')}
            </div>
        `;
    }

    function selectQuizOption(questionId, optionIdx, element) {
        quizAnswers[questionId] = optionIdx;
        
        // Remove selected styling from siblings
        const siblings = element.parentNode.children;
        for (let card of siblings) {
            card.classList.remove('selected');
            card.style.borderColor = 'rgba(255,255,255,0.06)';
            card.style.background = 'rgba(255,255,255,0.01)';
            card.querySelector('span').style.color = 'var(--text-muted)';
            card.querySelector('span:nth-child(2)').style.color = 'var(--text-secondary)';
        }

        // Apply active selection highlight
        element.classList.add('selected');
        element.style.borderColor = 'var(--accent-indigo)';
        element.style.background = 'rgba(99, 102, 241, 0.08)';
        element.querySelector('span').style.color = 'var(--accent-indigo)';
        element.querySelector('span:nth-child(2)').style.color = '#fff';
    }

    function updateQuizNavigationButtons() {
        document.getElementById("quizPrevBtn").disabled = (quizCurrentSlide === 0);
        document.getElementById("quizPrevBtn").style.opacity = (quizCurrentSlide === 0) ? '0.4' : '1';

        if (quizCurrentSlide === activeQuizQuestions.length - 1) {
            document.getElementById("quizNextBtn").style.display = "none";
            document.getElementById("quizSubmitBtn").style.display = "inline-flex";
        } else {
            document.getElementById("quizNextBtn").style.display = "inline-flex";
            document.getElementById("quizSubmitBtn").style.display = "none";
        }
    }

    function prevQuizQuestion() {
        if (quizCurrentSlide > 0) {
            quizCurrentSlide--;
            renderQuizTakingSlide();
            updateQuizNavigationButtons();
        }
    }

    function nextQuizQuestion() {
        if (quizCurrentSlide < activeQuizQuestions.length - 1) {
            quizCurrentSlide++;
            renderQuizTakingSlide();
            updateQuizNavigationButtons();
        }
    }

    function submitQuizAnswers() {
        clearInterval(quizTimerInterval);
        
        // Confirm before submitting if incomplete
        const unanswered = activeQuizQuestions.filter(q => quizAnswers[q.id] === undefined);
        if (unanswered.length > 0) {
            if (!confirm(`You have unanswered questions (${unanswered.length}). Are you sure you want to submit?`)) {
                // Resume timer
                quizTimerInterval = setInterval(() => {
                    quizTimeLeftSeconds--;
                    quizElapsedSeconds++;
                    updateTimerDisplay();
                    if (quizTimeLeftSeconds <= 0) {
                        clearInterval(quizTimerInterval);
                        submitQuizAnswers();
                    }
                }, 1000);
                return;
            }
        }

        const submitBtn = document.getElementById("quizSubmitBtn");
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Submitting...';

        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        fetch(`/quizzes/${activeQuizId}/submit`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                answers: quizAnswers,
                time_spent_seconds: quizElapsedSeconds
            })
        })
        .then(res => res.json())
        .then(data => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fa-solid fa-check-double"></i> Submit Quiz';

            if (data.success) {
                closeModal("quizTakingModal");
                
                document.getElementById("resultCorrectScore").innerText = `${data.score} / ${data.total_questions}`;
                document.getElementById("resultAccuracyPct").innerText = `${data.percentage}%`;
                
                const icon = document.getElementById("quizResultIcon");
                const heading = document.getElementById("quizResultHeading");
                const description = document.getElementById("quizResultDescription");
                
                if (data.percentage >= 90) {
                     icon.innerHTML = '<i class="fa-solid fa-crown" style="color: #fbbf24; filter: drop-shadow(0 0 10px rgba(251,191,36,0.4));"></i>';
                     heading.innerText = "Exceptional Score!";
                     heading.style.color = "#fbbf24";
                     description.innerText = `Grandmaster performance! You answered every question with blazing-fast precision in ${data.time_spent_formatted} minutes.`;
                } else if (data.percentage >= 70) {
                     icon.innerHTML = '<i class="fa-solid fa-award" style="color: var(--accent-emerald);"></i>';
                     heading.innerText = "Excellent Job!";
                     heading.style.color = "var(--accent-emerald)";
                     description.innerText = `You secured a Scholar ranking, completing the assessment in ${data.time_spent_formatted} minutes. Keep up the high standards!`;
                } else {
                     icon.innerHTML = '<i class="fa-solid fa-book" style="color: var(--accent-violet);"></i>';
                     heading.innerText = "Quiz Completed!";
                     heading.style.color = "var(--accent-violet)";
                     description.innerText = `You completed the test in ${data.time_spent_formatted} minutes. Practice makes perfect—review your deck definitions and try again!`;
                }

                openModal("quizResultModal");
            } else {
                alert("Error submitting answers: " + data.message);
            }
        })
        .catch(err => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fa-solid fa-check-double"></i> Submit Quiz';
            console.error("Quiz submission error:", err);
            alert("Connection failed. Could not save quiz attempt.");
        });
    }

    // ==============================================
    // CHART.JS ANALYTICS DASHBOARD INITIALIZER
    // ==============================================
    let tasksDoughnutChart = null;
    let engagementRadarChart = null;
    let studentScoresBarChart = null;

    function initAnalyticsCharts() {
        const doughnutCtx = document.getElementById('tasksDoughnutChart');
        if (!doughnutCtx) return;

        // 1. Kanban Doughnut Chart
        if (tasksDoughnutChart) tasksDoughnutChart.destroy();
        tasksDoughnutChart = new Chart(doughnutCtx.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['To Do', 'In Progress', 'Completed'],
                datasets: [{
                    data: [{{ $group->studyTasks->where('status', 'todo')->count() }}, {{ $group->studyTasks->where('status', 'in_progress')->count() }}, {{ $group->studyTasks->where('status', 'completed')->count() }}],
                    backgroundColor: [
                        'rgba(239, 68, 68, 0.6)',
                        'rgba(245, 158, 11, 0.6)',
                        'rgba(16, 185, 129, 0.6)'
                    ],
                    borderColor: [
                        '#ef4444',
                        '#f59e0b',
                        '#10b981'
                    ],
                    borderWidth: 1.5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: '#e2e8f0',
                            font: { family: 'Outfit, sans-serif' }
                        }
                    }
                }
            }
        });

        // 2. Room Resources Polar Area Chart
        const polarCtx = document.getElementById('engagementRadarChart');
        if (polarCtx) {
            if (engagementRadarChart) engagementRadarChart.destroy();
            engagementRadarChart = new Chart(polarCtx.getContext('2d'), {
                type: 'polarArea',
                data: {
                    labels: ['Resources', 'Live Meets', 'Quizzes', 'Flashcards'],
                    datasets: [{
                        data: [
                            {{ $group->resources->count() }}, 
                            {{ $group->studySessions->count() }}, 
                            {{ $group->quizzes->count() }}, 
                            {{ $group->flashcards->count() }}
                        ],
                        backgroundColor: [
                            'rgba(99, 102, 241, 0.5)',
                            'rgba(139, 92, 246, 0.5)',
                            'rgba(245, 158, 11, 0.5)',
                            'rgba(16, 185, 129, 0.5)'
                        ],
                        borderColor: [
                            '#6366f1',
                            '#8b5cf6',
                            '#f59e0b',
                            '#10b981'
                        ],
                        borderWidth: 1.5
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        r: {
                            grid: { color: 'rgba(255, 255, 255, 0.05)' },
                            angleLines: { color: 'rgba(255, 255, 255, 0.05)' },
                            ticks: { backdropColor: 'transparent', color: '#94a3b8' },
                            pointLabels: { color: '#e2e8f0', font: { family: 'Outfit, sans-serif' } }
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                color: '#e2e8f0',
                                font: { family: 'Outfit, sans-serif' }
                            }
                        }
                    }
                }
            });
        }

        // 3. Student Scores Bar Chart
        const barCtx = document.getElementById('studentScoresBarChart');
        if (barCtx) {
            if (studentScoresBarChart) studentScoresBarChart.destroy();

            @php
                $studentNames = [];
                $studentScores = [];
                $studentAccuracies = [];
                if (isset($rankings)) {
                    foreach ($rankings as $row) {
                        $studentNames[] = $row['name'];
                        $studentScores[] = $row['points'];
                        $studentAccuracies[] = $row['accuracy'];
                    }
                }
            @endphp

            studentScoresBarChart = new Chart(barCtx.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: {!! json_encode($studentNames) !!},
                    datasets: [
                        {
                            label: 'Total Correct Points',
                            data: {!! json_encode($studentScores) !!},
                            backgroundColor: 'rgba(99, 102, 241, 0.6)',
                            borderColor: '#6366f1',
                            borderWidth: 1.5
                        },
                        {
                            label: 'Avg Accuracy (%)',
                            data: {!! json_encode($studentAccuracies) !!},
                            backgroundColor: 'rgba(16, 185, 129, 0.6)',
                            borderColor: '#10b981',
                            borderWidth: 1.5
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            grid: { color: 'rgba(255, 255, 255, 0.03)' },
                            ticks: { color: '#94a3b8', font: { family: 'Outfit, sans-serif' } }
                        },
                        y: {
                            grid: { color: 'rgba(255, 255, 255, 0.03)' },
                            ticks: { color: '#94a3b8', font: { family: 'Outfit, sans-serif' } },
                            beginAtZero: true
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                color: '#e2e8f0',
                                font: { family: 'Outfit, sans-serif' }
                            }
                        }
                    }
                }
            });
        }
    }

    // Trigger deck load on startup
    document.addEventListener("DOMContentLoaded", () => {
        updateFlashcardUI();
        
        // Auto-open quiz question field
        const quizForm = document.getElementById("createQuizForm");
        if (quizForm) {
            quizForm.addEventListener("submit", (e) => {
                if (questionCount === 0) {
                    e.preventDefault();
                    alert("Please add at least one question block to the quiz!");
                }
            });
        }
    });
</script>
@endsection
