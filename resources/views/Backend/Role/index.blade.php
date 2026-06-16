@extends('Backend.master')

@section('content')
<div class="container-fluid px-0">
    <div class="page-header">
        <div>
            <h2>Role Management</h2>
            <p>Manage user roles and their permissions</p>
        </div>
        @can('manage roles')
            <button class="dark-btn" id="createRoleBtn">
                <i class="bi bi-shield-plus"></i> Create New Role
            </button>
        @endcan
    </div>

    @if(session('success'))
        <div class="glass-card mb-4 d-flex align-items-center gap-3 py-3" style="border-left:4px solid #059669;">
            <i class="bi bi-check-circle-fill" style="color:#059669;font-size:1.2rem;"></i>
            <span style="color:#1e1b4b;font-size:0.9rem;">{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="glass-card mb-4 d-flex align-items-center gap-3 py-3" style="border-left:4px solid #dc2626;">
            <i class="bi bi-exclamation-triangle-fill" style="color:#dc2626;font-size:1.2rem;"></i>
            <span style="color:#1e1b4b;font-size:0.9rem;">{{ session('error') }}</span>
        </div>
    @endif

    <!-- Stats -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="glass-card d-flex align-items-center gap-3">
                <div style="width:50px;height:50px;border-radius:16px;background:linear-gradient(135deg,#6366f1,#818cf8);display:flex;align-items:center;justify-content:center;font-size:1.5rem;color:white;box-shadow:0 8px 16px rgba(99,102,241,0.2);flex-shrink:0;">
                    <i class="bi bi-shield-check"></i>
                </div>
                <div>
                    <h6 style="color:#6366f1;font-size:0.65rem;text-transform:uppercase;letter-spacing:0.05em;font-weight:700;margin:0;">Total Roles</h6>
                    <h3 class="fw-bold mb-0" style="color:#1e1b4b;font-size:1.7rem;">{{ $roles->total() }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Roles Table -->
    <div class="glass-card p-0 overflow-hidden">
        <div class="d-flex justify-content-between align-items-center px-4 py-3" style="border-bottom:1.5px solid #e5e7eb;">
            <div style="color:#1e1b4b;font-weight:700;font-size:0.9rem;">
                <i class="bi bi-table me-2" style="color:#6366f1;"></i> Roles List
            </div>
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-search" style="color:#9ca3af;"></i>
                <input type="text" id="searchInput" placeholder="Search role..." style="border:1.5px solid #e5e7eb;border-radius:40px;padding:0.4rem 0.8rem;font-size:0.8rem;width:200px;outline:none;font-family:'Inter',sans-serif;">
            </div>
        </div>
        <div class="table-responsive">
            <table class="glass-table" id="rolesTable">
                <thead>
                    <tr><th>ID</th><th>Role Name</th><th>Guard</th><th>Permissions</th><th>Created</th><th class="text-center">Actions</th></tr>
                </thead>
                <tbody>
                    @forelse($roles as $role)
                        <tr>
                            <td style="color:#6366f1;font-weight:700;">{{ $role->id }}</td>
                            <td>
                                <span style="font-size:0.7rem;padding:0.25rem 0.8rem;border-radius:20px;background:#eef2ff;color:#6366f1;display:inline-block;">
                                    <i class="bi bi-tag me-1"></i> {{ $role->name }}
                                </span>
                            </td>
                            <td><span style="font-size:0.7rem;padding:0.15rem 0.6rem;border-radius:20px;background:#f3f4f6;color:#6b7280;">{{ $role->guard_name ?? 'web' }}</span></td>
                            <td>
                                @forelse($role->permissions as $perm)
                                    <span style="font-size:0.65rem;padding:0.15rem 0.5rem;border-radius:20px;background:#f0fdf4;color:#059669;display:inline-block;margin-right:0.15rem;margin-bottom:0.15rem;">
                                        <i class="bi bi-key me-1"></i> {{ $perm->name }}
                                    </span>
                                @empty
                                    <span style="color:#9ca3af;font-size:0.8rem;">No permissions</span>
                                @endforelse
                            </td>
                            <td style="color:#6b7280;font-size:0.8rem;">{{ $role->created_at->format('d M Y, h:i A') }}</td>
                            <td class="text-center">
                                @can('manage roles')
                                <div class="d-flex gap-1 justify-content-center">
                                    <button class="btn-soft py-1 px-2 edit-role-btn" style="font-size:0.75rem;" title="Edit"
                                        data-id="{{ $role->id }}"
                                        data-name="{{ $role->name }}"
                                        data-guard="{{ $role->guard_name ?? 'web' }}"
                                        data-permissions='{{ json_encode($role->permissions->pluck('id')) }}'>
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form action="{{ route('roles.destroy', $role->id) }}" method="POST" style="display:inline-block;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-soft py-1 px-2" style="font-size:0.75rem;color:#dc2626;" title="Delete" onclick="return confirm('⚠️ Delete this role? This action cannot be undone.')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5" style="color:#9ca3af;">
                                <i class="bi bi-database" style="font-size:2.5rem;color:#c7d2fe;display:block;margin-bottom:0.5rem;"></i>
                                No roles found. Click "Create New Role" to add one.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal for Create/Edit Role -->
<div class="modal" id="roleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow rounded-4">
            <form id="roleForm" method="POST">
                @csrf
                <input type="hidden" name="_method" id="methodField" value="POST">

                <div class="modal-header border-0" style="padding:1.5rem 1.5rem 0;">
                    <h5 class="modal-title" id="modalTitle" style="color:#1e1b4b;font-weight:800;">
                        <i class="bi bi-shield-plus me-2" style="color:#6366f1;"></i> Create Role
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body" style="padding:1.5rem;">
                    <div class="mb-4">
                        <label style="font-size:0.7rem;font-weight:600;text-transform:uppercase;letter-spacing:0.05em;color:#6366f1;margin-bottom:0.4rem;display:block;">Role Name <span style="color:#dc2626;">*</span></label>
                        <input type="text" name="name" id="roleName" class="form-control" required placeholder="e.g., admin, editor, viewer" style="background:white;border:1.5px solid #e5e7eb;border-radius:1rem;padding:0.7rem 1.1rem;font-size:0.9rem;width:100%;font-family:'Inter',sans-serif;">
                        <small style="color:#9ca3af;font-size:0.75rem;">Use lowercase letters and underscores.</small>
                    </div>

                    <div class="mb-4">
                        <label style="font-size:0.7rem;font-weight:600;text-transform:uppercase;letter-spacing:0.05em;color:#6366f1;margin-bottom:0.4rem;display:block;">Guard Name</label>
                        <select name="guard_name" id="guardName" class="form-select" style="background:white;border:1.5px solid #e5e7eb;border-radius:1rem;padding:0.7rem 1.1rem;font-size:0.9rem;width:100%;font-family:'Inter',sans-serif;">
                            <option value="web">web (default)</option>
                            <option value="api">api</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label style="font-size:0.7rem;font-weight:600;text-transform:uppercase;letter-spacing:0.05em;color:#6366f1;margin-bottom:0.4rem;display:block;">Assign Permissions</label>
                        <div style="background:rgba(255,255,255,0.5);border:1.5px solid #e5e7eb;border-radius:1rem;padding:1rem;max-height:300px;overflow-y:auto;">
                            <div class="row" id="permissionsList">
                                @foreach($permissions as $permission)
                                    <div class="col-md-6 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input permission-checkbox" type="checkbox"
                                                   name="permissions[]" value="{{ $permission->id }}"
                                                   id="perm_{{ $permission->id }}"
                                                   style="border-color:#c7d2fe;border-radius:4px;">
                                            <label class="form-check-label" for="perm_{{ $permission->id }}" style="color:#374151;font-size:0.85rem;">
                                                <i class="bi bi-key" style="color:#6366f1;margin-right:0.25rem;"></i> {{ $permission->name }}
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            @if($permissions->isEmpty())
                                <p style="color:#9ca3af;text-align:center;margin:0;">No permissions available. Please create permissions first.</p>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-0" style="padding:0 1.5rem 1.5rem;">
                    <button type="button" class="btn-soft" data-bs-dismiss="modal"><i class="bi bi-x"></i> Cancel</button>
                    <button type="submit" class="dark-btn" id="saveBtn"><i class="bi bi-check-lg"></i> Save Role</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.getElementById('searchInput')?.addEventListener('keyup', function() {
        const value = this.value.toLowerCase();
        document.querySelectorAll('#rolesTable tbody tr').forEach(row => {
            row.style.display = row.innerText.toLowerCase().includes(value) ? '' : 'none';
        });
    });
</script>

@push('scripts')
<script>
    let storeUrl = "{{ route('roles.store') }}";
    let baseUrl = storeUrl.replace(/\/$/, '');
    let updateUrlPattern = baseUrl + '/:id';

    function openCreate() {
        let form = document.getElementById('roleForm');
        if (form) form.action = storeUrl;
        let mf = document.getElementById('methodField');
        if (mf) mf.value = 'POST';
        let mt = document.getElementById('modalTitle');
        if (mt) mt.innerHTML = '<i class="bi bi-shield-plus me-2" style="color:#6366f1;"></i> Create Role';
        let rn = document.getElementById('roleName');
        if (rn) rn.value = '';
        let gn = document.getElementById('guardName');
        if (gn) gn.value = 'web';
        let sb = document.getElementById('saveBtn');
        if (sb) sb.innerHTML = '<i class="bi bi-check-lg"></i> Save Role';
        document.querySelectorAll('.permission-checkbox').forEach(cb => cb.checked = false);
    }

    function openEdit(id, name, guard, permissionsArray) {
        let form = document.getElementById('roleForm');
        if (form) form.action = updateUrlPattern.replace(':id', id);
        let mf = document.getElementById('methodField');
        if (mf) mf.value = 'PUT';
        let mt = document.getElementById('modalTitle');
        if (mt) mt.innerHTML = '<i class="bi bi-pencil me-2" style="color:#6366f1;"></i> Edit Role';
        let rn = document.getElementById('roleName');
        if (rn) rn.value = name;
        let gn = document.getElementById('guardName');
        if (gn) gn.value = guard;
        let sb = document.getElementById('saveBtn');
        if (sb) sb.innerHTML = '<i class="bi bi-check-lg"></i> Update Role';

        document.querySelectorAll('.permission-checkbox').forEach(cb => cb.checked = false);
        if (permissionsArray && permissionsArray.length) {
            permissionsArray.forEach(permId => {
                let cb = document.querySelector(`.permission-checkbox[value="${permId}"]`);
                if (cb) cb.checked = true;
            });
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        let modalEl = document.getElementById('roleModal');
        if (!modalEl) return;
        let modal = new bootstrap.Modal(modalEl);

        function ensureModalInRoot() {
            let root = document.getElementById('modal-root');
            if (root && modalEl.parentNode !== root) {
                root.appendChild(modalEl);
            }
        }

        document.getElementById('createRoleBtn')?.addEventListener('click', function () {
            openCreate();
            ensureModalInRoot();
            modal.show();
        });

        document.querySelectorAll('.edit-role-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                let permissions = [];
                try {
                    permissions = JSON.parse(this.dataset.permissions || '[]');
                } catch (e) {}
                openEdit(this.dataset.id, this.dataset.name, this.dataset.guard, permissions);
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
@endsection