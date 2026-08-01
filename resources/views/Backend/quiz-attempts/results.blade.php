@extends('Backend.master')

@section('content')
<style>
    .stat-card { transition: all 0.3s ease; position: relative; overflow: hidden; }
    .stat-card:hover { transform: translateY(-2px); }
    .stat-card .stat-icon { font-size: 1.8rem; opacity: 0.15; position: absolute; right: 10px; bottom: 5px; }
    .progress-ring { width: 100px; height: 100px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto; position: relative; }
    .progress-ring svg { position: absolute; top: 0; left: 0; }
    .result-option { padding: 0.5rem 0.75rem; border-radius: 0.6rem; font-size: 0.85rem; margin-bottom: 0.25rem; display: flex; align-items: center; gap: 0.5rem; transition: all 0.2s; }
    .result-option.correct { background: rgba(16,185,129,0.1); border: 1.5px solid rgba(16,185,129,0.3); color: #065f46; }
    .result-option.wrong { background: rgba(239,68,68,0.08); border: 1.5px solid rgba(239,68,68,0.25); color: #991b1b; }
    .result-option.neutral { background: var(--input-bg); border: 1.5px solid var(--input-border); color: var(--text-secondary); }
    .result-option .opt-label { font-weight: 700; font-size: 0.75rem; width: 22px; height: 22px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .result-option.correct .opt-label { background: rgba(16,185,129,0.2); color: #059669; }
    .result-option.wrong .opt-label { background: rgba(239,68,68,0.2); color: #dc2626; }
    .result-option.neutral .opt-label { background: var(--badge-bg); color: var(--text-muted); }
    .answer-badge { display: inline-flex; align-items: center; gap: 0.3rem; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.78rem; font-weight: 600; }
    .answer-badge.correct { background: rgba(16,185,129,0.12); color: #059669; }
    .answer-badge.wrong { background: rgba(239,68,68,0.12); color: #dc2626; }
    .explain-box { background: rgba(99,102,241,0.05); border: 1px solid rgba(99,102,241,0.15); border-radius: 0.6rem; padding: 0.6rem 0.75rem; margin-top: 0.5rem; font-size: 0.82rem; color: var(--text-secondary); }
</style>

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

    @php
        $correct = $quizAttempt->correct_answers;
        $total = $quizAttempt->total_questions;
        $wrong = $total - $correct;
        $pct = $quizAttempt->score_percentage;
        $ringColor = $pct >= 80 ? '#059669' : ($pct >= 50 ? '#d97706' : '#dc2626');
        $circumference = 2 * 3.14159 * 38;
        $offset = $circumference - ($pct / 100) * $circumference;
    @endphp

    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="glass-card stat-card text-center py-4">
                <div class="progress-ring">
                    <svg width="100" height="100" viewBox="0 0 100 100">
                        <circle cx="50" cy="50" r="38" fill="none" stroke="var(--input-border)" stroke-width="6"/>
                        <circle cx="50" cy="50" r="38" fill="none" stroke="{{ $ringColor }}" stroke-width="6"
                            stroke-dasharray="{{ $circumference }}" stroke-dashoffset="{{ $offset }}"
                            stroke-linecap="round" transform="rotate(-90 50 50)" style="transition: stroke-dashoffset 1s ease;"/>
                    </svg>
                    <span style="font-size:1.5rem;font-weight:800;color:{{ $ringColor }};">{{ $pct }}%</span>
                </div>
                <small style="color:var(--text-secondary);margin-top:0.25rem;display:block;">Score</small>
                <span class="stat-icon"><i class="bi bi-trophy"></i></span>
            </div>
        </div>
        <div class="col-md-3">
            <div class="glass-card stat-card text-center py-4">
                <div style="font-size:2rem;font-weight:700;color:var(--card-accent);">{{ $correct }}/{{ $total }}</div>
                <small style="color:var(--text-secondary);">Correct</small>
                <div style="font-size:0.75rem;color:#059669;margin-top:0.2rem;">
                    <i class="bi bi-check-circle-fill"></i> {{ $total > 0 ? round(($correct/$total)*100) : 0 }}% accuracy
                </div>
                <span class="stat-icon"><i class="bi bi-check-circle-fill" style="color:#059669;"></i></span>
            </div>
        </div>
        <div class="col-md-3">
            <div class="glass-card stat-card text-center py-4">
                <div style="font-size:2rem;font-weight:700;color:#ef4444;">{{ $wrong }}</div>
                <small style="color:var(--text-secondary);">Incorrect</small>
                <div style="font-size:0.75rem;color:#ef4444;margin-top:0.2rem;">
                    <i class="bi bi-x-circle-fill"></i> Needs review
                </div>
                <span class="stat-icon"><i class="bi bi-x-circle-fill" style="color:#ef4444;"></i></span>
            </div>
        </div>
        <div class="col-md-3">
            <div class="glass-card stat-card text-center py-4">
                <div style="font-size:2rem;font-weight:700;color:#0ea5e9;">
                    @if($quizAttempt->time_taken_seconds > 0)
                        {{ floor($quizAttempt->time_taken_seconds / 60) }}m {{ $quizAttempt->time_taken_seconds % 60 }}s
                    @else
                        —
                    @endif
                </div>
                <small style="color:var(--text-secondary);">Time Taken</small>
                <div style="font-size:0.75rem;color:var(--text-muted);margin-top:0.2rem;">
                    <i class="bi bi-clock"></i>
                    @if($total > 0 && $quizAttempt->time_taken_seconds > 0)
                        {{ round($quizAttempt->time_taken_seconds / $total) }}s per question
                    @endif
                </div>
                <span class="stat-icon"><i class="bi bi-clock" style="color:#0ea5e9;"></i></span>
            </div>
        </div>
    </div>

    @if($pct >= 80)
        <div class="glass-card mb-4 d-flex align-items-center gap-3 py-3" style="border-left:4px solid #059669;background:rgba(16,185,129,0.04);">
            <i class="bi bi-trophy-fill" style="color:#059669;font-size:1.5rem;"></i>
            <span style="color:var(--text-primary);font-weight:600;">Great job! You're doing excellent. Keep it up!</span>
        </div>
    @elseif($pct >= 50)
        <div class="glass-card mb-4 d-flex align-items-center gap-3 py-3" style="border-left:4px solid #f59e0b;background:rgba(245,158,11,0.04);">
            <i class="bi bi-graph-up" style="color:#d97706;font-size:1.5rem;"></i>
            <span style="color:var(--text-primary);font-weight:600;">Good effort! Review the questions you missed and try again.</span>
        </div>
    @else
        <div class="glass-card mb-4 d-flex align-items-center gap-3 py-3" style="border-left:4px solid #ef4444;background:rgba(239,68,68,0.04);">
            <i class="bi bi-book" style="color:#dc2626;font-size:1.5rem;"></i>
            <span style="color:var(--text-primary);font-weight:600;">Keep studying! Review your notes and try again to improve.</span>
        </div>
    @endif

    <div class="d-flex align-items-center justify-content-between mb-3">
        <h5 style="color:var(--text-primary);font-weight:700;margin:0;">
            <i class="bi bi-list-check me-1" style="color:var(--card-accent);"></i> Detailed Review
        </h5>
        <div class="d-flex gap-2">
            <span class="answer-badge correct"><i class="bi bi-check-circle-fill"></i> {{ $correct }} Correct</span>
            <span class="answer-badge wrong"><i class="bi bi-x-circle-fill"></i> {{ $wrong }} Wrong</span>
        </div>
    </div>

    @foreach($results as $index => $result)
        @php
            $q = $result['question'];
            $isCorrect = $result['is_correct'];
            $userAnswer = $result['user_answer'] ?? null;
            $answerLabels = ['A', 'B', 'C', 'D', 'E', 'F'];
            $barColor = $isCorrect ? '#22c55e' : '#ef4444';
        @endphp

        <div class="glass-card mb-3" style="border-left:4px solid {{ $barColor }};{{ $isCorrect ? '' : 'background:rgba(239,68,68,0.02);' }}">
            <div class="d-flex align-items-start gap-3">
                <div style="font-size:1.3rem;padding-top:2px;">
                    @if($isCorrect)
                        <i class="bi bi-check-circle-fill" style="color:#22c55e;"></i>
                    @else
                        <i class="bi bi-x-circle-fill" style="color:#ef4444;"></i>
                    @endif
                </div>
                <div class="flex-grow-1" style="min-width:0;">

                    <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                        <span style="font-size:0.7rem;padding:0.15rem 0.55rem;border-radius:20px;background:var(--badge-bg);color:var(--card-accent);font-weight:600;">
                            Q{{ $index + 1 }}
                        </span>
                        @if($q['type'] === 'mcq')
                            <span style="font-size:0.65rem;padding:0.1rem 0.5rem;border-radius:20px;background:rgba(99,102,241,0.08);color:var(--card-accent);">MCQ</span>
                        @else
                            <span style="font-size:0.65rem;padding:0.1rem 0.5rem;border-radius:20px;background:rgba(168,85,247,0.08);color:#a855f7;">T/F</span>
                        @endif
                        @if(!$isCorrect)
                            <span class="answer-badge wrong" style="font-size:0.7rem;padding:0.1rem 0.55rem;">Incorrect</span>
                        @else
                            <span class="answer-badge correct" style="font-size:0.7rem;padding:0.1rem 0.55rem;">Correct</span>
                        @endif
                    </div>

                    @if($q['type'] === 'mcq')
                        <p style="color:var(--text-primary);font-weight:600;font-size:0.92rem;margin-bottom:0.75rem;">{{ $q['question'] }}</p>

                        @php
                            $correctAnswerText = $q['options'][$q['correct_answer']] ?? $q['correct_answer'];
                        @endphp

                        <div style="margin-bottom:0.5rem;">
                            @foreach($q['options'] as $optIdx => $optText)
                                @php
                                    $isUserPick = isset($userAnswer) && (string)$optIdx === (string)$userAnswer;
                                    $isRightAnswer = $optIdx === (int)$q['correct_answer'];

                                    $class = 'neutral';
                                    if ($isRightAnswer) $class = 'correct';
                                    if ($isUserPick && !$isRightAnswer) $class = 'wrong';
                                    $label = $answerLabels[$optIdx] ?? $optIdx;
                                @endphp
                                <div class="result-option {{ $class }}">
                                    <span class="opt-label">{{ $label }}</span>
                                    <span>{{ $optText }}</span>
                                    @if($isRightAnswer)
                                        <span style="font-size:0.7rem;color:#059669;margin-left:auto;"><i class="bi bi-check-lg"></i> Correct</span>
                                    @elseif($isUserPick)
                                        <span style="font-size:0.7rem;color:#dc2626;margin-left:auto;"><i class="bi bi-x-lg"></i> Your pick</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        @if(!$isCorrect)
                            <div class="explain-box">
                                <i class="bi bi-lightbulb me-1" style="color:var(--card-accent);"></i>
                                The correct answer is <strong style="color:var(--card-accent);">{{ $correctAnswerText }}</strong>
                                @if(isset($userAnswer) && isset($q['options'][$userAnswer]) && $q['options'][$userAnswer] !== $correctAnswerText)
                                    &mdash; you selected <strong style="color:#dc2626;">{{ $q['options'][$userAnswer] }}</strong>
                                @endif
                            </div>
                        @endif

                    @elseif($q['type'] === 'true-false')
                        <p style="color:var(--text-primary);font-weight:600;font-size:0.92rem;margin-bottom:0.75rem;">{{ $q['statement'] }}</p>

                        @php
                            $tfCorrect = ucfirst($q['correct_answer']);
                            $tfUser = ucfirst($userAnswer ?? 'No answer');
                        @endphp

                        <div class="d-flex gap-2 mb-2">
                            <div class="result-option {{ $tfCorrect === 'True' ? 'correct' : ($tfUser === 'True' ? 'wrong' : 'neutral') }}" style="flex:1;">
                                <span class="opt-label"><i class="bi bi-check-lg"></i></span>
                                <span>True</span>
                                @if($tfCorrect === 'True')
                                    <span style="font-size:0.7rem;color:#059669;margin-left:auto;"><i class="bi bi-check-lg"></i> Correct</span>
                                @elseif($tfUser === 'True')
                                    <span style="font-size:0.7rem;color:#dc2626;margin-left:auto;"><i class="bi bi-x-lg"></i> Your pick</span>
                                @endif
                            </div>
                            <div class="result-option {{ $tfCorrect === 'False' ? 'correct' : ($tfUser === 'False' ? 'wrong' : 'neutral') }}" style="flex:1;">
                                <span class="opt-label"><i class="bi bi-x-lg"></i></span>
                                <span>False</span>
                                @if($tfCorrect === 'False')
                                    <span style="font-size:0.7rem;color:#059669;margin-left:auto;"><i class="bi bi-check-lg"></i> Correct</span>
                                @elseif($tfUser === 'False')
                                    <span style="font-size:0.7rem;color:#dc2626;margin-left:auto;"><i class="bi bi-x-lg"></i> Your pick</span>
                                @endif
                            </div>
                        </div>

                        @if(!$isCorrect)
                            <div class="explain-box">
                                <i class="bi bi-lightbulb me-1" style="color:var(--card-accent);"></i>
                                The correct answer is <strong style="color:var(--card-accent);">{{ $tfCorrect }}</strong>
                                &mdash; you selected <strong style="color:#dc2626;">{{ $tfUser }}</strong>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    @endforeach

    @if(count($results) === 0)
        <div class="glass-card text-center py-5">
            <i class="bi bi-inbox" style="font-size:2.5rem;color:var(--text-muted);display:block;margin-bottom:0.5rem;"></i>
            <p style="color:var(--text-muted);">No results available.</p>
        </div>
    @endif
</div>
@endsection
