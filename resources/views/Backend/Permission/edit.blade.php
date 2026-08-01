@extends('Backend.master')

@section('content')
<div class="container-fluid px-4">
    <div class="page-header">
        <div>
            <h2>Edit Permission</h2>
            <p>{{ $permission->name }}</p>
        </div>
        <a href="{{ route('permissions.index') }}" class="btn-soft"><i class="bi bi-arrow-left"></i> Back to Permissions</a>
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

    <form action="{{ route('permissions.update', $permission->id) }}" method="POST" class="form-glass">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="name" class="form-label">Permission Name <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-tag"></i></span>
                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                               id="name" name="name" value="{{ old('name', $permission->name) }}" required>
                    </div>
                    @error('name')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">Use lowercase letters and underscores. Example: create_user, edit_role</small>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="guard_name" class="form-label">Guard Name</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-shield-check"></i></span>
                        <select name="guard_name" id="guard_name" class="form-select @error('guard_name') is-invalid @enderror">
                            <option value="web" {{ old('guard_name', $permission->guard_name) == 'web' ? 'selected' : '' }}>web (default)</option>
                            <option value="api" {{ old('guard_name', $permission->guard_name) == 'api' ? 'selected' : '' }}>api</option>
                        </select>
                    </div>
                    @error('guard_name')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-3">
                <a href="{{ route('permissions.index') }}" class="btn-soft"><i class="bi bi-x-lg"></i> Cancel</a>
                <button type="submit" class="dark-btn"><i class="bi bi-save"></i> Update Permission</button>
            </div>
        </form>
</div>
@endsection
