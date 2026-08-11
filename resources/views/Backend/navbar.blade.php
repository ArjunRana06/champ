<nav class="navbar-top">
    <div class="navbar-left">
        <button class="sidebar-toggle-btn d-lg-none" type="button" id="sidebarToggle">
            <i class="bi bi-list"></i>
        </button>
        <div class="search-box" id="searchBoxWrap">
            <form action="{{ route('search') }}" method="GET" id="globalSearchForm" autocomplete="off">
                <i class="bi bi-search"></i>
                <input type="text" name="q" id="globalSearchInput" placeholder="Search subjects, documents..." value="{{ request('q') }}"
                       autocomplete="off">
            </form>
            <div class="search-suggest" id="searchSuggest" hidden>
                <div class="suggest-header">
                    <span class="suggest-title" id="suggestTitle"><i class="bi bi-clock-history"></i> Recent searches</span>
                    <button type="button" class="suggest-clear-all" id="suggestClearAll"><i class="bi bi-trash3"></i> Clear all</button>
                </div>
                <div class="suggest-list" id="suggestList"></div>
                <div class="suggest-empty" id="suggestEmpty">
                    <i class="bi bi-search"></i>
                    <span>No recent searches yet</span>
                    <small>Your searches will appear here.</small>
                </div>
            </div>
        </div>
    </div>

    <div class="navbar-right">
        <div class="dropdown">
            <a href="#" class="nav-icon-btn" title="Notifications" id="notificationBell" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-bell"></i>
                <span class="badge-dot" id="notificationBadge" style="display:none;"></span>
                <span class="badge-number" id="notificationBadgeCount" style="display:none;"></span>
            </a>
            <div class="dropdown-menu dropdown-menu-end p-0" style="min-width: 380px; max-width: 420px;" id="notificationDropdown">
                <div class="dropdown-header-custom d-flex justify-content-between align-items-center px-3 py-3" style="border-bottom: 1px solid var(--divider-color);">
                    <div>
                        <strong style="font-size:0.95rem;color:var(--text-primary);">Notifications</strong>
                        <span class="notif-count-badge ms-2" id="notifHeaderCount">0</span>
                    </div>
                    <div class="d-flex gap-1">
                        <button class="btn-soft py-1 px-2 notif-mark-all-btn" onclick="markAllNotifRead()" title="Mark all read" style="font-size:0.7rem;">
                            <i class="bi bi-check-all"></i>
                        </button>
                        <button class="btn-soft py-1 px-2 notif-clear-btn" onclick="clearAllNotif()" title="Clear all" style="font-size:0.7rem;color:#ef4444;">
                            <i class="bi bi-trash3"></i>
                        </button>
                    </div>
                </div>
                <div id="notificationList" style="max-height:360px;overflow-y:auto;">
                    <div class="text-center py-4" style="color:var(--text-muted);font-size:0.85rem;">
                        <div class="mb-2"><i class="bi bi-bell" style="font-size:1.5rem;"></i></div>
                        Loading...
                    </div>
                </div>
                <div class="text-center py-2" style="border-top:1px solid var(--divider-color);">
                    <a href="{{ route('notifications.index') }}" class="dropdown-item rounded-3 text-center" style="font-size:0.8rem;">
                        View all notifications <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>

        <a href="#" class="nav-icon-btn" title="Quick actions" data-bs-toggle="dropdown">
            <i class="bi bi-grid-3x3-gap"></i>
        </a>
        <ul class="dropdown-menu dropdown-menu-end p-2" style="min-width: 200px;">
            <li><a class="dropdown-item rounded-3" href="{{ route('subjects.create') }}"><i class="bi bi-plus-circle"></i> New Subject</a></li>
            <li><a class="dropdown-item rounded-3" href="{{ route('documents.index') }}"><i class="bi bi-upload"></i> Upload Document</a></li>
            <li><a class="dropdown-item rounded-3" href="{{ route('mcqs.create') }}"><i class="bi bi-patch-question"></i> Generate MCQ</a></li>
        </ul>

        <button id="darkModeToggle" class="nav-icon-btn" title="Toggle dark mode" style="font-size:0.9rem;">
            <i class="bi bi-moon-stars"></i>
        </button>

        <div class="dropdown">
            <a href="#" class="user-dropdown-btn" data-bs-toggle="dropdown" aria-expanded="false">
                <div class="user-avatar-small">
                    {{ substr(auth()->user()->name, 0, 1) }}
                </div>
                <span class="user-name">{{ auth()->user()->name }}</span>
                <i class="bi bi-chevron-down" style="font-size:0.7rem;color:var(--text-muted);"></i>
            </a>
            <ul class="dropdown-menu dropdown-menu-end p-2">
                <li><a class="dropdown-item rounded-3" href="{{ route('users.show', auth()->user()->id) }}"><i class="bi bi-person"></i> Profile</a></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item rounded-3"><i class="bi bi-box-arrow-right"></i> Logout</button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</nav>

<style>
    @keyframes bellPulse {
        0%, 100% { transform: scale(1); }
        25% { transform: scale(1.15); color: #ef4444; }
        50% { transform: scale(1); }
        75% { transform: scale(1.1); color: #f59e0b; }
    }
    #notificationBell.bell-animate {
        animation: bellPulse 0.6s ease-in-out 2;
    }
    .dropdown-header-custom {
        background: var(--glass-bg);
        backdrop-filter: blur(20px);
        border-radius: 1.2rem 1.2rem 0 0;
    }
    .notif-count-badge {
        display: inline-flex; align-items: center; justify-content: center;
        min-width: 20px; height: 20px; border-radius: 10px;
        background: #6366f1; color: white;
        font-size: 0.65rem; font-weight: 700; padding: 0 6px;
    }
    .badge-number {
        position: absolute; top: -2px; right: -6px;
        min-width: 16px; height: 16px; border-radius: 8px;
        background: #ef4444; color: white;
        font-size: 0.55rem; font-weight: 700;
        display: flex; align-items: center; justify-content: center;
        padding: 0 3px; border: 1.5px solid white;
    }
    .notif-item {
        transition: background 0.15s;
        border-bottom: 1px solid var(--divider-color);
    }
    .notif-item:last-child { border-bottom: none; }
    .notif-item:hover { background: var(--table-row-hover); }
    .notif-item.unread {
        background: rgba(99,102,241,0.03);
    }
    .notif-item.unread .notif-title {
        font-weight: 600;
    }
    .notif-icon {
        width: 36px; height: 36px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center; flex-shrink: 0;
        font-size: 0.9rem;
    }
    .notif-title {
        font-size: 0.82rem; color: var(--text-primary);
        display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden;
    }
    .notif-body {
        font-size: 0.75rem; color: var(--text-secondary);
        display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden;
    }
    .notif-time {
        font-size: 0.65rem; color: var(--text-muted); white-space: nowrap;
    }
    @keyframes notifSlideIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .notif-item {
        animation: notifSlideIn 0.2s ease-out;
    }

    /* ---------- Search history autocomplete ---------- */
    .search-box { position: relative; }
    .search-suggest {
        position: absolute;
        top: calc(100% + 10px);
        left: 0;
        right: 0;
        background: var(--dropdown-bg);
        backdrop-filter: blur(24px) saturate(1.8);
        border: 1px solid var(--glass-border);
        border-radius: 1.2rem;
        box-shadow: var(--card-shadow);
        overflow: hidden;
        z-index: 60;
        animation: suggestIn 0.18s ease-out;
    }
    @keyframes suggestIn {
        from { opacity: 0; transform: translateY(-6px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .suggest-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.7rem 1rem 0.55rem;
        border-bottom: 1px solid var(--divider-color);
    }
    .suggest-title {
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: var(--card-accent);
    }
    .suggest-title i { font-size: 0.85rem; margin-right: 0.25rem; }
    .suggest-clear-all {
        background: none;
        border: none;
        font-size: 0.7rem;
        color: var(--text-muted);
        cursor: pointer;
        padding: 0.15rem 0.4rem;
        border-radius: 0.5rem;
        transition: all 0.15s;
        font-family: 'Inter', sans-serif;
    }
    .suggest-clear-all:hover { color: #ef4444; background: rgba(239,68,68,0.08); }
    .suggest-list { max-height: 320px; overflow-y: auto; padding: 0.35rem; }
    .suggest-item {
        display: flex;
        align-items: center;
        gap: 0.7rem;
        width: 100%;
        padding: 0.55rem 0.7rem;
        border-radius: 0.8rem;
        border: none;
        background: none;
        cursor: pointer;
        text-align: left;
        font-family: 'Inter', sans-serif;
        color: var(--text-primary);
        transition: background 0.15s;
        animation: notifSlideIn 0.2s ease-out;
    }
    .suggest-item:hover,
    .suggest-item.active { background: var(--badge-bg); }
    .suggest-item .s-item-icon {
        width: 32px; height: 32px;
        border-radius: 10px;
        background: rgba(99,102,241,0.1);
        color: var(--card-accent);
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .suggest-item .s-item-body { flex: 1; min-width: 0; }
    .suggest-item .s-item-query {
        font-size: 0.85rem;
        font-weight: 500;
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .suggest-item .s-item-meta {
        font-size: 0.65rem;
        color: var(--text-muted);
        margin-top: 1px;
    }
    .suggest-item .s-item-del {
        width: 28px; height: 28px;
        border-radius: 50%;
        border: none;
        background: transparent;
        color: var(--text-muted);
        cursor: pointer;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
        transition: all 0.15s;
    }
    .suggest-item .s-item-del:hover { background: rgba(239,68,68,0.12); color: #ef4444; }
    .suggest-item .s-item-go {
        font-size: 0.85rem;
        color: var(--text-muted);
        flex-shrink: 0;
        opacity: 0;
        transition: opacity 0.15s;
    }
    .suggest-item:hover .s-item-go { opacity: 1; }
    .suggest-empty {
        text-align: center;
        padding: 1.5rem 1rem;
        color: var(--text-muted);
    }
    .suggest-empty i { font-size: 1.6rem; display: block; margin-bottom: 0.4rem; color: #c7d2fe; }
    .suggest-empty span { font-size: 0.85rem; display: block; font-weight: 500; color: var(--text-primary); }
    .suggest-empty small { font-size: 0.72rem; color: var(--text-muted); }
    .suggest-loading {
        padding: 1rem; text-align: center; color: var(--text-muted); font-size: 0.8rem;
    }
    .suggest-spinner {
        display: inline-block; width: 16px; height: 16px;
        border: 2px solid var(--badge-bg); border-top-color: var(--card-accent);
        border-radius: 50%; animation: spin 0.7s linear infinite; margin-right: 0.4rem;
        vertical-align: -3px;
    }
    @keyframes spin { to { transform: rotate(360deg); } }
    .suggest-hint {
        display: flex; align-items: center; gap: 0.6rem;
        padding: 0.5rem 1rem;
        border-top: 1px solid var(--divider-color);
        font-size: 0.65rem; color: var(--text-muted);
    }
    .suggest-hint kbd {
        background: var(--badge-bg); color: var(--card-accent);
        border-radius: 4px; padding: 0 0.35rem;
        font-size: 0.62rem; font-family: 'Inter', sans-serif;
        border: 1px solid var(--glass-border);
    }
    @media (max-width: 576px) {
        .search-box { display: block; width: 100%; }
        .search-suggest { left: 0; right: 0; }
    }
</style>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let lastUnreadCount = 0;
    let notifDropdownOpen = false;

    const escHtml = (s) => {
        if (!s) return '';
        const d = document.createElement('div');
        d.textContent = s;
        return d.innerHTML;
    };

    const typeMeta = {
        'success': { icon: 'bi-check-circle-fill', color: '#059669', bg: '#ecfdf5' },
        'error':   { icon: 'bi-exclamation-circle-fill', color: '#dc2626', bg: '#fef2f2' },
        'warning': { icon: 'bi-exclamation-triangle-fill', color: '#d97706', bg: '#fffbeb' },
        'info':    { icon: 'bi-info-circle-fill', color: '#6366f1', bg: '#eef2ff' },
    };

    function getTypeMeta(type) {
        return typeMeta[type] || typeMeta.info;
    }

    function timeAgo(dateStr) {
        const now = new Date();
        const date = new Date(dateStr);
        const diff = Math.floor((now - date) / 1000);
        if (diff < 60) return 'just now';
        if (diff < 3600) return Math.floor(diff / 60) + 'm ago';
        if (diff < 86400) return Math.floor(diff / 3600) + 'h ago';
        if (diff < 604800) return Math.floor(diff / 86400) + 'd ago';
        return date.toLocaleDateString();
    }

    function updateBadgeUI(count) {
        const badge = document.getElementById('notificationBadge');
        const badgeCount = document.getElementById('notificationBadgeCount');
        const headerCount = document.getElementById('notifHeaderCount');
        const sidebarBadge = document.getElementById('sidebarNotifBadge');
        const bell = document.getElementById('notificationBell');

        if (badgeCount) {
            if (count > 0) {
                badgeCount.style.display = 'flex';
                badgeCount.textContent = count > 99 ? '99+' : count;
            } else {
                badgeCount.style.display = 'none';
            }
        }
        if (badge) {
            badge.style.display = count > 0 ? 'block' : 'none';
        }
        if (headerCount) {
            headerCount.textContent = count || '0';
        }
        if (sidebarBadge) {
            if (count > 0) {
                sidebarBadge.style.display = 'inline';
                sidebarBadge.textContent = count > 99 ? '99+' : count;
            } else {
                sidebarBadge.style.display = 'none';
            }
        }
        if (bell && count > 0) {
            bell.style.animation = 'none';
            void bell.offsetHeight;
            bell.style.animation = 'bellPulse 0.6s ease-in-out 2';
        }
    }

    function fetchUnreadCount() {
        fetch('{{ route("notifications.unread-count") }}', {
            headers: {'X-Requested-With': 'XMLHttpRequest'}
        })
        .then(r => r.json())
        .then(data => {
            const count = data.count || 0;
            if (count !== lastUnreadCount) {
                if (count > lastUnreadCount) {
                    // New notification arrived — show toast
                    const diff = count - lastUnreadCount;
                    showToast(diff + ' new notification' + (diff > 1 ? 's' : ''), 'info');
                    // Play sound if available
                    try {
                        const ctx = new (window.AudioContext || window.webkitAudioContext)();
                        const osc = ctx.createOscillator();
                        const gain = ctx.createGain();
                        osc.connect(gain);
                        gain.connect(ctx.destination);
                        osc.frequency.value = 800;
                        gain.gain.value = 0.08;
                        osc.start();
                        osc.stop(ctx.currentTime + 0.12);
                    } catch (e) {}
                }
                lastUnreadCount = count;
                updateBadgeUI(count);
                // If dropdown is open, refresh full list
                if (notifDropdownOpen) {
                    fetchFullNotifications();
                }
            }
        })
        .catch(() => {});
    }

    function fetchFullNotifications() {
        fetch('{{ route("notifications.index") }}?ajax=1', {
            headers: {'X-Requested-With': 'XMLHttpRequest'}
        })
        .then(r => r.json())
        .then(data => {
            const list = document.getElementById('notificationList');
            lastUnreadCount = data.unread_count || 0;
            updateBadgeUI(lastUnreadCount);

            if (list) {
                if (!data.notifications || data.notifications.length === 0) {
                    list.innerHTML = `
                        <div class="text-center py-4" style="color:var(--text-muted);font-size:0.85rem;">
                            <div class="mb-2"><i class="bi bi-bell-slash" style="font-size:1.5rem;"></i></div>
                            All caught up! No notifications yet.
                        </div>`;
                    return;
                }

                let html = '';
                data.notifications.forEach((n, i) => {
                    const meta = getTypeMeta(n.type);
                    const safeTitle = escHtml(n.title);
                    const safeBody = escHtml(n.body);
                    const safeLink = escHtml(n.link || '#');
                    const unreadClass = n.is_read ? '' : 'unread';
                    const clickAttr = n.is_read ? '' : ` onclick="markNotifRead(${n.id}, this)"`;
                    const style = `animation-delay:${i * 0.03}s`;

                    html += `
                    <a href="${safeLink}" class="dropdown-item rounded-0 notif-item ${unreadClass} d-flex align-items-start gap-2 px-3 py-2 text-decoration-none" style="${style}"${clickAttr}>
                        <div class="notif-icon" style="background:${meta.bg};color:${meta.color};">
                            <i class="bi ${meta.icon}"></i>
                        </div>
                        <div class="flex-grow-1 min-w-0">
                            <div class="notif-title">${safeTitle}</div>
                            <div class="notif-body">${safeBody}</div>
                            <div class="notif-time">${timeAgo(n.created_at)}</div>
                        </div>
                        ${!n.is_read ? `<div class="ms-1" style="width:8px;height:8px;border-radius:50%;background:#6366f1;flex-shrink:0;margin-top:14px;"></div>` : ''}
                    </a>`;
                });
                list.innerHTML = html;
            }
        })
        .catch(() => {
            const list = document.getElementById('notificationList');
            if (list) {
                list.innerHTML = `
                    <div class="text-center py-4" style="color:var(--text-muted);font-size:0.85rem;">
                        <div class="mb-2"><i class="bi bi-wifi-off" style="font-size:1.5rem;"></i></div>
                        Could not load notifications
                    </div>`;
            }
        });
    }

    // Track dropdown open/close
    const bellDropdown = document.getElementById('notificationBell')?.closest('.dropdown');
    if (bellDropdown) {
        bellDropdown.addEventListener('show.bs.dropdown', function () {
            notifDropdownOpen = true;
            fetchFullNotifications();
        });
        bellDropdown.addEventListener('hide.bs.dropdown', function () {
            notifDropdownOpen = false;
        });
    }

    // Start real-time polling (every 3s for count, full list when dropdown open)
    fetchUnreadCount();
    setInterval(fetchUnreadCount, 3000);

    // Also fetch full list in background every 30s for stale data
    setInterval(() => {
        if (!notifDropdownOpen) fetchFullNotifications();
    }, 30000);

    // Pause when tab hidden
    document.addEventListener('visibilitychange', () => {
        if (!document.hidden) {
            fetchUnreadCount();
        }
    });

    window.markNotifRead = function(id, el) {
        fetch('/notifications/' + id + '/read', {method:'POST', headers:{
            'X-CSRF-TOKEN':'{{ csrf_token() }}'
        }}).then(() => {
            if (el) {
                el.classList.remove('unread');
                const dot = el.querySelector('.ms-1');
                if (dot) dot.remove();
            }
            lastUnreadCount = Math.max(0, lastUnreadCount - 1);
            updateBadgeUI(lastUnreadCount);
        });
    };

    function updateSidebarBadge(count) {
        const sidebarBadge = document.getElementById('sidebarNotifBadge');
        if (sidebarBadge) {
            if (count > 0) {
                sidebarBadge.style.display = 'inline';
                sidebarBadge.textContent = count > 99 ? '99+' : count;
            } else {
                sidebarBadge.style.display = 'none';
            }
        }
    }

    window.markAllNotifRead = function() {
        fetch('/notifications/read-all', {method:'POST', headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}'}})
        .then(() => {
            lastUnreadCount = 0;
            updateBadgeUI(0);
            document.querySelectorAll('.notif-item.unread').forEach(el => {
                el.classList.remove('unread');
                const dot = el.querySelector('.ms-1');
                if (dot) dot.remove();
            });
            if (notifDropdownOpen) fetchFullNotifications();
        });
    };

    window.clearAllNotif = function() {
        if (!confirm('Clear all notifications?')) return;
        fetch('/notifications', {method:'DELETE', headers:{
            'X-CSRF-TOKEN':'{{ csrf_token() }}'
        }}).then(() => {
            lastUnreadCount = 0;
            updateBadgeUI(0);
            if (notifDropdownOpen) fetchFullNotifications();
        });
    };
});

// ---------- Search history autocomplete ----------
(function () {
    const wrap = document.getElementById('searchBoxWrap');
    if (!wrap) return;

    const input = document.getElementById('globalSearchInput');
    const panel = document.getElementById('searchSuggest');
    const listEl = document.getElementById('suggestList');
    const titleEl = document.getElementById('suggestTitle');
    const clearAllBtn = document.getElementById('suggestClearAll');
    const emptyEl = document.getElementById('suggestEmpty');
    const form = document.getElementById('globalSearchForm');

    let items = [];
    let activeIndex = -1;
    let lastTerm = null;

    function escHtml(s) {
        if (!s) return '';
        const d = document.createElement('div');
        d.textContent = s;
        return d.innerHTML;
    }

    function render() {
        listEl.innerHTML = '';
        if (!items.length) {
            panel.classList.add('show-empty');
            emptyEl.hidden = false;
            return;
        }
        panel.classList.remove('show-empty');
        emptyEl.hidden = true;

        items.forEach((item, i) => {
            const row = document.createElement('button');
            row.type = 'button';
            row.className = 'suggest-item' + (i === activeIndex ? ' active' : '');
            row.style.animationDelay = (i * 0.025) + 's';
            const goArrow = input.value.trim() === '' ? '<span class="s-item-go"><i class="bi bi-arrow-return-left"></i></span>' : '';
            row.innerHTML = `
                <span class="s-item-icon"><i class="bi bi-search"></i></span>
                <span class="s-item-body">
                    <span class="s-item-query">${escHtml(item.query)}</span>
                    <span class="s-item-meta">${item.result_count != null ? item.result_count + ' result' + (item.result_count === 1 ? '' : 's') + ' · ' : ''}${escHtml(item.searched_at)}</span>
                </span>
                ${goArrow}
                <span class="s-item-del" data-del="${item.id}" title="Remove"><i class="bi bi-x-lg"></i></span>
            `;

            row.querySelector('.s-item-body').addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                input.value = item.query;
                form.submit();
            });

            row.querySelector('.s-item-del').addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                deleteItem(item.id, row);
            });

            listEl.appendChild(row);
        });

        if (clearAllBtn) clearAllBtn.style.display = items.length ? 'inline-flex' : 'none';
    }

    function open() {
        panel.hidden = false;
    }

    function close() {
        panel.hidden = true;
        activeIndex = -1;
    }

    function fetchSuggestions() {
        const term = input.value.trim();
        if (term === lastTerm) { open(); return; }
        lastTerm = term;

        if (term !== '') {
            titleEl.innerHTML = '<i class="bi bi-search"></i> Search suggestions';
        } else {
            titleEl.innerHTML = '<i class="bi bi-clock-history"></i> Recent searches';
        }

        listEl.innerHTML = '<div class="suggest-loading"><span class="suggest-spinner"></span>Loading...</div>';
        emptyEl.hidden = true;
        open();

        const params = new URLSearchParams();
        if (term) params.set('q', term);

        fetch('{{ route("search.history") }}?' + params.toString(), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(data => {
            items = data.history || [];
            activeIndex = -1;
            render();
        })
        .catch(() => {
            items = [];
            render();
        });
    }

    function deleteItem(id, rowEl) {
        fetch('{{ route("search.history.destroy", ':id') }}'.replace(':id', id), {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        })
        .then(r => r.json())
        .then(() => {
            if (rowEl) rowEl.style.opacity = '0';
            setTimeout(() => {
                items = items.filter(i => i.id !== id);
                render();
                if (items.length) {
                    open();
                } else {
                    close();
                }
            }, 120);
        });
    }

    function clearAll() {
        if (!confirm('Clear your entire search history?')) return;
        fetch('{{ route("search.history.clear") }}', {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        })
        .then(r => r.json())
        .then(() => {
            items = [];
            render();
            close();
        });
    }

    function setActive(dir) {
        if (!items.length) return;
        activeIndex = (activeIndex + dir + items.length) % items.length;
        const rows = listEl.querySelectorAll('.suggest-item');
        rows.forEach((r, i) => r.classList.toggle('active', i === activeIndex));
        rows[activeIndex]?.scrollIntoView({ block: 'nearest' });
    }

    input.addEventListener('focus', () => fetchSuggestions());

    input.addEventListener('input', () => { lastTerm = null; fetchSuggestions(); });

    input.addEventListener('keydown', (e) => {
        if (e.key === 'ArrowDown') { e.preventDefault(); open(); setActive(1); }
        else if (e.key === 'ArrowUp') { e.preventDefault(); setActive(-1); }
        else if (e.key === 'Enter') {
            if (activeIndex >= 0 && items[activeIndex]) {
                e.preventDefault();
                input.value = items[activeIndex].query;
                form.submit();
            }
        }
        else if (e.key === 'Escape') { close(); }
    });

    document.addEventListener('click', (e) => {
        if (!wrap.contains(e.target)) close();
    });

    if (clearAllBtn) clearAllBtn.addEventListener('click', clearAll);
})();
</script>
@endpush
