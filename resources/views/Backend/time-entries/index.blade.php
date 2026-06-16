@extends('Backend.master')

@section('content')
<div class="container-fluid px-0">
    <div class="page-header">
        <div>
            <h2>Focus & Time Tracking</h2>
            <p>Track your study hours and stay focused</p>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="glass-card text-center py-4">
                <div style="font-size:2rem;font-weight:800;color:#6366f1;" id="todayMinutes">{{ $todayMinutes }}</div>
                <small style="color:#6b7280;">Minutes Today</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="glass-card text-center py-4">
                <div style="font-size:2rem;font-weight:800;color:#a855f7;" id="weekMinutes">{{ $weekMinutes }}</div>
                <small style="color:#6b7280;">Minutes This Week</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="glass-card text-center py-4">
                <div id="timerDisplay" style="font-size:2rem;font-weight:800;color:#0ea5e9;font-family:monospace;">00:00:00</div>
                <div class="mt-2">
                    <button id="startTimerBtn" class="dark-btn" style="padding:0.4rem 1.2rem;font-size:0.8rem;"><i class="bi bi-play-fill"></i> Start</button>
                    <button id="stopTimerBtn" class="btn-soft" style="display:none;padding:0.4rem 1.2rem;font-size:0.8rem;"><i class="bi bi-stop-fill"></i> Stop</button>
                </div>
                <small style="color:#6b7280;display:block;margin-top:0.3rem;">
                    <select id="timerSubject" class="form-select" style="display:inline-block;width:auto;font-size:0.75rem;padding:0.2rem 0.5rem;">
                        <option value="">General</option>
                        @foreach($subjects as $s)
                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                        @endforeach
                    </select>
                    <input type="text" id="timerDesc" placeholder="What are you studying?" style="font-size:0.75rem;padding:0.2rem 0.5rem;border:1px solid #e5e7eb;border-radius:8px;width:auto;">
                </small>
            </div>
        </div>
    </div>

    <div class="glass-card">
        <div class="table-responsive">
            <table class="glass-table">
                <thead><tr><th>Date</th><th>Subject</th><th>Description</th><th>Duration</th><th></th></tr></thead>
                <tbody>
                    @forelse($entries as $entry)
                    <tr>
                        <td>{{ $entry->started_at->format('M d, Y') }}</td>
                        <td>{{ $entry->subject?->name ?? 'General' }}</td>
                        <td>{{ $entry->description ?? '—' }}</td>
                        <td>
                            @if($entry->duration_minutes)
                                {{ floor($entry->duration_minutes / 60) }}h {{ $entry->duration_minutes % 60 }}m
                            @elseif(!$entry->ended_at)
                                <span style="color:#f59e0b;">In progress...</span>
                            @else
                                —
                            @endif
                        </td>
                        <td>
                            <form action="{{ route('time-entries.destroy', $entry) }}" method="POST" style="display:inline;">
                                @csrf @method('DELETE')
                                <button class="btn-soft danger py-1 px-2" style="font-size:0.75rem;" onclick="return confirm('Delete?')"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" style="text-align:center;color:#9ca3af;padding:2rem;">No time entries yet. Start the timer above!</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if(method_exists($entries, 'links'))
        <div class="mt-4 pagination-glass d-flex justify-content-center">{{ $entries->links() }}</div>
    @endif
</div>

<script>
let timerInterval, seconds = 0, isRunning = false;

function updateTimerDisplay() {
    const h = String(Math.floor(seconds / 3600)).padStart(2, '0');
    const m = String(Math.floor((seconds % 3600) / 60)).padStart(2, '0');
    const s = String(seconds % 60).padStart(2, '0');
    document.getElementById('timerDisplay').textContent = `${h}:${m}:${s}`;
}

document.getElementById('startTimerBtn').addEventListener('click', function() {
    if (isRunning) return;
    isRunning = true;
    this.style.display = 'none';
    document.getElementById('stopTimerBtn').style.display = 'inline-flex';
    seconds = 0;
    updateTimerDisplay();
    timerInterval = setInterval(() => { seconds++; updateTimerDisplay(); }, 1000);

    fetch('{{ route("time-entries.start") }}', {
        method: 'POST',
        headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
        body: JSON.stringify({
            subject_id: document.getElementById('timerSubject').value,
            description: document.getElementById('timerDesc').value
        })
    });
});

document.getElementById('stopTimerBtn').addEventListener('click', function() {
    isRunning = false;
    clearInterval(timerInterval);
    this.style.display = 'none';
    document.getElementById('startTimerBtn').style.display = 'inline-flex';
    document.getElementById('startTimerBtn').innerHTML = '<i class="bi bi-arrow-repeat"></i> Resume';

    fetch('{{ route("time-entries.stop") }}', {
        method: 'POST',
        headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'}
    }).then(() => { setTimeout(() => location.reload(), 500); });
});
</script>
@endsection
