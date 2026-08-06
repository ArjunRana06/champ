@props(['id', 'title', 'icon' => 'bi-window-stack', 'open' => false, 'maxWidth' => '520px'])

@push('styles')
<style>
    .app-modal-backdrop {
        position: fixed; inset: 0; z-index: 2000;
        display: flex; align-items: center; justify-content: center;
        padding: 1rem; background: rgba(15,23,42,0.55); backdrop-filter: blur(6px);
    }
    .app-modal-backdrop[hidden] { display: none; }
    .app-modal {
        width: 100%; max-width: {{ $maxWidth }}; max-height: 90vh; overflow-y: auto;
        background: var(--glass-bg); backdrop-filter: blur(24px) saturate(1.8);
        border: 1px solid var(--glass-border); border-radius: 1.5rem;
        box-shadow: var(--card-shadow);
        animation: appModalPop 0.22s cubic-bezier(0.34, 1.56, 0.64, 1);
    }
    .app-modal-header {
        display: flex; align-items: center; justify-content: space-between; gap: 1rem;
        padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--divider-color);
        position: sticky; top: 0; background: var(--glass-bg);
        backdrop-filter: blur(24px) saturate(1.8); border-radius: 1.5rem 1.5rem 0 0; z-index: 1;
    }
    .app-modal-header h5 { margin: 0; font-weight: 700; color: var(--text-primary); font-size: 1.05rem; }
    .app-modal-header h5 i { color: var(--card-accent); margin-right: 0.5rem; }
    .app-modal-close {
        background: none; border: none; color: var(--text-muted); font-size: 1.5rem; line-height: 1;
        cursor: pointer; padding: 0.05rem 0.4rem; border-radius: 0.6rem; transition: all 0.15s;
    }
    .app-modal-close:hover { color: var(--text-primary); background: var(--badge-bg); }
    .app-modal-body { padding: 1.5rem; }
    .app-modal-footer {
        padding: 1rem 1.5rem; border-top: 1px solid var(--divider-color);
        display: flex; justify-content: flex-end; gap: 0.5rem;
        position: sticky; bottom: 0; background: var(--glass-bg);
        backdrop-filter: blur(24px) saturate(1.8); border-radius: 0 0 1.5rem 1.5rem;
    }
    @keyframes appModalPop {
        from { transform: translateY(12px) scale(0.98); }
        to { transform: translateY(0) scale(1); }
    }
    body.app-modal-open { overflow: hidden; }
</style>
@endpush

<div class="app-modal-backdrop" id="{{ $id }}" data-app-modal data-auto-open="{{ $open ? 'true' : 'false' }}" hidden>
    <div class="app-modal" role="dialog" aria-modal="true" aria-label="{{ $title }}">
        <div class="app-modal-header">
            <h5><i class="bi {{ $icon }}"></i>{{ $title }}</h5>
            <button type="button" class="app-modal-close" data-app-close="true" aria-label="Close">&times;</button>
        </div>
        <div class="app-modal-body">{{ $slot }}</div>
        @isset($footer)
            <div class="app-modal-footer">{{ $footer }}</div>
        @endisset
    </div>
</div>

@push('scripts')
<script>
(function () {
    const backdropId = '{{ $id }}';
    const backdrop = document.getElementById(backdropId);

    function openModal() {
        if (!backdrop) return;
        backdrop.hidden = false;
        document.body.classList.add('app-modal-open');
        const focus = backdrop.querySelector('input, select, textarea');
        if (focus) setTimeout(() => focus.focus(), 60);
    }
    function closeModal() {
        if (!backdrop) return;
        backdrop.hidden = true;
        document.body.classList.remove('app-modal-open');
    }
    window['open' + '{{ ucfirst($id) }}'] = openModal;
    window['close' + '{{ ucfirst($id) }}'] = closeModal;

    document.addEventListener('click', function (e) {
        const opener = e.target.closest('[data-open="' + backdropId + '"]');
        if (opener) { e.preventDefault(); openModal(); }
        const closer = e.target.closest('[data-app-close="true"]');
        if (closer) { closeModal(); }
        if (e.target === backdrop) { closeModal(); }
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && backdrop && !backdrop.hidden) closeModal();
    });

    if (backdrop && backdrop.getAttribute('data-auto-open') === 'true') {
        setTimeout(openModal, 120);
    }
})();
</script>
@endpush
