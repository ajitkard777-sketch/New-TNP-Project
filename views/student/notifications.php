<?php require_once ROOT_PATH . '/includes/header.php'; ?>
<div class="content-header">
    <div>
        <h1 class="page-title">Notifications</h1>
        <p class="subtitle">Stay updated with the latest alerts</p>
    </div>
    <a href="javascript:void(0)" onclick="TPMS.markAllNotificationsRead()" class="btn btn-outline-primary btn-sm">
        <i class="fas fa-check-double me-1"></i> Mark All Read
    </a>
</div>

<?php if (empty($notifications)): ?>
<div class="card">
    <div class="card-body empty-state">
        <i class="fas fa-bell-slash"></i>
        <h5>No Notifications</h5>
        <p>You're all caught up!</p>
    </div>
</div>
<?php else: ?>
<div class="card">
    <div class="card-body p-0">
        <?php foreach ($notifications as $n): ?>
        <?php 
            $targetUrl = getNotificationUrl($n, 'student'); 
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
                    <i class="fas fa-<?= $n['type'] === 'success' ? 'check-circle text-success' : ($n['type'] === 'warning' ? 'exclamation-triangle text-warning' : ($n['type'] === 'announcement' ? 'bullhorn text-info' : 'info-circle text-primary')) ?>"></i>
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
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>
<?php require_once ROOT_PATH . '/includes/footer.php'; ?>
