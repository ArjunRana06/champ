@extends('Backend.master')

@section('content')
<div class="container-fluid px-0">
    <div class="page-header">
        <div>
            <h2>True / False Questions</h2>
            <p>Practice questions generated from your study materials</p>
        </div>
        <a href="{{ route('true-false.create') }}" class="dark-btn">
            <i class="bi bi-plus-circle"></i> Generate New
        </a>
    </div>

    @if(session('success'))
        <div class="glass-card mb-4 d-flex align-items-center gap-3 py-3" style="border-left:4px solid #059669;">
            <i class="bi bi-check-circle-fill" style="color:#059669;font-size:1.2rem;"></i>
            <span style="color:#1e1b4b;font-size:0.9rem;">{{ session('success') }}</span>
        </div>
    @endif

    <div class="glass-card mb-4 d-flex align-items-center justify-content-between py-2 px-3" id="score-bar" style="display:none;border-left:4px solid #6366f1;">
        <div>
            <span style="color:#1e1b4b;font-weight:600;font-size:0.9rem;">Score: </span>
            <span id="score-display" style="color:#6366f1;font-weight:700;font-size:1rem;">0</span>
            <span style="color:#6b7280;font-size:0.85rem;"> / {{ $questions instanceof \Illuminate\Pagination\LengthAwarePaginator ? $questions->total() : $questions->count() }}</span>
        </div>
        <div class="d-flex align-items-center gap-2" style="flex:1;max-width:300px;">
            <div style="flex:1;height:8px;background:#e5e7eb;border-radius:4px;overflow:hidden;">
                <div id="progress-bar" style="height:100%;width:0%;background:linear-gradient(90deg,#6366f1,#22c55e);border-radius:4px;transition:width 0.5s ease;"></div>
            </div>
            <span id="progress-text" style="font-size:0.8rem;color:#6b7280;font-weight:500;min-width:60px;">0%</span>
        </div>
        <button class="btn-soft py-1 px-2" style="font-size:0.75rem;" onclick="resetAll()">
            <i class="bi bi-arrow-counterclockwise"></i> Reset
        </button>
    </div>

    <div class="row g-4" id="question-container">
        @forelse($questions as $question)
            <div class="col-md-6" data-aos="fade-up" data-aos-delay="{{ $loop->index * 50 }}">
                <div class="glass-card h-100 position-relative" id="card-{{ $question->id }}">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <span style="font-size:0.7rem;padding:0.2rem 0.7rem;border-radius:20px;
                            background:{{ $question->difficulty === 'easy' ? '#ecfdf5' : ($question->difficulty === 'hard' ? '#fef2f2' : '#fffbeb') }};
                            color:{{ $question->difficulty === 'easy' ? '#059669' : ($question->difficulty === 'hard' ? '#dc2626' : '#d97706') }};
                            font-weight:600;">
                            {{ ucfirst($question->difficulty) }}
                        </span>
                        <div class="d-flex gap-1">
                            <a href="{{ route('true-false.edit', $question) }}" class="btn-soft py-1 px-2" style="font-size:0.75rem;color:#6366f1;">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <button class="btn-soft py-1 px-2" style="font-size:0.75rem;color:#f59e0b;" onclick="toggleBookmark('App\\Models\\TrueFalseQuestion', {{ $question->id }}, this)">
                                <i class="bi bi-bookmark"></i>
                            </button>
                            <button class="btn-soft py-1 px-2" style="font-size:0.75rem;color:#dc2626;" onclick="confirmDelete({{ $question->id }})">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>

                    <p style="color:#1e1b4b;font-weight:600;font-size:0.95rem;margin-bottom:1rem;">{{ $question->statement }}</p>

                    <div class="d-flex gap-3 mb-3" data-question-id="{{ $question->id }}" data-correct="{{ $question->correct_answer ? 'true' : 'false' }}" data-explanation="{{ $question->explanation }}">
                        <button class="tf-btn true-btn flex-fill py-3 px-4 rounded-3" style="background:#f8fafc;border:2px solid #e5e7eb;font-size:1.1rem;font-weight:700;color:#374151;transition:all 0.3s ease;cursor:pointer;">
                            <i class="bi bi-check-lg"></i> True
                        </button>
                        <button class="tf-btn false-btn flex-fill py-3 px-4 rounded-3" style="background:#f8fafc;border:2px solid #e5e7eb;font-size:1.1rem;font-weight:700;color:#374151;transition:all 0.3s ease;cursor:pointer;">
                            <i class="bi bi-x-lg"></i> False
                        </button>
                    </div>

                    <div class="explanation-box" id="explanation-{{ $question->id }}" style="display:none;padding:0.75rem;border-radius:8px;background:#f0fdf4;border:1px solid #bbf7d0;margin-bottom:0.75rem;">
                        <i class="bi bi-info-circle" style="color:#16a34a;"></i>
                        <span id="explanation-text-{{ $question->id }}" style="color:#166534;font-size:0.85rem;"></span>
                    </div>

                    <div class="d-flex align-items-center gap-2" style="font-size:0.75rem;color:#6b7280;min-height:1.5rem;">
                        <span id="status-{{ $question->id }}"></span>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="glass-card text-center py-5">
                    <i class="bi bi-check2-circle" style="font-size:3rem;color:#c7d2fe;"></i>
                    <p class="mt-3" style="color:#6b7280;">No True/False questions generated yet. Click "Generate New" to create some from your notes.</p>
                    <a href="{{ route('true-false.create') }}" class="dark-btn"><i class="bi bi-plus-circle"></i> Generate Questions</a>
                </div>
            </div>
        @endforelse
    </div>

    @if(method_exists($questions, 'links'))
        <div class="mt-4 pagination-glass d-flex justify-content-center">
            {{ $questions->links() }}
        </div>
    @endif
</div>

<script>
    let score = 0;
    let answered = 0;
    const totalQuestions = {{ $questions instanceof \Illuminate\Pagination\LengthAwarePaginator ? $questions->total() : $questions->count() }};
    const answeredTracker = new Set();

    function updateScoreBar() {
        const bar = document.getElementById('score-bar');
        if (answered > 0) bar.style.display = 'flex';
        document.getElementById('score-display').textContent = score;
        const pct = Math.round((answered / totalQuestions) * 100);
        document.getElementById('progress-bar').style.width = pct + '%';
        document.getElementById('progress-text').textContent = pct + '%';
    }

    function markAnswer(container, selected) {
        const questionId = container.dataset.questionId;
        const correct = container.dataset.correct;
        const explanation = container.dataset.explanation;

        if (answeredTracker.has(questionId)) return;
        answeredTracker.add(questionId);

        const isCorrect = selected === correct;
        const btns = container.querySelectorAll('.tf-btn');

        btns.forEach(b => b.style.cursor = 'default');

        btns.forEach(b => {
            if (b.dataset.value === correct) {
                b.style.background = '#ecfdf5';
                b.style.borderColor = '#22c55e';
                b.style.boxShadow = '0 0 0 2px rgba(34,197,94,0.2)';
                b.style.color = '#059669';
            }
            if (!isCorrect && b === selected) {
                b.style.background = '#fef2f2';
                b.style.borderColor = '#ef4444';
                b.style.boxShadow = '0 0 0 2px rgba(239,68,68,0.2)';
                b.style.color = '#dc2626';
            }
        });

        answered++;
        if (isCorrect) score++;

        const statusEl = document.getElementById('status-' + questionId);
        statusEl.innerHTML = isCorrect
            ? '<span style="color:#16a34a;font-weight:600;"><i class="bi bi-check-circle"></i> Correct!</span>'
            : '<span style="color:#dc2626;font-weight:600;"><i class="bi bi-x-circle"></i> Incorrect. Correct answer: <strong>' + correct.toUpperCase() + '</strong></span>';

        const explanationBox = document.getElementById('explanation-' + questionId);
        document.getElementById('explanation-text-' + questionId).textContent = explanation;
        explanationBox.style.display = 'block';
        explanationBox.style.animation = 'fadeSlideIn 0.3s ease';

        if (answered === totalQuestions) {
            setTimeout(() => {
                const pct = Math.round((score / totalQuestions) * 100);
                statusEl.innerHTML += '<br><span style="color:#6366f1;font-size:0.9rem;font-weight:600;"><i class="bi bi-flag"></i> Final: ' + score + '/' + totalQuestions + ' (' + pct + '%)</span>';
            }, 500);
        }

        updateScoreBar();
    }

    document.querySelectorAll('.true-btn').forEach(btn => {
        btn.dataset.value = 'true';
        btn.addEventListener('click', function() {
            const container = this.closest('[data-question-id]');
            if (answeredTracker.has(container.dataset.questionId)) return;
            markAnswer(container, this);
        });
    });

    document.querySelectorAll('.false-btn').forEach(btn => {
        btn.dataset.value = 'false';
        btn.addEventListener('click', function() {
            const container = this.closest('[data-question-id]');
            if (answeredTracker.has(container.dataset.questionId)) return;
            markAnswer(container, this);
        });
    });

    function resetAll() {
        document.querySelectorAll('.tf-btn').forEach(b => {
            b.style.cursor = 'pointer';
            b.style.background = '#f8fafc';
            b.style.borderColor = '#e5e7eb';
            b.style.boxShadow = 'none';
            b.style.color = '#374151';
        });
        document.querySelectorAll('.explanation-box').forEach(b => b.style.display = 'none');
        document.querySelectorAll('[id^="status-"]').forEach(s => s.innerHTML = '');
        score = 0;
        answered = 0;
        answeredTracker.clear();
        document.getElementById('score-bar').style.display = 'none';
        document.getElementById('score-display').textContent = '0';
        document.getElementById('progress-bar').style.width = '0%';
        document.getElementById('progress-text').textContent = '0%';
    }

    function confirmDelete(id) {
        if (confirm('Delete this question?')) document.getElementById('delete-form-' + id).submit();
    }

    function toggleBookmark(type, id, btn) {
        fetch('/bookmarks/toggle', {
            method: 'POST',
            headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
            body: JSON.stringify({bookmarkable_type: type, bookmarkable_id: id})
        })
        .then(r => r.json())
        .then(data => {
            const icon = btn.querySelector('i');
            icon.className = data.bookmarked ? 'bi bi-bookmark-fill' : 'bi bi-bookmark';
        });
    }
</script>

<style>
    .tf-btn:not(.disabled):hover {
        background: #f1f5f9 !important;
        border-color: #cbd5e1 !important;
        transform: translateY(-2px);
    }
    @keyframes fadeSlideIn {
        from { opacity: 0; transform: translateY(-8px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>

@foreach($questions as $question)
    <form id="delete-form-{{ $question->id }}" action="{{ route('true-false.destroy', $question) }}" method="POST" style="display:none;">@csrf @method('DELETE')</form>
@endforeach
@endsection
