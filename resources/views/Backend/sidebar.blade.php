<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <h4>{{ config('app.name') }}</h4>
    </div>
    <nav class="nav">
        <div class="nav-item">
            <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i>
                <span>Dashboard</span>
            </a>
        </div>
        @can('is admin')
        <div class="nav-item">
            <a href="#" class="nav-link" data-bs-toggle="collapse" data-bs-target="#usersSubmenu"
                aria-expanded="false">
                <i class="bi bi-people"></i>
                <span>Users</span>
                <i class="bi bi-chevron-down ms-auto" style="font-size: 0.8rem;"></i>
            </a>
            <div class="collapse" id="usersSubmenu">
                <a href="{{ route('users.index') }}" class="nav-link ps-5 fs-6">
                    All Users
                </a>
                <a href="{{ route('roles.index') }}" class="nav-link ps-5" style="font-size: 0.9rem;">
                    <span>Roles</span>
                </a>
                <a href="{{ route('permissions.index') }}" class="nav-link ps-5" style="font-size: 0.9rem;">
                    <span>Permissions</span>
                </a>
            </div>
        </div>
        @endcan
        <div class="nav-item">
            <a href="{{route('events.index')}}" class="nav-link">
                <i class="bi bi-clock-history"></i>
                <span>Activities</span>
            </a>
        </div>
        <div class="nav-item">
            <a href="{{ route('ai.chat') }}" class="nav-link">
                <i class="bi bi-robot"></i>
                <span>AI Assistant</span>
            </a>
        </div>
    </nav>
</aside>

<!-- JavaScript for mobile toggle and submenu handling -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggleBtn = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('sidebar');
        if (toggleBtn && sidebar) {
            toggleBtn.addEventListener('click', function() {
                sidebar.classList.toggle('show');
            });
        }

        // Ensure submenus work correctly when sidebar is collapsed
        const sidebarLinks = document.querySelectorAll('.sidebar .nav-link[data-bs-toggle="collapse"]');
        sidebarLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                if (sidebar.offsetWidth < 100) {
                    // If sidebar is collapsed, expand it first
                    sidebar.style.width = '280px';
                    setTimeout(() => {
                        // Trigger collapse after a short delay
                        const target = document.querySelector(link.getAttribute(
                            'data-bs-target'));
                        if (target) {
                            new bootstrap.Collapse(target, {
                                toggle: true
                            });
                        }
                    }, 300);
                }
            });
        });
    });
</script>
