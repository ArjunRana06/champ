@extends('Backend.master')

@section('content')
<div class="chat-page">
    <div class="chat-container">
        <div class="chat-header">
            <div class="chat-header-left">
                <div class="chat-avatar">
                    <i class="bi bi-robot"></i>
                </div>
                <div class="chat-header-info">
                    <h5 class="chat-title">AI Study Assistant</h5>
                    <div class="chat-status">
                        <span class="status-dot"></span>
                        <span class="status-text">Online — Ready to help</span>
                    </div>
                </div>
            </div>
            <div class="chat-header-right">
                <div class="persona-group">
                    <i class="bi bi-person-workspace persona-icon"></i>
                    <select id="personaSelect" class="persona-select">
                        <option value="default">🎓 Professor</option>
                        <option value="strict">📚 Strict Professor</option>
                        <option value="friendly">🤗 Friendly Tutor</option>
                        <option value="socratic">❓ Socratic Tutor</option>
                        <option value="simplifier">💡 Simplifier</option>
                    </select>
                </div>
                <button id="clearChatBtn" class="chat-icon-btn" title="Clear conversation">
                    <i class="bi bi-trash3"></i>
                </button>
            </div>
        </div>

        <div class="chat-messages" id="aiChatMessages">
            <div class="message-row bot">
                <div class="msg-avatar bot-avatar"><i class="bi bi-robot"></i></div>
                <div class="msg-content">
                    <div class="msg-bubble bot-bubble">
                        <div class="msg-text">
                            👋 Hi {{ Auth::user()->name ?? 'there' }}! I'm your AI study assistant. I can help you understand concepts from your uploaded notes, generate practice questions, or explain any topic.
                        </div>
                    </div>
                    <div class="msg-footer">
                        <span class="msg-time">Just now</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="chat-suggestions" id="suggestions">
            <button class="chip" data-msg="Explain a concept from my notes">
                <span class="chip-icon">📖</span> Explain a concept
            </button>
            <button class="chip" data-msg="Create practice questions from my notes">
                <span class="chip-icon">📝</span> Practice questions
            </button>
            <button class="chip" data-msg="Summarize my key topics">
                <span class="chip-icon">📋</span> Summarize topics
            </button>
            <button class="chip" data-msg="How should I study effectively?">
                <span class="chip-icon">🎯</span> Study tips
            </button>
        </div>

        <div class="chat-input-area">
            <div class="input-wrapper">
                <textarea id="aiChatInput" rows="1" placeholder="Ask about your study material..." autofocus></textarea>
                <button id="aiSendBtn" class="send-btn">
                    <i class="bi bi-send-fill"></i>
                    <span>Send</span>
                </button>
            </div>
            <div class="input-footer">
                <span class="input-hint">Shift + Enter for new line</span>
            </div>
        </div>
    </div>
</div>

<style>
.chat-page {
    display: flex;
    flex-direction: column;
    height: calc(100vh - 140px);
    min-height: 0;
}

.chat-container {
    flex: 1;
    display: flex;
    flex-direction: column;
    background: var(--glass-bg);
    backdrop-filter: blur(20px) saturate(1.8);
    border: 1px solid var(--glass-border);
    border-radius: 1.5rem;
    box-shadow: var(--card-shadow);
    overflow: hidden;
    max-width: 960px;
    width: 100%;
    margin: 0 auto;
}

/* ── Header ── */
.chat-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1rem 1.5rem;
    border-bottom: 1px solid var(--divider-color);
    flex-shrink: 0;
    background: var(--glass-bg);
}

.chat-header-left {
    display: flex;
    align-items: center;
    gap: 0.85rem;
    min-width: 0;
}

.chat-avatar {
    width: 44px;
    height: 44px;
    border-radius: 14px;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.35rem;
    color: white;
    background: linear-gradient(135deg, #6366f1, #a855f7, #ec4899);
    box-shadow: 0 4px 16px rgba(99, 102, 241, 0.3);
    position: relative;
}

.chat-avatar::after {
    content: '';
    position: absolute;
    inset: -2px;
    border-radius: 16px;
    background: linear-gradient(135deg, #6366f1, #a855f7, #ec4899);
    opacity: 0.3;
    z-index: -1;
    filter: blur(8px);
}

.chat-header-info {
    min-width: 0;
}

.chat-title {
    font-weight: 800;
    font-size: 1.05rem;
    color: var(--text-primary);
    margin: 0;
    line-height: 1.3;
}

.chat-status {
    display: flex;
    align-items: center;
    gap: 0.4rem;
}

.status-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: #22c55e;
    display: inline-block;
    animation: pulse-dot 2s ease-in-out infinite;
}

@keyframes pulse-dot {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.4; }
}

.status-text {
    font-size: 0.75rem;
    color: var(--text-secondary);
}

.chat-header-right {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    flex-shrink: 0;
}

.persona-group {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    background: var(--input-bg);
    border: 1px solid var(--input-border);
    border-radius: 40px;
    padding: 0.2rem 0.2rem 0.2rem 0.75rem;
    transition: border-color 0.2s;
}

.persona-group:focus-within {
    border-color: #6366f1;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

.persona-icon {
    font-size: 0.85rem;
    color: var(--text-muted);
}

.persona-select {
    border: none;
    background: transparent;
    font-size: 0.78rem;
    padding: 0.3rem 0.5rem 0.3rem 0;
    color: var(--text-primary);
    outline: none;
    cursor: pointer;
    font-family: inherit;
    max-width: 130px;
}

.chat-icon-btn {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    border: 1px solid var(--input-border);
    background: var(--input-bg);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s;
    color: var(--text-muted);
    flex-shrink: 0;
}

.chat-icon-btn:hover {
    border-color: #ef4444;
    color: #ef4444;
    background: rgba(239, 68, 68, 0.06);
}

/* ── Messages ── */
.chat-messages {
    flex: 1;
    overflow-y: auto;
    padding: 1.25rem 1.5rem;
    display: flex;
    flex-direction: column;
    gap: 1rem;
    min-height: 0;
    scroll-behavior: smooth;
}

.chat-messages::-webkit-scrollbar { width: 5px; }
.chat-messages::-webkit-scrollbar-track { background: transparent; }
.chat-messages::-webkit-scrollbar-thumb {
    background: var(--text-muted);
    opacity: 0.3;
    border-radius: 10px;
}
.chat-messages::-webkit-scrollbar-thumb:hover { opacity: 0.5; }

.message-row {
    display: flex;
    gap: 0.75rem;
    align-items: flex-start;
    animation: msg-in 0.3s ease-out;
    max-width: 88%;
}

.message-row.user {
    flex-direction: row-reverse;
    align-self: flex-end;
}

@keyframes msg-in {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.msg-avatar {
    width: 34px;
    height: 34px;
    border-radius: 10px;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1rem;
    margin-top: 4px;
}

.bot-avatar {
    background: linear-gradient(135deg, #6366f1, #a855f7);
    box-shadow: 0 4px 12px rgba(99, 102, 241, 0.25);
}

.user-avatar {
    background: linear-gradient(135deg, #a855f7, #ec4899);
    box-shadow: 0 4px 12px rgba(168, 85, 247, 0.25);
}

.msg-content {
    display: flex;
    flex-direction: column;
    gap: 3px;
    min-width: 0;
}

.message-row.user .msg-content {
    align-items: flex-end;
}

.msg-bubble {
    padding: 0.75rem 1.1rem;
    border-radius: 1.2rem;
    font-size: 0.92rem;
    line-height: 1.65;
    word-break: break-word;
    overflow-wrap: break-word;
    position: relative;
}

.bot-bubble {
    background: var(--input-bg);
    color: var(--text-primary);
    border: 1px solid var(--input-border);
    border-bottom-left-radius: 0.3rem;
}

.bot-bubble.streaming {
    border-color: #6366f1;
    box-shadow: 0 0 20px rgba(99, 102, 241, 0.06);
}

.user-bubble {
    background: linear-gradient(135deg, #6366f1, #7c3aed);
    color: white;
    border-bottom-right-radius: 0.3rem;
}

.msg-text p { margin: 0 0 0.5rem 0; }
.msg-text p:last-child { margin-bottom: 0; }
.msg-text ul, .msg-text ol { margin: 0.3rem 0; padding-left: 1.3rem; }
.msg-text li { margin-bottom: 0.2rem; }
.msg-text strong { font-weight: 700; }
.msg-text em { font-style: italic; }
.msg-text code {
    background: rgba(99, 102, 241, 0.1);
    padding: 0.1rem 0.3rem;
    border-radius: 4px;
    font-size: 0.85em;
    font-family: 'JetBrains Mono', 'Fira Code', monospace;
}
.user-bubble code { background: rgba(255,255,255,0.15); color: inherit; }
.msg-text pre {
    background: #1e1e2e;
    color: #cdd6f4;
    padding: 0.8rem;
    border-radius: 10px;
    overflow-x: auto;
    margin: 0.5rem 0;
    font-size: 0.82rem;
    font-family: 'JetBrains Mono', 'Fira Code', monospace;
    line-height: 1.5;
}
.msg-text pre code { background: none; padding: 0; color: inherit; font-size: inherit; }
.msg-text h1, .msg-text h2, .msg-text h3, .msg-text h4 { margin: 0.5rem 0 0.3rem; font-weight: 700; }
.msg-text blockquote {
    border-left: 3px solid #6366f1;
    padding-left: 0.8rem;
    margin: 0.5rem 0;
    color: var(--text-secondary);
    font-style: italic;
}
.msg-text a { color: #6366f1; text-decoration: underline; }
.msg-text hr { border: none; border-top: 1px solid var(--divider-color); margin: 0.8rem 0; }
.msg-text table {
    border-collapse: collapse;
    margin: 0.5rem 0;
    font-size: 0.85rem;
    width: 100%;
}
.msg-text th, .msg-text td {
    border: 1px solid var(--input-border);
    padding: 0.4rem 0.6rem;
    text-align: left;
}
.msg-text th { background: rgba(99, 102, 241, 0.05); font-weight: 600; }

.msg-footer {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0 0.3rem;
    height: 20px;
}

.message-row.user .msg-footer { justify-content: flex-end; }

.msg-time {
    font-size: 0.68rem;
    color: var(--text-muted);
}

.msg-copy-btn {
    font-size: 0.68rem;
    color: var(--text-muted);
    background: none;
    border: none;
    padding: 0 2px;
    cursor: pointer;
    opacity: 0;
    transition: all 0.15s;
    display: inline-flex;
    align-items: center;
    gap: 3px;
    font-family: inherit;
}

.message-row.bot .msg-content:hover .msg-copy-btn { opacity: 1; }

.msg-copy-btn:hover { color: #6366f1; }

.msg-copy-btn.copied {
    opacity: 1;
    color: #22c55e;
}

/* ── Typing Indicator ── */
.typing-indicator {
    display: flex;
    align-items: center;
    gap: 4px;
    padding: 0.75rem 1.1rem;
}

.typing-dot {
    width: 7px;
    height: 7px;
    background: #6366f1;
    border-radius: 50%;
    animation: typing-bounce 1.2s infinite ease-in-out;
}

.typing-dot:nth-child(1) { animation-delay: 0s; }
.typing-dot:nth-child(2) { animation-delay: 0.15s; }
.typing-dot:nth-child(3) { animation-delay: 0.3s; }

@keyframes typing-bounce {
    0%, 60%, 100% { transform: translateY(0); }
    30% { transform: translateY(-6px); }
}

/* ── Suggestions ── */
.chat-suggestions {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
    padding: 0 1.5rem 0.75rem;
    flex-shrink: 0;
}

.chip {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.45rem 0.9rem;
    border-radius: 40px;
    border: 1px solid var(--input-border);
    background: var(--input-bg);
    font-size: 0.78rem;
    cursor: pointer;
    transition: all 0.2s;
    white-space: nowrap;
    color: var(--text-secondary);
    font-family: inherit;
}

.chip:hover {
    border-color: #6366f1;
    background: rgba(99, 102, 241, 0.06);
    color: #6366f1;
}

.chip-icon { font-size: 0.85rem; }

/* ── Input ── */
.chat-input-area {
    flex-shrink: 0;
    padding: 0 1.5rem 1.25rem;
}

.input-wrapper {
    display: flex;
    align-items: flex-end;
    gap: 0.5rem;
    background: var(--input-bg);
    border: 1.5px solid var(--input-border);
    border-radius: 16px;
    padding: 0.35rem;
    transition: border-color 0.2s, box-shadow 0.2s;
}

.input-wrapper:focus-within {
    border-color: #6366f1;
    box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
}

.input-wrapper textarea {
    flex: 1;
    border: none;
    background: transparent;
    padding: 0.55rem 0.8rem;
    font-size: 0.92rem;
    color: var(--text-primary);
    font-family: inherit;
    outline: none;
    resize: none;
    max-height: 120px;
    line-height: 1.5;
    min-height: 40px;
}

.input-wrapper textarea::placeholder { color: var(--text-muted); }

.send-btn {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.55rem 1.1rem;
    border: none;
    border-radius: 12px;
    background: linear-gradient(135deg, #6366f1, #7c3aed);
    color: white;
    font-weight: 600;
    font-size: 0.85rem;
    font-family: inherit;
    cursor: pointer;
    transition: all 0.2s;
    flex-shrink: 0;
    margin-bottom: 2px;
}

.send-btn:not(:disabled):hover {
    transform: translateY(-1px);
    box-shadow: 0 8px 25px rgba(99, 102, 241, 0.3);
}

.send-btn:not(:disabled):active {
    transform: translateY(0);
}

.send-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.input-footer {
    display: flex;
    justify-content: flex-end;
    padding: 0.35rem 0.3rem 0;
}

.input-hint {
    font-size: 0.68rem;
    color: var(--text-muted);
}

/* ── Responsive ── */
@media (max-width: 768px) {
    .chat-page {
        height: calc(100vh - 120px);
    }

    .chat-container {
        border-radius: 0;
        border-left: none;
        border-right: none;
    }

    .chat-header {
        padding: 0.75rem 1rem;
    }

    .chat-messages {
        padding: 1rem;
    }

    .message-row {
        max-width: 95%;
    }

    .msg-avatar {
        width: 30px;
        height: 30px;
        font-size: 0.85rem;
    }

    .chat-header-right {
        gap: 0.4rem;
    }

    .persona-select {
        max-width: 100px;
        font-size: 0.72rem;
    }

    .persona-icon { display: none; }

    .send-btn {
        padding: 0.5rem 0.8rem;
    }

    .send-btn span { display: none; }

    .chat-input-area {
        padding: 0 1rem 1rem;
    }

    .chat-suggestions {
        padding: 0 1rem 0.5rem;
    }

    .chip { font-size: 0.72rem; padding: 0.35rem 0.7rem; }
}

@media (max-width: 480px) {
    .chat-avatar {
        width: 36px;
        height: 36px;
        font-size: 1.1rem;
    }
}
</style>

<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.11/dist/katex.min.css">
<script src="https://cdn.jsdelivr.net/npm/katex@0.16.11/dist/katex.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/katex@0.16.11/dist/contrib/auto-render.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
const messagesEl = document.getElementById('aiChatMessages');
const chatInput = document.getElementById('aiChatInput');
const sendBtn = document.getElementById('aiSendBtn');
const suggestions = document.getElementById('suggestions');
let isSending = false;

marked.setOptions({ breaks: true, gfm: true });
sendBtn.disabled = !chatInput.value.trim();

function preprocessMath(text) {
    text = text.replace(/^\s*\\?\[\s*\n([\s\S]*?)\n\s*\\?\]\s*$/gm, '$$\n$1\n$$');
    text = text.replace(/\\?\[([^\]\n]+)\\?\]/g, (m, c) => {
        if (/\\/.test(c)) return '$$' + c.trim() + '$$';
        return m;
    });
    text = text.replace(/\\\(([\s\S]*?)\\\)/g, (m, c) => '$' + c.trim() + '$');
    return text;
}

function renderContent(text) {
    const html = marked.parse(preprocessMath(text));
    return html;
}

function renderMath(element) {
    if (window.renderMathInElement) {
        try {
            renderMathInElement(element, {
                delimiters: [
                    {left: '$$', right: '$$', display: true},
                    {left: '$', right: '$', display: false},
                    {left: '\\(', right: '\\)', display: false},
                    {left: '\\[', right: '\\]', display: true}
                ],
                throwOnError: false
            });
        } catch (e) {}
    }
}

function now() {
    return new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
}

function autoResize() {
    chatInput.style.height = 'auto';
    chatInput.style.height = Math.min(chatInput.scrollHeight, 120) + 'px';
}
chatInput.addEventListener('input', autoResize);

chatInput.addEventListener('input', () => {
    sendBtn.disabled = !chatInput.value.trim();
});

function scrollToBottom() {
    messagesEl.scrollTop = messagesEl.scrollHeight;
}

function addMessage(sender, text, isStreaming) {
    const row = document.createElement('div');
    row.className = `message-row ${sender}`;

    const avatar = sender === 'user'
        ? '<div class="msg-avatar user-avatar"><i class="bi bi-person"></i></div>'
        : '<div class="msg-avatar bot-avatar"><i class="bi bi-robot"></i></div>';

    const bubbleClass = `msg-bubble ${sender === 'user' ? 'user-bubble' : 'bot-bubble'}${isStreaming ? ' streaming' : ''}`;

    const body = sender === 'user'
        ? `<div class="msg-text">${escapeHtml(text)}</div>`
        : `<div class="msg-text">${renderContent(text)}</div>`;

    const copyBtn = sender === 'bot'
        ? `<button class="msg-copy-btn" onclick="copyMsg(this)"><i class="bi bi-clipboard"></i> Copy</button>`
        : '';

    row.innerHTML = `
        ${avatar}
        <div class="msg-content">
            <div class="${bubbleClass}">${body}</div>
            <div class="msg-footer">
                <span class="msg-time">${now()}</span>
                ${copyBtn}
            </div>
        </div>
    `;

    const msgText = row.querySelector('.msg-text');
    if (msgText && sender === 'bot') renderMath(msgText);

    messagesEl.appendChild(row);
    scrollToBottom();
    return row;
}

function updateStreamMsg(row, text) {
    const bubble = row.querySelector('.msg-bubble');
    if (bubble) {
        const msgText = bubble.querySelector('.msg-text');
        msgText.innerHTML = renderContent(text);
        renderMath(msgText);
        scrollToBottom();
    }
}

function finalizeStreamMsg(row) {
    const bubble = row.querySelector('.msg-bubble');
    if (bubble) bubble.classList.remove('streaming');
    const time = row.querySelector('.msg-time');
    if (time) time.textContent = now();
}

function escapeHtml(text) {
    const d = document.createElement('div');
    d.textContent = text;
    return d.innerHTML;
}

function copyMsg(btn) {
    const text = btn.closest('.msg-content').querySelector('.msg-text').innerText;
    navigator.clipboard.writeText(text).then(() => {
        btn.innerHTML = '<i class="bi bi-check"></i> Copied';
        btn.classList.add('copied');
        setTimeout(() => {
            btn.innerHTML = '<i class="bi bi-clipboard"></i> Copy';
            btn.classList.remove('copied');
        }, 2000);
    });
}

function showTyping() {
    const row = document.createElement('div');
    row.className = 'message-row bot';
    row.id = 'typingIndicator';
    row.innerHTML = `
        <div class="msg-avatar bot-avatar"><i class="bi bi-robot"></i></div>
        <div class="msg-content">
            <div class="msg-bubble bot-bubble">
                <div class="typing-indicator">
                    <div class="typing-dot"></div>
                    <div class="typing-dot"></div>
                    <div class="typing-dot"></div>
                </div>
            </div>
        </div>
    `;
    messagesEl.appendChild(row);
    scrollToBottom();
}

function hideTyping() {
    const el = document.getElementById('typingIndicator');
    if (el) el.remove();
}

async function sendMessage() {
    const message = chatInput.value.trim();
    if (!message || isSending) return;

    isSending = true;
    sendBtn.disabled = true;

    addMessage('user', message);
    chatInput.value = '';
    autoResize();
    suggestions.style.display = 'none';
    showTyping();

    const persona = document.getElementById('personaSelect')?.value || 'default';

    try {
        const response = await fetch('{{ route('chat.stream') }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ message, persona })
        });

        hideTyping();

        if (!response.ok) {
            addMessage('bot', 'AI service temporarily unavailable. Please try again.');
            isSending = false;
            sendBtn.disabled = false;
            chatInput.focus();
            return;
        }

        const reader = response.body.getReader();
        const decoder = new TextDecoder();
        let msgRow = addMessage('bot', '', true);
        let text = '';
        let buf = '';

        while (true) {
            const { done, value } = await reader.read();
            if (done) break;
            buf += decoder.decode(value, { stream: true });
            text += buf;
            updateStreamMsg(msgRow, text);
            buf = '';
        }

        finalizeStreamMsg(msgRow);
    } catch (err) {
        hideTyping();
        addMessage('bot', 'Network error. Please try again.');
    }

    isSending = false;
    sendBtn.disabled = false;
    chatInput.focus();
}

suggestions.addEventListener('click', (e) => {
    const chip = e.target.closest('.chip');
    if (chip) {
        chatInput.value = chip.dataset.msg;
        autoResize();
        sendBtn.disabled = false;
        sendMessage();
    }
});

document.getElementById('clearChatBtn').addEventListener('click', async () => {
    if (!confirm('Clear the conversation?')) return;
    try {
        await fetch('{{ route('chat.clear') }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        });
    } catch (_) {}
    messagesEl.innerHTML = '';
    suggestions.style.display = 'flex';
    const w = document.createElement('div');
    w.className = 'message-row bot';
    w.innerHTML = `
        <div class="msg-avatar bot-avatar"><i class="bi bi-robot"></i></div>
        <div class="msg-content">
            <div class="msg-bubble bot-bubble">
                <div class="msg-text">👋 Hi {{ Auth::user()->name ?? 'there' }}! I'm your AI study assistant. What would you like to study today?</div>
            </div>
            <div class="msg-footer">
                <span class="msg-time">Just now</span>
            </div>
        </div>
    `;
    messagesEl.appendChild(w);
});

sendBtn.addEventListener('click', sendMessage);
chatInput.addEventListener('keydown', (e) => {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        if (!sendBtn.disabled) sendMessage();
    }
});

});
</script>
@endsection
