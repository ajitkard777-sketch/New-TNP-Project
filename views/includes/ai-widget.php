<?php
/**
 * TPMS - AI Chat Widget
 * Floating AI assistant. Included by footer.php on all authenticated pages.
 * Role-aware: student, company, admin.
 */

// Only render if user is logged in
if (!AuthMiddleware::isLoggedIn()) return;

$aiRole        = $currentRole ?? getCurrentUserRole() ?? 'student';
$aiUserName    = '';
$aiGreeting    = '';
$aiPlaceholder = '';
$aiIcon        = 'fa-robot';
$aiColor       = '#6366f1'; // indigo default

switch ($aiRole) {
    case 'student':
        $aiGreeting    = "👋 Hi! I'm your **AI Career Assistant**.\n\nI can help you with:\n- 🎯 Job recommendations\n- 📄 Resume tips\n- 🎤 Interview preparation\n- 📚 Skill gap analysis\n- 🗺️ Career roadmap\n\nWhat would you like to know?";
        $aiPlaceholder = 'Ask about jobs, resume, interviews...';
        $aiIcon        = 'fa-graduation-cap';
        $aiColor       = '#6366f1';
        $aiTitle       = 'AI Career Assistant';
        break;
    case 'company':
        $aiGreeting    = "👋 Hi! I'm your **AI Recruitment Assistant**.\n\nI can help you with:\n- 📋 Writing better job descriptions\n- 👥 Ranking and recommending candidates\n- ❓ Interview question generation\n- 💰 Salary benchmarking\n- 🔍 Candidate profile summaries\n\nHow can I assist you today?";
        $aiPlaceholder = 'Ask about candidates, JDs, interviews...';
        $aiIcon        = 'fa-briefcase';
        $aiColor       = '#0ea5e9';
        $aiTitle       = 'AI Recruitment Assistant';
        break;
    case 'admin':
        $aiGreeting    = "👋 Hi! I'm your **AI Analytics Assistant**.\n\nI can help you with:\n- 📊 Placement statistics & trends\n- 🔮 Predictive insights\n- ⚠️ At-risk student identification\n- 📈 Report summaries\n- 💡 Process improvement suggestions\n\nWhat analytics do you need?";
        $aiPlaceholder = 'Ask about placements, trends, reports...';
        $aiIcon        = 'fa-chart-line';
        $aiColor       = '#10b981';
        $aiTitle       = 'AI Analytics Assistant';
        break;
    default:
        $aiGreeting    = "👋 Hi! I'm your AI assistant. How can I help?";
        $aiPlaceholder = 'Type a message...';
        $aiTitle       = 'AI Assistant';
}
?>

<!-- =====================================================
     AI CHAT WIDGET
     ===================================================== -->
<div id="ai-chat-widget"
     data-role="<?= htmlspecialchars($aiRole) ?>"
     data-greeting="<?= htmlspecialchars($aiGreeting) ?>"
     data-base-url="<?= BASE_URL ?>"
     aria-label="AI Assistant Chat Widget">

    <!-- Floating Toggle Button -->
    <button id="ai-chat-toggle"
            class="ai-chat-fab"
            title="<?= htmlspecialchars($aiTitle) ?>"
            aria-expanded="false"
            aria-controls="ai-chat-panel">
        <span class="ai-fab-icon-open"><i class="fas <?= $aiIcon ?>"></i></span>
        <span class="ai-fab-icon-close"><i class="fas fa-times"></i></span>
        <span class="ai-fab-pulse"></span>
    </button>

    <!-- Chat Panel -->
    <div id="ai-chat-panel" class="ai-chat-panel" role="dialog" aria-label="<?= htmlspecialchars($aiTitle) ?>" aria-hidden="true">

        <!-- Panel Header -->
        <div class="ai-panel-header">
            <div class="ai-panel-header-left">
                <div class="ai-avatar">
                    <i class="fas <?= $aiIcon ?>"></i>
                </div>
                <div class="ai-panel-title">
                    <span class="ai-name"><?= htmlspecialchars($aiTitle) ?></span>
                    <span class="ai-status"><span class="ai-status-dot"></span> Ready</span>
                </div>
            </div>
            <div class="ai-panel-header-right">
                <button id="ai-clear-btn" class="ai-icon-btn" title="Clear conversation" aria-label="Clear conversation">
                    <i class="fas fa-trash-alt"></i>
                </button>
                <button id="ai-chat-close" class="ai-icon-btn" title="Close" aria-label="Close AI assistant">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>

        <!-- Messages Area -->
        <div id="ai-messages" class="ai-messages-area" role="log" aria-live="polite" aria-label="Chat messages">
            <!-- Messages injected by JS -->
        </div>

        <!-- Input Area -->
        <div class="ai-input-area">
            <div class="ai-input-wrapper">
                <textarea
                    id="ai-message-input"
                    class="ai-textarea"
                    placeholder="<?= htmlspecialchars($aiPlaceholder) ?>"
                    rows="1"
                    maxlength="2000"
                    aria-label="Type your message"
                    autocomplete="off"></textarea>
                <button id="ai-send-btn" class="ai-send-btn" title="Send message" aria-label="Send message" disabled>
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
            <div class="ai-input-footer">
                <span class="ai-powered-by">Powered by <strong>Gemini AI</strong></span>
                <span id="ai-char-count" class="ai-char-count">0/2000</span>
            </div>
        </div>
    </div>
</div>
<!-- ===================================================== -->
