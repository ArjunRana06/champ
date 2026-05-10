@extends('Backend.master')

@section('content')
<div class="ai-chat-container">
    <div class="ai-chat-header glass-effect">
        <div class="d-flex align-items-center gap-3">
            <div class="header-icon pulse-glow"><i class="bi bi-robot"></i></div>
            <div>
                <h4 class="mb-0 gradient-text">AI Assistant</h4>
                <small class="text-muted">Chat with your personal AI – powered by your memories</small>
            </div>
        </div>
        <a href="{{ route('events.index') }}" class="refresh-btn" title="Back to Timeline"><i class="bi bi-arrow-left"></i></a>
    </div>

    <div class="ai-chat-body glass-effect premium-glass">
        <div id="aiChatMessages" class="ai-chat-messages custom-scroll">
            <div class="message-bot">
                <div class="bot-avatar"><i class="bi bi-robot"></i></div>
                <div class="bubble bot-bubble">
                    👋 Hi {{ Auth::user()->name ?? 'there' }}! I'm your personal AI. I can answer questions about your timeline, help you reflect, or just chat. What would you like to talk about?
                </div>
            </div>
        </div>
        <div class="ai-chat-input-area">
            <input type="text" id="aiChatInput" placeholder="Ask me anything..." class="message-input" autofocus>
            <button id="aiSendBtn" class="send-button"><i class="bi bi-send-fill"></i> Send</button>
        </div>
    </div>
</div>

<style>
    /* Enhanced AI Chat Page Design */
    .ai-chat-container {
        max-width: 1000px;
        margin: 0 auto;
        padding: 1.5rem;
        position: relative;
        z-index: 2;
    }

    /* Animated gradient background behind the chat */
    .ai-chat-container::before {
        content: '';
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: radial-gradient(circle at 10% 20%, #eef9ff, #d9f0fa);
        z-index: -1;
        animation: bgPulse 15s ease infinite;
    }

    @keyframes bgPulse {
        0% { opacity: 1; }
        50% { opacity: 0.98; }
        100% { opacity: 1; }
    }

    .ai-chat-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem 1.8rem;
        margin-bottom: 1.5rem;
        background: rgba(255,255,255,0.55);
        backdrop-filter: blur(20px);
        border-radius: 2rem;
        border: 1px solid rgba(14,165,233,0.25);
        box-shadow: 0 8px 20px -6px rgba(0,0,0,0.1);
        transition: all 0.3s;
    }

    .ai-chat-header:hover {
        background: rgba(255,255,255,0.7);
        box-shadow: 0 12px 28px -8px rgba(14,165,233,0.2);
    }

    .header-icon {
        width: 52px;
        height: 52px;
        background: linear-gradient(135deg, #0ea5e9, #0284c7);
        border-radius: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
        color: white;
        box-shadow: 0 8px 16px rgba(14,165,233,0.3);
        transition: transform 0.2s;
    }

    .pulse-glow {
        animation: softPulse 2s infinite;
    }

    @keyframes softPulse {
        0% { box-shadow: 0 0 0 0 rgba(14,165,233,0.4); }
        70% { box-shadow: 0 0 0 12px rgba(14,165,233,0); }
        100% { box-shadow: 0 0 0 0 rgba(14,165,233,0); }
    }

    .gradient-text {
        background: linear-gradient(135deg, #0c4a6e, #0ea5e9);
        background-clip: text;
        -webkit-background-clip: text;
        color: transparent;
        font-weight: 700;
    }

    .refresh-btn {
        background: rgba(255,255,255,0.8);
        border: none;
        width: 42px;
        height: 42px;
        border-radius: 42px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s;
        color: #0c4a6e;
        font-size: 1.2rem;
        text-decoration: none;
    }

    .refresh-btn:hover {
        transform: rotate(-5deg) scale(1.05);
        background: #0ea5e9;
        color: white;
        box-shadow: 0 4px 12px rgba(14,165,233,0.4);
    }

    .ai-chat-body {
        background: rgba(255,255,255,0.4);
        backdrop-filter: blur(16px);
        border-radius: 2rem;
        padding: 1.5rem;
        border: 1px solid rgba(14,165,233,0.3);
        box-shadow: 0 20px 35px -12px rgba(0,0,0,0.12);
    }

    .ai-chat-messages {
        height: 55vh;
        overflow-y: auto;
        padding: 0.8rem;
        margin-bottom: 1.2rem;
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    /* Custom scrollbar */
    .custom-scroll::-webkit-scrollbar {
        width: 6px;
    }
    .custom-scroll::-webkit-scrollbar-track {
        background: #e0f2fe;
        border-radius: 10px;
    }
    .custom-scroll::-webkit-scrollbar-thumb {
        background: #38bdf8;
        border-radius: 10px;
    }

    /* Message bubbles – modern, with avatar */
    .message-bot, .message-user {
        display: flex;
        gap: 0.8rem;
        align-items: flex-start;
        animation: fadeSlideUp 0.3s ease-out;
    }

    .message-user {
        flex-direction: row-reverse;
    }

    .bot-avatar, .user-avatar {
        width: 36px;
        height: 36px;
        background: linear-gradient(135deg, #0ea5e9, #0284c7);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.2rem;
        flex-shrink: 0;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .user-avatar {
        background: linear-gradient(135deg, #6366f1, #4f46e5);
    }

    .bubble {
        max-width: 70%;
        padding: 0.7rem 1rem;
        border-radius: 1.5rem;
        font-size: 0.95rem;
        line-height: 1.4;
        word-break: break-word;
        box-shadow: 0 2px 6px rgba(0,0,0,0.05);
        transition: transform 0.2s;
    }

    .bubble:hover {
        transform: translateX(2px);
    }

    .user-bubble {
        background: #0ea5e9;
        color: white;
        border-bottom-right-radius: 0.3rem;
    }

    .bot-bubble {
        background: rgba(255,255,255,0.9);
        color: #0f172a;
        border-bottom-left-radius: 0.3rem;
        backdrop-filter: blur(4px);
        border: 1px solid rgba(14,165,233,0.2);
    }

    @keyframes fadeSlideUp {
        from {
            opacity: 0;
            transform: translateY(12px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Typing indicator animation */
    .typing-indicator {
        display: flex;
        align-items: center;
        gap: 6px;
        padding: 0.5rem 1rem;
        background: rgba(255,255,255,0.85);
        border-radius: 2rem;
        width: fit-content;
    }
    .typing-dot {
        width: 8px;
        height: 8px;
        background: #0ea5e9;
        border-radius: 50%;
        animation: typingBounce 1.2s infinite ease-in-out;
    }
    .typing-dot:nth-child(1) { animation-delay: 0s; }
    .typing-dot:nth-child(2) { animation-delay: 0.2s; }
    .typing-dot:nth-child(3) { animation-delay: 0.4s; }
    @keyframes typingBounce {
        0%, 60%, 100% { transform: translateY(0); opacity: 0.4; }
        30% { transform: translateY(-8px); opacity: 1; }
    }

    /* Input area */
    .ai-chat-input-area {
        display: flex;
        gap: 0.8rem;
        margin-top: 0.5rem;
        background: rgba(255,255,255,0.5);
        border-radius: 3rem;
        padding: 0.3rem;
        backdrop-filter: blur(4px);
        border: 1px solid rgba(14,165,233,0.2);
    }

    .ai-chat-input-area .message-input {
        flex: 1;
        background: rgba(255,255,255,0.9);
        border: none;
        border-radius: 2.5rem;
        padding: 0.8rem 1.2rem;
        font-size: 0.95rem;
        outline: none;
        transition: all 0.2s;
    }

    .ai-chat-input-area .message-input:focus {
        background: white;
        box-shadow: 0 0 0 3px rgba(14,165,233,0.2);
    }

    .ai-chat-input-area .send-button {
        background: linear-gradient(115deg, #0ea5e9, #0284c7);
        color: white;
        border: none;
        border-radius: 2.5rem;
        padding: 0.6rem 1.8rem;
        font-weight: 600;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .ai-chat-input-area .send-button:hover {
        transform: scale(1.02);
        background: linear-gradient(115deg, #0284c7, #0369a1);
        box-shadow: 0 6px 14px rgba(14,165,233,0.4);
    }

    @media (max-width: 768px) {
        .ai-chat-container {
            padding: 1rem;
        }
        .bubble {
            max-width: 85%;
        }
        .ai-chat-input-area .send-button {
            padding: 0.6rem 1.2rem;
        }
        .bot-avatar, .user-avatar {
            width: 30px;
            height: 30px;
            font-size: 1rem;
        }
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
        msgDiv.innerHTML = `
            ${avatar}
            <div class="bubble ${bubbleClass}">${escapeHtml(text)}</div>
        `;
        messagesContainer.appendChild(msgDiv);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function showTypingIndicator() {
        const typingDiv = document.createElement('div');
        typingDiv.className = 'message-bot';
        typingDiv.id = 'typingIndicator';
        typingDiv.innerHTML = `
            <div class="bot-avatar"><i class="bi bi-robot"></i></div>
            <div class="typing-indicator">
                <div class="typing-dot"></div>
                <div class="typing-dot"></div>
                <div class="typing-dot"></div>
            </div>
        `;
        messagesContainer.appendChild(typingDiv);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    function removeTypingIndicator() {
        const indicator = document.getElementById('typingIndicator');
        if (indicator) indicator.remove();
    }

    async function sendMessage() {
        const message = chatInput.value.trim();
        if (!message) return;

        appendMessage('user', message);
        chatInput.value = '';
        showTypingIndicator();

        try {
            const response = await fetch('{{ route("chat.send") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ message: message })
            });
            const data = await response.json();
            removeTypingIndicator();
            if (data.response) {
                appendMessage('bot', data.response);
            } else {
                appendMessage('bot', 'Sorry, no response from AI.');
            }
        } catch (error) {
            removeTypingIndicator();
            appendMessage('bot', 'Network error. Please try again.');
            console.error(error);
        }
    }

    sendBtn.addEventListener('click', sendMessage);
    chatInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') sendMessage();
    });
</script>
@endsection
