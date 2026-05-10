<nav class="navbar-top">
    <div class="d-flex align-items-center">
        <!-- Mobile toggle button -->
        <button class="sidebar-toggle btn btn-link p-0 me-3 d-lg-none" type="button" id="sidebarToggle">
            <i class="bi bi-list fs-2" style="color: #334155;"></i>
        </button>
        <!-- Search -->
        <div class="search-box">
            <i class="bi bi-search"></i>
            <input type="text" placeholder="Search projects, tasks...">
        </div>
    </div>
    <div class="user-menu">
        <!-- Quick actions dropdown (optional) -->
        <div class="dropdown">
            <button class="btn btn-link p-0" type="button" data-bs-toggle="dropdown">
                <i class="bi bi-grid-3x3-gap-fill fs-4" style="color: #334155;"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end p-2" style="min-width: 200px;">
                <li><a class="dropdown-item rounded-3" href="#"><i class="bi bi-plus-circle me-2"></i>New Project</a></li>
                <li><a class="dropdown-item rounded-3" href="#"><i class="bi bi-person-plus me-2"></i>Add User</a></li>
                <li><a class="dropdown-item rounded-3" href="#"><i class="bi bi-file-earmark me-2"></i>Generate Report</a></li>
            </ul>
        </div>
        <!-- Notifications -->
        <div class="notification-icon">
            <i class="bi bi-bell"></i>
            <span class="notification-badge">3</span>
        </div>
        <!-- User dropdown -->
        <div class="dropdown">
            <div class="user-dropdown" data-bs-toggle="dropdown" aria-expanded="false">
                <div class="user-avatar">
                    {{ substr(auth()->user()->name, 0, 1) }}
                </div>
                <div class="user-info">
                    <div class="name">{{ auth()->user()->name }}</div>
                    <div class="email">{{ auth()->user()->email }}</div>
                </div>
                <i class="bi bi-chevron-down ms-1" style="color: #94a3b8;"></i>
            </div>
            <ul class="dropdown-menu dropdown-menu-end p-2">
                <li><a class="dropdown-item rounded-3" href="{{route('users.show', auth()->user()->id)}}"><i class="bi bi-person me-2"></i>Profile</a></li>
                {{-- <li><a class="dropdown-item rounded-3" href="#"><i class="bi bi-gear me-2"></i>Settings</a></li> --}}
                {{-- <li><a class="dropdown-item rounded-3" href="#"><i class="bi bi-credit-card me-2"></i>Billing</a></li> --}}
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item rounded-3"><i class="bi bi-box-arrow-right me-2"></i>Logout</button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</nav>
