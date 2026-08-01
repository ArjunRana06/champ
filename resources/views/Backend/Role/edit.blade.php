@extends('Backend.master')

@section('content')
<div class="container-fluid px-4">
    <div class="page-header">
        <div>
            <h2>Edit Role</h2>
            <p>{{ $role->name }} — Permissions</p>
        </div>
        <a href="{{ route('roles.index') }}" class="btn-soft"><i class="bi bi-arrow-left"></i> Back to Roles</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3" role="alert">
            <i class="bi bi-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3" role="alert">
            <i class="bi bi-exclamation-circle me-2"></i> Please fix the following errors:
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form action="{{ route('roles.update', $role->id) }}" method="POST" class="form-glass">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="name" class="form-label">Role Name <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-tag"></i></span>
                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                               id="name" name="name" value="{{ old('name', $role->name) }}" required>
                    </div>
                    @error('name')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">Use lowercase letters and underscores.</small>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="guard_name" class="form-label">Guard Name</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-shield-check"></i></span>
                        <select name="guard_name" id="guard_name" class="form-select @error('guard_name') is-invalid @enderror">
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
                <label class="form-label">Assign Permissions</label>
                <div style="max-height:400px;overflow-y:auto;background:var(--input-bg);border:1.5px solid var(--input-border);border-radius:1rem;padding:0.75rem;">
                    <div class="row">
                        @forelse($permissions as $permission)
                            <div class="col-md-4 col-lg-3 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input permission-checkbox" type="checkbox"
                                           name="permissions[]" value="{{ $permission->id }}"
                                           id="perm_{{ $permission->id }}"
                                           {{ in_array($permission->id, old('permissions', $rolePermissions)) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="perm_{{ $permission->id }}">
                                        <i class="bi bi-key me-1"></i> {{ $permission->name }}
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
                <a href="{{ route('roles.index') }}" class="btn-soft"><i class="bi bi-x-lg"></i> Cancel</a>
                <button type="submit" class="dark-btn"><i class="bi bi-save"></i> Update Role</button>
            </div>
        </form>
</div>
@endsection
