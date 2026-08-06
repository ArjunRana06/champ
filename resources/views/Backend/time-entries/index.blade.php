@extends('Backend.master')

@php
    if (!function_exists('fmt_minutes')) {
        function fmt_minutes($m) {
            return max(0, (int) $m) . ' min';
        }
    }
@endphp

@push('styles')
<style>
    .tt-timer-card {
        background: linear-gradient(135deg, rgba(14,165,233,0.08), rgba(99,102,241,0.08));
        border: 1px solid var(--glass-border);
    }
    #timerDisplay { font-family: 'Inter', monospace; letter-spacing: 0.05em; }
    .tt-chart-box { height: 220px; position: relative; }
    .tt-filter-btn {
        border: 1.5px solid var(--input-border); background: var(--input-bg); color: var(--text-secondary);
        border-radius: 40px; padding: 0.35rem 1rem; font-size: 0.78rem; font-weight: 600;
        transition: all 0.2s; cursor: pointer;
    }
    .tt-filter-btn.active, .tt-filter-btn:hover { border-color: var(--card-accent); color: var(--card-accent); background: var(--badge-bg); }
    .tt-subject-pill {
        display: inline-flex; align-items: center; gap: 0.35rem; font-size: 0.75rem; font-weight: 600;
        padding: 0.2rem 0.7rem; border-radius: 40px;
    }
    .tt-running-pill {
        display: inline-flex; align-items: center; gap: 0.4rem; font-size: 0.72rem; font-weight: 700;
        color: #f59e0b; text-transform: uppercase; letter-spacing: 0.05em;
    }
    .tt-running-pill .pulse-dot {
        width: 8px; height: 8px; border-radius: 50%; background: #f59e0b;
        animation: ttPulse 1.2s ease-in-out infinite;
    }
    @keyframes ttPulse { 0%,100% { opacity: 1; transform: scale(1); } 50% { opacity: 0.4; transform: scale(0.7); } }
    .tt-duration-badge { font-weight: 700; font-size: 0.8rem; }
</style>
@endpush

@section('content')
<div class="container-fluid px-0">
    <div class="page-header">
        <div>
            <h2><i class="bi bi-stopwatch me-2" style="color:#0ea5e9;"></i>Focus & Time Tracking</h2>
            <p>Track your study hours, stay focused, and spot your study rhythm</p>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="glass-card text-center py-3">
                <div style="font-size:1.7rem;font-weight:800;color:var(--card-accent);">{{ fmt_minutes($todayMinutes) }}</div>
                <small style="color:var(--text-secondary);">Today</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="glass-card text-center py-3">
                <div style="font-size:1.7rem;font-weight:800;color:#a855f7;">{{ fmt_minutes($weekMinutes) }}</div>
                <small style="color:var(--text-secondary);">This Week</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="glass-card text-center py-3">
                <div style="font-size:1.7rem;font-weight:800;color:#10b981;">{{ $totalSessions }}</div>
                <small style="color:var(--text-secondary);">Study Sessions</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="glass-card text-center py-3">
                <div style="font-size:1.7rem;font-weight:800;color:#f59e0b;">{{ fmt_minutes($totalMinutes) }}</div>
                <small style="color:var(--text-secondary);">Total Tracked</small>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-4">
            <div class="glass-card tt-timer-card text-center py-4 h-100">
                <div id="timerDisplay" style="font-size:2.6rem;font-weight:800;color:#0ea5e9;">00:00</div>
                <div class="mt-1 mb-2">
                    @if($activeEntry)
                        <span class="tt-running-pill"><span class="pulse-dot"></span> Session running &mdash; {{ $activeEntry->subject?->name ?? 'General' }}</span>
                    @else
                        <span style="font-size:0.78rem;color:var(--text-secondary);">No active session</span>
                    @endif
                </div>
                <div class="d-flex gap-2 justify-content-center mt-2">
                    <button id="startTimerBtn" class="dark-btn" style="padding:0.5rem 1.5rem;font-size:0.85rem;{{ $activeEntry ? 'display:none;' : '' }}"><i class="bi bi-play-fill"></i> Start</button>
                    <button id="stopTimerBtn" class="btn-soft" @if(!$activeEntry) style="display:none;" @endif><i class="bi bi-stop-fill"></i> Stop &amp; Save</button>
                    <button id="cancelTimerBtn" class="btn-soft" @if(!$activeEntry) style="display:none;" @endif title="Discard current session"><i class="bi bi-x-lg"></i></button>
                </div>
                <div class="mt-3 d-flex flex-wrap justify-content-center gap-2">
                    <select id="timerSubject" class="form-select" style="width:auto;font-size:0.78rem;padding:0.3rem 0.7rem;background:var(--input-bg);border:1.5px solid var(--input-border);border-radius:40px;color:var(--text-primary);">
                        <option value="">General</option>
                        @foreach($subjects as $s)
                            <option value="{{ $s->id }}" @if($activeEntry && $activeEntry->subject_id === $s->id) selected @endif>{{ $s->name }}</option>
                        @endforeach
                    </select>
                    <input type="text" id="timerDesc" placeholder="What are you studying?" value="{{ $activeEntry->description ?? '' }}" style="font-size:0.78rem;padding:0.3rem 0.9rem;border:1.5px solid var(--input-border);border-radius:40px;width:170px;background:var(--input-bg);color:var(--text-primary);">
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="glass-card h-100">
                <h5 style="color:var(--text-primary);font-weight:700;margin-bottom:1rem;"><i class="bi bi-bar-chart-line me-2" style="color:var(--card-accent);"></i> Last 7 Days</h5>
                <div class="tt-chart-box"><canvas id="weekChart"></canvas></div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-4">
            <div class="glass-card h-100">
                <h5 style="color:var(--text-primary);font-weight:700;margin-bottom:1rem;"><i class="bi bi-pie-chart me-2" style="color:var(--card-accent);"></i> By Subject</h5>
                @if($subjectStats)
                    <div class="tt-chart-box"><canvas id="subjectChart"></canvas></div>
                    <div class="mt-3" id="subjectLegend"></div>
                @else
                    <div class="text-center py-5" style="color:var(--text-muted);">
                        <i class="bi bi-inboxes" style="font-size:2rem;display:block;margin-bottom:0.5rem;"></i>
                        Complete a few sessions to see subject breakdown.
                    </div>
                @endif
            </div>
        </div>
        <div class="col-lg-8">
            <div class="glass-card">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                    <h5 style="color:var(--text-primary);font-weight:700;margin:0;"><i class="bi bi-clock-history me-2" style="color:var(--card-accent);"></i> History</h5>
                    <form method="GET" action="{{ route('time-entries.index') }}" class="d-flex gap-2 flex-wrap align-items-center">
                        <div class="d-flex gap-1" id="periodFilters">
                            @foreach(['all' => 'All', 'today' => 'Today', 'week' => 'Week', 'month' => 'Month'] as $val => $lbl)
                                <button type="button" value="{{ $val }}" class="tt-filter-btn @if((request('period', 'all')) === $val) active @endif" onclick="window.location = '{{ route('time-entries.index') }}' + (this.value === 'all' ? '' : '?period=' + this.value) + '{{ request('subject') ? '&subject=' . request('subject') : '' }}';">{{ $lbl }}</button>
                            @endforeach
                        </div>
                        <select name="subject" onchange="this.form.submit()" class="form-select" style="width:auto;font-size:0.78rem;padding:0.3rem 0.7rem;background:var(--input-bg);border:1.5px solid var(--input-border);border-radius:40px;color:var(--text-primary);">
                            <option value="">All subjects</option>
                            @foreach($subjects as $s)
                                <option value="{{ $s->id }}" @if(request('subject') == $s->id) selected @endif>{{ $s->name }}</option>
                            @endforeach
                        </select>
                        @if(request()->has('period') || request()->has('subject'))
                            <a href="{{ route('time-entries.index') }}" class="btn-soft py-1 px-2" style="font-size:0.75rem;" title="Clear filters"><i class="bi bi-x-lg"></i></a>
                        @endif
                    </form>
                </div>
                <div class="table-responsive">
                    <table class="glass-table">
                        <thead><tr><th>Date</th><th>Subject</th><th>Description</th><th>Duration</th><th></th></tr></thead>
                        <tbody>
                            @forelse($entries as $entry)
                            <tr>
                                <td>
                                    <div style="font-size:0.85rem;">{{ $entry->started_at->format('M d, Y') }}</div>
                                    @if($entry->started_at->format('H:i') !== '00:00')
                                        <div style="font-size:0.7rem;color:var(--text-muted);">{{ $entry->started_at->format('g:i A') }}{{ $entry->ended_at ? ' – ' . $entry->ended_at->format('g:i A') : '' }}</div>
                                    @endif
                                </td>
                                <td>
                                    @if($entry->subject)
                                        <span class="tt-subject-pill" style="background:var(--badge-bg);color:var(--badge-color);">{{ $entry->subject->name }}</span>
                                    @else
                                        <span style="font-size:0.78rem;color:var(--text-muted);">General</span>
                                    @endif
                                </td>
                                <td>
                                    @if($entry->description)
                                        <span style="font-size:0.85rem;color:var(--text-primary);">{{ $entry->description }}</span>
                                    @else
                                        <span style="color:var(--text-muted);">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if(!$entry->ended_at)
                                        <span class="tt-running-pill"><span class="pulse-dot"></span> Running</span>
                                    @elseif($entry->duration_minutes !== null)
                                        <span class="tt-duration-badge" style="color:{{ $entry->duration_minutes >= 60 ? '#10b981' : 'var(--card-accent)' }};">
                                            {{ fmt_minutes($entry->duration_minutes) }}
                                        </span>
                                    @else
                                        <span style="color:var(--text-muted);">—</span>
                                    @endif
                                </td>
                                <td>
                                    <form action="{{ route('time-entries.destroy', $entry) }}" method="POST" style="display:inline;">
                                        @csrf @method('DELETE')
                                        <button class="btn-soft danger py-1 px-2" style="font-size:0.75rem;" onclick="return confirm('Delete this entry?')"><i class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="5" style="text-align:center;color:var(--text-muted);padding:2.5rem;"><i class="bi bi-stopwatch" style="font-size:1.5rem;display:block;margin-bottom:0.5rem;"></i>No time entries match. Start the timer above!</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if(method_exists($entries, 'links'))
                    <div class="mt-3 pagination-glass d-flex justify-content-center">{{ $entries->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const ACTIVE_STARTED_AT = {{ $activeEntry ? json_encode($activeEntry->started_at->format('c')) : 'null' }};

let timerInterval, isRunning = false, seconds = 0;
const display = document.getElementById('timerDisplay');
const startBtn = document.getElementById('startTimerBtn');
const stopBtn = document.getElementById('stopTimerBtn');
const cancelBtn = document.getElementById('cancelTimerBtn');

function fmtHMS(s) {
    const m = String(Math.floor((s % 3600) / 60)).padStart(2, '0');
    const sec = String(s % 60).padStart(2, '0');
    if (s >= 3600) {
        return String(Math.floor(s / 3600)).padStart(2, '0') + ':' + m + ':' + sec;
    }
    return m + ':' + sec;
}

function updateTimerDisplay() { display.textContent = fmtHMS(seconds); }

function startLocalClock(initial) {
    seconds = initial;
    isRunning = true;
    updateTimerDisplay();
    clearInterval(timerInterval);
    timerInterval = setInterval(() => { seconds++; updateTimerDisplay(); }, 1000);
}

if (ACTIVE_STARTED_AT) {
    const start = new Date(ACTIVE_STARTED_AT).getTime();
    const elapsed = Math.floor((Date.now() - start) / 1000);
    startLocalClock(Math.max(0, elapsed));
}

startBtn.addEventListener('click', function () {
    if (isRunning) return;
    fetch('{{ route("time-entries.start") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({
            subject_id: document.getElementById('timerSubject').value,
            description: document.getElementById('timerDesc').value
        })
    }).then(r => r.json()).then(() => {
        startLocalClock(0);
        startBtn.style.display = 'none';
        stopBtn.style.display = 'inline-flex';
        cancelBtn.style.display = 'inline-flex';
        showToast('Timer started. Focus!', 'success');
    }).catch(() => showToast('Could not start timer.', 'error'));
});

function stopTimer() {
    if (!isRunning) return;
    fetch('{{ route("time-entries.stop") }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
    }).then(() => { showToast('Session saved.', 'success'); setTimeout(() => location.reload(), 600); })
      .catch(() => showToast('Could not save session.', 'error'));
}
stopBtn.addEventListener('click', stopTimer);

cancelBtn.addEventListener('click', function () {
    if (!isRunning || !confirm('Discard the current running session without saving?')) return;
    isRunning = false;
    clearInterval(timerInterval);
    fetch('{{ route("time-entries.stop") }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
    }).then(() => {
        const e = document.getElementById('timerDisplay');
        showToast('Session discarded.', 'warning');
        setTimeout(() => {
            const params = new URLSearchParams(window.location.search);
            if (params.has('subject')) { window.location.href = '{{ route('time-entries.index') }}?subject=' + params.get('subject'); }
            else { window.location.href = '{{ route('time-entries.index') }}'; }
        }, 500);
    });
});

window.addEventListener('beforeunload', function (e) {
    if (isRunning) { e.preventDefault(); e.returnValue = ''; }
});

Chart.defaults.color = getComputedStyle(document.body).getPropertyValue('--text-secondary') || '#6b7280';
Chart.defaults.borderColor = 'rgba(99,102,241,0.08)';

const weekLabels = @json($weekDaily->pluck('label')->all());
const weekValues = @json($weekDaily->pluck('minutes')->all());
new Chart(document.getElementById('weekChart'), {
    type: 'bar',
    data: {
        labels: weekLabels,
        datasets: [{
            label: 'Minutes',
            data: weekValues,
            backgroundColor: weekValues.map(v => v > 0 ? 'rgba(99,102,241,0.75)' : 'rgba(148,163,184,0.15)'),
            hoverBackgroundColor: 'rgba(99,102,241,0.95)',
            borderRadius: 8,
            maxBarThickness: 46
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false }, tooltip: { callbacks: { label: c => c.parsed.y + ' min' } } },
        scales: {
            y: { beginAtZero: true, ticks: { precision: 0, maxTicksLimit: 5 } },
            x: { grid: { display: false } }
        }
    }
});

const subjectNames = @json(collect($subjectStats)->pluck('name')->all());
const subjectMins = @json(collect($subjectStats)->pluck('minutes')->all());
if (subjectNames.length) {
    const palette = ['#6366f1', '#a855f7', '#0ea5e9', '#10b981', '#f59e0b', '#ef4444', '#ec4899', '#14b8a6', '#f97316', '#8b5cf6'];
    const colors = subjectNames.map((_, i) => palette[i % palette.length]);
    new Chart(document.getElementById('subjectChart'), {
        type: 'doughnut',
        data: {
            labels: subjectNames,
            datasets: [{
                data: subjectMins,
                backgroundColor: colors,
                borderWidth: 2,
                borderColor: getComputedStyle(document.body).getPropertyValue('--glass-bg') || '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '62%',
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: { label: c => c.label + ': ' + c.parsed + ' min' } }
            }
        }
    });
    const legend = document.getElementById('subjectLegend');
    const total = subjectMins.reduce((a, b) => a + b, 0);
    subjectNames.forEach((n, i) => {
        const row = document.createElement('div');
        row.style.cssText = 'display:flex;align-items:center;gap:0.5rem;padding:0.25rem 0;font-size:0.8rem;';
        row.innerHTML = '<span style="width:10px;height:10px;border-radius:3px;background:' + colors[i] + ';flex-shrink:0;"></span>' +
            '<span style="color:var(--text-primary);flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">' + n + '</span>' +
            '<span style="color:var(--text-secondary);font-weight:600;">' + Math.round(subjectMins[i] / total * 100) + '%</span>';
        legend.appendChild(row);
    });
}
</script>
@endpush
