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
        order: 1;
        flex: 1;
        min-width: 0;
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
        box-shadow: 0 6px 14px rgba(99,102,241,0.35);
    }
    .chat-header-info h6 {
        margin: 0; font-weight: 700; font-size: 0.92rem;
        color: var(--text-primary);
    }
    .chat-header-info small {
        color: var(--text-muted); font-size: 0.75rem;
        display: flex; align-items: center; gap: 0.35rem;
    }
    .chat-header-info small .status-dot {
        width: 7px; height: 7px;
        border-radius: 50%;
        background: #10b981;
        box-shadow: 0 0 0 3px rgba(16,185,129,0.18);
        display: inline-block;
    }
    .chat-header-actions {
        display: flex; gap: 0.5rem; align-items: center;
    }
    .chat-header-actions select {
        font-size: 0.8rem;
        font-weight: 500;
        padding: 0.4rem 1.8rem 0.4rem 0.7rem;
        border-radius: 0.7rem;
        border: 1.5px solid var(--input-border);
        background: var(--input-bg);
        color: var(--text-primary);
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6' viewBox='0 0 10 6'%3E%3Cpath d='M1 1l4 4 4-4' fill='none' stroke='%239ca3af' stroke-width='1.8' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 0.6rem center;
        cursor: pointer;
        transition: border-color 0.2s, box-shadow 0.2s;
        font-family: 'Inter', sans-serif;
    }
    .chat-header-actions select:hover { border-color: var(--card-accent); }
    .chat-header-actions select:focus {
        outline: none;
        border-color: var(--card-accent);
        box-shadow: 0 0 0 3px rgba(99,102,241,0.15);
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
        box-shadow: 0 2px 12px rgba(0,0,0,0.04);
    }
    .chat-msg.user .chat-msg-bubble {
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        color: white;
        border-top-right-radius: 0.25rem;
        box-shadow: 0 6px 16px -6px rgba(99,102,241,0.45);
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
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 0.6rem;
        padding: 0.5rem 0;
        flex-shrink: 0;
        max-width: 640px;
        margin: 0 auto;
        width: 100%;
    }
    .chat-suggestions.hidden { display: none; }
    .suggestion-card {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        text-align: left;
        padding: 0.75rem 0.85rem;
        border-radius: 1rem;
        border: 1.5px solid var(--glass-border);
        background: var(--glass-bg);
        backdrop-filter: blur(12px);
        cursor: pointer;
        transition: all 0.2s;
        font-family: 'Inter', sans-serif;
    }
    .suggestion-card:hover {
        border-color: var(--card-accent);
        transform: translateY(-2px);
        box-shadow: 0 10px 25px -8px rgba(99,102,241,0.25);
        background: var(--glass-bg-hover);
    }
    .suggestion-icon {
        width: 38px; height: 38px;
        border-radius: 11px;
        flex-shrink: 0;
        display: flex; align-items: center; justify-content: center;
        font-size: 1rem;
        background: var(--badge-bg);
        background: color-mix(in srgb, var(--c) 14%, transparent);
        color: var(--c);
    }
    .suggestion-title {
        display: block;
        font-size: 0.82rem;
        font-weight: 600;
        color: var(--text-primary);
    }
    .suggestion-desc {
        display: block;
        font-size: 0.7rem;
        color: var(--text-muted);
        margin-top: 1px;
    }


    .chat-input-area {
        padding: 0.75rem 0;
        flex-shrink: 0;
    }
    .chat-input-wrap {
        display: flex;
        align-items: flex-end;
        gap: 0.5rem;
        padding: 0.5rem;
        transition: border-color 0.2s, box-shadow 0.2s;
    }
    .chat-input-wrap:focus-within {
        border-color: rgba(99,102,241,0.5);
        box-shadow: 0 0 0 3px rgba(99,102,241,0.12), 0 10px 30px -10px rgba(99,102,241,0.25);
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
        width: 68px; height: 68px;
        border-radius: 22px;
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        display: flex; align-items: center; justify-content: center;
        color: white; font-size: 2rem;
        margin: 0 auto 1rem;
        box-shadow: 0 12px 30px -8px rgba(99,102,241,0.5);
        position: relative;
    }
    .welcome-avatar::after {
        content: '';
        position: absolute;
        inset: -6px;
        border-radius: 26px;
        background: linear-gradient(135deg, rgba(99,102,241,0.25), rgba(139,92,246,0.25));
        z-index: -1;
        animation: welcomePulse 3s ease-in-out infinite;
    }
    @keyframes welcomePulse {
        0%, 100% { transform: scale(1); opacity: 0.7; }
        50% { transform: scale(1.08); opacity: 1; }
    }
    .welcome-section h5 {
        font-weight: 800;
        color: var(--text-primary);
        margin-bottom: 0.4rem;
        font-size: 1.15rem;
    }
    .welcome-section p {
        color: var(--text-secondary);
        font-size: 0.88rem;
        margin: 0;
    }

    @media (max-width: 768px) {
        .chat-page { height: calc(100vh - 130px); }
        .chat-msg { max-width: 92%; }
        .chat-header-actions select { max-width: 110px; font-size: 0.75rem; }
        .chat-suggestions { grid-template-columns: 1fr; }
    }
    @media (max-width: 480px) {
        .chat-page { height: calc(100vh - 110px); padding: 0; }
        .chat-header { border-radius: 0; margin: 0; }
        .chat-input-area { padding: 0.5rem; }
    }

    /* ---------- ChatGPT-style conversation sidebar (right) ---------- */
    .chat-layout {
        display: flex;
        gap: 1rem;
        max-width: 1280px;
        margin: 0 auto;
        height: calc(100vh - 160px);
        min-height: 400px;
        position: relative;
    }
    .chat-sidebar {
        order: 2;
        width: 300px;
        min-width: 300px;
        background: var(--glass-bg);
        backdrop-filter: blur(24px) saturate(1.8);
        border: 1px solid var(--glass-border);
        border-radius: 1.5rem;
        box-shadow: var(--card-shadow);
        display: flex;
        flex-direction: column;
        overflow: hidden;
        position: relative;
        z-index: 30;
    }
    .chat-sidebar-backdrop {
        display: none;
    }
    .chat-sidebar-toggle {
        width: 38px; height: 38px;
        border-radius: 50%;
        background: var(--input-bg);
        border: 1.5px solid var(--input-border);
        color: var(--card-accent);
        display: none;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s;
        font-size: 1.1rem;
        flex-shrink: 0;
    }
    .chat-sidebar-toggle:hover {
        border-color: var(--card-accent);
        background: var(--badge-bg);
        transform: scale(1.05);
    }
    .cs-header {
        padding: 1rem;
        border-bottom: 1px solid var(--divider-color);
        background: linear-gradient(135deg, rgba(99,102,241,0.08), rgba(139,92,246,0.08));
    }
    .cs-title-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 0.75rem;
    }
    .cs-title {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.95rem;
        font-weight: 700;
        color: var(--text-primary);
    }
    .cs-title i {
        width: 28px; height: 28px;
        display: flex; align-items: center; justify-content: center;
        border-radius: 8px;
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        color: white;
        font-size: 0.85rem;
        box-shadow: 0 4px 10px rgba(99,102,241,0.35);
    }
    .cs-new-chat {
        width: 100%;
        padding: 0.6rem 0.8rem;
        border-radius: 0.9rem;
        border: 1.5px dashed var(--input-border);
        background: var(--input-bg);
        color: var(--text-primary);
        font-family: 'Inter', sans-serif;
        font-size: 0.85rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        cursor: pointer;
        transition: all 0.2s;
    }
    .cs-new-chat:hover {
        border-color: var(--card-accent);
        color: var(--card-accent);
        background: var(--badge-bg);
        transform: translateY(-1px);
    }
    .cs-search-wrap {
        position: relative;
        margin-top: 0.6rem;
    }
    .cs-search-wrap i {
        position: absolute;
        left: 0.75rem;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-muted);
        font-size: 0.8rem;
        pointer-events: none;
    }
    .cs-search {
        width: 100%;
        padding: 0.5rem 0.75rem 0.5rem 1.9rem;
        border-radius: 0.8rem;
        border: 1px solid var(--input-border);
        background: var(--input-bg);
        color: var(--text-primary);
        font-family: 'Inter', sans-serif;
        font-size: 0.78rem;
        outline: none;
        transition: all 0.2s;
    }
    .cs-search:focus {
        border-color: var(--card-accent);
        box-shadow: 0 0 0 3px rgba(99,102,241,0.15);
    }
    .cs-list {
        flex: 1;
        overflow-y: auto;
        padding: 0.6rem;
        display: flex;
        flex-direction: column;
        gap: 0.35rem;
    }
    .cs-list::-webkit-scrollbar { width: 5px; }
    .cs-list::-webkit-scrollbar-thumb { background: var(--divider-color); border-radius: 4px; }
    .cs-group-label {
        font-size: 0.66rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: var(--text-muted);
        padding: 0.75rem 0.5rem 0.3rem;
    }
    .cs-item {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        padding: 0.6rem;
        border-radius: 1rem;
        cursor: pointer;
        transition: all 0.18s ease;
        border: 1px solid transparent;
        background: none;
        text-align: left;
        width: 100%;
        font-family: 'Inter', sans-serif;
        color: var(--text-primary);
        position: relative;
    }
    .cs-item:hover {
        background: var(--table-row-hover);
        border-color: var(--divider-color);
    }
    .cs-item.active {
        background: linear-gradient(135deg, rgba(99,102,241,0.12), rgba(139,92,246,0.12));
        border-color: rgba(99,102,241,0.35);
    }
    .cs-item.active::before {
        content: '';
        position: absolute;
        left: -0.2rem;
        top: 50%;
        transform: translateY(-50%);
        width: 4px;
        height: 60%;
        border-radius: 4px;
        background: linear-gradient(180deg, #6366f1, #8b5cf6);
    }
    .cs-item-icon {
        width: 34px; height: 34px;
        flex-shrink: 0;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--badge-bg);
        color: var(--card-accent);
        font-size: 0.85rem;
        transition: all 0.2s;
    }
    .cs-item:hover .cs-item-icon,
    .cs-item.active .cs-item-icon {
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        color: white;
        box-shadow: 0 4px 10px rgba(99,102,241,0.35);
    }
    .cs-item-body { flex: 1; min-width: 0; }
    .cs-item-title {
        display: block;
        font-size: 0.8rem;
        font-weight: 500;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .cs-item.active .cs-item-title { color: var(--card-accent); font-weight: 600; }
    .cs-item-preview {
        font-size: 0.68rem;
        color: var(--text-muted);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        margin-top: 1px;
        display: block;
    }
    .cs-item-meta {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 0.3rem;
        flex-shrink: 0;
    }
    .cs-item-time {
        font-size: 0.6rem;
        color: var(--text-muted);
        background: var(--input-bg);
        border-radius: 6px;
        padding: 0.1rem 0.4rem;
        border: 1px solid var(--divider-color);
    }
    .cs-count-badge {
        min-width: 20px;
        height: 20px;
        padding: 0 0.4rem;
        border-radius: 10px;
        background: var(--badge-bg);
        color: var(--card-accent);
        font-size: 0.62rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .cs-item-menu { position: relative; flex-shrink: 0; }
    .cs-menu-btn {
        width: 28px; height: 28px;
        border-radius: 8px;
        border: none;
        background: transparent;
        color: var(--text-muted);
        display: flex; align-items: center; justify-content: center;
        cursor: pointer;
        transition: all 0.15s;
        opacity: 0;
        font-size: 0.8rem;
    }
    .cs-item:hover .cs-menu-btn,
    .cs-item.active .cs-menu-btn { opacity: 1; }
    .cs-menu-btn:hover { background: var(--badge-bg); color: var(--card-accent); }
    .cs-menu-dropdown {
        position: absolute;
        right: 0;
        top: calc(100% + 6px);
        min-width: 160px;
        background: var(--dropdown-bg);
        backdrop-filter: blur(14px);
        border: 1px solid var(--divider-color);
        border-radius: 0.85rem;
        box-shadow: 0 12px 30px -8px rgba(0,0,0,0.2);
        padding: 0.35rem;
        z-index: 60;
        opacity: 0;
        transform: translateY(-6px) scale(0.97);
        pointer-events: none;
        transition: all 0.15s ease;
        transform-origin: top right;
    }
    .cs-menu-dropdown.open {
        opacity: 1;
        transform: translateY(0) scale(1);
        pointer-events: auto;
    }
    .cs-menu-item {
        display: flex;
        align-items: center;
        gap: 0.55rem;
        width: 100%;
        padding: 0.5rem 0.65rem;
        border: none;
        background: transparent;
        border-radius: 0.55rem;
        font-size: 0.78rem;
        font-weight: 500;
        color: var(--text-primary);
        cursor: pointer;
        font-family: 'Inter', sans-serif;
        transition: background 0.12s;
        text-align: left;
    }
    .cs-menu-item i { font-size: 0.8rem; }
    .cs-menu-item:hover { background: var(--table-row-hover); }
    .cs-menu-item.danger { color: #ef4444; }
    .cs-menu-item.danger:hover { background: rgba(239,68,68,0.1); }
    .cs-empty {
        text-align: center;
        padding: 2.5rem 1rem;
        color: var(--text-muted);
    }
    .cs-empty-icon {
        width: 56px; height: 56px;
        margin: 0 auto 0.75rem;
        border-radius: 18px;
        background: linear-gradient(135deg, rgba(99,102,241,0.12), rgba(139,92,246,0.12));
        border: 1px solid rgba(99,102,241,0.2);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        color: var(--card-accent);
    }
    .cs-empty span { font-size: 0.85rem; display: block; font-weight: 600; color: var(--text-primary); }
    .cs-empty small { font-size: 0.72rem; color: var(--text-muted); display: block; margin-top: 0.2rem; }
    .cs-footer {
        padding: 0.6rem 0.75rem;
        border-top: 1px solid var(--divider-color);
        font-size: 0.68rem;
        color: var(--text-muted);
        text-align: center;
        background: rgba(0,0,0,0.02);
    }
    .cs-footer .cs-count { font-weight: 700; color: var(--card-accent); }
    @media (max-width: 992px) {
        .chat-sidebar-toggle { display: flex; }
        .chat-sidebar {
            position: absolute;
            right: 0;
            top: 0;
            bottom: 0;
            z-index: 40;
            transform: translateX(110%);
            transition: transform 0.3s ease;
            box-shadow: var(--card-shadow);
        }
        .chat-sidebar.open { transform: translateX(0); }
        .chat-sidebar-backdrop {
            display: block;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.35);
            z-index: 35;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s;
        }
        .chat-sidebar-backdrop.show {
            opacity: 1;
            pointer-events: auto;
        }
    }
    @media (max-width: 480px) {
        .chat-sidebar { width: 280px; min-width: 280px; }
    }

    /* ---------- Chat modal (rename / delete) ---------- */
    .chat-modal-backdrop {
        position: fixed; inset: 0;
        background: rgba(0,0,0,0.45);
        backdrop-filter: blur(4px);
        z-index: 1000;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1rem;
        animation: modalFade 0.2s ease;
    }
    .chat-modal-backdrop[hidden] { display: none; }
    .chat-modal {
        width: 100%; max-width: 420px;
        background: var(--glass-bg);
        backdrop-filter: blur(20px) saturate(1.8);
        border: 1px solid var(--glass-border);
        border-radius: 1.25rem;
        box-shadow: 0 30px 60px -12px rgba(0,0,0,0.35);
        padding: 1.5rem;
        animation: modalPop 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .chat-modal-icon {
        width: 48px; height: 48px;
        border-radius: 14px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.2rem;
        background: var(--badge-bg);
        color: var(--card-accent);
        margin-bottom: 0.75rem;
    }
    .chat-modal-icon.danger { background: rgba(239,68,68,0.12); color: #ef4444; }
    .chat-modal h6 { font-weight: 700; color: var(--text-primary); margin-bottom: 0.25rem; }
    .chat-modal p { font-size: 0.82rem; color: var(--text-secondary); margin: 0 0 0.85rem; }
    .chat-modal input {
        width: 100%;
        padding: 0.6rem 0.85rem;
        border-radius: 0.75rem;
        border: 1.5px solid var(--input-border);
        background: var(--input-bg);
        color: var(--text-primary);
        font-size: 0.88rem;
        font-family: 'Inter', sans-serif;
        outline: none;
        transition: border-color 0.2s, box-shadow 0.2s;
        margin-bottom: 1rem;
    }
    .chat-modal input:focus {
        border-color: var(--card-accent);
        box-shadow: 0 0 0 3px rgba(99,102,241,0.15);
    }
    .chat-modal-actions {
        display: flex;
        justify-content: flex-end;
        gap: 0.5rem;
    }
    .danger-btn {
        padding: 0.5rem 1rem;
        border-radius: 0.75rem;
        border: none;
        background: linear-gradient(135deg, #ef4444, #dc2626);
        color: white;
        font-size: 0.82rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
    }
    .danger-btn:hover { transform: translateY(-1px); box-shadow: 0 6px 14px rgba(239,68,68,0.35); }
    @keyframes modalFade { from { opacity: 0; } to { opacity: 1; } }
    @keyframes modalPop {
        from { opacity: 0; transform: scale(0.95) translateY(10px); }
        to { opacity: 1; transform: scale(1) translateY(0); }
    }
</style>

<div class="chat-layout">
    <aside class="chat-sidebar" id="chatSidebar" aria-label="Chat history">
        <div class="cs-header">
            <div class="cs-title-row">
                <span class="cs-title"><i class="bi bi-clock-history"></i> Chat History</span>
                <span class="cs-count-badge" id="csCount">0</span>
            </div>
            <button type="button" class="cs-new-chat" id="newChatBtn">
                <i class="bi bi-plus-lg"></i> New chat
            </button>
            <div class="cs-search-wrap">
                <i class="bi bi-search"></i>
                <input type="text" class="cs-search" id="csSearch" placeholder="Search conversations..." autocomplete="off">
            </div>
        </div>
        <div class="cs-list" id="csList"></div>
        <div class="cs-footer">
            <span class="cs-count" id="csTotal">0</span> saved conversations
        </div>
    </aside>
    <div class="chat-sidebar-backdrop" id="chatSidebarBackdrop"></div>

    <div class="chat-page">
        <div class="chat-header glass-card">
        <div class="chat-header-left">
            <button class="chat-sidebar-toggle" id="chatSidebarToggle" title="Chat history">
                <i class="bi bi-clock-history"></i>
            </button>
            <div class="chat-header-avatar"><i class="bi bi-robot"></i></div>
            <div class="chat-header-info">
                <h6>Study Assistant for Students</h6>
                <small><span class="status-dot"></span> Online</small>
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
            <p>I'm your AI study assistant. Ask me anything about your studies, or try one of these:</p>
        </div>
    </div>

    <div class="chat-suggestions" id="chatSuggestions">
        <button class="suggestion-card" data-msg="Explain photosynthesis like I'm 5">
            <span class="suggestion-icon" style="--c:#6366f1"><i class="bi bi-flower1"></i></span>
            <span>
                <span class="suggestion-title">Explain concepts</span>
                <span class="suggestion-desc">Break down any topic simply</span>
            </span>
        </button>
        <button class="suggestion-card" data-msg="What are the key topics in my notes?">
            <span class="suggestion-icon" style="--c:#10b981"><i class="bi bi-journal-text"></i></span>
            <span>
                <span class="suggestion-title">Review my notes</span>
                <span class="suggestion-desc">Summarize uploaded documents</span>
            </span>
        </button>
        <button class="suggestion-card" data-msg="Generate 5 practice MCQs on biology">
            <span class="suggestion-icon" style="--c:#f59e0b"><i class="bi bi-ui-radios"></i></span>
            <span>
                <span class="suggestion-title">Practice questions</span>
                <span class="suggestion-desc">Create MCQs to test yourself</span>
            </span>
        </button>
        <button class="suggestion-card" data-msg="Create a study plan for my exams">
            <span class="suggestion-icon" style="--c:#ec4899"><i class="bi bi-calendar2-check"></i></span>
            <span>
                <span class="suggestion-title">Study plan</span>
                <span class="suggestion-desc">Get a schedule for your exams</span>
            </span>
        </button>
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
</div>

<div class="chat-modal-backdrop" id="chatModalBackdrop" hidden>
    <div class="chat-modal" role="dialog" aria-modal="true" aria-labelledby="chatModalTitle">
        <div class="chat-modal-icon" id="chatModalIcon"><i class="bi bi-pencil"></i></div>
        <h6 id="chatModalTitle">Rename conversation</h6>
        <p id="chatModalText"></p>
        <input type="text" id="chatModalInput" maxlength="255" placeholder="Conversation title" autocomplete="off">
        <div class="chat-modal-actions">
            <button type="button" class="btn-soft" id="chatModalCancel">Cancel</button>
            <button type="button" class="dark-btn" id="chatModalConfirm">Save</button>
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

    const sidebar = document.getElementById('chatSidebar');
    const sidebarToggle = document.getElementById('chatSidebarToggle');
    const sidebarBackdrop = document.getElementById('chatSidebarBackdrop');
    const csList = document.getElementById('csList');
    const csCount = document.getElementById('csCount');
    const csTotal = document.getElementById('csTotal');
    const csSearch = document.getElementById('csSearch');
    const newChatBtn = document.getElementById('newChatBtn');
    let activeConversationId = null;
    let conversationsCache = [];

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
            if (data.conversation && data.conversation.id) {
                activeConversationId = data.conversation.id;
                loadConversations();
            }
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
            if (data.conversation && data.conversation.id) {
                activeConversationId = data.conversation.id;
            }
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

    /* ---------- Conversation sidebar ---------- */

    function resetChatView() {
        messagesEl.innerHTML = '';
        if (welcomeSection) {
            welcomeSection.style.display = '';
            messagesEl.appendChild(welcomeSection);
        }
        suggestions.classList.remove('hidden');
    }

    function closeSidebar() {
        if (sidebar) sidebar.classList.remove('open');
        if (sidebarBackdrop) sidebarBackdrop.classList.remove('show');
    }

    function toggleSidebar() {
        if (!sidebar) return;
        const isOpen = sidebar.classList.toggle('open');
        if (sidebarBackdrop) sidebarBackdrop.classList.toggle('show', isOpen);
    }

    function sidebarGroupLabel(iso) {
        const now = new Date();
        const startOfToday = new Date(now.getFullYear(), now.getMonth(), now.getDate()).getTime();
        const d = iso ? new Date(iso) : new Date();
        if (isNaN(d.getTime())) return 'Older';
        const startOfDay = new Date(d.getFullYear(), d.getMonth(), d.getDate()).getTime();
        const diffDays = Math.floor((startOfToday - startOfDay) / 86400000);
        if (diffDays <= 0) return 'Today';
        if (diffDays === 1) return 'Yesterday';
        if (diffDays < 7) return 'This week';
        return 'Older';
    }

    function renderSidebar(conversations) {
        conversationsCache = conversations || [];
        applySidebarFilter();
    }

    function applySidebarFilter() {
        if (!csList) return;

        const q = (csSearch ? csSearch.value : '').trim().toLowerCase();
        const filtered = q
            ? conversationsCache.filter(c =>
                (c.title || '').toLowerCase().includes(q) ||
                (c.last_message || '').toLowerCase().includes(q))
            : conversationsCache;

        if (csCount) csCount.textContent = filtered.length;
        if (csTotal) csTotal.textContent = conversationsCache.length;

        if (!conversationsCache.length) {
            csList.innerHTML = `
                <div class="cs-empty">
                    <div class="cs-empty-icon"><i class="bi bi-chat-square-dots"></i></div>
                    <span>No conversations yet</span>
                    <small>Your chats are saved here automatically.</small>
                </div>`;
            return;
        }

        if (!filtered.length) {
            csList.innerHTML = `
                <div class="cs-empty">
                    <div class="cs-empty-icon"><i class="bi bi-search"></i></div>
                    <span>No matches found</span>
                    <small>Try a different search term.</small>
                </div>`;
            return;
        }

        const groups = [];
        filtered.forEach(c => {
            const label = sidebarGroupLabel(c.updated_iso);
            let bucket = groups.find(g => g.label === label);
            if (!bucket) {
                bucket = { label, items: [] };
                groups.push(bucket);
            }
            bucket.items.push(c);
        });

        let html = '';
        groups.forEach(g => {
            html += `<div class="cs-group-label">${g.label}</div>`;
            g.items.forEach(c => {
                const count = c.message_count ? `<span class="cs-count-badge">${c.message_count}</span>` : '';
                const time = escapeHtml(c.updated_at || 'just now');
                const preview = c.last_message ? escapeHtml(c.last_message) : 'No messages yet';
                html += `
                <div class="cs-item ${c.id === activeConversationId ? 'active' : ''}" data-id="${c.id}">
                    <span class="cs-item-icon"><i class="bi bi-chat-left-text"></i></span>
                    <span class="cs-item-body">
                        <span class="cs-item-title">${escapeHtml(c.title)}</span>
                        <span class="cs-item-preview">${preview}</span>
                    </span>
                    <span class="cs-item-meta">
                        <span class="cs-item-time">${time}</span>
                        ${count}
                    </span>
                    <span class="cs-item-menu">
                        <button type="button" class="cs-menu-btn" data-menu="${c.id}" title="More options"><i class="bi bi-three-dots-vertical"></i></button>
                        <div class="cs-menu-dropdown" id="menu-${c.id}">
                            <button type="button" class="cs-menu-item rename" data-rename="${c.id}"><i class="bi bi-pencil"></i> Rename</button>
                            <button type="button" class="cs-menu-item danger" data-del="${c.id}"><i class="bi bi-trash3"></i> Delete</button>
                        </div>
                    </span>
                </div>`;
            });
        });
        csList.innerHTML = html;
    }

    async function loadConversations() {
        try {
            const res = await fetch('{{ route('chat.conversations') }}', {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }
            });
            const data = await res.json();
            renderSidebar(data.conversations || []);
        } catch(e) {}
    }

    async function openConversation(id) {
        try {
            closeSidebarMenus();
            const res = await fetch('{{ route('chat.conversations.show', ':id') }}'.replace(':id', id), {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }
            });
            const data = await res.json();
            if (!data.messages) return;

            activeConversationId = id;
            messagesEl.innerHTML = '';
            if (welcomeSection) welcomeSection.style.display = 'none';

            data.messages.forEach(msg => {
                addMessage(msg.role === 'assistant' ? 'bot' : msg.role, msg.content, true);
            });

            if (data.messages.length > 0) {
                suggestions.classList.add('hidden');
            } else {
                resetChatView();
            }
            scrollToBottom();
            loadConversations();
            closeSidebar();
        } catch(e) {}
    }

    /* ---------- Modal (rename / delete) ---------- */

    const modalBackdrop = document.getElementById('chatModalBackdrop');
    const modalTitleEl = document.getElementById('chatModalTitle');
    const modalTextEl = document.getElementById('chatModalText');
    const modalInput = document.getElementById('chatModalInput');
    const modalConfirm = document.getElementById('chatModalConfirm');
    const modalCancel = document.getElementById('chatModalCancel');
    const modalIconEl = document.getElementById('chatModalIcon');
    let modalAction = null;

    function openModal({ title, text = '', icon = 'bi-pencil', input = false, inputValue = '', confirmLabel = 'Save', danger = false, onConfirm }) {
        modalTitleEl.textContent = title;
        modalTextEl.textContent = text;
        modalTextEl.style.display = text ? 'block' : 'none';
        modalIconEl.innerHTML = `<i class="bi ${icon}"></i>`;
        modalIconEl.classList.toggle('danger', danger);
        modalInput.style.display = input ? 'block' : 'none';
        modalInput.value = inputValue;
        modalConfirm.innerHTML = confirmLabel;
        modalConfirm.classList.toggle('dark-btn', !danger);
        modalConfirm.classList.toggle('danger-btn', danger);
        modalAction = onConfirm;
        modalBackdrop.hidden = false;
        if (input) setTimeout(() => modalInput.focus(), 60);
    }

    function closeModal() {
        modalBackdrop.hidden = true;
        modalAction = null;
    }

    modalConfirm.addEventListener('click', () => { if (modalAction) modalAction(); });
    modalCancel.addEventListener('click', closeModal);
    modalBackdrop.addEventListener('click', (e) => { if (e.target === modalBackdrop) closeModal(); });
    modalInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') { e.preventDefault(); if (modalAction) modalAction(); }
        if (e.key === 'Escape') closeModal();
    });

    function renameConversation(id) {
        const item = csList?.querySelector(`.cs-item[data-id="${id}"]`);
        const current = item ? item.querySelector('.cs-item-title').textContent : '';
        openModal({
            title: 'Rename conversation',
            text: 'Give this conversation a clear, memorable title.',
            icon: 'bi-pencil',
            input: true,
            inputValue: current,
            confirmLabel: 'Save',
            onConfirm: async () => {
                const title = modalInput.value.trim();
                if (!title) return;
                closeModal();
                try {
                    const res = await fetch('{{ route('chat.conversations.rename', ':id') }}'.replace(':id', id), {
                        method: 'PATCH',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
                        body: JSON.stringify({ title })
                    });
                    const data = await res.json();
                    if (data.conversation) {
                        renderSidebar((await (await fetch('{{ route('chat.conversations') }}', { headers: { 'X-CSRF-TOKEN': CSRF } })).json()).conversations || []);
                        showToast('Conversation renamed.', 'success');
                    }
                } catch(e) {}
            }
        });
    }

    function deleteConversation(id) {
        openModal({
            title: 'Delete conversation?',
            text: 'This will permanently delete the conversation and all its messages. This cannot be undone.',
            icon: 'bi-trash3',
            confirmLabel: 'Delete',
            danger: true,
            onConfirm: async () => {
                closeModal();
                try {
                    await fetch('{{ route('chat.conversations.destroy', ':id') }}'.replace(':id', id), {
                        method: 'DELETE',
                        headers: { 'X-CSRF-TOKEN': CSRF }
                    });
                    if (id === activeConversationId) {
                        activeConversationId = null;
                        resetChatView();
                    }
                    loadConversations();
                    showToast('Conversation deleted.', 'success');
                } catch(e) {}
            }
        });
    }

    async function startNewChat() {
        try {
            await fetch('/chat/clear', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF }
            });
        } catch (_) {}
        activeConversationId = null;
        resetChatView();
        loadConversations();
        closeSidebar();
        chatInput.focus();
    }

    if (sidebarToggle) sidebarToggle.addEventListener('click', toggleSidebar);
    if (sidebarBackdrop) sidebarBackdrop.addEventListener('click', closeSidebar);
    if (newChatBtn) newChatBtn.addEventListener('click', startNewChat);
    if (csSearch) csSearch.addEventListener('input', applySidebarFilter);

    function closeSidebarMenus() {
        document.querySelectorAll('.cs-menu-dropdown.open').forEach(d => d.classList.remove('open'));
    }

    if (csList) {
        csList.addEventListener('click', (e) => {
            const menuBtn = e.target.closest('[data-menu]');
            const del = e.target.closest('[data-del]');
            const rename = e.target.closest('[data-rename]');
            if (menuBtn) {
                e.stopPropagation();
                const dd = document.getElementById('menu-' + menuBtn.dataset.menu);
                const wasOpen = dd && dd.classList.contains('open');
                closeSidebarMenus();
                if (dd && !wasOpen) dd.classList.add('open');
                return;
            }
            if (del) { e.stopPropagation(); closeSidebarMenus(); deleteConversation(del.dataset.del); return; }
            if (rename) { e.stopPropagation(); closeSidebarMenus(); renameConversation(rename.dataset.rename); return; }
            const item = e.target.closest('.cs-item');
            if (item && item.dataset.id) openConversation(item.dataset.id);
        });
    }

    document.addEventListener('click', (e) => {
        if (!e.target.closest('.cs-item-menu')) closeSidebarMenus();
    });

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

    document.getElementById('clearChatBtn').addEventListener('click', () => {
        openModal({
            title: 'Start a new chat?',
            text: 'The current conversation stays saved in the sidebar — this just clears the screen.',
            icon: 'bi-arrow-counterclockwise',
            confirmLabel: 'New chat',
            onConfirm: () => { closeModal(); startNewChat(); }
        });
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
        const chip = e.target.closest('.suggestion-card');
        if (chip) {
            chatInput.value = chip.dataset.msg;
            autoResize();
            sendMessage();
        }
    });

    loadHistory();
    loadConversations();
    chatInput.focus();
});
</script>
@endsection
