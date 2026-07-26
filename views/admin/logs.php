<?php require_once ROOT_PATH . '/includes/header.php'; ?>
<div class="content-header"><div><h1 class="page-title">Activity Logs</h1><p class="subtitle">System activity audit trail</p></div></div>
<div class="card"><div class="card-body p-0"><div class="table-responsive">
    <table class="table mb-0"><thead><tr><th>User</th><th>Role</th><th>Action</th><th>Module</th><th>Description</th><th>IP</th><th>Time</th></tr></thead><tbody>
        <?php foreach ($logs as $l): ?>
        <tr><td><small><?= htmlspecialchars($l['email'] ?? 'System') ?></small></td><td><span class="badge bg-light text-dark"><?= ucfirst($l['role'] ?? 'N/A') ?></span></td>
        <td><span class="badge bg-primary"><?= htmlspecialchars($l['action']) ?></span></td><td><small><?= htmlspecialchars($l['module'] ?? '') ?></small></td>
        <td><small><?= htmlspecialchars($l['description'] ?? '') ?></small></td><td><small class="text-muted"><?= htmlspecialchars($l['ip_address'] ?? '') ?></small></td>
        <td><small class="text-muted"><?= timeAgo($l['created_at']) ?></small></td></tr>
        <?php endforeach; ?>
    </tbody></table>
</div></div></div>
<div class="mt-4"><?= renderPagination($pagination, url('/admin/logs')) ?></div>
<?php require_once ROOT_PATH . '/includes/footer.php'; ?>
