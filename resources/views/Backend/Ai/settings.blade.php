@extends('Backend.master')

@section('content')
<style>
    .provider-card {
        transition: all 0.3s ease;
    }
    .provider-card:hover {
        background: var(--glass-bg-hover);
        box-shadow: var(--card-shadow);
    }
    .provider-status {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 4px 12px; border-radius: 20px;
        font-size: 0.78rem; font-weight: 600;
    }
    .provider-status.active { background: rgba(16,185,129,0.15); color: #059669; }
    .provider-status.inactive { background: rgba(239,68,68,0.12); color: #dc2626; }
    .provider-status .dot { width: 7px; height: 7px; border-radius: 50%; }
    .provider-status.active .dot { background: #059669; }
    .provider-status.inactive .dot { background: #dc2626; }
    .provider-desc { color: var(--text-muted); font-size: 0.82rem; margin-bottom: 0.75rem; }
    .free-badge {
        display: inline-block;
        background: rgba(16,185,129,0.15); color: #059669;
        padding: 2px 8px; border-radius: 4px;
        font-size: 0.72rem; font-weight: 600; margin-left: 6px;
    }
    .priority-label { color: #d97706; font-size: 0.75rem; font-weight: 600; margin-left: 6px; }
    .info-box {
        background: rgba(99,102,241,0.08);
        border: 1px solid rgba(99,102,241,0.2);
        border-radius: 0.75rem; padding: 0.85rem 1rem;
        font-size: 0.85rem; color: var(--text-secondary);
        margin-bottom: 1.25rem;
    }
    .test-result { display: flex; align-items: center; gap: 8px; padding: 5px 0; font-size: 0.85rem; }
</style>

<div class="container-fluid px-0" style="max-width:800px;">
    <div class="page-header">
        <div>
            <h2>AI Provider Settings</h2>
            <p>Configure AI providers for the chat assistant</p>
        </div>
        <a href="{{ route('ai.chat') }}" class="btn-soft"><i class="bi bi-chat-dots"></i> Back to Chat</a>
    </div>

    <div class="info-box">
        <strong>How it works:</strong> The system tries providers in order: Gemini &rarr; Groq &rarr; OpenRouter. When one fails, it automatically falls back to the next. Add at least one provider below. Response caching reduces API calls by caching recent answers.
    </div>

    <div id="alert-box"></div>

    <form id="settingsForm">
        <div class="glass-card mb-3 provider-card">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h5 style="font-weight:700;font-size:0.95rem;color:var(--text-primary);margin:0;">
                    Google Gemini <span class="free-badge">FREE</span> <span class="priority-label">Recommended</span>
                </h5>
                <span class="provider-status {{ $providers['gemini'] ? 'active' : 'inactive' }}">
                    <span class="dot"></span>
                    {{ $providers['gemini'] ? 'Active' : 'Not configured' }}
                </span>
            </div>
            <p class="provider-desc">1,500 requests/day, 15 requests/min. No credit card required. Best free tier available.</p>
            <div class="mb-2">
                <label class="form-label">API Key</label>
                <div class="d-flex gap-2">
                    <input type="password" name="gemini_api_key" class="form-control" placeholder="Paste your Gemini API key here" value="{{ config('services.gemini.api_key') }}">
                    <a href="https://aistudio.google.com/app/apikey" target="_blank" class="btn-soft" style="white-space:nowrap;">Get Free Key</a>
                </div>
                <small class="text-muted">Go to <a href="https://aistudio.google.com/app/apikey" target="_blank" style="color:var(--link-color);text-decoration:none;">aistudio.google.com</a> &rarr; Create API Key &rarr; Paste it here. No credit card needed.</small>
            </div>
        </div>

        <div class="glass-card mb-3 provider-card">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h5 style="font-weight:700;font-size:0.95rem;color:var(--text-primary);margin:0;">
                    Groq <span class="free-badge">FREE</span>
                </h5>
                <span class="provider-status {{ $providers['groq'] ? 'active' : 'inactive' }}">
                    <span class="dot"></span>
                    {{ $providers['groq'] ? 'Active' : 'Not configured' }}
                </span>
            </div>
            <p class="provider-desc">Ultra-fast inference, 30 requests/min. No credit card required. Great backup provider.</p>
            <div class="mb-2">
                <label class="form-label">API Key</label>
                <div class="d-flex gap-2">
                    <input type="password" name="groq_api_key" class="form-control" placeholder="Paste your Groq API key here" value="{{ config('services.groq.api_key') }}">
                    <a href="https://console.groq.com/keys" target="_blank" class="btn-soft" style="white-space:nowrap;">Get Free Key</a>
                </div>
                <small class="text-muted">Go to <a href="https://console.groq.com/keys" target="_blank" style="color:var(--link-color);text-decoration:none;">console.groq.com</a> &rarr; Create API Key &rarr; Paste it here. No credit card needed.</small>
            </div>
        </div>

        <div class="glass-card mb-3 provider-card">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h5 style="font-weight:700;font-size:0.95rem;color:var(--text-primary);margin:0;">
                    OpenRouter
                </h5>
                <span class="provider-status {{ $providers['openrouter'] ? 'active' : 'inactive' }}">
                    <span class="dot"></span>
                    {{ $providers['openrouter'] ? 'Active' : 'Not configured' }}
                </span>
            </div>
            <p class="provider-desc">Access to 70+ free models. Daily limit: 50-200 requests. Used as last fallback. Also used for document embeddings.</p>
            <div class="mb-2">
                <label class="form-label">API Key</label>
                <input type="text" class="form-control" disabled value="{{ config('services.openrouter.api_key') ? '••••••••' . substr(config('services.openrouter.api_key'), -8) : 'Not configured' }}">
                <small class="text-muted">OpenRouter key is configured in .env file. <a href="https://openrouter.ai" target="_blank" style="color:var(--link-color);text-decoration:none;">openrouter.ai</a></small>
            </div>
        </div>

        <div class="d-flex gap-2 mt-3">
            <button type="submit" class="dark-btn" id="saveBtn"><i class="bi bi-gear"></i> Save Settings</button>
            <button type="button" class="btn-soft" id="testBtn" onclick="testProviders()"><i class="bi bi-plug"></i> Test Connection</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
async function testProviders() {
    const btn = document.getElementById('testBtn');
    const alertBox = document.getElementById('alert-box');
    btn.textContent = 'Testing...';
    btn.disabled = true;
    alertBox.innerHTML = '';

    try {
        const resp = await fetch('{{ route("ai.settings.update") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ test_only: true })
        });
        const data = await resp.json();
        if (data.test_results) {
            let html = '<div class="glass-card" style="margin-bottom:1rem;padding:1rem;">';
            for (const [name, result] of Object.entries(data.test_results)) {
                const icon = result.status === 'ok' ? 'bi-check-circle-fill' : 'bi-x-circle-fill';
                const color = result.status === 'ok' ? '#059669' : '#dc2626';
                html += `<div class="test-result"><i class="bi ${icon}" style="color:${color}"></i> <strong>${name}</strong>: ${result.message}</div>`;
            }
            html += '</div>';
            alertBox.innerHTML = html;
        } else {
            alertBox.innerHTML = '<div class="alert alert-danger" style="padding:12px;border-radius:8px;margin-bottom:1rem;">' + (data.message || 'Test failed') + '</div>';
        }
    } catch(err) {
        alertBox.innerHTML = '<div class="alert alert-danger" style="padding:12px;border-radius:8px;margin-bottom:1rem;">Network error: ' + err.message + '</div>';
    }
    btn.textContent = 'Test Connection';
    btn.disabled = false;
}

document.getElementById('settingsForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('saveBtn');
    btn.textContent = 'Saving...';
    btn.disabled = true;

    const formData = new FormData(this);
    try {
        const resp = await fetch('{{ route("ai.settings.update") }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
            body: formData
        });
        const data = await resp.json();
        const alertBox = document.getElementById('alert-box');
        if (data.success) {
            alertBox.innerHTML = '<div class="alert alert-success" style="padding:12px;border-radius:8px;margin-bottom:1rem;">' + data.message + '</div>';
            setTimeout(() => location.reload(), 1500);
        } else {
            alertBox.innerHTML = '<div class="alert alert-danger" style="padding:12px;border-radius:8px;margin-bottom:1rem;">Error saving settings</div>';
        }
    } catch(err) {
        document.getElementById('alert-box').innerHTML = '<div class="alert alert-danger" style="padding:12px;border-radius:8px;margin-bottom:1rem;">Network error: ' + err.message + '</div>';
    }
    btn.textContent = 'Save Settings';
    btn.disabled = false;
});
</script>
@endpush
