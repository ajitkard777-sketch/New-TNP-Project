/**
 * TPMS - Real-Time Chat System JS
 * Handles messaging, attachments, read receipts, online presence, typing indicators & desktop notifications.
 */

(function () {
    'use strict';

    // ── Global Unread Badge Polling (runs on all logged-in pages) ──
    const baseUrl = document.querySelector('meta[name="base-url"]')?.content || '/TNP';

    function getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.content || '';
    }

    function checkGlobalUnreadCount() {
        const navBadge = document.getElementById('navChatUnreadBadge');
        const fabBadge = document.getElementById('chatFabUnreadBadge');
        if (!navBadge && !fabBadge) return;

        fetch(baseUrl + '/chat/unread-count', {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(data => {
            const countStr = (data.success && data.unread_count > 99) ? '99+' : (data.unread_count || 0);
            const hasUnread = data.success && data.unread_count > 0;

            if (navBadge) {
                navBadge.textContent = countStr;
                navBadge.style.display = hasUnread ? 'flex' : 'none';
            }
            if (fabBadge) {
                fabBadge.textContent = countStr;
                fabBadge.style.display = hasUnread ? 'flex' : 'none';
            }
        })
        .catch(() => {});
    }


    // Run global badge check on page load & every 10 seconds
    document.addEventListener('DOMContentLoaded', function () {
        checkGlobalUnreadCount();
        setInterval(checkGlobalUnreadCount, 10000);
    });

    // If we are NOT on the /chat page, stop here (only global polling needed)
    if (!document.querySelector('.chat-page-wrapper')) {
        return;
    }

    // ── Full Chat Application Engine ──────────────────────────────
    const state = {
        activeConvId:       null,
        activeOtherUserId:  null,
        conversations:      [],
        messages:           [],
        contacts:           [],
        filter:             'all', // 'all', 'unread', 'archived'
        selectedFile:       null,
        pollingInterval:    null,
        typingTimeout:      null,
        isTypingSent:       false,
        lastMessageCount:   0,
        urlOpenUserId:      null,
        urlOpenConvId:      null,
        urlHandled:         false,
    };

    // DOM Elements
    let convListEl, emptyStateEl, activeContainerEl, messagesBodyEl,
        searchInputEl, filterBtns, textareaEl, sendBtnEl, formEl,
        attachBtnEl, fileInputEl, previewStripEl, previewNameEl, previewSizeEl, removeFileBtnEl,
        emojiBtnEl, emojiPickerEl, recipientAvatarEl, recipientNameEl, recipientRoleBadgeEl,
        recipientStatusTextEl, recipientOnlineDotEl, typingBannerEl, typingTextEl,
        newChatModalEl, newChatListEl, newChatSearchEl, inMsgSearchBoxEl, inMsgSearchInputEl,
        toggleArchiveBtnEl, archiveBtnTextEl, deleteConvBtnEl;

    document.addEventListener('DOMContentLoaded', function () {
        initChatApp();
    });

    function initChatApp() {
        // Bind DOM elements
        convListEl            = document.getElementById('chatConversationList');
        emptyStateEl          = document.getElementById('chatEmptyState');
        activeContainerEl     = document.getElementById('chatActiveContainer');
        messagesBodyEl        = document.getElementById('chatMessagesBody');
        searchInputEl         = document.getElementById('chatSearchInput');
        filterBtns            = document.querySelectorAll('.chat-filter-btn');
        textareaEl            = document.getElementById('chatMessageTextarea');
        sendBtnEl             = document.getElementById('chatSendBtn');
        formEl                = document.getElementById('chatMessageForm');
        attachBtnEl           = document.getElementById('chatAttachBtn');
        fileInputEl           = document.getElementById('chatFileInput');
        previewStripEl        = document.getElementById('chatAttachmentPreview');
        previewNameEl         = document.getElementById('chatAttachmentFileName');
        previewSizeEl         = document.getElementById('chatAttachmentFileSize');
        removeFileBtnEl       = document.getElementById('removeAttachmentBtn');
        emojiBtnEl            = document.getElementById('chatEmojiBtn');
        emojiPickerEl         = document.getElementById('chatEmojiPicker');
        recipientAvatarEl     = document.getElementById('chatRecipientAvatar');
        recipientNameEl       = document.getElementById('chatRecipientName');
        recipientRoleBadgeEl  = document.getElementById('chatRecipientRoleBadge');
        recipientStatusTextEl = document.getElementById('chatRecipientStatusText');
        recipientOnlineDotEl  = document.getElementById('chatRecipientOnlineDot');
        typingBannerEl        = document.getElementById('chatTypingBanner');
        typingTextEl          = document.getElementById('chatTypingText');
        newChatModalEl        = document.getElementById('newChatModal');
        newChatListEl         = document.getElementById('newChatContactsList');
        newChatSearchEl       = document.getElementById('newChatContactSearch');
        inMsgSearchBoxEl      = document.getElementById('chatInMsgSearchBox');
        inMsgSearchInputEl    = document.getElementById('inMsgSearchInput');
        toggleArchiveBtnEl    = document.getElementById('chatToggleArchiveBtn');
        archiveBtnTextEl      = document.getElementById('archiveBtnText');
        deleteConvBtnEl       = document.getElementById('chatDeleteConvBtn');

        // Request Browser Notification Permission
        if ('Notification' in window && Notification.permission === 'default') {
            Notification.requestPermission();
        }

        bindEvents();

        // Check URL query parameters for auto-open (e.g. /chat?user_id=5 or /chat?conversation_id=2)
        const urlParams = new URLSearchParams(window.location.search);
        const urlUserId = urlParams.get('user_id');
        const urlConvId = urlParams.get('conversation_id');

        // Store URL intent so loadConversations callback can act after async load
        state.urlOpenUserId = urlUserId ? parseInt(urlUserId) : null;
        state.urlOpenConvId = urlConvId ? parseInt(urlConvId) : null;

        loadConversations(true);

        // Fast Polling loop (every 3 seconds) for active chat & conversation list
        state.pollingInterval = setInterval(function () {
            loadConversations(false);
            if (state.activeConvId) {
                loadMessages(state.activeConvId, false);
            }
        }, 3000);
    }


    function bindEvents() {
        // Filter tabs
        filterBtns.forEach(btn => {
            btn.addEventListener('click', function () {
                filterBtns.forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                state.filter = this.dataset.filter;
                renderConversations();
            });
        });

        // Search input
        searchInputEl.addEventListener('input', function () {
            renderConversations();
        });

        // Textarea events (auto-resize & send on Enter)
        textareaEl.addEventListener('input', function () {
            updateInputState();
            triggerTypingIndicator();
        });

        textareaEl.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                if (!sendBtnEl.disabled) {
                    submitMessage();
                }
            }
        });

        // Form Submit
        formEl.addEventListener('submit', function (e) {
            e.preventDefault();
            submitMessage();
        });

        // Attachment button & file input
        attachBtnEl.addEventListener('click', () => fileInputEl.click());
        fileInputEl.addEventListener('change', function () {
            if (this.files && this.files[0]) {
                state.selectedFile = this.files[0];
                previewNameEl.textContent = state.selectedFile.name;
                previewSizeEl.textContent = '(' + formatBytes(state.selectedFile.size) + ')';
                previewStripEl.classList.remove('d-none');
                updateInputState();
            }
        });

        removeFileBtnEl.addEventListener('click', function () {
            state.selectedFile = null;
            fileInputEl.value = '';
            previewStripEl.classList.add('d-none');
            updateInputState();
        });

        // Emoji Picker Toggle
        emojiBtnEl.addEventListener('click', function (e) {
            e.stopPropagation();
            emojiPickerEl.classList.toggle('d-none');
        });

        document.addEventListener('click', function (e) {
            if (!emojiPickerEl.contains(e.target) && e.target !== emojiBtnEl) {
                emojiPickerEl.classList.add('d-none');
            }
        });

        emojiPickerEl.querySelectorAll('.chat-emoji-grid span').forEach(span => {
            span.addEventListener('click', function () {
                textareaEl.value += this.textContent;
                textareaEl.focus();
                updateInputState();
                emojiPickerEl.classList.add('d-none');
            });
        });

        // New Chat Modal Contacts
        if (newChatModalEl) {
            newChatModalEl.addEventListener('show.bs.modal', loadContacts);
            newChatSearchEl.addEventListener('input', filterContacts);
        }

        // Back button on mobile
        document.getElementById('chatBackToList')?.addEventListener('click', function () {
            document.querySelector('.chat-sidebar-panel').classList.remove('chat-hide-mobile');
            document.querySelector('.chat-main-window').classList.remove('chat-show-mobile');
        });

        // In-Message Search Toggle
        document.getElementById('chatToggleSearchInMsg')?.addEventListener('click', function () {
            inMsgSearchBoxEl.classList.toggle('d-none');
            if (!inMsgSearchBoxEl.classList.contains('d-none')) {
                inMsgSearchInputEl.focus();
            }
        });

        document.getElementById('closeInMsgSearch')?.addEventListener('click', function () {
            inMsgSearchBoxEl.classList.add('d-none');
            inMsgSearchInputEl.value = '';
            filterMessageBubbles('');
        });

        inMsgSearchInputEl?.addEventListener('input', function () {
            filterMessageBubbles(this.value.trim().toLowerCase());
        });

        // Archive Toggle
        toggleArchiveBtnEl?.addEventListener('click', function () {
            if (!state.activeConvId) return;
            fetch(baseUrl + '/chat/archive', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ conversation_id: state.activeConvId, _csrf: getCsrfToken() })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    loadConversations(true);
                }
            });
        });

        // Delete Conversation
        deleteConvBtnEl?.addEventListener('click', function () {
            if (!state.activeConvId) return;
            if (!confirm('Are you sure you want to delete this conversation? It will be removed from your list.')) return;

            fetch(baseUrl + '/chat/delete', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ conversation_id: state.activeConvId, _csrf: getCsrfToken() })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    state.activeConvId = null;
                    activeContainerEl.classList.add('d-none');
                    emptyStateEl.classList.remove('d-none');
                    loadConversations(true);
                }
            });
        });
    }

    // ── 1. Load & Render Conversations List ───────────────────────
    function loadConversations(isFirstLoad) {
        fetch(baseUrl + '/chat/conversations', {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                state.conversations = data.conversations;
                renderConversations();

                // Handle URL param auto-open (must be done after conversations load so list renders correctly)
                if (isFirstLoad && !state.urlHandled) {
                    state.urlHandled = true;
                    if (state.urlOpenConvId) {
                        openConversation(state.urlOpenConvId);
                    } else if (state.urlOpenUserId) {
                        startNewConversation(state.urlOpenUserId);
                    } else if (!state.activeConvId && data.conversations.length > 0) {
                        // Auto-open first conversation if no URL param
                        openConversation(data.conversations[0].id);
                    }
                }

                // Update unread filter badge
                const unreadTotal = data.conversations.reduce((acc, c) => acc + c.unread_count, 0);
                const filterBadge = document.getElementById('unreadFilterBadge');
                if (filterBadge) {
                    if (unreadTotal > 0) {
                        filterBadge.textContent = unreadTotal;
                        filterBadge.classList.remove('d-none');
                    } else {
                        filterBadge.classList.add('d-none');
                    }
                }
            }
        })
        .catch(() => {});
    }

    function renderConversations() {
        const query = searchInputEl.value.trim().toLowerCase();

        let filtered = state.conversations.filter(c => {
            // Tab Filter
            if (state.filter === 'unread' && c.unread_count === 0) return false;
            if (state.filter === 'archived' && !c.is_archived) return false;
            if (state.filter !== 'archived' && c.is_archived) return false;

            // Search Query Filter
            if (query) {
                const nameMatch = c.name.toLowerCase().includes(query);
                const lastMsgMatch = c.last_message ? c.last_message.toLowerCase().includes(query) : false;
                const roleMatch = c.role_label.toLowerCase().includes(query);
                return nameMatch || lastMsgMatch || roleMatch;
            }

            return true;
        });

        if (filtered.length === 0) {
            convListEl.innerHTML = `
                <div class="p-4 text-center text-muted">
                    <i class="far fa-comments mb-2 text-primary opacity-75" style="font-size:2rem"></i>
                    <div class="fw-bold small text-dark mb-1">No active conversations</div>
                    <p class="small text-muted mb-3" style="font-size:0.75rem">Start a chat with Placement Officers or Recruiters below:</p>
                    <button class="btn btn-sm btn-primary w-100 rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#newChatModal">
                        <i class="fas fa-plus-circle me-1"></i> Start New Message
                    </button>
                </div>
            `;
            return;
        }


        convListEl.innerHTML = '';
        filtered.forEach(c => {
            const item = document.createElement('div');
            item.className = 'chat-conv-item ' + (c.id === state.activeConvId ? 'active' : '') + (c.unread_count > 0 ? ' unread' : '');
            item.dataset.convId = c.id;

            const timeStr = c.last_time ? formatConvTime(c.last_time) : '';

            item.innerHTML = `
                <div class="position-relative flex-shrink-0">
                    <img src="${c.photo || baseUrl + '/assets/images/default-avatar.png'}" class="chat-avatar-md" onerror="this.src='${baseUrl}/assets/images/default-avatar.png'">
                    <span class="chat-online-dot ${c.is_online ? '' : 'd-none'}"></span>
                </div>
                <div class="min-w-0 flex-grow-1">
                    <div class="d-flex align-items-center justify-content-between gap-1 mb-1">
                        <span class="chat-conv-name text-truncate">${escapeHtml(c.name)}</span>
                        <span class="chat-conv-time">${timeStr}</span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between gap-1">
                        <span class="chat-conv-snippet text-truncate">
                            ${c.is_typing ? '<span class="text-primary fw-bold"><i class="fas fa-pencil-alt me-1"></i>typing...</span>' : escapeHtml(c.last_message || 'Attachment')}
                        </span>
                        ${c.unread_count > 0 ? `<span class="chat-conv-unread">${c.unread_count}</span>` : ''}
                    </div>
                </div>
            `;

            item.addEventListener('click', function () {
                openConversation(c.id);
            });

            convListEl.appendChild(item);
        });
    }

    // ── 2. Open & Load Messages for Conversation ──────────────────
    function openConversation(convId) {
        state.activeConvId = convId;

        // Mobile UI switch
        document.querySelector('.chat-sidebar-panel').classList.add('chat-hide-mobile');
        document.querySelector('.chat-main-window').classList.add('chat-show-mobile');

        renderConversations();
        loadMessages(convId, true);
    }

    function loadMessages(convId, forceScroll) {
        fetch(baseUrl + '/chat/messages/' + convId, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success && state.activeConvId === convId) {
                state.activeOtherUserId = data.other_user_id;
                state.messages          = data.messages;

                // Update Active Header Details
                const recipient = data.recipient;
                recipientAvatarEl.src = recipient.photo || (baseUrl + '/assets/images/default-avatar.png');
                recipientNameEl.textContent = recipient.name;
                recipientRoleBadgeEl.textContent = recipient.role_label;
                recipientStatusTextEl.textContent = recipient.detail || (data.is_online ? 'Online' : 'Offline');

                if (data.is_online) {
                    recipientOnlineDotEl.classList.remove('d-none');
                } else {
                    recipientOnlineDotEl.classList.add('d-none');
                }

                // Update Typing Banner
                if (data.is_typing) {
                    typingTextEl.textContent = recipient.name + ' is typing...';
                    typingBannerEl.classList.remove('d-none');
                } else {
                    typingBannerEl.classList.add('d-none');
                }

                // Update Archive Button State
                const conv = state.conversations.find(c => c.id === convId);
                if (conv && conv.is_archived) {
                    archiveBtnTextEl.textContent = 'Unarchive Conversation';
                } else {
                    archiveBtnTextEl.textContent = 'Archive Conversation';
                }

                // Desktop Notification for new incoming message
                if (!forceScroll && data.messages.length > state.lastMessageCount) {
                    const latest = data.messages[data.messages.length - 1];
                    if (latest && latest.sender_id === data.other_user_id) {
                        triggerDesktopNotification(recipient.name, latest.message || 'Sent an attachment');
                    }
                }

                state.lastMessageCount = data.messages.length;

                emptyStateEl.classList.add('d-none');
                activeContainerEl.classList.remove('d-none');

                renderMessageBubbles(forceScroll);
            }
        })
        .catch(() => {});
    }

    function renderMessageBubbles(forceScroll) {
        const wasAtBottom = isScrolledToBottom(messagesBodyEl);
        messagesBodyEl.innerHTML = '';

        if (!state.messages || state.messages.length === 0) {
            messagesBodyEl.innerHTML = `
                <div class="d-flex flex-column align-items-center justify-content-center h-100 p-4 text-center text-muted chat-empty-msg-placeholder">
                    <div class="bg-primary-subtle rounded-circle p-3 mb-3 text-primary" style="width:60px;height:60px;display:flex;align-items:center;justify-content:center;">
                        <i class="far fa-comments fa-2x"></i>
                    </div>
                    <h6 class="fw-bold text-dark mb-1">No messages yet. Start a conversation.</h6>
                    <p class="small text-muted mb-0">Type a message below to send a note.</p>
                </div>
            `;
            return;
        }

        let lastDate = '';


        state.messages.forEach(msg => {
            // Group by Date Divider
            if (msg.date_formatted !== lastDate) {
                lastDate = msg.date_formatted;
                const dateDiv = document.createElement('div');
                dateDiv.className = 'chat-date-divider';
                dateDiv.innerHTML = `<span>${msg.date_formatted}</span>`;
                messagesBodyEl.appendChild(dateDiv);
            }

            const isMe = (msg.sender_id !== state.activeOtherUserId);
            const msgWrapper = document.createElement('div');
            msgWrapper.className = 'chat-msg-row ' + (isMe ? 'msg-outgoing' : 'msg-incoming');
            msgWrapper.dataset.msgId = msg.id;

            let attachmentsHtml = '';
            if (msg.attachments && msg.attachments.length > 0) {
                msg.attachments.forEach(att => {
                    if (att.file_type === 'image') {
                        attachmentsHtml += `
                            <div class="chat-msg-attachment-img mt-1">
                                <a href="${att.file_path}" target="_blank">
                                    <img src="${att.file_path}" alt="${escapeHtml(att.file_name)}" class="rounded img-fluid" style="max-height:220px;object-fit:cover;">
                                </a>
                            </div>
                        `;
                    } else {
                        attachmentsHtml += `
                            <div class="chat-msg-attachment-doc mt-1">
                                <a href="${baseUrl}/chat/download/${att.id}" class="d-flex align-items-center gap-2 p-2 rounded border bg-light text-decoration-none text-dark">
                                    <i class="fas fa-file-pdf text-danger fa-2x"></i>
                                    <div class="min-w-0">
                                        <div class="fw-bold small text-truncate" style="max-width:180px;">${escapeHtml(att.file_name)}</div>
                                        <div class="text-muted" style="font-size:0.68rem;">${att.size_fmt}</div>
                                    </div>
                                    <i class="fas fa-download ms-auto text-primary"></i>
                                </a>
                            </div>
                        `;
                    }
                });
            }

            // Read Receipt Icon (✓ or ✓✓ in blue)
            let statusTicks = '';
            if (isMe) {
                if (msg.is_read) {
                    statusTicks = '<i class="fas fa-check-double text-primary ms-1" title="Seen"></i>';
                } else {
                    statusTicks = '<i class="fas fa-check text-muted ms-1" title="Sent"></i>';
                }
            }

            msgWrapper.innerHTML = `
                <div class="chat-msg-bubble">
                    ${msg.message ? `<div class="chat-msg-text">${escapeHtml(msg.message)}</div>` : ''}
                    ${attachmentsHtml}
                    <div class="chat-msg-time">
                        ${msg.time_formatted} ${statusTicks}
                    </div>
                </div>
            `;

            messagesBodyEl.appendChild(msgWrapper);
        });

        if (forceScroll || wasAtBottom) {
            scrollToBottom(messagesBodyEl);
        }
    }

    // ── 3. Submit Message ─────────────────────────────────────────
    function submitMessage() {
        const text = textareaEl.value.trim();
        if ((!text && !state.selectedFile) || !state.activeConvId) return;

        const formData = new FormData();
        formData.append('conversation_id', state.activeConvId);
        formData.append('message', text);
        formData.append('_csrf', getCsrfToken());

        if (state.selectedFile) {
            formData.append('attachment', state.selectedFile);
        }

        // Disable send button while uploading
        sendBtnEl.disabled = true;

        fetch(baseUrl + '/chat/send', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            sendBtnEl.disabled = false;
            if (data.success) {
                textareaEl.value = '';
                textareaEl.style.height = 'auto';
                state.selectedFile = null;
                fileInputEl.value = '';
                previewStripEl.classList.add('d-none');
                updateInputState();

                loadMessages(state.activeConvId, true);
                loadConversations(false);
            } else if (data.error) {
                alert(data.error);
            }
        })
        .catch(err => {
            sendBtnEl.disabled = false;
            alert('Failed to send message. Please check your connection.');
        });
    }

    // ── 4. Typing Indicator Dispatch ─────────────────────────────
    function triggerTypingIndicator() {
        if (!state.activeOtherUserId) return;

        if (!state.isTypingSent) {
            state.isTypingSent = true;
            sendTypingState(true);
        }

        clearTimeout(state.typingTimeout);
        state.typingTimeout = setTimeout(function () {
            state.isTypingSent = false;
            sendTypingState(false);
        }, 3000);
    }

    function sendTypingState(isTyping) {
        fetch(baseUrl + '/chat/typing', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                target_user_id: state.activeOtherUserId,
                is_typing: isTyping,
                _csrf: getCsrfToken()
            })
        });
    }

    // ── 5. New Chat Modal Contacts ────────────────────────────────
    function loadContacts() {
        fetch(baseUrl + '/chat/contacts', {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                state.contacts = data.contacts;
                renderContacts(data.contacts);
            }
        });
    }

    function renderContacts(list) {
        if (!list || list.length === 0) {
            newChatListEl.innerHTML = `
                <div class="p-4 text-center text-muted">
                    <i class="fas fa-users-slash mb-2" style="font-size:1.5rem"></i>
                    <div class="small">No eligible contacts available to message.</div>
                </div>
            `;
            return;
        }

        newChatListEl.innerHTML = '';
        list.forEach(c => {
            const item = document.createElement('a');
            item.href = '#';
            item.className = 'list-group-item list-group-item-action d-flex align-items-center gap-3 p-3 border-0 rounded-3 mb-1';
            item.innerHTML = `
                <img src="${c.photo || baseUrl + '/assets/images/default-avatar.png'}" class="chat-avatar-md" onerror="this.src='${baseUrl}/assets/images/default-avatar.png'">
                <div class="min-w-0 flex-grow-1">
                    <div class="d-flex align-items-center gap-2">
                        <span class="fw-bold text-dark text-truncate">${escapeHtml(c.name)}</span>
                        <span class="badge bg-light text-dark border flex-shrink-0" style="font-size:0.68rem;">${escapeHtml(c.role_label)}</span>
                    </div>
                    <div class="text-muted small text-truncate" style="font-size:0.75rem;">${escapeHtml(c.detail || '')}</div>
                </div>
                <i class="fas fa-chevron-right text-muted"></i>
            `;

            item.addEventListener('click', function (e) {
                e.preventDefault();
                startNewConversation(c.user_id);
                bootstrap.Modal.getInstance(newChatModalEl)?.hide();
            });

            newChatListEl.appendChild(item);
        });
    }

    function filterContacts() {
        const q = newChatSearchEl.value.trim().toLowerCase();
        const filtered = state.contacts.filter(c => {
            return c.name.toLowerCase().includes(q) || (c.detail && c.detail.toLowerCase().includes(q)) || c.role_label.toLowerCase().includes(q);
        });
        renderContacts(filtered);
    }

    function startNewConversation(targetUserId) {
        fetch(baseUrl + '/chat/start', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ target_user_id: targetUserId, _csrf: getCsrfToken() })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                loadConversations(true);
                openConversation(data.conversation_id);
            } else if (data.error) {
                alert(data.error);
            }
        });
    }

    // ── 6. In-Message Bubbles Filter ─────────────────────────────
    function filterMessageBubbles(query) {
        const rows = messagesBodyEl.querySelectorAll('.chat-msg-row');
        rows.forEach(r => {
            const txt = r.textContent.toLowerCase();
            r.style.display = (query === '' || txt.includes(query)) ? '' : 'none';
        });
    }

    // ── 7. Helpers & Utilities ───────────────────────────────────
    function updateInputState() {
        const hasText = textareaEl.value.trim().length > 0;
        sendBtnEl.disabled = (!hasText && !state.selectedFile);

        // Auto-resize textarea
        textareaEl.style.height = 'auto';
        textareaEl.style.height = Math.min(textareaEl.scrollHeight, 120) + 'px';
    }

    function isScrolledToBottom(el) {
        return el.scrollHeight - el.clientHeight <= el.scrollTop + 50;
    }

    function scrollToBottom(el) {
        el.scrollTop = el.scrollHeight;
    }

    function triggerDesktopNotification(title, body) {
        if ('Notification' in window && Notification.permission === 'granted') {
            new Notification(title, {
                body: body,
                icon: baseUrl + '/assets/images/logo.png',
            });
        }
    }

    function formatConvTime(timeStr) {
        const date = new Date(timeStr);
        const now  = new Date();

        if (date.toDateString() === now.toDateString()) {
            return date.toLocaleTimeString('en-IN', { hour: '2-digit', minute: '2-digit' });
        }
        return date.toLocaleDateString('en-IN', { day: '2-digit', month: 'short' });
    }

    function formatBytes(bytes) {
        if (bytes >= 1048576) return (bytes / 1048576).toFixed(1) + ' MB';
        if (bytes >= 1024) return (bytes / 1024).toFixed(0) + ' KB';
        return bytes + ' B';
    }

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

})();
