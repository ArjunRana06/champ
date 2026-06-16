@extends('Backend.master')

@section('content')
<div class="container-fluid px-0">
    <div class="page-header">
        <div>
            <h2>Matching Questions</h2>
            <p>Matching exercises generated from your study materials</p>
        </div>
        <a href="{{ route('matching.create') }}" class="dark-btn">
            <i class="bi bi-plus-circle"></i> Generate New
        </a>
    </div>

    @if(session('success'))
        <div class="glass-card mb-4 d-flex align-items-center gap-3 py-3" style="border-left:4px solid #059669;">
            <i class="bi bi-check-circle-fill" style="color:#059669;font-size:1.2rem;"></i>
            <span style="color:var(--text-primary);font-size:0.9rem;">{{ session('success') }}</span>
        </div>
    @endif

    <div class="glass-card mb-4 d-flex align-items-center justify-content-between py-2 px-3" id="score-bar" style="display:none;border-left:4px solid #6366f1;">
        <div>
            <span style="color:var(--text-primary);font-weight:600;font-size:0.9rem;">Score: </span>
            <span id="score-display" style="color:var(--card-accent);font-weight:700;font-size:1rem;">0</span>
            <span style="color:var(--text-secondary);font-size:0.85rem;"> / {{ $questions instanceof \Illuminate\Pagination\LengthAwarePaginator ? $questions->total() : $questions->count() }}</span>
        </div>
        <div class="d-flex align-items-center gap-2" style="flex:1;max-width:300px;">
            <div style="flex:1;height:8px;background:#e5e7eb;border-radius:4px;overflow:hidden;">
                <div id="progress-bar" style="height:100%;width:0%;background:linear-gradient(90deg,#6366f1,#22c55e);border-radius:4px;transition:width 0.5s ease;"></div>
            </div>
            <span id="progress-text" style="font-size:0.8rem;color:var(--text-secondary);font-weight:500;min-width:60px;">0%</span>
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
                            <a href="{{ route('matching.edit', $question) }}" class="btn-soft py-1 px-2" style="font-size:0.75rem;color:var(--card-accent);">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <button class="btn-soft py-1 px-2" style="font-size:0.75rem;color:#f59e0b;" onclick="toggleBookmark('App\\Models\\MatchingQuestion', {{ $question->id }}, this)">
                                <i class="bi bi-bookmark"></i>
                            </button>
                            <button class="btn-soft py-1 px-2" style="font-size:0.75rem;color:#dc2626;" onclick="confirmDelete({{ $question->id }})">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>

                    <p style="color:var(--text-secondary);font-size:0.8rem;font-weight:600;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:0.75rem;">Match each item with the correct match</p>

                    @php
                        $shuffled = $question->right_items;
                        shuffle($shuffled);
                    @endphp

                    <div class="matching-exercise" data-qid="{{ $question->id }}">
                        @foreach($question->left_items as $index => $leftItem)
                            <div class="row g-2 align-items-center mb-2">
                                <div class="col-5">
                                    <div class="matching-item py-2 px-3 rounded-3" style="background:#eef2ff;border:1px solid #c7d2fe;color:#4338ca;font-size:0.85rem;font-weight:500;height:100%;display:flex;align-items:center;">
                                        {{ $leftItem }}
                                    </div>
                                </div>
                                <div class="col-1 text-center" style="color:var(--text-muted);font-size:1.2rem;">
                                    <i class="bi bi-arrow-right"></i>
                                </div>
                                <div class="col-6">
                                    <select class="form-select matching-select" style="font-size:0.8rem;border-radius:0.75rem;background:#f8fafc;border:1.5px solid #e5e7eb;">
                                        <option value="">-- Select match --</option>
                                        @foreach($shuffled as $rightItem)
                                            <option value="{{ $rightItem }}">{{ $rightItem }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        @endforeach

                        <div class="matching-feedback mt-3" style="font-size:0.85rem;font-weight:500;min-height:1.5rem;"></div>

                        <button class="dark-btn w-100 mt-2 check-matching-btn" style="padding:0.6rem;">
                            <i class="bi bi-check-lg"></i> Check Answers
                        </button>
                    </div>

                    <script id="match-data-{{ $question->id }}" type="application/json">@json($question->correct_pairs)</script>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="glass-card text-center py-5">
                    <i class="bi bi-arrow-left-right" style="font-size:3rem;color:#c7d2fe;"></i>
                    <p class="mt-3" style="color:var(--text-secondary);">No matching questions generated yet. Click "Generate New" to create some from your notes.</p>
                    <a href="{{ route('matching.create') }}" class="dark-btn"><i class="bi bi-plus-circle"></i> Generate Questions</a>
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

    document.querySelectorAll('.check-matching-btn').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            var exercise = this.closest('.matching-exercise');
            if (!exercise) return;

            var qid = exercise.getAttribute('data-qid');
            if (!qid || answeredTracker.has(qid)) return;

            var script = document.getElementById('match-data-' + qid);
            if (!script) return;

            var correctPairs;
            try {
                correctPairs = JSON.parse(script.textContent);
            } catch(e) { return; }

            var rows = exercise.querySelectorAll('.row.g-2');
            var feedback = exercise.querySelector('.matching-feedback');

            var correctCount = 0;
            var totalPairs = rows.length;

            rows.forEach(function(row) {
                var select = row.querySelector('.matching-select');
                var matchingItem = row.querySelector('.matching-item');
                if (!select || !matchingItem) return;

                var selectedValue = select.value;
                var leftItem = matchingItem.textContent.trim();

                select.style.borderColor = '#e5e7eb';
                select.style.background = '#f8fafc';

                if (selectedValue && correctPairs[leftItem] && selectedValue === correctPairs[leftItem]) {
                    correctCount++;
                    select.style.borderColor = '#22c55e';
                    select.style.background = '#ecfdf5';
                } else if (selectedValue) {
                    select.style.borderColor = '#ef4444';
                    select.style.background = '#fef2f2';
                }
            });

            answeredTracker.add(qid);
            answered++;
            if (correctCount === totalPairs) score++;

            var isAllCorrect = correctCount === totalPairs;
            feedback.innerHTML = isAllCorrect
                ? '<span style="color:#16a34a;"><i class="bi bi-check-circle"></i> All correct! (' + correctCount + '/' + totalPairs + ')</span>'
                : '<span style="color:#dc2626;"><i class="bi bi-x-circle"></i> ' + correctCount + '/' + totalPairs + ' correct. Review the highlighted items.</span>';

            feedback.style.animation = 'fadeSlideIn 0.3s ease';

            rows.forEach(function(row) {
                var sel = row.querySelector('.matching-select');
                if (sel) sel.disabled = true;
            });
            this.disabled = true;

            updateScoreBar();
        });
    });

    function resetAll() {
        document.querySelectorAll('.matching-select').forEach(function(sel) {
            sel.value = '';
            sel.disabled = false;
            sel.style.borderColor = '#e5e7eb';
            sel.style.background = '#f8fafc';
        });
        document.querySelectorAll('.check-matching-btn').forEach(function(b) { b.disabled = false; });
        document.querySelectorAll('.matching-feedback').forEach(function(f) { f.innerHTML = ''; });
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
</style>

@foreach($questions as $question)
    <form id="delete-form-{{ $question->id }}" action="{{ route('matching.destroy', $question) }}" method="POST" style="display:none;">@csrf @method('DELETE')</form>
@endforeach
@endsection
