<?php require_once ROOT_PATH . '/includes/header.php'; ?>
<div class="content-header">
    <div>
        <h1 class="page-title">Notifications</h1>
        <p class="subtitle">Send and manage notifications</p>
    </div>
    <div class="d-flex gap-2">
        <a href="javascript:void(0)" onclick="TPMS.markAllNotificationsRead()" class="btn btn-outline-primary btn-sm">
            <i class="fas fa-check-double me-1"></i> Mark All Read
        </a>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#sendNotifModal">
            <i class="fas fa-bullhorn me-1"></i> Send Notification
        </button>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <?php if (empty($notifications)): ?>
        <div class="empty-state py-4">
            <i class="fas fa-bell-slash"></i>
            <p><small>No notifications.</small></p>
        </div>
        <?php else: foreach ($notifications as $n): ?>
        <?php 
            $targetUrl = getNotificationUrl($n, 'admin'); 
            $nData = [
                'id'        => (int)$n['id'],
                'title'     => $n['title'],
                'message'   => $n['message'],
                'type'      => $n['type'] ?? 'info',
                'category'  => $n['category'] ?? 'system',
                'time_ago'  => timeAgo($n['created_at']),
                'is_read'   => (int)($n['is_read'] ?? 0),
                'is_global' => (int)($n['is_global'] ?? 0),
                'link'      => $targetUrl
            ];
            $jsonNotif = htmlspecialchars(json_encode($nData), ENT_QUOTES, 'UTF-8');
        ?>
        <div class="notification-item <?= !$n['is_read'] && !$n['is_global'] ? 'unread' : '' ?>" data-id="<?= $n['id'] ?>">
            <a href="javascript:void(0)" class="notification-item-link" onclick='TPMS.openNotificationFullView(<?= $jsonNotif ?>, event)'>
                <div class="n-icon bg-<?= $n['type'] === 'success' ? 'success' : ($n['type'] === 'warning' ? 'warning' : ($n['type'] === 'danger' ? 'danger' : 'primary')) ?>-soft">
                    <i class="fas fa-<?= $n['type'] === 'announcement' ? 'bullhorn' : ($n['type'] === 'success' ? 'check-circle' : 'bell') ?> text-<?= $n['type'] === 'success' ? 'success' : ($n['type'] === 'warning' ? 'warning' : 'primary') ?>"></i>
                </div>
                <div class="n-content">
                    <div class="n-title"><?= htmlspecialchars($n['title']) ?></div>
                    <div class="n-text" style="white-space:normal"><?= htmlspecialchars($n['message']) ?></div>
                    <div class="n-time">
                        <i class="far fa-clock me-1"></i><?= timeAgo($n['created_at']) ?> 
                        <?php if (!empty($n['is_global'])): ?>
                            <span class="badge bg-info ms-1" style="font-size:0.6rem">Global</span>
                        <?php endif; ?>
                    </div>
                </div>
            </a>
            <?php if (!$n['is_read'] && !$n['is_global']): ?>
            <button class="notif-mark-read-btn" title="Mark as read"
                onclick="TPMS.markNotificationRead(<?= $n['id'] ?>, null, true); this.closest('.notification-item').classList.remove('unread'); this.remove();">
                <i class="fas fa-check"></i>
            </button>
            <?php endif; ?>
        </div>
        <?php endforeach; endif; ?>
    </div>
</div>

<!-- Send Notification Modal -->
<div class="modal fade" id="sendNotifModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Send Notification</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= url('/admin/send-notification') ?>" method="POST"><?= CsrfMiddleware::tokenField() ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Target *</label>
                        <select class="form-select" name="target">
                            <option value="all">All Users (Global)</option>
                            <option value="students">All Students</option>
                            <option value="companies">All Companies</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Title *</label>
                        <input type="text" class="form-control" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Message *</label>
                        <textarea class="form-control" name="message" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Type</label>
                        <select class="form-select" name="type">
                            <option value="info">Info</option>
                            <option value="success">Success</option>
                            <option value="warning">Warning</option>
                            <option value="announcement">Announcement</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane me-1"></i>Send</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php require_once ROOT_PATH . '/includes/footer.php'; ?>
