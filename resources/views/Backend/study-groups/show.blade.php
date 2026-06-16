@extends('Backend.master')

@section('content')
<div class="container" style="max-width:700px;">
    @if(session('success'))
        <div class="glass-card mb-4 d-flex align-items-center gap-3 py-3" style="border-left:4px solid #059669;">
            <i class="bi bi-check-circle-fill" style="color:#059669;font-size:1.2rem;"></i>
            <span style="color:#1e1b4b;font-size:0.9rem;">{{ session('success') }}</span>
        </div>
    @endif

    <div class="glass-card p-4">
        <a href="{{ route('study-groups.index') }}" class="btn-soft mb-3" style="font-size:0.8rem;"><i class="bi bi-arrow-left"></i> Back</a>
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <h2 style="color:#1e1b4b;font-weight:800;font-size:1.3rem;">{{ $studyGroup->name }}</h2>
                <p style="color:#6b7280;">{{ $studyGroup->description ?? 'No description' }}</p>
                <small style="color:#9ca3af;">Created by {{ $studyGroup->creator?->name ?? 'Unknown' }}</small>
            </div>
            <form action="{{ route('study-groups.leave', $studyGroup) }}" method="POST">
                @csrf
                <button class="btn-soft py-1 px-2" style="font-size:0.7rem;color:#dc2626;" onclick="return confirm('Leave this group?')"><i class="bi bi-box-arrow-right"></i> Leave</button>
            </form>
        </div>

        <h5 style="color:#6366f1;font-weight:700;font-size:0.9rem;margin-top:1.5rem;">Members ({{ $studyGroup->members->count() }})</h5>
        <div class="d-flex flex-column gap-2 mt-2">
            @foreach($studyGroup->members as $member)
            <div class="d-flex align-items-center gap-3 py-2 px-3" style="background:rgba(255,255,255,0.4);border-radius:1rem;">
                <div style="width:36px;height:36px;background:linear-gradient(135deg,#6366f1,#a855f7);border-radius:50%;display:flex;align-items:center;justify-content:center;color:white;font-weight:600;font-size:0.85rem;">
                    {{ substr($member->user->name, 0, 1) }}
                </div>
                <div>
                    <div style="font-weight:600;font-size:0.88rem;color:#1e1b4b;">{{ $member->user->name }}</div>
                    <small style="color:#6b7280;">{{ $member->role }}</small>
                </div>
            </div>
            @endforeach
        </div>

        @php
            $typeLabels = [
                'App\Models\Mcq' => 'MCQ',
                'App\Models\TrueFalseQuestion' => 'True/False',
                'App\Models\ShortAnswer' => 'Short Answer',
                'App\Models\FillBlank' => 'Fill in Blank',
                'App\Models\MatchingQuestion' => 'Matching',
                'App\Models\Flashcard' => 'Flashcard',
            ];
            $typeIcons = [
                'App\Models\Mcq' => 'bi-list-check',
                'App\Models\TrueFalseQuestion' => 'bi-toggle-on',
                'App\Models\ShortAnswer' => 'bi-pencil-square',
                'App\Models\FillBlank' => 'bi-input-cursor-text',
                'App\Models\MatchingQuestion' => 'bi-diagram-3',
                'App\Models\Flashcard' => 'bi-card-text',
            ];
        @endphp

        <div class="d-flex justify-content-between align-items-center mt-4 mb-2">
            <h5 style="color:#6366f1;font-weight:700;font-size:0.9rem;margin:0;">Shared Resources ({{ $studyGroup->resources->count() }})</h5>
            <button class="dark-btn py-1 px-2" style="font-size:0.7rem;" id="shareQuestionBtn"><i class="bi bi-share"></i> Share Question</button>
        </div>

        @if($studyGroup->resources->count())
        <div class="d-flex flex-column gap-2 mt-2">
            @foreach($studyGroup->resources as $resource)
            <div class="d-flex align-items-center gap-3 py-2 px-3" style="background:rgba(255,255,255,0.4);border-radius:1rem;">
                <div style="width:36px;height:36px;background:linear-gradient(135deg,#f59e0b,#ef4444);border-radius:50%;display:flex;align-items:center;justify-content:center;color:white;font-size:1rem;">
                    <i class="bi {{ $typeIcons[$resource->resourceable_type] ?? 'bi-question-circle' }}"></i>
                </div>
                <div class="flex-grow-1" style="min-width:0;">
                    <div style="font-weight:600;font-size:0.88rem;color:#1e1b4b;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                        {{ $resource->resourceable?->question ?? $resource->resourceable?->statement ?? $resource->resourceable?->front ?? $resource->resourceable?->sentence_with_blanks ?? 'Resource' }}
                    </div>
                    <div class="d-flex gap-2">
                        <small style="color:#6366f1;">{{ $typeLabels[$resource->resourceable_type] ?? 'Question' }}</small>
                        <small style="color:#9ca3af;">by {{ $resource->user->name }}</small>
                    </div>
                </div>
                @php
                    $canUnshare = $resource->user_id === auth()->id() || $studyGroup->members->firstWhere('user_id', auth()->id())?->role === 'admin';
                @endphp
                @if($canUnshare)
                <form action="{{ route('study-groups.unshare', [$studyGroup, $resource]) }}" method="POST">
                    @csrf @method('DELETE')
                    <button class="btn-soft danger py-1 px-2" style="font-size:0.7rem;" onclick="return confirm('Remove this resource?')"><i class="bi bi-x"></i></button>
                </form>
                @endif
            </div>
            @endforeach
        </div>
        @else
        <div class="text-center py-4" style="color:#9ca3af;font-size:0.85rem;">
            <i class="bi bi-inbox" style="font-size:1.5rem;display:block;margin-bottom:0.3rem;"></i>
            No resources shared yet. Share your questions with the group!
        </div>
        @endif
    </div>
</div>

<div class="modal" id="shareModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content border-0 shadow rounded-4">
            <form action="{{ route('study-groups.share', $studyGroup) }}" method="POST">
                @csrf
                <div class="modal-header border-0" style="padding:1.5rem 1.5rem 0;">
                    <h5 class="modal-title" style="color:#1e1b4b;font-weight:800;">
                        <i class="bi bi-share-fill me-2" style="color:#6366f1;"></i> Share a Question
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" style="padding:1.5rem;">
                    <div class="mb-4">
                        <label style="font-size:0.7rem;font-weight:600;text-transform:uppercase;letter-spacing:0.05em;color:#6366f1;margin-bottom:0.4rem;display:block;">Question Type</label>
                        <select name="type" id="questionType" class="form-control" required
                                style="background:white;border:1.5px solid #e5e7eb;border-radius:1rem;padding:0.7rem 1.1rem;font-size:0.9rem;width:100%;font-family:'Inter',sans-serif;">
                            <option value="">Select type...</option>
                            <option value="mcqs">MCQ</option>
                            <option value="true_false">True / False</option>
                            <option value="short_answers">Short Answer</option>
                            <option value="fill_blanks">Fill in Blank</option>
                            <option value="matching">Matching</option>
                            <option value="flashcards">Flashcard</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label style="font-size:0.7rem;font-weight:600;text-transform:uppercase;letter-spacing:0.05em;color:#6366f1;margin-bottom:0.4rem;display:block;">Question</label>
                        <select name="id" id="questionId" class="form-control" required
                                style="background:white;border:1.5px solid #e5e7eb;border-radius:1rem;padding:0.7rem 1.1rem;font-size:0.9rem;width:100%;font-family:'Inter',sans-serif;">
                            <option value="">Select type first...</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0" style="padding:0 1.5rem 1.5rem;">
                    <button type="button" class="btn-soft" data-bs-dismiss="modal"><i class="bi bi-x"></i> Cancel</button>
                    <button type="submit" class="dark-btn"><i class="bi bi-share"></i> Share</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        let shareModalEl = document.getElementById('shareModal');
        if (!shareModalEl) return;
        let shareModal = new bootstrap.Modal(shareModalEl);

        document.getElementById('shareQuestionBtn')?.addEventListener('click', function () {
            let root = document.getElementById('modal-root');
            if (root && shareModalEl.parentNode !== root) root.appendChild(shareModalEl);
            shareModal.show();
        });

        let questions = @json($questions);
        let typeSelect = document.getElementById('questionType');
        let questionSelect = document.getElementById('questionId');
        if (typeSelect && questionSelect) {
            typeSelect.addEventListener('change', function () {
                let type = this.value;
                questionSelect.innerHTML = '<option value="">Select question...</option>';
                if (type && questions[type]) {
                    questions[type].forEach(function (q) {
                        let opt = document.createElement('option');
                        opt.value = q.id;
                        opt.textContent = '[' + q.subject + '] ' + q.label;
                        questionSelect.appendChild(opt);
                    });
                }
            });
        }
    });
</script>
@endpush
