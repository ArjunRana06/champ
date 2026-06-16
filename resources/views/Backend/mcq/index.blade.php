@extends('Backend.master')

@section('content')
<div class="container-fluid px-0">
    <div class="page-header">
        <div>
            <h2>Generated MCQs</h2>
            <p>Practice questions generated from your study materials</p>
        </div>
        <a href="{{ route('mcqs.create') }}" class="dark-btn">
            <i class="bi bi-plus-circle"></i> Generate New MCQs
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
            <span style="color:#6b7280;font-size:0.85rem;"> / {{ $mcqs instanceof \Illuminate\Pagination\LengthAwarePaginator ? $mcqs->total() : $mcqs->count() }}</span>
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

    <div class="row g-4" id="mcq-container">
        @forelse($mcqs as $mcq)
            <div class="col-md-6" data-aos="fade-up" data-aos-delay="{{ $loop->index * 50 }}">
                <div class="glass-card h-100 position-relative" id="card-{{ $mcq->id }}">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <span style="font-size:0.7rem;padding:0.2rem 0.7rem;border-radius:20px;
                            background:{{ $mcq->difficulty === 'easy' ? '#ecfdf5' : ($mcq->difficulty === 'hard' ? '#fef2f2' : '#fffbeb') }};
                            color:{{ $mcq->difficulty === 'easy' ? '#059669' : ($mcq->difficulty === 'hard' ? '#dc2626' : '#d97706') }};
                            font-weight:600;">
                            {{ ucfirst($mcq->difficulty) }}
                        </span>
                        <div class="d-flex gap-1">
                            <a href="{{ route('mcqs.edit', $mcq) }}" class="btn-soft py-1 px-2" style="font-size:0.75rem;color:#6366f1;">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <button class="btn-soft py-1 px-2" style="font-size:0.75rem;color:#f59e0b;" onclick="toggleBookmark('App\\Models\\Mcq', {{ $mcq->id }}, this)">
                                <i class="bi bi-bookmark"></i>
                            </button>
                            <button class="btn-soft py-1 px-2" style="font-size:0.75rem;color:#dc2626;" onclick="confirmDelete({{ $mcq->id }})">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>

                    <p style="color:#1e1b4b;font-weight:600;font-size:0.95rem;margin-bottom:1rem;">{{ $mcq->question }}</p>

                    <div class="mb-3 options-container" data-question-id="{{ $mcq->id }}" data-correct="{{ $mcq->correct_answer }}" data-correct-index="{{ array_search($mcq->correct_answer, ['A','B','C','D']) }}" data-explanation="{{ $mcq->explanation }}">
                        @foreach($mcq->options as $option)
                            <div class="d-flex align-items-center gap-2 py-2 px-3 rounded-3 mb-1 option-row" data-index="{{ $loop->index }}" style="background:#f8fafc;border:1px solid #f1f5f9;transition:all 0.3s ease;cursor:pointer;">
                                <input class="form-check-input" type="radio" name="answer_{{ $mcq->id }}" value="{{ $loop->index }}" style="accent-color:#6366f1;">
                                <label style="color:#374151;font-size:0.85rem;cursor:pointer;width:100%;">{{ $option }}</label>
                            </div>
                        @endforeach
                    </div>

                    <div class="explanation-box" id="explanation-{{ $mcq->id }}" style="display:none;padding:0.75rem;border-radius:8px;background:#f0fdf4;border:1px solid #bbf7d0;margin-bottom:0.75rem;">
                        <i class="bi bi-info-circle" style="color:#16a34a;"></i>
                        <span id="explanation-text-{{ $mcq->id }}" style="color:#166534;font-size:0.85rem;"></span>
                    </div>

                    <div class="d-flex align-items-center gap-2" style="font-size:0.75rem;color:#6b7280;min-height:1.5rem;">
                        <span id="status-{{ $mcq->id }}"></span>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="glass-card text-center py-5">
                    <i class="bi bi-patch-question" style="font-size:3rem;color:#c7d2fe;"></i>
                    <p class="mt-3" style="color:#6b7280;">No MCQs generated yet. Click "Generate New MCQs" to create some from your notes.</p>
                    <a href="{{ route('mcqs.create') }}" class="dark-btn"><i class="bi bi-plus-circle"></i> Generate MCQs</a>
                </div>
            </div>
        @endforelse
    </div>

    @if(method_exists($mcqs, 'links'))
        <div class="mt-4 pagination-glass d-flex justify-content-center">
            {{ $mcqs->links() }}
        </div>
    @endif
</div>

<script>
    let score = 0;
    let answered = 0;
    const totalQuestions = {{ $mcqs instanceof \Illuminate\Pagination\LengthAwarePaginator ? $mcqs->total() : $mcqs->count() }};
    const answeredTracker = new Set();

    function updateScoreBar() {
        const bar = document.getElementById('score-bar');
        if (answered > 0) bar.style.display = 'flex';

        document.getElementById('score-display').textContent = score;
        const pct = Math.round((answered / totalQuestions) * 100);
        document.getElementById('progress-bar').style.width = pct + '%';
        document.getElementById('progress-text').textContent = pct + '%';
    }

    function markOption(container, selectedRow) {
        const questionId = container.dataset.questionId;
        const correctIndex = parseInt(container.dataset.correctIndex);
        const correctAnswer = container.dataset.correct;
        const explanation = container.dataset.explanation;
        const rows = container.querySelectorAll('.option-row');

        if (answeredTracker.has(questionId)) return;
        answeredTracker.add(questionId);

        const selectedIndex = parseInt(selectedRow.dataset.index);
        const isCorrect = selectedIndex === correctIndex;

        rows.forEach(r => r.classList.add('disabled'));
        rows.forEach(r => r.style.cursor = 'default');

        rows.forEach(r => {
            const label = r.querySelector('label');
            if (parseInt(r.dataset.index) === correctIndex) {
                r.style.background = '#ecfdf5';
                r.style.borderColor = '#22c55e';
                r.style.boxShadow = '0 0 0 2px rgba(34,197,94,0.2)';
                if (!r.querySelector('.mark-icon')) {
                    const marker = document.createElement('span');
                    marker.className = 'mark-icon ms-auto';
                    marker.innerHTML = '<i class="bi bi-check-circle-fill" style="color:#16a34a;font-size:0.9rem;"></i>';
                    r.querySelector('label').after(marker);
                }
            }
            if (!isCorrect && r === selectedRow) {
                r.style.background = '#fef2f2';
                r.style.borderColor = '#ef4444';
                r.style.boxShadow = '0 0 0 2px rgba(239,68,68,0.2)';
                if (!r.querySelector('.mark-icon')) {
                    const marker = document.createElement('span');
                    marker.className = 'mark-icon ms-auto';
                    marker.innerHTML = '<i class="bi bi-x-circle-fill" style="color:#dc2626;font-size:0.9rem;"></i>';
                    r.querySelector('label').after(marker);
                }
            }
        });

        answered++;
        if (isCorrect) score++;

        const statusEl = document.getElementById('status-' + questionId);
        statusEl.innerHTML = isCorrect
            ? '<span style="color:#16a34a;font-weight:600;"><i class="bi bi-check-circle"></i> Correct!</span>'
            : '<span style="color:#dc2626;font-weight:600;"><i class="bi bi-x-circle"></i> Incorrect. Correct answer: <strong>' + correctAnswer + '</strong></span>';

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

    document.querySelectorAll('.option-row').forEach(row => {
        row.addEventListener('click', function(e) {
            if (this.classList.contains('disabled')) return;
            const container = this.closest('.options-container');
            if (answeredTracker.has(container.dataset.questionId)) return;
            const radio = this.querySelector('input[type="radio"]');
            radio.checked = true;
            markOption(container, this);
        });
    });

    function resetAll() {
        document.querySelectorAll('.option-row').forEach(r => {
            r.classList.remove('disabled');
            r.style.cursor = 'pointer';
            r.style.background = '#f8fafc';
            r.style.borderColor = '#f1f5f9';
            r.style.boxShadow = 'none';
            const mark = r.querySelector('.mark-icon');
            if (mark) mark.remove();
            const radio = r.querySelector('input[type="radio"]');
            if (radio) radio.checked = false;
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
        if (confirm('Delete this MCQ?')) document.getElementById('delete-form-' + id).submit();
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
    .option-row {
        transition: all 0.3s ease;
        position: relative;
    }
    .option-row:not(.disabled):hover {
        background: #f1f5f9 !important;
        border-color: #cbd5e1 !important;
        transform: translateX(4px);
    }
    .option-row.disabled {
        opacity: 0.85;
    }
    @keyframes fadeSlideIn {
        from { opacity: 0; transform: translateY(-8px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>

@foreach($mcqs as $mcq)
    <form id="delete-form-{{ $mcq->id }}" action="{{ route('mcqs.destroy', $mcq) }}" method="POST" style="display:none;">@csrf @method('DELETE')</form>
@endforeach
@endsection
