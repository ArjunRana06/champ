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

    @if(session('success'))
        <div class="glass-card mb-4 d-flex align-items-center gap-3 py-3" style="border-left:4px solid #059669;">
            <i class="bi bi-check-circle-fill" style="color:#059669;font-size:1.2rem;"></i>
            <span style="color:var(--text-primary);font-size:0.9rem;">{{ session('success') }}</span>
        </div>
    @endif

    @if($myGroups->count())
    <h5 class="mb-3" style="color:var(--card-accent);font-weight:700;font-size:0.9rem;">My Groups</h5>
    <div class="row g-4 mb-5">
        @foreach($myGroups as $group)
        <div class="col-md-4">
            <div class="glass-card h-100">
                <div style="color:var(--card-accent);font-weight:700;font-size:1.1rem;">{{ $group->name }}</div>
                <p style="color:var(--text-secondary);font-size:0.82rem;margin:0.3rem 0 0.5rem;">{{ $group->description ?? 'No description' }}</p>
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small style="color:var(--text-muted);">{{ $group->members_count }} member(s)</small>
                        @if($group->resources_count)
                        <small style="color:var(--card-accent);margin-left:0.5rem;"><i class="bi bi-share"></i> {{ $group->resources_count }} shared</small>
                        @endif
                    </div>
                    <div class="d-flex gap-1">
                        <a href="{{ route('study-groups.show', $group) }}" class="btn-soft py-1 px-2" style="font-size:0.7rem;">View</a>
                        @if($group->is_admin)
                        <form action="{{ route('study-groups.destroy', $group) }}" method="POST" style="display:inline;">
                            @csrf @method('DELETE')
                            <button class="btn-soft danger py-1 px-2" style="font-size:0.7rem;" onclick="return confirm('Delete this group?')"><i class="bi bi-trash"></i></button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="glass-card text-center py-5">
        <i class="bi bi-people" style="font-size:3rem;color:#c7d2fe;"></i>
        <p class="mt-3" style="color:var(--text-secondary);">You haven't joined any study groups yet.</p>
        <button class="dark-btn" id="createGroupBtnEmpty"><i class="bi bi-plus-circle"></i> Create Group</button>
    </div>
    @endif

    @if($otherGroups->count())
    <h5 class="mb-3" style="color:var(--card-accent);font-weight:700;font-size:0.9rem;">Discover Groups</h5>
    <div class="row g-4">
        @foreach($otherGroups as $group)
        <div class="col-md-4">
            <div class="glass-card h-100">
                <div style="color:var(--text-primary);font-weight:700;font-size:1.1rem;">{{ $group->name }}</div>
                <p style="color:var(--text-secondary);font-size:0.82rem;margin:0.3rem 0 0.5rem;">{{ $group->description ?? 'No description' }}</p>
                <div class="d-flex justify-content-between align-items-center">
                    <small style="color:var(--text-muted);">{{ $group->members_count }} member(s)</small>
                    <form action="{{ route('study-groups.join', $group) }}" method="POST">
                        @csrf
                        <button class="dark-btn py-1 px-2" style="font-size:0.7rem;"><i class="bi bi-plus-circle"></i> Join</button>
                    </form>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @elseif($myGroups->count())
    <div class="glass-card text-center py-4">
        <p style="color:var(--text-secondary);font-size:0.85rem;margin:0;">No other groups available. Create one!</p>
    </div>
    @endif
</div>

<!-- Create Group Modal -->
<div class="modal" id="createGroupModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content border-0 shadow rounded-4">
            <form action="{{ route('study-groups.store') }}" method="POST">
                @csrf
                <div class="modal-header border-0" style="padding:1.5rem 1.5rem 0;">
                    <h5 class="modal-title" style="color:var(--text-primary);font-weight:800;">
                        <i class="bi bi-people-fill me-2" style="color:var(--card-accent);"></i> Create Study Group
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" style="padding:1.5rem;">
                    <div class="mb-4">
                        <label style="font-size:0.7rem;font-weight:600;text-transform:uppercase;letter-spacing:0.05em;color:var(--card-accent);margin-bottom:0.4rem;display:block;">Group Name <span style="color:#dc2626;">*</span></label>
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
                    <button type="submit" class="dark-btn"><i class="bi bi-save"></i> Create</button>
                </div>
            </form>
        </div>
    </div>
</div>
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
    });
</script>
@endpush
