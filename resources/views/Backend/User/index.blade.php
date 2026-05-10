@extends('Backend.master')

@section('content')
<div class="container-fluid px-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <div>
            <h1 class="display-6 fw-bold text-primary">
                <i class="fas fa-users me-2"></i>User Management
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb bg-transparent p-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Users</li>
                </ol>
            </nav>
        </div>
        @can('add user')
            <button class="btn btn-primary shadow-sm rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#addUserModal">
                <i class="fas fa-plus-circle me-1"></i> Add User
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
                            <h6 class="text-white-50">Total Users</h6>
                            {{-- <h2 class="fw-bold mb-0">{{ $users->total() }}</h2> --}}
                        </div>
                        <i class="fas fa-user-friends fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Users Table Card -->
    <div class="card border-0 shadow-lg rounded-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center rounded-top-4 border-bottom">
            <div>
                <i class="fas fa-table text-primary me-2"></i>
                <strong>Users List</strong>
            </div>
            <div class="input-group w-25">
                <span class="input-group-text bg-transparent border-end-0"><i class="fas fa-search"></i></span>
                <input type="text" id="searchInput" class="form-control border-start-0" placeholder="Search user...">
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="usersTable">
                    <thead class="bg-light">
                        <tr>
                            <th class="border-0 py-3">ID</th>
                            <th class="border-0 py-3">Name</th>
                            <th class="border-0 py-3">Email</th>
                            <th class="border-0 py-3">Roles</th>
                            <th class="border-0 py-3">Registered On</th>
                            <th class="border-0 py-3 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            <tr>
                                <td class="fw-bold text-primary">{{ $user->id }}</td>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    @foreach ($user->roles as $role)
                                        <span class="badge bg-primary bg-gradient rounded-pill px-3 py-1 me-1">
                                            <i class="fas fa-tag me-1"></i> {{ $role->name }}
                                        </span>
                                    @endforeach
                                </td>
                                <td>{{ $user->created_at->format('d M Y, h:i A') }}</td>
                                <td class="text-center">
                                    @can('view users')
                                        <a href="{{ route('users.show', $user->id) }}" class="btn btn-sm btn-outline-info rounded-pill px-3 me-1">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                    @endcan
                                    @can('edit users')
                                        <a href="{{ route('users.edit', $user->id) }}" class="btn btn-sm btn-outline-warning rounded-pill px-3 me-1">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                    @endcan
                                    @php
                                        $isAdmin = $user->roles->contains('name', 'admin');
                                    @endphp
                                    @if ($isAdmin)
                                        <span class="text-muted"><i class="fas fa-shield-alt me-1"></i>Admin cannot be deleted</span>
                                    @else
                                        @can('delete users')
                                            <form action="{{ route('users.destroy', $user->id) }}" method="POST" style="display:inline-block;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3"
                                                        onclick="return confirm('⚠️ Delete this user? This action cannot be undone.')">
                                                    <i class="fas fa-trash-alt"></i> Delete
                                                </button>
                                            </form>
                                        @endcan
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <i class="fas fa-database fa-3x text-muted mb-3"></i>
                                    <p class="text-muted mb-0">No users found. Click "Add User" to create one.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white py-3 rounded-bottom-4">
            <div class="d-flex justify-content-center">
                {{-- {{ $users->links() }} --}}
            </div>
        </div>
    </div>
</div>

<!-- Add User Modal (Design upgraded, functionality unchanged) -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <form action="{{ route('users.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-primary text-white rounded-top-4 border-0">
                    <h5 class="modal-title" id="addUserModalLabel">
                        <i class="fas fa-user-plus me-2"></i>Add New User
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="name" class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fas fa-user text-primary"></i></span>
                            <input type="text" class="form-control border-start-0" id="name" name="name" required placeholder="e.g., John Doe">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold">Email Address <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fas fa-envelope text-primary"></i></span>
                            <input type="email" class="form-control border-start-0" id="email" name="email" required placeholder="user@example.com">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label fw-semibold">Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fas fa-lock text-primary"></i></span>
                            <input type="password" class="form-control border-start-0" id="password" name="password" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label fw-semibold">Confirm Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fas fa-check-circle text-primary"></i></span>
                            <input type="password" class="form-control border-start-0" id="password_confirmation" name="password_confirmation" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Assign Role</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fas fa-shield-alt text-primary"></i></span>
                            <select name="role" class="form-select border-start-0" required>
                                <option value="">-- Select Role --</option>
                                @foreach ($roles as $role)
                                    <option value="{{ $role->name }}">{{ $role->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0 rounded-bottom-4">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">
                        <i class="fas fa-save me-1"></i> Create User
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
        let rows = document.querySelectorAll('#usersTable tbody tr');
        rows.forEach(row => {
            let text = row.innerText.toLowerCase();
            row.style.display = text.includes(value) ? '' : 'none';
        });
    });
</script>
@endsection

@push('scripts')
    {{-- Optional: DataTables can still be used if uncommented --}}
    <script>
        // $(document).ready(function() {
        //     $('#usersTable').DataTable();
        // });
    </script>
@endpush
