<nav class="navbar-top">
    <div class="navbar-left">
        <button class="sidebar-toggle-btn d-lg-none" type="button" id="sidebarToggle">
            <i class="bi bi-list"></i>
        </button>
        <form action="{{ route('search') }}" method="GET" class="search-box">
            <i class="bi bi-search"></i>
            <input type="text" name="q" placeholder="Search subjects, documents..." value="{{ request('q') }}"
                   onkeydown="if(event.key==='Enter') this.form.submit()">
        </form>
    </div>

    <div class="navbar-right">
        <div class="dropdown">
            <a href="#" class="nav-icon-btn" title="Notifications" id="notificationBell" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-bell"></i>
                <span class="badge-dot" id="notificationBadge" style="display:none;"></span>
            </a>
            <div class="dropdown-menu dropdown-menu-end p-2" style="min-width: 320px;" id="notificationDropdown">
                <div class="d-flex justify-content-between align-items-center px-2 py-1">
                    <strong style="font-size:0.85rem;color:var(--text-primary);">Notifications</strong>
                    <button class="btn-soft py-1 px-2" style="font-size:0.7rem;" onclick="markAllNotifRead()">Mark all read</button>
                </div>
                <div id="notificationList" style="max-height:300px;overflow-y:auto;">
                    <div class="text-center py-3" style="color:var(--text-muted);font-size:0.85rem;">Loading...</div>
                </div>
                <hr class="my-1">
                <a href="{{ route('notifications.index') }}" class="dropdown-item rounded-3 text-center" style="font-size:0.8rem;">View all</a>
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    function fetchNotifications() {
        fetch('{{ route("notifications.index") }}?ajax=1', {
            headers: {'X-Requested-With': 'XMLHttpRequest'}
        })
        .then(r => r.json())
        .then(data => {
            const list = document.getElementById('notificationList');
            const badge = document.getElementById('notificationBadge');
            if (badge) {
                if (data.unread_count > 0) {
                    badge.style.display = 'block';
                } else {
                    badge.style.display = 'none';
                }
            }
            if (list) {
                if (!data.notifications || data.notifications.length === 0) {
                    list.innerHTML = '<div class="text-center py-3" style="color:var(--text-muted);font-size:0.85rem;">No notifications yet</div>';
                    return;
                }
                list.innerHTML = data.notifications.map(n => `
                    <a href="${n.link || '#'}" class="dropdown-item rounded-3 ${n.is_read ? '' : 'fw-bold'}" style="font-size:0.82rem;white-space:normal;border-bottom:1px solid var(--divider-color);padding:0.5rem 0.7rem;" onclick="if(!${n.is_read}) markNotifRead(${n.id})">
                        <div style="color:var(--text-primary);">${n.title}</div>
                        <small style="color:var(--text-secondary);">${n.body || ''}</small>
                    </a>
                `).join('');
            }
        })
        .catch(() => {});
    }
    fetchNotifications();
    setInterval(fetchNotifications, 30000);

    window.markNotifRead = function(id) {
        fetch('/notifications/' + id + '/read', {method:'POST', headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}'}});
    };
    window.markAllNotifRead = function() {
        fetch('/notifications/read-all', {method:'POST', headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}'}})
        .then(() => fetchNotifications());
    };
});
</script>
@endpush
