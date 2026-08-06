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
    <div class="page-header">
        <div>
            <h2>Shared Question Banks</h2>
            <p>Browse and share questions with peers</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <form method="GET" class="d-flex gap-2 align-items-center flex-wrap">
                <select name="subject" class="form-control" style="width:auto;min-width:140px;background:white;border:1.5px solid #e5e7eb;border-radius:2rem;padding:0.4rem 1rem;font-size:0.8rem;font-family:'Inter',sans-serif;"
                        onchange="this.form.submit()">
                    <option value="">All Subjects</option>
                    @foreach($subjects as $subject)
                    <option value="{{ $subject->id }}" {{ $subjectId == $subject->id ? 'selected' : '' }}>{{ $subject->name }}</option>
                    @endforeach
                </select>
                <div class="d-flex gap-1">
                    <input type="text" name="search" placeholder="Search questions..." value="{{ $search }}"
                           style="background:white;border:1.5px solid #e5e7eb;border-radius:2rem;padding:0.4rem 1rem;font-size:0.8rem;width:200px;font-family:'Inter',sans-serif;">
                    <button type="submit" class="btn-soft py-1 px-2" style="font-size:0.7rem;"><i class="bi bi-search"></i></button>
                    @if($search || $subjectId)
                    <a href="{{ route('shared-questions.index') }}" class="btn-soft py-1 px-2" style="font-size:0.7rem;color:#dc2626;">
                        <i class="bi bi-x"></i> Clear
                    </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    @php $hasPublic = collect($shared)->flatten()->isNotEmpty(); @endphp

    @if($hasPublic)
    <div class="mb-4">
        <ul class="nav nav-pills mb-3" id="sharedTabs" role="tablist">
            @foreach($types as $i => $type)
            @php $items = $shared[$type] ?? collect(); @endphp
            <li class="nav-item" role="presentation">
                <button class="nav-link py-1 px-3 {{ $i === 0 ? 'active' : '' }}" style="font-size:0.78rem;border-radius:2rem;"
                        id="tab-{{ $type }}" data-bs-toggle="pill" data-bs-target="#pane-{{ $type }}" type="button" role="tab">
                    {{ ucfirst(str_replace('_', ' ', $type)) }}
                    @if($items->count())
                    <span class="stat-badge ms-1" style="font-size:0.55rem;background:var(--badge-bg);color:var(--card-accent);">{{ $items->count() }}</span>
                    @endif
                </button>
            </li>
            @endforeach
        </ul>
        <div class="tab-content">
            @foreach($types as $i => $type)
            @php $items = $shared[$type] ?? collect(); @endphp
            <div class="tab-pane fade {{ $i === 0 ? 'show active' : '' }}" id="pane-{{ $type }}" role="tabpanel">
                <div class="glass-card">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 style="color:var(--text-primary);font-weight:700;margin:0;font-size:0.95rem;">
                            <i class="bi bi-share me-2" style="color:var(--card-accent);"></i>
                            {{ ucfirst(str_replace('_', ' ', $type)) }}
                        </h5>
                        @if($items->count() >= 10)
                        <button class="btn-soft py-1 px-2 load-more-btn" style="font-size:0.65rem;" data-type="{{ $type }}" data-offset="{{ $items->count() }}">
                            <i class="bi bi-arrow-down"></i> Load More
                        </button>
                        @endif
                    </div>
                    @if($items->count())
                    <div class="d-flex flex-column gap-2 shared-items-container" data-type="{{ $type }}">
                        @foreach($items as $item)
                        <div class="d-flex align-items-center gap-3 py-2 px-3 shared-item" style="background:rgba(255,255,255,0.3);border-radius:1rem;">
                            <div style="flex:1;min-width:0;">
                                <div style="font-size:0.88rem;color:var(--text-primary);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                    {{ Str::limit(strip_tags(questionText($item)), 80) }}
                                </div>
                                <div class="d-flex gap-2">
                                    <small style="color:var(--text-muted);"><i class="bi bi-person-circle"></i> {{ $item->user?->name ?? 'Unknown' }}</small>
                                    <span class="stat-badge" style="background:#eef2ff;color:var(--card-accent);font-size:0.55rem;">{{ $item->subject?->name ?? 'General' }}</span>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="text-center py-4" style="color:var(--text-muted);font-size:0.85rem;">
                        <i class="bi bi-inbox" style="font-size:1.5rem;display:block;margin-bottom:0.3rem;"></i>
                        No public {{ str_replace('_', ' ', $type) }} questions found.
                    </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @else
    <div class="glass-card text-center py-5 mb-4">
        <i class="bi bi-inbox" style="font-size:3rem;color:#c7d2fe;"></i>
        <p class="mt-3" style="color:var(--text-secondary);">No public questions shared yet.</p>
        <p style="color:var(--text-muted);font-size:0.85rem;">Make your questions public from the section below to contribute!</p>
    </div>
    @endif

    <div class="glass-card">
        <h5 style="color:var(--text-primary);font-weight:700;margin-bottom:1rem;">
            <i class="bi bi-person me-2" style="color:var(--card-accent);"></i> Your Questions
            <small style="font-weight:400;color:var(--text-muted);font-size:0.75rem;">(toggle visibility to share with peers)</small>
        </h5>
        @php $hasOwn = false; @endphp
        @foreach($types as $type)
            @php $items = $myQuestions[$type] ?? collect(); @endphp
            @if($items->count())
                @php $hasOwn = true; @endphp
                <h6 style="color:var(--text-secondary);font-weight:600;font-size:0.8rem;margin:1rem 0 0.5rem;text-transform:uppercase;letter-spacing:0.05em;">
                    <i class="bi bi-dot me-1" style="color:var(--card-accent);"></i> {{ ucfirst(str_replace('_', ' ', $type)) }}
                </h6>
                <div class="d-flex flex-column gap-2 mb-3">
                    @foreach($items as $item)
                    <div class="d-flex align-items-center gap-3 py-2 px-3" style="background:rgba(255,255,255,0.3);border-radius:1rem;">
                        <div style="flex:1;min-width:0;">
                            <div style="font-size:0.88rem;color:var(--text-primary);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                {{ Str::limit(strip_tags(questionText($item)), 80) }}
                            </div>
                            <small style="color:var(--text-muted);">{{ $item->subject?->name ?? 'General' }}</small>
                        </div>
                        <button type="button" class="btn-soft py-1 px-2 group-share-btn" style="font-size:0.65rem;"
                                data-type="{{ $type }}" data-id="{{ $item->id }}"
                                title="Share into a study group">
                            <i class="bi bi-people"></i>
                            <span>Share</span>
                        </button>
                        <button class="btn-soft py-1 px-2 toggle-vis-btn" style="font-size:0.65rem;{{ $item->is_public ? 'color:#059669;border-color:#059669;' : 'color:var(--text-muted);' }}"
                                data-type="{{ $type }}" data-id="{{ $item->id }}" data-public="{{ $item->is_public ? '1' : '0' }}">
                            <i class="bi {{ $item->is_public ? 'bi-unlock-fill' : 'bi-lock-fill' }}"></i>
                            <span class="vis-text">{{ $item->is_public ? 'Public' : 'Private' }}</span>
                        </button>
                    </div>
                    @endforeach
                </div>
            @endif
        @endforeach
        @if(!$hasOwn)
        <div class="text-center py-4" style="color:var(--text-muted);font-size:0.85rem;">
            <i class="bi bi-pencil" style="font-size:1.5rem;display:block;margin-bottom:0.3rem;"></i>
            You haven't created any questions yet.
            <div class="mt-2">
                <a href="{{ route('mcqs.create') }}" class="btn-soft py-1 px-2" style="font-size:0.7rem;">
                    <i class="bi bi-plus-circle"></i> Create Questions
                </a>
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Share to Group Modal -->
<div class="modal" id="groupShareModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content border-0 shadow rounded-4">
            <form id="groupShareForm">
                @csrf
                <input type="hidden" name="type" id="groupShareType">
                <input type="hidden" name="id" id="groupShareId">
                <div class="modal-header border-0" style="padding:1.5rem 1.5rem 0;">
                    <h5 class="modal-title" style="color:var(--text-primary);font-weight:800;">
                        <i class="bi bi-people-fill me-2" style="color:var(--card-accent);"></i> Share Question to Group
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" style="padding:1.5rem;">
                    <div id="groupShareEmpty" style="display:none;text-align:center;padding:1rem 0;">
                        <i class="bi bi-people" style="font-size:2rem;color:#c7d2fe;display:block;margin-bottom:0.5rem;"></i>
                        <p style="color:var(--text-secondary);font-size:0.9rem;margin:0 0 0.3rem;">You need to be in a study group to share questions.</p>
                        <a href="{{ route('study-groups.index') }}" class="dark-btn py-1 px-3" style="font-size:0.75rem;display:inline-flex;">
                            <i class="bi bi-plus-circle me-1"></i> Join or Create a Group
                        </a>
                    </div>
                    <div id="groupShareFormBody">
                        <div class="mb-4 p-3" style="background:#f8fafc;border-radius:1rem;border:1px solid #e5e7eb;">
                            <label style="font-size:0.65rem;font-weight:600;text-transform:uppercase;letter-spacing:0.05em;color:var(--card-accent);margin-bottom:0.3rem;display:block;">Question</label>
                            <p id="groupShareQuestion" style="color:var(--text-primary);font-size:0.9rem;margin:0;"></p>
                        </div>
                        <div class="mb-3">
                            <label style="font-size:0.7rem;font-weight:600;text-transform:uppercase;letter-spacing:0.05em;color:var(--card-accent);margin-bottom:0.4rem;display:block;">
                                Share to Group <span style="color:#dc2626;">*</span>
                            </label>
                            <select name="group_id" id="groupShareGroup" class="form-control" required
                                    style="background:white;border:1.5px solid #e5e7eb;border-radius:1rem;padding:0.7rem 1.1rem;font-size:0.9rem;width:100%;font-family:'Inter',sans-serif;">
                                <option value="">Select group...</option>
                                @foreach($myGroups as $group)
                                <option value="{{ $group->id }}">{{ $group->name }} ({{ $group->members_count }} member{{ $group->members_count === 1 ? '' : 's' }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0" style="padding:0 1.5rem 1.5rem;">
                    <button type="button" class="btn-soft" data-bs-dismiss="modal"><i class="bi bi-x"></i> Cancel</button>
                    <button type="submit" class="dark-btn" id="groupShareSubmit" form="groupShareForm"><i class="bi bi-share"></i> Share</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.nav-pills .nav-link { color: var(--text-secondary); background: transparent; border: 1.5px solid transparent; }
.nav-pills .nav-link:hover { color: var(--card-accent); background: var(--badge-bg); border-color: var(--card-accent); }
.nav-pills .nav-link.active { color: white !important; background: #111827 !important; border-color: #111827 !important; }
[data-theme="dark"] .nav-pills .nav-link.active { background: #1e293b !important; border-color: #1e293b !important; }
</style>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Share to Group modal
    const groupShareModalEl = document.getElementById('groupShareModal');
    let groupShareModal = null;
    if (groupShareModalEl) {
        groupShareModal = new bootstrap.Modal(groupShareModalEl);
    }

    function openGroupShare(type, id, questionText) {
        if (!groupShareModal) return;
        const root = document.getElementById('modal-root');
        if (root && groupShareModalEl.parentNode !== root) root.appendChild(groupShareModalEl);

        document.getElementById('groupShareType').value = type;
        document.getElementById('groupShareId').value = id;
        document.getElementById('groupShareQuestion').textContent = questionText || 'Question';
        document.getElementById('groupShareGroup').value = '';
        document.getElementById('groupShareForm').classList.remove('was-validated');

        const hasGroups = document.getElementById('groupShareGroup').options.length > 1;
        document.getElementById('groupShareFormBody').style.display = hasGroups ? 'block' : 'none';
        document.getElementById('groupShareEmpty').style.display = hasGroups ? 'none' : 'block';
        document.getElementById('groupShareSubmit').style.display = hasGroups ? '' : 'none';

        groupShareModal.show();
    }

    document.querySelectorAll('.group-share-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const text = this.closest('.d-flex').querySelector('div div')?.textContent.trim() || 'Question';
            openGroupShare(this.dataset.type, this.dataset.id, text);
        });
    });

    document.getElementById('groupShareForm')?.addEventListener('submit', function (e) {
        e.preventDefault();
        if (!this.checkValidity()) {
            this.classList.add('was-validated');
            return;
        }
        const btn = document.getElementById('groupShareSubmit');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span> Sharing...';

        const groupId = document.getElementById('groupShareGroup').value;
        fetch('{{ url("study-groups") }}/' + groupId + '/share', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/json' },
            body: JSON.stringify({
                type: document.getElementById('groupShareType').value,
                id: document.getElementById('groupShareId').value
            })
        })
        .then(r => r.json().then(data => ({ ok: r.ok, data })))
        .then(({ ok, data }) => {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-share"></i> Share';
            if (ok && data.success) {
                showToast(data.message, 'success');
                groupShareModal.hide();
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

    document.querySelectorAll('.toggle-vis-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const type = this.dataset.type;
            const id = this.dataset.id;
            const wasPublic = this.dataset.public === '1';
            const icon = this.querySelector('i');
            const text = this.querySelector('.vis-text');

            this.disabled = true;
            this.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span>';

            fetch('{{ route("shared-questions.toggle") }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/json' },
                body: JSON.stringify({ type, id })
            })
            .then(r => r.json().then(data => ({ ok: r.ok, data })))
            .then(({ ok, data }) => {
                if (ok && data.success) {
                    showToast(data.message, 'success');
                    this.dataset.public = data.is_public ? '1' : '0';
                    this.innerHTML = '<i class="bi ' + (data.is_public ? 'bi-unlock-fill' : 'bi-lock-fill') + '"></i> <span class="vis-text">' + (data.is_public ? 'Public' : 'Private') + '</span>';
                    this.style.color = data.is_public ? '#059669' : 'var(--text-muted)';
                    this.style.borderColor = data.is_public ? '#059669' : '';
                    this.disabled = false;
                } else {
                    showToast(data.message || 'Error toggling visibility', 'error');
                    this.innerHTML = '<i class="bi ' + (wasPublic ? 'bi-unlock-fill' : 'bi-lock-fill') + '"></i> <span class="vis-text">' + (wasPublic ? 'Public' : 'Private') + '</span>';
                    this.disabled = false;
                }
            })
            .catch(() => {
                this.innerHTML = '<i class="bi ' + (wasPublic ? 'bi-unlock-fill' : 'bi-lock-fill') + '"></i> <span class="vis-text">' + (wasPublic ? 'Public' : 'Private') + '</span>';
                this.disabled = false;
                showToast('Error toggling visibility', 'error');
            });
        });
    });

    document.querySelectorAll('.load-more-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const type = this.dataset.type;
            const offset = parseInt(this.dataset.offset);
            const container = document.querySelector('.shared-items-container[data-type="' + type + '"]');
            const btnEl = this;

            btnEl.disabled = true;
            btnEl.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Loading...';

            fetch('{{ route("shared-questions.fetch") }}?type=' + type + '&offset=' + offset, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.json())
            .then(data => {
                if (data.items && data.items.length) {
                    data.items.forEach(function (item) {
                        const div = document.createElement('div');
                        div.className = 'd-flex align-items-center gap-3 py-2 px-3 shared-item';
                        div.style.cssText = 'background:rgba(255,255,255,0.3);border-radius:1rem;';
                        div.innerHTML = `
                            <div style="flex:1;min-width:0;">
                                <div style="font-size:0.88rem;color:var(--text-primary);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">${escHtml(item.text)}</div>
                                <div class="d-flex gap-2">
                                    <small style="color:var(--text-muted);"><i class="bi bi-person-circle"></i> ${escHtml(item.user)}</small>
                                    <span class="stat-badge" style="background:#eef2ff;color:var(--card-accent);font-size:0.55rem;">${escHtml(item.subject)}</span>
                                </div>
                            </div>`;
                        container.appendChild(div);
                    });
                    btnEl.dataset.offset = offset + data.items.length;
                    btnEl.innerHTML = '<i class="bi bi-arrow-down"></i> Load More';
                    if (!data.has_more) {
                        btnEl.style.display = 'none';
                    }
                } else {
                    btnEl.innerHTML = '<i class="bi bi-arrow-down"></i> Load More';
                    showToast('No more questions', 'info');
                }
                btnEl.disabled = false;
            })
            .catch(() => {
                btnEl.innerHTML = '<i class="bi bi-arrow-down"></i> Load More';
                btnEl.disabled = false;
                showToast('Error loading questions', 'error');
            });
        });
    });

    function escHtml(s) {
        if (!s) return '';
        const d = document.createElement('div');
        d.textContent = s;
        return d.innerHTML;
    }
});
</script>
@endpush
