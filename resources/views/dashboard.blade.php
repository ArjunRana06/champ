@extends('Backend.master')

@section('content')
<div class="container-fluid px-0">
    <!-- Quick Actions (restyled) -->
    <div class="quick-actions" data-aos="fade-down">
        <button class="quick-action-btn"><i class="bi bi-plus-circle"></i> New Memory</button>
        <button class="quick-action-btn"><i class="bi bi-upload"></i> Import Timeline</button>
        <button class="quick-action-btn"><i class="bi bi-download"></i> Export Report</button>
        <button class="quick-action-btn"><i class="bi bi-share"></i> Share Journey</button>
    </div>

    <!-- Welcome & Date -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3" data-aos="fade-right">
        <div>
            <h2 class="fw-bold" style="color: #0c4a6e;">Welcome back, {{ auth()->user()->name }}!</h2>
            <p class="text-muted">Your life timeline at a glance.</p>
        </div>
        <div class="bg-white/50 backdrop-blur-sm p-3 rounded-4 shadow-sm border border-blue-200">
            <i class="bi bi-calendar3 me-2" style="color: #0ea5e9;"></i>
            <span class="fw-medium">{{ now()->format('l, F j, Y') }}</span>
        </div>
    </div>

    <!-- Stats Cards (dynamic) -->
    <div class="row g-4 mb-4">
        <div class="col-md-3" data-aos="fade-up" data-aos-delay="100">
            <div class="card-modern">
                <div class="stat-icon blue">
                    <i class="bi bi-emoji-smile"></i>
                </div>
                <h6 class="text-muted text-uppercase small">Happy Moments</h6>
                <h3 class="fw-bold">{{ number_format($happyCount) }}</h3>
                <span class="text-success"><i class="bi bi-arrow-up"></i> {{ $happyIncrease }}</span>
                <small class="text-muted ms-2">this month</small>
            </div>
        </div>
        <div class="col-md-3" data-aos="fade-up" data-aos-delay="200">
            <div class="card-modern">
                <div class="stat-icon green">
                    <i class="bi bi-timeline"></i>
                </div>
                <h6 class="text-muted text-uppercase small">Total Memories</h6>
                <h3 class="fw-bold">{{ number_format($totalMemories) }}</h3>
                <span class="text-success"><i class="bi bi-arrow-up"></i> {{ $totalIncrease }}</span>
                <small class="text-muted ms-2">new this week</small>
            </div>
        </div>
        <div class="col-md-3" data-aos="fade-up" data-aos-delay="300">
            <div class="card-modern">
                <div class="stat-icon orange">
                    <i class="bi bi-tags"></i>
                </div>
                <h6 class="text-muted text-uppercase small">Tags Used</h6>
                <h3 class="fw-bold">{{ number_format($uniqueTagsCount) }}</h3>
                <span class="text-success"><i class="bi bi-arrow-up"></i> {{ $tagIncrease }}</span>
                <small class="text-muted ms-2">new this week</small>
            </div>
        </div>
        <div class="col-md-3" data-aos="fade-up" data-aos-delay="400">
            <div class="card-modern">
                <div class="stat-icon pink">
                    <i class="bi bi-gem"></i>
                </div>
                <h6 class="text-muted text-uppercase small">AI Insights</h6>
                <h3 class="fw-bold">{{ number_format($aiInsightsCount) }}</h3>
                <span class="text-success"><i class="bi bi-arrow-up"></i> {{ $aiInsightsIncrease }}</span>
                <small class="text-muted ms-2">this month</small>
            </div>
        </div>
    </div>

    <!-- Charts Row (dynamic) -->
    <div class="row g-4 mb-4">
        <div class="col-md-8" data-aos="fade-right">
            <div class="chart-card">
                <div class="chart-header">
                    <h5><i class="bi bi-graph-up me-2" style="color: #0ea5e9;"></i> Emotional Timeline</h5>
                    <select id="chartRange" class="form-select form-select-sm w-auto" style="border-radius: 20px; background: white;">
                        <option value="12">Last 12 months</option>
                        <option value="6">Last 6 months</option>
                        <option value="3">Last 3 months</option>
                    </select>
                </div>
                <canvas id="revenueChart" height="250"></canvas>
            </div>
        </div>
        <div class="col-md-4" data-aos="fade-left">
            <div class="chart-card">
                <div class="chart-header">
                    <h5><i class="bi bi-pie-chart me-2" style="color: #f72585;"></i> Mood Distribution</h5>
                </div>
                <canvas id="trafficChart" height="250"></canvas>
            </div>
        </div>
    </div>

    <!-- Recent Activity & Top Tags (dynamic) -->
    <div class="row g-4 mb-4">
        <div class="col-md-5" data-aos="fade-right" data-aos-delay="100">
            <div class="chart-card">
                <div class="chart-header">
                    <h5><i class="bi bi-clock-history me-2" style="color: #4cc9f0;"></i> Recent Activity</h5>
                    <a href="{{ route('events.index') }}" class="text-decoration-none" style="color: #0ea5e9;">View all</a>
                </div>
                <ul class="list-unstyled">
                    @foreach($recentActivities as $activity)
                    <li class="d-flex align-items-center gap-3 mb-3 p-2 rounded-3 hover-bg-light">
                        <span class="bg-{{ $activity->emotions->first()?->color ?? 'secondary' }} rounded-circle p-1" style="width: 10px; height: 10px;"></span>
                        <div class="flex-grow-1">
                            <div class="fw-medium">{{ \Illuminate\Support\Str::limit($activity->parsed_content ?? $activity->type, 50) }}</div>
                            <small class="text-muted">{{ $activity->created_at->diffForHumans() }}</small>
                        </div>
                        <i class="bi bi-{{ $activity->type === 'text' ? 'chat-text' : ($activity->type === 'image' ? 'image' : 'camera-reels') }}"></i>
                    </li>
                    @endforeach
                    @if($recentActivities->isEmpty())
                    <li class="text-muted text-center py-3">No recent activity yet.</li>
                    @endif
                </ul>
            </div>
        </div>
        <div class="col-md-7" data-aos="fade-left" data-aos-delay="200">
            <div class="chart-card">
                <div class="chart-header">
                    <h5><i class="bi bi-trophy me-2" style="color: #f8961e;"></i> Top Memory Tags</h5>
                    <a href="#" class="text-decoration-none" style="color: #0ea5e9;">View all</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-modern">
                        <thead>
                            <tr><th>Tag</th><th>Usage count</th><th>Trend</th></tr>
                        </thead>
                        <tbody>
                            @foreach($topTags as $tag)
                            <tr>
                                <td>#{{ $tag['tag'] }}</td>
                                <td>{{ $tag['count'] }}</td>
                                <td><span class="badge-modern badge-success">+{{ $tag['trend'] }}%</span></td>
                            </tr>
                            @endforeach
                            @if($topTags->isEmpty())
                            <tr><td colspan="3" class="text-center text-muted">No tags yet</td></tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Memories Table (dynamic) -->
    <div class="chart-card" data-aos="fade-up">
        <div class="chart-header">
            <h5><i class="bi bi-kanban me-2" style="color: #0ea5e9;"></i> Recent Memories</h5>
            <a href="{{ route('events.index') }}" class="text-decoration-none" style="color: #0ea5e9;">View all memories</a>
        </div>
        <div class="table-responsive">
            <table class="table table-modern">
                <thead>
                    <tr><th>Title</th><th>Mood</th><th>Date</th><th>Tags</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    @foreach($recentMemories as $memory)
                    <tr>
                        <td><i class="bi bi-folder-fill me-2" style="color: #0ea5e9;"></i> {{ $memory->title }}</td>
                        <td><span class="badge-modern badge-{{ $memory->mood_color ?? 'success' }}">{{ $memory->mood_icon ?? '😊' }} {{ ucfirst($memory->mood) }}</span></td>
                        <td>{{ $memory->date }}</td>
                        <td>{!! $memory->tags !!}</td>
                        <td><a href="{{ route('events.show', $memory->id) }}"><i class="bi bi-eye"></i></a></td>
                    </tr>
                    @endforeach
                    @if($recentMemories->isEmpty())
                    <tr><td colspan="5" class="text-center text-muted">No memories yet. Start adding now!</td></tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    /* Override styles to match new color scheme */
    .quick-actions {
        display: flex;
        gap: 12px;
        margin-bottom: 24px;
        flex-wrap: wrap;
    }
    .quick-action-btn {
        background: rgba(255,255,255,0.7);
        backdrop-filter: blur(4px);
        border: 1px solid rgba(14,165,233,0.3);
        border-radius: 40px;
        padding: 10px 20px;
        font-weight: 500;
        color: #0c4a6e;
        transition: all 0.2s;
    }
    .quick-action-btn:hover {
        background: white;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px -6px rgba(0,0,0,0.1);
        border-color: #0ea5e9;
    }

    .card-modern {
        background: rgba(255,255,255,0.7);
        backdrop-filter: blur(8px);
        border: 1px solid rgba(14,165,233,0.2);
        border-radius: 1.5rem;
        padding: 20px;
        transition: all 0.3s;
    }
    .card-modern:hover {
        transform: translateY(-5px);
        background: white;
        box-shadow: 0 20px 30px -12px rgba(0,0,0,0.1);
    }

    .stat-icon {
        width: 55px;
        height: 55px;
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
        margin-bottom: 15px;
    }
    .stat-icon.blue { background: linear-gradient(135deg, #0ea5e9, #0284c7); color: white; }
    .stat-icon.green { background: linear-gradient(135deg, #4cc9f0, #4895ef); color: white; }
    .stat-icon.orange { background: linear-gradient(135deg, #f8961e, #f3722c); color: white; }
    .stat-icon.pink { background: linear-gradient(135deg, #f72585, #b5179e); color: white; }

    .chart-card {
        background: rgba(255,255,255,0.7);
        backdrop-filter: blur(8px);
        border: 1px solid rgba(14,165,233,0.2);
        border-radius: 1.5rem;
        padding: 20px;
        transition: all 0.2s;
    }
    .chart-card:hover {
        background: white;
        box-shadow: 0 8px 20px -8px rgba(0,0,0,0.08);
    }

    .table-modern {
        background: transparent;
    }
    .table-modern thead th {
        background: rgba(255,255,255,0.5);
        color: #0c4a6e;
        border-bottom: 1px solid rgba(14,165,233,0.2);
    }
    .badge-modern {
        padding: 4px 10px;
        border-radius: 40px;
        font-size: 0.75rem;
    }
    .badge-success { background: #dcfce7; color: #166534; }
    .badge-warning { background: #fef9c3; color: #854d0e; }
    .badge-info { background: #dbeafe; color: #1e40af; }

    .hover-bg-light:hover {
        background: rgba(56, 189, 248, 0.1);
        border-radius: 12px;
    }
</style>

<script>
    // Dynamic charts powered by controller data
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
                        label: 'Mood Score (1-10)',
                        data: monthlyMoodScores,
                        borderColor: '#0ea5e9',
                        backgroundColor: 'rgba(14, 165, 233, 0.1)',
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: '#0284c7',
                        pointBorderColor: 'white',
                        pointRadius: 4,
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true, grid: { color: '#e2e8f0' } }, x: { grid: { display: false } } }
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
                        backgroundColor: ['#0ea5e9', '#4cc9f0', '#f8961e', '#f72585', '#94a3b8'],
                        borderWidth: 0,
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { position: 'bottom' } },
                    cutout: '65%',
                }
            });
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        initCharts();

        // Range selector (optional – if you want to reload with different data)
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
@endsection
