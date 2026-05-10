@extends('Backend.master')

@section('content')
<div class="container-fluid px-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <div>
            <h1 class="display-6 fw-bold text-primary">
                <i class="fas fa-user-circle me-2"></i>User Details
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb bg-transparent p-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('users.index') }}">Users</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $user->name }}</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('users.index') }}" class="btn btn-secondary shadow-sm rounded-pill px-4">
            <i class="fas fa-arrow-left me-1"></i> Back to Users
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

    <!-- User Details Card -->
    <div class="card border-0 shadow-lg rounded-4">
        <div class="card-header bg-white py-3 rounded-top-4 border-bottom">
            <div>
                <i class="fas fa-info-circle text-primary me-2"></i>
                <strong>User Information</strong>
            </div>
        </div>
        <div class="card-body p-4">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold text-muted">Full Name</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-user text-primary"></i></span>
                        <input type="text" class="form-control border-start-0 bg-white" value="{{ $user->name }}" readonly disabled>
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold text-muted">Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-envelope text-primary"></i></span>
                        <input type="email" class="form-control border-start-0 bg-white" value="{{ $user->email }}" readonly disabled>
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold text-muted">User ID</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-hashtag text-primary"></i></span>
                        <input type="text" class="form-control border-start-0 bg-white" value="{{ $user->id }}" readonly disabled>
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold text-muted">Registered On</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-calendar-alt text-primary"></i></span>
                        <input type="text" class="form-control border-start-0 bg-white" value="{{ $user->created_at->format('d M Y, h:i A') }}" readonly disabled>
                    </div>
                </div>

                <div class="col-12 mb-3">
                    <label class="form-label fw-semibold text-muted">Assigned Roles</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-shield-alt text-primary"></i></span>
                        <div class="form-control border-start-0 bg-white" style="min-height: 46px;">
                            @forelse($user->roles as $role)
                                <span class="badge bg-primary bg-gradient rounded-pill px-3 py-2 me-1">
                                    <i class="fas fa-tag me-1"></i> {{ $role->name }}
                                </span>
                            @empty
                                <span class="text-muted">No roles assigned</span>
                            @endforelse
                        </div>
                    </div>
                </div>

                @if($user->created_at != $user->updated_at)
                <div class="col-12 mb-3">
                    <label class="form-label fw-semibold text-muted">Last Updated</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-edit text-primary"></i></span>
                        <input type="text" class="form-control border-start-0 bg-white" value="{{ $user->updated_at->format('d M Y, h:i A') }}" readonly disabled>
                    </div>
                </div>
                @endif
            </div>

            <div class="d-flex justify-content-end gap-2 mt-3">
                @can('edit users')
                    <a href="{{ route('users.edit', $user->id) }}" class="btn btn-warning rounded-pill px-4">
                        <i class="fas fa-edit me-1"></i> Edit User
                    </a>
                @endcan
                @can('delete users')
                    @if(!$user->roles->contains('name', 'admin'))
                        <form action="{{ route('users.destroy', $user->id) }}" method="POST" style="display:inline-block;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger rounded-pill px-4" onclick="return confirm('⚠️ Delete this user? This action cannot be undone.')">
                                <i class="fas fa-trash-alt me-1"></i> Delete User
                            </button>
                        </form>
                    @endif
                @endcan
            </div>
        </div>
    </div>
</div>
@endsection
