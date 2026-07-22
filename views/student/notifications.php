<?php require_once ROOT_PATH . '/includes/header.php'; ?>
<div class="content-header"><div><h1 class="page-title">Notifications</h1><p class="subtitle">Stay updated with the latest alerts</p></div>
<a href="javascript:void(0)" onclick="$.post(TPMS.baseUrl+'/notifications/mark-all-read',{csrf_token:TPMS.csrfToken},()=>location.reload())" class="btn btn-outline-primary btn-sm"><i class="fas fa-check-double me-1"></i> Mark All Read</a>
</div>
<?php if (empty($notifications)): ?>
<div class="card"><div class="card-body empty-state"><i class="fas fa-bell-slash"></i><h5>No Notifications</h5><p>You're all caught up!</p></div></div>
<?php else: ?>
<div class="card"><div class="card-body p-0">
    <?php foreach ($notifications as $n): ?>
    <div class="notification-item <?= !$n['is_read'] && !$n['is_global'] ? 'unread' : '' ?>" onclick="<?= !$n['is_read'] ? "$.post(TPMS.baseUrl+'/notifications/mark-read/{$n['id']}',{csrf_token:TPMS.csrfToken})" : '' ?>">
        <div class="n-icon bg-<?= $n['type'] === 'success' ? 'success' : ($n['type'] === 'warning' ? 'warning' : ($n['type'] === 'danger' ? 'danger' : 'primary')) ?>-soft">
            <i class="fas fa-<?= $n['type'] === 'success' ? 'check-circle text-success' : ($n['type'] === 'warning' ? 'exclamation-triangle text-warning' : ($n['type'] === 'announcement' ? 'bullhorn text-info' : 'info-circle text-primary')) ?>"></i>
        </div>
        <div class="n-content"><div class="n-title"><?= htmlspecialchars($n['title']) ?></div><div class="n-text" style="white-space:normal"><?= htmlspecialchars($n['message']) ?></div><div class="n-time"><i class="far fa-clock me-1"></i><?= timeAgo($n['created_at']) ?> <?php if ($n['is_global']): ?><span class="badge bg-info ms-1" style="font-size:0.6rem">Global</span><?php endif; ?></div></div>
    </div>
    <?php endforeach; ?>
</div></div>
<?php endif; ?>
<?php require_once ROOT_PATH . '/includes/footer.php'; ?>
