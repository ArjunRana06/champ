@extends('Backend.master')

@section('content')
<div class="container-fluid px-0">
    <div class="page-header">
        <div>
            <h2>Study Groups</h2>
            <p>Collaborate and learn together</p>
        </div>
        <button class="dark-btn" id="createGroupBtn"><i class="bi bi-plus-circle"></i> New Group</button>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-4 col-6">
            <div class="glass-card text-center py-3">
                <div style="font-size:1.8rem;font-weight:800;color:var(--card-accent);">{{ $myGroups->count() }}</div>
                <small style="color:var(--text-muted);">My Groups</small>
            </div>
        </div>
        <div class="col-md-4 col-6">
            <div class="glass-card text-center py-3">
                <div style="font-size:1.8rem;font-weight:800;color:var(--card-accent);">{{ $otherGroups->count() }}</div>
                <small style="color:var(--text-muted);">Available Groups</small>
            </div>
        </div>
        <div class="col-md-4 col-12">
            <div class="glass-card text-center py-3">
                <div style="font-size:1.8rem;font-weight:800;color:var(--card-accent);">{{ $myGroups->sum('members_count') }}</div>
                <small style="color:var(--text-muted);">Total Members</small>
            </div>
        </div>
    </div>

    @if($myGroups->count())
    <h5 class="mb-3" style="color:var(--card-accent);font-weight:700;font-size:0.9rem;">
        <i class="bi bi-people-fill me-1"></i> My Groups
    </h5>
    <div class="row g-4 mb-5" id="myGroupsContainer">
        @foreach($myGroups as $group)
        <div class="col-md-4 group-card" data-id="{{ $group->id }}">
            <div class="glass-card h-100 position-relative" style="border-left:3px solid var(--card-accent);">
                @if($group->members->first()?->role === 'admin')
                <span class="position-absolute top-0 end-0 m-2 stat-badge up" style="font-size:0.55rem;">Admin</span>
                @endif
                <div style="color:var(--text-primary);font-weight:700;font-size:1.1rem;">{{ $group->name }}</div>
                <p style="color:var(--text-secondary);font-size:0.82rem;margin:0.3rem 0 0.5rem;">{{ $group->description ? Str::limit($group->description, 80) : 'No description' }}</p>
                <div class="d-flex justify-content-between align-items-center mt-2">
                    <div class="d-flex align-items-center gap-2">
                        <span class="stat-badge" style="background:#eef2ff;color:var(--card-accent);font-size:0.6rem;">
                            <i class="bi bi-people"></i> {{ $group->members_count }}
                        </span>
                        @if($group->resources_count)
                        <span class="stat-badge" style="background:#fef3c7;color:#d97706;font-size:0.6rem;">
                            <i class="bi bi-share"></i> {{ $group->resources_count }}
                        </span>
                        @endif
                    </div>
                    <div class="d-flex gap-1">
                        <a href="{{ route('study-groups.show', $group) }}" class="btn-soft py-1 px-2" style="font-size:0.7rem;">
                            <i class="bi bi-eye"></i>
                        </a>
                        @if($group->members->first()?->role === 'admin')
                        <button class="btn-soft danger py-1 px-2" style="font-size:0.7rem;" onclick="deleteGroup({{ $group->id }}, this)" title="Delete group">
                            <i class="bi bi-trash"></i>
                        </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="glass-card text-center py-5 mb-5">
        <i class="bi bi-people" style="font-size:3rem;color:#c7d2fe;"></i>
        <p class="mt-3" style="color:var(--text-secondary);">You haven't joined any study groups yet.</p>
        <p style="color:var(--text-muted);font-size:0.85rem;">Create one or discover groups below!</p>
        <button class="dark-btn" id="createGroupBtnEmpty"><i class="bi bi-plus-circle"></i> Create Group</button>
    </div>
    @endif

    @if($otherGroups->count())
    <h5 class="mb-3" style="color:var(--card-accent);font-weight:700;font-size:0.9rem;">
        <i class="bi bi-compass me-1"></i> Discover Groups
    </h5>
    <div class="row g-4" id="discoverGroups">
        @foreach($otherGroups as $group)
        <div class="col-md-4">
            <div class="glass-card h-100">
                <div style="color:var(--text-primary);font-weight:700;font-size:1.1rem;">{{ $group->name }}</div>
                <p style="color:var(--text-secondary);font-size:0.82rem;margin:0.3rem 0 0.5rem;">{{ $group->description ? Str::limit($group->description, 80) : 'No description' }}</p>
                <div class="d-flex justify-content-between align-items-center mt-2">
                    <span class="stat-badge" style="background:#eef2ff;color:var(--card-accent);font-size:0.6rem;">
                        <i class="bi bi-people"></i> {{ $group->members_count }} members
                    </span>
                    <button class="dark-btn py-1 px-2" style="font-size:0.7rem;" onclick="joinGroup({{ $group->id }}, this)">
                        <i class="bi bi-plus-circle"></i> Join
                    </button>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @elseif($myGroups->count())
    <div class="glass-card text-center py-4">
        <p style="color:var(--text-secondary);font-size:0.85rem;margin:0;">
            <i class="bi bi-check-circle me-1" style="color:#059669;"></i> No other groups available. Create one!
        </p>
    </div>
    @endif
</div>

<!-- Create Group Modal -->
<div class="modal" id="createGroupModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content border-0 shadow rounded-4">
            <form id="createGroupForm">
                @csrf
                <div class="modal-header border-0" style="padding:1.5rem 1.5rem 0;">
                    <h5 class="modal-title" style="color:var(--text-primary);font-weight:800;">
                        <i class="bi bi-people-fill me-2" style="color:var(--card-accent);"></i> Create Study Group
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" style="padding:1.5rem;">
                    <div class="mb-4">
                        <label style="font-size:0.7rem;font-weight:600;text-transform:uppercase;letter-spacing:0.05em;color:var(--card-accent);margin-bottom:0.4rem;display:block;">
                            Group Name <span style="color:#dc2626;">*</span>
                        </label>
                        <input type="text" name="name" class="form-control" required placeholder="e.g., Biology Study Group"
                               style="background:white;border:1.5px solid #e5e7eb;border-radius:1rem;padding:0.7rem 1.1rem;font-size:0.9rem;width:100%;font-family:'Inter',sans-serif;">
                    </div>
                    <div class="mb-3">
                        <label style="font-size:0.7rem;font-weight:600;text-transform:uppercase;letter-spacing:0.05em;color:var(--card-accent);margin-bottom:0.4rem;display:block;">Description</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="What will you study?"
                                  style="background:white;border:1.5px solid #e5e7eb;border-radius:1rem;padding:0.7rem 1.1rem;font-size:0.9rem;width:100%;font-family:'Inter',sans-serif;resize:vertical;min-height:100px;"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0" style="padding:0 1.5rem 1.5rem;">
                    <button type="button" class="btn-soft" data-bs-dismiss="modal"><i class="bi bi-x"></i> Cancel</button>
                    <button type="submit" class="dark-btn" id="createGroupSubmit"><i class="bi bi-save"></i> Create</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.group-card .glass-card { transition: transform 0.2s, box-shadow 0.2s; }
.group-card .glass-card:hover { transform: translateY(-2px); box-shadow: 0 12px 40px rgba(99,102,241,0.1); }
</style>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        let modalEl = document.getElementById('createGroupModal');
        if (!modalEl) return;
        let modal = new bootstrap.Modal(modalEl);

        function ensureModalInRoot() {
            let root = document.getElementById('modal-root');
            if (root && modalEl.parentNode !== root) {
                root.appendChild(modalEl);
            }
        }

        document.getElementById('createGroupBtn')?.addEventListener('click', function () {
            ensureModalInRoot();
            modal.show();
        });
        document.getElementById('createGroupBtnEmpty')?.addEventListener('click', function () {
            ensureModalInRoot();
            modal.show();
        });

        document.getElementById('createGroupForm')?.addEventListener('submit', function (e) {
            e.preventDefault();
            const btn = document.getElementById('createGroupSubmit');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span> Creating...';
            fetch('{{ route("study-groups.store") }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'X-Requested-With': 'XMLHttpRequest' },
                body: new FormData(this)
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message, 'success');
                    modal.hide();
                    location.reload();
                }
            })
            .catch(() => {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-save"></i> Create';
            });
        });
    });

    function joinGroup(id, btn) {
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span>';
        fetch('{{ url("study-groups") }}/' + id + '/join', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showToast(data.message, 'success');
                location.reload();
            }
        })
        .catch(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-plus-circle"></i> Join';
        });
    }

    function deleteGroup(id, btn) {
        if (!confirm('Delete this group? This cannot be undone.')) return;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span>';
        fetch('{{ url("study-groups") }}/' + id, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showToast(data.message, 'success');
                const card = btn.closest('.group-card');
                if (card) card.remove();
            }
        })
        .catch(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-trash"></i>';
        });
    }
</script>
@endpush
