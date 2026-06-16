@extends('Backend.master')

@section('content')
<div class="container-fluid px-0">
    <div class="page-header">
        <div>
            <h2>Quiz Results</h2>
            <p>{{ $quizAttempt->title }} &bull; {{ $quizAttempt->created_at->format('M d, Y') }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('quiz-attempts.create') }}" class="dark-btn"><i class="bi bi-arrow-repeat"></i> New Quiz</a>
            <a href="{{ route('quiz-attempts.index') }}" class="btn-soft"><i class="bi bi-arrow-left"></i> History</a>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="glass-card text-center py-4">
                <div style="font-size:2.5rem;font-weight:800;color:{{ $quizAttempt->score_percentage >= 80 ? '#059669' : ($quizAttempt->score_percentage >= 50 ? '#d97706' : '#dc2626') }};">
                    {{ $quizAttempt->score_percentage }}%
                </div>
                <small style="color:#6b7280;">Score</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="glass-card text-center py-4">
                <div style="font-size:2rem;font-weight:700;color:#6366f1;">{{ $quizAttempt->correct_answers }}/{{ $quizAttempt->total_questions }}</div>
                <small style="color:#6b7280;">Correct</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="glass-card text-center py-4">
                <div style="font-size:2rem;font-weight:700;color:#a855f7;">{{ $quizAttempt->total_questions - $quizAttempt->correct_answers }}</div>
                <small style="color:#6b7280;">Incorrect</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="glass-card text-center py-4">
                <div style="font-size:2rem;font-weight:700;color:#0ea5e9;">
                    @if($quizAttempt->time_taken_seconds > 0)
                        {{ floor($quizAttempt->time_taken_seconds / 60) }}m {{ $quizAttempt->time_taken_seconds % 60 }}s
                    @else
                        —
                    @endif
                </div>
                <small style="color:#6b7280;">Time Taken</small>
            </div>
        </div>
    </div>

    @if($quizAttempt->score_percentage >= 80)
        <div class="glass-card mb-4 d-flex align-items-center gap-3 py-3" style="border-left:4px solid #059669;">
            <i class="bi bi-trophy-fill" style="color:#059669;font-size:1.5rem;"></i>
            <span style="color:#1e1b4b;font-weight:600;">Great job! You're doing excellent. Keep it up!</span>
        </div>
    @elseif($quizAttempt->score_percentage >= 50)
        <div class="glass-card mb-4 d-flex align-items-center gap-3 py-3" style="border-left:4px solid #f59e0b;">
            <i class="bi bi-graph-up" style="color:#d97706;font-size:1.5rem;"></i>
            <span style="color:#1e1b4b;font-weight:600;">Good effort! Review the questions you missed and try again.</span>
        </div>
    @else
        <div class="glass-card mb-4 d-flex align-items-center gap-3 py-3" style="border-left:4px solid #ef4444;">
            <i class="bi bi-book" style="color:#dc2626;font-size:1.5rem;"></i>
            <span style="color:#1e1b4b;font-weight:600;">Keep studying! Review your notes and try again to improve.</span>
        </div>
    @endif

    <h5 style="color:#1e1b4b;font-weight:700;margin-bottom:1rem;">Detailed Review</h5>

    @foreach($results as $result)
        @php $q = $result['question']; @endphp
        <div class="glass-card mb-3 {{ $result['is_correct'] ? '' : 'border-start border-4 border-danger' }}" style="{{ !$result['is_correct'] ? 'border-left:4px solid #ef4444;' : 'border-left:4px solid #22c55e;' }}">
            <div class="d-flex align-items-start gap-3">
                <div style="font-size:1.2rem;">
                    @if($result['is_correct'])
                        <i class="bi bi-check-circle-fill" style="color:#22c55e;"></i>
                    @else
                        <i class="bi bi-x-circle-fill" style="color:#ef4444;"></i>
                    @endif
                </div>
                <div class="flex-grow-1">
                    @if($q['type'] === 'mcq')
                        <p style="color:#1e1b4b;font-weight:600;margin-bottom:0.5rem;">{{ $q['question'] }}</p>
                        <small style="color:#6b7280;">
                            Your answer: <strong>{{ isset($result['user_answer']) ? $q['options'][$result['user_answer']] ?? 'No answer' : 'No answer' }}</strong>
                            @if(!$result['is_correct'])
                                &nbsp;|&nbsp; Correct answer: <strong style="color:#059669;">{{ $q['correct_answer'] }}</strong>
                            @endif
                        </small>
                    @elseif($q['type'] === 'true-false')
                        <p style="color:#1e1b4b;font-weight:600;margin-bottom:0.5rem;">{{ $q['statement'] }}</p>
                        <small style="color:#6b7280;">
                            Your answer: <strong>{{ ucfirst($result['user_answer'] ?? 'No answer') }}</strong>
                            @if(!$result['is_correct'])
                                &nbsp;|&nbsp; Correct answer: <strong style="color:#059669;">{{ ucfirst($q['correct_answer']) }}</strong>
                            @endif
                        </small>
                    @endif
                </div>
            </div>
        </div>
    @endforeach
</div>
@endsection
