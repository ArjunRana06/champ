@extends('Backend.master')

@section('content')
<div class="ai-chat-container">
    <div class="ai-chat-header glass-card">
        <div class="d-flex align-items-center gap-3">
            <div style="width:50px;height:50px;border-radius:16px;background:linear-gradient(135deg,#6366f1,#a855f7);display:flex;align-items:center;justify-content:center;font-size:1.5rem;color:white;box-shadow:0 8px 16px rgba(99,102,241,0.2);">
                <i class="bi bi-robot"></i>
            </div>
            <div>
                <h4 class="mb-0" style="font-weight:800;color:var(--text-primary);">AI Study Assistant</h4>
                <small style="color:var(--text-secondary);">Chat with your personal AI tutor — powered by your study materials</small>
            </div>
            <div class="ms-auto">
                <select id="personaSelect" class="form-select" style="width:auto;display:inline-block;border-radius:40px;font-size:0.8rem;padding:0.3rem 1rem;background:white;border:1.5px solid #e5e7eb;">
                    <option value="default">🎓 Professor (Default)</option>
                    <option value="strict">📚 Strict Professor</option>
                    <option value="friendly">🤗 Friendly Tutor</option>
                    <option value="socratic">❓ Socratic Tutor</option>
                    <option value="simplifier">💡 Simplifier</option>
                </select>
            </div>
        </div>
    </div>

    <div class="ai-chat-body glass-card">
        <div id="aiChatMessages" class="ai-chat-messages">
            <div class="message-bot">
                <div class="bot-avatar"><i class="bi bi-robot"></i></div>
                <div class="bubble bot-bubble">
                    👋 Hi {{ Auth::user()->name ?? 'there' }}! I'm your AI study assistant. I can help you understand concepts from your uploaded notes, generate practice questions, or explain any topic. What would you like to study today?
                </div>
            </div>
        </div>
        <div class="ai-chat-input-area">
            <input type="text" id="aiChatInput" placeholder="Ask about your study material..." autofocus>
            <button id="aiSendBtn"><i class="bi bi-send-fill"></i> Send</button>
        </div>
    </div>
</div>

<style>
    .ai-chat-container { max-width: 900px; margin: 0 auto; position: relative; z-index: 2; }
    .ai-chat-header { margin-bottom: 1.5rem; }

    .ai-chat-body { padding: 1.25rem; }
    .ai-chat-messages {
        height: 55vh; overflow-y: auto; padding: 0.5rem; margin-bottom: 1rem;
        display: flex; flex-direction: column; gap: 1rem;
    }
    .ai-chat-messages::-webkit-scrollbar { width: 4px; }
    .ai-chat-messages::-webkit-scrollbar-track { background: transparent; }
    .ai-chat-messages::-webkit-scrollbar-thumb { background: #c7d2fe; border-radius: 4px; }

    .message-bot, .message-user {
        display: flex; gap: 0.8rem; align-items: flex-start;
        animation: fadeSlideUp 0.3s ease-out;
    }
    .message-user { flex-direction: row-reverse; }

    .bot-avatar, .user-avatar {
        width: 36px; height: 36px;
        background: linear-gradient(135deg, #6366f1, #a855f7);
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        color: white; font-size: 1.1rem; flex-shrink: 0;
        box-shadow: 0 4px 12px rgba(99,102,241,0.2);
    }
    .user-avatar { background: linear-gradient(135deg, #a855f7, #c084fc); }

    .bubble {
        white-space: pre-wrap; word-wrap: break-word; max-width: 72%;
        padding: 0.75rem 1.1rem; border-radius: 1.2rem;
        font-size: 0.92rem; line-height: 1.5; word-break: break-word;
    }
    .user-bubble {
        background: #111827; color: white;
        border-bottom-right-radius: 0.3rem;
    }
    .bot-bubble {
        background: #f8fafc; color: #1e1b4b;
        border: 1.5px solid #e5e7eb;
        border-bottom-left-radius: 0.3rem;
    }

    @keyframes fadeSlideUp {
        from { opacity: 0; transform: translateY(12px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .typing-indicator {
        display: flex; align-items: center; gap: 5px;
        padding: 0.75rem 1.1rem; background: #f8fafc;
        border-radius: 1.2rem; border: 1.5px solid #e5e7eb; width: fit-content;
    }
    .typing-dot {
        width: 7px; height: 7px; background: #6366f1; border-radius: 50%;
        animation: typingBounce 1.2s infinite ease-in-out;
    }
    .typing-dot:nth-child(1) { animation-delay: 0s; }
    .typing-dot:nth-child(2) { animation-delay: 0.2s; }
    .typing-dot:nth-child(3) { animation-delay: 0.4s; }
    @keyframes typingBounce {
        0%, 60%, 100% { transform: translateY(0); opacity: 0.4; }
        30% { transform: translateY(-8px); opacity: 1; }
    }

    .ai-chat-input-area {
        display: flex; gap: 0.6rem;
        background: white; border: 1.5px solid #e5e7eb;
        border-radius: 40px; padding: 0.3rem;
    }
    .ai-chat-input-area input {
        flex: 1; background: transparent; border: none;
        padding: 0.7rem 1.2rem; font-size: 0.92rem; color: #111827;
        font-family: 'Inter', sans-serif; outline: none;
    }
    .ai-chat-input-area input::placeholder { color: #9ca3af; }
    .ai-chat-input-area button {
        background: #111827; color: white; border: none;
        border-radius: 40px; padding: 0.6rem 1.6rem; font-weight: 600;
        font-size: 0.85rem; font-family: 'Inter', sans-serif;
        cursor: pointer; transition: all 0.2s;
        display: flex; align-items: center; gap: 0.4rem;
    }
    .ai-chat-input-area button:hover {
        background: #1f2937;
        transform: translateY(-1px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.12);
    }

    @media (max-width: 768px) {
        .ai-chat-container { padding: 0; }
        .bubble { max-width: 85%; }
        .ai-chat-input-area button { padding: 0.6rem 1rem; }
        .bot-avatar, .user-avatar { width: 30px; height: 30px; font-size: 0.9rem; }
    }
</style>

<script>
    const messagesContainer = document.getElementById('aiChatMessages');
    const chatInput = document.getElementById('aiChatInput');
    const sendBtn = document.getElementById('aiSendBtn');

    function appendMessage(sender, text) {
        const msgDiv = document.createElement('div');
        msgDiv.className = sender === 'user' ? 'message-user' : 'message-bot';
        const avatar = sender === 'user'
            ? '<div class="user-avatar"><i class="bi bi-person"></i></div>'
            : '<div class="bot-avatar"><i class="bi bi-robot"></i></div>';
        const bubbleClass = sender === 'user' ? 'user-bubble' : 'bot-bubble';
        msgDiv.innerHTML = `${avatar}<div class="bubble ${bubbleClass}">${escapeHtml(text)}</div>`;
        messagesContainer.appendChild(msgDiv);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }
    function escapeHtml(text) { const d = document.createElement('div'); d.textContent = text; return d.innerHTML; }
    function showTypingIndicator() {
        const typingDiv = document.createElement('div');
        typingDiv.className = 'message-bot'; typingDiv.id = 'typingIndicator';
        typingDiv.innerHTML = `<div class="bot-avatar"><i class="bi bi-robot"></i></div><div class="typing-indicator"><div class="typing-dot"></div><div class="typing-dot"></div><div class="typing-dot"></div></div>`;
        messagesContainer.appendChild(typingDiv);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }
    function removeTypingIndicator() { const el = document.getElementById('typingIndicator'); if (el) el.remove(); }

    async function sendMessage() {
        const message = chatInput.value.trim();
        if (!message) return;
        appendMessage('user', message);
        chatInput.value = '';
        showTypingIndicator();
        const persona = document.getElementById('personaSelect')?.value || 'default';
        try {
            const response = await fetch('{{ route('chat.send') }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ message, persona })
            });
            const data = await response.json();
            removeTypingIndicator();
            appendMessage('bot', data.response || 'Sorry, no response from AI.');
        } catch (error) {
            removeTypingIndicator();
            appendMessage('bot', 'Network error. Please try again.');
        }
    }
    sendBtn.addEventListener('click', sendMessage);
    chatInput.addEventListener('keypress', (e) => { if (e.key === 'Enter') sendMessage(); });
</script>
@endsection
