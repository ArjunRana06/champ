@extends('Backend.master')

@section('content')
<div class="container-fluid px-0">
    <div class="page-header">
        <div>
            <h2>🍅 Pomodoro Timer</h2>
            <p>Focus in intervals — {{ $todayCount }} sessions completed today</p>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-4 offset-md-4">
            <div class="glass-card text-center py-5">
                <div id="pomodoroTimer" style="font-size:4rem;font-weight:800;color:#ef4444;font-family:monospace;letter-spacing:0.1em;">25:00</div>
                <div class="mt-3">
                    <span id="pomodoroStatus" style="color:var(--text-secondary);font-size:0.9rem;font-weight:500;">Focus Time</span>
                </div>
                <div class="mt-4 d-flex gap-2 justify-content-center">
                    <button id="pomodoroStartBtn" class="dark-btn"><i class="bi bi-play-fill"></i> Start</button>
                    <button id="pomodoroPauseBtn" class="btn-soft" style="display:none;"><i class="bi bi-pause-fill"></i> Pause</button>
                    <button id="pomodoroResetBtn" class="btn-soft"><i class="bi bi-arrow-counterclockwise"></i> Reset</button>
                </div>
                <div class="mt-3">
                    <small style="color:var(--text-muted);">Focus: <span id="focusMinutes">25</span> min &bull; Break: <span id="breakMinutes">5</span> min</small>
                    <br>
                    <select id="pomodoroSubject" class="form-select" style="display:inline-block;width:auto;font-size:0.75rem;margin-top:0.3rem;">
                        <option value="">General</option>
                        @foreach($subjects as $s)
                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    @if($sessions->count())
    <div class="glass-card">
        <h5 style="color:var(--text-primary);font-weight:700;margin-bottom:1rem;">Session History</h5>
        <div class="table-responsive">
            <table class="glass-table">
                <thead><tr><th>Date</th><th>Duration</th><th>Subject</th></tr></thead>
                <tbody>
                    @foreach($sessions as $session)
                    <tr>
                        <td>{{ $session->created_at->format('M d, Y H:i') }}</td>
                        <td>{{ $session->duration_minutes }} min</td>
                        <td>{{ $session->subject?->name ?? 'General' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>

<script>
let pomodoroSeconds = 25 * 60, pomodoroRunning = false, pomodoroInterval, isBreak = false;
const focusDefault = 25, breakDefault = 5;

function updatePomodoroDisplay() {
    const m = String(Math.floor(pomodoroSeconds / 60)).padStart(2, '0');
    const s = String(pomodoroSeconds % 60).padStart(2, '0');
    document.getElementById('pomodoroTimer').textContent = `${m}:${s}`;
}

function togglePomodoro() {
    if (pomodoroRunning) {
        clearInterval(pomodoroInterval);
        pomodoroRunning = false;
        document.getElementById('pomodoroStartBtn').style.display = 'inline-flex';
        document.getElementById('pomodoroPauseBtn').style.display = 'none';
        document.getElementById('pomodoroStartBtn').innerHTML = '<i class="bi bi-play-fill"></i> Resume';
    } else {
        pomodoroRunning = true;
        document.getElementById('pomodoroStartBtn').style.display = 'none';
        document.getElementById('pomodoroPauseBtn').style.display = 'inline-flex';
        pomodoroInterval = setInterval(() => {
            pomodoroSeconds--;
            updatePomodoroDisplay();
            if (pomodoroSeconds <= 0) {
                clearInterval(pomodoroInterval);
                pomodoroRunning = false;
                const wasBreak = isBreak;
                if (!isBreak) {
                    isBreak = true;
                    pomodoroSeconds = breakDefault * 60;
                    document.getElementById('pomodoroStatus').textContent = '☕ Break Time';
                    document.getElementById('pomodoroTimer').style.color = '#10b981';

                    fetch('{{ route("pomodoro.complete") }}', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
                        body: JSON.stringify({
                            duration_minutes: focusDefault,
                            break_minutes: breakDefault,
                            subject_id: document.getElementById('pomodoroSubject').value
                        })
                    });

                    setTimeout(() => {
                        if (confirm('Break is over! Start next focus session?')) {
                            isBreak = false;
                            pomodoroSeconds = focusDefault * 60;
                            document.getElementById('pomodoroStatus').textContent = 'Focus Time';
                            document.getElementById('pomodoroTimer').style.color = '#ef4444';
                            updatePomodoroDisplay();
                            document.getElementById('pomodoroStartBtn').click();
                        }
                    }, breakDefault * 60 * 1000);

                } else {
                    isBreak = false;
                    pomodoroSeconds = focusDefault * 60;
                    document.getElementById('pomodoroStatus').textContent = 'Focus Time';
                    document.getElementById('pomodoroTimer').style.color = '#ef4444';
                }
                updatePomodoroDisplay();
                document.getElementById('pomodoroStartBtn').style.display = 'inline-flex';
                document.getElementById('pomodoroPauseBtn').style.display = 'none';
                document.getElementById('pomodoroStartBtn').innerHTML = '<i class="bi bi-play-fill"></i> Start';
            }
        }, 1000);
        document.getElementById('pomodoroPauseBtn').style.display = 'inline-flex';
    }
}

document.getElementById('pomodoroStartBtn').addEventListener('click', togglePomodoro);
document.getElementById('pomodoroPauseBtn').addEventListener('click', togglePomodoro);
document.getElementById('pomodoroResetBtn').addEventListener('click', function() {
    clearInterval(pomodoroInterval);
    pomodoroRunning = false;
    isBreak = false;
    pomodoroSeconds = focusDefault * 60;
    updatePomodoroDisplay();
    document.getElementById('pomodoroStatus').textContent = 'Focus Time';
    document.getElementById('pomodoroTimer').style.color = '#ef4444';
    document.getElementById('pomodoroStartBtn').style.display = 'inline-flex';
    document.getElementById('pomodoroPauseBtn').style.display = 'none';
    document.getElementById('pomodoroStartBtn').innerHTML = '<i class="bi bi-play-fill"></i> Start';
});
</script>
@endsection
