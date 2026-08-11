@extends('Backend.master')

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
    $currentUserRole = $studyGroup->members->firstWhere('user_id', auth()->id())?->role;
@endphp

@section('content')
<div class="container" style="max-width:800px;">
    <div class="glass-card p-4">
        <a href="{{ route('study-groups.index') }}" class="btn-soft mb-3" style="font-size:0.8rem;">
            <i class="bi bi-arrow-left"></i> Back to Groups
        </a>

        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div class="flex-grow-1" style="min-width:0;">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <h2 style="color:var(--text-primary);font-weight:800;font-size:1.3rem;margin:0;" id="groupName">{{ $studyGroup->name }}</h2>
                    @if($currentUserRole === 'admin')
                    <button class="btn-soft py-1 px-2" style="font-size:0.65rem;" id="editGroupBtn" title="Edit group">
                        <i class="bi bi-pencil"></i>
                    </button>
                    @endif
                </div>
                <p style="color:var(--text-secondary);" id="groupDescription">{{ $studyGroup->description ?? 'No description' }}</p>
                <small style="color:var(--text-muted);">
                    <i class="bi bi-person-circle me-1"></i> Created by {{ $studyGroup->creator?->name ?? 'Unknown' }}
                    &middot; {{ $studyGroup->created_at->format('M d, Y') }}
                </small>
            </div>
            <div class="d-flex gap-2">
                @if($currentUserRole === 'admin')
                <form action="{{ route('study-groups.destroy', $studyGroup) }}" method="POST" onsubmit="return confirm('Delete this group permanently?')">
                    @csrf @method('DELETE')
                    <button class="btn-soft danger py-1 px-2" style="font-size:0.7rem;">
                        <i class="bi bi-trash"></i> Delete
                    </button>
                </form>
                @endif
                <form action="{{ route('study-groups.leave', $studyGroup) }}" method="POST" onsubmit="return confirm('Leave this group?')">
                    @csrf
                    <button class="btn-soft py-1 px-2" style="font-size:0.7rem;color:#dc2626;">
                        <i class="bi bi-box-arrow-right"></i> Leave
                    </button>
                </form>
            </div>
        </div>

        <hr style="border-color:var(--divider-color);margin:1.25rem 0;">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 style="color:var(--card-accent);font-weight:700;font-size:0.9rem;margin:0;">
                <i class="bi bi-people me-1"></i> Members ({{ $studyGroup->members->count() }})
            </h5>
        </div>
        <div class="d-flex flex-column gap-2 mb-4" id="membersList">
            @foreach($studyGroup->members as $member)
            <div class="d-flex align-items-center gap-3 py-2 px-3 member-row" style="background:rgba(255,255,255,0.4);border-radius:1rem;" data-member-id="{{ $member->id }}">
                <div style="width:38px;height:38px;background:linear-gradient(135deg,{{ $member->role === 'admin' ? '#f59e0b,#ef4444' : '#6366f1,#a855f7' }});border-radius:50%;display:flex;align-items:center;justify-content:center;color:white;font-weight:600;font-size:0.85rem;flex-shrink:0;">
                    {{ substr($member->user->name, 0, 1) }}
                </div>
                <div class="flex-grow-1" style="min-width:0;">
                    <div style="font-weight:600;font-size:0.88rem;color:var(--text-primary);">
                        {{ $member->user->name }}
                        @if($member->user_id === $studyGroup->created_by)
                        <span class="stat-badge" style="background:#fef3c7;color:#d97706;font-size:0.55rem;vertical-align:middle;">Owner</span>
                        @endif
                    </div>
                    <small style="color:var(--text-secondary);">
                        <span class="member-role-badge">{{ $member->role }}</span>
                    </small>
                </div>
                @if($currentUserRole === 'admin' && $member->user_id !== auth()->id())
                <div class="d-flex gap-1">
                    <button class="btn-soft py-1 px-2" style="font-size:0.65rem;"
                            onclick="toggleRole({{ $member->id }}, '{{ $member->role }}', this)"
                            title="Toggle admin/member role">
                        <i class="bi bi-shield-{{ $member->role === 'admin' ? 'exclamation' : 'check' }}"></i>
                    </button>
                    <button class="btn-soft danger py-1 px-2" style="font-size:0.65rem;"
                            onclick="removeMember({{ $member->id }}, this)" title="Remove member">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
                @endif
            </div>
            @endforeach
        </div>

        <hr style="border-color:var(--divider-color);margin:1.25rem 0;">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 style="color:var(--card-accent);font-weight:700;font-size:0.9rem;margin:0;">
                <i class="bi bi-share me-1"></i> Shared Resources ({{ $studyGroup->resources->count() }})
            </h5>
            <button class="dark-btn py-1 px-2" style="font-size:0.7rem;" id="shareQuestionBtn">
                <i class="bi bi-share"></i> Share Question
            </button>
        </div>

        <div id="resourcesList">
            @if($studyGroup->resources->count())
            <div class="d-flex flex-column gap-2">
                @foreach($studyGroup->resources as $resource)
                <div class="d-flex align-items-center gap-3 py-2 px-3 resource-row" style="background:rgba(255,255,255,0.4);border-radius:1rem;" data-resource-id="{{ $resource->id }}">
                    <div style="width:36px;height:36px;background:linear-gradient(135deg,#f59e0b,#ef4444);border-radius:50%;display:flex;align-items:center;justify-content:center;color:white;font-size:1rem;flex-shrink:0;">
                        <i class="bi {{ $typeIcons[$resource->resourceable_type] ?? 'bi-question-circle' }}"></i>
                    </div>
                    <div class="flex-grow-1" style="min-width:0;">
                        <div style="font-weight:600;font-size:0.88rem;color:var(--text-primary);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                            {{ $resource->resourceable?->question ?? $resource->resourceable?->statement ?? $resource->resourceable?->front ?? $resource->resourceable?->sentence_with_blanks ?? 'Resource' }}
                        </div>
                        <div class="d-flex gap-2">
                            <small style="color:var(--card-accent);">{{ $typeLabels[$resource->resourceable_type] ?? 'Question' }}</small>
                            <small style="color:var(--text-muted);">by {{ $resource->user->name }}</small>
                        </div>
                    </div>
                    @php $canUnshare = $resource->user_id === auth()->id() || $currentUserRole === 'admin'; @endphp
                    @if($canUnshare)
                    <div class="d-flex gap-1">
                        <button class="btn-soft danger py-1 px-2" style="font-size:0.65rem;"
                                onclick="unshareResource({{ $resource->id }}, this)" title="Remove">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
            @else
            <div class="text-center py-5" style="color:var(--text-muted);font-size:0.85rem;">
                <i class="bi bi-inbox" style="font-size:2rem;display:block;margin-bottom:0.5rem;"></i>
                No resources shared yet. Share your questions with the group!
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Share Question Modal -->
<div class="modal" id="shareModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content border-0 shadow rounded-4">
            <form id="shareForm">
                @csrf
                <div class="modal-header border-0" style="padding:1.5rem 1.5rem 0;">
                    <h5 class="modal-title" style="color:var(--text-primary);font-weight:800;">
                        <i class="bi bi-share-fill me-2" style="color:var(--card-accent);"></i> Share a Question
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" style="padding:1.5rem;">
                    <div class="mb-4 p-3" style="background:#f8fafc;border-radius:1rem;border:1px solid #e5e7eb;">
                        <label style="font-size:0.65rem;font-weight:600;text-transform:uppercase;letter-spacing:0.05em;color:var(--card-accent);margin-bottom:0.3rem;display:block;">Sharing into</label>
                        <p style="color:var(--text-primary);font-size:0.9rem;font-weight:700;margin:0;">
                            <i class="bi bi-people-fill me-1" style="color:var(--card-accent);"></i> {{ $studyGroup->name }}
                        </p>
                    </div>
                    <div class="mb-4">
                        <label style="font-size:0.7rem;font-weight:600;text-transform:uppercase;letter-spacing:0.05em;color:var(--card-accent);margin-bottom:0.4rem;display:block;">Question Type</label>
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
                        <label style="font-size:0.7rem;font-weight:600;text-transform:uppercase;letter-spacing:0.05em;color:var(--card-accent);margin-bottom:0.4rem;display:block;">Question</label>
                        <select name="id" id="questionId" class="form-control" required
                                style="background:white;border:1.5px solid #e5e7eb;border-radius:1rem;padding:0.7rem 1.1rem;font-size:0.9rem;width:100%;font-family:'Inter',sans-serif;">
                            <option value="">Select type first...</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0" style="padding:0 1.5rem 1.5rem;">
                    <button type="button" class="btn-soft" data-bs-dismiss="modal"><i class="bi bi-x"></i> Cancel</button>
                    <button type="submit" class="dark-btn" id="shareSubmit"><i class="bi bi-share"></i> Share</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Group Modal -->
<div class="modal" id="editGroupModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content border-0 shadow rounded-4">
            <form id="editGroupForm">
                @csrf @method('PUT')
                <div class="modal-header border-0" style="padding:1.5rem 1.5rem 0;">
                    <h5 class="modal-title" style="color:var(--text-primary);font-weight:800;">
                        <i class="bi bi-pencil-fill me-2" style="color:var(--card-accent);"></i> Edit Group
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" style="padding:1.5rem;">
                    <div class="mb-4">
                        <label style="font-size:0.7rem;font-weight:600;text-transform:uppercase;letter-spacing:0.05em;color:var(--card-accent);margin-bottom:0.4rem;display:block;">Group Name <span style="color:#dc2626;">*</span></label>
                        <input type="text" name="name" class="form-control" required value="{{ $studyGroup->name }}"
                               style="background:white;border:1.5px solid #e5e7eb;border-radius:1rem;padding:0.7rem 1.1rem;font-size:0.9rem;width:100%;font-family:'Inter',sans-serif;">
                    </div>
                    <div class="mb-3">
                        <label style="font-size:0.7rem;font-weight:600;text-transform:uppercase;letter-spacing:0.05em;color:var(--card-accent);margin-bottom:0.4rem;display:block;">Description</label>
                        <textarea name="description" class="form-control" rows="3"
                                  style="background:white;border:1.5px solid #e5e7eb;border-radius:1rem;padding:0.7rem 1.1rem;font-size:0.9rem;width:100%;font-family:'Inter',sans-serif;resize:vertical;min-height:100px;">{{ $studyGroup->description }}</textarea>
                    </div>
                </div>
                <div class="modal-footer border-0" style="padding:0 1.5rem 1.5rem;">
                    <button type="button" class="btn-soft" data-bs-dismiss="modal"><i class="bi bi-x"></i> Cancel</button>
                    <button type="submit" class="dark-btn" id="editGroupSubmit"><i class="bi bi-save"></i> Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Share modal
        let shareModalEl = document.getElementById('shareModal');
        let shareModal = null;
        if (shareModalEl) {
            shareModal = new bootstrap.Modal(shareModalEl);
        }

        // Edit modal
        let editModalEl = document.getElementById('editGroupModal');
        if (editModalEl) {
            let editModal = new bootstrap.Modal(editModalEl);
            document.getElementById('editGroupBtn')?.addEventListener('click', function () {
                let root = document.getElementById('modal-root');
                if (root && editModalEl.parentNode !== root) root.appendChild(editModalEl);
                editModal.show();
            });

            document.getElementById('editGroupForm')?.addEventListener('submit', function (e) {
                e.preventDefault();
                const btn = document.getElementById('editGroupSubmit');
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span> Saving...';
                fetch('{{ route("study-groups.update", $studyGroup) }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'X-Requested-With': 'XMLHttpRequest' },
                    body: new FormData(this)
                })
                .then(r => r.json().then(data => ({ ok: r.ok, data })))
                .then(({ ok, data }) => {
                    if (ok && data.success) {
                        showToast(data.message, 'success');
                        document.getElementById('groupName').textContent = document.querySelector('#editGroupForm input[name="name"]').value;
                        document.getElementById('groupDescription').textContent = document.querySelector('#editGroupForm textarea[name="description"]').value || 'No description';
                        editModal.hide();
                    } else {
                        showToast(data.message || 'Could not update group', 'error');
                        btn.disabled = false;
                        btn.innerHTML = '<i class="bi bi-save"></i> Save';
                    }
                })
                .catch(() => {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-save"></i> Save';
                    showToast('Something went wrong. Please try again.', 'error');
                });
            });
        }

        // Share form - question type selector
        let questions = @json($questions);
        let hasAnyQuestion = Object.values(questions).some(arr => arr.length > 0);
        let typeSelect = document.getElementById('questionType');
        let questionSelect = document.getElementById('questionId');
        if (typeSelect && questionSelect) {
            typeSelect.addEventListener('change', function () {
                let type = this.value;
                questionSelect.innerHTML = '';
                if (!type) {
                    questionSelect.innerHTML = '<option value="">Select type first...</option>';
                    return;
                }
                if (questions[type] && questions[type].length) {
                    let placeholder = document.createElement('option');
                    placeholder.value = '';
                    placeholder.textContent = 'Select question...';
                    placeholder.disabled = true;
                    placeholder.selected = true;
                    questionSelect.appendChild(placeholder);
                    questions[type].forEach(function (q) {
                        let opt = document.createElement('option');
                        opt.value = q.id;
                        opt.textContent = '[' + q.subject + '] ' + q.label;
                        questionSelect.appendChild(opt);
                    });
                } else {
                    let opt = document.createElement('option');
                    opt.value = '';
                    opt.textContent = 'No ' + type.replace('_', ' ') + ' questions yet. Generate some first!';
                    opt.disabled = true;
                    questionSelect.appendChild(opt);
                }
            });
        }

        // Share button: warn when there is nothing to share
        document.getElementById('shareQuestionBtn')?.addEventListener('click', function (e) {
            if (!hasAnyQuestion) {
                e.preventDefault();
                showToast('You have no questions to share yet. Generate some questions first!', 'warning');
                return;
            }
            let root = document.getElementById('modal-root');
            if (root && shareModalEl.parentNode !== root) root.appendChild(shareModalEl);
            shareModal.show();
        });

        // Share form - submit via AJAX
        document.getElementById('shareForm')?.addEventListener('submit', function (e) {
            e.preventDefault();
            if (!this.checkValidity()) {
                this.classList.add('was-validated');
                return;
            }
            const btn = document.getElementById('shareSubmit');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span> Sharing...';
            fetch('{{ route("study-groups.share", $studyGroup) }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'X-Requested-With': 'XMLHttpRequest' },
                body: new FormData(this)
            })
            .then(r => r.json().then(data => ({ ok: r.ok, data })))
            .then(({ ok, data }) => {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-share"></i> Share';
                if (ok && data.success) {
                    showToast(data.message, 'success');
                    shareModal.hide();
                    location.reload();
                } else {
                    showToast(data.message || 'Could not share question.', 'error');
                }
            })
            .catch(() => {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-share"></i> Share';
                showToast('Something went wrong. Please try again.', 'error');
            });
        });
    });

    function toggleRole(memberId, currentRole, btn) {
        const newRole = currentRole === 'admin' ? 'member' : 'admin';
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span>';
        fetch('{{ url("study-groups") }}/{{ $studyGroup->id }}/members/' + memberId + '/role', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/json' },
            body: JSON.stringify({ role: newRole })
        })
        .then(r => r.json().then(data => ({ ok: r.ok, data })))
        .then(({ ok, data }) => {
            if (ok && data.success) {
                showToast(data.message, 'success');
                location.reload();
            } else {
                showToast(data.message || 'Error updating role', 'error');
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-shield-' + (currentRole === 'admin' ? 'exclamation' : 'check') + '"></i>';
            }
        })
        .catch(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-shield-' + (currentRole === 'admin' ? 'exclamation' : 'check') + '"></i>';
            showToast('Something went wrong. Please try again.', 'error');
        });
    }

    function removeMember(memberId, btn) {
        if (!confirm('Remove this member from the group?')) return;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span>';
        fetch('{{ url("study-groups") }}/{{ $studyGroup->id }}/members/' + memberId + '/remove', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json().then(data => ({ ok: r.ok, data })))
        .then(({ ok, data }) => {
            if (ok && data.success) {
                showToast(data.message, 'success');
                const row = btn.closest('.member-row');
                if (row) row.remove();
            } else {
                showToast(data.message || 'Error removing member', 'error');
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-x"></i>';
            }
        })
        .catch(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-x"></i>';
            showToast('Something went wrong. Please try again.', 'error');
        });
    }

    function unshareResource(resourceId, btn) {
        if (!confirm('Remove this resource from the group?')) return;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span>';
        fetch('{{ url("study-groups") }}/{{ $studyGroup->id }}/share/' + resourceId, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json().then(data => ({ ok: r.ok, data })))
        .then(({ ok, data }) => {
            if (ok && data.success) {
                showToast(data.message, 'success');
                const row = btn.closest('.resource-row');
                if (row) row.remove();
                const list = document.getElementById('resourcesList');
                if (list && !list.querySelector('.resource-row')) {
                    list.innerHTML = '<div class="text-center py-5" style="color:var(--text-muted);font-size:0.85rem;"><i class="bi bi-inbox" style="font-size:2rem;display:block;margin-bottom:0.5rem;"></i>No resources shared yet.</div>';
                }
            } else {
                showToast(data.message || 'Could not remove resource', 'error');
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-x"></i>';
            }
        })
        .catch(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-x"></i>';
            showToast('Something went wrong. Please try again.', 'error');
        });
    }
</script>
@endpush
