@extends('Backend.master')

@section('content')
<div class="container-fluid px-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <div>
            <h1 class="display-6 fw-bold text-primary">
                <i class="fas fa-key me-2"></i>Permission Management
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb bg-transparent p-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Permissions</li>
                </ol>
            </nav>
        </div>
        @can('manage permissions')
        <button class="btn btn-primary shadow-sm rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#permissionModal" onclick="openCreate()">
            <i class="fas fa-plus-circle me-1"></i> Create New Permission
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
                            <h6 class="text-white-50">Total Permissions</h6>
                            {{-- <h2 class="fw-bold mb-0">{{ $permissions->total() }}</h2> --}}
                        </div>
                        <i class="fas fa-key fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Permissions Table Card -->
    <div class="card border-0 shadow-lg rounded-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center rounded-top-4 border-bottom">
            <div>
                <i class="fas fa-table text-primary me-2"></i>
                <strong>Permissions List</strong>
            </div>
            <div class="input-group w-25">
                <span class="input-group-text bg-transparent border-end-0"><i class="fas fa-search"></i></span>
                <input type="text" id="searchInput" class="form-control border-start-0" placeholder="Search permission...">
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="permissionsTable">
                    <thead class="bg-light">
                        <tr>
                            <th class="border-0 py-3">#ID</th>
                            <th class="border-0 py-3">Permission Name</th>
                            <th class="border-0 py-3">Guard</th>
                            <th class="border-0 py-3">Created</th>
                            <th class="border-0 py-3 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($permissions as $permission)
                            <tr>
                                <td class="fw-bold text-primary">{{ $permission->id }}</td>
                                <td>
                                    <span class="badge bg-info bg-gradient rounded-pill px-3 py-2">
                                        <i class="fas fa-lock me-1"></i> {{ $permission->name }}
                                    </span>
                                </td>
                                <td><span class="badge bg-secondary bg-gradient">{{ $permission->guard_name ?? 'web' }}</span></td>
                                <td>{{ $permission->created_at->format('d M Y, h:i A') }}</td>
                                <td class="text-center">
                                    @can('manage permissions')
                                    <a href="{{route('permissions.edit', $permission->id)}}" class="btn btn-sm btn-outline-warning rounded-pill px-3 me-1">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <form action="{{ route('permissions.destroy', $permission->id) }}" method="POST" style="display:inline-block;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3"
                                                onclick="return confirm('⚠️ Delete this permission? This action cannot be undone.')">
                                            <i class="fas fa-trash-alt"></i> Delete
                                        </button>
                                    </form>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <i class="fas fa-database fa-3x text-muted mb-3"></i>
                                    <p class="text-muted mb-0">No permissions found. Click "Create New Permission" to add one.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white py-3 rounded-bottom-4">
            <div class="d-flex justify-content-center">
                {{-- {{ $permissions->links() }} --}}
            </div>
        </div>
    </div>
</div>

<!-- Modern Modal for Create/Edit Permission -->
<div class="modal fade" id="permissionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <form id="permissionForm" method="POST">
                @csrf
                <input type="hidden" name="_method" id="methodField" value="POST">

                <div class="modal-header bg-primary text-white rounded-top-4 border-0">
                    <h5 class="modal-title" id="modalTitle">
                        <i class="fas fa-plus-circle me-2"></i>Create Permission
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body p-4">
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Permission Name <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fas fa-tag text-primary"></i></span>
                            <input type="text" name="name" id="permName" class="form-control border-start-0"
                                   placeholder="e.g., view_users, edit_posts, delete_roles" required autofocus>
                        </div>
                        <small class="text-muted">Use lowercase letters and underscores. Example: create_user, edit_role</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Guard Name</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fas fa-shield-alt text-primary"></i></span>
                            <select name="guard_name" id="guardName" class="form-select border-start-0">
                                <option value="web">web (default)</option>
                                <option value="api">api</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light border-0 rounded-bottom-4">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm" id="saveBtn">
                        <i class="fas fa-save me-1"></i> Save Permission
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Inline Search Script -->
<script>
    document.getElementById('searchInput')?.addEventListener('keyup', function() {
        let value = this.value.toLowerCase();
        let rows = document.querySelectorAll('#permissionsTable tbody tr');
        rows.forEach(row => {
            let text = row.innerText.toLowerCase();
            row.style.display = text.includes(value) ? '' : 'none';
        });
    });
</script>
@endsection

@push('scripts')
<script>
    // Safe URL handling
    let storeUrl = "{{ route('permissions.store') }}";
    let baseUrl = storeUrl.replace(/\/$/, '');
    let updateUrlPattern = baseUrl + '/:id';

    function openCreate() {
        let form = document.getElementById('permissionForm');
        if (form) form.action = storeUrl;
        let methodField = document.getElementById('methodField');
        if (methodField) methodField.value = 'POST';

        document.getElementById('modalTitle').innerHTML = '<i class="fas fa-plus-circle me-2"></i>Create Permission';
        document.getElementById('permName').value = '';
        document.getElementById('guardName').value = 'web';
        document.getElementById('saveBtn').innerHTML = '<i class="fas fa-save me-1"></i> Save Permission';
    }

    function openEdit(id, name, guard) {
        let form = document.getElementById('permissionForm');
        if (form) form.action = updateUrlPattern.replace(':id', id);
        let methodField = document.getElementById('methodField');
        if (methodField) methodField.value = 'PUT';

        document.getElementById('modalTitle').innerHTML = '<i class="fas fa-edit me-2"></i>Edit Permission';
        document.getElementById('permName').value = name;
        document.getElementById('guardName').value = guard;
        document.getElementById('saveBtn').innerHTML = '<i class="fas fa-upload me-1"></i> Update Permission';
    }

    document.addEventListener('DOMContentLoaded', function () {
        let modal = document.getElementById('permissionModal');
        if (modal) {
            modal.addEventListener('hidden.bs.modal', function () {
                openCreate();
            });
        }
    });
</script>
@endpush
