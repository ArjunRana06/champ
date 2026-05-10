@extends('Backend.master')

@section('content')
<div class="chat-wrapper" x-data="{ filter: 'chats' }">
    <div class="two-columns">
        <!-- LEFT COLUMN: Original Chat (unchanged) -->
        <div class="left-column">
            <div class="chat-header glass-effect">
                <div class="d-flex align-items-center gap-3">
                    <div class="header-icon"><i class="bi bi-chat-dots-fill"></i></div>
                    <div>
                        <h4 class="mb-0 gradient-text">Timeline Conversation</h4>
                        <small class="text-muted">
                            <span x-text="{
                                chats: {{ $activities->whereNotIn('type', ['image','video'])->count() }},
                                images: {{ $activities->where('type','image')->count() }},
                                videos: {{ $activities->where('type','video')->count() }}
                            }[filter]"></span> memories · replay your story
                        </small>
                    </div>
                </div>
                <button class="refresh-btn" onclick="window.location.reload()"><i class="bi bi-arrow-repeat"></i></button>
            </div>

            <!-- Messages Area (exactly as you had, but each message has x-show) -->
            <div class="messages-area" id="messagesArea">
                @forelse($activities as $activity)
                @php
                $showChat = !in_array($activity->type, ['image', 'video']);
                $showImage = $activity->type === 'image';
                $showVideo = $activity->type === 'video';
                @endphp
                <div class="message-group"
                    x-show="filter === 'chats' ? {{ $showChat ? 'true' : 'false' }} : (filter === 'images' ? {{ $showImage ? 'true' : 'false' }} : {{ $showVideo ? 'true' : 'false' }})"
                    x-transition.duration.300ms>
                    <div class="message-bubble">
                        <div class="bubble-wrapper">
                            <div class="message-meta">
                                <span class="type-badge">
                                    @switch($activity->type)
                                    @case('text') <i class="bi bi-chat-text"></i> @break
                                    @case('voice') <i class="bi bi-mic"></i> @break
                                    @case('video') <i class="bi bi-camera-reels"></i> @break
                                    @case('image') <i class="bi bi-image"></i> @break
                                    @case('emoji') <i class="bi bi-emoji-smile"></i> @break
                                    @case('emotion') <i class="bi bi-heart"></i> @break
                                    @endswitch
                                    {{ ucfirst($activity->type) }}
                                </span>
                                <span class="time">{{ $activity->created_at->diffForHumans() }}</span>
                            </div>
                            <div class="message-content">
                                @if($activity->type === 'text' || $activity->type === 'emoji')
                                <p>{{ $activity->parsed_content }}</p>
                                @elseif($activity->type === 'emotion')
                                <p>Feeling: <strong>{{ $activity->parsed_content }}</strong></p>
                                @elseif(in_array($activity->type, ['image', 'video', 'voice']))
                                @if($activity->file_path)
                                @if($activity->type === 'image')
                                <img src="{{ $activity->file_url }}" class="media-file" alt="image">
                                @elseif($activity->type === 'video')
                                <video controls class="media-file">
                                    <source src="{{ $activity->file_url }}" type="video/mp4">
                                </video>
                                @elseif($activity->type === 'voice')
                                <audio controls class="audio-file">
                                    <source src="{{ $activity->file_url }}" type="audio/mpeg">
                                </audio>
                                @endif
                                @else
                                <span class="text-muted">[File missing]</span>
                                @endif
                                @endif
                            </div>
                            @if($activity->emotions->isNotEmpty())
                            <div class="emotion-chip">
                                <i class="bi bi-emoji-smile"></i> {{ $activity->emotions->first()->emotion }}
                            </div>
                            @endif
                        </div>
                        <div class="action-icons">
                            <a href="{{ route('events.edit', $activity) }}" class="action-icon edit-icon"><i class="bi bi-pencil-square"></i></a>
                            <form action="{{ route('events.destroy', $activity) }}" method="POST" onsubmit="return confirm('Delete this memory?')">
                                @csrf @method('DELETE')
                                <button class="action-icon delete-icon"><i class="bi bi-trash3"></i></button>
                            </form>
                        </div>
                    </div>
                </div>
                @empty
                <div class="empty-state">
                    <i class="bi bi-chat-square-dots"></i>
                    <p>No memories yet. Start the conversation below ✨</p>
                </div>
                @endforelse
            </div>

            <!-- Floating scroll button -->
            <button class="scroll-bottom-btn" id="scrollBottomBtn" title="Scroll to bottom"><i class="bi bi-arrow-down-circle-fill"></i></button>

            <!-- Input Area (exactly as you had) -->
            <div class="input-container glass-effect">
                <form method="POST" action="{{ route('events.store') }}" enctype="multipart/form-data" id="chatForm">
                    @csrf
                    <div class="input-row">
                        <select name="emotion" class="emotion-select">
                            <option value="">😊 Mood</option>
                            <option value="happy">😊 Happy</option>
                            <option value="excited">🤩 Excited</option>
                            <option value="neutral">😐 Neutral</option>
                            <option value="sad">😔 Sad</option>
                            <option value="stressed">😫 Stressed</option>
                        </select>
                        <input type="text" name="content" class="message-input" placeholder="Write your memory..." id="messageInput"
                            x-ref="messageInput" @keydown.enter.prevent="submitForm()" autofocus>
                        <button type="button" class="attach-button" onclick="document.getElementById('fileInput').click();">
                            <i class="bi bi-paperclip"></i>
                        </button>
                        <input type="file" name="file" id="fileInput" style="display:none;" accept="image/*,video/*,audio/*">
                        <button type="submit" class="send-button" id="sendButton">
                            <i class="bi bi-send-fill"></i>
                        </button>
                    </div>
                    <div id="filePreviewContainer" class="file-preview-card" style="display:none;"></div>
                </form>
            </div>
        </div>

        <!-- RIGHT COLUMN: Filter Buttons (new) -->
        <div class="right-column glass-effect">
            <h5 class="filter-title"><i class="bi bi-funnel-fill"></i> Filter memories</h5>
            <div class="filter-buttons">
                <button @click="filter = 'chats'" :class="{ active: filter === 'chats' }" class="filter-btn chats-btn">
                    <i class="bi bi-chat-dots-fill"></i> Chats
                    <span class="badge">{{ $activities->whereNotIn('type', ['image','video'])->count() }}</span>
                </button>
                <button @click="filter = 'images'" :class="{ active: filter === 'images' }" class="filter-btn images-btn">
                    <i class="bi bi-image-fill"></i> Images
                    <span class="badge">{{ $activities->where('type', 'image')->count() }}</span>
                </button>
                <button @click="filter = 'videos'" :class="{ active: filter === 'videos' }" class="filter-btn videos-btn">
                    <i class="bi bi-camera-reels-fill"></i> Videos
                    <span class="badge">{{ $activities->where('type', 'video')->count() }}</span>
                </button>
            </div>
            <div class="filter-info" x-text="filter === 'chats' ? '💬 All text, emotion & voice memories' : (filter === 'images' ? '🖼️ Showing only uploaded images' : '🎬 Showing only uploaded videos')"></div>
        </div>
    </div>
</div>

<style>
    /* ========== TWO-COLUMN LAYOUT ========== */
    .two-columns {
        display: flex;
        gap: 1.5rem;
    }

    .left-column {
        flex: 3;
        min-width: 0;
    }

    .right-column {
        flex: 1;
        background: rgba(255, 255, 255, 0.4);
        backdrop-filter: blur(12px);
        border-radius: 2rem;
        padding: 1.2rem;
        height: fit-content;
        position: sticky;
        top: 1rem;
    }

    .filter-title {
        font-size: 1rem;
        font-weight: 600;
        color: #0c4a6e;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .filter-buttons {
        display: flex;
        flex-direction: column;
        gap: 0.8rem;
    }

    .filter-btn {
        background: rgba(255, 255, 255, 0.6);
        border: 1px solid rgba(14, 165, 233, 0.3);
        border-radius: 3rem;
        padding: 0.7rem 1rem;
        font-weight: 500;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        gap: 0.8rem;
        color: #0c4a6e;
        cursor: pointer;
        width: 100%;
    }

    .filter-btn i {
        font-size: 1.2rem;
    }

    .filter-btn .badge {
        margin-left: auto;
        background: #e0f2fe;
        border-radius: 30px;
        padding: 0.1rem 0.6rem;
        font-size: 0.7rem;
        font-weight: 600;
    }

    .filter-btn.active {
        background: linear-gradient(135deg, #0ea5e9, #0284c7);
        color: white;
        border-color: transparent;
        box-shadow: 0 6px 12px rgba(14, 165, 233, 0.3);
    }

    .filter-btn.active .badge {
        background: rgba(255, 255, 255, 0.3);
        color: white;
    }

    .filter-info {
        margin-top: 1rem;
        font-size: 0.75rem;
        color: #475569;
        text-align: center;
        padding-top: 0.8rem;
        border-top: 1px solid rgba(14, 165, 233, 0.2);
    }

    @media (max-width: 768px) {
        .two-columns {
            flex-direction: column;
        }

        .right-column {
            position: static;
            order: -1;
        }
    }

    /* ========== ALL YOUR ORIGINAL CHAT STYLES (keep everything from your working version) ========== */
    /* I'm including them exactly as they were – replace with your existing CSS */
    .chat-wrapper {
        background: linear-gradient(145deg, #f0f9ff 0%, #e6f7ff 100%);
        border-radius: 2rem;
        padding: 1.5rem;
        box-shadow: 0 20px 40px -12px rgba(0, 0, 0, 0.1);
    }

    .glass-effect {
        background: rgba(255, 255, 255, 0.6);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(14, 165, 233, 0.2);
        border-radius: 2rem;
    }

    .chat-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem 1.5rem;
        margin-bottom: 1.5rem;
    }

    .header-icon {
        width: 48px;
        height: 48px;
        background: linear-gradient(135deg, #0ea5e9, #0284c7);
        border-radius: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.6rem;
        color: white;
        box-shadow: 0 6px 12px rgba(14, 165, 233, 0.3);
        transition: transform 0.2s;
    }

    .header-icon:hover {
        transform: scale(1.05);
    }

    .gradient-text {
        background: linear-gradient(135deg, #0c4a6e, #0ea5e9);
        background-clip: text;
        -webkit-background-clip: text;
        color: transparent;
        font-weight: 700;
    }

    .refresh-btn {
        background: white;
        border: none;
        width: 40px;
        height: 40px;
        border-radius: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s;
        color: #0c4a6e;
    }

    .refresh-btn:hover {
        transform: rotate(90deg) scale(1.05);
        background: #0ea5e9;
        color: white;
    }

    .messages-area {
        height: 55vh;
        overflow-y: auto;
        padding: 0.5rem 0.5rem 1rem 0.5rem;
        scroll-behavior: smooth;
    }

    .messages-area::-webkit-scrollbar {
        width: 6px;
    }

    .messages-area::-webkit-scrollbar-track {
        background: #e0f2fe;
        border-radius: 10px;
    }

    .messages-area::-webkit-scrollbar-thumb {
        background: #7dd3fc;
        border-radius: 10px;
    }

    .message-group {
        margin-bottom: 1.2rem;
        animation: fadeSlideUp 0.35s cubic-bezier(0.2, 0.9, 0.4, 1.1);
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

    .message-bubble {
        display: flex;
        justify-content: flex-start;
        gap: 12px;
    }

    .bubble-wrapper {
        max-width: 75%;
        background: white;
        border-radius: 1.8rem 1.8rem 1.8rem 0.8rem;
        padding: 0.9rem 1.2rem;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        transition: all 0.25s;
    }

    .bubble-wrapper:hover {
        transform: translateX(4px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
    }

    .message-meta {
        display: flex;
        justify-content: space-between;
        margin-bottom: 0.5rem;
        font-size: 0.7rem;
    }

    .type-badge {
        background: #e6f7ff;
        padding: 0.2rem 0.6rem;
        border-radius: 30px;
        color: #0284c7;
        font-weight: 500;
    }

    .time {
        color: #6c8b9b;
    }

    .message-content {
        font-size: 0.9rem;
        word-break: break-word;
    }

    .media-file {
        max-width: 260px;
        max-height: 200px;
        border-radius: 1rem;
        margin-top: 0.5rem;
        cursor: pointer;
        transition: transform 0.2s;
    }

    .media-file:hover {
        transform: scale(1.01);
    }

    .audio-file {
        width: 220px;
        height: 40px;
    }

    .emotion-chip {
        display: inline-block;
        font-size: 0.7rem;
        background: #f1f5f9;
        border-radius: 30px;
        padding: 0.2rem 0.8rem;
        margin-top: 0.5rem;
    }

    .action-icons {
        display: flex;
        flex-direction: column;
        gap: 6px;
        opacity: 0.4;
        transition: opacity 0.2s;
    }

    .message-bubble:hover .action-icons {
        opacity: 1;
    }

    .action-icon {
        background: transparent;
        border: none;
        font-size: 1rem;
        color: #64748b;
        transition: all 0.2s;
    }

    .edit-icon:hover {
        color: #0ea5e9;
        transform: scale(1.1);
    }

    .delete-icon:hover {
        color: #f43f5e;
        transform: scale(1.1);
    }

    .empty-state {
        text-align: center;
        padding: 3rem;
        color: #94a3b8;
    }

    .empty-state i {
        font-size: 3rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }

    .input-container {
        margin-top: 1.5rem;
        padding: 0.8rem;
        backdrop-filter: blur(12px);
    }

    .input-row {
        display: flex;
        gap: 12px;
        align-items: center;
        flex-wrap: wrap;
    }

    .emotion-select {
        background: white;
        border: 1px solid #cbd5e1;
        border-radius: 40px;
        padding: 0.6rem 1rem;
        font-size: 0.9rem;
        cursor: pointer;
        transition: all 0.2s;
    }

    .emotion-select:hover {
        border-color: #0ea5e9;
    }

    .message-input {
        flex: 1;
        background: white;
        border: 1px solid #cbd5e1;
        border-radius: 40px;
        padding: 0.6rem 1.2rem;
        font-size: 0.9rem;
        transition: 0.2s;
    }

    .message-input:focus {
        outline: none;
        border-color: #0ea5e9;
        box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.2);
    }

    .attach-button,
    .send-button {
        background: white;
        border: 1px solid #cbd5e1;
        border-radius: 40px;
        width: 44px;
        height: 44px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
        color: #0c4a6e;
    }

    .attach-button:hover {
        background: #e0f2fe;
        transform: scale(1.05);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .send-button {
        background: linear-gradient(135deg, #0ea5e9, #0284c7);
        color: white;
        border: none;
    }

    .send-button:hover:not(:disabled) {
        transform: scale(1.05);
        box-shadow: 0 6px 14px rgba(14, 165, 233, 0.4);
    }

    .file-preview-card {
        margin-top: 12px;
        background: rgba(255, 255, 255, 0.95);
        border-radius: 1.2rem;
        padding: 0.6rem;
        border: 1px solid #e2e8f0;
        animation: slideDown 0.2s ease-out;
    }

    .preview-flex {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .preview-image {
        width: 50px;
        height: 50px;
        object-fit: cover;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
    }

    .preview-file-icon {
        font-size: 2.2rem;
        color: #0ea5e9;
    }

    .preview-details {
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .preview-filename {
        font-size: 0.85rem;
        font-weight: 600;
        color: #0c4a6e;
    }

    .preview-filesize {
        font-size: 0.7rem;
        color: #64748b;
    }

    .preview-close {
        background: none;
        border: none;
        font-size: 1.5rem;
        cursor: pointer;
        color: #94a3b8;
        transition: 0.2s;
        line-height: 1;
    }

    .preview-close:hover {
        color: #ef4444;
        transform: scale(1.1);
    }

    .scroll-bottom-btn {
        position: fixed;
        bottom: 2rem;
        right: 2rem;
        background: linear-gradient(135deg, #0ea5e9, #0284c7);
        border: none;
        border-radius: 60px;
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 2rem;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        cursor: pointer;
        transition: all 0.2s;
        z-index: 999;
        opacity: 0;
        visibility: hidden;
        transform: scale(0.8);
    }

    .scroll-bottom-btn.show {
        opacity: 1;
        visibility: visible;
        transform: scale(1);
    }

    .scroll-bottom-btn:hover {
        transform: scale(1.1);
        background: linear-gradient(135deg, #0284c7, #0369a1);
        box-shadow: 0 6px 16px rgba(2, 132, 199, 0.4);
    }

    [x-cloak] {
        display: none;
    }

    .edit-textarea:focus {
        outline: none;
        border-color: #0ea5e9;
        box-shadow: 0 0 0 2px rgba(14, 165, 233, 0.2);
    }

    .save-edit-btn,
    .cancel-edit-btn {
        cursor: pointer;
        transition: transform 0.1s;
    }

    .save-edit-btn:hover {
        background: #0284c7 !important;
        transform: scale(1.02);
    }

    .cancel-edit-btn:hover {
        background: #cbd5e1 !important;
        transform: scale(1.02);
    }
</style>

<script>
    // File preview and send button logic (same as your working version)
    document.addEventListener('DOMContentLoaded', function() {
        const fileInput = document.getElementById('fileInput');
        const previewContainer = document.getElementById('filePreviewContainer');

        function updateFilePreview() {
            const file = fileInput.files[0];
            if (!file) {
                previewContainer.style.display = 'none';
                previewContainer.innerHTML = '';
                return;
            }
            let previewHTML = '<div class="preview-flex">';
            if (file.type.startsWith('image/')) {
                const imgURL = URL.createObjectURL(file);
                previewHTML += `<img src="${imgURL}" class="preview-image">`;
            } else if (file.type.startsWith('video/')) {
                previewHTML += `<div class="preview-file-icon"><i class="bi bi-camera-reels-fill"></i></div>`;
            } else {
                previewHTML += `<div class="preview-file-icon"><i class="bi bi-file-earmark-fill"></i></div>`;
            }
            const fileSize = (file.size / 1024).toFixed(1) + ' KB';
            previewHTML += `
                <div class="preview-details">
                    <span class="preview-filename">${escapeHtml(file.name)}</span>
                    <span class="preview-filesize">${fileSize}</span>
                </div>
                <button type="button" class="preview-close" id="removePreviewBtn">×</button>
            </div>`;
            previewContainer.innerHTML = previewHTML;
            previewContainer.style.display = 'block';

            const removeBtn = document.getElementById('removePreviewBtn');
            if (removeBtn) {
                removeBtn.addEventListener('click', function() {
                    fileInput.value = '';
                    previewContainer.style.display = 'none';
                    previewContainer.innerHTML = '';
                });
            }
        }
        fileInput.addEventListener('change', updateFilePreview);

        function escapeHtml(str) {
            if (!str) return '';
            return str.replace(/[&<>]/g, function(m) {
                if (m === '&') return '&amp;';
                if (m === '<') return '&lt;';
                if (m === '>') return '&gt;';
                return m;
            });
        }

        // Send button logic (enable when text or file)
        const textInput = document.getElementById('messageInput');
        const sendButton = document.getElementById('sendButton');

        function updateSendButton() {
            const hasText = textInput.value.trim().length > 0;
            const hasFile = fileInput.files.length > 0;
            sendButton.disabled = !(hasText || hasFile);
        }
        textInput.addEventListener('input', updateSendButton);
        fileInput.addEventListener('change', updateSendButton);
        updateSendButton();

        // Scroll to bottom
        const area = document.getElementById('messagesArea');
        if (area) area.scrollTop = area.scrollHeight;
        const scrollBtn = document.getElementById('scrollBottomBtn');
        if (scrollBtn && area) {
            area.addEventListener('scroll', () => {
                const isNearBottom = area.scrollHeight - area.scrollTop - area.clientHeight < 100;
                if (isNearBottom) scrollBtn.classList.remove('show');
                else scrollBtn.classList.add('show');
            });
            scrollBtn.addEventListener('click', () => {
                area.scrollTo({
                    top: area.scrollHeight,
                    behavior: 'smooth'
                });
            });
        }
    });
</script>
@endsection
