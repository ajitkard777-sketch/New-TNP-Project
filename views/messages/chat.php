<?php
/**
 * TPMS - Real-Time Messages & Chat View
 */
$extraCss = ['chat.css'];
require_once ROOT_PATH . '/includes/header.php';
?>

<div class="content-header mb-3">
    <div>
        <h1 class="page-title"><i class="fas fa-comments text-primary me-2"></i>Messages &amp; Chat</h1>
        <p class="subtitle">Direct real-time communication between Students and Recruiters</p>
    </div>
</div>

<div class="chat-container" id="chatContainer" data-partner-id="<?= (int)($partnerId ?? 0) ?>">

    <!-- ════════════════════ LEFT SIDEBAR ════════════════════ -->
    <div class="chat-sidebar" id="chatSidebar">
        <div class="chat-sidebar-header">
            <div class="chat-sidebar-title">
                <span>Conversations</span>
                <span class="badge bg-primary rounded-pill chat-unread-badge" style="display:none">0</span>
            </div>
            <div class="chat-search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="chatSearchInput" placeholder="Search conversations..." autocomplete="off">
            </div>
        </div>

        <div class="conversation-list" id="conversationList">
            <div class="p-4 text-center text-muted">
                <div class="spinner-border spinner-border-sm text-primary mb-2"></div>
                <div>Loading conversations...</div>
            </div>
        </div>
    </div>

    <!-- ════════════════════ RIGHT CHAT PANEL ════════════════════ -->
    <div class="chat-main">
        <!-- Initial Empty State -->
        <div class="chat-empty-state" id="chatEmptyState" style="<?= ($partnerId > 0) ? 'display:none' : 'display:block' ?>">
            <i class="fas fa-comments text-primary" style="font-size: 3.5rem; opacity: 0.6; margin-bottom: 1rem;"></i>
            <h5 class="fw-bold">Real-Time Messaging</h5>
            <p class="text-muted max-w-md mx-auto mb-4" style="max-width: 420px; font-size: 0.92rem;">
                Direct real-time communication is active between <strong>Students</strong> and <strong>Company Recruiters</strong>.
            </p>
            <?php if (getCurrentUserRole() === 'student'): ?>
                <a href="<?= url('/student/companies') ?>" class="btn btn-primary btn-sm px-3 shadow-sm">
                    <i class="fas fa-building me-1"></i> Browse Companies &amp; Chat HR
                </a>
            <?php elseif (getCurrentUserRole() === 'company'): ?>
                <a href="<?= url('/company/jobs') ?>" class="btn btn-primary btn-sm px-3 shadow-sm">
                    <i class="fas fa-users me-1"></i> View Applications &amp; Message Applicants
                </a>
            <?php endif; ?>
        </div>

        <!-- Active Chat Main Viewport -->
        <div id="chatMainPanel" style="<?= ($partnerId > 0) ? 'display:flex; flex-direction:column; height:100%;' : 'display:none' ?>">
            <!-- Chat Header -->
            <div class="chat-header">
                <div class="chat-header-user">
                    <button class="mobile-sidebar-btn" id="mobileSidebarToggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <img src="<?= asset('images/default-avatar.png') ?>" id="chatHeaderAvatar" class="chat-header-avatar" alt="">
                    <div>
                        <h6 class="chat-header-name" id="chatHeaderName">Loading...</h6>
                        <div class="chat-header-status" id="chatHeaderStatus">
                            <i class="fas fa-circle" style="font-size:0.6rem"></i> Connecting...
                        </div>
                    </div>
                </div>
            </div>

            <!-- Messages Stream Area -->
            <div class="chat-messages" id="chatMessages">
                <div class="text-center text-muted p-4">
                    <div class="spinner-border spinner-border-sm text-primary me-2"></div>
                    Loading messages...
                </div>
            </div>

            <!-- Footer & Controls -->
            <div class="chat-footer">
                <!-- File Attachment Preview Bar -->
                <div class="chat-attachment-preview" id="chatAttachmentPreview" style="display:none">
                    <i class="fas fa-paperclip text-primary"></i>
                    <span id="chatAttachmentName">filename.pdf</span>
                    <button type="button" class="btn-close ms-2" id="chatRemoveFileBtn" style="font-size:0.75rem"></button>
                </div>

                <form id="chatSendForm" class="d-flex align-items-center gap-2">
                    <input type="file" id="chatFileInput" class="d-none" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.gif,.webp,.txt,.zip">
                    
                    <button type="button" class="chat-btn-icon" id="chatAttachBtn" title="Attach Document or Image">
                        <i class="fas fa-paperclip"></i>
                    </button>

                    <div class="chat-input-wrapper flex-grow-1">
                        <textarea id="chatMsgInput" class="chat-input" rows="1" placeholder="Type your message..." autocomplete="off"></textarea>
                    </div>

                    <button type="submit" class="chat-btn-send" id="chatSendBtn" title="Send Message">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>

</div>

<script src="<?= asset('js/chat.js') ?>"></script>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>
