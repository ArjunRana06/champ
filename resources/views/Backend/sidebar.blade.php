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
            <a href="#questionsSubmenu" class="nav-link {{ request()->routeIs('mcqs.*') || request()->routeIs('true-false.*') || request()->routeIs('short-answers.*') || request()->routeIs('fill-blanks.*') || request()->routeIs('matching.*') || request()->routeIs('flashcards.*') ? 'active' : '' }}"
               data-bs-toggle="collapse" role="button" aria-expanded="{{ request()->routeIs('mcqs.*') || request()->routeIs('true-false.*') || request()->routeIs('short-answers.*') || request()->routeIs('fill-blanks.*') || request()->routeIs('matching.*') || request()->routeIs('flashcards.*') ? 'true' : 'false' }}">
                <i class="bi bi-patch-question-fill"></i>
                <span>Question Generator</span>
                <i class="bi bi-chevron-down chevron"></i>
            </a>
            <div class="collapse submenu {{ request()->routeIs('mcqs.*') || request()->routeIs('true-false.*') || request()->routeIs('short-answers.*') || request()->routeIs('fill-blanks.*') || request()->routeIs('matching.*') || request()->routeIs('flashcards.*') ? 'show' : '' }}" id="questionsSubmenu">
                <a href="{{ route('mcqs.index') }}" class="nav-link {{ request()->routeIs('mcqs.*') ? 'active' : '' }}">
                    <i class="bi bi-ui-radios"></i>
                    <span>Multiple Choice</span>
                </a>
                <a href="{{ route('true-false.index') }}" class="nav-link {{ request()->routeIs('true-false.*') ? 'active' : '' }}">
                    <i class="bi bi-check2-circle"></i>
                    <span>True / False</span>
                </a>
                <a href="{{ route('short-answers.index') }}" class="nav-link {{ request()->routeIs('short-answers.*') ? 'active' : '' }}">
                    <i class="bi bi-pencil-square"></i>
                    <span>Short Answer</span>
                </a>
                <a href="{{ route('fill-blanks.index') }}" class="nav-link {{ request()->routeIs('fill-blanks.*') ? 'active' : '' }}">
                    <i class="bi bi-input-cursor-text"></i>
                    <span>Fill in the Blank</span>
                </a>
                <a href="{{ route('matching.index') }}" class="nav-link {{ request()->routeIs('matching.*') ? 'active' : '' }}">
                    <i class="bi bi-arrow-left-right"></i>
                    <span>Matching</span>
                </a>
                <a href="{{ route('flashcards.index') }}" class="nav-link {{ request()->routeIs('flashcards.*') ? 'active' : '' }}">
                    <i class="bi bi-card-text"></i>
                    <span>Flashcards</span>
                </a>
            </div>
        </div>

        <div class="nav-item">
            <a href="{{ route('ai.chat') }}" class="nav-link {{ request()->routeIs('ai.chat') ? 'active' : '' }}">
                <i class="bi bi-robot"></i>
                <span>AI Assistant</span>
            </a>
        </div>

        @role('Admin')
        <div class="nav-item">
            <a href="{{ route('ai.settings') }}" class="nav-link {{ request()->routeIs('ai.settings') ? 'active' : '' }}">
                <i class="bi bi-gear"></i>
                <span>AI Settings</span>
            </a>
        </div>
        @endrole

        <div class="nav-section-label">Practice</div>

        <div class="nav-item">
            <a href="{{ route('quiz-attempts.index') }}" class="nav-link {{ request()->routeIs('quiz-attempts.*') ? 'active' : '' }}">
                <i class="bi bi-pencil-square"></i>
                <span>Quiz</span>
            </a>
        </div>

        <div class="nav-item">
            <a href="{{ route('study-plans.index') }}" class="nav-link {{ request()->routeIs('study-plans.*') ? 'active' : '' }}">
                <i class="bi bi-calendar-week"></i>
                <span>Study Plans</span>
            </a>
        </div>

        <div class="nav-item">
            <a href="{{ route('exams.index') }}" class="nav-link {{ request()->routeIs('exams.*') ? 'active' : '' }}">
                <i class="bi bi-calendar-check"></i>
                <span>Exam Calendar</span>
            </a>
        </div>

        <div class="nav-item">
            <a href="{{ route('pomodoro.index') }}" class="nav-link {{ request()->routeIs('pomodoro.*') ? 'active' : '' }}">
                <i class="bi bi-clock-history"></i>
                <span>Pomodoro Timer</span>
            </a>
        </div>

        <div class="nav-item">
            <a href="{{ route('time-entries.index') }}" class="nav-link {{ request()->routeIs('time-entries.*') ? 'active' : '' }}">
                <i class="bi bi-stopwatch"></i>
                <span>Time Tracking</span>
            </a>
        </div>

        <div class="nav-section-label">Tools</div>

        <div class="nav-item">
            <a href="{{ route('bookmarks.index') }}" class="nav-link {{ request()->routeIs('bookmarks.*') ? 'active' : '' }}">
                <i class="bi bi-bookmark"></i>
                <span>Bookmarks</span>
            </a>
        </div>

        <div class="nav-item">
            <a href="{{ route('notifications.index') }}" class="nav-link {{ request()->routeIs('notifications.*') ? 'active' : '' }}" id="sidebarNotifLink">
                <i class="bi bi-bell"></i>
                <span>Notifications</span>
                <span class="sidebar-badge" id="sidebarNotifBadge" style="display:none;margin-left:auto;background:#ef4444;color:white;font-size:0.6rem;padding:0.1rem 0.45rem;border-radius:20px;font-weight:700;">0</span>
            </a>
        </div>

        <div class="nav-item">
            <a href="{{ route('export.form') }}" class="nav-link {{ request()->routeIs('export.*') ? 'active' : '' }}">
                <i class="bi bi-download"></i>
                <span>Export</span>
            </a>
        </div>

        <div class="nav-section-label">Community</div>

        <div class="nav-item">
            <a href="{{ route('study-groups.index') }}" class="nav-link {{ request()->routeIs('study-groups.*') ? 'active' : '' }}">
                <i class="bi bi-people"></i>
                <span>Study Groups</span>
            </a>
        </div>

        <div class="nav-item">
            <a href="{{ route('shared-questions.index') }}" class="nav-link {{ request()->routeIs('shared-questions.*') ? 'active' : '' }}">
                <i class="bi bi-share"></i>
                <span>Shared Questions</span>
            </a>
        </div>

        <div class="nav-item">
            <a href="{{ route('peer-reviews.index') }}" class="nav-link {{ request()->routeIs('peer-reviews.*') ? 'active' : '' }}">
                <i class="bi bi-star"></i>
                <span>Peer Reviews</span>
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
        <form id="logout-form-sidebar" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
        <button type="button" onclick="event.preventDefault(); document.getElementById('logout-form-sidebar').submit();" title="Logout" style="background:none;border:none;color:rgba(255,255,255,0.4);font-size:1rem;cursor:pointer;padding:0;">
            <i class="bi bi-box-arrow-right"></i>
        </button>
    </div>
</aside>
