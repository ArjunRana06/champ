@extends('Backend.master')

@section('content')
<div class="container-fluid px-0">
    <div class="page-header">
        <div>
            <h2>Permission Management</h2>
            <p>Manage permissions for user roles</p>
        </div>
        <button class="dark-btn" id="createPermissionBtn">
            <i class="bi bi-key"></i> Create New Permission
        </button>
    </div>

    @if(session('success'))
        <div class="glass-card mb-4 d-flex align-items-center gap-3 py-3" style="border-left:4px solid #059669;">
            <i class="bi bi-check-circle-fill" style="color:#059669;font-size:1.2rem;"></i>
            <span style="color:var(--text-primary);font-size:0.9rem;">{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="glass-card mb-4 d-flex align-items-center gap-3 py-3" style="border-left:4px solid #dc2626;">
            <i class="bi bi-exclamation-triangle-fill" style="color:#dc2626;font-size:1.2rem;"></i>
            <span style="color:var(--text-primary);font-size:0.9rem;">{{ session('error') }}</span>
        </div>
    @endif

    <!-- Stats -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="glass-card d-flex align-items-center gap-3">
                <div style="width:50px;height:50px;border-radius:16px;background:linear-gradient(135deg,#6366f1,#818cf8);display:flex;align-items:center;justify-content:center;font-size:1.5rem;color:white;box-shadow:0 8px 16px rgba(99,102,241,0.2);flex-shrink:0;">
                    <i class="bi bi-key"></i>
                </div>
                <div>
                    <h6 style="color:var(--card-accent);font-size:0.65rem;text-transform:uppercase;letter-spacing:0.05em;font-weight:700;margin:0;">Total Permissions</h6>
                    <h3 class="fw-bold mb-0" style="color:var(--text-primary);font-size:1.7rem;">{{ $permissions->count() }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Permissions Table -->
    <div class="glass-card p-0 overflow-hidden">
        <div class="d-flex justify-content-between align-items-center px-4 py-3" style="border-bottom:1.5px solid #e5e7eb;">
            <div style="color:var(--text-primary);font-weight:700;font-size:0.9rem;">
                <i class="bi bi-table me-2" style="color:var(--card-accent);"></i> Permissions List
            </div>
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-search" style="color:var(--text-muted);"></i>
                <input type="text" id="searchInput" placeholder="Search permission..." style="border:1.5px solid #e5e7eb;border-radius:40px;padding:0.4rem 0.8rem;font-size:0.8rem;width:200px;outline:none;font-family:'Inter',sans-serif;">
            </div>
        </div>
        <div class="table-responsive">
            <table class="glass-table" id="permissionsTable">
                <thead>
                    <tr><th>ID</th><th>Permission Name</th><th>Guard</th><th>Created</th><th class="text-center">Actions</th></tr>
                </thead>
                <tbody>
                    @forelse($permissions as $permission)
                        <tr>
                            <td style="color:var(--card-accent);font-weight:700;">{{ $permission->id }}</td>
                            <td>
                                <span style="font-size:0.7rem;padding:0.25rem 0.8rem;border-radius:20px;background:#eef2ff;color:var(--card-accent);display:inline-block;">
                                    <i class="bi bi-key me-1"></i> {{ $permission->name }}
                                </span>
                            </td>
                            <td><span style="font-size:0.7rem;padding:0.15rem 0.6rem;border-radius:20px;background:#f3f4f6;color:var(--text-secondary);">{{ $permission->guard_name ?? 'web' }}</span></td>
                            <td style="color:var(--text-secondary);font-size:0.8rem;">{{ $permission->created_at->format('d M Y, h:i A') }}</td>
                            <td class="text-center">
                                <div class="d-flex gap-1 justify-content-center">
                                    <button class="btn-soft py-1 px-2 edit-perm-btn" style="font-size:0.75rem;" title="Edit"
                                        data-id="{{ $permission->id }}"
                                        data-name="{{ $permission->name }}"
                                        data-guard="{{ $permission->guard_name ?? 'web' }}">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form action="{{ route('permissions.destroy', $permission->id) }}" method="POST" style="display:inline-block;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-soft py-1 px-2" style="font-size:0.75rem;color:#dc2626;" title="Delete" onclick="return confirm('⚠️ Delete this permission? This action cannot be undone.')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5" style="color:var(--text-muted);">
                                <i class="bi bi-database" style="font-size:2.5rem;color:#c7d2fe;display:block;margin-bottom:0.5rem;"></i>
                                No permissions found. Click "Create New Permission" to add one.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal for Create/Edit Permission -->
<div class="modal" id="permissionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content border-0 shadow rounded-4">
            <form id="permissionForm" method="POST">
                @csrf
                <input type="hidden" name="_method" id="methodField" value="POST">

                <div class="modal-header border-0" style="padding:1.5rem 1.5rem 0;">
                    <h5 class="modal-title" id="modalTitle" style="color:var(--text-primary);font-weight:800;">
                        <i class="bi bi-key me-2" style="color:var(--card-accent);"></i> Create Permission
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body" style="padding:1.5rem;">
                    <div class="mb-4">
                        <label style="font-size:0.7rem;font-weight:600;text-transform:uppercase;letter-spacing:0.05em;color:var(--card-accent);margin-bottom:0.4rem;display:block;">Permission Name <span style="color:#dc2626;">*</span></label>
                        <input type="text" name="name" id="permName" class="form-control" required placeholder="e.g., view_users, edit_posts, delete_roles"
                               style="background:white;border:1.5px solid #e5e7eb;border-radius:1rem;padding:0.7rem 1.1rem;font-size:0.9rem;width:100%;font-family:'Inter',sans-serif;">
                        <small style="color:var(--text-muted);font-size:0.75rem;">Use lowercase letters and underscores.</small>
                    </div>

                    <div class="mb-3">
                        <label style="font-size:0.7rem;font-weight:600;text-transform:uppercase;letter-spacing:0.05em;color:var(--card-accent);margin-bottom:0.4rem;display:block;">Guard Name</label>
                        <select name="guard_name" id="guardName" class="form-select" style="background:white;border:1.5px solid #e5e7eb;border-radius:1rem;padding:0.7rem 1.1rem;font-size:0.9rem;width:100%;font-family:'Inter',sans-serif;">
                            <option value="web">web (default)</option>
                            <option value="api">api</option>
                        </select>
                    </div>
                </div>

                <div class="modal-footer border-0" style="padding:0 1.5rem 1.5rem;">
                    <button type="button" class="btn-soft" data-bs-dismiss="modal"><i class="bi bi-x"></i> Cancel</button>
                    <button type="submit" class="dark-btn" id="saveBtn"><i class="bi bi-check-lg"></i> Save Permission</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.getElementById('searchInput')?.addEventListener('keyup', function() {
        const value = this.value.toLowerCase();
        document.querySelectorAll('#permissionsTable tbody tr').forEach(row => {
            row.style.display = row.innerText.toLowerCase().includes(value) ? '' : 'none';
        });
    });
</script>
@endsection

@push('scripts')
<script>
    let storeUrl = "{{ route('permissions.store') }}";
    let baseUrl = storeUrl.replace(/\/$/, '');
    let updateUrlPattern = baseUrl + '/:id';

    function openCreate() {
        let form = document.getElementById('permissionForm');
        if (form) form.action = storeUrl;
        let mf = document.getElementById('methodField');
        if (mf) mf.value = 'POST';
        let mt = document.getElementById('modalTitle');
        if (mt) mt.innerHTML = '<i class="bi bi-key me-2" style="color:var(--card-accent);"></i> Create Permission';
        let pn = document.getElementById('permName');
        if (pn) pn.value = '';
        let gn = document.getElementById('guardName');
        if (gn) gn.value = 'web';
        let sb = document.getElementById('saveBtn');
        if (sb) sb.innerHTML = '<i class="bi bi-check-lg"></i> Save Permission';
    }

    function openEdit(id, name, guard) {
        let form = document.getElementById('permissionForm');
        if (form) form.action = updateUrlPattern.replace(':id', id);
        let mf = document.getElementById('methodField');
        if (mf) mf.value = 'PUT';
        let mt = document.getElementById('modalTitle');
        if (mt) mt.innerHTML = '<i class="bi bi-pencil me-2" style="color:var(--card-accent);"></i> Edit Permission';
        let pn = document.getElementById('permName');
        if (pn) pn.value = name;
        let gn = document.getElementById('guardName');
        if (gn) gn.value = guard || 'web';
        let sb = document.getElementById('saveBtn');
        if (sb) sb.innerHTML = '<i class="bi bi-check-lg"></i> Update Permission';
    }

    document.addEventListener('DOMContentLoaded', function () {
        let modalEl = document.getElementById('permissionModal');
        if (!modalEl) return;
        let modal = new bootstrap.Modal(modalEl);

        function ensureModalInRoot() {
            let root = document.getElementById('modal-root');
            if (root && modalEl.parentNode !== root) {
                root.appendChild(modalEl);
            }
        }

        document.getElementById('createPermissionBtn')?.addEventListener('click', function () {
            openCreate();
            ensureModalInRoot();
            modal.show();
        });

        document.querySelectorAll('.edit-perm-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                openEdit(this.dataset.id, this.dataset.name, this.dataset.guard);
                ensureModalInRoot();
                modal.show();
            });
        });

        modalEl.addEventListener('hidden.bs.modal', function () {
            openCreate();
        });
    });
</script>
@endpush
