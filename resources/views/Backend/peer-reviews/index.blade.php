@extends('Backend.master')

@section('content')
<div class="container-fluid px-0">
    <div class="page-header">
        <div>
            <h2>Peer Reviews</h2>
            <p>Review questions from other students and earn XP</p>
        </div>
    </div>

    @if(session('success'))
        <div class="glass-card mb-4" style="border-left:4px solid #059669;padding:0.75rem 1rem;">
            <span style="color:#059669;font-weight:500;">{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="glass-card mb-4" style="border-left:4px solid #dc2626;padding:0.75rem 1rem;">
            <span style="color:#dc2626;font-weight:500;">{{ session('error') }}</span>
        </div>
    @endif

    @php
        function questionText($item) {
            return $item->question ?? $item->statement ?? $item->front ?? $item->sentence_with_blanks ?? (is_array($item->left_items) ? ($item->left_items[0] ?? 'Matching question') : 'Question #'.$item->id);
        }
    @endphp

    @if($available->count())
    <div class="glass-card mb-4">
        <h5 style="color:var(--text-primary);font-weight:700;margin-bottom:1rem;">Questions Needing Review</h5>
        <div class="d-flex flex-column gap-2">
            @foreach($available as $item)
            <div class="d-flex align-items-center gap-2 py-2 px-3" style="background:rgba(255,255,255,0.3);border-radius:1rem;">
                <span class="stat-badge" style="background:#eef2ff;color:var(--card-accent);font-size:0.65rem;">{{ str_replace('_', ' ', $item['type']) }}</span>
                <div style="flex:1;font-size:0.85rem;color:var(--text-primary);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ Str::limit(strip_tags(questionText($item['item'])), 60) }}</div>
                <small style="color:var(--text-muted);">{{ $item['item']->user?->name ?? 'Unknown' }}</small>
                <button class="btn-soft py-1 px-2" style="font-size:0.7rem;" onclick="openReview('{{ $item['type'] }}', {{ $item['item']->id }}, '{{ addslashes(Str::limit(strip_tags(questionText($item['item'])), 100)) }}')">Review</button>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <div class="glass-card">
        <h5 style="color:var(--text-primary);font-weight:700;margin-bottom:1rem;">Past Reviews</h5>
        @if($reviews->count())
        <div class="table-responsive">
            <table class="glass-table">
                <thead><tr><th>Question</th><th>Rating</th><th>Comment</th><th>Date</th></tr></thead>
                <tbody>
                    @foreach($reviews as $review)
                    <tr>
                        <td style="font-size:0.85rem;max-width:250px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                            @if($review->reviewable)
                                {{ Str::limit(strip_tags(questionText($review->reviewable)), 60) }}
                            @else
                                <span style="color:var(--text-muted);">[Deleted]</span>
                            @endif
                        </td>
                        <td>
                            @for($i = 1; $i <= 5; $i++)
                                <i class="bi bi-star{{ $i <= $review->rating ? '-fill' : '' }}" style="color:{{ $i <= $review->rating ? '#f59e0b' : '#d1d5db' }};font-size:0.7rem;"></i>
                            @endfor
                        </td>
                        <td style="color:var(--text-secondary);font-size:0.85rem;">{{ $review->comment ?? '—' }}</td>
                        <td style="font-size:0.85rem;">{{ $review->created_at->format('M d, Y') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <p style="color:var(--text-muted);text-align:center;padding:2rem;">No reviews yet.</p>
        @endif
    </div>
</div>

@if($receivedReviews->count())
<div class="glass-card mt-4">
    <h5 style="color:var(--text-primary);font-weight:700;margin-bottom:1rem;"><i class="bi bi-inbox me-2" style="color:var(--card-accent);"></i> Reviews on Your Questions</h5>
    <div class="d-flex flex-column gap-2">
        @foreach($receivedReviews as $review)
        <div class="d-flex align-items-start gap-3 py-2 px-3" style="background:rgba(255,255,255,0.3);border-radius:1rem;">
            <div style="flex:1;min-width:0;">
                <div style="font-size:0.85rem;color:var(--text-primary);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                    @if($review->reviewable)
                        {{ Str::limit(strip_tags(questionText($review->reviewable)), 60) }}
                    @else
                        <span style="color:var(--text-muted);">[Deleted]</span>
                    @endif
                </div>
                <div class="d-flex gap-2 mt-1">
                    <small style="color:var(--card-accent);">
                        @for($i = 1; $i <= 5; $i++)
                            <i class="bi bi-star{{ $i <= $review->rating ? '-fill' : '' }}" style="color:{{ $i <= $review->rating ? '#f59e0b' : '#d1d5db' }};font-size:0.65rem;"></i>
                        @endfor
                    </small>
                    <small style="color:var(--text-muted);">by {{ $review->reviewer?->name ?? 'Unknown' }}</small>
                </div>
                @if($review->comment)
                <p style="color:var(--text-secondary);font-size:0.8rem;margin:0.2rem 0 0;font-style:italic;">"{{ $review->comment }}"</p>
                @endif
            </div>
            <small style="color:var(--text-muted);font-size:0.7rem;white-space:nowrap;">{{ $review->created_at->format('M d, Y') }}</small>
        </div>
        @endforeach
    </div>
</div>
@endif

<div class="modal" id="reviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content border-0 shadow rounded-4">
            <form action="{{ route('peer-reviews.store') }}" method="POST">
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
                    <p id="reviewQuestionPreview" style="color:var(--text-primary);font-size:0.9rem;padding:0.75rem;background:#f8fafc;border-radius:0.75rem;"></p>
                    <div class="mb-4">
                        <label style="font-size:0.7rem;font-weight:600;text-transform:uppercase;letter-spacing:0.05em;color:var(--card-accent);margin-bottom:0.4rem;display:block;">Rating</label>
                        <div class="d-flex gap-2" id="starRating">
                            @for($i = 1; $i <= 5; $i++)
                            <label style="cursor:pointer;">
                                <input type="radio" name="rating" value="{{ $i }}" style="display:none;">
                                <i class="bi bi-star" style="font-size:1.5rem;color:#d1d5db;transition:color 0.15s;" data-star="{{ $i }}"></i>
                            </label>
                            @endfor
                        </div>
                    </div>
                    <div class="mb-3">
                        <label style="font-size:0.7rem;font-weight:600;text-transform:uppercase;letter-spacing:0.05em;color:var(--card-accent);margin-bottom:0.4rem;display:block;">Comment (optional)</label>
                        <textarea name="comment" class="form-control" rows="3" placeholder="What do you think about this question?"
                                  style="background:white;border:1.5px solid #e5e7eb;border-radius:1rem;padding:0.7rem 1.1rem;font-size:0.9rem;width:100%;font-family:'Inter',sans-serif;resize:vertical;min-height:100px;"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0" style="padding:0 1.5rem 1.5rem;">
                    <button type="button" class="btn-soft" data-bs-dismiss="modal"><i class="bi bi-x"></i> Cancel</button>
                    <button type="submit" class="dark-btn"><i class="bi bi-send"></i> Submit (+5 XP)</button>
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
            'true_false': 'TrueFalse',
            'short_answers': 'ShortAnswer',
            'fill_blanks': 'FillBlank',
            'matching': 'MatchingQuestion',
            'flashcards': 'Flashcard',
        };
        document.getElementById('reviewType').value = 'App\\Models\\' + (modelMap[type] || 'Mcq');
        document.getElementById('reviewId').value = id;
        document.getElementById('reviewQuestionPreview').textContent = question;
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
});
</script>
@endpush
