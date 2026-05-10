@extends('Backend.master')

@section('content')
<div class="container-fluid px-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <div>
            <h1 class="display-6 fw-bold text-primary">
                <i class="fas fa-shield-alt me-2"></i>Role Management
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb bg-transparent p-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Roles</li>
                </ol>
            </nav>
        </div>
        @can('manage roles')
        <button class="btn btn-primary shadow-sm rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#roleModal" onclick="openCreate()">
            <i class="fas fa-plus-circle me-1"></i> Create New Role
        </button>
        @endcan
    </div>

    <!-- Alerts -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3" role="alert">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Stats Card -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 bg-gradient-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50">Total Roles</h6>
                            {{-- <h2 class="fw-bold mb-0">{{ $roles->total() }}</h2> --}}
                        </div>
                        <i class="fas fa-lock fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Roles Table Card -->
    <div class="card border-0 shadow-lg rounded-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center rounded-top-4 border-bottom">
            <div>
                <i class="fas fa-table text-primary me-2"></i>
                <strong>Roles List</strong>
            </div>
            <div class="input-group w-25">
                <span class="input-group-text bg-transparent border-end-0"><i class="fas fa-search"></i></span>
                <input type="text" id="searchInput" class="form-control border-start-0" placeholder="Search role...">
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="rolesTable">
                    <thead class="bg-light">
                        <tr>
                            <th class="border-0 py-3">#ID</th>
                            <th class="border-0 py-3">Role Name</th>
                            <th class="border-0 py-3">Guard</th>
                            <th class="border-0 py-3">Permissions</th>
                            <th class="border-0 py-3">Created</th>
                            <th class="border-0 py-3 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($roles as $role)
                            <tr>
                                <td class="fw-bold text-primary">{{ $role->id }}</td>
                                <td>
                                    <span class="badge bg-primary bg-gradient rounded-pill px-3 py-2">
                                        <i class="fas fa-tag me-1"></i> {{ $role->name }}
                                    </span>
                                </td>
                                <td><span class="badge bg-secondary bg-gradient">{{ $role->guard_name ?? 'web' }}</span></td>
                                <td>
                                    @forelse($role->permissions as $perm)
                                        <span class="badge bg-info bg-gradient rounded-pill px-2 py-1 me-1 mb-1">
                                            <i class="fas fa-key me-1"></i> {{ $perm->name }}
                                        </span>
                                    @empty
                                        <span class="text-muted">No permissions</span>
                                    @endforelse
                                </td>
                                <td>{{ $role->created_at->format('d M Y, h:i A') }}</td>
                                <td class="text-center">
                                    @can('manage roles')
                                    <a href="{{ route('roles.edit', $role->id) }}" class="btn btn-sm btn-outline-warning rounded-pill px-3 me-1">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <form action="{{ route('roles.destroy', $role->id) }}" method="POST" style="display:inline-block;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3"
                                                onclick="return confirm('⚠️ Delete this role? This action cannot be undone.')">
                                            <i class="fas fa-trash-alt"></i> Delete
                                        </button>
                                    </form>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <i class="fas fa-database fa-3x text-muted mb-3"></i>
                                    <p class="text-muted mb-0">No roles found. Click "Create New Role" to add one.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white py-3 rounded-bottom-4">
            <div class="d-flex justify-content-center">
                {{-- {{ $roles->links() }} --}}
            </div>
        </div>
    </div>
</div>

<!-- Modal with Permissions Selection -->
<div class="modal fade" id="roleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <form id="roleForm" method="POST">
                @csrf
                <input type="hidden" name="_method" id="methodField" value="POST">
                <input type="hidden" name="role_id" id="roleId">

                <div class="modal-header bg-primary text-white rounded-top-4 border-0">
                    <h5 class="modal-title" id="modalTitle">
                        <i class="fas fa-plus-circle me-2"></i>Create Role
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body p-4">
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Role Name <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fas fa-tag text-primary"></i></span>
                            <input type="text" name="name" id="roleName" class="form-control border-start-0"
                                   placeholder="e.g., admin, editor, viewer" required autofocus>
                        </div>
                        <small class="text-muted">Use lowercase letters and underscores.</small>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Guard Name</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fas fa-shield-alt text-primary"></i></span>
                            <select name="guard_name" id="guardName" class="form-select border-start-0">
                                <option value="web">web (default)</option>
                                <option value="api">api</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Assign Permissions</label>
                        <div class="card border rounded-3 p-3" style="max-height: 300px; overflow-y: auto;">
                            <div class="row" id="permissionsList">
                                @foreach($permissions as $permission)
                                    <div class="col-md-6 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input permission-checkbox" type="checkbox"
                                                   name="permissions[]" value="{{ $permission->id }}"
                                                   id="perm_{{ $permission->id }}">
                                            <label class="form-check-label" for="perm_{{ $permission->id }}">
                                                <i class="fas fa-key text-primary me-1"></i> {{ $permission->name }}
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            @if($permissions->isEmpty())
                                <p class="text-muted text-center mb-0">No permissions available. Please create permissions first.</p>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light border-0 rounded-bottom-4">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm" id="saveBtn">
                        <i class="fas fa-save me-1"></i> Save Role
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Search Script -->
<script>
    document.getElementById('searchInput')?.addEventListener('keyup', function() {
        let value = this.value.toLowerCase();
        let rows = document.querySelectorAll('#rolesTable tbody tr');
        rows.forEach(row => {
            let text = row.innerText.toLowerCase();
            row.style.display = text.includes(value) ? '' : 'none';
        });
    });
</script>
@endsection

@push('scripts')
<script>
    let storeUrl = "{{ route('roles.store') }}";
    let baseUrl = storeUrl.replace(/\/$/, '');
    let updateUrlPattern = baseUrl + '/:id';

    function openCreate() {
        let form = document.getElementById('roleForm');
        form.action = storeUrl;
        document.getElementById('methodField').value = 'POST';
        document.getElementById('modalTitle').innerHTML = '<i class="fas fa-plus-circle me-2"></i>Create Role';
        document.getElementById('roleName').value = '';
        document.getElementById('guardName').value = 'web';
        document.getElementById('saveBtn').innerHTML = '<i class="fas fa-save me-1"></i> Save Role';
        // Uncheck all permissions
        document.querySelectorAll('.permission-checkbox').forEach(cb => cb.checked = false);
    }

    function openEdit(id, name, guard, permissionsArray) {
        let form = document.getElementById('roleForm');
        form.action = updateUrlPattern.replace(':id', id);
        document.getElementById('methodField').value = 'PUT';
        document.getElementById('modalTitle').innerHTML = '<i class="fas fa-edit me-2"></i>Edit Role';
        document.getElementById('roleName').value = name;
        document.getElementById('guardName').value = guard;
        document.getElementById('saveBtn').innerHTML = '<i class="fas fa-upload me-1"></i> Update Role';

        // Uncheck all first
        document.querySelectorAll('.permission-checkbox').forEach(cb => cb.checked = false);
        // Check the ones assigned to this role
        if (permissionsArray && permissionsArray.length) {
            permissionsArray.forEach(permId => {
                let cb = document.querySelector(`.permission-checkbox[value="${permId}"]`);
                if (cb) cb.checked = true;
            });
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        let modal = document.getElementById('roleModal');
        if (modal) {
            modal.addEventListener('hidden.bs.modal', function () {
                openCreate();
            });
        }
    });
</script>
@endpush
