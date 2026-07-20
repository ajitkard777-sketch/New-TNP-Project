<?php
/**
 * ============================================================
 *  TPMS — includes/auth.php  (v3.0)
 *  Complete Authentication Module
 *
 *  Provides:
 *  ─ Session management with 30-min timeout
 *  ─ CSRF token generation & verification
 *  ─ Role-based access guards
 *  ─ loginUser()         — queries users table (+ tpms_users fallback)
 *  ─ registerStudent()   — creates users + students row
 *  ─ registerCompany()   — creates users + companies row
 *  ─ initiateForgotPassword()
 *  ─ getResetUser()
 *  ─ resetPassword()
 *  ─ doLogout()
 *  ─ seedDefaultUsers()  — backward-compat seed for tpms_users
 * ============================================================
 */

require_once __DIR__ . '/db_connection.php';

// ── Constants ─────────────────────────────────────────────────
if (!defined('SESSION_TIMEOUT')) define('SESSION_TIMEOUT', 1800); // 30 minutes

// =============================================================
//  SESSION HELPERS
// =============================================================

if (!function_exists('tpms_session_start')) {
    function tpms_session_start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_set_cookie_params([
                'lifetime' => 0,
                'path'     => '/',
                'httponly' => true,
                'samesite' => 'Strict',
            ]);
            session_start();
        }
    }
}

if (!function_exists('redirect')) {
    function redirect(string $url): void
    {
        header('Location: ' . $url);
        exit;
    }
}

// =============================================================
//  CSRF
// =============================================================

/**
 * Generate (or return cached) CSRF token for this session.
 */
function generateCSRF(): string
{
    tpms_session_start();
    if (empty($_SESSION['_tpms_csrf'])) {
        $_SESSION['_tpms_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_tpms_csrf'];
}

/**
 * Verify CSRF token from POST. Kills script on mismatch.
 */
function verifyCSRF(): void
{
    tpms_session_start();
    $submitted = $_POST['csrf_token'] ?? '';
    $stored    = $_SESSION['_tpms_csrf'] ?? '';

    if (!$stored || !hash_equals($stored, $submitted)) {
        http_response_code(403);
        die(
            '<!DOCTYPE html><html><head><title>Security Error</title></head><body>' .
            '<h2 style="font-family:sans-serif;color:#c00;">⚠ Security check failed.</h2>' .
            '<p style="font-family:sans-serif;"><a href="login.php">← Return to Login</a></p>' .
            '</body></html>'
        );
    }
}

// =============================================================
//  SESSION TIMEOUT
// =============================================================

/**
 * Check whether the session has timed out.
 * Resets the activity timer if still valid.
 * Call inside requireLogin() — no need to call manually.
 */
function checkSessionTimeout(): void
{
    tpms_session_start();
    if (empty($_SESSION['tpms_role'])) return;

    if (isset($_SESSION['_tpms_last_active'])) {
        if ((time() - $_SESSION['_tpms_last_active']) > SESSION_TIMEOUT) {
            // Session expired
            $_SESSION = [];
            if (ini_get('session.use_cookies')) {
                $p = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000,
                    $p['path'], $p['domain'] ?? '', $p['secure'] ?? false, $p['httponly'] ?? true);
            }
            session_destroy();
            redirect('login.php?error=session_expired');
        }
    }
    $_SESSION['_tpms_last_active'] = time();
}

// =============================================================
//  ACCESS GUARDS
// =============================================================

/**
 * Require a valid session. Redirect to login.php otherwise.
 */
function requireLogin(): void
{
    tpms_session_start();
    checkSessionTimeout();
    if (empty($_SESSION['tpms_role'])) {
        redirect('login.php');
    }
}

/**
 * Require a specific role (or one of an array of roles).
 * @param string|string[] $roles
 */
function requireRole($roles): void
{
    requireLogin();
    $allowed = (array) $roles;
    if (!in_array($_SESSION['tpms_role'], $allowed, true)) {
        session_destroy();
        redirect('login.php?error=unauthorized');
    }
}

/**
 * Return the session user data array.
 */
function getSessionUser(): array
{
    tpms_session_start();
    return [
        'id'    => $_SESSION['tpms_user_id'] ?? null,
        'uid'   => $_SESSION['tpms_uid']     ?? '',
        'name'  => $_SESSION['tpms_name']    ?? '',
        'email' => $_SESSION['tpms_email']   ?? '',
        'role'  => $_SESSION['tpms_role']    ?? 'guest',
    ];
}

// =============================================================
//  LOGIN
// =============================================================

/**
 * Authenticate a user by email + password.
 *
 * Checks the `users` table first; falls back to `tpms_users`.
 * Optionally verifies the expected role matches the account role.
 *
 * @param string $email
 * @param string $password    Plain-text password
 * @param string $expectedRole  'admin'|'student'|'company'|'' ('' = any)
 * @return array  ['success'=>bool, 'message'=>str, 'role'=>str?, 'name'=>str?]
 */
function loginUser(string $email, string $password, string $expectedRole = ''): array
{
    global $conn;

    // Input validation
    if (empty(trim($email)) || empty($password)) {
        return ['success' => false, 'message' => 'Email and password are required.'];
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'message' => 'Please enter a valid email address.'];
    }

    $email = trim($email);

    // ── Primary: `users` table ────────────────────────────────
    $stmt = $conn->prepare(
        "SELECT id, uid, name, email, password_hash AS pw, role, status
         FROM users WHERE email = ? LIMIT 1"
    );
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    // ── Fallback: `tpms_users` table ──────────────────────────
    if (!$user) {
        $stmt2 = $conn->prepare(
            "SELECT id, '' AS uid, name, email, password AS pw, role, status
             FROM tpms_users WHERE email = ? LIMIT 1"
        );
        $stmt2->bind_param('s', $email);
        $stmt2->execute();
        $user = $stmt2->get_result()->fetch_assoc();
        $stmt2->close();
    }

    if (!$user) {
        return ['success' => false, 'message' => 'Invalid email or password.'];
    }

    if ((int)($user['status'] ?? 1) === 0) {
        return ['success' => false, 'message' => 'Your account has been deactivated. Contact the administrator.'];
    }

    if (!password_verify($password, $user['pw'])) {
        return ['success' => false, 'message' => 'Invalid email or password.'];
    }

    // Role mismatch check
    if ($expectedRole !== '' && $user['role'] !== $expectedRole) {
        return [
            'success' => false,
            'message' => 'This account is registered as "' . ucfirst($user['role']) .
                         '". Please select the correct role tab.',
        ];
    }

    // ── Create authenticated session ──────────────────────────
    session_regenerate_id(true);
    $_SESSION['tpms_user_id']      = $user['id'];
    $_SESSION['tpms_uid']          = $user['uid'];
    $_SESSION['tpms_name']         = $user['name'];
    $_SESSION['tpms_email']        = $user['email'];
    $_SESSION['tpms_role']         = $user['role'];
    $_SESSION['_tpms_last_active'] = time();

    return ['success' => true, 'role' => $user['role'], 'name' => $user['name']];
}

// =============================================================
//  STUDENT REGISTRATION
// =============================================================

/**
 * Register a new student account.
 *
 * Required keys in $data:
 *   name, email, password, confirm_password
 * Optional keys:
 *   phone, department (→ stored as branch), year, roll_number
 */
function registerStudent(array $data): array
{
    global $conn;

    // Sanitise
    $name    = trim($data['name']             ?? '');
    $email   = trim($data['email']            ?? '');
    $pass    = $data['password']              ?? '';
    $confirm = $data['confirm_password']      ?? '';
    $phone   = trim($data['phone']            ?? '');
    $dept    = trim($data['department']       ?? '');   // maps → branch
    $year    = trim($data['year']             ?? '');
    $roll    = trim($data['roll_number']      ?? '');

    // Required field check
    if (!$name || !$email || !$pass || !$confirm) {
        return ['success' => false, 'message' => 'Full Name, Email, and Password are required.'];
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'message' => 'Please enter a valid email address.'];
    }
    if (strlen($pass) < 8) {
        return ['success' => false, 'message' => 'Password must be at least 8 characters long.'];
    }
    if (!preg_match('/[A-Z]/', $pass)) {
        return ['success' => false, 'message' => 'Password must contain at least one uppercase letter.'];
    }
    if (!preg_match('/[0-9]/', $pass)) {
        return ['success' => false, 'message' => 'Password must contain at least one number.'];
    }
    if ($pass !== $confirm) {
        return ['success' => false, 'message' => 'Passwords do not match.'];
    }

    // Duplicate email check
    $chk = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
    $chk->bind_param('s', $email);
    $chk->execute();
    $chk->store_result();
    $exists = $chk->num_rows > 0;
    $chk->close();
    if ($exists) {
        return ['success' => false, 'message' => 'An account with this email already exists. Please log in.'];
    }

    $uid  = _auth_uid('STU');
    $hash = password_hash($pass, PASSWORD_BCRYPT, ['cost' => 12]);

    // Insert user
    $stmt = $conn->prepare(
        "INSERT INTO users (uid, name, email, password_hash, role) VALUES (?, ?, ?, ?, 'student')"
    );
    $stmt->bind_param('ssss', $uid, $name, $email, $hash);
    if (!$stmt->execute()) {
        $stmt->close();
        error_log('[TPMS] registerStudent insert failed: ' . $conn->error);
        return ['success' => false, 'message' => 'Registration failed. Please try again.'];
    }
    $userId = (int)$conn->insert_id;
    $stmt->close();

    // Insert student profile
    $stmt2 = $conn->prepare(
        "INSERT INTO students (user_id, student_uid, branch, phone, year, roll_number) VALUES (?, ?, ?, ?, ?, ?)"
    );
    $stmt2->bind_param('isssss', $userId, $uid, $dept, $phone, $year, $roll);
    $stmt2->execute();
    $stmt2->close();

    return ['success' => true, 'message' => 'Account created successfully! Please log in with your credentials.'];
}

// =============================================================
//  COMPANY REGISTRATION
// =============================================================

/**
 * Register a new company account.
 *
 * Required keys: company_name, email, password, confirm_password
 * Optional keys: hr_name, phone, website, location
 */
function registerCompany(array $data): array
{
    global $conn;

    $compName = trim($data['company_name']    ?? '');
    $hrName   = trim($data['hr_name']         ?? '');
    $email    = trim($data['email']           ?? '');
    $pass     = $data['password']             ?? '';
    $confirm  = $data['confirm_password']     ?? '';
    $phone    = trim($data['phone']           ?? '');
    $website  = trim($data['website']         ?? '');
    $location = trim($data['location']        ?? '');

    if (!$compName || !$email || !$pass || !$confirm) {
        return ['success' => false, 'message' => 'Company Name, Email, and Password are required.'];
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'message' => 'Please enter a valid email address.'];
    }
    if (strlen($pass) < 8) {
        return ['success' => false, 'message' => 'Password must be at least 8 characters long.'];
    }
    if (!preg_match('/[A-Z]/', $pass)) {
        return ['success' => false, 'message' => 'Password must contain at least one uppercase letter.'];
    }
    if (!preg_match('/[0-9]/', $pass)) {
        return ['success' => false, 'message' => 'Password must contain at least one number.'];
    }
    if ($pass !== $confirm) {
        return ['success' => false, 'message' => 'Passwords do not match.'];
    }

    // Duplicate check
    $chk = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
    $chk->bind_param('s', $email);
    $chk->execute();
    $chk->store_result();
    $exists = $chk->num_rows > 0;
    $chk->close();
    if ($exists) {
        return ['success' => false, 'message' => 'An account with this email already exists. Please log in.'];
    }

    $uid  = _auth_uid('COMP');
    $hash = password_hash($pass, PASSWORD_BCRYPT, ['cost' => 12]);

    $stmt = $conn->prepare(
        "INSERT INTO users (uid, name, email, password_hash, role) VALUES (?, ?, ?, ?, 'company')"
    );
    $stmt->bind_param('ssss', $uid, $compName, $email, $hash);
    if (!$stmt->execute()) {
        $stmt->close();
        return ['success' => false, 'message' => 'Registration failed. Please try again.'];
    }
    $userId = (int)$conn->insert_id;
    $stmt->close();

    // Insert company profile
    $stmt2 = $conn->prepare(
        "INSERT INTO companies (comp_uid, user_id, name, website, contact, hr_name, location, registered_date)
         VALUES (?, ?, ?, ?, ?, ?, ?, CURDATE())"
    );
    $stmt2->bind_param('sissssss', $uid, $userId, $compName, $website, $phone, $hrName, $location);
    $stmt2->execute();
    $stmt2->close();

    return ['success' => true, 'message' => 'Company registered successfully! Please log in to access your dashboard.'];
}

// =============================================================
//  FORGOT PASSWORD
// =============================================================

/**
 * Generate a password reset token for the given email.
 * In development, the reset link is returned for display.
 * In production, you would email it instead.
 *
 * @return array ['success'=>bool, 'message'=>str, 'dev_link'=>str?]
 */
function initiateForgotPassword(string $email): array
{
    global $conn;

    $email = trim($email);
    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'message' => 'Please enter a valid email address.'];
    }

    // Look up in primary users table
    $stmt = $conn->prepare("SELECT id, name FROM users WHERE email = ? AND status = 1 LIMIT 1");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    // Generic response even when email not found (security: don't leak if email exists)
    if (!$user) {
        return [
            'success'  => true,
            'dev_link' => null,
            'message'  => 'If this email is registered, a reset link has been generated below.',
        ];
    }

    $token   = bin2hex(random_bytes(32));        // 64-char hex
    $expires = date('Y-m-d H:i:s', time() + 3600); // 1-hour expiry

    // Store token in users table
    $upd = $conn->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE id = ?");
    $upd->bind_param('ssi', $token, $expires, $user['id']);
    $upd->execute();
    $upd->close();

    // Also store in password_resets for auditability
    $ins = $conn->prepare(
        "INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)
         ON DUPLICATE KEY UPDATE token = VALUES(token), expires_at = VALUES(expires_at), used = 0"
    );
    $ins->bind_param('sss', $email, $token, $expires);
    $ins->execute();
    $ins->close();

    // Build reset URL
    $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
    $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $base   = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
    $link   = "{$scheme}://{$host}{$base}/reset_password.php?token={$token}";

    return [
        'success'  => true,
        'dev_link' => $link,
        'message'  => 'Reset link generated. (In production this would be emailed to ' . htmlspecialchars($email) . ')',
    ];
}

// =============================================================
//  RESET PASSWORD
// =============================================================

/**
 * Fetch a user by valid (non-expired) reset token.
 * Returns null if invalid or expired.
 */
function getResetUser(string $token): ?array
{
    global $conn;
    if (strlen($token) !== 64) return null;

    $stmt = $conn->prepare(
        "SELECT id, name, email FROM users
         WHERE reset_token = ? AND reset_expires > NOW() LIMIT 1"
    );
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $user ?: null;
}

/**
 * Apply a new password using a valid reset token.
 */
function resetPassword(string $token, string $newPass, string $confirm): array
{
    if (strlen($newPass) < 8) {
        return ['success' => false, 'message' => 'Password must be at least 8 characters.'];
    }
    if (!preg_match('/[A-Z]/', $newPass) || !preg_match('/[0-9]/', $newPass)) {
        return ['success' => false, 'message' => 'Password needs at least one uppercase letter and one number.'];
    }
    if ($newPass !== $confirm) {
        return ['success' => false, 'message' => 'Passwords do not match.'];
    }

    $user = getResetUser($token);
    if (!$user) {
        return ['success' => false, 'message' => 'Reset link is invalid or has expired. Please request a new one.'];
    }

    global $conn;
    $hash = password_hash($newPass, PASSWORD_BCRYPT, ['cost' => 12]);

    $upd = $conn->prepare(
        "UPDATE users SET password_hash = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?"
    );
    $upd->bind_param('si', $hash, $user['id']);
    $upd->execute();
    $upd->close();

    // Mark token used in password_resets
    $conn->query("UPDATE password_resets SET used = 1 WHERE token = '" . $conn->real_escape_string($token) . "'");

    return ['success' => true, 'message' => 'Password updated successfully! You can now log in with your new password.'];
}

// =============================================================
//  LOGOUT
// =============================================================

function doLogout(): void
{
    tpms_session_start();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(
            session_name(), '', time() - 42000,
            $p['path'], $p['domain'] ?? '', $p['secure'] ?? false, $p['httponly'] ?? true
        );
    }
    session_destroy();
    redirect('login.php?msg=loggedout');
}

// =============================================================
//  UTILITIES
// =============================================================

/**
 * Generate a collision-resistant UID (e.g. STU20261234).
 */
function _auth_uid(string $prefix): string
{
    return strtoupper($prefix) . date('Y') . str_pad((string)random_int(1, 99999), 5, '0', STR_PAD_LEFT);
}

// =============================================================
//  BACKWARD COMPAT — seeds tpms_users (called from login.php)
// =============================================================
function seedDefaultUsers(): void
{
    // Now handled inside _tpms_seed_default_users() which runs
    // automatically in _tpms_bootstrap_tables() on every page load.
    // This stub kept for backward compatibility.
}
