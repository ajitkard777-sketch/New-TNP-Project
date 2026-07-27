<?php
/**
 * TPMS - AI Admin Assistant Floating Chatbot Widget
 * Included on all Admin Portal pages
 */
if (!AuthMiddleware::isLoggedIn() || ($_SESSION['user_role'] ?? '') !== 'admin') {
    return;
}
?>
<style>
/* Admin Floating AI Chatbot Widget Styles */
.admin-chat-trigger {
    position: fixed;
    bottom: 25px;
    right: 25px;
    width: 62px;
    height: 62px;
    border-radius: 50%;
    background: linear-gradient(135deg, #0f172a, #3b82f6);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.6rem;
    cursor: pointer;
    box-shadow: 0 10px 25px -5px rgba(59, 130, 246, 0.5);
    z-index: 1050;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    border: 2px solid rgba(255, 255, 255, 0.3);
}

.admin-chat-trigger:hover {
    transform: scale(1.08) rotate(-5deg);
    box-shadow: 0 15px 30px -5px rgba(15, 23, 42, 0.6);
}

.admin-chat-pulse {
    position: absolute;
    top: -2px;
    right: -2px;
    width: 15px;
    height: 15px;
    background: #3b82f6;
    border: 2px solid white;
    border-radius: 50%;
}

.admin-chat-window {
    position: fixed;
    bottom: 95px;
    right: 25px;
    width: 440px;
    max-width: calc(100vw - 40px);
    height: 600px;
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

.admin-chat-window.active {
    opacity: 1;
    transform: translateY(0) scale(1);
    pointer-events: all;
}

.dark-mode .admin-chat-window {
    background: rgba(15, 23, 42, 0.95);
    border-color: rgba(51, 65, 85, 0.8);
    color: #f8fafc;
}

.admin-chat-header {
    background: linear-gradient(135deg, #0f172a, #1e293b);
    color: white;
    padding: 1rem 1.25rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 2px solid #3b82f6;
}

.admin-chat-body {
    flex: 1;
    padding: 1rem;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 0.85rem;
}

.admin-chat-pills {
    padding: 0.5rem 1rem;
    display: flex;
    gap: 0.4rem;
    overflow-x: auto;
    white-space: nowrap;
    border-top: 1px solid rgba(226, 232, 240, 0.5);
    background: rgba(248, 250, 252, 0.5);
}

.admin-chat-footer {
    padding: 0.75rem 1rem;
    border-top: 1px solid rgba(226, 232, 240, 0.8);
    background: white;
}

.dark-mode .admin-chat-footer {
    background: #0f172a;
    border-color: rgba(51, 65, 85, 0.8);
}
</style>

<!-- Floating AI Admin Chat Trigger -->
<div class="admin-chat-trigger" onclick="toggleAdminAiChat()" id="adminAiChatTrigger" title="Open AI Admin Assistant">
    <img src="<?= asset('images/admin-bot-logo.svg') ?>" alt="AI Admin Logo" style="width: 28px; height: 28px; object-fit: contain; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.3));" onerror="this.style.display='none'; this.nextElementSibling.style.display='inline-block';">
    <i class="fas fa-user-shield" style="display:none;"></i>
    <div class="admin-chat-pulse"></div>
</div>

<!-- Admin Chat Window -->
<div class="admin-chat-window" id="adminAiChatWindow">
    <div class="admin-chat-header">
        <div class="d-flex align-items-center gap-2">
            <div class="bg-white text-primary rounded-circle d-flex align-items-center justify-content-center p-1" style="width: 38px; height: 38px;">
                <img src="<?= asset('images/admin-bot-logo.svg') ?>" alt="AI Admin Avatar" style="width: 28px; height: 28px; object-fit: contain;" onerror="this.style.display='none'; this.nextElementSibling.style.display='inline-block';">
                <i class="fas fa-robot text-primary" style="display:none; font-size: 1.1rem;"></i>
            </div>
            <div>
                <h6 class="fw-bold mb-0 text-white" style="font-size: 0.95rem;">AI Admin Assistant</h6>
                <small class="text-white-50" style="font-size: 0.72rem;">Placement &amp; Candidate Intelligence</small>
            </div>
        </div>
        <button type="button" class="btn-close btn-close-white btn-sm" onclick="toggleAdminAiChat()"></button>
    </div>

    <!-- Message Stream -->
    <div class="admin-chat-body" id="adminAiChatBody">
        <div class="chat-msg bot animate-fade-in-up">
            🛡️ Welcome Administrator!<br><br>
            I am your <strong>AI Admin Assistant</strong>. Ask me to find candidate matches, filter by CGPA/skills, generate placement statistics, or export reports!
        </div>
    </div>

    <!-- Quick Question Chips -->
    <div class="admin-chat-pills" id="adminAiChatPills">
        <button class="chat-pill-btn" onclick="sendAdminChatPrompt('Recommend students for Job ID 1')">🤖 Recommend Candidates</button>
        <button class="chat-pill-btn" onclick="sendAdminChatPrompt('Show students with CGPA above 8.0')">📊 CGPA $\ge$ 8.0</button>
        <button class="chat-pill-btn" onclick="sendAdminChatPrompt('Show students with Java and React')">💻 Java + React Devs</button>
        <button class="chat-pill-btn" onclick="sendAdminChatPrompt('How many students applied today?')">📈 Application Stats</button>
        <button class="chat-pill-btn" onclick="sendAdminChatPrompt('Generate placement statistics')">🏆 Placement Analytics</button>
        <button class="chat-pill-btn" onclick="sendAdminChatPrompt('Which skills are currently in high demand?')">🔥 High Demand Skills</button>
        <button class="chat-pill-btn" onclick="sendAdminChatPrompt('Which students are not placement-ready?')">⚠️ Needs Support</button>
    </div>

    <!-- Footer Input Form -->
    <div class="admin-chat-footer">
        <form onsubmit="handleAdminChatSubmit(event)" id="adminAiChatForm" class="d-flex gap-2">
            <input type="text" class="form-control form-control-sm rounded-pill px-3" id="adminAiChatInput" placeholder="Ask admin query (e.g. Students above 8.5 CGPA)..." autocomplete="off">
            <button type="submit" class="btn btn-primary btn-sm rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; flex-shrink: 0;">
                <i class="fas fa-paper-plane"></i>
            </button>
        </form>
    </div>
</div>

<script>
function toggleAdminAiChat() {
    const win = document.getElementById('adminAiChatWindow');
    win.classList.toggle('active');
    if (win.classList.contains('active')) {
        document.getElementById('adminAiChatInput').focus();
    }
}

function sendAdminChatPrompt(text) {
    document.getElementById('adminAiChatInput').value = text;
    handleAdminChatSubmit(new Event('submit'));
}

function handleAdminChatSubmit(e) {
    if (e) e.preventDefault();
    const input = document.getElementById('adminAiChatInput');
    const msg = input.value.trim();
    if (!msg) return;

    input.value = '';
    appendAdminMessage(msg, 'user');
    showAdminTypingIndicator();

    fetch('<?= url('/api/admin/chat') ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'message=' + encodeURIComponent(msg) + '&csrf_token=<?= $_SESSION['csrf_token'] ?? '' ?>'
    })
    .then(r => r.json())
    .then(data => {
        hideAdminTypingIndicator();
        if (data.success) {
            appendAdminMessage(data.response, 'bot');
        } else {
            appendAdminMessage('Sorry, I encountered an issue. Please try again.', 'bot');
        }
    })
    .catch(err => {
        hideAdminTypingIndicator();
        appendAdminMessage('Network error. Please try again.', 'bot');
    });
}

function appendAdminMessage(text, sender) {
    const body = document.getElementById('adminAiChatBody');
    const div = document.createElement('div');
    div.className = 'chat-msg ' + sender + ' animate-fade-in-up';
    
    let formatted = text
        .replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;")
        .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
        .replace(/\*(.*?)\*/g, '<em>$1</em>')
        .replace(/\[(.*?)\]\((.*?)\)/g, '<a href="$2" target="_blank" style="color:#3b82f6;font-weight:600;">$1</a>')
        .replace(/\n/g, '<br>');

    div.innerHTML = formatted;
    body.appendChild(div);
    body.scrollTop = body.scrollHeight;
}

function showAdminTypingIndicator() {
    hideAdminTypingIndicator();
    const body = document.getElementById('adminAiChatBody');
    const div = document.createElement('div');
    div.id = 'adminTypingIndicator';
    div.className = 'typing-indicator';
    div.innerHTML = '<div class="typing-dot"></div><div class="typing-dot"></div><div class="typing-dot"></div>';
    body.appendChild(div);
    body.scrollTop = body.scrollHeight;
}

function hideAdminTypingIndicator() {
    const el = document.getElementById('adminTypingIndicator');
    if (el) el.remove();
}
</script>
