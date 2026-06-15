<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <span class="brand-icon"><i class="bi bi-brain"></i></span>
        <span class="brand-text">{{ config('app.name') }}</span>
        <span class="brand-badge">v2.0</span>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-section-label">Main</div>

        <div class="nav-item">
            <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="bi bi-grid-1x2-fill"></i>
                <span>Dashboard</span>
            </a>
        </div>

        @role('Admin')
        <div class="nav-item">
            <a href="#usersSubmenu" class="nav-link {{ request()->routeIs('users.*') || request()->routeIs('roles.*') || request()->routeIs('permissions.*') ? 'active' : '' }}"
               data-bs-toggle="collapse" role="button" aria-expanded="{{ request()->routeIs('users.*') || request()->routeIs('roles.*') || request()->routeIs('permissions.*') ? 'true' : 'false' }}">
                <i class="bi bi-people-fill"></i>
                <span>Users</span>
                <i class="bi bi-chevron-down chevron"></i>
            </a>
            <div class="collapse submenu {{ request()->routeIs('users.*') || request()->routeIs('roles.*') || request()->routeIs('permissions.*') ? 'show' : '' }}" id="usersSubmenu">
                @can('view users')
                <a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.index') ? 'active' : '' }}">
                    <i class="bi bi-person"></i>
                    <span>All Users</span>
                </a>
                @endcan
                @can('manage roles')
                <a href="{{ route('roles.index') }}" class="nav-link {{ request()->routeIs('roles.*') ? 'active' : '' }}">
                    <i class="bi bi-shield-check"></i>
                    <span>Roles</span>
                </a>
                @endcan
                @can('manage permissions')
                <a href="{{ route('permissions.index') }}" class="nav-link {{ request()->routeIs('permissions.*') ? 'active' : '' }}">
                    <i class="bi bi-key"></i>
                    <span>Permissions</span>
                </a>
                @endcan
            </div>
        </div>
        @endrole

        <div class="nav-section-label">Study</div>

        <div class="nav-item">
            <a href="{{ route('subjects.index') }}" class="nav-link {{ request()->routeIs('subjects.*') ? 'active' : '' }}">
                <i class="bi bi-bookmark-star-fill"></i>
                <span>Subjects</span>
            </a>
        </div>

        <div class="nav-item">
            <a href="{{ route('documents.index') }}" class="nav-link {{ request()->routeIs('documents.*') ? 'active' : '' }}">
                <i class="bi bi-file-earmark-arrow-up-fill"></i>
                <span>Uploads</span>
            </a>
        </div>

        <div class="nav-item">
            <a href="{{ route('mcqs.index') }}" class="nav-link {{ request()->routeIs('mcqs.*') ? 'active' : '' }}">
                <i class="bi bi-patch-question-fill"></i>
                <span>MCQ Generator</span>
            </a>
        </div>

        <div class="nav-item">
            <a href="{{ route('ai.chat') }}" class="nav-link {{ request()->routeIs('ai.chat') ? 'active' : '' }}">
                <i class="bi bi-robot"></i>
                <span>AI Assistant</span>
            </a>
        </div>
    </nav>

    <div class="sidebar-footer">
        <div class="mini-avatar">
            {{ substr(auth()->user()->name, 0, 1) }}
        </div>
        <div class="sidebar-user-info">
            <div class="name">{{ auth()->user()->name }}</div>
            <div class="role">{{ auth()->user()->roles->first()?->name ?? 'Student' }}</div>
        </div>
        <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form-sidebar').submit();" title="Logout">
            <i class="bi bi-box-arrow-right"></i>
        </a>
        <form id="logout-form-sidebar" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
    </div>
</aside>
