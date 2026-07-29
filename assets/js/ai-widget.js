/**
 * TPMS - AI Chat Widget JavaScript
 * Standalone module — no framework dependency.
 * Works with Bootstrap 5, jQuery (already loaded in app), and marked.js (loaded below).
 */

(function () {
    'use strict';

    // ─── Load marked.js for markdown rendering (lightweight) ──────
    if (typeof marked === 'undefined') {
        const script = document.createElement('script');
        script.src = 'https://cdn.jsdelivr.net/npm/marked@9.1.6/marked.min.js';
        script.async = true;
        document.head.appendChild(script);
    }

    // ─── State ────────────────────────────────────────────────────
    const state = {
        isOpen:     false,
        isLoading:  false,
        initialized: false,
    };

    // ─── DOM refs (populated after DOMContentLoaded) ──────────────
    let widget, panel, toggleBtn, closeBtn, clearBtn,
        messagesArea, textarea, sendBtn, charCount;

    let baseUrl  = '';
    let greeting = '';
    let role     = '';

    // ─── Init ─────────────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', function () {
        widget       = document.getElementById('ai-chat-widget');
        if (!widget) return; // not logged in or widget missing

        panel        = document.getElementById('ai-chat-panel');
        toggleBtn    = document.getElementById('ai-chat-toggle');
        closeBtn     = document.getElementById('ai-chat-close');
        clearBtn     = document.getElementById('ai-clear-btn');
        messagesArea = document.getElementById('ai-messages');
        textarea     = document.getElementById('ai-message-input');
        sendBtn      = document.getElementById('ai-send-btn');
        charCount    = document.getElementById('ai-char-count');

        baseUrl  = widget.dataset.baseUrl  || '/TNP';
        greeting = widget.dataset.greeting || 'Hello! How can I help you?';
        role     = widget.dataset.role     || 'student';

        bindEvents();
    });

    // ─── Event Bindings ───────────────────────────────────────────
    function bindEvents() {
        toggleBtn.addEventListener('click', togglePanel);
        closeBtn.addEventListener('click',  closePanel);

        clearBtn.addEventListener('click', function () {
            if (!confirm('Clear this conversation?')) return;
            clearHistory();
        });

        textarea.addEventListener('input', onTextareaInput);
        textarea.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                if (!sendBtn.disabled) sendMessage();
            }
        });

        sendBtn.addEventListener('click', sendMessage);
    }

    // ─── Panel Toggle ─────────────────────────────────────────────
    function togglePanel() {
        if (state.isOpen) {
            closePanel();
        } else {
            openPanel();
        }
    }

    function openPanel() {
        state.isOpen = true;
        panel.classList.add('ai-panel-open');
        panel.setAttribute('aria-hidden', 'false');
        toggleBtn.setAttribute('aria-expanded', 'true');
        toggleBtn.classList.add('ai-fab-active');

        if (!state.initialized) {
            state.initialized = true;
            loadHistory();
        } else {
            scrollToBottom();
        }

        setTimeout(() => textarea.focus(), 300);
    }

    function closePanel() {
        state.isOpen = false;
        panel.classList.remove('ai-panel-open');
        panel.setAttribute('aria-hidden', 'true');
        toggleBtn.setAttribute('aria-expanded', 'false');
        toggleBtn.classList.remove('ai-fab-active');
    }

    // ─── Textarea auto-resize + char count ───────────────────────
    function onTextareaInput() {
        const len = textarea.value.length;
        charCount.textContent = len + '/2000';
        charCount.style.color = len > 1800 ? '#ef4444' : '';

        sendBtn.disabled = (len === 0 || state.isLoading);

        // Auto-resize
        textarea.style.height = 'auto';
        textarea.style.height = Math.min(textarea.scrollHeight, 120) + 'px';
    }

    // ─── Load history from server ─────────────────────────────────
    function loadHistory() {
        fetch(baseUrl + '/ai-chat/history', {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-Token': getCsrfToken(),
            },
            credentials: 'same-origin'
        })
        .then(r => r.json())
        .then(data => {
            if (data.success && data.history && data.history.length > 0) {
                messagesArea.innerHTML = '';
                data.history.forEach(function (msg) {
                    appendMessage(msg.sender, msg.message, false);
                });
                scrollToBottom();
            } else {
                // Show welcome greeting
                appendMessage('ai', greeting, true);
            }
        })
        .catch(function () {
            appendMessage('ai', greeting, true);
        });
    }

    // ─── Send message ─────────────────────────────────────────────
    function sendMessage() {
        const text = textarea.value.trim();
        if (!text || state.isLoading) return;

        appendMessage('user', text, true);
        textarea.value = '';
        textarea.style.height = 'auto';
        charCount.textContent = '0/2000';
        sendBtn.disabled = true;

        showTypingIndicator();
        state.isLoading = true;

        const csrfToken = getCsrfToken();

        fetch(baseUrl + '/ai-chat/chat', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-Token': csrfToken,
            },
            credentials: 'same-origin',
            body: JSON.stringify({ message: text, _csrf: csrfToken })
        })
        .then(r => r.json())
        .then(data => {
            removeTypingIndicator();
            state.isLoading = false;
            sendBtn.disabled = false;

            if (data.reply) {
                appendMessage('ai', data.reply, true);
            } else if (data.error) {
                appendMessage('ai', '⚠️ ' + data.error, true);
            }

            textarea.focus();
        })
        .catch(function (err) {
            removeTypingIndicator();
            state.isLoading = false;
            sendBtn.disabled = false;
            appendMessage('ai', '⚠️ Network error. Please check your connection and try again.', true);
            console.error('AI Chat error:', err);
        });
    }

    // ─── Clear history ────────────────────────────────────────────
    function clearHistory() {
        fetch(baseUrl + '/ai-chat/clear', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-Token': getCsrfToken(),
            },
            credentials: 'same-origin',
            body: JSON.stringify({ _csrf: getCsrfToken() })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                messagesArea.innerHTML = '';
                appendMessage('ai', greeting, true);
            }
        })
        .catch(function () {
            appendMessage('ai', '⚠️ Could not clear history. Please try again.', true);
        });
    }

    // ─── DOM helpers ──────────────────────────────────────────────
    function appendMessage(sender, text) {
        const wrapper = document.createElement('div');
        wrapper.className = 'ai-msg-wrapper ' + (sender === 'user' ? 'ai-msg-user' : 'ai-msg-ai');

        const bubble = document.createElement('div');
        bubble.className = 'ai-msg-bubble';

        if (sender === 'ai') {
            // Render markdown — wait for marked.js to load
            if (typeof marked !== 'undefined') {
                bubble.innerHTML = marked.parse(text);
            } else {
                // Fallback: basic newline handling until marked loads
                bubble.innerHTML = text.replace(/\n/g, '<br>');
                // Retry once marked is loaded
                setTimeout(function () {
                    if (typeof marked !== 'undefined') {
                        bubble.innerHTML = marked.parse(text);
                    }
                }, 1500);
            }
        } else {
            bubble.textContent = text;
        }

        const timeEl = document.createElement('span');
        timeEl.className = 'ai-msg-time';
        timeEl.textContent = formatTime(new Date());

        wrapper.appendChild(bubble);
        wrapper.appendChild(timeEl);
        messagesArea.appendChild(wrapper);
        scrollToBottom();
    }

    function showTypingIndicator() {
        const el = document.createElement('div');
        el.className  = 'ai-msg-wrapper ai-msg-ai';
        el.id         = 'ai-typing-indicator';
        el.innerHTML  = '<div class="ai-msg-bubble ai-typing"><span></span><span></span><span></span></div>';
        messagesArea.appendChild(el);
        scrollToBottom();
    }

    function removeTypingIndicator() {
        const el = document.getElementById('ai-typing-indicator');
        if (el) el.remove();
    }

    function scrollToBottom() {
        messagesArea.scrollTop = messagesArea.scrollHeight;
    }

    // ─── Utilities ────────────────────────────────────────────────
    function getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.content || '';
    }

    function formatTime(date) {
        return date.toLocaleTimeString('en-IN', { hour: '2-digit', minute: '2-digit' });
    }

})();
