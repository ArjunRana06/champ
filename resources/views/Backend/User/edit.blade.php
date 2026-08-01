@extends('Backend.master')

@section('content')
<div class="container-fluid px-4">
    <div class="page-header">
        <div>
            <h2>Edit User</h2>
            <p>{{ $user->name }}</p>
        </div>
        <a href="{{ route('users.index') }}" class="btn-soft"><i class="bi bi-arrow-left"></i> Back to Users</a>
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

    <form action="{{ route('users.update', $user->id) }}" method="POST" class="form-glass">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                               id="name" name="name" value="{{ old('name', $user->name) }}" required>
                    </div>
                    @error('name')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                        <input type="email" class="form-control @error('email') is-invalid @enderror"
                               id="email" name="email" value="{{ old('email', $user->email) }}" required>
                    </div>
                    @error('email')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="password" class="form-label">Password <span class="text-muted">(Leave blank to keep current)</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input type="password" class="form-control @error('password') is-invalid @enderror"
                               id="password" name="password">
                    </div>
                    @error('password')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="password_confirmation" class="form-label">Confirm Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-check-circle"></i></span>
                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation">
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label">Assign Role</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-shield-check"></i></span>
                    <select name="role" class="form-select @error('role') is-invalid @enderror" required>
                        <option value="">-- Select Role --</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role->name }}"
                                {{ (old('role', $user->roles->first()->name ?? '') == $role->name) ? 'selected' : '' }}>
                                {{ $role->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @error('role')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('users.index') }}" class="btn-soft"><i class="bi bi-x-lg"></i> Cancel</a>
                <button type="submit" class="dark-btn"><i class="bi bi-save"></i> Update User</button>
            </div>
        </form>
</div>
@endsection
