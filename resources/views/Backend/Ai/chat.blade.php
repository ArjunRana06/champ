@extends('Backend.master')

@section('content')
<style>
    .chat-page {
        height: calc(100vh - 160px);
        min-height: 400px;
        display: flex;
        flex-direction: column;
        max-width: 900px;
        margin: 0 auto;
    }
    .chat-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.75rem 1rem;
        margin-bottom: 0.75rem;
        flex-shrink: 0;
    }
    .chat-header-left {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    .chat-header-avatar {
        width: 40px; height: 40px;
        border-radius: 12px;
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        display: flex; align-items: center; justify-content: center;
        color: white; font-size: 1.1rem;
    }
    .chat-header-info h6 {
        margin: 0; font-weight: 600; font-size: 0.9rem;
        color: var(--text-primary);
    }
    .chat-header-info small {
        color: var(--text-muted); font-size: 0.75rem;
    }
    .chat-header-actions {
        display: flex; gap: 0.5rem; align-items: center;
    }
    .chat-header-actions select {
        font-size: 0.8rem;
        padding: 0.3rem 0.6rem;
        border-radius: 0.6rem;
        border: 1px solid var(--input-border);
        background: var(--input-bg);
        color: var(--text-primary);
    }


    .chat-messages {
        flex: 1;
        overflow-y: auto;
        padding: 1rem 0.5rem;
        scroll-behavior: smooth;
    }

    .chat-msg {
        display: flex;
        gap: 0.6rem;
        margin-bottom: 1rem;
        max-width: 85%;
        animation: msgIn 0.3s ease;
    }
    .chat-msg.user { margin-left: auto; flex-direction: row-reverse; }
    .chat-msg.bot { margin-right: auto; }

    @keyframes msgIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .chat-msg-avatar {
        width: 32px; height: 32px;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: 0.8rem;
        flex-shrink: 0;
    }
    .chat-msg.bot .chat-msg-avatar {
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        color: white;
    }
    .chat-msg.user .chat-msg-avatar {
        background: var(--badge-bg);
        color: var(--card-accent);
    }

    .chat-msg-body {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }

    .chat-msg-bubble {
        padding: 0.75rem 1rem;
        border-radius: 1rem;
        font-size: 0.88rem;
        line-height: 1.6;
        word-wrap: break-word;
        position: relative;
    }
    .chat-msg.bot .chat-msg-bubble {
        background: var(--glass-bg);
        backdrop-filter: blur(12px);
        border: 1px solid var(--glass-border);
        border-top-left-radius: 0.25rem;
        color: var(--text-primary);
    }
    .chat-msg.user .chat-msg-bubble {
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        color: white;
        border-top-right-radius: 0.25rem;
    }

    .chat-msg-bubble p { margin: 0 0 0.5rem; }
    .chat-msg-bubble p:last-child { margin-bottom: 0; }
    .chat-msg-bubble ul, .chat-msg-bubble ol { margin: 0.4rem 0; padding-left: 1.5rem; }
    .chat-msg-bubble li { margin-bottom: 0.25rem; }
    .chat-msg-bubble code {
        background: rgba(99,102,241,0.1);
        padding: 0.15rem 0.4rem;
        border-radius: 0.3rem;
        font-size: 0.82rem;
    }
    .chat-msg.user .chat-msg-bubble code {
        background: rgba(255,255,255,0.2);
    }
    .chat-msg-bubble pre {
        background: #1e293b;
        color: #e2e8f0;
        padding: 0.75rem 1rem;
        border-radius: 0.6rem;
        overflow-x: auto;
        margin: 0.5rem 0;
        font-size: 0.82rem;
    }
    .chat-msg-bubble pre code {
        background: none; padding: 0; color: inherit;
    }
    .chat-msg-bubble h1, .chat-msg-bubble h2, .chat-msg-bubble h3, .chat-msg-bubble h4 {
        margin: 0.75rem 0 0.4rem;
        font-size: 1rem;
        font-weight: 600;
    }
    .chat-msg-bubble blockquote {
        border-left: 3px solid var(--card-accent);
        margin: 0.5rem 0;
        padding: 0.4rem 0.75rem;
        background: rgba(99,102,241,0.05);
        border-radius: 0 0.4rem 0.4rem 0;
        color: var(--text-secondary);
    }
    .chat-msg-bubble a { color: var(--card-accent); }

    .chat-msg-meta {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0 0.25rem;
    }
    .chat-msg.user .chat-msg-meta { justify-content: flex-end; }
    .chat-msg-time {
        font-size: 0.7rem;
        color: var(--text-muted);
    }
    .chat-msg-actions {
        display: flex;
        gap: 0.25rem;
        opacity: 0;
        transition: opacity 0.2s;
    }
    .chat-msg:hover .chat-msg-actions { opacity: 1; }
    .chat-msg-action {
        background: none;
        border: none;
        color: var(--text-muted);
        cursor: pointer;
        padding: 0.15rem;
        font-size: 0.75rem;
        border-radius: 0.3rem;
        transition: all 0.15s;
    }
    .chat-msg-action:hover { color: var(--card-accent); background: var(--badge-bg); }
    .chat-msg-action.copied { color: #10b981; }

    .typing-indicator {
        display: flex;
        gap: 0.6rem;
        margin-bottom: 1rem;
        max-width: 85%;
        animation: msgIn 0.3s ease;
    }
    .typing-dots {
        display: flex;
        gap: 4px;
        padding: 0.75rem 1rem;
        background: var(--glass-bg);
        border: 1px solid var(--glass-border);
        border-radius: 1rem;
        border-top-left-radius: 0.25rem;
    }
    .typing-dots span {
        width: 7px; height: 7px;
        border-radius: 50%;
        background: var(--text-muted);
        animation: typingBounce 1.4s infinite ease-in-out;
    }
    .typing-dots span:nth-child(2) { animation-delay: 0.2s; }
    .typing-dots span:nth-child(3) { animation-delay: 0.4s; }
    @keyframes typingBounce {
        0%, 80%, 100% { transform: translateY(0); opacity: 0.4; }
        40% { transform: translateY(-6px); opacity: 1; }
    }

    .chat-suggestions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.4rem;
        padding: 0.5rem 0;
        justify-content: center;
        flex-shrink: 0;
    }
    .chat-suggestions.hidden { display: none; }
    .suggestion-chip { white-space: nowrap; }


    .chat-input-area {
        padding: 0.75rem 0;
        flex-shrink: 0;
    }
    .chat-input-wrap {
        display: flex;
        align-items: flex-end;
        gap: 0.5rem;
        padding: 0.5rem;
    }
    .chat-input-wrap textarea {
        flex: 1;
        border: none;
        background: transparent;
        resize: none;
        outline: none;
        font-size: 0.88rem;
        line-height: 1.5;
        color: var(--text-primary);
        padding: 0.4rem 0.5rem;
        max-height: 120px;
        min-height: 24px;
        font-family: inherit;
    }
    .chat-input-wrap textarea::placeholder { color: var(--text-muted); }
    .chat-input-actions {
        display: flex;
        gap: 0.3rem;
        align-items: center;
    }
    .chat-send-btn {
        width: 38px; height: 38px;
        border-radius: 0.7rem;
        border: none;
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s;
        font-size: 1rem;
    }
    .chat-send-btn:hover { transform: scale(1.05); box-shadow: 0 4px 12px rgba(99,102,241,0.4); }
    .chat-send-btn:disabled { opacity: 0.5; cursor: not-allowed; transform: none; box-shadow: none; }
    .chat-send-btn.stop-btn {
        background: linear-gradient(135deg, #ef4444, #dc2626);
    }
    .chat-char-count {
        font-size: 0.7rem;
        color: var(--text-muted);
        padding: 0 0.5rem;
    }
    .chat-char-count.warn { color: #f59e0b; }
    .chat-char-count.over { color: #ef4444; }

    .welcome-section {
        text-align: center;
        padding: 2rem 1rem;
    }
    .welcome-avatar {
        width: 64px; height: 64px;
        border-radius: 20px;
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        display: flex; align-items: center; justify-content: center;
        color: white; font-size: 1.8rem;
        margin: 0 auto 1rem;
    }
    .welcome-section h5 {
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 0.4rem;
    }
    .welcome-section p {
        color: var(--text-secondary);
        font-size: 0.88rem;
        margin: 0;
    }

    @media (max-width: 768px) {
        .chat-page { height: calc(100vh - 130px); }
        .chat-msg { max-width: 92%; }
        .chat-header-actions select { max-width: 100px; font-size: 0.75rem; }
    }
    @media (max-width: 480px) {
        .chat-page { height: calc(100vh - 110px); padding: 0; }
        .chat-header { border-radius: 0; margin: 0; }
        .chat-input-area { padding: 0.5rem; }
    }
</style>

<div class="chat-page">
    <div class="chat-header glass-card">
        <div class="chat-header-left">
            <div class="chat-header-avatar"><i class="bi bi-robot"></i></div>
            <div class="chat-header-info">
                <h6>AI Study Assistant</h6>
                <small id="chatStatus">Online</small>
            </div>
        </div>
        <div class="chat-header-actions">
            <select id="personaSelect" title="AI Personality">
                <option value="default">Default</option>
                <option value="strict">Strict</option>
                <option value="friendly">Friendly</option>
                <option value="socratic">Socratic</option>
                <option value="simplifier">Simplifier</option>
            </select>
            <button class="btn-soft" id="clearChatBtn" title="Clear chat">
                <i class="bi bi-trash3"></i>
            </button>
            <a href="{{ route('ai.settings') }}" class="btn-soft" title="AI Provider Settings">
                <i class="bi bi-gear"></i>
            </a>
        </div>
    </div>

    @php
        $anyProvider = $providers['gemini'] || $providers['groq'] || $providers['openrouter'];
    @endphp

    @if(!$providers['gemini'] && !$providers['groq'])
    <div class="setup-banner" id="setupBanner" style="background:linear-gradient(135deg,rgba(99,102,241,0.12),rgba(139,92,246,0.12));border:1px solid rgba(99,102,241,0.25);border-radius:0.75rem;padding:0.75rem 1rem;margin-bottom:0.75rem;display:flex;align-items:center;justify-content:space-between;flex-shrink:0;gap:0.75rem;">
        <div style="display:flex;align-items:center;gap:0.5rem;min-width:0;">
            <i class="bi bi-exclamation-triangle-fill" style="color:#f39c12;font-size:1rem;flex-shrink:0;"></i>
            <span style="font-size:0.82rem;color:var(--text-primary);">
                AI is using OpenRouter free tier (daily limit reached). Add <strong>Google Gemini</strong> for 1,500 free requests/day.
            </span>
        </div>
        <a href="{{ route('ai.settings') }}" class="dark-btn" style="padding:0.4rem 0.8rem;font-size:0.8rem;">
            <i class="bi bi-gear"></i> Setup AI
        </a>
    </div>
    @endif

    @if(!$anyProvider)
    <div class="setup-banner" style="background:linear-gradient(135deg,rgba(231,76,60,0.12),rgba(192,57,43,0.12));border:1px solid rgba(231,76,60,0.3);border-radius:0.75rem;padding:0.75rem 1rem;margin-bottom:0.75rem;display:flex;align-items:center;justify-content:space-between;flex-shrink:0;gap:0.75rem;">
        <div style="display:flex;align-items:center;gap:0.5rem;min-width:0;">
            <i class="bi bi-x-circle-fill" style="color:#e74c3c;font-size:1rem;flex-shrink:0;"></i>
            <span style="font-size:0.82rem;color:var(--text-primary);">
                <strong>No AI providers configured.</strong> Add a free API key to enable AI chat.
            </span>
        </div>
        <a href="{{ route('ai.settings') }}" class="dark-btn" style="padding:0.4rem 0.8rem;font-size:0.8rem;background:#dc2626;">
            <i class="bi bi-gear"></i> Setup AI
        </a>
    </div>
    @endif

    <div class="chat-messages" id="chatMessages">
        <div class="welcome-section" id="welcomeSection">
            <div class="welcome-avatar"><i class="bi bi-robot"></i></div>
            <h5>Hi {{ Auth::user()->name ?? 'there' }}!</h5>
            <p>I'm your AI study assistant. Ask me anything about your studies.</p>
        </div>
    </div>

    <div class="chat-suggestions" id="chatSuggestions">
        <button class="btn-soft suggestion-chip" data-msg="Explain photosynthesis like I'm 5">Explain photosynthesis</button>
        <button class="btn-soft suggestion-chip" data-msg="What are the key topics in my notes?">What's in my notes?</button>
        <button class="btn-soft suggestion-chip" data-msg="Generate 5 practice MCQs on biology">Generate practice MCQs</button>
        <button class="btn-soft suggestion-chip" data-msg="Create a study plan for my exams">Create a study plan</button>
    </div>

    <div class="chat-input-area">
        <div class="chat-input-wrap glass-card" style="padding:0.5rem;">
            <textarea id="chatInput" rows="1" placeholder="Ask me anything about your studies..." maxlength="2000"></textarea>
            <div class="chat-input-actions">
                <span class="chat-char-count" id="charCount">0/2000</span>
                <button class="chat-send-btn" id="sendBtn" title="Send message">
                    <i class="bi bi-send-fill"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.css">
<script src="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const messagesEl = document.getElementById('chatMessages');
    const chatInput = document.getElementById('chatInput');
    const sendBtn = document.getElementById('sendBtn');
    const suggestions = document.getElementById('chatSuggestions');
    const welcomeSection = document.getElementById('welcomeSection');
    const charCount = document.getElementById('charCount');
    const MAX_CHARS = 2000;
    let isSending = false;
    let abortController = null;

    const CSRF = '{{ csrf_token() }}';

    marked.setOptions({
        breaks: true,
        gfm: true,
        headerIds: false,
        mangle: false
    });

    function escapeHtml(text) {
        const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
        return text.replace(/[&<>"']/g, m => map[m]);
    }

    function preprocessMath(text) {
        text = text.replace(/\$\$([\s\S]+?)\$\$/g, (_, expr) => {
            try { return katex.renderToString(expr.trim(), { displayMode: true, throwOnError: false }); }
            catch(e) { return expr; }
        });
        text = text.replace(/\$([^\n$]+?)\$/g, (_, expr) => {
            try { return katex.renderToString(expr.trim(), { displayMode: false, throwOnError: false }); }
            catch(e) { return expr; }
        });
        return text;
    }

    function renderContent(text) {
        const safe = escapeHtml(text);
        const withMath = preprocessMath(safe);
        return marked.parse(withMath);
    }

    function getTimeStr() {
        return new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    }

    function addMessage(role, content, skipScroll) {
        if (welcomeSection) welcomeSection.style.display = 'none';

        const msg = document.createElement('div');
        msg.className = 'chat-msg ' + role;

        const avatarIcon = role === 'bot' ? '<i class="bi bi-robot"></i>' : '<i class="bi bi-person-fill"></i>';
        const rendered = role === 'bot' ? renderContent(content) : '<p>' + escapeHtml(content) + '</p>';

        const actionBtns = role === 'bot'
            ? `<span class="chat-msg-actions">
                <button class="chat-msg-action" onclick="copyMsg(this)" title="Copy"><i class="bi bi-clipboard"></i></button>
               </span>`
            : '';

        msg.innerHTML = `
            <div class="chat-msg-avatar">${avatarIcon}</div>
            <div class="chat-msg-body">
                <div class="chat-msg-bubble">${rendered}</div>
                <div class="chat-msg-meta">
                    <span class="chat-msg-time">${getTimeStr()}</span>
                    ${actionBtns}
                </div>
            </div>
        `;
        messagesEl.appendChild(msg);
        if (!skipScroll) scrollToBottom();
        return msg;
    }

    function showTyping() {
        const el = document.createElement('div');
        el.className = 'typing-indicator';
        el.id = 'typingIndicator';
        el.innerHTML = `
            <div class="chat-msg-avatar" style="background:linear-gradient(135deg,#6366f1,#8b5cf6);color:white;width:32px;height:32px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:0.8rem">
                <i class="bi bi-robot"></i>
            </div>
            <div class="typing-dots"><span></span><span></span><span></span></div>
        `;
        messagesEl.appendChild(el);
        scrollToBottom();
    }

    function hideTyping() {
        const el = document.getElementById('typingIndicator');
        if (el) el.remove();
    }

    function scrollToBottom() {
        requestAnimationFrame(() => {
            messagesEl.scrollTop = messagesEl.scrollHeight;
        });
    }

    function autoResize() {
        chatInput.style.height = 'auto';
        chatInput.style.height = Math.min(chatInput.scrollHeight, 120) + 'px';
    }

    function updateCharCount() {
        const len = chatInput.value.length;
        charCount.textContent = len + '/' + MAX_CHARS;
        charCount.className = 'chat-char-count' + (len > MAX_CHARS ? ' over' : len > 1800 ? ' warn' : '');
    }

    async function sendMessage() {
        const message = chatInput.value.trim();
        if (!message || isSending) return;

        isSending = true;
        sendBtn.disabled = true;
        sendBtn.innerHTML = '<i class="bi bi-stop-fill"></i>';
        sendBtn.classList.add('stop-btn');
        sendBtn.title = 'Stop generating';

        addMessage('user', message);
        chatInput.value = '';
        autoResize();
        updateCharCount();
        suggestions.classList.add('hidden');
        showTyping();

        const persona = document.getElementById('personaSelect')?.value || 'default';

        abortController = new AbortController();
        const timeoutId = setTimeout(() => abortController.abort(), 90000);

        try {
            const response = await fetch('/chat', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': CSRF
                },
                body: JSON.stringify({ message, persona }),
                signal: abortController.signal
            });

            clearTimeout(timeoutId);
            hideTyping();

            const data = await response.json();
            let msg = data.response || 'No response received.';
            if (data.error) {
                msg += '\n\n> **Quick fix:** [Open AI Settings](/ai-settings) to add a free API key';
            }
            addMessage('bot', msg);
        } catch (err) {
            hideTyping();
            clearTimeout(timeoutId);
            let errMsg;
            if (err.name === 'AbortError') {
                errMsg = 'Request timed out. The AI is taking longer than usual — please try again.';
            } else {
                errMsg = 'Something went wrong. Please try again.';
            }
            addMessage('bot', errMsg);
        }

        isSending = false;
        abortController = null;
        sendBtn.disabled = false;
        sendBtn.innerHTML = '<i class="bi bi-send-fill"></i>';
        sendBtn.classList.remove('stop-btn');
        sendBtn.title = 'Send message';
        chatInput.focus();
    }

    function stopGeneration() {
        if (abortController) {
            abortController.abort();
        }
    }

    async function loadHistory() {
        try {
            const res = await fetch('/chat/history', {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }
            });
            const data = await res.json();
            if (data.messages && data.messages.length > 0) {
                if (welcomeSection) welcomeSection.style.display = 'none';
                data.messages.forEach(msg => {
                    addMessage(msg.role === 'assistant' ? 'bot' : msg.role, msg.content, true);
                });
                scrollToBottom();
                suggestions.classList.add('hidden');
            }
        } catch(e) {}
    }

    window.copyMsg = function(btn) {
        const bubble = btn.closest('.chat-msg-body').querySelector('.chat-msg-bubble');
        const text = bubble.innerText || bubble.textContent;
        navigator.clipboard.writeText(text).then(() => {
            btn.innerHTML = '<i class="bi bi-check-lg"></i>';
            btn.classList.add('copied');
            setTimeout(() => {
                btn.innerHTML = '<i class="bi bi-clipboard"></i>';
                btn.classList.remove('copied');
            }, 2000);
        });
    };

    document.getElementById('clearChatBtn').addEventListener('click', async () => {
        if (!confirm('Clear the conversation?')) return;
        try {
            await fetch('/chat/clear', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF }
            });
        } catch (_) {}
        messagesEl.innerHTML = '';
        if (welcomeSection) {
            welcomeSection.style.display = '';
            messagesEl.appendChild(welcomeSection);
        }
        suggestions.classList.remove('hidden');
    });

    sendBtn.addEventListener('click', () => {
        if (isSending) { stopGeneration(); return; }
        sendMessage();
    });

    chatInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            if (!sendBtn.disabled) sendMessage();
        }
    });

    chatInput.addEventListener('input', () => {
        autoResize();
        updateCharCount();
    });

    suggestions.addEventListener('click', (e) => {
        const chip = e.target.closest('.suggestion-chip');
        if (chip) {
            chatInput.value = chip.dataset.msg;
            autoResize();
            sendMessage();
        }
    });

    loadHistory();
    chatInput.focus();
});
</script>
@endsection
