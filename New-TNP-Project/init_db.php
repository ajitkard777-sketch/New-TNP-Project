<?php
/**
 * ============================================================
 *  TPMS — init_db.php
 *  Database Initialization & Diagnostics Page.
 *
 *  Visit once in browser to:
 *    1. Create the tnp_db database (if missing)
 *    2. Create all required tables (if missing)
 *    3. Seed default user accounts (if missing)
 *    4. Verify every table exists and show row counts
 *
 *  URL: http://localhost/Project/New-TNP-Project/init_db.php
 *  Safe to run multiple times — everything uses IF NOT EXISTS.
 * ============================================================
 */

// Load the fixed db_connection (auto-creates DB + tables)
require_once __DIR__ . '/includes/db_connection.php';
// Load auth helpers (seeds tpms_users default accounts)
require_once __DIR__ . '/includes/auth.php';

$checks  = [];
$errors  = [];
$overall = true;

// ── 1. Connection test ────────────────────────────────────────
if ($conn && !$conn->connect_error) {
    $checks[] = ['label' => 'MySQL Connection',  'ok' => true,  'detail' => 'Connected to ' . DB_HOST . ' as ' . DB_USER];
} else {
    $checks[] = ['label' => 'MySQL Connection',  'ok' => false, 'detail' => $conn->connect_error ?? 'Unknown error'];
    $errors[]  = 'MySQL connection failed.';
    $overall   = false;
}

// ── 2. Database exists ────────────────────────────────────────
$res = $conn->query("SELECT SCHEMA_NAME FROM information_schema.SCHEMATA WHERE SCHEMA_NAME = '" . DB_NAME . "'");
$dbExists = ($res && $res->num_rows > 0);
$checks[] = ['label' => 'Database `' . DB_NAME . '`', 'ok' => $dbExists, 'detail' => $dbExists ? 'Exists' : 'Created automatically'];
if (!$dbExists) $errors[] = 'Database was missing and may not have been created.';

// ── 3. Table verification ─────────────────────────────────────
$required = [
    'tpms_users', 'users', 'students', 'student_skills',
    'companies', 'jobs', 'job_skills', 'applications',
    'app_timeline', 'training', 'student_training',
    'universities', 'university_apps', 'bookmarked_jobs', 'activities',
];

foreach ($required as $table) {
    $r = $conn->query("SELECT COUNT(*) AS cnt FROM `{$table}`");
    if ($r) {
        $row = $r->fetch_assoc();
        $checks[] = ['label' => "Table `{$table}`", 'ok' => true, 'detail' => $row['cnt'] . ' rows'];
    } else {
        $checks[] = ['label' => "Table `{$table}`", 'ok' => false, 'detail' => 'Missing or error: ' . $conn->error];
        $errors[]  = "Table `{$table}` is missing.";
        $overall   = false;
    }
}

// ── 4. Seed default login accounts ───────────────────────────
try {
    seedDefaultUsers();   // from includes/auth.php
    $seedOk = true;
    $seedMsg = 'Default accounts present (admin / student / company)';
} catch (Throwable $ex) {
    $seedOk  = false;
    $seedMsg = $ex->getMessage();
    $overall = false;
}
$checks[] = ['label' => 'Default Login Accounts', 'ok' => $seedOk, 'detail' => $seedMsg];

// ── 5. Quick SELECT test on users table ──────────────────────
$r = $conn->query("SELECT COUNT(*) AS cnt FROM `tpms_users`");
if ($r) {
    $row = $r->fetch_assoc();
    $checks[] = ['label' => 'Auth Users Count', 'ok' => true, 'detail' => $row['cnt'] . ' account(s) in tpms_users'];
} else {
    $checks[] = ['label' => 'Auth Users Count', 'ok' => false, 'detail' => $conn->error];
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>DB Diagnostics — TPMS</title>
    <link rel="stylesheet" href="styles.css" />
    <style>
        body { min-height:100vh; background:var(--bg-base); display:flex; align-items:flex-start; justify-content:center; padding:2rem 1rem; font-family:'Inter',sans-serif; }
        .card { background:var(--bg-card); border:1px solid var(--border-color); border-radius:var(--radius-xl); padding:2rem; max-width:640px; width:100%; box-shadow:var(--shadow-xl); }
        h1 { font-size:1.3rem; font-weight:800; color:var(--text-primary); margin:0 0 .4rem; }
        p.sub { font-size:.82rem; color:var(--text-faint); margin:0 0 1.5rem; }
        table { width:100%; border-collapse:collapse; font-size:.8rem; }
        th,td { padding:.55rem .75rem; border-bottom:1px solid var(--border-color); text-align:left; vertical-align:top; }
        th { color:var(--text-faint); font-weight:600; font-size:.72rem; text-transform:uppercase; }
        td { color:var(--text-secondary); }
        .ok   { color:var(--success); font-weight:700; }
        .fail { color:var(--danger);  font-weight:700; }
        .banner { padding:.75rem 1rem; border-radius:var(--radius-md); font-size:.82rem; font-weight:600; margin-bottom:1.25rem; display:flex; align-items:center; gap:.5rem; }
        .banner-ok   { background:var(--success-light); color:var(--success); border:1px solid rgba(34,197,94,.2); }
        .banner-fail { background:var(--danger-light);  color:var(--danger);  border:1px solid rgba(239,68,68,.2); }
        .go-btn { display:inline-block; margin-top:1.5rem; padding:.55rem 1.25rem; background:var(--primary); color:#fff; border-radius:var(--radius-md); font-size:.82rem; font-weight:600; text-decoration:none; box-shadow:var(--shadow-brand); }
        .creds { margin-top:1.5rem; background:var(--bg-subtle); border:1px solid var(--border-color); border-radius:var(--radius-md); padding:1rem; font-size:.78rem; color:var(--text-faint); line-height:1.9; }
        .creds code { background:var(--bg-hover); border-radius:3px; padding:1px 5px; color:var(--primary); font-size:.73rem; }
    </style>
</head>
<body>
<div class="card">
    <h1>🛠 TPMS Database Diagnostics</h1>
    <p class="sub">Checks MySQL connection, creates missing database/tables, and seeds default accounts.</p>

    <?php if ($overall): ?>
    <div class="banner banner-ok">✅ All checks passed — database is ready!</div>
    <?php else: ?>
    <div class="banner banner-fail">❌ Some checks failed — see details below.</div>
    <?php endif; ?>

    <table>
        <thead><tr><th>Check</th><th>Status</th><th>Detail</th></tr></thead>
        <tbody>
        <?php foreach ($checks as $c): ?>
            <tr>
                <td><?= htmlspecialchars($c['label']) ?></td>
                <td class="<?= $c['ok'] ? 'ok' : 'fail' ?>"><?= $c['ok'] ? '✓ OK' : '✗ FAIL' ?></td>
                <td><?= htmlspecialchars($c['detail']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <div class="creds">
        <strong style="color:var(--text-secondary);">Default Login Credentials</strong><br />
        Admin &nbsp;&nbsp; → <code>admin@tpms.com</code> / <code>Admin@123</code><br />
        Student → <code>student@tpms.com</code> / <code>Student@123</code><br />
        Company → <code>company@tpms.com</code> / <code>Company@123</code>
    </div>

    <a href="login.php" class="go-btn">Go to Login Page →</a>
</div>
</body>
</html>
