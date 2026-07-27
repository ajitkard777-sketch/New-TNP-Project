<?php
/**
 * TPMS - AI Career Assistant Floating Chatbot Widget
 * Included on all Student Dashboard & Portal pages
 */
if (!AuthMiddleware::isLoggedIn() || ($_SESSION['user_role'] ?? '') !== 'student') {
    return;
}
?>
<style>
/* Floating AI Chatbot Widget Styles */
.ai-chat-trigger {
    position: fixed;
    bottom: 25px;
    right: 25px;
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, #6366f1, #a855f7);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    cursor: pointer;
    box-shadow: 0 10px 25px -5px rgba(99, 102, 241, 0.5);
    z-index: 1050;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    border: 2px solid rgba(255, 255, 255, 0.3);
}

.ai-chat-trigger:hover {
    transform: scale(1.08) rotate(5deg);
    box-shadow: 0 15px 30px -5px rgba(168, 85, 247, 0.6);
}

.ai-chat-pulse {
    position: absolute;
    top: -2px;
    right: -2px;
    width: 14px;
    height: 14px;
    background: #10b981;
    border: 2px solid white;
    border-radius: 50%;
}

.ai-chat-window {
    position: fixed;
    bottom: 95px;
    right: 25px;
    width: 410px;
    max-width: calc(100vw - 40px);
    height: 580px;
    max-height: calc(100vh - 120px);
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    border: 1px solid rgba(226, 232, 240, 0.8);
    border-radius: 1.25rem;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    z-index: 1050;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    opacity: 0;
    transform: translateY(20px) scale(0.95);
    pointer-events: none;
}

.ai-chat-window.active {
    opacity: 1;
    transform: translateY(0) scale(1);
    pointer-events: all;
}

.dark-mode .ai-chat-window {
    background: rgba(15, 23, 42, 0.95);
    border-color: rgba(51, 65, 85, 0.8);
    color: #f8fafc;
}

.ai-chat-header {
    background: linear-gradient(135deg, #4338ca, #6366f1);
    color: white;
    padding: 1rem 1.25rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.ai-chat-body {
    flex: 1;
    padding: 1rem;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 0.85rem;
}

.chat-msg {
    max-width: 85%;
    padding: 0.75rem 1rem;
    border-radius: 1rem;
    font-size: 0.88rem;
    line-height: 1.5;
    position: relative;
    word-break: break-word;
}

.chat-msg.bot {
    align-self: flex-start;
    background: rgba(241, 245, 249, 0.9);
    color: #1e293b;
    border-bottom-left-radius: 0.2rem;
    border: 1px solid rgba(226, 232, 240, 0.8);
}

.dark-mode .chat-msg.bot {
    background: rgba(30, 41, 59, 0.9);
    color: #f1f5f9;
    border-color: rgba(51, 65, 85, 0.8);
}

.chat-msg.user {
    align-self: flex-end;
    background: linear-gradient(135deg, #6366f1, #a855f7);
    color: white;
    border-bottom-right-radius: 0.2rem;
}

.typing-indicator {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 0.5rem 1rem;
    background: rgba(241, 245, 249, 0.9);
    border-radius: 1rem;
    align-self: flex-start;
}

.typing-dot {
    width: 6px;
    height: 6px;
    background: #6366f1;
    border-radius: 50%;
    animation: typingBounce 1.4s infinite ease-in-out both;
}

.typing-dot:nth-child(1) { animation-delay: -0.32s; }
.typing-dot:nth-child(2) { animation-delay: -0.16s; }

@keyframes typingBounce {
    0%, 80%, 100% { transform: scale(0); }
    40% { transform: scale(1); }
}

.ai-chat-pills {
    padding: 0.5rem 1rem;
    display: flex;
    gap: 0.4rem;
    overflow-x: auto;
    white-space: nowrap;
    border-top: 1px solid rgba(226, 232, 240, 0.5);
    background: rgba(248, 250, 252, 0.5);
}

.dark-mode .ai-chat-pills {
    background: rgba(15, 23, 42, 0.5);
    border-color: rgba(51, 65, 85, 0.5);
}

.chat-pill-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    padding: 0.3rem 0.75rem;
    border-radius: 1rem;
    background: white;
    border: 1px solid rgba(226, 232, 240, 0.8);
    color: #475569;
    font-size: 0.75rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.dark-mode .chat-pill-btn {
    background: #1e293b;
    border-color: #334155;
    color: #cbd5e1;
}

.chat-pill-btn:hover {
    background: #6366f1;
    color: white;
    border-color: #6366f1;
}

.ai-chat-footer {
    padding: 0.75rem 1rem;
    border-top: 1px solid rgba(226, 232, 240, 0.8);
    background: white;
}

.dark-mode .ai-chat-footer {
    background: #0f172a;
    border-color: rgba(51, 65, 85, 0.8);
}
</style>

<!-- Floating Chat Trigger Button -->
<div class="ai-chat-trigger" onclick="toggleAiChat()" id="aiChatTrigger" title="Ask AI Career Assistant">
    <img src="<?= asset('images/bot-logo.svg') ?>" alt="AI Logo" style="width: 28px; height: 28px; object-fit: contain; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));" onerror="this.style.display='none'; this.nextElementSibling.style.display='inline-block';">
    <i class="fas fa-robot" style="display:none;"></i>
    <div class="ai-chat-pulse"></div>
</div>

<!-- Chatbot Window -->
<div class="ai-chat-window" id="aiChatWindow">
    <div class="ai-chat-header">
        <div class="d-flex align-items-center gap-2">
            <div class="bg-white text-primary rounded-circle d-flex align-items-center justify-content-center p-1" style="width: 38px; height: 38px;">
                <img src="<?= asset('images/bot-logo.svg') ?>" alt="AI Avatar" style="width: 28px; height: 28px; object-fit: contain;" onerror="this.style.display='none'; this.nextElementSibling.style.display='inline-block';">
                <i class="fas fa-sparkles text-primary" style="display:none; font-size: 1.1rem;"></i>
            </div>
            <div>
                <h6 class="fw-bold mb-0 text-white" style="font-size: 0.95rem;">AI Career Assistant</h6>
                <small class="text-white-50" style="font-size: 0.72rem;">TPMS Placement &amp; Job Guidance</small>
            </div>
        </div>
        <button type="button" class="btn-close btn-close-white btn-sm" onclick="toggleAiChat()"></button>
    </div>

    <!-- Message Stream -->
    <div class="ai-chat-body" id="aiChatBody">
        <div class="chat-msg bot animate-fade-in-up">
            👋 Hello! I am your <strong>AI Career Assistant</strong>.<br><br>
            I have analyzed your profile and can help you find jobs, verify eligibility, check missing skills, or practice interview questions!
        </div>
    </div>

    <!-- Quick Suggestion Pills -->
    <div class="ai-chat-pills" id="aiChatPills">
        <button class="chat-pill-btn" onclick="sendChatPrompt('Recommend jobs for me')">🎯 Recommend Jobs</button>
        <button class="chat-pill-btn" onclick="sendChatPrompt('Which jobs am I eligible for?')">🎓 Eligibility Check</button>
        <button class="chat-pill-btn" onclick="sendChatPrompt('What skills should I learn?')">💡 Skill Suggestions</button>
        <button class="chat-pill-btn" onclick="sendChatPrompt('Give me HR interview tips')">👔 HR Questions</button>
        <button class="chat-pill-btn" onclick="sendChatPrompt('Give me technical interview tips')">💻 Tech Tips</button>
        <button class="chat-pill-btn" onclick="sendChatPrompt('Review my resume')">📄 Resume Review</button>
        <button class="chat-pill-btn" onclick="sendChatPrompt('Which companies are hiring?')">🏢 Top Companies</button>
    </div>

    <!-- Chat Input Form -->
    <div class="ai-chat-footer">
        <form onsubmit="handleChatSubmit(event)" id="aiChatForm" class="d-flex gap-2">
            <input type="text" class="form-control form-control-sm rounded-pill px-3" id="aiChatInput" placeholder="Ask about jobs, skills, interviews..." autocomplete="off">
            <button type="submit" class="btn btn-primary btn-sm rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; flex-shrink: 0;">
                <i class="fas fa-paper-plane"></i>
            </button>
        </form>
    </div>
</div>

<script>
let chatLoaded = false;

function toggleAiChat() {
    const win = document.getElementById('aiChatWindow');
    win.classList.toggle('active');
    if (win.classList.contains('active')) {
        document.getElementById('aiChatInput').focus();
        if (!chatLoaded) {
            loadChatHistory();
            chatLoaded = true;
        }
    }
}

function sendChatPrompt(text) {
    document.getElementById('aiChatInput').value = text;
    handleChatSubmit(new Event('submit'));
}

function handleChatSubmit(e) {
    if (e) e.preventDefault();
    const input = document.getElementById('aiChatInput');
    const msg = input.value.trim();
    if (!msg) return;

    input.value = '';
    appendMessage(msg, 'user');
    showTypingIndicator();

    fetch('<?= url('/api/chat') ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'message=' + encodeURIComponent(msg) + '&csrf_token=<?= $_SESSION['csrf_token'] ?? '' ?>'
    })
    .then(r => r.json())
    .then(data => {
        hideTypingIndicator();
        if (data.success) {
            appendMessage(data.response, 'bot');
        } else {
            appendMessage('Sorry, I encountered an issue. Please try again.', 'bot');
        }
    })
    .catch(err => {
        hideTypingIndicator();
        appendMessage('Network error. Please check your connection.', 'bot');
    });
}

function appendMessage(text, sender) {
    const body = document.getElementById('aiChatBody');
    const div = document.createElement('div');
    div.className = 'chat-msg ' + sender + ' animate-fade-in-up';
    
    // Parse markdown-like bold/line breaks safely
    let formatted = text
        .replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;")
        .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
        .replace(/\*(.*?)\*/g, '<em>$1</em>')
        .replace(/\[(.*?)\]\((.*?)\)/g, '<a href="$2" style="color:#6366f1;font-weight:600;">$1</a>')
        .replace(/\n/g, '<br>');

    div.innerHTML = formatted;
    body.appendChild(div);
    body.scrollTop = body.scrollHeight;
}

function showTypingIndicator() {
    hideTypingIndicator();
    const body = document.getElementById('aiChatBody');
    const div = document.createElement('div');
    div.id = 'typingIndicator';
    div.className = 'typing-indicator';
    div.innerHTML = '<div class="typing-dot"></div><div class="typing-dot"></div><div class="typing-dot"></div>';
    body.appendChild(div);
    body.scrollTop = body.scrollHeight;
}

function hideTypingIndicator() {
    const el = document.getElementById('typingIndicator');
    if (el) el.remove();
}

function loadChatHistory() {
    fetch('<?= url('/api/chat/history') ?>')
    .then(r => r.json())
    .then(data => {
        if (data.success && data.history && data.history.length > 0) {
            const body = document.getElementById('aiChatBody');
            body.innerHTML = ''; // Clear default welcome
            data.history.forEach(item => {
                appendMessage(item.message, 'user');
                appendMessage(item.response, 'bot');
            });
        }
    });
}
</script>
