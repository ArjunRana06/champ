@extends('Backend.master')

@section('content')
<div class="container-fluid px-4">
    <div class="page-header">
        <div>
            <h2>User Details</h2>
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

    <div class="form-glass">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Full Name</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                    <input type="text" class="form-control" value="{{ $user->name }}" readonly disabled>
                </div>
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Email Address</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                    <input type="email" class="form-control" value="{{ $user->email }}" readonly disabled>
                </div>
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">User ID</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-hash"></i></span>
                    <input type="text" class="form-control" value="{{ $user->id }}" readonly disabled>
                </div>
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Registered On</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-calendar3"></i></span>
                    <input type="text" class="form-control" value="{{ $user->created_at->format('d M Y, h:i A') }}" readonly disabled>
                </div>
            </div>

            <div class="col-12 mb-3">
                <label class="form-label">Assigned Roles</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-shield-check"></i></span>
                    <div class="form-control" style="min-height:46px;">
                        @forelse($user->roles as $role)
                            <span class="stat-badge up me-1">
                                <i class="bi bi-tag me-1"></i> {{ $role->name }}
                            </span>
                        @empty
                            <span class="text-muted">No roles assigned</span>
                        @endforelse
                    </div>
                </div>
            </div>

            @if($user->created_at != $user->updated_at)
            <div class="col-12 mb-3">
                <label class="form-label">Last Updated</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-pencil"></i></span>
                    <input type="text" class="form-control" value="{{ $user->updated_at->format('d M Y, h:i A') }}" readonly disabled>
                </div>
            </div>
            @endif
        </div>

        <div class="d-flex justify-content-end gap-2 mt-3">
            @can('edit users')
                <a href="{{ route('users.edit', $user->id) }}" class="btn-soft warning"><i class="bi bi-pencil"></i> Edit User</a>
            @endcan
            @can('delete users')
                @if(!$user->roles->contains('name', 'admin'))
                    <form action="{{ route('users.destroy', $user->id) }}" method="POST" style="display:inline-block;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-soft danger" onclick="return confirm('⚠️ Delete this user? This action cannot be undone.')">
                            <i class="bi bi-trash"></i> Delete User
                        </button>
                    </form>
                @endif
            @endcan
        </div>
    </div>
</div>
@endsection
