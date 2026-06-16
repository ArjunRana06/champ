@extends('Backend.master')

@section('content')
<div class="container-fluid px-0">
    <div class="page-header">
        <div>
            <h2>Quiz Attempts</h2>
            <p>Track your practice quiz performance over time</p>
        </div>
        <a href="{{ route('quiz-attempts.create') }}" class="dark-btn"><i class="bi bi-play-circle"></i> Start Quiz</a>
    </div>

    @if(session('success'))
        <div class="glass-card mb-4 d-flex align-items-center gap-3 py-3" style="border-left:4px solid #059669;">
            <i class="bi bi-check-circle-fill" style="color:#059669;font-size:1.2rem;"></i>
            <span style="color:#1e1b4b;font-size:0.9rem;">{{ session('success') }}</span>
        </div>
    @endif

    <div class="glass-card">
        <div class="table-responsive">
            <table class="glass-table">
                <thead>
                    <tr><th>Date</th><th>Title</th><th>Type</th><th>Score</th><th>Time</th><th></th></tr>
                </thead>
                <tbody>
                    @forelse($attempts as $attempt)
                    <tr>
                        <td>{{ $attempt->created_at->format('M d, Y') }}</td>
                        <td>{{ $attempt->title }} @if($attempt->is_exam_mode)<span class="stat-badge up" style="background:#fef2f2;color:#dc2626;">EXAM</span>@endif</td>
                        <td><span style="font-size:0.7rem;padding:0.15rem 0.6rem;border-radius:20px;background:#eef2ff;color:#6366f1;">{{ $attempt->type }}</span></td>
                        <td>
                            <span style="font-weight:700;color:{{ $attempt->score_percentage >= 80 ? '#059669' : ($attempt->score_percentage >= 50 ? '#d97706' : '#dc2626') }};">
                                {{ $attempt->correct_answers }}/{{ $attempt->total_questions }} ({{ $attempt->score_percentage }}%)
                            </span>
                        </td>
                        <td style="color:#6b7280;">
                            @if($attempt->time_taken_seconds > 0)
                                {{ floor($attempt->time_taken_seconds / 60) }}m {{ $attempt->time_taken_seconds % 60 }}s
                            @else
                                —
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('quiz-attempts.results', $attempt) }}" class="btn-soft py-1 px-2" style="font-size:0.75rem;"><i class="bi bi-bar-chart"></i></a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" style="text-align:center;color:#9ca3af;padding:2rem;">No quiz attempts yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if(method_exists($attempts, 'links'))
        <div class="mt-4 pagination-glass d-flex justify-content-center">{{ $attempts->links() }}</div>
    @endif
</div>
@endsection
