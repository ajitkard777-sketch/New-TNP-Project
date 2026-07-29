<?php require_once ROOT_PATH . '/includes/header.php'; ?>

<div class="chat-page-wrapper">

    <!-- Header bar -->
    <div class="content-header mb-2 d-flex align-items-center justify-content-between flex-wrap gap-2" style="min-height:0; padding-bottom:8px;">
        <div>
            <h1 class="page-title" style="font-size:1.2rem;"><i class="fas fa-comments text-primary me-2"></i>Messages &amp; Communication</h1>
            <p class="subtitle" style="font-size:0.8rem; margin-bottom:0;">Real-time messaging with Companies, Placement Officers, and Applicants</p>
        </div>
        <div>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#newChatModal">
                <i class="fas fa-plus-circle me-1"></i> New Message
            </button>
        </div>
    </div>

    <!-- Main Dual Panel Chat Container -->
    <div class="chat-app-card">

        <!-- 1. LEFT PANEL: Conversations & Contacts Sidebar -->
        <div class="chat-sidebar-panel">

            <!-- Search Bar -->
            <div class="chat-search-wrapper">
                <i class="fas fa-search chat-search-icon"></i>
                <input type="text" id="chatSearchInput" class="chat-search-input" placeholder="Search messages or contacts..." autocomplete="off">
            </div>

            <!-- Filter Tabs -->
            <div class="chat-filter-tabs">
                <button class="chat-filter-btn active" data-filter="all">All</button>
                <button class="chat-filter-btn" data-filter="unread">Unread <span id="unreadFilterBadge" class="badge bg-danger ms-1 d-none">0</span></button>
                <button class="chat-filter-btn" data-filter="archived">Archived</button>
            </div>

            <!-- Conversations List -->
            <div id="chatConversationList" class="chat-conversations-list">
                <div class="p-4 text-center text-muted">
                    <div class="spinner-border spinner-border-sm text-primary mb-2"></div>
                    <div class="small">Loading conversations...</div>
                </div>
            </div>
        </div>

        <!-- 2. RIGHT PANEL: Active Chat Window -->
        <div class="chat-main-window">

            <!-- Empty State (When no conversation is selected) -->
            <div id="chatEmptyState" class="chat-empty-state">
                <div class="chat-empty-content text-center">
                    <div class="chat-empty-icon mb-3">
                        <i class="fas fa-paper-plane text-primary"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-2">Your Messages</h5>
                    <p class="text-muted small mb-4" style="max-width: 340px;">
                        Select an existing conversation from the list or start a new chat with Placement Cell or Recruiters.
                    </p>
                    <button class="btn btn-primary btn-sm px-4" data-bs-toggle="modal" data-bs-target="#newChatModal">
                        <i class="fas fa-edit me-1"></i> Start New Conversation
                    </button>
                </div>
            </div>

            <!-- Active Chat Interface -->
            <div id="chatActiveContainer" class="chat-active-container d-none">

                <!-- Chat Header -->
                <div class="chat-header-bar">
                    <div class="d-flex align-items-center gap-3 min-w-0">
                        <button class="btn btn-sm btn-light border d-md-none" id="chatBackToList" title="Back to conversations">
                            <i class="fas fa-arrow-left"></i>
                        </button>

                        <div class="position-relative flex-shrink-0">
                            <img id="chatRecipientAvatar" src="<?= asset('images/default-avatar.png') ?>" alt="" class="chat-avatar-lg">
                            <span id="chatRecipientOnlineDot" class="chat-online-dot d-none"></span>
                        </div>

                        <div class="min-w-0 flex-grow-1">
                            <div class="d-flex align-items-center gap-2">
                                <h6 id="chatRecipientName" class="fw-bold mb-0 text-truncate text-dark" style="font-size: 0.95rem;">-</h6>
                                <span id="chatRecipientRoleBadge" class="badge bg-light text-dark border flex-shrink-0" style="font-size:0.68rem;">-</span>
                            </div>
                            <div id="chatRecipientStatusText" class="text-muted small text-truncate" style="font-size: 0.76rem;">-</div>
                        </div>
                    </div>

                    <!-- Right Action Menu -->
                    <div class="d-flex align-items-center gap-1">
                        <button id="chatToggleSearchInMsg" class="chat-icon-action-btn" title="Search in message history">
                            <i class="fas fa-search"></i>
                        </button>
                        <div class="dropdown">
                            <button class="chat-icon-action-btn" data-bs-toggle="dropdown" title="Conversation Options">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                <li>
                                    <button id="chatToggleArchiveBtn" class="dropdown-item">
                                        <i class="fas fa-archive me-2 text-warning"></i><span id="archiveBtnText">Archive Conversation</span>
                                    </button>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <button id="chatDeleteConvBtn" class="dropdown-item text-danger">
                                        <i class="fas fa-trash-alt me-2"></i>Delete Conversation
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- In-Chat Search Bar (Collapsible) -->
                <div id="chatInMsgSearchBox" class="chat-in-msg-search p-2 border-bottom bg-light d-none">
                    <div class="input-group input-group-sm">
                        <input type="text" id="inMsgSearchInput" class="form-control" placeholder="Search in this conversation...">
                        <button class="btn btn-outline-secondary" id="closeInMsgSearch"><i class="fas fa-times"></i></button>
                    </div>
                </div>

                <!-- Message Scroll Area -->
                <div id="chatMessagesBody" class="chat-messages-area">
                    <!-- Dynamic message bubbles injected by JS -->
                </div>

                <!-- Live Typing Banner -->
                <div id="chatTypingBanner" class="chat-typing-banner d-none">
                    <div class="chat-typing-dots"><span></span><span></span><span></span></div>
                    <span id="chatTypingText" class="small text-muted ms-2">Recipient is typing...</span>
                </div>

                <!-- Attachment Preview Strip -->
                <div id="chatAttachmentPreview" class="chat-attachment-preview d-none">
                    <div class="d-flex align-items-center justify-content-between p-2 rounded border bg-light">
                        <div class="d-flex align-items-center gap-2 min-w-0">
                            <i class="fas fa-file-alt text-primary flex-shrink-0"></i>
                            <span id="chatAttachmentFileName" class="small text-truncate fw-medium">filename.pdf</span>
                            <span id="chatAttachmentFileSize" class="small text-muted flex-shrink-0">(0 KB)</span>
                        </div>
                        <button id="removeAttachmentBtn" class="btn btn-sm btn-link text-danger p-0 ms-2"><i class="fas fa-times"></i></button>
                    </div>
                </div>

                <!-- Input Footer -->
                <div class="chat-input-footer">
                    <!-- Hidden File Input -->
                    <input type="file" id="chatFileInput" class="d-none" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.gif,.webp,.zip,.txt">

                    <form id="chatMessageForm" class="d-flex align-items-end gap-2 w-100" enctype="multipart/form-data">
                        <button type="button" id="chatAttachBtn" class="chat-input-action-btn" title="Attach file (PDF, Doc, Image)">
                            <i class="fas fa-paperclip"></i>
                        </button>

                        <div class="position-relative flex-grow-1">
                            <button type="button" id="chatEmojiBtn" class="chat-emoji-trigger" title="Add emoji">
                                <i class="far fa-smile"></i>
                            </button>

                            <!-- Simple Quick Emoji Picker Dropdown -->
                            <div id="chatEmojiPicker" class="chat-emoji-picker shadow-lg d-none">
                                <div class="chat-emoji-grid">
                                    <span>👍</span><span>❤️</span><span>😊</span><span>🎉</span><span>🙏</span><span>👏</span>
                                    <span>🔥</span><span>✅</span><span>🙌</span><span>💡</span><span>💼</span><span>🎓</span>
                                    <span>⭐</span><span>🚀</span><span>📄</span><span>👀</span><span>🤝</span><span>😃</span>
                                </div>
                            </div>

                            <textarea id="chatMessageTextarea" class="chat-textarea" placeholder="Type a message... (Press Enter to send, Shift+Enter for newline)" rows="1" maxlength="2000"></textarea>
                        </div>

                        <button type="submit" id="chatSendBtn" class="chat-send-btn" title="Send Message" disabled>
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- =====================================================
     NEW CHAT MODAL (Start Conversation)
     ===================================================== -->
<div class="modal fade" id="newChatModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-bold"><i class="fas fa-user-plus text-primary me-2"></i>Start New Conversation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3">
                <div class="input-group input-group-sm mb-3">
                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" id="newChatContactSearch" class="form-control border-start-0" placeholder="Search contacts by name or role...">
                </div>

                <div id="newChatContactsList" class="list-group list-group-flush" style="max-height: 340px; overflow-y: auto;">
                    <div class="p-4 text-center text-muted">
                        <div class="spinner-border spinner-border-sm text-primary mb-2"></div>
                        <div class="small">Loading eligible contacts...</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>
