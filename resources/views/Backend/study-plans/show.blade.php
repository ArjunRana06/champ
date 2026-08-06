@extends('Backend.master')

@php
    $schedule = $planData['weekly_schedule'] ?? [];
    $totalSessions = collect($schedule)->sum(fn($d) => count($d['sessions'] ?? []));
    $priorityColors = ['high' => '#ef4444', 'medium' => '#f59e0b', 'low' => '#10b981'];
    $priorityBg = ['high' => '#fef2f2', 'medium' => '#fffbeb', 'low' => '#ecfdf5'];
    $priorityText = ['high' => '#dc2626', 'medium' => '#d97706', 'low' => '#059669'];
@endphp

@push('styles')
<style>
    .sp-day-tab {
        border: 1.5px solid var(--input-border); background: var(--input-bg); color: var(--text-secondary);
        border-radius: 40px; padding: 0.4rem 1.1rem; font-size: 0.8rem; font-weight: 600;
        transition: all 0.2s; cursor: pointer;
    }
    .sp-day-tab.active { background: var(--card-accent); border-color: var(--card-accent); color: #fff; }
    .sp-day-tab .done-count { font-size: 0.68rem; opacity: 0.75; }
    .sp-session {
        display: flex; align-items: center; gap: 0.9rem; padding: 0.85rem 1rem;
        border-radius: 1rem; background: rgba(99,102,241,0.03);
        border-left: 4px solid var(--glass-border); transition: all 0.2s;
    }
    .sp-session:hover { background: rgba(99,102,241,0.07); }
    .sp-session.done { opacity: 0.55; }
    .sp-session.done .sp-session-main { text-decoration: line-through; }
    .sp-check {
        width: 20px; height: 20px; border-radius: 50%; flex-shrink: 0;
        border: 2px solid #c7d2fe; cursor: pointer; display: flex; align-items: center; justify-content: center;
        background: transparent; transition: all 0.2s; color: #fff; font-size: 0.7rem;
    }
    .sp-check:hover { border-color: var(--card-accent); }
    .sp-check.checked { background: #10b981; border-color: #10b981; box-shadow: 0 0 8px rgba(16,185,129,0.4); }
    .sp-progress { height: 10px; border-radius: 20px; background: var(--table-header-bg); overflow: hidden; }
    .sp-progress-fill {
        height: 100%; width: 0%; border-radius: 20px; background: linear-gradient(90deg, #6366f1, #10b981);
        transition: width 0.4s ease;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-0">
    <div class="page-header">
        <div>
            <h2>{{ $planData['title'] ?? $studyPlan->title }}</h2>
            <p>Generated {{ $studyPlan->created_at->diffForHumans() }} &bull; {{ $studyPlan->hours_per_day }} hours/day &bull; {{ $totalSessions }} sessions this week</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn-soft" onclick="markAllDone(true)"><i class="bi bi-check-all"></i> Mark All Done</button>
            <button class="btn-soft" onclick="markAllDone(false)"><i class="bi bi-arrow-counterclockwise"></i> Reset</button>
            <a href="{{ route('study-plans.index') }}" class="btn-soft"><i class="bi bi-arrow-left"></i> All Plans</a>
        </div>
    </div>

    <div class="glass-card mb-4">
        <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
            <div>
                <span style="font-weight:700;color:var(--text-primary);font-size:0.95rem;">Overall Progress</span>
                <span class="ms-2" id="spOverallPct" style="font-weight:800;color:var(--card-accent);font-size:1.1rem;">0%</span>
            </div>
            <span id="spOverallCount" style="font-size:0.8rem;color:var(--text-secondary);">0 / {{ $totalSessions }} sessions</span>
        </div>
        <div class="sp-progress"><div class="sp-progress-fill" id="spOverallBar"></div></div>
    </div>

    @if(isset($planData['overview']))
        <div class="glass-card mb-4">
            <p style="color:var(--text-primary);font-size:0.9rem;line-height:1.65;margin:0;"><i class="bi bi-info-circle me-2" style="color:var(--card-accent);"></i>{{ $planData['overview'] }}</p>
        </div>
    @endif

    @if(count($schedule))
        <div class="d-flex flex-wrap gap-2 mb-4" id="dayTabs">
            @foreach($schedule as $di => $day)
                <span class="sp-day-tab {{ $di === 0 ? 'active' : '' }}" data-day="{{ $di }}">
                    {{ $day['day'] }}
                    <span class="done-count" data-count-for="{{ $di }}"></span>
                </span>
            @endforeach
        </div>

        @foreach($schedule as $di => $day)
            <div class="day-panel mb-4" data-panel="{{ $di }}" style="{{ $di === 0 ? '' : 'display:none;' }}">
                <div class="glass-card">
                    <h5 style="color:var(--text-primary);font-weight:700;margin-bottom:1rem;">
                        <i class="bi bi-calendar-day me-2" style="color:var(--card-accent);"></i> {{ $day['day'] }}
                        <span style="font-size:0.75rem;color:var(--text-muted);font-weight:500;">&mdash; {{ count($day['sessions'] ?? []) }} sessions</span>
                    </h5>
                    @if(isset($day['sessions']) && count($day['sessions']))
                        <div class="d-flex flex-column gap-2">
                            @foreach($day['sessions'] as $si => $session)
                                @php $color = $priorityColors[$session['priority'] ?? 'low'] ?? '#6366f1'; @endphp
                                <div class="sp-session" id="session-{{ $di }}-{{ $si }}">
                                    <span class="sp-check" data-day="{{ $di }}" data-session="{{ $si }}" role="button" title="Toggle complete"></span>
                                    <div style="min-width:150px;flex-shrink:0;">
                                        <span style="font-size:0.78rem;font-weight:600;color:var(--card-accent);">{{ $session['time'] }}</span>
                                    </div>
                                    <div class="flex-grow-1 sp-session-main" style="min-width:0;">
                                        <div style="color:var(--text-primary);font-weight:600;font-size:0.88rem;">{{ $session['subject'] }}</div>
                                        <div style="color:var(--text-secondary);font-size:0.82rem;">{{ $session['topic'] }}</div>
                                        <div style="color:var(--text-muted);font-size:0.78rem;">{{ $session['activity'] }}</div>
                                    </div>
                                    <span style="font-size:0.65rem;padding:0.15rem 0.55rem;border-radius:20px;
                                        background:{{ $priorityBg[$session['priority'] ?? 'low'] ?? '#ecfdf5' }};
                                        color:{{ $priorityText[$session['priority'] ?? 'low'] ?? '#059669' }};
                                        font-weight:700;text-transform:uppercase;flex-shrink:0;">
                                        {{ $session['priority'] ?? 'low' }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p style="color:var(--text-muted);font-size:0.85rem;">Rest day — no scheduled sessions.</p>
                    @endif
                </div>
            </div>
        @endforeach
    @endif

    @if(isset($planData['tips']))
        <div class="glass-card mb-4">
            <h5 style="color:var(--text-primary);font-weight:700;margin-bottom:1rem;"><i class="bi bi-lightbulb me-2" style="color:#f59e0b;"></i> Study Tips</h5>
            <ul style="color:var(--text-primary);font-size:0.88rem;line-height:1.8;margin:0;padding-left:1.2rem;">
                @foreach($planData['tips'] as $tip)
                    <li>{{ $tip }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(isset($planData['daily_goal']))
        <div class="glass-card" style="border-left:4px solid #6366f1;">
            <p style="color:var(--text-primary);font-weight:600;margin:0;"><i class="bi bi-bullseye me-2" style="color:var(--card-accent);"></i> {{ $planData['daily_goal'] }}</p>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
const PLAN_ID = {{ $studyPlan->id }};
const STORE_KEY = 'studyplan_' + PLAN_ID + '_done';
const TOTAL_SESSIONS = {{ $totalSessions }};
const TOTAL_DAYS = {{ count($schedule) }};

function loadDone() {
    try { return JSON.parse(localStorage.getItem(STORE_KEY) || '{}'); } catch (e) { return {}; }
}
function saveDone(done) {
    try { localStorage.setItem(STORE_KEY, JSON.stringify(done)); } catch (e) {}
}
function keyFor(d, s) { return 'd' + d + 's' + s; }

function renderProgress() {
    const done = loadDone();
    let filled = 0, total = 0;
    const dayCounts = {};
    document.querySelectorAll('.sp-check').forEach(function (chk) {
        const d = chk.dataset.day, s = chk.dataset.session;
        const isDone = !!done[keyFor(d, s)];
        chk.classList.toggle('checked', isDone);
        chk.innerHTML = isDone ? '<i class="bi bi-check-lg"></i>' : '';
        document.getElementById('session-' + d + '-' + s).classList.toggle('done', isDone);
        if (!dayCounts[d]) dayCounts[d] = { done: 0, total: 0 };
        dayCounts[d].total++;
        if (isDone) { filled++; dayCounts[d].done++; }
        total++;
    });
    const pct = total > 0 ? Math.round((filled / total) * 100) : 0;
    document.getElementById('spOverallBar').style.width = pct + '%';
    document.getElementById('spOverallPct').textContent = pct + '%';
    document.getElementById('spOverallCount').textContent = filled + ' / ' + total + ' sessions';
    document.querySelectorAll('[data-count-for]').forEach(function (el) {
        const d = el.dataset.countFor;
        const c = dayCounts[d];
        el.textContent = c && c.total ? ' (' + c.done + '/' + c.total + ')' : '';
    });
}

document.querySelectorAll('.sp-check').forEach(function (chk) {
    chk.addEventListener('click', function () {
        const done = loadDone();
        const k = keyFor(this.dataset.day, this.dataset.session);
        done[k] = !done[k];
        saveDone(done);
        renderProgress();
    });
});

document.getElementById('dayTabs').addEventListener('click', function (e) {
    const tab = e.target.closest('.sp-day-tab');
    if (!tab) return;
    const day = tab.dataset.day;
    document.querySelectorAll('.sp-day-tab').forEach(t => t.classList.remove('active'));
    tab.classList.add('active');
    document.querySelectorAll('.day-panel').forEach(p => {
        p.style.display = p.dataset.panel === day ? '' : 'none';
    });
});

function markAllDone(checked) {
    const done = loadDone();
    Object.keys(done).forEach(k => { done[k] = checked; });
    document.querySelectorAll('.sp-check').forEach(function (chk) {
        done[keyFor(chk.dataset.day, chk.dataset.session)] = checked;
    });
    saveDone(done);
    renderProgress();
    showToast(checked ? 'All sessions marked complete' : 'Plan progress reset', checked ? 'success' : 'warning');
}

renderProgress();
</script>
@endpush
