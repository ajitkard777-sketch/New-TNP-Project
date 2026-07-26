<?php require_once ROOT_PATH . '/includes/header.php'; ?>
<div class="content-header"><div><h1 class="page-title">System Settings</h1><p class="subtitle">Configure system preferences</p></div></div>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card"><div class="card-header"><h6><i class="fas fa-info-circle me-2 text-primary"></i>System Information</h6></div>
        <div class="card-body">
            <div class="row g-2" style="font-size:0.9rem">
                <div class="col-6"><strong>App Name:</strong></div><div class="col-6"><?= APP_NAME ?></div>
                <div class="col-6"><strong>Version:</strong></div><div class="col-6"><?= APP_VERSION ?></div>
                <div class="col-6"><strong>PHP Version:</strong></div><div class="col-6"><?= PHP_VERSION ?></div>
                <div class="col-6"><strong>Server:</strong></div><div class="col-6"><?= $_SERVER['SERVER_SOFTWARE'] ?? 'N/A' ?></div>
                <div class="col-6"><strong>Database:</strong></div><div class="col-6">MySQL (<?= DB_NAME ?>)</div>
                <div class="col-6"><strong>Upload Limit:</strong></div><div class="col-6"><?= ini_get('upload_max_filesize') ?></div>
                <div class="col-6"><strong>Max File Size:</strong></div><div class="col-6"><?= formatFileSize(MAX_FILE_SIZE) ?></div>
            </div>
        </div></div>
    </div>
    <div class="col-lg-6">
        <div class="card"><div class="card-header"><h6><i class="fas fa-database me-2 text-primary"></i>Database Stats</h6></div>
        <div class="card-body">
            <?php
            $tables = ['users', 'students', 'companies', 'jobs', 'applications', 'placements', 'trainings', 'notifications', 'activity_logs'];
            foreach ($tables as $t):
                $count = (int)$this->db->fetchColumn("SELECT COUNT(*) FROM `$t`");
            ?>
            <div class="d-flex justify-content-between align-items-center mb-2 p-2 rounded" style="background:var(--gray-50)">
                <span style="font-size:0.85rem" class="fw-medium"><?= ucfirst($t) ?></span>
                <span class="badge bg-primary"><?= number_format($count) ?></span>
            </div>
            <?php endforeach; ?>
        </div></div>
    </div>
</div>
<?php require_once ROOT_PATH . '/includes/footer.php'; ?>
