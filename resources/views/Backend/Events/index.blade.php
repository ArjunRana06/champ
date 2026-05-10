@extends('Backend.master')

@section('content')
    <div class="chat-wrapper" x-data="{ filter: 'chats' }">
        <div class="two-columns">
            <!-- LEFT COLUMN (slimmer) -->
            <div class="left-column">
                <div class="chat-header glass-effect">
                    <div class="d-flex align-items-center gap-3">
                        <div class="header-icon"><i class="bi bi-chat-dots-fill"></i></div>
                        <div>
                            <h4 class="mb-0 gradient-text">Timeline Conversation</h4>
                            <small class="text-muted">
                                <span
                                    x-text="{
                                chats: {{ $activities->whereNotIn('type', ['image', 'video'])->count() }},
                                images: {{ $activities->where('type', 'image')->count() }},
                                videos: {{ $activities->where('type', 'video')->count() }}
                            }[filter]"></span>
                                memories · replay your story
                            </small>
                        </div>
                    </div>
                    <button class="refresh-btn" onclick="window.location.reload()"><i
                            class="bi bi-arrow-repeat"></i></button>
                </div>

                <!-- Messages Area -->
                <div class="messages-area" id="messagesArea">
                    @forelse($activities as $activity)
                        @php
                            $showChat = !in_array($activity->type, ['image', 'video']);
                            $showImage = $activity->type === 'image';
                            $showVideo = $activity->type === 'video';
                            $isEditable = in_array($activity->type, ['text', 'emoji', 'emotion']);
                            $updateRoute = route('events.update', $activity);
                            $currentEmotion = optional($activity->emotions->first())->emotion ?? '';
                        @endphp

                        <div class="message-group"
                            x-show="filter === 'chats' ? {{ $showChat ? 'true' : 'false' }} : (filter === 'images' ? {{ $showImage ? 'true' : 'false' }} : {{ $showVideo ? 'true' : 'false' }})"
                            x-transition.duration.300ms>

                            <div class="message-bubble">
                                <div class="bubble-wrapper">
                                    <div class="message-meta">
                                        <span class="type-badge">
                                            @switch($activity->type)
                                                @case('text')
                                                    <i class="bi bi-chat-text"></i>
                                                @break

                                                @case('voice')
                                                    <i class="bi bi-mic"></i>
                                                @break

                                                @case('video')
                                                    <i class="bi bi-camera-reels"></i>
                                                @break

                                                @case('image')
                                                    <i class="bi bi-image"></i>
                                                @break

                                                @case('emoji')
                                                    <i class="bi bi-emoji-smile"></i>
                                                @break

                                                @case('emotion')
                                                    <i class="bi bi-heart"></i>
                                                @break
                                            @endswitch
                                            {{ ucfirst($activity->type) }}
                                        </span>
                                        <span class="time">{{ $activity->created_at->diffForHumans() }}</span>
                                    </div>

                                    <!-- Normal display content -->
                                    <div class="message-content" id="normal-content-{{ $activity->id }}">
                                        @if ($activity->type === 'text' || $activity->type === 'emoji')
                                            <p id="msg-text-{{ $activity->id }}">{{ $activity->parsed_content }}</p>
                                        @elseif($activity->type === 'emotion')
                                            <p>Feeling: <strong
                                                    id="msg-text-{{ $activity->id }}">{{ $activity->parsed_content }}</strong>
                                            </p>
                                        @elseif(in_array($activity->type, ['image', 'video', 'voice']))
                                            @if ($activity->file_path)
                                                @if ($activity->type === 'image')
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

                                    <!-- Edit form (hidden by default) -->
                                    <div id="edit-form-{{ $activity->id }}" style="display: none;">
                                        <textarea id="edit-content-{{ $activity->id }}" class="edit-textarea" rows="2"></textarea>
                                        <div style="margin-top: 0.5rem;">
                                            <select id="edit-emotion-{{ $activity->id }}" class="edit-emotion-select">
                                                <option value="">😊 No mood</option>
                                                <option value="happy" {{ $currentEmotion == 'happy' ? 'selected' : '' }}>
                                                    😊 Happy</option>
                                                <option value="excited"
                                                    {{ $currentEmotion == 'excited' ? 'selected' : '' }}>🤩 Excited
                                                </option>
                                                <option value="neutral"
                                                    {{ $currentEmotion == 'neutral' ? 'selected' : '' }}>😐 Neutral
                                                </option>
                                                <option value="sad" {{ $currentEmotion == 'sad' ? 'selected' : '' }}>😔
                                                    Sad</option>
                                                <option value="stressed"
                                                    {{ $currentEmotion == 'stressed' ? 'selected' : '' }}>😫 Stressed
                                                </option>
                                            </select>
                                        </div>
                                        <div style="display:flex; gap:0.5rem; margin-top:0.8rem;">
                                            <button onclick="saveEdit({{ $activity->id }}, '{{ $updateRoute }}')"
                                                class="save-edit-btn">💾 Save</button>
                                            <button onclick="cancelEdit({{ $activity->id }})"
                                                class="cancel-edit-btn">Cancel</button>
                                        </div>
                                    </div>

                                    @if ($activity->emotions->isNotEmpty())
                                        <div class="emotion-chip" id="emotion-chip-{{ $activity->id }}">
                                            <i class="bi bi-emoji-smile"></i> {{ $currentEmotion }}
                                        </div>
                                    @endif
                                </div>

                                <!-- Action Icons -->
                                <div class="action-icons">
                                    @if ($isEditable)
                                        <button class="action-icon edit-icon" onclick="toggleEdit({{ $activity->id }})"
                                            title="Edit message">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                    @endif
                                    <form action="{{ route('events.destroy', $activity) }}" method="POST"
                                        onsubmit="return confirm('Delete this memory?')">
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
                    <button class="scroll-bottom-btn" id="scrollBottomBtn" title="Scroll to bottom"><i
                            class="bi bi-arrow-down-circle-fill"></i></button>

                    <!-- Input Area -->
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
                                <input type="text" name="content" class="message-input" placeholder="Write your memory..."
                                    id="messageInput" x-ref="messageInput" @keydown.enter.prevent="submitForm()" autofocus>
                                <button type="button" class="attach-button"
                                    onclick="document.getElementById('fileInput').click();">
                                    <i class="bi bi-paperclip"></i>
                                </button>
                                <input type="file" name="file" id="fileInput" style="display:none;"
                                    accept="image/*,video/*,audio/*">
                                <button type="submit" class="send-button" id="sendButton">
                                    <i class="bi bi-send-fill"></i>
                                </button>
                            </div>
                            <div id="filePreviewContainer" class="file-preview-card" style="display:none;"></div>
                        </form>
                    </div>
                </div>

                <!-- RIGHT COLUMN (wider) -->
                <div class="right-column glass-effect">
                    <h5 class="filter-title"><i class="bi bi-funnel-fill"></i> Filter memories</h5>
                    <div class="filter-buttons">
                        <button type="button" data-type="chats" class="filter-btn chats-btn">
                            <i class="bi bi-chat-dots-fill"></i> Chats
                            <span class="badge">{{ $activities->whereNotIn('type', ['image', 'video'])->count() }}</span>
                        </button>
                        <button type="button" data-type="images" class="filter-btn images-btn">
                            <i class="bi bi-image-fill"></i> Images
                            <span class="badge">{{ $activities->where('type', 'image')->count() }}</span>
                        </button>
                        <button type="button" data-type="videos" class="filter-btn videos-btn">
                            <i class="bi bi-camera-reels-fill"></i> Videos
                            <span class="badge">{{ $activities->where('type', 'video')->count() }}</span>
                        </button>
                    </div>

                    <!-- New AI Assistant Button (matches filter-btn style) -->
                    <a href="{{ route('ai.chat') }}" class="filter-btn ai-btn" style="margin-top: 1rem;">
                        <i class="bi bi-robot"></i> AI Assistant
                        <span class="badge">✨</span>
                    </a>

                    <!-- Container for filtered items -->
                    <div id="filteredItemsContainer" class="filtered-container" style="display: none;">
                        <div class="filtered-header">
                            <i class="bi bi-collection"></i> <span id="filteredTitle"></span>
                            <button id="closeFiltered" class="close-filtered"><i class="bi bi-x-lg"></i></button>
                        </div>
                        <div id="filteredItemsList" class="filtered-list"></div>
                    </div>
                </div>
            </div>
        </div>

        <style>
            /* ========== FULL CSS – DO NOT REMOVE ========== */
            .two-columns {
                display: flex;
                gap: 1.8rem;
            }

            .left-column {
                flex: 2.4;
                min-width: 0;
            }

            .right-column {
                flex: 1.6;
                background: rgba(255, 255, 255, 0.45);
                backdrop-filter: blur(16px);
                border-radius: 2rem;
                padding: 1.5rem;
                height: fit-content;
                position: sticky;
                top: 1rem;
                transition: all 0.3s ease;
                border: 1px solid rgba(14, 165, 233, 0.25);
            }

            .right-column:hover {
                background: rgba(255, 255, 255, 0.55);
                box-shadow: 0 8px 20px -6px rgba(0, 0, 0, 0.08);
            }

            .filter-title {
                font-size: 1rem;
                font-weight: 700;
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
                background: rgba(255, 255, 255, 0.7);
                border: 1px solid rgba(14, 165, 233, 0.4);
                border-radius: 3rem;
                padding: 0.75rem 1rem;
                font-weight: 600;
                transition: all 0.25s cubic-bezier(0.2, 0.9, 0.4, 1.1);
                display: flex;
                align-items: center;
                gap: 0.8rem;
                color: #0c4a6e;
                cursor: pointer;
                width: 100%;
                position: relative;
                overflow: hidden;
            }

            .filter-btn::after {
                content: '';
                position: absolute;
                top: 50%;
                left: 50%;
                width: 0;
                height: 0;
                background: rgba(14, 165, 233, 0.2);
                border-radius: 50%;
                transform: translate(-50%, -50%);
                transition: width 0.4s, height 0.4s;
            }

            .filter-btn:active::after {
                width: 200px;
                height: 200px;
                opacity: 0;
            }

            .filter-btn i {
                font-size: 1.2rem;
                transition: transform 0.2s;
            }

            .filter-btn:hover i {
                transform: scale(1.1);
            }

            .filter-btn .badge {
                margin-left: auto;
                background: #e0f2fe;
                border-radius: 30px;
                padding: 0.2rem 0.7rem;
                font-size: 0.7rem;
                font-weight: 700;
            }

            .filter-btn.active {
                background: linear-gradient(105deg, #0ea5e9, #0284c7);
                color: white;
                border-color: transparent;
                box-shadow: 0 8px 18px rgba(14, 165, 233, 0.4);
            }

            .filter-btn.active .badge {
                background: rgba(255, 255, 255, 0.25);
                color: white;
            }

            .filtered-container {
                background: rgba(255, 255, 255, 0.75);
                backdrop-filter: blur(12px);
                border-radius: 1.5rem;
                padding: 1rem;
                border: 1px solid rgba(14, 165, 233, 0.35);
                max-height: 450px;
                overflow-y: auto;
                margin-top: 1.5rem;
                animation: fadeSlideUp 0.3s ease-out;
            }

            @keyframes fadeSlideUp {
                from {
                    opacity: 0;
                    transform: translateY(10px);
                }

                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            .filtered-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding-bottom: 0.6rem;
                margin-bottom: 1rem;
                border-bottom: 1px solid rgba(14, 165, 233, 0.3);
                font-weight: 700;
                color: #0c4a6e;
            }

            .close-filtered {
                background: rgba(0, 0, 0, 0.05);
                border: none;
                font-size: 1rem;
                width: 28px;
                height: 28px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                transition: all 0.2s;
            }

            .close-filtered:hover {
                background: #fee2e2;
                color: #ef4444;
                transform: rotate(90deg);
            }

            .filtered-item {
                background: white;
                border-radius: 1.2rem;
                padding: 0.8rem;
                margin-bottom: 0.8rem;
                box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
                transition: all 0.2s;
                border: 1px solid rgba(0, 0, 0, 0.03);
            }

            .filtered-item:hover {
                transform: translateX(5px);
                background: #fafcff;
                border-left: 3px solid #0ea5e9;
                box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
            }

            .filtered-item-meta {
                display: flex;
                justify-content: space-between;
                font-size: 0.7rem;
                margin-bottom: 0.3rem;
            }

            .filtered-badge {
                background: #e0f2fe;
                padding: 0.1rem 0.5rem;
                border-radius: 30px;
                color: #0284c7;
            }

            .filtered-time {
                color: #6c8b9b;
            }

            .filtered-item-content {
                font-size: 0.85rem;
                word-break: break-word;
            }

            .filtered-media {
                max-width: 100%;
                max-height: 150px;
                border-radius: 0.8rem;
                margin-top: 0.3rem;
            }

            .filtered-empty {
                text-align: center;
                color: #94a3b8;
                padding: 1rem;
            }

            /* Chat styles */
            .chat-wrapper {
                background: radial-gradient(circle at 10% 30%, #eef9ff, #dfeffa);
                border-radius: 2rem;
                padding: 1.5rem;
                box-shadow: 0 20px 40px -12px rgba(0, 0, 0, 0.12);
            }

            .glass-effect {
                background: rgba(255, 255, 255, 0.6);
                backdrop-filter: blur(14px);
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
                width: 5px;
            }

            .messages-area::-webkit-scrollbar-track {
                background: #e0f2fe;
                border-radius: 10px;
            }

            .messages-area::-webkit-scrollbar-thumb {
                background: #38bdf8;
                border-radius: 10px;
            }

            .message-group {
                margin-bottom: 1.2rem;
                animation: messageGlow 0.4s ease-out;
            }

            @keyframes messageGlow {
                0% {
                    opacity: 0;
                    transform: translateY(10px);
                }

                100% {
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
                transition: all 0.25s cubic-bezier(0.2, 0.9, 0.4, 1.1);
            }

            .bubble-wrapper:hover {
                transform: translateX(6px) scale(1.01);
                box-shadow: 0 12px 22px -10px rgba(0, 0, 0, 0.12);
            }

            .message-meta {
                display: flex;
                justify-content: space-between;
                margin-bottom: 0.5rem;
                font-size: 0.7rem;
            }

            .type-badge {
                background: #e0f2fecc;
                backdrop-filter: blur(2px);
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
                display: inline-flex;
                align-items: center;
                gap: 0.2rem;
                font-size: 0.7rem;
                background: #f1f5f9;
                border-radius: 50px;
                padding: 0.2rem 0.9rem;
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
                cursor: pointer;
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
                background: rgba(255, 255, 255, 0.9);
                backdrop-filter: blur(4px);
                border: 1px solid #cbd5e1;
                border-radius: 40px;
                padding: 0.6rem 1.2rem;
                font-size: 0.9rem;
                transition: 0.2s;
            }

            .message-input:focus {
                outline: none;
                border-color: #0ea5e9;
                box-shadow: 0 0 0 4px rgba(14, 165, 233, 0.2);
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
                cursor: pointer;
            }

            .attach-button:hover {
                background: #e0f2fe;
                transform: scale(1.05);
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            }

            .send-button {
                background: linear-gradient(115deg, #0ea5e9, #0284c7);
                color: white;
                border: none;
            }

            .send-button:hover:not(:disabled) {
                transform: scale(1.05) rotate(2deg);
                filter: brightness(1.02);
                box-shadow: 0 6px 14px rgba(14, 165, 233, 0.4);
            }

            .file-preview-card {
                margin-top: 12px;
                background: rgba(255, 255, 255, 0.95);
                border-radius: 1.2rem;
                padding: 0.6rem;
                border: 1px solid #e2e8f0;
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
            }

            .preview-close:hover {
                color: #ef4444;
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
                transition: all 0.2s cubic-bezier(0.2, 0.9, 0.4, 1.1);
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
                transform: scale(1.15);
                background: linear-gradient(135deg, #0284c7, #0369a1);
            }

            .edit-textarea {
                width: 100%;
                border-radius: 1rem;
                padding: 0.5rem;
                border: 1px solid #cbd5e1;
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
                font-weight: 500;
                padding: 0.4rem 1rem;
                border-radius: 2rem;
            }

            .save-edit-btn {
                background: #0ea5e9;
                color: white;
                border: none;
            }

            .save-edit-btn:hover {
                background: #0284c7 !important;
                transform: scale(1.02);
            }

            .cancel-edit-btn {
                background: #e2e8f0;
                border: none;
            }

            .cancel-edit-btn:hover {
                background: #cbd5e1 !important;
                transform: scale(1.02);
            }

            .edit-emotion-select {
                border-radius: 2rem;
                padding: 0.3rem 0.8rem;
                font-size: 0.8rem;
            }

            @media (max-width: 768px) {
                .two-columns {
                    flex-direction: column;
                }

                .left-column,
                .right-column {
                    flex: auto;
                    width: 100%;
                }

                .right-column {
                    position: static;
                }
            }
        </style>

        <script>
            // EDIT FUNCTIONS
            function toggleEdit(id) {
                const normalDiv = document.getElementById(`normal-content-${id}`);
                const editForm = document.getElementById(`edit-form-${id}`);
                if (!normalDiv || !editForm) return;
                if (normalDiv.style.display === 'none') {
                    normalDiv.style.display = 'block';
                    editForm.style.display = 'none';
                } else {
                    normalDiv.style.display = 'none';
                    editForm.style.display = 'block';
                }
            }

            function cancelEdit(id) {
                const normalDiv = document.getElementById(`normal-content-${id}`);
                const editForm = document.getElementById(`edit-form-${id}`);
                if (normalDiv) normalDiv.style.display = 'block';
                if (editForm) editForm.style.display = 'none';
                const textarea = document.getElementById(`edit-content-${id}`);
                if (textarea) {
                    const originalText = textarea.getAttribute('data-original') || textarea.value;
                    textarea.value = originalText;
                }
                const emotionSelect = document.getElementById(`edit-emotion-${id}`);
                if (emotionSelect) {
                    const originalEmotion = emotionSelect.getAttribute('data-original') || '';
                    emotionSelect.value = originalEmotion;
                }
            }

            function saveEdit(id, updateRoute) {
                const textarea = document.getElementById(`edit-content-${id}`);
                const emotionSelect = document.getElementById(`edit-emotion-${id}`);
                if (!textarea) return alert('Edit error');
                const newContent = textarea.value;
                const newEmotion = emotionSelect ? emotionSelect.value : '';
                fetch(updateRoute, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ||
                                '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            content: newContent,
                            emotion: newEmotion
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            const msgText = document.getElementById(`msg-text-${id}`);
                            if (msgText) msgText.innerText = data.new_content;
                            let emotionChip = document.getElementById(`emotion-chip-${id}`);
                            if (data.new_emotion) {
                                if (emotionChip) {
                                    emotionChip.innerHTML = `<i class='bi bi-emoji-smile'></i> ${data.new_emotion}`;
                                    emotionChip.style.display = 'inline-block';
                                } else {
                                    const newChip = document.createElement('div');
                                    newChip.id = `emotion-chip-${id}`;
                                    newChip.className = 'emotion-chip';
                                    newChip.innerHTML = `<i class='bi bi-emoji-smile'></i> ${data.new_emotion}`;
                                    const normalDiv = document.getElementById(`normal-content-${id}`);
                                    if (normalDiv && normalDiv.parentNode) normalDiv.parentNode.insertBefore(newChip,
                                        normalDiv.nextSibling);
                                }
                            } else if (emotionChip) emotionChip.style.display = 'none';
                            textarea.setAttribute('data-original', newContent);
                            if (emotionSelect) emotionSelect.setAttribute('data-original', newEmotion);
                            cancelEdit(id);
                            alert('Message updated!');
                        } else alert('Update failed');
                    })
                    .catch(() => alert('Error updating message'));
            }

            document.addEventListener('DOMContentLoaded', function() {
                @foreach ($activities as $activity)
                    @if (in_array($activity->type, ['text', 'emoji', 'emotion']))
                        (function() {
                            const ta = document.getElementById(`edit-content-{{ $activity->id }}`);
                            if (ta) ta.setAttribute('data-original', ta.value);
                            const sel = document.getElementById(`edit-emotion-{{ $activity->id }}`);
                            if (sel) sel.setAttribute('data-original', sel.value);
                        })();
                    @endif
                @endforeach

                // File preview
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
                    previewHTML += `<div class="preview-details"><span class="preview-filename">${escapeHtml(file.name)}</span><span class="preview-filesize">${fileSize}</span></div>
                <button type="button" class="preview-close" id="removePreviewBtn">×</button></div>`;
                    previewContainer.innerHTML = previewHTML;
                    previewContainer.style.display = 'block';
                    document.getElementById('removePreviewBtn')?.addEventListener('click', function() {
                        fileInput.value = '';
                        previewContainer.style.display = 'none';
                        previewContainer.innerHTML = '';
                    });
                }
                fileInput.addEventListener('change', updateFilePreview);

                function escapeHtml(str) {
                    return str.replace(/[&<>]/g, m => ({
                        '&': '&amp;',
                        '<': '&lt;',
                        '>': '&gt;'
                    } [m]));
                }

                const textInput = document.getElementById('messageInput');
                const sendButton = document.getElementById('sendButton');

                function updateSendButton() {
                    sendButton.disabled = !(textInput.value.trim().length > 0 || fileInput.files.length > 0);
                }
                textInput.addEventListener('input', updateSendButton);
                fileInput.addEventListener('change', updateSendButton);
                updateSendButton();

                const area = document.getElementById('messagesArea');
                if (area) area.scrollTop = area.scrollHeight;
                const scrollBtn = document.getElementById('scrollBottomBtn');
                if (scrollBtn && area) {
                    area.addEventListener('scroll', () => {
                        const isNearBottom = area.scrollHeight - area.scrollTop - area.clientHeight < 100;
                        scrollBtn.classList.toggle('show', !isNearBottom);
                    });
                    scrollBtn.addEventListener('click', () => area.scrollTo({
                        top: area.scrollHeight,
                        behavior: 'smooth'
                    }));
                }
            });

            // Filter buttons (right column AJAX)
            document.querySelectorAll('.filter-btn[data-type]').forEach(btn => {
                btn.addEventListener('click', async function() {
                    const type = this.getAttribute('data-type');
                    const container = document.getElementById('filteredItemsContainer');
                    const listDiv = document.getElementById('filteredItemsList');
                    const titleSpan = document.getElementById('filteredTitle');
                    container.style.display = 'block';
                    listDiv.innerHTML =
                        '<div class="filtered-empty"><i class="bi bi-hourglass-split"></i> Loading...</div>';
                    let displayTitle = type === 'chats' ? '📝 Text & Emotion Memories' : (type ===
                        'images' ? '🖼️ Uploaded Images' : '🎬 Uploaded Videos');
                    titleSpan.innerText = displayTitle;
                    try {
                        const response = await fetch(`/events/filter/${type}`);
                        const html = await response.text();
                        listDiv.innerHTML = html;
                    } catch (error) {
                        listDiv.innerHTML =
                            '<div class="filtered-empty">❌ Failed to load. Please try again.</div>';
                    }
                });
            });
            document.getElementById('closeFiltered')?.addEventListener('click', () => {
                document.getElementById('filteredItemsContainer').style.display = 'none';
            });
            // AI Chat (FIXED)
            const chatMessages = document.getElementById('chatMessages');
            const chatInput = document.getElementById('chatUserInput');
            const sendChatBtn = document.getElementById('sendChatBtn');

            function appendChatMessage(sender, text) {
                const msgDiv = document.createElement('div');
                msgDiv.className = sender === 'user' ? 'user-message' : 'bot-message';
                msgDiv.style.margin = '0.5rem 0';
                msgDiv.style.padding = '0.3rem 0.8rem';
                msgDiv.style.borderRadius = '1rem';
                msgDiv.style.backgroundColor = sender === 'user' ? '#0ea5e9' : '#e2e8f0';
                msgDiv.style.color = sender === 'user' ? 'white' : '#0f172a';
                msgDiv.style.maxWidth = '90%';
                msgDiv.innerHTML = `<strong>${sender === 'user' ? 'You' : 'AI'}:</strong> ${text}`;
                chatMessages.appendChild(msgDiv);
                chatMessages.scrollTop = chatMessages.scrollHeight;
            }

            async function sendChatMessage() {
                const message = chatInput.value.trim();
                if (!message) return;

                appendChatMessage('user', message);
                chatInput.value = '';

                const thinkingDiv = document.createElement('div');
                thinkingDiv.innerHTML = '<em>AI is thinking...</em>';
                thinkingDiv.style.margin = '0.5rem 0';
                thinkingDiv.style.padding = '0.3rem 0.8rem';
                thinkingDiv.style.backgroundColor = '#e2e8f0';
                thinkingDiv.style.borderRadius = '1rem';
                chatMessages.appendChild(thinkingDiv);
                chatMessages.scrollTop = chatMessages.scrollHeight;

                try {
                    const response = await fetch('/chat', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ||
                                '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            message: message
                        })
                    });

                    const rawText = await response.text();
                    console.log('Raw response:', rawText);

                    let data;
                    try {
                        data = JSON.parse(rawText);
                    } catch (parseError) {
                        console.error('Failed to parse JSON:', rawText);
                        thinkingDiv.remove();
                        appendChatMessage('bot', 'Server returned invalid response. Check console.');
                        return;
                    }

                    thinkingDiv.remove();
                    if (data.response) {
                        appendChatMessage('bot', data.response);
                    } else {
                        appendChatMessage('bot', 'Sorry, no response from server.');
                    }
                } catch (error) {
                    thinkingDiv.remove();
                    appendChatMessage('bot', 'Network error. Please check the console.');
                    console.error('Fetch error:', error);
                }
            }

            sendChatBtn.addEventListener('click', sendChatMessage);
            chatInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') sendChatMessage();
            });
        </script>
    @endsection
