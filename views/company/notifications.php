<?php require_once ROOT_PATH . '/includes/header.php'; ?>
<div class="content-header">
    <div>
        <h1 class="page-title">Notifications</h1>
        <p class="subtitle">System announcements and recruitment updates</p>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <?php if (empty($notifications)): ?>
        <div class="empty-state py-5 text-center">
            <i class="fas fa-bell-slash text-muted" style="font-size:3rem; opacity:0.5;"></i>
            <h5 class="mt-3 fw-bold">No Notifications</h5>
            <p class="text-muted">You will receive updates here regarding job approvals, applications, and announcements.</p>
        </div>
        <?php else: ?>
        <div class="list-group list-group-flush">
            <?php foreach ($notifications as $notif): ?>
            <div class="list-group-item p-3 border-bottom">
                <div class="d-flex align-items-start gap-3">
                    <div class="n-icon bg-<?= $notif['type'] === 'success' ? 'success' : ($notif['type'] === 'warning' ? 'warning' : ($notif['type'] === 'danger' ? 'danger' : 'primary')) ?>-soft p-2 rounded-circle">
                        <i class="fas fa-<?= $notif['type'] === 'success' ? 'check-circle text-success' : ($notif['type'] === 'warning' ? 'exclamation-triangle text-warning' : ($notif['type'] === 'announcement' ? 'bullhorn text-info' : 'info-circle text-primary')) ?>"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <h6 class="fw-bold mb-0"><?= htmlspecialchars($notif['title']) ?></h6>
                            <small class="text-muted"><i class="far fa-clock me-1"></i><?= timeAgo($notif['created_at']) ?></small>
                        </div>
                        <p class="text-muted small mb-0"><?= htmlspecialchars($notif['message']) ?></p>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php require_once ROOT_PATH . '/includes/footer.php'; ?>
