@extends('Backend.master')

@section('content')
<div class="container-fluid px-0">
    <div class="page-header">
        <div>
            <h2>Exam Calendar</h2>
            <p>Track your upcoming exams and deadlines</p>
        </div>
        <a href="{{ route('exams.create') }}" class="dark-btn"><i class="bi bi-plus-circle"></i> Add Exam</a>
    </div>

    @if(session('success'))
        <div class="glass-card mb-4 d-flex align-items-center gap-3 py-3" style="border-left:4px solid #059669;">
            <i class="bi bi-check-circle-fill" style="color:#059669;font-size:1.2rem;"></i>
            <span style="color:#1e1b4b;font-size:0.9rem;">{{ session('success') }}</span>
        </div>
    @endif

    @php $grouped = $exams->groupBy(fn($e) => $e->exam_date->format('F Y')); @endphp

    @forelse($grouped as $month => $monthExams)
        <div class="glass-card mb-4">
            <h5 style="color:#1e1b4b;font-weight:700;margin-bottom:1rem;"><i class="bi bi-calendar-month me-2" style="color:#6366f1;"></i> {{ $month }}</h5>
            @foreach($monthExams as $exam)
                <div class="d-flex align-items-center gap-3 py-2 {{ !$loop->last ? 'border-bottom border-light' : '' }}" style="opacity:{{ $exam->is_completed ? 0.5 : 1 }};">
                    <div style="min-width:50px;text-align:center;">
                        <div style="font-size:1.3rem;font-weight:800;color:#6366f1;">{{ $exam->exam_date->format('d') }}</div>
                        <div style="font-size:0.65rem;color:#9ca3af;text-transform:uppercase;">{{ $exam->exam_date->format('D') }}</div>
                    </div>
                    <div class="flex-grow-1">
                        <div style="color:#1e1b4b;font-weight:600;font-size:0.9rem;">{{ $exam->title }}</div>
                        <div style="color:#6b7280;font-size:0.8rem;">
                            {{ $exam->subject?->name ?? 'General' }}
                            @if($exam->time) &bull; {{ $exam->time }} @endif
                            @if($exam->location) &bull; {{ $exam->location }} @endif
                        </div>
                    </div>
                    <div class="d-flex gap-1">
                        <form action="{{ route('exams.update', $exam) }}" method="POST" style="display:inline;">
                            @csrf @method('PUT')
                            <input type="hidden" name="is_completed" value="{{ $exam->is_completed ? 0 : 1 }}">
                            <button class="btn-soft py-1 px-2" style="font-size:0.75rem;color:{{ $exam->is_completed ? '#9ca3af' : '#10b981' }};">
                                <i class="bi bi-{{ $exam->is_completed ? 'arrow-counterclockwise' : 'check-lg' }}"></i>
                            </button>
                        </form>
                        <form action="{{ route('exams.destroy', $exam) }}" method="POST" style="display:inline;">
                            @csrf @method('DELETE')
                            <button class="btn-soft danger py-1 px-2" style="font-size:0.75rem;" onclick="return confirm('Delete this exam?')"><i class="bi bi-trash"></i></button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @empty
        <div class="glass-card text-center py-5">
            <i class="bi bi-calendar" style="font-size:3rem;color:#c7d2fe;"></i>
            <p class="mt-3" style="color:#6b7280;">No exams added yet. Start tracking your exam schedule.</p>
            <a href="{{ route('exams.create') }}" class="dark-btn"><i class="bi bi-plus-circle"></i> Add Exam</a>
        </div>
    @endforelse
</div>
@endsection
