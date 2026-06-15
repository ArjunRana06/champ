<nav class="navbar-top">
    <div class="navbar-left">
        <button class="sidebar-toggle-btn d-lg-none" type="button" id="sidebarToggle">
            <i class="bi bi-list"></i>
        </button>
        <div class="search-box">
            <i class="bi bi-search"></i>
            <input type="text" placeholder="Search subjects, documents...">
        </div>
    </div>

    <div class="navbar-right">
        <a href="#" class="nav-icon-btn" title="Notifications">
            <i class="bi bi-bell"></i>
            <span class="badge-dot"></span>
        </a>

        <a href="#" class="nav-icon-btn" title="Quick actions" data-bs-toggle="dropdown">
            <i class="bi bi-grid-3x3-gap"></i>
        </a>
        <ul class="dropdown-menu dropdown-menu-end p-2" style="min-width: 200px;">
            <li><a class="dropdown-item rounded-3" href="{{ route('subjects.create') }}"><i class="bi bi-plus-circle"></i> New Subject</a></li>
            <li><a class="dropdown-item rounded-3" href="{{ route('documents.index') }}"><i class="bi bi-upload"></i> Upload Document</a></li>
            <li><a class="dropdown-item rounded-3" href="{{ route('mcqs.create') }}"><i class="bi bi-patch-question"></i> Generate MCQ</a></li>
        </ul>

        <div class="dropdown">
            <a href="#" class="user-dropdown-btn" data-bs-toggle="dropdown" aria-expanded="false">
                <div class="user-avatar-small">
                    {{ substr(auth()->user()->name, 0, 1) }}
                </div>
                <span class="user-name">{{ auth()->user()->name }}</span>
                <i class="bi bi-chevron-down" style="font-size:0.7rem;color:#9ca3af;"></i>
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
