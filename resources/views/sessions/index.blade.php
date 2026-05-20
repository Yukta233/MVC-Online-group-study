@extends('layouts.app')

@section('title', 'Upcoming Live Sessions - CollabSphere')

@section('content')
<!-- Load FullCalendar.js CDN -->
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>

<style>
    /* Midnight Glass style adjustments for FullCalendar */
    .fc {
        font-family: 'Outfit', system-ui, sans-serif;
        background: rgba(10, 11, 16, 0.4);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 1.25rem;
        color: var(--text-primary);
    }
    .fc-theme-standard td, .fc-theme-standard th {
        border-color: rgba(255, 255, 255, 0.05);
    }
    .fc-theme-standard .fc-scrollgrid {
        border-color: rgba(255, 255, 255, 0.08);
        border-radius: 8px;
        overflow: hidden;
    }
    .fc-header-toolbar {
        margin-bottom: 1.5rem !important;
        flex-wrap: wrap;
        gap: 0.5rem;
    }
    .fc-toolbar-title {
        font-size: 1.25rem !important;
        font-weight: 700 !important;
        color: var(--text-primary);
    }
    .fc-button-primary {
        background: rgba(99, 102, 241, 0.1) !important;
        border: 1px solid rgba(99, 102, 241, 0.3) !important;
        color: #818cf8 !important;
        text-transform: capitalize !important;
        font-weight: 600 !important;
        font-size: 0.82rem !important;
        padding: 0.4rem 0.85rem !important;
        border-radius: 6px !important;
        transition: all 0.2s !important;
    }
    .fc-button-primary:hover, .fc-button-primary:active, .fc-button-primary:focus {
        background: var(--accent-indigo) !important;
        color: #fff !important;
        border-color: var(--accent-indigo) !important;
        box-shadow: 0 0 10px rgba(99, 102, 241, 0.3) !important;
    }
    .fc-button-primary:disabled {
        background: rgba(255,255,255,0.02) !important;
        border-color: var(--border-color) !important;
        color: var(--text-muted) !important;
        opacity: 0.6;
    }
    .fc-button-active {
        background: var(--accent-indigo) !important;
        color: #fff !important;
        border-color: var(--accent-indigo) !important;
    }
    .fc-day-today {
        background: rgba(139, 92, 246, 0.05) !important;
    }
    .fc-event {
        background: linear-gradient(135deg, var(--accent-indigo), var(--accent-violet)) !important;
        border: none !important;
        border-radius: 4px !important;
        padding: 0.15rem 0.4rem !important;
        font-size: 0.75rem !important;
        font-weight: 600 !important;
        box-shadow: 0 2px 6px rgba(0,0,0,0.15) !important;
        cursor: pointer;
    }
    .fc-col-header-cell-cushion {
        color: var(--text-secondary);
        font-weight: 600;
        font-size: 0.85rem;
        text-decoration: none;
    }
    .fc-daygrid-day-number {
        color: var(--text-muted);
        font-size: 0.82rem;
        font-family: monospace;
        text-decoration: none;
        padding: 4px;
    }
    .fc-daygrid-day-events {
        min-height: 2em;
    }
</style>

<div class="sessions-wrapper" style="height: auto; min-height: calc(100vh - 100px);">
    <div class="header-container" style="margin-bottom: 2rem;">
        <div class="header-title">
            <h1><i class="fa-solid fa-calendar-days" style="color: var(--accent-violet);"></i> Group Meeting Calendar</h1>
            <p>View, join, and schedule live peer study rooms across all your active study spaces.</p>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem; align-items: start;">
        <!-- Left: FullCalendar Component -->
        <div class="glass-panel" style="padding: 1.5rem; border: 1px solid var(--border-color);">
            <div id="calendar"></div>
        </div>

        <!-- Right: Upcoming sessions card list -->
        <div>
            <h3 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 1rem; color: var(--accent-violet); display: flex; align-items: center; gap: 0.5rem;">
                <i class="fa-solid fa-list-ul"></i> Upcoming Live Lists
            </h3>

            @if($upcomingSessions->isEmpty())
                <div class="glass-panel" style="padding: 3rem 1.5rem; text-align: center; color: var(--text-secondary); border: 1px solid var(--border-color);">
                    <i class="fa-solid fa-video" style="font-size: 2.25rem; color: var(--text-muted); margin-bottom: 1rem; display: block;"></i>
                    <p style="font-weight: 500; font-size: 0.95rem;">No upcoming live study sessions.</p>
                </div>
            @else
                <div style="display: flex; flex-direction: column; gap: 1rem; max-height: 600px; overflow-y: auto; padding-right: 0.25rem;">
                    @foreach($upcomingSessions as $session)
                        <div class="glass-panel glass-card session-card" style="padding: 1.25rem; border: 1px solid var(--border-color); background: rgba(255,255,255,0.01);">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.5rem; flex-wrap: wrap; gap: 0.25rem;">
                                <span style="font-size: 0.68rem; color: var(--accent-indigo); font-weight: 700; background: rgba(99, 102, 241, 0.1); padding: 0.15rem 0.4rem; border-radius: 4px;">
                                    {{ $session->studyGroup->name }}
                                </span>
                                <span style="font-size: 0.72rem; color: var(--accent-amber); font-weight: 600; font-family: monospace;">
                                    <i class="fa-solid fa-clock"></i> {{ $session->scheduled_at->format('M d, h:i A') }}
                                </span>
                            </div>
                            
                            <h4 style="font-size: 1rem; font-weight: 600; margin-bottom: 0.35rem; color: #ffffff;">{{ $session->title }}</h4>
                            <p style="font-size: 0.78rem; color: var(--text-secondary); margin-bottom: 1rem; line-height: 1.4;">
                                {{ $session->description ?: 'No study agenda or chapters outlined.' }}
                            </p>
                            
                            <div style="border-top: 1px solid rgba(255, 255, 255, 0.04); padding-top: 0.75rem; display: flex; justify-content: space-between; align-items: center;">
                                <span style="font-size: 0.75rem; color: var(--text-muted);">
                                    <i class="fa-solid fa-hourglass-half"></i> {{ $session->duration_minutes }}m duration
                                </span>
                                
                                @if($session->meeting_link)
                                    <a href="{{ $session->meeting_link }}" target="_blank" class="session-join-btn" style="margin-top: 0; width: auto; padding: 0.35rem 1rem; font-size: 0.75rem;">
                                        <i class="fa-solid fa-video"></i> Launch
                                    </a>
                                @else
                                    <span style="font-size: 0.72rem; color: var(--text-muted);"><i class="fa-solid fa-circle-info"></i> In-Person</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const calendarEl = document.getElementById('calendar');
        const calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            events: [
                @foreach($upcomingSessions as $session)
                {
                    title: '{{ addslashes($session->title) }} ({{ addslashes($session->studyGroup->name) }})',
                    start: '{{ $session->scheduled_at->toIso8601String() }}',
                    end: '{{ $session->scheduled_at->addMinutes($session->duration_minutes)->toIso8601String() }}',
                    url: '{{ $session->meeting_link ?: route("groups.show", $session->study_group_id) }}',
                    description: '{{ addslashes($session->description) }}'
                },
                @endforeach
            ],
            eventClick: function(info) {
                if (info.event.url) {
                    info.jsEvent.preventDefault();
                    window.open(info.event.url, '_blank');
                }
            }
        });
        calendar.render();
    });
</script>
@endsection
