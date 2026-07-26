<?php
/**
 * TPMS — Database Setup & Migration Runner
 *
 * Run this once from your browser to initialize the database.
 * Access at: http://localhost/Internship%20Project/New-TNP-Project/setup.php
 *
 * After a successful run, this page is safe to leave in place —
 * it only applies pending migrations and skips already-applied ones.
 */

// ── Bootstrap ─────────────────────────────────────────────────────────────
define('TPMS_RUNNING', true);
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/database/Migrator.php';

// ── Run migrations ────────────────────────────────────────────────────────
$results    = [];
$dbError    = null;
$dbOk       = false;
$pending    = [];

try {
    $migrator = new Migrator();
    $pending  = $migrator->getPending();
    $results  = $migrator->run(true); // verbose mode: continue on error
    $dbOk     = true;

    // Update the lock file so index.php knows migrations are current
    $migrationCount = count(glob(__DIR__ . '/database/migrations/*.php') ?: []);
    @file_put_contents(__DIR__ . '/cache/migrations.lock', $migrationCount);

} catch (PDOException $e) {
    $dbError = $e->getMessage();
} catch (Exception $e) {
    $dbError = $e->getMessage();
}

// ── Gather table status (only if DB connected) ────────────────────────────
$tableStatus = [];
$userCount   = 0;
$testCredentials = [];

if ($dbOk) {
    $db = Database::getInstance();
    $requiredTables = [
        'users', 'students', 'companies', 'jobs', 'applications',
        'interviews', 'placements', 'trainings', 'training_registrations',
        'bookmarks', 'notifications', 'documents', 'student_projects',
        'student_certifications', 'student_languages', 'student_achievements',
        'faculty', 'universities', 'courses', 'entrance_exams',
        'scholarships', 'higher_study_applications', 'activity_logs',
        'password_resets', 'messages', 'user_presence', '_schema_versions',
    ];

    foreach ($requiredTables as $table) {
        try {
            $count = $db->fetchColumn("SELECT COUNT(*) FROM `{$table}`");
            $tableStatus[$table] = ['exists' => true, 'rows' => (int)$count];
        } catch (Exception $e) {
            $tableStatus[$table] = ['exists' => false, 'rows' => 0];
        }
    }

    // Check default users
    $testUsers = [
        ['email' => 'admin@tpms.com',   'password' => 'Admin@123',   'role' => 'admin'],
        ['email' => 'student@tpms.com', 'password' => 'Student@123', 'role' => 'student'],
        ['email' => 'company@tpms.com', 'password' => 'Company@123', 'role' => 'company'],
    ];

    foreach ($testUsers as $cred) {
        $user = $db->fetchOne("SELECT id, status, email_verified FROM users WHERE email = ?", [$cred['email']]);
        $testCredentials[] = array_merge($cred, [
            'exists'   => (bool)$user,
            'status'   => $user['status'] ?? 'n/a',
            'verified' => $user['email_verified'] ?? 0,
        ]);
    }
}

$hasErrors = !empty(array_filter($results, fn($r) => $r['status'] === 'error'));
$allOk     = $dbOk && !$hasErrors;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TPMS — Database Setup</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #0f172a; color: #e2e8f0; font-family: 'Segoe UI', system-ui, sans-serif; }
        .setup-card { background: #1e293b; border: 1px solid #334155; border-radius: 16px; }
        .badge-ok    { background: #16a34a; color: #fff; }
        .badge-skip  { background: #0284c7; color: #fff; }
        .badge-error { background: #dc2626; color: #fff; }
        .table-dark th { background: #0f172a; }
        .cred-box { background: #0f172a; border: 1px solid #334155; border-radius: 10px; }
        .cred-pass { color: #22d3ee; font-family: monospace; font-size: 1rem; }
        code { color: #86efac; background: #0f172a; padding: 2px 6px; border-radius: 4px; }
        .step-num { width: 32px; height: 32px; border-radius: 50%; display:inline-flex;
                    align-items:center; justify-content:center; font-weight:700; font-size:0.85rem; }
    </style>
</head>
<body class="py-4">
<div class="container" style="max-width:900px">

    <!-- Header -->
    <div class="text-center mb-4">
        <div class="mb-3">
            <i class="fas fa-graduation-cap fa-3x text-primary"></i>
        </div>
        <h1 class="h3 fw-bold text-white">TPMS Database Setup</h1>
        <p class="text-muted">Training &amp; Placement Management System — Migration Runner</p>
    </div>

    <!-- DB Connection Status -->
    <div class="setup-card p-4 mb-4">
        <h5 class="fw-semibold mb-3">
            <i class="fas fa-database me-2 text-primary"></i>Database Connection
        </h5>
        <?php if ($dbError): ?>
            <div class="alert alert-danger mb-0">
                <i class="fas fa-times-circle me-2"></i>
                <strong>Connection Failed:</strong> <?= htmlspecialchars($dbError) ?>
                <hr>
                <small>
                    Check your credentials in <code>config/database.php</code>:<br>
                    host = <strong>localhost</strong> &nbsp;|&nbsp;
                    dbname = <strong>team1</strong> &nbsp;|&nbsp;
                    user = <strong>root</strong> &nbsp;|&nbsp;
                    password = <strong>(empty)</strong>
                    <br><br>
                    Make sure the <strong>team1</strong> database exists. Create it in phpMyAdmin if needed:<br>
                    <code>CREATE DATABASE `team1` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;</code>
                </small>
            </div>
        <?php else: ?>
            <div class="alert alert-success mb-0">
                <i class="fas fa-check-circle me-2"></i>
                Connected to <strong>team1</strong> database on <strong>localhost</strong>.
            </div>
        <?php endif; ?>
    </div>

    <?php if ($dbOk): ?>

    <!-- Migration Results -->
    <div class="setup-card p-4 mb-4">
        <h5 class="fw-semibold mb-3">
            <i class="fas fa-code-branch me-2 text-info"></i>Migration Results
        </h5>

        <?php if (empty($results) && empty($pending)): ?>
            <div class="alert alert-info mb-0">
                <i class="fas fa-check-double me-2"></i>
                All migrations are already applied — nothing to run.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-dark table-sm mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Migration</th>
                            <th>Status</th>
                            <th>Message</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($results as $i => $r): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td><code><?= htmlspecialchars($r['name']) ?></code></td>
                            <td>
                                <?php if ($r['status'] === 'success'): ?>
                                    <span class="badge badge-ok">Applied</span>
                                <?php else: ?>
                                    <span class="badge badge-error">Error</span>
                                <?php endif; ?>
                            </td>
                            <td class="small text-muted"><?= htmlspecialchars($r['message'] ?? '') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <?php if ($allOk || empty($results)): ?>
            <div class="alert alert-success mt-3 mb-0">
                <i class="fas fa-rocket me-2"></i>
                <strong>Database is ready!</strong> All tables and patches are applied.
            </div>
        <?php elseif ($hasErrors): ?>
            <div class="alert alert-warning mt-3 mb-0">
                <i class="fas fa-exclamation-triangle me-2"></i>
                Some migrations had errors. The application may still work if the failed
                migrations are for optional features. Check the error messages above.
            </div>
        <?php endif; ?>
    </div>

    <!-- Table Status -->
    <div class="setup-card p-4 mb-4">
        <h5 class="fw-semibold mb-3">
            <i class="fas fa-table me-2 text-warning"></i>Table Status
        </h5>
        <div class="row g-2">
            <?php foreach ($tableStatus as $table => $info): ?>
            <div class="col-md-4">
                <div class="d-flex align-items-center gap-2 p-2 rounded"
                     style="background:<?= $info['exists'] ? '#052e16' : '#450a0a' ?>">
                    <i class="fas fa-<?= $info['exists'] ? 'check-circle text-success' : 'times-circle text-danger' ?>"></i>
                    <span class="small fw-medium"><?= htmlspecialchars($table) ?></span>
                    <?php if ($info['exists']): ?>
                        <span class="ms-auto badge bg-secondary small"><?= $info['rows'] ?> rows</span>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Test Credentials -->
    <div class="setup-card p-4 mb-4">
        <h5 class="fw-semibold mb-3">
            <i class="fas fa-key me-2 text-warning"></i>Default Login Credentials
        </h5>
        <div class="row g-3">
            <?php
            $icons = ['admin' => 'user-shield', 'student' => 'user-graduate', 'company' => 'building'];
            $colors = ['admin' => '#7c3aed', 'student' => '#0284c7', 'company' => '#d97706'];
            foreach ($testCredentials as $c):
                $icon  = $icons[$c['role']]  ?? 'user';
                $color = $colors[$c['role']] ?? '#6b7280';
            ?>
            <div class="col-md-4">
                <div class="cred-box p-3">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <span class="step-num" style="background:<?= $color ?>">
                            <i class="fas fa-<?= $icon ?> text-white"></i>
                        </span>
                        <span class="fw-semibold text-capitalize"><?= $c['role'] ?></span>
                        <?php if ($c['exists']): ?>
                            <span class="badge badge-ok ms-auto">Ready</span>
                        <?php else: ?>
                            <span class="badge badge-error ms-auto">Missing</span>
                        <?php endif; ?>
                    </div>
                    <div class="small mb-1">
                        <span class="text-muted">Email:</span><br>
                        <code><?= htmlspecialchars($c['email']) ?></code>
                    </div>
                    <div class="small">
                        <span class="text-muted">Password:</span><br>
                        <span class="cred-pass"><?= htmlspecialchars($c['password']) ?></span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Quick Links -->
    <div class="setup-card p-4 mb-4">
        <h5 class="fw-semibold mb-3">
            <i class="fas fa-link me-2 text-info"></i>Quick Links
        </h5>
        <div class="d-flex flex-wrap gap-3">
            <a href="<?= BASE_URL ?>/login" class="btn btn-primary">
                <i class="fas fa-sign-in-alt me-2"></i>Go to Login
            </a>
            <a href="<?= BASE_URL ?>/register/student" class="btn btn-outline-info">
                <i class="fas fa-user-graduate me-2"></i>Register as Student
            </a>
            <a href="<?= BASE_URL ?>/register/company" class="btn btn-outline-warning">
                <i class="fas fa-building me-2"></i>Register as Company
            </a>
            <a href="<?= $_SERVER['REQUEST_URI'] ?>" class="btn btn-outline-secondary">
                <i class="fas fa-sync me-2"></i>Re-run Setup
            </a>
        </div>
    </div>

    <?php endif; // dbOk ?>

    <div class="text-center text-muted small mt-4 pb-4">
        TPMS v<?= APP_VERSION ?> &mdash; Setup page is safe to keep; it only runs pending migrations.
    </div>
</div>
</body>
</html>
