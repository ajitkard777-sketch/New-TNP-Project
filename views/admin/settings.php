<?php require_once ROOT_PATH . '/includes/header.php'; ?>
<div class="content-header">
    <div><h1 class="page-title">System Settings</h1><p class="subtitle">Configure system preferences</p></div>
</div>

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

<!-- ============================================================
     Email Configuration Card
     ============================================================ -->
<div class="card mt-4">
    <div class="card-header d-flex align-items-center justify-content-between">
        <h6 class="mb-0"><i class="fas fa-envelope me-2 text-primary"></i>Email Configuration — Brevo SMTP</h6>
        <?php
        $connBadge = $smtpStatus['connection_ok']
            ? '<span class="badge" style="background:#d1fae5;color:#065f46;font-size:0.78rem;"><i class="fas fa-circle me-1" style="font-size:0.5rem;"></i>Connected</span>'
            : '<span class="badge" style="background:#fee2e2;color:#991b1b;font-size:0.78rem;"><i class="fas fa-circle me-1" style="font-size:0.5rem;"></i>Unreachable</span>';
        echo $connBadge;
        ?>
    </div>
    <div class="card-body">

        <div class="row g-4">
            <!-- Left: SMTP Details -->
            <div class="col-lg-6">
                <h6 class="fw-bold mb-3" style="font-size:0.82rem;text-transform:uppercase;letter-spacing:0.5px;color:var(--text-muted);">
                    <i class="fas fa-server me-1"></i> SMTP Configuration
                </h6>
                <table class="table table-sm mb-0" style="font-size:0.875rem;">
                    <tbody>
                        <tr>
                            <td class="fw-semibold text-muted" style="width:140px;">Provider</td>
                            <td><strong><?= htmlspecialchars($smtpStatus['provider']) ?></strong>
                                <a href="https://app.brevo.com" target="_blank" class="ms-2 text-muted" style="font-size:0.75rem;">(Dashboard →)</a>
                            </td>
                        </tr>
                        <tr>
                            <td class="fw-semibold text-muted">Host</td>
                            <td><code style="font-size:0.82rem;"><?= htmlspecialchars($smtpStatus['host']) ?></code></td>
                        </tr>
                        <tr>
                            <td class="fw-semibold text-muted">Port</td>
                            <td><?= (int)$smtpStatus['port'] ?></td>
                        </tr>
                        <tr>
                            <td class="fw-semibold text-muted">Encryption</td>
                            <td><?= htmlspecialchars($smtpStatus['encryption']) ?></td>
                        </tr>
                        <tr>
                            <td class="fw-semibold text-muted">Username</td>
                            <td><?= htmlspecialchars($smtpStatus['username']) ?></td>
                        </tr>
                        <tr>
                            <td class="fw-semibold text-muted">From Address</td>
                            <td><?= htmlspecialchars($smtpStatus['from_email']) ?></td>
                        </tr>
                        <tr>
                            <td class="fw-semibold text-muted">From Name</td>
                            <td><?= htmlspecialchars($smtpStatus['from_name']) ?></td>
                        </tr>
                        <tr>
                            <td class="fw-semibold text-muted">Credentials</td>
                            <td>
                                <?php if ($smtpStatus['configured']): ?>
                                    <span style="color:#16a34a;font-weight:600;"><i class="fas fa-check-circle me-1"></i>Configured</span>
                                <?php else: ?>
                                    <span style="color:#dc2626;font-weight:600;"><i class="fas fa-times-circle me-1"></i>Missing — check .env file</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="fw-semibold text-muted">Connection</td>
                            <td>
                                <?php if ($smtpStatus['connection_ok']): ?>
                                    <span style="color:#16a34a;font-weight:600;"><i class="fas fa-wifi me-1"></i>Port <?= (int)$smtpStatus['port'] ?> reachable</span>
                                <?php else: ?>
                                    <span style="color:#dc2626;font-weight:600;"><i class="fas fa-exclamation-triangle me-1"></i>Port <?= (int)$smtpStatus['port'] ?> unreachable</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Right: Status + Test -->
            <div class="col-lg-6">
                <h6 class="fw-bold mb-3" style="font-size:0.82rem;text-transform:uppercase;letter-spacing:0.5px;color:var(--text-muted);">
                    <i class="fas fa-history me-1"></i> Email Activity
                </h6>

                <!-- Last Email Sent -->
                <div class="p-3 rounded mb-3" style="background:var(--gray-50);border:1px solid var(--border-color);">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="fas fa-paper-plane text-primary"></i>
                        <span class="fw-bold" style="font-size:0.85rem;">Last Email Sent</span>
                    </div>
                    <?php if ($smtpStatus['last_sent_at']): ?>
                        <div style="font-size:0.85rem;">
                            <div><span class="text-muted fw-medium">Time: </span><?= htmlspecialchars($smtpStatus['last_sent_at']) ?></div>
                            <div><span class="text-muted fw-medium">To: </span><?= htmlspecialchars($smtpStatus['last_sent_to']) ?></div>
                            <div class="text-truncate"><span class="text-muted fw-medium">Subject: </span><?= htmlspecialchars($smtpStatus['last_sent_subject']) ?></div>
                        </div>
                    <?php else: ?>
                        <p class="mb-0 text-muted" style="font-size:0.84rem;">No emails sent since system start.</p>
                    <?php endif; ?>
                </div>

                <!-- Recent Email Log -->
                <?php
                $emailLog = LOGS_PATH . '/email.log';
                $recentLines = [];
                if (file_exists($emailLog)) {
                    $lines = file($emailLog, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                    $recentLines = array_slice(array_reverse($lines), 0, 5);
                }
                ?>
                <div class="mb-3">
                    <div class="fw-bold mb-2" style="font-size:0.82rem;text-transform:uppercase;letter-spacing:0.5px;color:var(--text-muted);">
                        <i class="fas fa-list me-1"></i> Recent Email Log
                    </div>
                    <?php if ($recentLines): ?>
                        <div style="background:#0f172a;border-radius:8px;padding:12px;max-height:180px;overflow-y:auto;">
                            <?php foreach ($recentLines as $line): ?>
                                <?php
                                $lineClass = str_contains($line, '[SUCCESS]') ? '#4ade80'
                                    : (str_contains($line, '[ERROR]')   ? '#f87171'
                                    : (str_contains($line, '[WARN]')    ? '#fbbf24' : '#94a3b8'));
                                ?>
                                <div style="font-size:0.72rem;font-family:'Courier New',monospace;color:<?= $lineClass ?>;margin-bottom:4px;word-break:break-all;">
                                    <?= htmlspecialchars($line) ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="text-end mt-1">
                            <a href="<?= url('/admin/logs') ?>" class="text-primary text-decoration-none" style="font-size:0.78rem;">View full log →</a>
                        </div>
                    <?php else: ?>
                        <p class="text-muted mb-0" style="font-size:0.84rem;">No email log entries found.</p>
                    <?php endif; ?>
                </div>

                <!-- Send Test Email Button -->
                <div class="mt-3 pt-3" style="border-top:1px solid var(--border-color);">
                    <h6 class="fw-bold mb-2" style="font-size:0.82rem;text-transform:uppercase;letter-spacing:0.5px;color:var(--text-muted);">
                        <i class="fas fa-vial me-1"></i> Send Test Email
                    </h6>
                    <p class="text-muted mb-3" style="font-size:0.82rem;">
                        Sends a test email to your admin account (<strong><?= htmlspecialchars($_SESSION['user_email'] ?? 'N/A') ?></strong>) to confirm Brevo SMTP is working.
                    </p>
                    <button type="button" class="btn btn-primary btn-sm" id="btnSendTestEmail"
                            <?= $smtpStatus['configured'] ? '' : 'disabled title="SMTP not configured"' ?>>
                        <i class="fas fa-paper-plane me-2"></i>Send Test Email
                    </button>
                    <div id="testEmailResult" class="mt-3" style="display:none;"></div>
                </div>
            </div>
        </div>

        <!-- Info note -->
        <div class="mt-4 p-3 rounded" style="background:rgba(99,102,241,0.06);border:1px solid rgba(99,102,241,0.18);font-size:0.82rem;color:var(--text-muted);">
            <i class="fas fa-info-circle me-2 text-primary"></i>
            To update SMTP settings, edit the <code>.env</code> file in the project root and restart the server.
            Brevo free plan: <strong>300 emails/day</strong>. Upgrade at
            <a href="https://app.brevo.com/plan" target="_blank" class="text-primary">app.brevo.com/plan</a>.
        </div>

    </div>
</div>

<script>
(function() {
    const btn = document.getElementById('btnSendTestEmail');
    const result = document.getElementById('testEmailResult');
    if (!btn) return;

    btn.addEventListener('click', async function() {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Sending…';
        result.style.display = 'none';

        try {
            const formData = new FormData();
            formData.append('csrf_token', '<?= CsrfMiddleware::getToken() ?>');

            const resp = await fetch('<?= url('/admin/send-test-email') ?>', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            });
            const data = await resp.json();

            result.style.display = 'block';
            if (data.success) {
                result.innerHTML = '<div class="alert alert-success py-2 mb-0"><i class="fas fa-check-circle me-2"></i>' + escHtml(data.message) + '</div>';
                btn.innerHTML = '<i class="fas fa-check me-2"></i>Sent!';
            } else {
                result.innerHTML = '<div class="alert alert-danger py-2 mb-0"><i class="fas fa-times-circle me-2"></i>' + escHtml(data.message) + '</div>';
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-paper-plane me-2"></i>Send Test Email';
            }
        } catch (e) {
            result.style.display = 'block';
            result.innerHTML = '<div class="alert alert-danger py-2 mb-0"><i class="fas fa-times-circle me-2"></i>Network error. Please try again.</div>';
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-paper-plane me-2"></i>Send Test Email';
        }
    });

    function escHtml(str) {
        return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }
})();
</script>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>
