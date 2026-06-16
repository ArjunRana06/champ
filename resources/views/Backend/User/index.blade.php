@extends('Backend.master')

@section('content')
<div class="container-fluid px-0">
    <div class="page-header">
        <div>
            <h2>User Management</h2>
            <p>Manage users, roles, and permissions</p>
        </div>
        @can('add user')
            <button class="dark-btn" data-bs-toggle="modal" data-bs-target="#addUserModal">
                <i class="bi bi-person-plus"></i> Add User
            </button>
        @endcan
    </div>

    @if(session('success'))
        <div class="glass-card mb-4 d-flex align-items-center gap-3 py-3" style="border-left:4px solid #059669;">
            <i class="bi bi-check-circle-fill" style="color:#059669;font-size:1.2rem;"></i>
            <span style="color:var(--text-primary);font-size:0.9rem;">{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="glass-card mb-4 d-flex align-items-center gap-3 py-3" style="border-left:4px solid #dc2626;">
            <i class="bi bi-exclamation-triangle-fill" style="color:#dc2626;font-size:1.2rem;"></i>
            <span style="color:var(--text-primary);font-size:0.9rem;">{{ session('error') }}</span>
        </div>
    @endif

    <!-- Stats -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="glass-card d-flex align-items-center gap-3">
                <div style="width:50px;height:50px;border-radius:16px;background:linear-gradient(135deg,#6366f1,#818cf8);display:flex;align-items:center;justify-content:center;font-size:1.5rem;color:white;box-shadow:0 8px 16px rgba(99,102,241,0.2);flex-shrink:0;">
                    <i class="bi bi-people-fill"></i>
                </div>
                <div>
                    <h6 style="color:var(--card-accent);font-size:0.65rem;text-transform:uppercase;letter-spacing:0.05em;font-weight:700;margin:0;">Total Users</h6>
                    <h3 class="fw-bold mb-0" style="color:var(--text-primary);font-size:1.7rem;">{{ $totalUsers }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Users Table -->
    <div class="glass-card p-0 overflow-hidden">
        <div class="d-flex justify-content-between align-items-center px-4 py-3" style="border-bottom:1.5px solid #e5e7eb;">
            <div style="color:var(--text-primary);font-weight:700;font-size:0.9rem;">
                <i class="bi bi-table me-2" style="color:var(--card-accent);"></i> Users List
            </div>
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-search" style="color:var(--text-muted);"></i>
                <input type="text" id="searchInput" placeholder="Search user..." style="border:1.5px solid #e5e7eb;border-radius:40px;padding:0.4rem 0.8rem;font-size:0.8rem;width:200px;outline:none;font-family:'Inter',sans-serif;">
            </div>
        </div>
        <div class="table-responsive">
            <table class="glass-table" id="usersTable">
                <thead>
                    <tr><th>ID</th><th>Name</th><th>Email</th><th>Roles</th><th>Registered</th><th class="text-center">Actions</th></tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td style="color:var(--card-accent);font-weight:700;">{{ $user->id }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span style="width:32px;height:32px;border-radius:8px;background:linear-gradient(135deg,#6366f1,#a855f7);display:flex;align-items:center;justify-content:center;color:white;font-weight:600;font-size:0.75rem;flex-shrink:0;">
                                        {{ substr($user->name, 0, 1) }}
                                    </span>
                                    {{ $user->name }}
                                </div>
                            </td>
                            <td style="color:var(--text-secondary);">{{ $user->email }}</td>
                            <td>
                                @foreach ($user->roles as $role)
                                    <span style="font-size:0.7rem;padding:0.15rem 0.6rem;border-radius:20px;background:#eef2ff;color:var(--card-accent);display:inline-block;margin-right:0.25rem;">
                                        <i class="bi bi-tag me-1"></i> {{ $role->name }}
                                    </span>
                                @endforeach
                            </td>
                            <td style="color:var(--text-secondary);font-size:0.8rem;">{{ $user->created_at->format('d M Y, h:i A') }}</td>
                            <td class="text-center">
                                <div class="d-flex gap-1 justify-content-center">
                                    @can('view users')
                                        <a href="{{ route('users.show', $user->id) }}" class="btn-soft py-1 px-2" style="font-size:0.75rem;" title="View"><i class="bi bi-eye"></i></a>
                                    @endcan
                                    @can('edit users')
                                        <a href="{{ route('users.edit', $user->id) }}" class="btn-soft py-1 px-2" style="font-size:0.75rem;" title="Edit"><i class="bi bi-pencil"></i></a>
                                    @endcan
                                    @php $isAdmin = $user->roles->contains('name', 'admin'); @endphp
                                    @if ($isAdmin)
                                        <span style="color:var(--text-muted);font-size:0.75rem;padding:0.25rem 0.5rem;" title="Admin cannot be deleted"><i class="bi bi-shield-check"></i></span>
                                    @else
                                        @can('delete users')
                                            <form action="{{ route('users.destroy', $user->id) }}" method="POST" style="display:inline-block;">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn-soft py-1 px-2" style="font-size:0.75rem;color:#dc2626;" title="Delete" onclick="return confirm('⚠️ Delete this user? This action cannot be undone.')">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        @endcan
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5" style="color:var(--text-muted);">
                                <i class="bi bi-database" style="font-size:2.5rem;color:#c7d2fe;display:block;margin-bottom:0.5rem;"></i>
                                No users found. Click "Add User" to create one.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background:rgba(255,255,255,0.85);backdrop-filter:blur(24px) saturate(1.8);border:1px solid rgba(255,255,255,0.5);border-radius:1.5rem;box-shadow:0 25px 60px -12px rgba(0,0,0,0.15);">
            <form action="{{ route('users.store') }}" method="POST">
                @csrf
                <div class="modal-header border-0" style="padding:1.5rem 1.5rem 0;">
                    <h5 class="modal-title" style="color:var(--text-primary);font-weight:800;">
                        <i class="bi bi-person-plus me-2" style="color:var(--card-accent);"></i> Add New User
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" style="padding:1.5rem;">
                    <div class="mb-3">
                        <label style="font-size:0.7rem;font-weight:600;text-transform:uppercase;letter-spacing:0.05em;color:var(--card-accent);margin-bottom:0.4rem;display:block;">Full Name *</label>
                        <input type="text" class="form-control" name="name" required placeholder="John Doe" style="background:white;border:1.5px solid #e5e7eb;border-radius:1rem;padding:0.7rem 1.1rem;font-size:0.9rem;width:100%;font-family:'Inter',sans-serif;">
                    </div>
                    <div class="mb-3">
                        <label style="font-size:0.7rem;font-weight:600;text-transform:uppercase;letter-spacing:0.05em;color:var(--card-accent);margin-bottom:0.4rem;display:block;">Email *</label>
                        <input type="email" class="form-control" name="email" required placeholder="user@example.com" style="background:white;border:1.5px solid #e5e7eb;border-radius:1rem;padding:0.7rem 1.1rem;font-size:0.9rem;width:100%;font-family:'Inter',sans-serif;">
                    </div>
                    <div class="mb-3">
                        <label style="font-size:0.7rem;font-weight:600;text-transform:uppercase;letter-spacing:0.05em;color:var(--card-accent);margin-bottom:0.4rem;display:block;">Password *</label>
                        <input type="password" class="form-control" name="password" required style="background:white;border:1.5px solid #e5e7eb;border-radius:1rem;padding:0.7rem 1.1rem;font-size:0.9rem;width:100%;font-family:'Inter',sans-serif;">
                    </div>
                    <div class="mb-3">
                        <label style="font-size:0.7rem;font-weight:600;text-transform:uppercase;letter-spacing:0.05em;color:var(--card-accent);margin-bottom:0.4rem;display:block;">Confirm Password</label>
                        <input type="password" class="form-control" name="password_confirmation" required style="background:white;border:1.5px solid #e5e7eb;border-radius:1rem;padding:0.7rem 1.1rem;font-size:0.9rem;width:100%;font-family:'Inter',sans-serif;">
                    </div>
                    <div class="mb-3">
                        <label style="font-size:0.7rem;font-weight:600;text-transform:uppercase;letter-spacing:0.05em;color:var(--card-accent);margin-bottom:0.4rem;display:block;">Assign Role</label>
                        <select name="role" class="form-select" required style="background:white;border:1.5px solid #e5e7eb;border-radius:1rem;padding:0.7rem 1.1rem;font-size:0.9rem;width:100%;font-family:'Inter',sans-serif;">
                            <option value="">-- Select Role --</option>
                            @foreach ($roles as $role)
                                <option value="{{ $role->name }}">{{ $role->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0" style="padding:0 1.5rem 1.5rem;">
                    <button type="button" class="btn-soft" data-bs-dismiss="modal"><i class="bi bi-x"></i> Cancel</button>
                    <button type="submit" class="dark-btn"><i class="bi bi-check-lg"></i> Create User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.getElementById('searchInput')?.addEventListener('keyup', function() {
        const value = this.value.toLowerCase();
        document.querySelectorAll('#usersTable tbody tr').forEach(row => {
            row.style.display = row.innerText.toLowerCase().includes(value) ? '' : 'none';
        });
    });
</script>
@endsection
