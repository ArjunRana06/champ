@extends('Backend.master')

@section('content')
<div class="container-fluid px-0">
    <div class="page-header">
        <div>
            <h2>Notifications</h2>
            <p>Stay updated on your study progress</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <button type="button" class="btn-soft" id="markAllReadBtn" onclick="markAllNotifReadIndex()"><i class="bi bi-check-all"></i> Mark All Read</button>
            <button type="button" class="btn-soft danger" id="clearAllBtn" onclick="clearAllNotifIndex()"><i class="bi bi-trash3"></i> Clear All</button>
        </div>
    </div>

    @if(session('success'))
        <div class="glass-card mb-4 d-flex align-items-center gap-3 py-3" style="border-left:4px solid #059669;">
            <i class="bi bi-check-circle-fill" style="color:#059669;font-size:1.2rem;"></i>
            <span style="color:var(--text-primary);font-size:0.9rem;">{{ session('success') }}</span>
        </div>
    @endif

    {{-- Filter tabs --}}
    <div class="d-flex gap-2 mb-4 flex-wrap">
        <a href="{{ route('notifications.index') }}"
           class="btn-soft {{ !request('type') ? 'active-filter' : '' }}"
           style="{{ !request('type') ? 'background:var(--card-accent);color:white;border-color:var(--card-accent);' : '' }}">
            All <span class="stat-badge up ms-1">{{ $successCount + $errorCount + $warningCount + $infoCount }}</span>
        </a>
        <a href="{{ route('notifications.index', ['type' => 'success']) }}"
           class="btn-soft {{ request('type') === 'success' ? 'active-filter' : '' }}"
           style="{{ request('type') === 'success' ? 'background:#059669;color:white;border-color:#059669;' : 'color:#059669;' }}">
            <i class="bi bi-check-circle-fill me-1"></i> Success
            <span class="stat-badge up ms-1">{{ $successCount }}</span>
        </a>
        <a href="{{ route('notifications.index', ['type' => 'info']) }}"
           class="btn-soft {{ request('type') === 'info' ? 'active-filter' : '' }}"
           style="{{ request('type') === 'info' ? 'background:#6366f1;color:white;border-color:#6366f1;' : 'color:#6366f1;' }}">
            <i class="bi bi-info-circle-fill me-1"></i> Info
            <span class="stat-badge up ms-1" style="background:#eef2ff;color:#6366f1;">{{ $infoCount }}</span>
        </a>
        <a href="{{ route('notifications.index', ['type' => 'warning']) }}"
           class="btn-soft {{ request('type') === 'warning' ? 'active-filter' : '' }}"
           style="{{ request('type') === 'warning' ? 'background:#d97706;color:white;border-color:#d97706;' : 'color:#d97706;' }}">
            <i class="bi bi-exclamation-triangle-fill me-1"></i> Warning
            <span class="stat-badge up ms-1" style="background:#fffbeb;color:#d97706;">{{ $warningCount }}</span>
        </a>
        <a href="{{ route('notifications.index', ['type' => 'error']) }}"
           class="btn-soft {{ request('type') === 'error' ? 'active-filter' : '' }}"
           style="{{ request('type') === 'error' ? 'background:#dc2626;color:white;border-color:#dc2626;' : 'color:#dc2626;' }}">
            <i class="bi bi-exclamation-circle-fill me-1"></i> Error
            <span class="stat-badge up ms-1" style="background:#fef2f2;color:#dc2626;">{{ $errorCount }}</span>
        </a>
    </div>

    {{-- Stats summary --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <div class="glass-card text-center py-3">
                <div style="font-size:1.8rem;font-weight:800;color:var(--card-accent);">{{ $unreadCount }}</div>
                <div style="font-size:0.8rem;color:var(--text-secondary);">Unread</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="glass-card text-center py-3">
                <div style="font-size:1.8rem;font-weight:800;color:#059669;">{{ $successCount }}</div>
                <div style="font-size:0.8rem;color:var(--text-secondary);">Success</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="glass-card text-center py-3">
                <div style="font-size:1.8rem;font-weight:800;color:#d97706;">{{ $warningCount }}</div>
                <div style="font-size:0.8rem;color:var(--text-secondary);">Warnings</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="glass-card text-center py-3">
                <div style="font-size:1.8rem;font-weight:800;color:#dc2626;">{{ $errorCount }}</div>
                <div style="font-size:0.8rem;color:var(--text-secondary);">Errors</div>
            </div>
        </div>
    </div>

    {{-- Notifications list --}}
    <div class="glass-card">
        @forelse($notifications as $notification)
            @php
                $meta = \App\Services\NotificationService::getTypeMeta($notification->type);
                $bg = $notification->is_read ? 'transparent' : $meta['bg'];
            @endphp
            <div class="d-flex align-items-start gap-3 p-3 notif-page-item {{ !$notification->is_read ? 'unread' : '' }}"
                 style="border-bottom:1px solid #f1f5f9; background:{{ $bg }};">
                <div class="notif-page-icon" style="background:{{ $meta['bg'] }};color:{{ $meta['color'] }};">
                    <i class="bi {{ $meta['icon'] }}"></i>
                </div>
                <div class="flex-grow-1" style="min-width:0;">
                    <div class="d-flex justify-content-between align-items-start gap-2">
                        <div>
                            <div class="notif-page-title" style="color:var(--text-primary);font-size:0.88rem;{{ !$notification->is_read ? 'font-weight:600;' : '' }}">
                                {{ $notification->title }}
                            </div>
                            @if($notification->body)
                                <div style="color:var(--text-secondary);font-size:0.82rem;margin-top:2px;">{{ $notification->body }}</div>
                            @endif
                            <div style="color:var(--text-muted);font-size:0.7rem;margin-top:4px;">
                                <i class="bi bi-clock me-1"></i>{{ $notification->created_at->diffForHumans() }}
                                <span class="ms-2 stat-badge" style="background:{{ $meta['bg'] }};color:{{ $meta['color'] }};">
                                    {{ ucfirst($notification->type) }}
                                </span>
                            </div>
                        </div>
                        <div class="d-flex gap-1 flex-shrink-0">
                            @if($notification->link)
                                <a href="{{ $notification->link }}" class="btn-soft py-1 px-2" style="font-size:0.7rem;" title="View">
                                    <i class="bi bi-arrow-right"></i>
                                </a>
                            @endif
                            @if(!$notification->is_read)
                                <button type="button" class="btn-soft py-1 px-2" style="font-size:0.7rem;color:var(--card-accent);" title="Mark read" onclick="markOneRead({{ $notification->id }}, this)">
                                    <i class="bi bi-check"></i>
                                </button>
                            @endif
                            <button type="button" class="btn-soft py-1 px-2 danger" style="font-size:0.7rem;" title="Delete" onclick="deleteOneNotif({{ $notification->id }}, this)">
                                <i class="bi bi-x"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-5">
                <div style="width:64px;height:64px;border-radius:50%;background:#eef2ff;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;">
                    <i class="bi bi-bell" style="font-size:1.8rem;color:#6366f1;"></i>
                </div>
                <p style="color:var(--text-secondary);font-size:0.95rem;margin-bottom:0.3rem;">No notifications yet</p>
                <p style="color:var(--text-muted);font-size:0.82rem;">Notifications will appear here as you use the app</p>
            </div>
        @endforelse
    </div>

    @if(method_exists($notifications, 'links'))
        <div class="mt-4 pagination-glass d-flex justify-content-center">
            {{ $notifications->links() }}
        </div>
    @endif
</div>

<style>
    .notif-page-item {
        transition: background 0.2s;
    }
    .notif-page-item:hover {
        background: var(--table-row-hover) !important;
    }
    .notif-page-item.unread {
        border-left: 3px solid var(--card-accent);
    }
    .notif-page-icon {
        width: 40px; height: 40px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center; flex-shrink: 0;
        font-size: 1rem;
    }
    .notif-page-title {
        display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden;
    }
    .active-filter {
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
</style>

@push('scripts')
<script>
    function markAllNotifReadIndex() {
        const btn = document.getElementById('markAllReadBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span> Marking...';
        fetch('{{ route("notifications.read-all") }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(() => {
            showToast('All notifications marked as read', 'success');
            location.reload();
        })
        .catch(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check-all"></i> Mark All Read';
        });
    }

    function clearAllNotifIndex() {
        if (!confirm('Clear all notifications?')) return;
        const btn = document.getElementById('clearAllBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span> Clearing...';
        fetch('{{ route("notifications.clear-all") }}', {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(r => r.json())
        .then(() => {
            showToast('All notifications cleared', 'info');
            location.reload();
        })
        .catch(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-trash3"></i> Clear All';
        });
    }

    function markOneRead(id, btn) {
        btn.disabled = true;
        fetch('/notifications/' + id + '/read', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(() => {
            const item = btn.closest('.notif-page-item');
            if (item) {
                item.classList.remove('unread');
                item.style.background = 'transparent';
                btn.remove();
            }
            showToast('Marked as read', 'success');
        })
        .catch(() => { btn.disabled = false; });
    }

    function deleteOneNotif(id, btn) {
        if (!confirm('Delete this notification?')) return;
        btn.disabled = true;
        fetch('/notifications/' + id, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(() => {
            const item = btn.closest('.notif-page-item');
            if (item) item.remove();
            showToast('Notification deleted', 'info');
        })
        .catch(() => { btn.disabled = false; });
    }
</script>
@endpush
@endsection
