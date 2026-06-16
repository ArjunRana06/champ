@extends('Backend.master')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 style="color:var(--text-primary);font-weight:800;font-size:1.3rem;">
                <i class="bi bi-play-circle me-2" style="color:var(--card-accent);"></i>
                {{ $quizAttempt->is_exam_mode ? '📝 Exam Mode' : '📝 Practice Quiz' }}
            </h2>
            <p style="color:var(--text-secondary);font-size:0.85rem;">{{ count($questions) }} questions</p>
        </div>
        <div class="d-flex align-items-center gap-3">
            @if($timeLimit > 0)
                <div class="glass-card py-2 px-3 d-flex align-items-center gap-2">
                    <i class="bi bi-clock" style="color:#ef4444;"></i>
                    <span id="timer" style="font-weight:700;color:var(--text-primary);font-size:1.1rem;">{{ $timeLimit }}:00</span>
                </div>
            @endif
            <span id="progress-text" style="color:var(--text-secondary);font-weight:500;">0/{{ count($questions) }}</span>
        </div>
    </div>

    <form id="quizForm" action="{{ route('quiz-attempts.submit', $quizAttempt) }}" method="POST">
        @csrf
        <input type="hidden" name="time_taken" id="timeTaken" value="0">

        @foreach($questions as $index => $q)
            <div class="glass-card mb-3 question-card" id="q-{{ $index }}">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span style="font-size:0.7rem;padding:0.2rem 0.7rem;border-radius:20px;background:#eef2ff;color:var(--card-accent);font-weight:600;">
                        Question {{ $index + 1 }} of {{ count($questions) }}
                    </span>
                    <span style="font-size:0.7rem;padding:0.2rem 0.7rem;border-radius:20px;
                        background:{{ $q['difficulty'] === 'easy' ? '#ecfdf5' : ($q['difficulty'] === 'hard' ? '#fef2f2' : '#fffbeb') }};
                        color:{{ $q['difficulty'] === 'easy' ? '#059669' : ($q['difficulty'] === 'hard' ? '#dc2626' : '#d97706') }};
                        font-weight:600;">
                        {{ ucfirst($q['difficulty']) }}
                    </span>
                </div>

                @if($q['type'] === 'mcq')
                    <p style="color:var(--text-primary);font-weight:600;font-size:0.95rem;margin-bottom:1rem;">{{ $q['question'] }}</p>
                    <div class="options-group">
                        @foreach($q['options'] as $optIndex => $option)
                            <label class="d-flex align-items-center gap-2 py-2 px-3 rounded-3 mb-1 option-label" style="background:#f8fafc;border:1px solid #f1f5f9;cursor:pointer;transition:all 0.2s;">
                                <input type="radio" name="answers[{{ $index }}]" value="{{ $optIndex }}" class="form-check-input" style="accent-color:var(--card-accent);">
                                <span style="color:var(--text-primary);font-size:0.85rem;">{{ $option }}</span>
                            </label>
                        @endforeach
                    </div>
                @elseif($q['type'] === 'true-false')
                    <p style="color:var(--text-primary);font-weight:600;font-size:0.95rem;margin-bottom:1rem;">{{ $q['statement'] }}</p>
                    <div class="d-flex gap-3">
                        <label class="flex-fill text-center py-3 px-4 rounded-3 tf-label" style="background:#f8fafc;border:2px solid #e5e7eb;cursor:pointer;transition:all 0.2s;">
                            <input type="radio" name="answers[{{ $index }}]" value="true" class="d-none">
                            <span style="font-weight:700;font-size:1.1rem;"><i class="bi bi-check-lg"></i> True</span>
                        </label>
                        <label class="flex-fill text-center py-3 px-4 rounded-3 tf-label" style="background:#f8fafc;border:2px solid #e5e7eb;cursor:pointer;transition:all 0.2s;">
                            <input type="radio" name="answers[{{ $index }}]" value="false" class="d-none">
                            <span style="font-weight:700;font-size:1.1rem;"><i class="bi bi-x-lg"></i> False</span>
                        </label>
                    </div>
                @endif
            </div>
        @endforeach

        <div class="glass-card p-3 text-center">
            <button type="submit" class="dark-btn" id="submitQuizBtn" style="padding:0.8rem 3rem;font-size:1rem;">
                <i class="bi bi-check-circle"></i> Submit Quiz
            </button>
            <p class="mt-2" style="color:var(--text-muted);font-size:0.8rem;">Answered: <span id="answeredCount">0</span>/{{ count($questions) }}</p>
        </div>
    </form>
</div>

<style>
    .option-label:hover { background:#f1f5f9 !important; border-color:#cbd5e1 !important; }
    .option-label:has(input:checked) { background:#eef2ff !important; border-color:#6366f1 !important; }
    .tf-label:has(input:checked) { background:#eef2ff !important; border-color:#6366f1 !important; }
    .question-card { transition: all 0.3s ease; }
    .question-card.answered { border-left: 4px solid #6366f1; }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const totalQuestions = {{ count($questions) }};
        const answered = new Set();
        const progressText = document.getElementById('progress-text');
        const answeredCount = document.getElementById('answeredCount');

        document.querySelectorAll('input[type="radio"]').forEach(radio => {
            radio.addEventListener('change', function() {
                const card = this.closest('.question-card');
                if (card) {
                    card.classList.add('answered');
                    const qIndex = Array.from(document.querySelectorAll('.question-card')).indexOf(card);
                    answered.add(qIndex);
                    progressText.textContent = `${answered.size}/${totalQuestions}`;
                    if (answeredCount) answeredCount.textContent = answered.size;
                }
            });
        });

        @if($timeLimit > 0)
        let timeLeft = {{ $timeLimit * 60 }};
        const timerEl = document.getElementById('timer');
        const timerInterval = setInterval(() => {
            timeLeft--;
            const mins = Math.floor(timeLeft / 60);
            const secs = timeLeft % 60;
            timerEl.textContent = `${mins}:${secs.toString().padStart(2, '0')}`;
            document.getElementById('timeTaken').value = {{ $timeLimit * 60 }} - timeLeft;
            if (timeLeft <= 0) {
                clearInterval(timerInterval);
                document.getElementById('quizForm').submit();
            }
            if (timeLeft <= 60) timerEl.style.color = '#dc2626';
        }, 1000);
        @endif

        document.getElementById('submitQuizBtn').addEventListener('click', function(e) {
            if (answered.size < totalQuestions) {
                if (!confirm(`You've answered ${answered.size}/${totalQuestions} questions. Submit anyway?`)) {
                    e.preventDefault();
                }
            }
        });
    });
</script>
@endsection
