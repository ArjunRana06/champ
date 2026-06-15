@extends('Backend.master')

@section('content')
<div class="container-fluid px-0">
    <!-- Quick Actions -->
    <div class="d-flex gap-2 mb-4 flex-wrap" data-aos="fade-down">
        <a href="{{ route('subjects.create') }}" class="btn-soft shadow-sm"><i class="bi bi-plus-circle"></i> New Subject</a>
        <a href="{{ route('documents.index') }}" class="btn-soft shadow-sm"><i class="bi bi-upload"></i> Upload Notes</a>
        <a href="{{ route('mcqs.create') }}" class="btn-soft shadow-sm"><i class="bi bi-patch-question"></i> Generate MCQ</a>
        <a href="{{ route('ai.chat') }}" class="btn-soft shadow-sm"><i class="bi bi-robot"></i> AI Chat</a>
    </div>

    <!-- Welcome -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3" data-aos="fade-right">
        <div>
            <h2 class="fw-bold" style="color:#1e1b4b;">Welcome back, {{ auth()->user()->name }}!</h2>
            <p style="color:#6b7280;">Your AI‑powered study dashboard.</p>
        </div>
        <div class="glass-card d-flex align-items-center gap-2 py-2 px-3" style="border-radius: 40px;">
            <i class="bi bi-calendar3" style="color:#6366f1;"></i>
            <span class="fw-medium" style="color:#1e1b4b; font-size: 0.9rem;">{{ now()->format('l, F j, Y') }}</span>
        </div>
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
                        <h6 style="color:#6366f1;font-size:0.65rem;text-transform:uppercase;letter-spacing:0.05em;font-weight:700;margin:0;">Subjects</h6>
                        <h3 class="fw-bold mb-0" style="color:#1e1b4b;font-size:1.7rem;">{{ number_format($happyCount) }}</h3>
                    </div>
                </div>
                <span class="stat-badge up"><i class="bi bi-arrow-up"></i> {{ $happyIncrease }}</span>
                <small style="color:#9ca3af;font-size:0.7rem;"> vs last month</small>
            </div>
        </div>
        <div class="col-md-3" data-aos="fade-up" data-aos-delay="200">
            <div class="glass-card">
                <div class="d-flex align-items-center gap-3 mb-2">
                    <div style="width:50px;height:50px;border-radius:16px;background:linear-gradient(135deg,#a855f7,#c084fc);display:flex;align-items:center;justify-content:center;font-size:1.5rem;color:white;box-shadow:0 8px 16px rgba(168,85,247,0.2);">
                        <i class="bi bi-file-earmark-text-fill"></i>
                    </div>
                    <div>
                        <h6 style="color:#6366f1;font-size:0.65rem;text-transform:uppercase;letter-spacing:0.05em;font-weight:700;margin:0;">Documents</h6>
                        <h3 class="fw-bold mb-0" style="color:#1e1b4b;font-size:1.7rem;">{{ number_format($totalMemories) }}</h3>
                    </div>
                </div>
                <span class="stat-badge up"><i class="bi bi-arrow-up"></i> {{ $totalIncrease }}</span>
                <small style="color:#9ca3af;font-size:0.7rem;"> vs last week</small>
            </div>
        </div>
        <div class="col-md-3" data-aos="fade-up" data-aos-delay="300">
            <div class="glass-card">
                <div class="d-flex align-items-center gap-3 mb-2">
                    <div style="width:50px;height:50px;border-radius:16px;background:linear-gradient(135deg,#f59e0b,#fbbf24);display:flex;align-items:center;justify-content:center;font-size:1.5rem;color:white;box-shadow:0 8px 16px rgba(245,158,11,0.2);">
                        <i class="bi bi-patch-question-fill"></i>
                    </div>
                    <div>
                        <h6 style="color:#6366f1;font-size:0.65rem;text-transform:uppercase;letter-spacing:0.05em;font-weight:700;margin:0;">MCQs</h6>
                        <h3 class="fw-bold mb-0" style="color:#1e1b4b;font-size:1.7rem;">{{ number_format($aiInsightsCount) }}</h3>
                    </div>
                </div>
                <span class="stat-badge up"><i class="bi bi-arrow-up"></i> {{ $aiInsightsIncrease }}</span>
                <small style="color:#9ca3af;font-size:0.7rem;"> vs last month</small>
            </div>
        </div>
        <div class="col-md-3" data-aos="fade-up" data-aos-delay="400">
            <div class="glass-card">
                <div class="d-flex align-items-center gap-3 mb-2">
                    <div style="width:50px;height:50px;border-radius:16px;background:linear-gradient(135deg,#ec4899,#f472b6);display:flex;align-items:center;justify-content:center;font-size:1.5rem;color:white;box-shadow:0 8px 16px rgba(236,72,153,0.2);">
                        <i class="bi bi-tags-fill"></i>
                    </div>
                    <div>
                        <h6 style="color:#6366f1;font-size:0.65rem;text-transform:uppercase;letter-spacing:0.05em;font-weight:700;margin:0;">Topics</h6>
                        <h3 class="fw-bold mb-0" style="color:#1e1b4b;font-size:1.7rem;">{{ number_format($uniqueTagsCount) }}</h3>
                    </div>
                </div>
                <span class="stat-badge up"><i class="bi bi-arrow-up"></i> {{ $tagIncrease }}</span>
                <small style="color:#9ca3af;font-size:0.7rem;"> this week</small>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="row g-4 mb-4">
        <div class="col-md-8" data-aos="fade-right">
            <div class="glass-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 style="color:#1e1b4b;font-weight:700;font-size:1rem;margin:0;">
                        <i class="bi bi-graph-up me-2" style="color:#6366f1;"></i> Study Activity
                    </h5>
                    <select id="chartRange" class="form-select form-select-sm w-auto" style="border:1.5px solid #e5e7eb;border-radius:40px;font-size:0.8rem;padding:0.3rem 0.8rem;background:white;">
                        <option value="12">Last 12 months</option>
                        <option value="6">Last 6 months</option>
                        <option value="3">Last 3 months</option>
                    </select>
                </div>
                <canvas id="revenueChart" height="240"></canvas>
            </div>
        </div>
        <div class="col-md-4" data-aos="fade-left">
            <div class="glass-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 style="color:#1e1b4b;font-weight:700;font-size:1rem;margin:0;">
                        <i class="bi bi-pie-chart me-2" style="color:#a855f7;"></i> Subject Distribution
                    </h5>
                </div>
                <canvas id="trafficChart" height="240"></canvas>
            </div>
        </div>
    </div>

    <!-- Activity & Topics -->
    <div class="row g-4 mb-4">
        <div class="col-md-5" data-aos="fade-right" data-aos-delay="100">
            <div class="glass-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 style="color:#1e1b4b;font-weight:700;font-size:1rem;margin:0;">
                        <i class="bi bi-clock-history me-2" style="color:#0ea5e9;"></i> Recent Activity
                    </h5>
                </div>
                <ul class="list-unstyled">
                    @foreach($recentActivities as $activity)
                    <li class="d-flex align-items-center gap-3 mb-2 p-2 rounded-3" style="transition:all 0.15s;">
                        <span style="width:8px;height:8px;border-radius:50%;background:{{ $activity->emotions->first()?->color ?? '#6366f1' }};flex-shrink:0;"></span>
                        <div class="flex-grow-1" style="min-width:0;">
                            <div style="color:#1e1b4b;font-size:0.85rem;font-weight:500;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ \Illuminate\Support\Str::limit($activity->parsed_content ?? $activity->type, 50) }}</div>
                            <small style="color:#9ca3af;font-size:0.7rem;">{{ $activity->created_at->diffForHumans() }}</small>
                        </div>
                        <i class="bi bi-{{ $activity->type === 'text' ? 'chat-text' : ($activity->type === 'image' ? 'image' : 'camera-reels') }}" style="color:#9ca3af;"></i>
                    </li>
                    @endforeach
                    @if($recentActivities->isEmpty())
                    <li style="color:#9ca3af;text-align:center;padding:1.5rem 0;">No recent activity yet.</li>
                    @endif
                </ul>
            </div>
        </div>
        <div class="col-md-7" data-aos="fade-left" data-aos-delay="200">
            <div class="glass-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 style="color:#1e1b4b;font-weight:700;font-size:1rem;margin:0;">
                        <i class="bi bi-trophy me-2" style="color:#f59e0b;"></i> Top Subjects
                    </h5>
                    <a href="{{ route('subjects.index') }}" class="btn-soft py-1 px-3" style="font-size:0.75rem;">View all</a>
                </div>
                <div class="table-responsive">
                    <table class="glass-table">
                        <thead>
                            <tr><th>Subject</th><th>Documents</th><th>Trend</th></tr>
                        </thead>
                        <tbody>
                            @foreach($topTags as $tag)
                            <tr>
                                <td style="font-weight:500;">{{ $tag['tag'] }}</td>
                                <td>{{ $tag['count'] }}</td>
                                <td><span class="stat-badge up"><i class="bi bi-arrow-up"></i> +{{ $tag['trend'] }}%</span></td>
                            </tr>
                            @endforeach
                            @if($topTags->isEmpty())
                            <tr><td colspan="3" style="text-align:center;color:#9ca3af;padding:1.5rem 0;">No subjects yet</td></tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Documents -->
    <div class="glass-card" data-aos="fade-up">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 style="color:#1e1b4b;font-weight:700;font-size:1rem;margin:0;">
                <i class="bi bi-file-earmark-text me-2" style="color:#6366f1;"></i> Recent Documents
            </h5>
            <a href="{{ route('documents.index') }}" class="btn-soft py-1 px-3" style="font-size:0.75rem;">View all</a>
        </div>
        <div class="table-responsive">
            <table class="glass-table">
                <thead>
                    <tr><th>Title</th><th>Subject</th><th>Date</th><th>Status</th><th style="text-align:right;">Actions</th></tr>
                </thead>
                <tbody>
                    @foreach($recentMemories as $memory)
                    <tr>
                        <td><i class="bi bi-file-earmark-text me-2" style="color:#6366f1;"></i> {{ $memory->title }}</td>
                        <td><span style="font-size:0.7rem;padding:0.15rem 0.6rem;border-radius:20px;background:#eef2ff;color:#6366f1;">{{ $memory->mood_icon ?? '📘' }} {{ ucfirst($memory->mood) }}</span></td>
                        <td style="color:#6b7280;">{{ $memory->date }}</td>
                        <td><span class="stat-badge up">Completed</span></td>
                        <td style="text-align:right;">
                            <a href="#" class="btn-soft py-1 px-2" style="font-size:0.75rem;"><i class="bi bi-eye"></i></a>
                        </td>
                    </tr>
                    @endforeach
                    @if($recentMemories->isEmpty())
                    <tr><td colspan="5" style="text-align:center;color:#9ca3af;padding:1.5rem 0;">No documents yet. Start uploading now!</td></tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const monthlyMoodLabels = @json($monthlyMood->pluck('month'));
    const monthlyMoodScores = @json($monthlyMood->pluck('score'));
    const moodLabels = @json(array_keys($moodData));
    const moodValues = @json(array_values($moodData));
    let revenueChart, trafficChart;

    function initCharts() {
        const ctx1 = document.getElementById('revenueChart')?.getContext('2d');
        if (ctx1 && revenueChart) revenueChart.destroy();
        if (ctx1) {
            revenueChart = new Chart(ctx1, {
                type: 'line',
                data: {
                    labels: monthlyMoodLabels,
                    datasets: [{
                        label: 'Activity Score',
                        data: monthlyMoodScores,
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
                    labels: moodLabels,
                    datasets: [{
                        data: moodValues,
                        backgroundColor: ['#6366f1', '#a855f7', '#f59e0b', '#ec4899', '#0ea5e9'],
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
        document.getElementById('chartRange')?.addEventListener('change', function(e) {
            const months = e.target.value;
            fetch(`/dashboard/chart-data?months=${months}`)
                .then(res => res.json())
                .then(data => {
                    revenueChart.data.labels = data.labels;
                    revenueChart.data.datasets[0].data = data.scores;
                    revenueChart.update();
                });
        });
    });
</script>
@endpush
@endsection
