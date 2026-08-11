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
    .pomo-ring-wrap { position: relative; width: 240px; height: 240px; margin: 0 auto; }
    .pomo-ring-wrap svg { transform: rotate(-90deg); }
    .pomo-ring-bg { fill: none; stroke: var(--table-header-bg); stroke-width: 14; }
    .pomo-ring-fg {
        fill: none; stroke: #ef4444; stroke-width: 14; stroke-linecap: round;
        stroke-dasharray: 628.32; stroke-dashoffset: 0; transition: stroke-dashoffset 0.5s linear, stroke 0.4s ease;
    }
    .pomo-timer-center {
        position: absolute; inset: 0; display: flex; flex-direction: column;
        align-items: center; justify-content: center;
    }
    .pomo-timer-center .time {
        font-size: 3.4rem; font-weight: 800; font-family: 'Inter', monospace;
        letter-spacing: 0.06em; color: #ef4444; line-height: 1; transition: color 0.4s ease;
    }
    .pomo-timer-center .phase {
        margin-top: 0.4rem; font-size: 0.85rem; font-weight: 600; letter-spacing: 0.04em;
        text-transform: uppercase; color: var(--text-secondary);
    }
    .pomo-dots { display: flex; gap: 0.5rem; justify-content: center; margin-top: 1rem; }
    .pomo-dots .dot {
        width: 12px; height: 12px; border-radius: 50%;
        background: var(--table-header-bg); border: 2px solid var(--glass-border);
        transition: all 0.3s ease;
    }
    .pomo-dots .dot.done { background: #10b981; border-color: #10b981; box-shadow: 0 0 10px rgba(16,185,129,0.4); }
    .pomo-dots .dot.current { background: #ef4444; border-color: #ef4444; box-shadow: 0 0 10px rgba(239,68,68,0.4); }
    .pomo-settings-input {
        width: 72px; text-align: center; padding: 0.35rem 0.4rem;
        border: 1.5px solid var(--input-border); border-radius: 0.75rem;
        background: var(--input-bg); color: var(--text-primary); font-size: 0.85rem; font-weight: 600;
    }
    .pomo-settings-input:focus { outline: none; border-color: var(--card-accent); box-shadow: 0 0 0 4px rgba(99,102,241,0.1); }
    .pomo-stat {
        text-align: center; padding: 1rem 0.5rem;
    }
    .pomo-stat .val { font-size: 1.6rem; font-weight: 800; color: var(--card-accent); line-height: 1.1; }
    .pomo-stat .lbl { font-size: 0.72rem; color: var(--text-secondary); margin-top: 0.3rem; font-weight: 500; }
    .pomo-mode-pill {
        display: inline-flex; align-items: center; gap: 0.4rem;
        padding: 0.35rem 0.9rem; border-radius: 40px; font-size: 0.75rem; font-weight: 600;
        border: 1.5px solid var(--input-border); cursor: pointer; transition: all 0.2s; user-select: none;
        color: var(--text-secondary); background: var(--input-bg);
    }
    .pomo-mode-pill.active { background: #ef4444; border-color: #ef4444; color: #fff; }
    .pomo-mode-pill.break.active { background: #10b981; border-color: #10b981; color: #fff; }
    .pomo-mode-pill.longbreak.active { background: #6366f1; border-color: #6366f1; color: #fff; }
</style>
@endpush

@section('content')
<div class="container-fluid px-0">
    <div class="page-header">
        <div>
            <h2><i class="bi bi-stopwatch me-2" style="color:#ef4444;"></i>Pomodoro Timer</h2>
            <p>Focus in intervals &mdash; <span id="todayCountPill">{{ $todayCount }}</span> sessions completed today</p>
        </div>
        <button class="btn-soft" onclick="openSettings()"><i class="bi bi-gear"></i> Settings</button>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="glass-card text-center py-3">
                <div class="pomo-stat">
                    <div class="val" id="statToday">{{ $todayCount }}</div>
                    <div class="lbl">Sessions Today</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="glass-card text-center py-3">
                <div class="pomo-stat">
                    <div class="val" style="color:#10b981;">{{ fmt_minutes($todayMinutes) }}</div>
                    <div class="lbl">Minutes Focused Today</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="glass-card text-center py-3">
                <div class="pomo-stat">
                    <div class="val" style="color:#a855f7;">{{ fmt_minutes($totalMinutes) }}</div>
                    <div class="lbl">Total Focus Time</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-5 offset-lg-1">
            <div class="glass-card text-center py-5">
                <div class="pomo-ring-wrap">
                    <svg width="240" height="240" viewBox="0 0 240 240">
                        <circle class="pomo-ring-bg" cx="120" cy="120" r="100"></circle>
                        <circle class="pomo-ring-fg" id="pomoRing" cx="120" cy="120" r="100"></circle>
                    </svg>
                    <div class="pomo-timer-center">
                        <div class="time" id="pomodoroTimer">25:00</div>
                        <div class="phase" id="pomodoroStatus">Focus Time</div>
                    </div>
                </div>

                <div class="pomo-dots" id="pomoDots"></div>

                <div class="d-flex gap-2 justify-content-center mt-4 flex-wrap">
                    <button id="pomodoroStartBtn" class="dark-btn" style="min-width:120px;"><i class="bi bi-play-fill"></i> Start</button>
                    <button id="pomodoroPauseBtn" class="btn-soft" style="display:none;"><i class="bi bi-pause-fill"></i> Pause</button>
                    <button id="pomodoroSkipBtn" class="btn-soft" style="display:none;"><i class="bi bi-skip-forward-fill"></i> Skip</button>
                    <button id="pomodoroResetBtn" class="btn-soft"><i class="bi bi-arrow-counterclockwise"></i> Reset</button>
                </div>

                <div class="mt-4 d-flex gap-2 justify-content-center flex-wrap">
                    <span class="pomo-mode-pill active" data-mode="focus"><i class="bi bi-bullseye"></i> Focus</span>
                    <span class="pomo-mode-pill break" data-mode="short"><i class="bi bi-cup-hot"></i> Short Break</span>
                    <span class="pomo-mode-pill longbreak" data-mode="long"><i class="bi bi-moon"></i> Long Break</span>
                </div>

                <div class="mt-4 d-flex align-items-center justify-content-center gap-3 flex-wrap">
                    <select id="pomodoroSubject" class="form-select" style="width:auto;font-size:0.8rem;background:var(--input-bg);border:1.5px solid var(--input-border);border-radius:40px;color:var(--text-primary);">
                        <option value="">General</option>
                        @foreach($subjects as $s)
                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                        @endforeach
                    </select>
                    <label class="d-inline-flex align-items-center gap-2" style="font-size:0.78rem;color:var(--text-secondary);cursor:pointer;">
                        <input type="checkbox" id="pomodoroAutoStart" class="form-check-input" checked> Auto-start breaks
                    </label>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="glass-card mb-4">
                <h5 style="color:var(--text-primary);font-weight:700;margin-bottom:1rem;"><i class="bi bi-calendar-week me-2" style="color:var(--card-accent);"></i> This Week</h5>
                <div class="d-flex flex-wrap gap-3 align-items-end" id="pomoWeekBars" style="height:120px;padding-top:0.5rem;"></div>
                <div class="mt-2" style="font-size:0.75rem;color:var(--text-muted);">Total this week: <strong style="color:var(--text-primary);">{{ fmt_minutes($weekMinutes) }}</strong></div>
            </div>

            @if($sessions->count())
            <div class="glass-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 style="color:var(--text-primary);font-weight:700;margin:0;"><i class="bi bi-clock-history me-2" style="color:var(--card-accent);"></i> Session History</h5>
                    <span class="stat-badge" style="background:var(--badge-bg);color:var(--badge-color);">{{ $sessions->total() }} total</span>
                </div>
                <div class="table-responsive">
                    <table class="glass-table">
                        <thead><tr><th>Date</th><th>Duration</th><th>Subject</th><th>Cycle</th></tr></thead>
                        <tbody>
                            @foreach($sessions as $session)
                            <tr>
                                <td>{{ $session->created_at->format('M d, Y H:i') }}</td>
                                <td><span class="stat-badge" style="background:#fef2f2;color:#dc2626;">{{ $session->duration_minutes }} min</span></td>
                                <td>
                                    @if($session->subject)
                                        <span style="font-size:0.75rem;padding:0.15rem 0.6rem;border-radius:20px;background:var(--badge-bg);color:var(--badge-color);">{{ $session->subject->name }}</span>
                                    @else
                                        <span style="font-size:0.75rem;color:var(--text-muted);">General</span>
                                    @endif
                                </td>
                                <td>
                                    <span style="font-size:0.75rem;color:var(--text-muted);">{{ $session->break_minutes }} min break</span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if(method_exists($sessions, 'links'))
                    <div class="mt-3 pagination-glass d-flex justify-content-center">{{ $sessions->links() }}</div>
                @endif
            </div>
            @endif
        </div>
    </div>
</div>

<x-app-modal id="pomodoroSettingsModal" title="Timer Settings" icon="bi-gear" maxWidth="520px">
    <div class="row g-3 text-center">
        <div class="col-6 col-md-3">
            <label style="font-size:0.7rem;font-weight:600;color:var(--text-secondary);display:block;margin-bottom:0.35rem;">Focus (min)</label>
            <input type="number" id="setFocus" class="pomo-settings-input" min="1" max="90" value="25">
        </div>
        <div class="col-6 col-md-3">
            <label style="font-size:0.7rem;font-weight:600;color:var(--text-secondary);display:block;margin-bottom:0.35rem;">Short break (min)</label>
            <input type="number" id="setShort" class="pomo-settings-input" min="1" max="30" value="5">
        </div>
        <div class="col-6 col-md-3">
            <label style="font-size:0.7rem;font-weight:600;color:var(--text-secondary);display:block;margin-bottom:0.35rem;">Long break (min)</label>
            <input type="number" id="setLong" class="pomo-settings-input" min="1" max="60" value="15">
        </div>
        <div class="col-6 col-md-3">
            <label style="font-size:0.7rem;font-weight:600;color:var(--text-secondary);display:block;margin-bottom:0.35rem;">Sessions / cycle</label>
            <input type="number" id="setCycle" class="pomo-settings-input" min="1" max="8" value="4">
        </div>
    </div>
    <div class="form-check form-switch mt-4">
        <input class="form-check-input" type="checkbox" id="setSound" checked>
        <label class="form-check-label" for="setSound" style="font-size:0.85rem;color:var(--text-primary);">Play sound at session end</label>
    </div>
    <div class="form-check form-switch">
        <input class="form-check-input" type="checkbox" id="setNotif" checked>
        <label class="form-check-label" for="setNotif" style="font-size:0.85rem;color:var(--text-primary);">Browser notification at session end</label>
    </div>
    <p id="notifPermHint" style="font-size:0.75rem;color:var(--text-muted);display:none;margin-top:0.5rem;">Notification permission is blocked in your browser settings.</p>
    <x-slot name="footer">
        <button type="button" class="btn-soft" data-app-close="true">Cancel</button>
        <button type="button" class="dark-btn" onclick="saveSettings(); window.closePomodoroSettingsModal();"><i class="bi bi-check-lg"></i> Save Settings</button>
    </x-slot>
</x-app-modal>
@endsection

@push('scripts')
<script>
const RING_C = 2 * Math.PI * 100;
document.getElementById('pomoRing').style.strokeDasharray = RING_C;

const store = {
    get: (k, d) => { try { const v = localStorage.getItem('pomo_' + k); return v === null ? d : JSON.parse(v); } catch(e) { return d; } },
    set: (k, v) => { try { localStorage.setItem('pomo_' + k, JSON.stringify(v)); } catch(e) {} },
    del: (k) => { try { localStorage.removeItem('pomo_' + k); } catch(e) {} }
};

const settings = {
    focus: store.get('focus', 25),
    short: store.get('short', 5),
    long: store.get('long', 15),
    cycle: store.get('cycle', 4),
    sound: store.get('sound', true),
    notif: store.get('notif', true)
};

let phase = 'focus';              // focus | short | long
let running = false;
let remaining = settings.focus * 60;
let totalForPhase = settings.focus * 60;
let ticker = null;
let endsAtRef = null;
let completedFocus = store.get('completed', 0);

const els = {
    timer: document.getElementById('pomodoroTimer'),
    status: document.getElementById('pomodoroStatus'),
    ring: document.getElementById('pomoRing'),
    start: document.getElementById('pomodoroStartBtn'),
    pause: document.getElementById('pomodoroPauseBtn'),
    skip: document.getElementById('pomodoroSkipBtn'),
    reset: document.getElementById('pomodoroResetBtn'),
    auto: document.getElementById('pomodoroAutoStart'),
    subject: document.getElementById('pomodoroSubject')
};

const PHASE_META = {
    focus:  { color: '#ef4444', label: 'Focus Time',  pill: 'focus' },
    short:  { color: '#10b981', label: 'Short Break', pill: 'break' },
    long:   { color: '#6366f1', label: 'Long Break',  pill: 'longbreak' }
};

function fmt(sec) {
    const m = String(Math.floor(sec / 60)).padStart(2, '0');
    const s = String(sec % 60).padStart(2, '0');
    return m + ':' + s;
}

function renderDots() {
    const wrap = document.getElementById('pomoDots');
    const cycle = settings.cycle;
    wrap.innerHTML = '';
    const pos = completedFocus % cycle;
    for (let i = 0; i < cycle; i++) {
        const d = document.createElement('span');
        d.className = 'dot';
        if (i < pos) d.classList.add('done');
        if (phase === 'focus' && i === pos && !running) d.classList.add('current');
        wrap.appendChild(d);
    }
}

function renderRing() {
    const fg = els.ring;
    const frac = totalForPhase > 0 ? remaining / totalForPhase : 0;
    fg.style.strokeDashoffset = RING_C * (1 - frac);
    fg.style.stroke = PHASE_META[phase].color;
    els.timer.style.color = PHASE_META[phase].color;
    els.timer.textContent = fmt(remaining);
    els.status.textContent = PHASE_META[phase].label;
    document.querySelectorAll('.pomo-mode-pill').forEach(p => p.classList.remove('active'));
    const pill = document.querySelector('.pomo-mode-pill[data-mode="' + PHASE_META[phase].pill + '"]');
    if (pill) pill.classList.add('active');
}

function renderTitle() {
    document.title = (running ? fmt(remaining) + ' — ' : '') + PHASE_META[phase].label + ' | Study Assistant';
}

function beep() {
    try {
        const Ctx = window.AudioContext || window.webkitAudioContext;
        const ctx = new Ctx();
        [523, 659, 784].forEach((f, i) => {
            const o = ctx.createOscillator(), g = ctx.createGain();
            o.type = 'sine'; o.frequency.value = f;
            o.connect(g); g.connect(ctx.destination);
            const t = ctx.currentTime + i * 0.18;
            g.gain.setValueAtTime(0.001, t);
            g.gain.exponentialRampToValueAtTime(0.3, t + 0.02);
            g.gain.exponentialRampToValueAtTime(0.001, t + 0.15);
            o.start(t); o.stop(t + 0.16);
        });
    } catch (e) {}
}

function sendNotif(title, body) {
    if (!settings.notif || !('Notification' in window)) return;
    if (Notification.permission === 'granted') {
        const n = new Notification(title, { body: body, icon: '/icon.png' });
        setTimeout(() => n.close(), 8000);
    }
}

function announce(title, body) {
    if (settings.sound) beep();
    sendNotif(title, body);
    if (!running) {
        showToast(title + ' — ' + body, 'info');
    }
}

function recordFocusSession() {
    const duration = settings.focus;
    completedFocus++;
    store.set('completed', completedFocus);
    fetch('{{ route("pomodoro.complete") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({
            duration_minutes: duration,
            break_minutes: (completedFocus % settings.cycle === 0) ? settings.long : settings.short,
            subject_id: els.subject.value
        })
    }).catch(() => {});
    const pill = document.getElementById('todayCountPill');
    pill.textContent = parseInt(pill.textContent || '0', 10) + 1;
    renderDots();
}

function switchPhase(next) {
    phase = next;
    totalForPhase = settings[next] * 60;
    remaining = totalForPhase;
    running = false;
    clearInterval(ticker); ticker = null;
    endsAtRef = null;
    els.start.style.display = 'inline-flex';
    els.pause.style.display = 'none';
    els.skip.style.display = 'none';
    els.start.innerHTML = '<i class="bi bi-play-fill"></i> ' + (next === 'focus' ? 'Start' : 'Start Break');
    renderRing();
    renderDots();
    renderTitle();
    persist();
}

function finishPhase() {
    clearInterval(ticker); ticker = null;
    if (phase === 'focus') {
        recordFocusSession();
        announce('Focus complete!', 'Time for a ' + (completedFocus % settings.cycle === 0 ? 'long' : 'short') + ' break.');
        switchPhase(completedFocus % settings.cycle === 0 ? 'long' : 'short');
        if (els.auto.checked) tick();
    } else {
        announce('Break over!', 'Ready for the next focus session?');
        switchPhase('focus');
        if (els.auto.checked) tick();
    }
}

function persist() {
    const st = { running: running, phase: phase, total: totalForPhase, subject: els.subject.value };
    if (running) {
        endsAtRef = Date.now() + Math.max(0, remaining) * 1000;
        st.endsAt = endsAtRef;
    } else {
        st.remaining = Math.max(0, remaining);
    }
    store.set('state', st);
}

function tickLoop() {
    remaining--;
    if (remaining <= 0) {
        remaining = 0;
        renderRing();
        renderTitle();
        finishPhase();
        return;
    }
    els.timer.textContent = fmt(remaining);
    els.ring.style.strokeDashoffset = RING_C * (1 - remaining / totalForPhase);
    renderTitle();
}

function tick() {
    if (running) return;
    running = true;
    els.start.style.display = 'none';
    els.pause.style.display = 'inline-flex';
    els.skip.style.display = 'inline-flex';
    renderTitle();
    ticker = setInterval(tickLoop, 1000);
    els.timer.textContent = fmt(remaining);
    els.ring.style.strokeDashoffset = RING_C * (1 - remaining / totalForPhase);
    persist();
}

function pause() {
    running = false;
    clearInterval(ticker); ticker = null;
    endsAtRef = null;
    els.start.style.display = 'inline-flex';
    els.pause.style.display = 'none';
    els.skip.style.display = 'none';
    els.start.innerHTML = '<i class="bi bi-play-fill"></i> Resume';
    renderTitle();
    persist();
}

function restoreState() {
    els.subject.value = store.get('subject', '') || '';
    els.auto.checked = store.get('auto', true);
    const st = store.get('state', null);
    if (!st || !st.phase || !PHASE_META[st.phase] || !(st.total > 0)) {
        switchPhase('focus');
        return;
    }
    phase = st.phase;
    totalForPhase = st.total > 0 ? st.total : settings[st.phase] * 60;
    if (st.subject) els.subject.value = st.subject;

    if (st.running && typeof st.endsAt === 'number') {
        remaining = Math.round((st.endsAt - Date.now()) / 1000);
        if (remaining <= 0) {
            remaining = 0;
            finishPhase();
            return;
        }
        running = true;
        els.start.style.display = 'none';
        els.pause.style.display = 'inline-flex';
        els.skip.style.display = 'inline-flex';
        renderRing(); renderDots(); renderTitle();
        endsAtRef = st.endsAt;
        ticker = setInterval(tickLoop, 1000);
    } else {
        remaining = (typeof st.remaining === 'number' && st.remaining > 0) ? st.remaining : totalForPhase;
        running = false;
        els.start.style.display = 'inline-flex';
        els.pause.style.display = 'none';
        els.skip.style.display = 'none';
        els.start.innerHTML = '<i class="bi bi-play-fill"></i> ' + (phase === 'focus' ? 'Start' : 'Start Break');
        renderRing(); renderDots(); renderTitle();
    }
}

els.start.addEventListener('click', tick);
els.pause.addEventListener('click', pause);
els.skip.addEventListener('click', function () {
    const next = phase === 'focus'
        ? (completedFocus % settings.cycle === 0 ? 'long' : 'short')
        : 'focus';
    switchPhase(next);
});
els.reset.addEventListener('click', function () {
    clearInterval(ticker); ticker = null;
    running = false;
    endsAtRef = null;
    phase = 'focus';
    totalForPhase = settings.focus * 60;
    remaining = totalForPhase;
    els.start.style.display = 'inline-flex';
    els.pause.style.display = 'none';
    els.skip.style.display = 'none';
    els.start.innerHTML = '<i class="bi bi-play-fill"></i> Start';
    renderRing(); renderDots(); renderTitle();
    persist();
});
els.subject.addEventListener('change', function () {
    store.set('subject', els.subject.value);
    persist();
});
els.auto.addEventListener('change', function () {
    store.set('auto', els.auto.checked);
});

document.querySelectorAll('.pomo-mode-pill').forEach(pill => {
    pill.addEventListener('click', function () {
        switchPhase(this.dataset.mode === 'short' ? 'short' : this.dataset.mode === 'long' ? 'long' : 'focus');
    });
});

function openSettings() {
    document.getElementById('setFocus').value = settings.focus;
    document.getElementById('setShort').value = settings.short;
    document.getElementById('setLong').value = settings.long;
    document.getElementById('setCycle').value = settings.cycle;
    document.getElementById('setSound').checked = settings.sound;
    document.getElementById('setNotif').checked = settings.notif;
    document.getElementById('notifPermHint').style.display =
        ('Notification' in window && Notification.permission === 'denied') ? 'block' : 'none';
    window.openPomodoroSettingsModal();
}

function saveSettings() {
    const parse = (id, min, max, fallback) => {
        const v = parseInt(document.getElementById(id).value, 10);
        if (isNaN(v)) return fallback;
        return Math.min(max, Math.max(min, v));
    };
    settings.focus = parse('setFocus', 1, 90, settings.focus);
    settings.short = parse('setShort', 1, 30, settings.short);
    settings.long = parse('setLong', 1, 60, settings.long);
    settings.cycle = parse('setCycle', 1, 8, settings.cycle);
    settings.sound = document.getElementById('setSound').checked;
    settings.notif = document.getElementById('setNotif').checked;
    store.set('focus', settings.focus);
    store.set('short', settings.short);
    store.set('long', settings.long);
    store.set('cycle', settings.cycle);
    store.set('sound', settings.sound);
    store.set('notif', settings.notif);
    if (!running) {
        phase = 'focus';
        totalForPhase = settings.focus * 60;
        remaining = totalForPhase;
        renderRing(); renderDots(); renderTitle();
        persist();
    }
    window.closePomodoroSettingsModal();
    showToast('Timer settings saved', 'success');
}

(function initWeekBars() {
    const bars = document.getElementById('pomoWeekBars');
    const weekMinutes = @json($weekMinutes);
    const labels = @json($weekDaily->pluck('label')->all());
    const dayMinutes = @json($weekDaily->pluck('minutes')->all());
    const max = Math.max.apply(null, dayMinutes.concat([1]));
    dayMinutes.forEach((m, i) => {
        const col = document.createElement('div');
        col.style.cssText = 'display:flex;flex-direction:column;align-items:center;gap:0.3rem;flex:1;min-width:0;';
        const bar = document.createElement('div');
        const h = Math.max(4, Math.round((m / max) * 100));
        bar.style.cssText = 'width:100%;max-width:34px;height:' + h + 'px;border-radius:8px 8px 4px 4px;background:linear-gradient(180deg,#6366f1,#a855f7);opacity:' + (m > 0 ? 0.95 : 0.25) + ';transition:height 0.4s;';
        bar.title = labels[i] + ': ' + m + ' min';
        col.appendChild(bar);
        const l = document.createElement('small');
        l.textContent = labels[i][0];
        l.style.cssText = 'font-size:0.6rem;color:var(--text-muted);';
        col.appendChild(l);
        bars.appendChild(col);
    });
})();

document.addEventListener('visibilitychange', () => {
    if (!document.hidden) {
        if (running && endsAtRef) {
            remaining = Math.max(0, Math.round((endsAtRef - Date.now()) / 1000));
            if (remaining <= 0) {
                remaining = 0;
                finishPhase();
                return;
            }
            renderRing();
        }
        renderTitle();
    }
});

if ('Notification' in window && settings.notif && Notification.permission === 'default') {
    Notification.requestPermission().catch(() => {});
}

restoreState();
</script>
@endpush
