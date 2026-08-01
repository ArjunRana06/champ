@extends('Backend.master')

@section('content')
<div class="container-fluid px-0">
    <!-- Welcome -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3" data-aos="fade-right">
        <div>
            <h2 class="fw-bold" style="color:var(--text-primary);">Welcome back, {{ auth()->user()->name }}!</h2>
            <p style="color:var(--text-secondary);">Your AI‑powered study dashboard.</p>
        </div>
        <div class="glass-card d-flex align-items-center gap-2 py-2 px-3" style="border-radius: 40px;">
            <i class="bi bi-calendar3" style="color:var(--card-accent);"></i>
            <span class="fw-medium" style="color:var(--text-primary); font-size: 0.9rem;">{{ now()->format('l, F j, Y') }}</span>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="d-flex gap-2 mb-4 flex-wrap" data-aos="fade-down">
        <a href="{{ route('subjects.create') }}" class="btn-soft shadow-sm"><i class="bi bi-plus-circle"></i> New Subject</a>
        <a href="{{ route('documents.index') }}" class="btn-soft shadow-sm"><i class="bi bi-upload"></i> Upload Notes</a>
        <a href="{{ route('mcqs.create') }}" class="btn-soft shadow-sm"><i class="bi bi-patch-question"></i> Generate MCQ</a>
        <a href="{{ route('flashcards.create') }}" class="btn-soft shadow-sm"><i class="bi bi-card-text"></i> Flashcards</a>
        <a href="{{ route('ai.chat') }}" class="btn-soft shadow-sm"><i class="bi bi-robot"></i> AI Chat</a>
        <a href="{{ route('quiz-attempts.create') }}" class="btn-soft shadow-sm"><i class="bi bi-pencil-square"></i> Take Quiz</a>
        <a href="{{ route('study-plans.index') }}" class="btn-soft shadow-sm"><i class="bi bi-calendar-week"></i> Study Plans</a>
    </div>

    <!-- Stats Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3" data-aos="fade-up" data-aos-delay="100">
            <div class="glass-card">
                <div class="d-flex align-items-center gap-3 mb-2">
                    <div style="width:50px;height:50px;border-radius:16px;background:linear-gradient(135deg,#6366f1,#818cf8);display:flex;align-items:center;justify-content:center;font-size:1.5rem;color:white;box-shadow:0 8px 16px rgba(99,102,241,0.2);">
                        <i class="bi bi-journal-bookmark-fill"></i>
                    </div>
                    <div>
                        <h6 style="color:var(--card-accent);font-size:0.65rem;text-transform:uppercase;letter-spacing:0.05em;font-weight:700;margin:0;">Subjects</h6>
                        <h3 class="fw-bold mb-0" style="color:var(--text-primary);font-size:1.7rem;">{{ number_format($subjectsCount) }}</h3>
                    </div>
                </div>
                <span class="stat-badge up"><i class="bi bi-arrow-up"></i> {{ $subjectsTrend }}</span>
                <small style="color:var(--text-muted);font-size:0.7rem;"> vs last month</small>
            </div>
        </div>
        <div class="col-md-3" data-aos="fade-up" data-aos-delay="200">
            <div class="glass-card">
                <div class="d-flex align-items-center gap-3 mb-2">
                    <div style="width:50px;height:50px;border-radius:16px;background:linear-gradient(135deg,#a855f7,#c084fc);display:flex;align-items:center;justify-content:center;font-size:1.5rem;color:white;box-shadow:0 8px 16px rgba(168,85,247,0.2);">
                        <i class="bi bi-file-earmark-text-fill"></i>
                    </div>
                    <div>
                        <h6 style="color:var(--card-accent);font-size:0.65rem;text-transform:uppercase;letter-spacing:0.05em;font-weight:700;margin:0;">Documents</h6>
                        <h3 class="fw-bold mb-0" style="color:var(--text-primary);font-size:1.7rem;">{{ number_format($documentsCount) }}</h3>
                    </div>
                </div>
                <span class="stat-badge up"><i class="bi bi-arrow-up"></i> {{ $documentsTrend }}</span>
                <small style="color:var(--text-muted);font-size:0.7rem;"> vs last month</small>
            </div>
        </div>
        <div class="col-md-3" data-aos="fade-up" data-aos-delay="300">
            <div class="glass-card">
                <div class="d-flex align-items-center gap-3 mb-2">
                    <div style="width:50px;height:50px;border-radius:16px;background:linear-gradient(135deg,#f59e0b,#fbbf24);display:flex;align-items:center;justify-content:center;font-size:1.5rem;color:white;box-shadow:0 8px 16px rgba(245,158,11,0.2);">
                        <i class="bi bi-patch-question-fill"></i>
                    </div>
                    <div>
                        <h6 style="color:var(--card-accent);font-size:0.65rem;text-transform:uppercase;letter-spacing:0.05em;font-weight:700;margin:0;">Questions</h6>
                        <h3 class="fw-bold mb-0" style="color:#1e1b4b;font-size:1.7rem;">{{ number_format($totalQuestions) }}</h3>
                    </div>
                </div>
                <span class="stat-badge up"><i class="bi bi-arrow-up"></i> {{ $questionsTrend }}</span>
                <small style="color:var(--text-muted);font-size:0.7rem;"> vs last month</small>
            </div>
        </div>
        <div class="col-md-3" data-aos="fade-up" data-aos-delay="400">
            <div class="glass-card">
                <div class="d-flex align-items-center gap-3 mb-2">
                    <div style="width:50px;height:50px;border-radius:16px;background:linear-gradient(135deg,#ec4899,#f472b6);display:flex;align-items:center;justify-content:center;font-size:1.5rem;color:white;box-shadow:0 8px 16px rgba(236,72,153,0.2);">
                        <i class="bi bi-bell-fill"></i>
                    </div>
                    <div>
                        <h6 style="color:var(--card-accent);font-size:0.65rem;text-transform:uppercase;letter-spacing:0.05em;font-weight:700;margin:0;">Notifications</h6>
                        <h3 class="fw-bold mb-0" style="color:#1e1b4b;font-size:1.7rem;">{{ number_format($unreadNotificationsCount) }}</h3>
                    </div>
                </div>
                <span style="color:var(--text-muted);font-size:0.7rem;">unread notifications</span>
                <a href="{{ route('notifications.index') }}" class="float-end" style="font-size:0.7rem;color:var(--card-accent);text-decoration:none;">View all</a>
            </div>
        </div>
    </div>

    <!-- Performance Analytics & New Features -->
    <div class="row g-4 mb-4">
        <div class="col-md-3" data-aos="fade-up" data-aos-delay="100">
            <div class="glass-card text-center">
                <div style="font-size:1.8rem;font-weight:800;color:#6366f1;">{{ number_format($quizAttempts) }}</div>
                <small style="color:var(--text-secondary);">Quizzes Taken</small>
                @if($avgScore)
                <div style="margin-top:0.3rem;font-size:0.8rem;color:var(--stat-up-color);">Avg Score: {{ number_format($avgScore, 1) }}%</div>
                @endif
            </div>
        </div>
        <div class="col-md-3" data-aos="fade-up" data-aos-delay="200">
            <div class="glass-card text-center">
                <div style="font-size:1.8rem;font-weight:800;color:#a855f7;">{{ number_format($studyPlansCount) }}</div>
                <small style="color:var(--text-secondary);">Study Plans</small>
            </div>
        </div>
        <div class="col-md-3" data-aos="fade-up" data-aos-delay="300">
            <div class="glass-card text-center">
                <div style="font-size:1.8rem;font-weight:800;color:#0ea5e9;">{{ number_format($focusMinutes) }}</div>
                <small style="color:var(--text-secondary);">Focus Minutes</small>
            </div>
        </div>
        <div class="col-md-3" data-aos="fade-up" data-aos-delay="400">
            <div class="glass-card text-center">
                <div style="font-size:1.8rem;font-weight:800;color:#10b981;">{{ number_format($pomodoroCount) }}</div>
                <small style="color:var(--text-secondary);">Pomodoros</small>
            </div>
        </div>
    </div>

    @if($recentQuizzes->count())
    <div class="glass-card mb-4" data-aos="fade-up">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h5 style="color:var(--text-primary);font-weight:700;font-size:1rem;margin:0;">
                <i class="bi bi-trophy me-2" style="color:#f59e0b;"></i> Recent Quiz Results
            </h5>
            <a href="{{ route('quiz-attempts.index') }}" class="btn-soft py-1 px-3" style="font-size:0.75rem;">View all</a>
        </div>
        <div class="table-responsive">
            <table class="glass-table">
                <thead><tr><th>Date</th><th>Title</th><th>Score</th><th>Type</th></tr></thead>
                <tbody>
                    @foreach($recentQuizzes as $q)
                    <tr>
                        <td>{{ $q->created_at->format('M d, Y') }}</td>
                        <td>{{ $q->title ?? 'Quiz #'.$q->id }}</td>
                        <td>
                            @if($q->total_questions > 0)
                            <span style="font-weight:600;color:{{ $q->score_percentage >= 80 ? '#10b981' : ($q->score_percentage >= 50 ? '#f59e0b' : '#ef4444') }};">{{ number_format($q->score_percentage, 1) }}%</span>
                            @else
                            <span style="color:var(--text-muted);">In progress</span>
                            @endif
                        </td>
                        <td><span class="stat-badge up" style="font-size:0.65rem;">{{ $q->is_exam_mode ? 'Exam' : 'Practice' }}</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- Charts -->
    <div class="row g-4 mb-4">
        <div class="col-md-8" data-aos="fade-right">
            <div class="glass-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 style="color:var(--text-primary);font-weight:700;font-size:1rem;margin:0;">
                        <i class="bi bi-graph-up me-2" style="color:var(--card-accent);"></i> Questions Generated
                    </h5>
                </div>
                <canvas id="revenueChart" height="240"></canvas>
            </div>
        </div>
        <div class="col-md-4" data-aos="fade-left">
            <div class="glass-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 style="color:var(--text-primary);font-weight:700;font-size:1rem;margin:0;">
                        <i class="bi bi-pie-chart me-2" style="color:var(--card-accent);"></i> Content Mix
                    </h5>
                </div>
                <canvas id="trafficChart" height="240"></canvas>
            </div>
        </div>
    </div>

    <!-- Top Subjects & Recent Activity -->
    <div class="row g-4 mb-4">
        <div class="col-md-5" data-aos="fade-right" data-aos-delay="100">
            <div class="glass-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 style="color:var(--text-primary);font-weight:700;font-size:1rem;margin:0;">
                        <i class="bi bi-trophy me-2" style="color:#f59e0b;"></i> Top Subjects
                    </h5>
                    <a href="{{ route('subjects.index') }}" class="btn-soft py-1 px-3" style="font-size:0.75rem;">View all</a>
                </div>
                <div class="table-responsive">
                    <table class="glass-table">
                        <thead>
                            <tr><th>Subject</th><th>Docs</th><th>Questions</th></tr>
                        </thead>
                        <tbody>
                            @forelse($topSubjects as $sub)
                            <tr>
                                <td style="font-weight:500;">{{ $sub['name'] }} <span style="color:var(--text-muted);font-size:0.7rem;">{{ $sub['code'] ? '('. $sub['code'] .')' : '' }}</span></td>
                                <td>{{ $sub['documents'] }}</td>
                                <td>{{ $sub['questions'] }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="3" style="text-align:center;color:var(--text-muted);padding:1.5rem 0;">No subjects yet</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-7" data-aos="fade-left" data-aos-delay="200">
            <div class="glass-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 style="color:var(--text-primary);font-weight:700;font-size:1rem;margin:0;">
                        <i class="bi bi-clock-history me-2" style="color:var(--card-accent);"></i> Recent Activity
                    </h5>
                </div>
                <ul class="list-unstyled">
                    @forelse($recentActivities as $activity)
                    <li class="d-flex align-items-center gap-3 mb-2 p-2 rounded-3" style="transition:all 0.15s;">
                        <span style="width:8px;height:8px;border-radius:50%;background:{{ $activity->emotions->first()?->color ?? '#6366f1' }};flex-shrink:0;"></span>
                        <div class="flex-grow-1" style="min-width:0;">
                            <div style="color:var(--text-primary);font-size:0.85rem;font-weight:500;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ Str::limit($activity->parsed_content ?? $activity->type, 60) }}</div>
                            <small style="color:var(--text-muted);font-size:0.7rem;">{{ $activity->created_at->diffForHumans() }}</small>
                        </div>
                        <i class="bi bi-{{ $activity->type === 'text' ? 'chat-text' : ($activity->type === 'image' ? 'image' : 'camera-reels') }}" style="color:var(--text-muted);"></i>
                    </li>
                    @empty
                    <li style="color:var(--text-muted);text-align:center;padding:1.5rem 0;">No recent activity yet.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>

    <!-- Recent Notifications -->
    <div class="glass-card mb-4" data-aos="fade-up">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 style="color:var(--text-primary);font-weight:700;font-size:1rem;margin:0;">
                <i class="bi bi-bell me-2" style="color:var(--card-accent);"></i> Recent Notifications
            </h5>
            <a href="{{ route('notifications.index') }}" class="btn-soft py-1 px-3" style="font-size:0.75rem;">View all</a>
        </div>
        @if($recentNotifications->count() > 0)
            <div style="max-height:280px;overflow-y:auto;">
                @foreach($recentNotifications as $notif)
                    @php $meta = \App\Services\NotificationService::getTypeMeta($notif->type); @endphp
                    <a href="{{ $notif->link ?: '#' }}" class="d-flex align-items-start gap-2 px-2 py-2 text-decoration-none" style="border-bottom:1px solid var(--divider-color);transition:background 0.15s;border-radius:0.5rem;{{ !$notif->is_read ? 'background:rgba(99,102,241,0.03);' : '' }}"
                       onmouseover="this.style.background='var(--table-row-hover)'" onmouseout="this.style.background='{{ !$notif->is_read ? 'rgba(99,102,241,0.03)' : 'transparent' }}'">
                        <div style="width:32px;height:32px;border-radius:50%;background:{{ $meta['bg'] }};color:{{ $meta['color'] }};display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:0.8rem;">
                            <i class="bi {{ $meta['icon'] }}"></i>
                        </div>
                        <div class="flex-grow-1 min-w-0">
                            <div style="font-size:0.82rem;color:var(--text-primary);{{ !$notif->is_read ? 'font-weight:600;' : '' }}white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $notif->title }}</div>
                            <div style="font-size:0.7rem;color:var(--text-muted);">{{ $notif->created_at->diffForHumans() }}</div>
                        </div>
                        @if(!$notif->is_read)
                            <div style="width:6px;height:6px;border-radius:50%;background:#6366f1;flex-shrink:0;margin-top:12px;"></div>
                        @endif
                    </a>
                @endforeach
            </div>
        @else
            <div class="text-center py-3" style="color:var(--text-muted);font-size:0.85rem;">
                <i class="bi bi-bell-slash me-1"></i> No notifications yet.
            </div>
        @endif
    </div>

    <!-- Recent Documents -->
    <div class="glass-card" data-aos="fade-up">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 style="color:var(--text-primary);font-weight:700;font-size:1rem;margin:0;">
                <i class="bi bi-file-earmark-text me-2" style="color:var(--card-accent);"></i> Recent Documents
            </h5>
            <a href="{{ route('documents.index') }}" class="btn-soft py-1 px-3" style="font-size:0.75rem;">View all</a>
        </div>
        <div class="table-responsive">
            <table class="glass-table">
                <thead>
                    <tr><th>Title</th><th>Subject</th><th>Date</th><th>Status</th></tr>
                </thead>
                <tbody>
                    @forelse($recentDocuments as $doc)
                    <tr>
                        <td><i class="bi bi-file-earmark-text me-2" style="color:#6366f1;"></i> {{ $doc->title }}</td>
                        <td><span style="font-size:0.7rem;padding:0.15rem 0.6rem;border-radius:20px;background:#eef2ff;color:#6366f1;">{{ $doc->subject }}</span></td>
                        <td style="color:var(--text-secondary);">{{ $doc->date }}</td>
                        <td><span class="stat-badge up">{{ ucfirst($doc->status) }}</span></td>
                    </tr>
                    @empty
                    <tr><td colspan="4" style="text-align:center;color:var(--text-muted);padding:1.5rem 0;">No documents yet. Start uploading now!</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const monthlyLabels = @json($monthlyQuestions->pluck('month'));
    const monthlyCounts = @json($monthlyQuestions->pluck('count'));

    const distLabels = @json(array_keys($quizDistribution));
    const distValues = @json(array_values($quizDistribution));

    let revenueChart, trafficChart;

    function initCharts() {
        const ctx1 = document.getElementById('revenueChart')?.getContext('2d');
        if (ctx1 && revenueChart) revenueChart.destroy();
        if (ctx1) {
            revenueChart = new Chart(ctx1, {
                type: 'line',
                data: {
                    labels: monthlyLabels,
                    datasets: [{
                        label: 'Questions Generated',
                        data: monthlyCounts,
                        borderColor: '#6366f1',
                        backgroundColor: 'rgba(99,102,241,0.08)',
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: '#6366f1',
                        pointBorderColor: 'white',
                        pointRadius: 4,
                        borderWidth: 2,
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { color: '#94a3b8' } },
                        x: { grid: { display: false }, ticks: { color: '#94a3b8' } }
                    }
                }
            });
        }

        const ctx2 = document.getElementById('trafficChart')?.getContext('2d');
        if (ctx2 && trafficChart) trafficChart.destroy();
        if (ctx2) {
            trafficChart = new Chart(ctx2, {
                type: 'doughnut',
                data: {
                    labels: distLabels,
                    datasets: [{
                        data: distValues,
                        backgroundColor: ['#6366f1', '#a855f7', '#f59e0b', '#ec4899', '#0ea5e9', '#10b981'],
                        borderWidth: 0,
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { position: 'bottom', labels: { color: '#64748b', font: { size: 11 }, padding: 12 } }
                    },
                    cutout: '70%',
                }
            });
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        initCharts();
    });
</script>
@endpush
@endsection
