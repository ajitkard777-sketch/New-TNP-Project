/**
 * TPMS - Real-Time Messaging & Chat Engine
 * AJAX polling, file attachments, typing indicator, read receipts, online presence.
 */

const TPMS_Chat = {
    activePartnerId: 0,
    lastMessageId: 0,
    pollInterval: null,
    typingTimer: null,
    isTyping: false,
    initialPartnerId: 0,

    init() {
        const chatContainer = document.getElementById('chatContainer');
        if (!chatContainer) return;

        this.initialPartnerId = parseInt(chatContainer.dataset.partnerId || 0);

        this.bindEvents();
        this.loadConversations();
        this.startPolling();
    },

    bindEvents() {
        // Conversation search filter
        const searchInput = document.getElementById('chatSearchInput');
        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                this.loadConversations(e.target.value.trim());
            });
        }

        // Send Form submit
        const sendForm = document.getElementById('chatSendForm');
        if (sendForm) {
            sendForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.sendMessage();
            });
        }

        // Message input enter key (Shift+Enter for newline)
        const msgInput = document.getElementById('chatMsgInput');
        if (msgInput) {
            msgInput.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    this.sendMessage();
                }
            });

            // Typing indicator trigger
            msgInput.addEventListener('input', () => {
                this.handleTyping();
            });
        }

        // File attachment click & change
        const fileInput = document.getElementById('chatFileInput');
        const attachBtn = document.getElementById('chatAttachBtn');
        if (attachBtn && fileInput) {
            attachBtn.addEventListener('click', () => fileInput.click());
            fileInput.addEventListener('change', () => this.handleFileSelect());
        }

        // Remove attachment button
        const removeFileBtn = document.getElementById('chatRemoveFileBtn');
        if (removeFileBtn) {
            removeFileBtn.addEventListener('click', () => this.clearFileSelect());
        }

        // Mobile sidebar toggle
        const toggleBtn = document.getElementById('mobileSidebarToggle');
        const sidebar = document.getElementById('chatSidebar');
        if (toggleBtn && sidebar) {
            toggleBtn.addEventListener('click', () => {
                sidebar.classList.toggle('open');
            });
        }
    },

    /**
     * Fetch conversation list
     */
    loadConversations(search = '') {
        const listContainer = document.getElementById('conversationList');
        if (!listContainer) return;

        fetch(`${TPMS.baseUrl}/messages/conversations?search=${encodeURIComponent(search)}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            if (!data.success) return;

            this.updateBadges(data.total_unread);
            this.renderConversationList(data.conversations);

            // Auto select initial partner if provided in query string
            if (this.initialPartnerId > 0 && !this.activePartnerId) {
                this.selectConversation(this.initialPartnerId);
                this.initialPartnerId = 0;
            }
        })
        .catch(err => console.error('Error loading conversations:', err));
    },

    /**
     * Render conversation items in sidebar
     */
    renderConversationList(conversations) {
        const listContainer = document.getElementById('conversationList');
        if (!listContainer) return;

        if (conversations.length === 0) {
            listContainer.innerHTML = `
                <div class="p-4 text-center text-muted" style="font-size:0.85rem">
                    <i class="fas fa-comments mb-2 d-block text-primary" style="font-size:2.2rem;opacity:0.4"></i>
                    <div class="fw-semibold mb-1">No Active Conversations</div>
                    <p class="small text-muted mb-3">Chats are enabled once an application is submitted for a job opening.</p>
                    <a href="${TPMS.baseUrl}/student/jobs" class="btn btn-sm btn-outline-primary" style="font-size:0.78rem">
                        <i class="fas fa-briefcase me-1"></i> Explore Opportunities
                    </a>
                </div>
            `;
            return;
        }

        let html = '';
        conversations.forEach(c => {
            const isActive = c.partner_id === this.activePartnerId ? 'active' : '';
            const unreadBadge = c.unread_count > 0 ? `<span class="unread-badge">${c.unread_count}</span>` : '';
            const onlineDot = c.is_online ? `<span class="online-dot"></span>` : '';

            html += `
                <div class="conversation-item ${isActive}" data-partner-id="${c.partner_id}" onclick="TPMS_Chat.selectConversation(${c.partner_id})">
                    <div class="conversation-avatar-wrapper">
                        <img src="${c.avatar}" alt="" class="conversation-avatar" onerror="this.src='${TPMS.baseUrl}/assets/images/default-avatar.png'">
                        ${onlineDot}
                    </div>
                    <div class="conversation-info">
                        <div class="conversation-top">
                            <span class="conversation-name">${this.escapeHtml(c.name)}</span>
                            <span class="conversation-time">${c.last_time}</span>
                        </div>
                        <div class="conversation-subtitle">${this.escapeHtml(c.subtitle)}</div>
                        <div class="conversation-preview">${c.is_typing ? '<em class="text-primary">typing...</em>' : this.escapeHtml(c.last_message)}</div>
                    </div>
                    ${unreadBadge}
                </div>
            `;
        });

        listContainer.innerHTML = html;
    },

    /**
     * Select active conversation and load history
     */
    selectConversation(partnerId) {
        if (this.activePartnerId === partnerId) return;

        this.activePartnerId = partnerId;
        this.lastMessageId = 0;

        // Close mobile sidebar drawer if open
        const sidebar = document.getElementById('chatSidebar');
        if (sidebar) sidebar.classList.remove('open');

        // Highlight selected in sidebar
        document.querySelectorAll('.conversation-item').forEach(item => {
            item.classList.toggle('active', parseInt(item.dataset.partnerId) === partnerId);
        });

        // Show chat view, hide empty state
        const emptyState = document.getElementById('chatEmptyState');
        const mainPanel  = document.getElementById('chatMainPanel');
        if (emptyState) emptyState.style.display = 'none';
        if (mainPanel)  mainPanel.style.display  = 'flex';

        this.loadHistory(partnerId);
    },

    /**
     * Load message stream for active conversation
     */
    loadHistory(partnerId) {
        const messagesBox = document.getElementById('chatMessages');
        if (!messagesBox) return;

        messagesBox.innerHTML = `
            <div class="text-center text-muted p-4">
                <div class="spinner-border spinner-border-sm text-primary me-2"></div>
                Loading message history...
            </div>
        `;

        fetch(`${TPMS.baseUrl}/messages/history?partner_id=${partnerId}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                messagesBox.innerHTML = `<div class="alert alert-danger m-3">${data.message}</div>`;
                return;
            }

            // Render Header Partner Details
            document.getElementById('chatHeaderName').textContent = data.partner.name;
            document.getElementById('chatHeaderAvatar').src = data.partner.avatar;

            const statusEl = document.getElementById('chatHeaderStatus');
            statusEl.className = 'chat-header-status ' + (data.partner.is_online ? 'online' : '');
            statusEl.innerHTML = data.partner.is_typing
                ? '<i class="fas fa-pencil-alt text-primary"></i> typing...'
                : (data.partner.is_online ? '<i class="fas fa-circle" style="font-size:0.6rem"></i> Online' : data.partner.last_seen);

            // Render Messages
            this.renderMessages(data.messages, true);

            // Enable input controls
            document.getElementById('chatMsgInput').disabled = false;
            document.getElementById('chatSendBtn').disabled = false;
            document.getElementById('chatAttachBtn').disabled = false;
        })
        .catch(err => {
            console.error('Error loading history:', err);
            messagesBox.innerHTML = `<div class="alert alert-danger m-3">Failed to load chat history.</div>`;
        });
    },

    /**
     * Render message list into chat viewport
     */
    renderMessages(messages, clearFirst = false) {
        const messagesBox = document.getElementById('chatMessages');
        if (!messagesBox) return;

        if (clearFirst) {
            messagesBox.innerHTML = '';
        }

        if (messages.length === 0 && clearFirst) {
            messagesBox.innerHTML = `
                <div class="chat-empty-state">
                    <i class="fas fa-comments"></i>
                    <h6>No messages yet</h6>
                    <small>Send a message or attachment to start the conversation.</small>
                </div>
            `;
            return;
        }

        // Remove empty state if present
        const emptyEl = messagesBox.querySelector('.chat-empty-state');
        if (emptyEl) emptyEl.remove();

        messages.forEach(m => {
            if (m.id > this.lastMessageId) {
                this.lastMessageId = m.id;
            }

            const isMine = parseInt(m.is_mine) === 1;
            const groupClass = isMine ? 'sent' : 'received';

            let fileHtml = '';
            if (m.file_path) {
                if (m.file_type === 'image') {
                    fileHtml = `<a href="${m.file_url}" target="_blank">
                        <img src="${m.file_url}" class="chat-image-preview" alt="Attachment">
                    </a>`;
                } else {
                    fileHtml = `
                        <a href="${m.file_url}" class="attachment-card" target="_blank">
                            <i class="fas fa-file-pdf attachment-icon text-danger"></i>
                            <div class="attachment-info">
                                <div class="attachment-name">${this.escapeHtml(m.file_name || 'Document')}</div>
                                <div class="attachment-size">${this.formatFileSize(m.file_size)}</div>
                            </div>
                            <i class="fas fa-download ms-2"></i>
                        </a>
                    `;
                }
            }

            const textHtml = m.message ? `<div>${this.escapeHtml(m.message).replace(/\n/g, '<br>')}</div>` : '';
            const readIcon = isMine ? `<i class="fas fa-check-double read-check ${m.is_read ? 'read' : ''}"></i>` : '';

            const msgDiv = document.createElement('div');
            msgDiv.className = `message-group ${groupClass}`;
            msgDiv.dataset.messageId = m.id;
            msgDiv.innerHTML = `
                <div class="message-bubble">
                    ${textHtml}
                    ${fileHtml}
                </div>
                <div class="message-meta">
                    <span>${m.time_formatted}</span>
                    ${readIcon}
                </div>
            `;

            messagesBox.appendChild(msgDiv);
        });

        this.scrollToBottom();
    },

    /**
     * Send Message Action
     */
    sendMessage() {
        if (!this.activePartnerId) return;

        const input = document.getElementById('chatMsgInput');
        const fileInput = document.getElementById('chatFileInput');
        const text = input ? input.value.trim() : '';
        const file = fileInput && fileInput.files.length > 0 ? fileInput.files[0] : null;

        if (!text && !file) return;

        const formData = new FormData();
        formData.append('csrf_token', TPMS.csrfToken);
        formData.append('receiver_id', this.activePartnerId);
        formData.append('message', text);
        if (file) {
            formData.append('attachment', file);
        }

        // Clear inputs immediately for smooth UX
        if (input) input.value = '';
        this.clearFileSelect();

        // Cancel typing indicator
        this.sendTypingStatus(false);

        fetch(`${TPMS.baseUrl}/messages/send`, {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                Swal.fire({ icon: 'error', title: 'Sending Failed', text: data.message });
                return;
            }

            this.renderMessages([data.message], false);
            this.loadConversations();
        })
        .catch(err => {
            console.error('Error sending message:', err);
            Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to send message.' });
        });
    },

    /**
     * Polling Engine — checks for new messages every 2.5s
     */
    startPolling() {
        if (this.pollInterval) clearInterval(this.pollInterval);

        this.pollInterval = setInterval(() => {
            fetch(`${TPMS.baseUrl}/messages/poll?partner_id=${this.activePartnerId}&last_id=${this.lastMessageId}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(res => res.json())
            .then(data => {
                if (!data.success) return;

                // Update unread count badge
                this.updateBadges(data.total_unread);

                // Update presence info in active chat header
                if (this.activePartnerId > 0 && data.presence) {
                    const statusEl = document.getElementById('chatHeaderStatus');
                    if (statusEl) {
                        statusEl.className = 'chat-header-status ' + (data.presence.is_online ? 'online' : '');
                        statusEl.innerHTML = data.presence.is_typing
                            ? '<i class="fas fa-pencil-alt text-primary"></i> typing...'
                            : (data.presence.is_online ? '<i class="fas fa-circle" style="font-size:0.6rem"></i> Online' : data.presence.last_seen);
                    }
                }

                // If new messages received in active chat window
                if (data.new_messages && data.new_messages.length > 0) {
                    this.renderMessages(data.new_messages, false);
                    this.loadConversations();
                }
            })
            .catch(err => console.error('Polling error:', err));
        }, 2500);
    },

    /**
     * Send typing status update
     */
    handleTyping() {
        if (!this.activePartnerId) return;

        if (!this.isTyping) {
            this.isTyping = true;
            this.sendTypingStatus(true);
        }

        clearTimeout(this.typingTimer);
        this.typingTimer = setTimeout(() => {
            this.isTyping = false;
            this.sendTypingStatus(false);
        }, 3000);
    },

    sendTypingStatus(isTyping) {
        if (!this.activePartnerId) return;

        const formData = new FormData();
        formData.append('csrf_token', TPMS.csrfToken);
        formData.append('target_id', this.activePartnerId);
        formData.append('is_typing', isTyping ? 1 : 0);

        fetch(`${TPMS.baseUrl}/messages/typing`, {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        }).catch(() => {});
    },

    /**
     * File Selection Handlers
     */
    handleFileSelect() {
        const fileInput = document.getElementById('chatFileInput');
        const previewBar = document.getElementById('chatAttachmentPreview');
        const fileNameEl = document.getElementById('chatAttachmentName');

        if (fileInput && fileInput.files.length > 0) {
            const file = fileInput.files[0];
            if (fileNameEl) fileNameEl.textContent = file.name + ' (' + this.formatFileSize(file.size) + ')';
            if (previewBar) previewBar.style.display = 'flex';
        }
    },

    clearFileSelect() {
        const fileInput = document.getElementById('chatFileInput');
        const previewBar = document.getElementById('chatAttachmentPreview');
        if (fileInput) fileInput.value = '';
        if (previewBar) previewBar.style.display = 'none';
    },

    /**
     * Update Navbar & Sidebar Unread Badges
     */
    updateBadges(totalUnread) {
        document.querySelectorAll('.chat-unread-badge').forEach(badge => {
            if (totalUnread > 0) {
                badge.textContent = totalUnread;
                badge.style.display = 'inline-block';
            } else {
                badge.style.display = 'none';
            }
        });
    },

    /**
     * Scroll chat stream to bottom
     */
    scrollToBottom() {
        const box = document.getElementById('chatMessages');
        if (box) {
            box.scrollTop = box.scrollHeight;
        }
    },

    /**
     * Utilities
     */
    escapeHtml(str) {
        if (!str) return '';
        return str.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
    },

    formatFileSize(bytes) {
        if (!bytes) return '';
        if (bytes >= 1048576) return (bytes / 1048576).toFixed(1) + ' MB';
        if (bytes >= 1024) return (bytes / 1024).toFixed(0) + ' KB';
        return bytes + ' B';
    }
};

document.addEventListener('DOMContentLoaded', () => {
    TPMS_Chat.init();
});
