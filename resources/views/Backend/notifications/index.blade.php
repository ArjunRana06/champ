@extends('Backend.master')

@section('content')
<div class="container-fluid px-0">
    <div class="page-header">
        <div>
            <h2>Notifications</h2>
            <p>Stay updated on your study progress</p>
        </div>
        <form action="{{ route('notifications.read-all') }}" method="POST" style="display:inline;">
            @csrf
            <button type="submit" class="btn-soft"><i class="bi bi-check-all"></i> Mark All as Read</button>
        </form>
    </div>

    @if(session('success'))
        <div class="glass-card mb-4 d-flex align-items-center gap-3 py-3" style="border-left:4px solid #059669;">
            <i class="bi bi-check-circle-fill" style="color:#059669;font-size:1.2rem;"></i>
            <span style="color:#1e1b4b;font-size:0.9rem;">{{ session('success') }}</span>
        </div>
    @endif

    <div class="glass-card">
        @forelse($notifications as $notification)
            <div class="d-flex align-items-start gap-3 p-3 {{ !$notification->is_read ? 'fw-bold' : '' }}" style="border-bottom:1px solid #f1f5f9; {{ !$notification->is_read ? 'background:rgba(99,102,241,0.03);' : '' }}">
                <div style="width:38px;height:38px;border-radius:50%;
                    background:{{ $notification->type === 'doc_ready' ? '#ecfdf5' : ($notification->type === 'shared' ? '#eef2ff' : ($notification->type === 'quiz_reminder' ? '#fffbeb' : '#f8fafc')) }};
                    display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="bi bi-{{ $notification->type === 'doc_ready' ? 'file-earmark-check' : ($notification->type === 'shared' ? 'people' : ($notification->type === 'quiz_reminder' ? 'patch-question' : 'info-circle')) }}"
                       style="color:{{ $notification->type === 'doc_ready' ? '#059669' : ($notification->type === 'shared' ? '#6366f1' : ($notification->type === 'quiz_reminder' ? '#d97706' : '#6b7280')) }};"></i>
                </div>
                <div class="flex-grow-1" style="min-width:0;">
                    <div style="color:#1e1b4b;font-size:0.88rem;">{{ $notification->title }}</div>
                    <small style="color:#6b7280;">{{ $notification->body }}</small>
                    <br>
                    <small style="color:#9ca3af;font-size:0.7rem;">{{ $notification->created_at->diffForHumans() }}</small>
                </div>
                <div>
                    @if($notification->link)
                        <a href="{{ $notification->link }}" class="btn-soft py-1 px-2" style="font-size:0.75rem;"><i class="bi bi-arrow-right"></i></a>
                    @endif
                    @if(!$notification->is_read)
                        <form action="{{ route('notifications.read', $notification->id) }}" method="POST" style="display:inline;">
                            @csrf
                            <button type="submit" class="btn-soft py-1 px-2" style="font-size:0.75rem;color:#6366f1;"><i class="bi bi-check"></i></button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <div class="text-center py-5">
                <i class="bi bi-bell" style="font-size:3rem;color:#c7d2fe;"></i>
                <p class="mt-3" style="color:#6b7280;">No notifications yet.</p>
            </div>
        @endforelse
    </div>

    @if(method_exists($notifications, 'links'))
        <div class="mt-4 pagination-glass d-flex justify-content-center">
            {{ $notifications->links() }}
        </div>
    @endif
</div>
@endsection
