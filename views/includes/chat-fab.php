<?php
/**
 * TPMS - Floating Chat FAB Button
 * Renders a persistent floating chat button at the bottom-right of all dashboard pages.
 */
if (!AuthMiddleware::isLoggedIn()) return;
?>
<!-- Floating Chat FAB Button -->
<div class="chat-fab-wrapper">
    <a href="<?= url('/chat') ?>" class="chat-fab-btn" id="chatFabBtn" title="Chat & Messages">
        <i class="fas fa-comment-dots"></i>
        <span class="chat-fab-badge" id="chatFabUnreadBadge" style="display:none">0</span>
    </a>
</div>
