<?php
/**
 * TPMS Setup Verification Script
 * Run at: http://localhost/team1/setup_check.php
 * DELETE THIS FILE AFTER VERIFICATION
 */
define('TPMS_RUNNING', true);

$checks = [];
$errors = [];

// ── 1. Config & Constants ──────────────────────────────────────────────────
require_once __DIR__ . '/config/config.php';
$checks['PHP Version'] = ['ok' => version_compare(PHP_VERSION, '7.4', '>='), 'val' => PHP_VERSION];
$checks['BASE_URL'] = ['ok' => defined('BASE_URL'), 'val' => BASE_URL];
$checks['ROOT_PATH'] = ['ok' => defined('ROOT_PATH'), 'val' => ROOT_PATH];
$checks['UPLOADS_PATH'] = ['ok' => defined('UPLOADS_PATH'), 'val' => UPLOADS_PATH];

// ── 2. Database Connection ─────────────────────────────────────────────────
require_once __DIR__ . '/config/database.php';
$dbOk = false;
$dbTables = [];
try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    $dbOk = true;
    $stmt = $pdo->query("SHOW TABLES");
    $dbTables = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {
    $errors[] = 'DB Connection: ' . $e->getMessage();
}
$checks['Database Connection'] = ['ok' => $dbOk, 'val' => $dbOk ? 'Connected to team1' : 'FAILED'];

// ── 3. Expected Tables ─────────────────────────────────────────────────────
$expectedTables = [
    'users','students','companies','jobs','applications','interviews',
    'placements','trainings','training_registrations','bookmarks',
    'notifications','documents','student_projects','student_certifications',
    'student_languages','student_achievements','faculty','universities',
    'courses','entrance_exams','scholarships','higher_study_applications',
    'activity_logs','password_resets'
];
$missingTables = array_diff($expectedTables, $dbTables);
$checks['All Tables Present'] = ['ok' => count($missingTables) === 0, 'val' => count($missingTables) === 0 ? count($dbTables).' tables found' : 'MISSING: '.implode(', ', $missingTables)];

// ── 4. Sample Data ─────────────────────────────────────────────────────────
if ($dbOk) {
    $userCount    = $db->fetchColumn("SELECT COUNT(*) FROM users");
    $studentCount = $db->fetchColumn("SELECT COUNT(*) FROM students");
    $companyCount = $db->fetchColumn("SELECT COUNT(*) FROM companies");
    $jobCount     = $db->fetchColumn("SELECT COUNT(*) FROM jobs");
    $checks['Users in DB']    = ['ok' => $userCount > 0,    'val' => "$userCount users"];
    $checks['Students in DB'] = ['ok' => $studentCount > 0, 'val' => "$studentCount students"];
    $checks['Companies in DB'] = ['ok' => $companyCount > 0,'val' => "$companyCount companies"];
    $checks['Jobs in DB']     = ['ok' => $jobCount > 0,     'val' => "$jobCount jobs"];
}

// ── 5. Upload Directories ──────────────────────────────────────────────────
$uploadDirs = ['profile_photos','resume','company','documents'];
foreach ($uploadDirs as $dir) {
    $path = UPLOADS_PATH . '/' . $dir;
    $checks["uploads/$dir"] = ['ok' => is_dir($path), 'val' => is_dir($path) ? 'Exists' : 'MISSING'];
}

// ── 6. Logs & Cache Directories ───────────────────────────────────────────
$checks['logs/ directory'] = ['ok' => is_dir(ROOT_PATH . '/logs'),  'val' => is_dir(ROOT_PATH . '/logs')  ? 'Exists' : 'MISSING'];
$checks['cache/ directory'] = ['ok' => is_dir(ROOT_PATH . '/cache'), 'val' => is_dir(ROOT_PATH . '/cache') ? 'Exists' : 'MISSING'];

// ── 7. Critical Files ─────────────────────────────────────────────────────
$criticalFiles = [
    'index.php','config/config.php','config/database.php',
    'includes/helpers.php','includes/header.php','includes/Mailer.php',
    'middleware/AuthMiddleware.php','middleware/CsrfMiddleware.php',
    'assets/css/style.css','assets/css/dark-mode.css',
    'assets/js/app.js','vendor/phpmailer/src/PHPMailer.php'
];
foreach ($criticalFiles as $file) {
    $path = ROOT_PATH . '/' . $file;
    $checks["File: $file"] = ['ok' => file_exists($path), 'val' => file_exists($path) ? 'Found' : 'MISSING'];
}

// ── 8. PHP Extensions ─────────────────────────────────────────────────────
$exts = ['pdo','pdo_mysql','mbstring','openssl','fileinfo'];
foreach ($exts as $ext) {
    $checks["PHP ext: $ext"] = ['ok' => extension_loaded($ext), 'val' => extension_loaded($ext) ? 'Loaded' : 'MISSING'];
}

// ── 9. Admin Account ─────────────────────────────────────────────────────
if ($dbOk) {
    $admin = $db->fetchOne("SELECT id, email, role, status FROM users WHERE email = 'admin@tpms.com'");
    $checks['Admin Account'] = ['ok' => !empty($admin), 'val' => $admin ? "Found (status: {$admin['status']})" : 'MISSING - re-import SQL'];
}

// ── Totals ────────────────────────────────────────────────────────────────
$passed = count(array_filter($checks, fn($c) => $c['ok']));
$total  = count($checks);
$failed = $total - $passed;

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>TPMS Setup Check</title>
<style>
  *{box-sizing:border-box;margin:0;padding:0}
  body{font-family:'Segoe UI',sans-serif;background:#0f172a;color:#e2e8f0;min-height:100vh;padding:2rem}
  .container{max-width:900px;margin:0 auto}
  h1{font-size:2rem;font-weight:800;background:linear-gradient(135deg,#6366f1,#8b5cf6);-webkit-background-clip:text;-webkit-text-fill-color:transparent;margin-bottom:.5rem}
  .subtitle{color:#94a3b8;margin-bottom:2rem;font-size:.95rem}
  .summary{display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;margin-bottom:2rem}
  .stat{background:#1e293b;border-radius:12px;padding:1.5rem;text-align:center;border:1px solid #334155}
  .stat .num{font-size:2.5rem;font-weight:800;line-height:1}
  .stat .lbl{color:#94a3b8;font-size:.85rem;margin-top:.25rem}
  .stat.pass .num{color:#34d399}
  .stat.fail .num{color:#f87171}
  .stat.total .num{color:#818cf8}
  table{width:100%;border-collapse:collapse;background:#1e293b;border-radius:12px;overflow:hidden;border:1px solid #334155}
  th{background:#0f172a;padding:12px 16px;text-align:left;font-size:.8rem;text-transform:uppercase;letter-spacing:.05em;color:#64748b}
  td{padding:10px 16px;border-top:1px solid #334155;font-size:.9rem}
  .badge{display:inline-block;padding:3px 10px;border-radius:20px;font-size:.75rem;font-weight:600}
  .badge.ok{background:#064e3b;color:#34d399}
  .badge.fail{background:#7f1d1d;color:#f87171}
  .val{color:#94a3b8;font-size:.85rem}
  .footer{margin-top:2rem;padding:1rem;background:#1e293b;border-radius:8px;border:1px solid #f59e0b33;color:#fbbf24;font-size:.85rem}
  .login-box{background:#1e293b;border-radius:12px;border:1px solid #334155;padding:1.5rem;margin:1.5rem 0}
  .login-box h3{color:#818cf8;margin-bottom:1rem;font-size:1rem}
  .cred{display:flex;gap:.5rem;margin-bottom:.5rem;align-items:center;font-size:.875rem}
  .cred .role{background:#312e81;color:#a5b4fc;padding:2px 10px;border-radius:4px;font-size:.75rem;font-weight:600;min-width:70px;text-align:center}
  .link{margin-top:1.5rem;text-align:center}
  .link a{background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;text-decoration:none;padding:12px 32px;border-radius:8px;font-weight:600;display:inline-block}
</style>
</head>
<body>
<div class="container">
  <h1>⚙️ TPMS Setup Check</h1>
  <p class="subtitle">Training & Placement Management System — Local Environment Diagnostic</p>

  <div class="summary">
    <div class="stat total"><div class="num"><?= $total ?></div><div class="lbl">Total Checks</div></div>
    <div class="stat pass"><div class="num"><?= $passed ?></div><div class="lbl">Passed ✅</div></div>
    <div class="stat fail"><div class="num"><?= $failed ?></div><div class="lbl">Failed ❌</div></div>
  </div>

  <?php if ($failed === 0): ?>
    <div style="background:#064e3b;color:#34d399;padding:1rem 1.5rem;border-radius:8px;font-weight:700;margin-bottom:1.5rem;font-size:1.1rem">
      🎉 All checks passed! Your TPMS is ready to run.
    </div>
  <?php else: ?>
    <div style="background:#7f1d1d;color:#f87171;padding:1rem 1.5rem;border-radius:8px;font-weight:700;margin-bottom:1.5rem">
      ⚠️ <?= $failed ?> check(s) failed. Review the table below.
    </div>
  <?php endif; ?>

  <table>
    <thead><tr><th>Check</th><th>Status</th><th>Value</th></tr></thead>
    <tbody>
    <?php foreach ($checks as $name => $check): ?>
      <tr>
        <td><?= htmlspecialchars($name) ?></td>
        <td><span class="badge <?= $check['ok'] ? 'ok' : 'fail' ?>"><?= $check['ok'] ? 'PASS' : 'FAIL' ?></span></td>
        <td class="val"><?= htmlspecialchars($check['val']) ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>

  <div class="login-box">
    <h3>🔑 Default Login Credentials</h3>
    <div class="cred"><span class="role">ADMIN</span> admin@tpms.com &nbsp;|&nbsp; password: <strong>password</strong></div>
    <div class="cred"><span class="role">STUDENT</span> student1@test.com &nbsp;|&nbsp; password: <strong>password</strong></div>
    <div class="cred"><span class="role">COMPANY</span> hr@techcorp.com &nbsp;|&nbsp; password: <strong>password</strong></div>
  </div>

  <div class="link">
    <a href="<?= BASE_URL ?>">🚀 Launch TPMS Application</a>
  </div>

  <div class="footer">
    ⚠️ <strong>Security Note:</strong> Delete <code>setup_check.php</code> from the project root after verification. Do not leave this file on a production server.
  </div>
</div>
</body>
</html>
