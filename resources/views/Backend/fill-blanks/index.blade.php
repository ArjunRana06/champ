@extends('Backend.master')

@section('content')
<div class="container-fluid px-0">
    <div class="page-header">
        <div>
            <h2>Fill-in-the-Blank Questions</h2>
            <p>Practice questions generated from your study materials</p>
        </div>
        <a href="{{ route('fill-blanks.create') }}" class="dark-btn">
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
                            <a href="{{ route('fill-blanks.edit', $question) }}" class="btn-soft py-1 px-2" style="font-size:0.75rem;color:#6366f1;">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <button class="btn-soft py-1 px-2" style="font-size:0.75rem;color:#f59e0b;" onclick="toggleBookmark('App\\Models\\FillBlank', {{ $question->id }}, this)">
                                <i class="bi bi-bookmark"></i>
                            </button>
                            <button class="btn-soft py-1 px-2" style="font-size:0.75rem;color:#dc2626;" onclick="confirmDelete({{ $question->id }})">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>

                    <div class="mb-3 sentence-display" style="color:#1e1b4b;font-weight:500;font-size:0.95rem;line-height:2.2;">
                        {!! $question->sentence_with_blanks !!}
                    </div>

                    <div class="fb-container" data-qid="{{ $question->id }}">
                        <div class="d-flex gap-2 align-items-center">
                            <input type="text" class="form-control blank-input" placeholder="Fill in the blank..." style="background:#f8fafc;border:1.5px solid #e5e7eb;border-radius:1rem;font-size:0.85rem;padding:0.6rem 1rem;flex:1;">
                            <button class="dark-btn check-blank-btn" style="padding:0.6rem 1.2rem;font-size:0.85rem;">
                                <i class="bi bi-check-lg"></i> Check
                            </button>
                        </div>
                        <div class="blank-feedback mt-2" style="font-size:0.85rem;font-weight:500;min-height:1.5rem;"></div>
                    </div>

                    <script id="fb-data-{{ $question->id }}" type="application/json">@json($question->answers)</script>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="glass-card text-center py-5">
                    <i class="bi bi-input-cursor-text" style="font-size:3rem;color:#c7d2fe;"></i>
                    <p class="mt-3" style="color:#6b7280;">No fill-in-the-blank questions generated yet. Click "Generate New" to create some from your notes.</p>
                    <a href="{{ route('fill-blanks.create') }}" class="dark-btn"><i class="bi bi-plus-circle"></i> Generate Questions</a>
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

    document.querySelectorAll('.check-blank-btn').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            var container = this.closest('.fb-container');
            if (!container) return;
            var qid = container.getAttribute('data-qid');
            if (!qid || answeredTracker.has(qid)) return;

            var script = document.getElementById('fb-data-' + qid);
            if (!script) return;

            var correctAnswers;
            try {
                correctAnswers = JSON.parse(script.textContent);
            } catch(e) { return; }
            if (!Array.isArray(correctAnswers)) correctAnswers = [String(correctAnswers)];

            var input = container.querySelector('.blank-input');
            var feedback = container.querySelector('.blank-feedback');
            if (!input || !feedback) return;

            var userAnswer = input.value.trim().toLowerCase();

            var isCorrect = correctAnswers.some(function(a) {
                return String(a).toLowerCase() === userAnswer;
            });

            answeredTracker.add(qid);
            answered++;
            if (isCorrect) score++;

            if (isCorrect) {
                feedback.innerHTML = '<span style="color:#16a34a;"><i class="bi bi-check-circle"></i> Correct!</span>';
                input.style.borderColor = '#22c55e';
                input.style.background = '#ecfdf5';
            } else {
                feedback.innerHTML = '<span style="color:#dc2626;"><i class="bi bi-x-circle"></i> Incorrect. Correct answer(s): <strong>' + correctAnswers.join(', ') + '</strong></span>';
                input.style.borderColor = '#ef4444';
                input.style.background = '#fef2f2';
            }

            input.disabled = true;
            this.disabled = true;

            updateScoreBar();
        });
    });

    function resetAll() {
        document.querySelectorAll('.blank-input').forEach(function(inp) {
            inp.value = '';
            inp.disabled = false;
            inp.style.borderColor = '#e5e7eb';
            inp.style.background = '#f8fafc';
        });
        document.querySelectorAll('.check-blank-btn').forEach(function(b) { b.disabled = false; });
        document.querySelectorAll('.blank-feedback').forEach(function(f) { f.innerHTML = ''; });
        score = 0;
        answered = 0;
        answeredTracker.clear();
        var bar = document.getElementById('score-bar');
        if (bar) bar.style.display = 'none';
        var sd = document.getElementById('score-display');
        if (sd) sd.textContent = '0';
        var pb = document.getElementById('progress-bar');
        if (pb) pb.style.width = '0%';
        var pt = document.getElementById('progress-text');
        if (pt) pt.textContent = '0%';
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
    @keyframes fadeSlideIn {
        from { opacity: 0; transform: translateY(-8px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .sentence-display u {
        text-decoration: none;
        background: #fef3c7;
        padding: 0.1rem 0.3rem;
        border-radius: 4px;
        color: #92400e;
        font-weight: 700;
    }
</style>

@foreach($questions as $question)
    <form id="delete-form-{{ $question->id }}" action="{{ route('fill-blanks.destroy', $question) }}" method="POST" style="display:none;">@csrf @method('DELETE')</form>
@endforeach
@endsection
