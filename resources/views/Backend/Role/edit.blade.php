@extends('Backend.master')

@section('content')
<div class="container-fluid px-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <div>
            <h1 class="display-6 fw-bold text-primary">
                <i class="fas fa-edit me-2"></i>Edit Role
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb bg-transparent p-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('roles.index') }}">Roles</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Edit Role</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('roles.index') }}" class="btn btn-secondary shadow-sm rounded-pill px-4">
            <i class="fas fa-arrow-left me-1"></i> Back to Roles
        </a>
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

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i> Please fix the following errors:
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Edit Role Card -->
    <div class="card border-0 shadow-lg rounded-4">
        <div class="card-header bg-white py-3 rounded-top-4 border-bottom">
            <div>
                <i class="fas fa-user-tag text-primary me-2"></i>
                <strong>Edit Role: {{ $role->name }}</strong>
            </div>
        </div>
        <div class="card-body p-4">
            <form action="{{ route('roles.update', $role->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="name" class="form-label fw-semibold">Role Name <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fas fa-tag text-primary"></i></span>
                            <input type="text" class="form-control border-start-0 @error('name') is-invalid @enderror"
                                   id="name" name="name" value="{{ old('name', $role->name) }}" required>
                        </div>
                        @error('name')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Use lowercase letters and underscores.</small>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="guard_name" class="form-label fw-semibold">Guard Name</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fas fa-shield-alt text-primary"></i></span>
                            <select name="guard_name" id="guard_name" class="form-select border-start-0 @error('guard_name') is-invalid @enderror">
                                <option value="web" {{ old('guard_name', $role->guard_name) == 'web' ? 'selected' : '' }}>web (default)</option>
                                <option value="api" {{ old('guard_name', $role->guard_name) == 'api' ? 'selected' : '' }}>api</option>
                            </select>
                        </div>
                        @error('guard_name')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold">Assign Permissions</label>
                    <div class="card border rounded-3 p-3" style="max-height: 400px; overflow-y: auto;">
                        <div class="row">
                            @forelse($permissions as $permission)
                                <div class="col-md-4 col-lg-3 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input permission-checkbox" type="checkbox"
                                               name="permissions[]" value="{{ $permission->id }}"
                                               id="perm_{{ $permission->id }}"
                                               {{ in_array($permission->id, old('permissions', $rolePermissions)) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="perm_{{ $permission->id }}">
                                            <i class="fas fa-key text-primary me-1"></i> {{ $permission->name }}
                                        </label>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12">
                                    <p class="text-muted text-center mb-0">No permissions available. Please create permissions first.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                    @error('permissions')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('roles.index') }}" class="btn btn-secondary rounded-pill px-4">
                        <i class="fas fa-times me-1"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">
                        <i class="fas fa-save me-1"></i> Update Role
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Optional: Select/Deselect all permissions
    // You can add a "Select All" checkbox if needed
</script>
@endpush
