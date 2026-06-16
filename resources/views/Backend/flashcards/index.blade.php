@extends('Backend.master')

@section('content')
<div class="container-fluid px-0">
    <div class="page-header">
        <div>
            <h2>Flashcards</h2>
            <p>Study cards generated from your materials</p>
        </div>
        <a href="{{ route('flashcards.create') }}" class="dark-btn">
            <i class="bi bi-plus-circle"></i> Generate New
        </a>
    </div>

    @if(session('success'))
        <div class="glass-card mb-4 d-flex align-items-center gap-3 py-3" style="border-left:4px solid #059669;">
            <i class="bi bi-check-circle-fill" style="color:#059669;font-size:1.2rem;"></i>
            <span style="color:#1e1b4b;font-size:0.9rem;">{{ session('success') }}</span>
        </div>
    @endif

    <div class="glass-card mb-4 d-flex align-items-center justify-content-between py-2 px-3" style="border-left:4px solid #6366f1;">
        <div>
            <span style="color:#1e1b4b;font-weight:600;font-size:0.9rem;">Progress: </span>
            <span id="known-count" style="color:#6366f1;font-weight:700;font-size:1rem;">0</span>
            <span style="color:#6b7280;font-size:0.85rem;"> known / {{ $flashcards instanceof \Illuminate\Pagination\LengthAwarePaginator ? $flashcards->total() : $flashcards->count() }}</span>
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

    <div class="row g-4" id="flashcard-container">
        @forelse($flashcards as $flashcard)
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="{{ $loop->index * 50 }}">
                <div class="glass-card position-relative flashcard-card" id="fcard-{{ $flashcard->id }}" style="min-height:240px;perspective:1000px;">
                    <div class="d-flex justify-content-between align-items-start mb-2" style="position:relative;z-index:2;">
                        <span style="font-size:0.7rem;padding:0.2rem 0.7rem;border-radius:20px;
                            background:{{ $flashcard->difficulty === 'easy' ? '#ecfdf5' : ($flashcard->difficulty === 'hard' ? '#fef2f2' : '#fffbeb') }};
                            color:{{ $flashcard->difficulty === 'easy' ? '#059669' : ($flashcard->difficulty === 'hard' ? '#dc2626' : '#d97706') }};
                            font-weight:600;">
                            {{ ucfirst($flashcard->difficulty) }}
                        </span>
                        <div class="d-flex gap-1">
                            <a href="{{ route('flashcards.edit', $flashcard) }}" class="btn-soft py-1 px-2" style="font-size:0.75rem;color:#6366f1;">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <button class="btn-soft py-1 px-2" style="font-size:0.75rem;color:#f59e0b;" onclick="toggleBookmark('App\\Models\\Flashcard', {{ $flashcard->id }}, this)">
                                <i class="bi bi-bookmark"></i>
                            </button>
                            <button class="btn-soft py-1 px-2 flashcard-delete-btn" style="font-size:0.75rem;color:#dc2626;" data-id="{{ $flashcard->id }}">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>

                    <div class="flashcard-inner" id="fc-{{ $flashcard->id }}" style="transition:transform 0.6s;transform-style:preserve-3d;position:relative;min-height:160px;cursor:pointer;">
                        <div class="flashcard-face flashcard-front" style="backface-visibility:hidden;position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;">
                            <p style="color:#1e1b4b;font-weight:600;font-size:1rem;text-align:center;padding:1.5rem 0.5rem;margin:0;">
                                {{ $flashcard->front }}
                            </p>
                            <p class="click-hint" style="text-align:center;font-size:0.75rem;color:#9ca3af;margin-top:0.5rem;">
                                <i class="bi bi-arrow-repeat"></i> Click to reveal
                            </p>
                        </div>
                        <div class="flashcard-face flashcard-back" style="backface-visibility:hidden;position:absolute;inset:0;transform:rotateY(180deg);display:flex;align-items:center;justify-content:center;padding:1rem;">
                            <p style="color:#4338ca;font-weight:500;font-size:0.95rem;text-align:center;margin:0;">
                                {{ $flashcard->back }}
                            </p>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-3 flashcard-actions" id="actions-{{ $flashcard->id }}" style="display:none;position:relative;z-index:2;">
                        <button class="btn-soft success flex-fill known-btn" data-id="{{ $flashcard->id }}" style="border-color:#22c55e;color:#059669;">
                            <i class="bi bi-check-lg"></i> Know
                        </button>
                        <button class="btn-soft danger flex-fill review-btn" data-id="{{ $flashcard->id }}" style="border-color:#ef4444;color:#dc2626;">
                            <i class="bi bi-arrow-repeat"></i> Review Later
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="glass-card text-center py-5">
                    <i class="bi bi-card-text" style="font-size:3rem;color:#c7d2fe;"></i>
                    <p class="mt-3" style="color:#6b7280;">No flashcards generated yet. Click "Generate New" to create some from your notes.</p>
                    <a href="{{ route('flashcards.create') }}" class="dark-btn"><i class="bi bi-plus-circle"></i> Generate Flashcards</a>
                </div>
            </div>
        @endforelse
    </div>

    @if(method_exists($flashcards, 'links'))
        <div class="mt-4 pagination-glass d-flex justify-content-center">
            {{ $flashcards->links() }}
        </div>
    @endif
</div>

<script>
    let knownCount = 0;
    const totalCards = {{ $flashcards instanceof \Illuminate\Pagination\LengthAwarePaginator ? $flashcards->total() : $flashcards->count() }};
    const flippedCards = new Set();

    function updateProgress() {
        document.getElementById('known-count').textContent = knownCount;
        const pct = totalCards > 0 ? Math.round((knownCount / totalCards) * 100) : 0;
        document.getElementById('progress-bar').style.width = pct + '%';
        document.getElementById('progress-text').textContent = pct + '%';
    }

    function flipCard(innerEl) {
        if (!innerEl) return;
        const id = innerEl.id.replace('fc-', '');
        if (flippedCards.has(id)) return;

        innerEl.style.transform = 'rotateY(180deg)';
        flippedCards.add(id);

        var actions = document.getElementById('actions-' + id);
        if (actions) {
            actions.style.display = 'flex';
            actions.style.animation = 'fadeSlideIn 0.3s ease';
        }
    }

    document.querySelectorAll('.flashcard-inner').forEach(function(inner) {
        inner.addEventListener('click', function(e) {
            if (e.target.closest('.flashcard-delete-btn') || e.target.closest('.known-btn') || e.target.closest('.review-btn')) return;
            flipCard(this);
        });
    });

    document.querySelectorAll('.known-btn').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            var card = this.closest('.flashcard-card');
            if (!card) return;
            card.style.opacity = '0.4';
            card.style.pointerEvents = 'none';
            knownCount++;
            updateProgress();
        });
    });

    document.querySelectorAll('.review-btn').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            var card = this.closest('.flashcard-card');
            if (!card) return;
            var inner = card.querySelector('.flashcard-inner');
            if (inner) {
                inner.style.transform = 'rotateY(0deg)';
                var id = inner.id.replace('fc-', '');
                flippedCards.delete(id);
            }
            var actions = card.querySelector('.flashcard-actions');
            if (actions) {
                actions.style.display = 'none';
            }
        });
    });

    document.querySelectorAll('.flashcard-delete-btn').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            if (confirm('Delete this flashcard?')) {
                var id = this.dataset.id;
                document.getElementById('delete-form-' + id).submit();
            }
        });
    });

    function resetAll() {
        document.querySelectorAll('.flashcard-inner').forEach(function(fc) {
            fc.style.transform = 'rotateY(0deg)';
        });
        document.querySelectorAll('.flashcard-actions').forEach(function(a) {
            a.style.display = 'none';
        });
        document.querySelectorAll('.flashcard-card').forEach(function(c) {
            c.style.opacity = '1';
            c.style.pointerEvents = 'auto';
        });
        knownCount = 0;
        flippedCards.clear();
        updateProgress();
    }

    function confirmDelete(id) {
        if (confirm('Delete this flashcard?')) document.getElementById('delete-form-' + id).submit();
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
    .flashcard-inner {
        z-index: 1;
    }
    .flashcard-face {
        border-radius: 0.75rem;
    }
    .flashcard-back {
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        border-radius: 0.75rem;
    }
</style>

@foreach($flashcards as $flashcard)
    <form id="delete-form-{{ $flashcard->id }}" action="{{ route('flashcards.destroy', $flashcard) }}" method="POST" style="display:none;">@csrf @method('DELETE')</form>
@endforeach
@endsection
