<?php
/**
 * ============================================================
 *  TPMS — seed_users.php
 *  One-time seed script for default user accounts.
 *
 *  Run once in browser: http://localhost/Project/New-TNP-Project/seed_users.php
 *
 *  NOTE: This is also called automatically by login.php on every
 *  page load (idempotent — safe to run multiple times).
 *  This standalone script is provided for manual use or CI/CD.
 * ============================================================
 */
require_once __DIR__ . '/includes/auth.php';

header('Content-Type: text/html; charset=UTF-8');

// Run the seeder
seedDefaultUsers();

$defaults = [
    ['name' => 'TPO Administrator', 'email' => 'admin@tpms.com',   'role' => 'admin',   'pass' => 'Admin@123'],
    ['name' => 'Demo Student',      'email' => 'student@tpms.com', 'role' => 'student', 'pass' => 'Student@123'],
    ['name' => 'Demo Company',      'email' => 'company@tpms.com', 'role' => 'company', 'pass' => 'Company@123'],
];
?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Seed Users — TPMS</title>
    <link rel="stylesheet" href="styles.css" />
    <style>
        body { min-height:100vh; display:flex; align-items:center; justify-content:center; background:var(--bg-base); font-family:'Inter',sans-serif; padding:2rem; }
        .card { background:var(--bg-card); border:1px solid var(--border-color); border-radius:var(--radius-xl); padding:2rem; max-width:520px; width:100%; box-shadow:var(--shadow-xl); }
        table { width:100%; border-collapse:collapse; font-size:.8rem; margin-top:1.25rem; }
        th,td { padding:.55rem .75rem; border-bottom:1px solid var(--border-color); text-align:left; }
        th { color:var(--text-faint); font-weight:600; font-size:.72rem; text-transform:uppercase; }
        td { color:var(--text-secondary); }
        code { background:var(--bg-subtle); border-radius:4px; padding:2px 6px; color:var(--primary); font-size:.75rem; }
        .badge-success { background:var(--success-light); color:var(--success); padding:2px 8px; border-radius:99px; font-size:.72rem; font-weight:600; }
        .go-btn { display:inline-flex; align-items:center; gap:.4rem; margin-top:1.5rem; padding:.55rem 1.25rem; background:var(--primary); color:#fff; border-radius:var(--radius-md); font-size:.82rem; font-weight:600; text-decoration:none; box-shadow:var(--shadow-brand); }
    </style>
</head>
<body>
<div class="card">
    <h1 style="font-size:1.25rem;font-weight:800;color:var(--text-primary);margin:0 0 .35rem;">✅ Seed Complete</h1>
    <p style="font-size:.82rem;color:var(--text-faint);margin:0;">
        Default accounts have been created (or were already present). Passwords are stored as bcrypt hashes.
    </p>

    <table>
        <thead>
            <tr><th>Name</th><th>Email</th><th>Password</th><th>Role</th><th>Status</th></tr>
        </thead>
        <tbody>
            <?php foreach ($defaults as $u): ?>
            <tr>
                <td><?= htmlspecialchars($u['name']) ?></td>
                <td><code><?= htmlspecialchars($u['email']) ?></code></td>
                <td><code><?= htmlspecialchars($u['pass']) ?></code></td>
                <td><?= htmlspecialchars($u['role']) ?></td>
                <td><span class="badge-success">Active</span></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <a href="login.php" class="go-btn">Go to Login Page →</a>
</div>
</body>
</html>
