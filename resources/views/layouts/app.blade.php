<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Group Study & Collaboration Portal')</title>
    
    <!-- FontAwesome & Outfit Google Font -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
    <div class="app-container">
        
        <!-- Persistent Sidebar -->
        <aside class="sidebar glass-panel">
            <div class="logo-container">
                <i class="fa-solid fa-graduation-cap logo-icon"></i>
                <span class="logo-text">CollabSphere</span>
            </div>
            
            <ul class="nav-menu">
                <li>
                    <a href="{{ route('dashboard') }}" class="nav-link {{ Route::currentRouteName() == 'dashboard' ? 'active' : '' }}">
                        <i class="fa-solid fa-chart-pie"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('global.tasks') }}" class="nav-link {{ Route::currentRouteName() == 'global.tasks' ? 'active' : '' }}">
                        <i class="fa-solid fa-clipboard-list"></i>
                        <span>My Tasks</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('global.sessions') }}" class="nav-link {{ Route::currentRouteName() == 'global.sessions' ? 'active' : '' }}">
                        <i class="fa-solid fa-calendar-days"></i>
                        <span>Live Sessions</span>
                    </a>
                </li>
                @auth
                    @if(in_array(Auth::user()->role, ['admin', 'moderator']))
                        <li>
                            <a href="{{ route('admin.dashboard') }}" class="nav-link {{ str_starts_with(Route::currentRouteName(), 'admin.') ? 'active' : '' }}">
                                <i class="fa-solid fa-user-shield" style="color: var(--accent-rose);"></i>
                                <span style="color: var(--accent-rose); font-weight: 600;">Admin Space</span>
                            </a>
                        </li>
                    @endif
                @endauth
                @yield('sidebar_menu')
            </ul>
            
            <div class="sidebar-footer">
                @auth
                    <div class="user-profile">
                        <div class="user-avatar">
                            {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                        </div>
                        <div class="user-info">
                            <div class="user-name">{{ Auth::user()->name }}</div>
                            <div class="user-email">{{ Auth::user()->email }}</div>
                        </div>
                    </div>
                    
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="logout-btn">
                            <i class="fa-solid fa-arrow-right-from-bracket"></i>
                            <span>Sign Out</span>
                        </button>
                    </form>
                @endauth
            </div>
        </aside>
        
        <!-- Main Panel Content -->
        <main class="main-content">
            
            <!-- Glassmorphic Global Topbar -->
            <header class="topbar">
                <div class="topbar-search-container">
                    <div class="topbar-search-input-wrapper">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="text" id="globalSearchInput" class="topbar-search-input" placeholder="Search rooms, resources, tasks...">
                    </div>
                    <div id="searchDropdown" class="search-dropdown">
                        <!-- Loaded dynamically via AJAX -->
                    </div>
                </div>

                <div class="topbar-actions">
                    <!-- Real-Time local clock -->
                    <div class="topbar-clock" id="navClock">
                        <i class="fa-solid fa-clock"></i>
                        <span id="navClockTime">--:-- --</span>
                    </div>

                    <!-- Notification bell with activity feed panel -->
                    <div class="bell-container">
                        <button class="bell-btn" id="bellBtn" title="Recent Activities">
                            <i class="fa-solid fa-bell"></i>
                            <span class="bell-badge" id="bellBadge"></span>
                        </button>

                        <div class="notification-panel" id="notificationPanel">
                            <div class="notification-panel-header">
                                <h3>Activity Feed</h3>
                                <button class="clear-notifications-btn" id="clearBellsBtn">Dismiss All</button>
                            </div>
                            <div class="notification-list" id="notificationList">
                                <!-- Loaded dynamically via AJAX -->
                            </div>
                        </div>
                    </div>
                </div>
            </header>
            
            <!-- Alert/Flash Notifications -->
            @if(session('success'))
                <div class="alert alert-success">
                    <i class="fa-solid fa-circle-check"></i>
                    <div>{{ session('success') }}</div>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <ul class="validation-errors">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            
            @yield('content')
            
        </main>
    </div>
    
    <!-- Topbar Core Script -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // --- 1. Navbar Local clock display ---
        const navClockTime = document.getElementById('navClockTime');
        function updateClock() {
            const now = new Date();
            let hours = now.getHours();
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const ampm = hours >= 12 ? 'PM' : 'AM';
            hours = hours % 12;
            hours = hours ? hours : 12; // local military 0 is 12
            const strTime = hours + ':' + minutes + ' ' + ampm;
            if (navClockTime) navClockTime.textContent = strTime;
        }
        updateClock();
        setInterval(updateClock, 10000);

        // --- 2. Notification Panel Overlay ---
        const bellBtn = document.getElementById('bellBtn');
        const bellBadge = document.getElementById('bellBadge');
        const notificationPanel = document.getElementById('notificationPanel');
        const clearBellsBtn = document.getElementById('clearBellsBtn');
        const notificationList = document.getElementById('notificationList');

        if (bellBtn) {
            bellBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                notificationPanel.classList.toggle('active');
                bellBadge.classList.remove('active');
                localStorage.setItem('bells_read_timestamp', new Date().toISOString());
            });
        }

        if (notificationPanel) {
            notificationPanel.addEventListener('click', function(e) {
                e.stopPropagation();
            });
        }

        // --- 3. Web Audio Soft Chime Synth ---
        function playChime() {
            try {
                const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                
                // Primary node (D5 chime)
                const osc1 = audioCtx.createOscillator();
                const gain1 = audioCtx.createGain();
                osc1.type = 'sine';
                osc1.frequency.setValueAtTime(587.33, audioCtx.currentTime);
                gain1.gain.setValueAtTime(0.08, audioCtx.currentTime);
                gain1.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + 0.5);
                osc1.connect(gain1);
                gain1.connect(audioCtx.destination);
                osc1.start();
                osc1.stop(audioCtx.currentTime + 0.5);

                // Secondary node (A5 chime)
                setTimeout(() => {
                    const osc2 = audioCtx.createOscillator();
                    const gain2 = audioCtx.createGain();
                    osc2.type = 'sine';
                    osc2.frequency.setValueAtTime(880, audioCtx.currentTime);
                    gain2.gain.setValueAtTime(0.08, audioCtx.currentTime);
                    gain2.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + 0.7);
                    osc2.connect(gain2);
                    gain2.connect(audioCtx.destination);
                    osc2.start();
                    osc2.stop(audioCtx.currentTime + 0.7);
                }, 100);
            } catch (e) {
                console.log("AudioContext blocked/prevented by browser security:", e);
            }
        }

        // --- 4. AJAX Poll Recent Activities ---
        let knownNotificationIds = new Set();
        let isFirstPoll = true;

        function pollNotifications() {
            fetch('{{ route("global.notifications") }}')
                .then(response => {
                    if (!response.ok) throw new Error("HTTP " + response.status);
                    return response.json();
                })
                .then(data => {
                    const notifications = data.notifications;
                    if (!notifications || notifications.length === 0) {
                        notificationList.innerHTML = `
                            <div class="notification-empty">
                                <i class="fa-solid fa-bell-slash"></i>
                                <p>No recent activity logs.</p>
                            </div>
                        `;
                        return;
                    }

                    let html = '';
                    let hasNew = false;

                    notifications.forEach(item => {
                        html += `
                            <div class="notification-item" onclick="window.location.href='${item.url}'">
                                <div class="notification-item-icon">
                                    <i class="fa-solid ${item.icon}"></i>
                                </div>
                                <div class="notification-item-content">
                                    <div class="notification-item-title">${item.type}</div>
                                    <div class="notification-item-msg">${item.message}</div>
                                    <div class="notification-item-time">${item.time}</div>
                                </div>
                            </div>
                        `;

                        // Detect newly discovered notification IDs
                        if (!knownNotificationIds.has(item.id)) {
                            knownNotificationIds.add(item.id);
                            if (!isFirstPoll) {
                                hasNew = true;
                            }
                        }
                    });

                    notificationList.innerHTML = html;

                    if (hasNew) {
                        bellBadge.classList.add('active');
                        bellBtn.classList.add('active-bell');
                        playChime();
                        setTimeout(() => {
                            bellBtn.classList.remove('active-bell');
                        }, 500);
                    }

                    isFirstPoll = false;
                })
                .catch(err => console.log("Failed polling notifications:", err));
        }

        // Poll instantly, then set intervals
        pollNotifications();
        setInterval(pollNotifications, 15000);

        if (clearBellsBtn) {
            clearBellsBtn.addEventListener('click', function() {
                localStorage.setItem('bells_read_timestamp', new Date().toISOString());
                bellBadge.classList.remove('active');
                notificationPanel.classList.remove('active');
            });
        }

        // --- 5. Quick-Search AJAX Engine ---
        const searchInput = document.getElementById('globalSearchInput');
        const searchDropdown = document.getElementById('searchDropdown');
        let searchDebounce = null;

        if (searchInput) {
            searchInput.addEventListener('input', function() {
                clearTimeout(searchDebounce);
                const query = searchInput.value.trim();

                if (query.length < 2) {
                    searchDropdown.classList.remove('active');
                    searchDropdown.innerHTML = '';
                    return;
                }

                searchDebounce = setTimeout(() => {
                    fetch(`/search?q=${encodeURIComponent(query)}`)
                        .then(res => res.json())
                        .then(data => {
                            const results = data.results;
                            if (results.length === 0) {
                                searchDropdown.innerHTML = `
                                    <div class="search-no-results">
                                        <i class="fa-solid fa-face-frown" style="font-size: 1.25rem; color: var(--text-muted); margin-bottom: 0.5rem; display: block;"></i>
                                        No matches found for "${query}"
                                    </div>
                                `;
                            } else {
                                let html = '';
                                results.forEach(item => {
                                    html += `
                                        <div class="search-result-item" onclick="window.location.href='${item.url}'">
                                            <div class="search-result-icon">
                                                <i class="fa-solid ${item.icon}"></i>
                                            </div>
                                            <div class="search-result-info">
                                                <div class="search-result-type">${item.type}</div>
                                                <div class="search-result-title">${item.title}</div>
                                                <div class="search-result-subtitle">${item.subtitle}</div>
                                            </div>
                                        </div>
                                    `;
                                });
                                searchDropdown.innerHTML = html;
                            }
                            searchDropdown.classList.add('active');
                        })
                        .catch(err => console.error("Search failure:", err));
                }, 300);
            });

            searchInput.addEventListener('focus', function() {
                if (searchInput.value.trim().length >= 2) {
                    searchDropdown.classList.add('active');
                }
            });
        }

        // Dismiss overlays on outer clicks
        document.addEventListener('click', function(e) {
            if (searchDropdown && !searchDropdown.contains(e.target) && e.target !== searchInput) {
                searchDropdown.classList.remove('active');
            }
            if (notificationPanel && !notificationPanel.contains(e.target) && e.target !== bellBtn) {
                notificationPanel.classList.remove('active');
            }
        });
    });
    </script>
    
    @yield('scripts')
</body>
</html>
