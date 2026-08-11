<div class="glass-card text-center py-5" style="max-width:560px;margin:2rem auto;">
    <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3" style="width:72px;height:72px;background:var(--badge-bg);">
        <i class="bi bi-people" style="font-size:2.2rem;color:var(--card-accent);"></i>
    </div>
    <h4 style="color:var(--text-primary);font-weight:800;">Study Group Required</h4>
    <p style="color:var(--text-secondary);max-width:380px;margin:0.75rem auto 0;">
        {{ $message ?? 'This section is only available to students who belong to a study group. Join or create a group to share and browse questions with your peers.' }}
    </p>
    <div class="mt-4">
        <a href="{{ route('study-groups.index') }}" class="dark-btn py-2 px-4" style="font-size:0.85rem;display:inline-flex;align-items:center;gap:0.5rem;">
            <i class="bi bi-plus-circle"></i> Join or Create a Group
        </a>
    </div>
</div>
