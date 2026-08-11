@extends('Backend.master')

@php
if (!function_exists('questionText')) {
    function questionText($item) {
        return $item->question ?? $item->statement ?? $item->front ?? $item->sentence_with_blanks ?? (is_array($item->left_items) ? ($item->left_items[0] ?? 'Matching question') : 'Question #'.$item->id);
    }
}
@endphp

@section('content')
<div class="container-fluid px-0">
    @if($hasGroups)
    <div class="page-header">
        <div>
            <h2>Peer Reviews</h2>
            <p>Review questions from other students and earn XP</p>
        </div>
        <div class="d-flex gap-2">
            <span class="stat-badge up" style="font-size:0.75rem;padding:0.3rem 0.8rem;">
                <i class="bi bi-star-fill"></i> +5 XP per review
            </span>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-4 col-6">
            <div class="glass-card text-center py-3">
                <div style="font-size:1.8rem;font-weight:800;color:var(--card-accent);">{{ $available->count() }}</div>
                <small style="color:var(--text-muted);">Available for Review</small>
            </div>
        </div>
        <div class="col-md-4 col-6">
            <div class="glass-card text-center py-3">
                <div style="font-size:1.8rem;font-weight:800;color:var(--card-accent);">{{ $receivedReviews->count() }}</div>
                <small style="color:var(--text-muted);">Reviews Received</small>
            </div>
        </div>
        <div class="col-md-4 col-6">
            <div class="glass-card text-center py-3">
                <div style="font-size:1.8rem;font-weight:800;color:var(--card-accent);">{{ $myReviews->total() }}</div>
                <small style="color:var(--text-muted);">Reviews Given</small>
            </div>
        </div>
    </div>

    @if($available->count())
    <div class="glass-card mb-4">
        <h5 style="color:var(--text-primary);font-weight:700;margin-bottom:1rem;">
            <i class="bi bi-pencil-square me-2" style="color:var(--card-accent);"></i> Questions Needing Review
        </h5>
        <div class="d-flex flex-column gap-2">
            @foreach($available as $item)
            <div class="d-flex align-items-center gap-2 py-2 px-3" style="background:rgba(255,255,255,0.3);border-radius:1rem;">
                <span class="stat-badge" style="background:#eef2ff;color:var(--card-accent);font-size:0.6rem;white-space:nowrap;">
                    {{ ucfirst(str_replace('_', ' ', $item['type'])) }}
                </span>
                <div style="flex:1;min-width:0;">
                    <div style="font-size:0.85rem;color:var(--text-primary);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                        {{ Str::limit(strip_tags(questionText($item['item'])), 80) }}
                    </div>
                    <div class="d-flex gap-2">
                        <small style="color:var(--text-muted);">
                            <i class="bi bi-person-circle" style="font-size:0.65rem;"></i> {{ $item['item']->user?->name ?? 'Unknown' }}
                        </small>
                        @if($item['item']->subject)
                        <small style="color:var(--card-accent);">{{ $item['item']->subject->name }}</small>
                        @endif
                    </div>
                </div>
                <button class="dark-btn py-1 px-2" style="font-size:0.7rem;white-space:nowrap;"
                        onclick="openReview('{{ $item['type'] }}', {{ $item['item']->id }}, {{ json_encode(Str::limit(strip_tags(questionText($item['item'])), 120)) }})">
                    <i class="bi bi-star"></i> Review
                </button>
            </div>
            @endforeach
        </div>
    </div>
    @else
    <div class="glass-card mb-4 text-center py-5">
        <i class="bi bi-check-circle" style="font-size:2.5rem;color:#059669;"></i>
        <p class="mt-2" style="color:var(--text-secondary);font-weight:500;">All caught up!</p>
        <p style="color:var(--text-muted);font-size:0.85rem;">No questions available for review right now. Check back later!</p>
    </div>
    @endif

    <div class="row g-4">
        <div class="col-md-6">
            <div class="glass-card h-100">
                <h5 style="color:var(--text-primary);font-weight:700;margin-bottom:1rem;">
                    <i class="bi bi-send me-2" style="color:var(--card-accent);"></i> My Reviews ({{ $myReviews->total() }})
                </h5>
                @if($myReviews->count())
                <div class="d-flex flex-column gap-2">
                    @foreach($myReviews as $review)
                    <div class="py-2 px-3" style="background:rgba(255,255,255,0.3);border-radius:1rem;">
                        <div class="d-flex justify-content-between align-items-start">
                            <div style="flex:1;min-width:0;font-size:0.85rem;color:var(--text-primary);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                @if($review->reviewable)
                                    {{ Str::limit(strip_tags(questionText($review->reviewable)), 60) }}
                                @else
                                    <span style="color:var(--text-muted);">[Deleted]</span>
                                @endif
                            </div>
                            <small style="color:var(--text-muted);font-size:0.65rem;white-space:nowrap;margin-left:0.5rem;">
                                {{ $review->created_at->diffForHumans() }}
                            </small>
                        </div>
                        <div class="d-flex align-items-center gap-2 mt-1">
                            @for($i = 1; $i <= 5; $i++)
                                <i class="bi bi-star{{ $i <= $review->rating ? '-fill' : '' }}" style="color:{{ $i <= $review->rating ? '#f59e0b' : '#d1d5db' }};font-size:0.6rem;"></i>
                            @endfor
                            @if($review->comment)
                            <span style="color:var(--text-secondary);font-size:0.75rem;font-style:italic;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                &mdash; "{{ $review->comment }}"
                            </span>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
                @if($myReviews->hasPages())
                <div class="mt-3">
                    {{ $myReviews->links('pagination::bootstrap-5') }}
                </div>
                @endif
                @else
                <p style="color:var(--text-muted);text-align:center;padding:2rem;">You haven't reviewed any questions yet.</p>
                @endif
            </div>
        </div>

        <div class="col-md-6">
            <div class="glass-card h-100">
                <h5 style="color:var(--text-primary);font-weight:700;margin-bottom:1rem;">
                    <i class="bi bi-inbox me-2" style="color:var(--card-accent);"></i> Reviews on Your Questions
                    @if($receivedReviews->count())
                    <span class="stat-badge up ms-2" style="font-size:0.6rem;">{{ $receivedReviews->count() }}</span>
                    @endif
                </h5>
                @if($receivedReviews->count())
                <div class="d-flex flex-column gap-2">
                    @foreach($receivedReviews as $review)
                    <div class="py-2 px-3" style="background:rgba(255,255,255,0.3);border-radius:1rem;">
                        <div class="d-flex justify-content-between align-items-start">
                            <div style="flex:1;min-width:0;">
                                <div style="font-size:0.85rem;color:var(--text-primary);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                    @if($review->reviewable)
                                        {{ Str::limit(strip_tags(questionText($review->reviewable)), 60) }}
                                    @else
                                        <span style="color:var(--text-muted);">[Deleted]</span>
                                    @endif
                                </div>
                                <div class="d-flex align-items-center gap-2 mt-1">
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="bi bi-star{{ $i <= $review->rating ? '-fill' : '' }}" style="color:{{ $i <= $review->rating ? '#f59e0b' : '#d1d5db' }};font-size:0.55rem;"></i>
                                    @endfor
                                    <small style="color:var(--text-muted);font-size:0.7rem;">
                                        by <strong>{{ $review->reviewer?->name ?? 'Unknown' }}</strong>
                                    </small>
                                </div>
                            </div>
                            <small style="color:var(--text-muted);font-size:0.65rem;white-space:nowrap;margin-left:0.5rem;">
                                {{ $review->created_at->diffForHumans() }}
                            </small>
                        </div>
                        @if($review->comment)
                        <p style="color:var(--text-secondary);font-size:0.78rem;margin:0.3rem 0 0;font-style:italic;background:rgba(255,255,255,0.3);padding:0.4rem 0.7rem;border-radius:0.5rem;">
                            "{{ $review->comment }}"
                        </p>
                        @endif
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-4" style="color:var(--text-muted);font-size:0.85rem;">
                    <i class="bi bi-inbox" style="font-size:1.5rem;display:block;margin-bottom:0.3rem;"></i>
                    No one has reviewed your questions yet. Share questions with your study group to get reviews!
                </div>
                @endif
                @if($receivedReviews->count() === 0)
                <div style="text-align:center;margin-top:0.5rem;">
                    <a href="{{ route('shared-questions.index') }}" class="btn-soft py-1 px-2" style="font-size:0.75rem;">
                        <i class="bi bi-unlock"></i> Manage Shared Questions
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>
    @else
    @include('Backend.partials.group-required')
    @endif
</div>

<!-- Review Modal -->
<div class="modal" id="reviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content border-0 shadow rounded-4">
            <form id="reviewForm">
                @csrf
                <input type="hidden" name="reviewable_type" id="reviewType">
                <input type="hidden" name="reviewable_id" id="reviewId">
                <div class="modal-header border-0" style="padding:1.5rem 1.5rem 0;">
                    <h5 class="modal-title" style="color:var(--text-primary);font-weight:800;">
                        <i class="bi bi-star me-2" style="color:var(--card-accent);"></i> Review Question
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" style="padding:1.5rem;">
                    <div class="mb-4 p-3" style="background:#f8fafc;border-radius:1rem;border:1px solid #e5e7eb;">
                        <label style="font-size:0.65rem;font-weight:600;text-transform:uppercase;letter-spacing:0.05em;color:var(--card-accent);margin-bottom:0.3rem;display:block;">Question</label>
                        <p id="reviewQuestionPreview" style="color:var(--text-primary);font-size:0.9rem;margin:0;"></p>
                    </div>
                    <div class="mb-4">
                        <label style="font-size:0.7rem;font-weight:600;text-transform:uppercase;letter-spacing:0.05em;color:var(--card-accent);margin-bottom:0.4rem;display:block;">Rating</label>
                        <div class="d-flex gap-2" id="starRating">
                            @for($i = 1; $i <= 5; $i++)
                            <label style="cursor:pointer;transition:transform 0.15s;" onmouseover="this.style.transform='scale(1.2)'" onmouseout="this.style.transform='scale(1)'">
                                <input type="radio" name="rating" value="{{ $i }}" style="display:none;">
                                <i class="bi bi-star" style="font-size:1.8rem;color:#d1d5db;transition:color 0.15s;" data-star="{{ $i }}"></i>
                            </label>
                            @endfor
                        </div>
                    </div>
                    <div class="mb-3">
                        <label style="font-size:0.7rem;font-weight:600;text-transform:uppercase;letter-spacing:0.05em;color:var(--card-accent);margin-bottom:0.4rem;display:block;">Comment (optional)</label>
                        <textarea name="comment" class="form-control" rows="3" placeholder="What did you think? Any suggestions?"
                                  style="background:white;border:1.5px solid #e5e7eb;border-radius:1rem;padding:0.7rem 1.1rem;font-size:0.9rem;width:100%;font-family:'Inter',sans-serif;resize:vertical;min-height:100px;"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0" style="padding:0 1.5rem 1.5rem;">
                    <button type="button" class="btn-soft" data-bs-dismiss="modal"><i class="bi bi-x"></i> Cancel</button>
                    <button type="submit" class="dark-btn" id="reviewSubmit"><i class="bi bi-send"></i> Submit (+5 XP)</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    let modalEl = document.getElementById('reviewModal');
    if (!modalEl) return;
    let reviewModal = new bootstrap.Modal(modalEl);

    function ensureModalInRoot() {
        let root = document.getElementById('modal-root');
        if (root && modalEl.parentNode !== root) root.appendChild(modalEl);
    }

    function resetStars() {
        document.querySelectorAll('#starRating input').forEach(r => r.checked = false);
        document.querySelectorAll('#starRating i').forEach(el => {
            el.className = 'bi bi-star';
            el.style.color = '#d1d5db';
        });
    }

    window.openReview = function (type, id, question) {
        const modelMap = {
            'mcqs': 'Mcq',
            'true_false': 'TrueFalseQuestion',
            'short_answers': 'ShortAnswer',
            'fill_blanks': 'FillBlank',
            'matching': 'MatchingQuestion',
            'flashcards': 'Flashcard',
        };
        document.getElementById('reviewType').value = 'App\\Models\\' + (modelMap[type] || 'Mcq');
        document.getElementById('reviewId').value = id;
        document.getElementById('reviewQuestionPreview').textContent = question;
        document.getElementById('reviewForm').reset();
        ensureModalInRoot();
        resetStars();
        reviewModal.show();
    };

    document.querySelectorAll('#starRating label').forEach(function (label) {
        label.addEventListener('click', function () {
            let input = this.querySelector('input');
            if (!input) return;
            let val = parseInt(input.value);
            document.querySelectorAll('#starRating i').forEach(function (el) {
                let star = parseInt(el.dataset.star);
                el.className = star <= val ? 'bi bi-star-fill' : 'bi bi-star';
                el.style.color = star <= val ? '#f59e0b' : '#d1d5db';
            });
        });
    });

    document.getElementById('reviewForm')?.addEventListener('submit', function (e) {
        e.preventDefault();
        const btn = document.getElementById('reviewSubmit');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span> Submitting...';
        fetch('{{ route("peer-reviews.store") }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'X-Requested-With': 'XMLHttpRequest' },
            body: new FormData(this)
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showToast(data.message, 'success');
                reviewModal.hide();
                location.reload();
            } else {
                showToast(data.message || 'Error submitting review', 'error');
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-send"></i> Submit (+5 XP)';
            }
        })
        .catch(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-send"></i> Submit (+5 XP)';
            showToast('Something went wrong. Please try again.', 'error');
        });
    });
});
</script>
@endpush
