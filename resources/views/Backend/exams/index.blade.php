@extends('Backend.master')

@php
    $monthRef = request('m', now()->format('Y-m'));
    $cursor = preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', (string) $monthRef)
        ? \Carbon\Carbon::createFromFormat('Y-m', $monthRef)
        : now();
    $startOfMonth = $cursor->copy()->startOfMonth();
    $endOfMonth = $cursor->copy()->endOfMonth();
    $gridStart = $startOfMonth->copy()->startOfWeek(\Carbon\Carbon::SUNDAY);
    $gridEnd = $endOfMonth->copy()->endOfWeek(\Carbon\Carbon::SUNDAY);
    $byDate = $exams->groupBy(fn($e) => $e->exam_date->format('Y-m-d'));
    $daysLabel = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
    $todayStr = now()->format('Y-m-d');
    $prevMonth = $startOfMonth->copy()->subMonth()->format('Y-m');
    $nextMonth = $startOfMonth->copy()->addMonth()->format('Y-m');
    $priorityColor = [1 => '#9ca3af', 2 => '#22c55e', 3 => '#f59e0b', 4 => '#f97316', 5 => '#ef4444'];
@endphp

@push('styles')
<style>
    .cal-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 0.4rem; }
    .cal-day-head {
        font-size: 0.65rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em;
        color: var(--text-muted); text-align: center; padding: 0.35rem 0;
    }
    .cal-cell {
        min-height: 96px; border: 1.5px solid var(--glass-border); border-radius: 1rem;
        background: var(--glass-bg); padding: 0.45rem; display: flex; flex-direction: column;
        gap: 0.25rem; transition: all 0.2s;
    }
    .cal-cell:hover { border-color: var(--card-accent); background: var(--glass-bg-hover); }
    .cal-cell.out { opacity: 0.4; }
    .cal-cell.today { border-color: var(--card-accent); box-shadow: 0 0 0 3px rgba(99,102,241,0.12); }
    .cal-day-num {
        font-size: 0.72rem; font-weight: 700; color: var(--text-secondary);
        display: flex; align-items: center; gap: 0.4rem;
    }
    .cal-cell.today .cal-day-num {
        color: var(--card-accent);
    }
    .cal-day-num .today-tag {
        font-size: 0.55rem; font-weight: 700; background: var(--card-accent); color: #fff;
        padding: 0.05rem 0.4rem; border-radius: 20px; letter-spacing: 0.05em;
    }
    .cal-chip {
        font-size: 0.62rem; font-weight: 600; color: #fff; border-radius: 0.45rem;
        padding: 0.18rem 0.4rem; line-height: 1.25; overflow: hidden; text-overflow: ellipsis;
        white-space: nowrap; cursor: default; display: block; text-align: left;
    }
    .cal-cell .more { font-size: 0.6rem; color: var(--text-muted); font-weight: 600; }
    .countdown-pill {
        display: inline-flex; align-items: center; gap: 0.3rem; font-size: 0.7rem; font-weight: 700;
        padding: 0.2rem 0.65rem; border-radius: 40px; white-space: nowrap;
    }
    .exam-row {
        display: flex; align-items: center; gap: 0.85rem; padding: 0.7rem 0.25rem;
        border-bottom: 1px solid var(--divider-color);
    }
    .exam-row:last-child { border-bottom: none; }
    .cal-nav-btn {
        display: inline-flex; align-items: center; justify-content: center;
        width: 36px; height: 36px; border-radius: 12px;
        border: 1.5px solid var(--input-border); background: var(--input-bg); color: var(--text-primary);
        font-size: 0.9rem; transition: all 0.2s; text-decoration: none;
    }
    .cal-nav-btn:hover { border-color: var(--card-accent); color: var(--card-accent); background: var(--badge-bg); }
</style>
@endpush

@section('content')
<div class="container-fluid px-0">
    <div class="page-header">
        <div>
            <h2><i class="bi bi-calendar-event me-2" style="color:var(--card-accent);"></i>Exam Calendar</h2>
            <p>Track your upcoming exams and deadlines</p>
        </div>
        <button type="button" class="dark-btn" data-open="addExamModal"><i class="bi bi-plus-circle"></i> Add Exam</button>
    </div>

    @if(session('success'))
        <div class="glass-card mb-4 d-flex align-items-center gap-3 py-3" style="border-left:4px solid #059669;">
            <i class="bi bi-check-circle-fill" style="color:#059669;font-size:1.2rem;"></i>
            <span style="color:var(--text-primary);font-size:0.9rem;">{{ session('success') }}</span>
        </div>
    @endif

    <div class="row g-4 mb-4">
        <div class="col-6 col-md-3">
            <div class="glass-card text-center py-3">
                <div style="font-size:1.7rem;font-weight:800;color:var(--card-accent);">{{ $upcomingCount }}</div>
                <small style="color:var(--text-secondary);">Upcoming</small>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="glass-card text-center py-3">
                <div style="font-size:1.7rem;font-weight:800;color:#f59e0b;">{{ $thisWeekCount }}</div>
                <small style="color:var(--text-secondary);">This Week</small>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="glass-card text-center py-3">
                <div style="font-size:1.7rem;font-weight:800;color:#10b981;">{{ $completedCount }}</div>
                <small style="color:var(--text-secondary);">Completed</small>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="glass-card text-center py-3">
                <div style="font-size:1.7rem;font-weight:800;color:#ef4444;">{{ $overdueCount }}</div>
                <small style="color:var(--text-secondary);">Overdue</small>
            </div>
        </div>
    </div>

    @if($nextExam)
        <div class="glass-card mb-4 py-3 px-4 d-flex align-items-center flex-wrap gap-3" style="border-left:4px solid var(--card-accent);">
            <i class="bi bi-alarm" style="font-size:1.3rem;color:var(--card-accent);"></i>
            <div class="flex-grow-1">
                <div style="color:var(--text-primary);font-weight:700;font-size:0.92rem;">{{ $nextExam->title }}</div>
                <div style="color:var(--text-secondary);font-size:0.8rem;">
                    {{ $nextExam->exam_date->format('l, F j') }}{{ $nextExam->time ? ' at ' . $nextExam->time : '' }}
                </div>
            </div>
            <span class="countdown-pill" style="background:var(--badge-bg);color:var(--badge-color);">
                <i class="bi bi-hourglass-split"></i>
                {{ $nextExam->exam_date->isToday() ? 'Today!' : 'in ' . $nextExam->exam_date->diffInDays(today()) . ' day' . ($nextExam->exam_date->diffInDays(today()) === 1 ? '' : 's') }}
            </span>
        </div>
    @endif

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="glass-card">
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                    <h5 style="color:var(--text-primary);font-weight:700;margin:0;">
                        <i class="bi bi-calendar-month me-2" style="color:var(--card-accent);"></i>
                        {{ $cursor->format('F Y') }}
                    </h5>
                    <div class="d-flex gap-2">
                        <a href="{{ route('exams.index', ['m' => $prevMonth]) }}" class="cal-nav-btn"><i class="bi bi-chevron-left"></i></a>
                        <a href="{{ route('exams.index') }}" class="cal-nav-btn" title="Today" style="width:auto;padding:0 0.9rem;font-size:0.78rem;font-weight:600;">Today</a>
                        <a href="{{ route('exams.index', ['m' => $nextMonth]) }}" class="cal-nav-btn"><i class="bi bi-chevron-right"></i></a>
                    </div>
                </div>

                <div class="cal-grid">
                    @foreach($daysLabel as $d)
                        <div class="cal-day-head">{{ $d }}</div>
                    @endforeach

                    @for($date = $gridStart->copy(); $date->lte($gridEnd); $date->addDay())
                        @php
                            $key = $date->format('Y-m-d');
                            $dayExams = $byDate->get($key, collect());
                            $isToday = $key === $todayStr;
                        @endphp
                        <div class="cal-cell @if(!$date->isSameMonth($startOfMonth)) out @endif @if($isToday) today @endif">
                            <div class="cal-day-num">
                                {{ $date->format('j') }}
                                @if($isToday)<span class="today-tag">TODAY</span>@endif
                            </div>
                            @foreach($dayExams->take(3) as $exam)
                                <span class="cal-chip" style="background:{{ $priorityColor[$exam->priority] ?? '#6366f1' }};text-decoration:{{ $exam->is_completed ? 'line-through' : 'none' }};" title="{{ $exam->title }}{{ $exam->time ? ' · ' . $exam->time : '' }}">
                                    {{ $exam->is_completed ? '✓ ' : '' }}{{ $exam->title }}
                                </span>
                            @endforeach
                            @if($dayExams->count() > 3)
                                <span class="more">+{{ $dayExams->count() - 3 }} more</span>
                            @endif
                        </div>
                    @endfor
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            @php
                $upcomingList = $exams->where('is_completed', false)->sortBy('exam_date')->take(8);
            @endphp
            <div class="glass-card mb-4">
                <h5 style="color:var(--text-primary);font-weight:700;margin-bottom:0.5rem;"><i class="bi bi-list-check me-2" style="color:var(--card-accent);"></i> Upcoming Exams</h5>
                @forelse($upcomingList as $exam)
                    @php
                        $daysLeft = today()->diffInDays($exam->exam_date, false);
                        $pill = $daysLeft <= 0 ? ['bg' => '#fef2f2', 'color' => '#dc2626', 'txt' => $exam->exam_date->isToday() ? 'Today!' : 'Overdue'] :
                               ($daysLeft <= 3 ? ['bg' => '#fffbeb', 'color' => '#d97706', 'txt' => 'in ' . $daysLeft . 'd'] :
                               ['bg' => 'var(--badge-bg)', 'color' => 'var(--badge-color)', 'txt' => 'in ' . $daysLeft . 'd']);
                    @endphp
                    <div class="exam-row">
                        <div style="min-width:44px;text-align:center;">
                            <div style="font-size:1.1rem;font-weight:800;color:var(--card-accent);">{{ $exam->exam_date->format('d') }}</div>
                            <div style="font-size:0.6rem;color:var(--text-muted);text-transform:uppercase;">{{ $exam->exam_date->format('M') }}</div>
                        </div>
                        <div class="flex-grow-1" style="min-width:0;">
                            <div style="color:var(--text-primary);font-weight:600;font-size:0.85rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $exam->title }}</div>
                            <div style="color:var(--text-secondary);font-size:0.75rem;">
                                {{ $exam->subject?->name ?? 'General' }}{{ $exam->time ? ' · ' . $exam->time : '' }}
                            </div>
                        </div>
                        <span class="countdown-pill" style="background:{{ $pill['bg'] }};color:{{ $pill['color'] }};">{{ $pill['txt'] }}</span>
                    </div>
                @empty
                    <div class="text-center py-5" style="color:var(--text-muted);">
                        <i class="bi bi-calendar-x" style="font-size:1.6rem;display:block;margin-bottom:0.5rem;"></i>
                        No upcoming exams. Add one!
                    </div>
                @endforelse
            </div>

            <div class="glass-card">
                <h5 style="color:var(--text-primary);font-weight:700;margin-bottom:0.5rem;"><i class="bi bi-journal-check me-2" style="color:var(--card-accent);"></i> All Exams</h5>
                <div class="table-responsive">
                    <table class="glass-table">
                        <thead><tr><th>Exam</th><th>Date</th><th>Status</th><th></th></tr></thead>
                        <tbody>
                            @forelse($exams as $exam)
                            <tr style="opacity:{{ $exam->is_completed ? 0.55 : 1 }};">
                                <td>
                                    <div style="font-weight:600;font-size:0.83rem;">{{ $exam->title }}</div>
                                    <div style="font-size:0.72rem;color:var(--text-muted);">{{ $exam->subject?->name ?? 'General' }}</div>
                                </td>
                                <td style="font-size:0.8rem;">{{ $exam->exam_date->format('M j, Y') }}</td>
                                <td>
                                    @if($exam->is_completed)
                                        <span class="countdown-pill" style="background:#ecfdf5;color:#059669;"><i class="bi bi-check-lg"></i> Done</span>
                                    @elseif($exam->exam_date->isToday())
                                        <span class="countdown-pill" style="background:var(--badge-bg);color:var(--badge-color);">Today</span>
                                    @elseif($exam->exam_date->lt(today()))
                                        <span class="countdown-pill" style="background:#fef2f2;color:#dc2626;">Overdue</span>
                                    @else
                                        <span class="countdown-pill" style="background:#ecfdf5;color:#059669;">in {{ $exam->exam_date->diffInDays(today()) }}d</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex gap-1 justify-content-end">
                                        <form action="{{ route('exams.update', $exam) }}" method="POST">
                                            @csrf @method('PUT')
                                            <input type="hidden" name="title" value="{{ $exam->title }}">
                                            <input type="hidden" name="subject_id" value="{{ $exam->subject_id }}">
                                            <input type="hidden" name="exam_date" value="{{ $exam->exam_date->format('Y-m-d') }}">
                                            <input type="hidden" name="time" value="{{ $exam->time }}">
                                            <input type="hidden" name="location" value="{{ $exam->location }}">
                                            <input type="hidden" name="notes" value="{{ $exam->notes }}">
                                            <input type="hidden" name="priority" value="{{ $exam->priority }}">
                                            <input type="hidden" name="is_completed" value="{{ $exam->is_completed ? 0 : 1 }}">
                                            <button class="btn-soft py-1 px-2" style="font-size:0.72rem;color:{{ $exam->is_completed ? '#9ca3af' : '#10b981' }};" title="{{ $exam->is_completed ? 'Mark incomplete' : 'Mark complete' }}">
                                                <i class="bi bi-{{ $exam->is_completed ? 'arrow-counterclockwise' : 'check-lg' }}"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('exams.destroy', $exam) }}" method="POST">
                                            @csrf @method('DELETE')
                                            <button class="btn-soft danger py-1 px-2" style="font-size:0.72rem;" onclick="return confirm('Delete this exam?')" title="Delete"><i class="bi bi-trash"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="4" style="text-align:center;color:var(--text-muted);padding:2rem;">No exams yet. Add your first exam!</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<x-app-modal id="addExamModal" title="Add Exam" icon="bi-plus-circle" :open="$errors->any()">
    <form action="{{ route('exams.store') }}" method="POST" class="form-glass" id="addExamForm">
        @csrf
        @error('title')
            <div class="alert alert-danger py-2 mb-3" style="border-radius:0.75rem;font-size:0.8rem;">{{ $message }}</div>
        @enderror
        <div class="mb-3">
            <label class="form-label">Exam Title</label>
            <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" placeholder="e.g., Midterm Exam" value="{{ old('title') }}" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Subject</label>
            <select name="subject_id" class="form-select">
                <option value="">-- None --</option>
                @foreach($subjects as $subject)
                    <option value="{{ $subject->id }}" @selected(old('subject_id') == $subject->id)>{{ $subject->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="row g-3 mb-3">
            <div class="col-7">
                <label class="form-label">Date</label>
                <input type="date" name="exam_date" class="form-control @error('exam_date') is-invalid @enderror" value="{{ old('exam_date', now()->format('Y-m-d')) }}" required>
            </div>
            <div class="col-5">
                <label class="form-label">Time</label>
                <input type="time" name="time" class="form-control" value="{{ old('time') }}">
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label">Location / Room</label>
            <input type="text" name="location" class="form-control" placeholder="e.g., Room 301" value="{{ old('location') }}">
        </div>
        <div class="row g-3">
            <div class="col-6">
                <label class="form-label">Priority</label>
                <select name="priority" class="form-select">
                    <option value="1">Low</option>
                    <option value="2">Medium-Low</option>
                    <option value="3" selected>Medium</option>
                    <option value="4">High</option>
                    <option value="5">Critical</option>
                </select>
            </div>
            <div class="col-6">
                <label class="form-label">Notes</label>
                <input type="text" name="notes" class="form-control" placeholder="Optional notes" value="{{ old('notes') }}">
            </div>
        </div>
    </form>
    <x-slot name="footer">
        <button type="button" class="btn-soft" data-app-close="true"><i class="bi bi-x"></i> Cancel</button>
        <button type="submit" class="dark-btn" form="addExamForm"><i class="bi bi-save"></i> Add Exam</button>
    </x-slot>
</x-app-modal>
@endsection
