<?php
/**
 * ============================================================
 *  TPMS — api/auth.php
 *  Handles AJAX: session, csrf, login, logout, register,
 *                update_profile, change_password, forgot_password
 *
 *  Security: Prepared statements, strict validation,
 *            session timeout check, CSRF checks.
 * ============================================================
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/helpers.php';

setApiHeaders();
tpms_session_start();

$method = $_SERVER['REQUEST_METHOD'];
$action = get_param('action') ?: (post('action') ?: 'session');

switch ($action) {
    case 'session':         handleSession();        break;
    case 'csrf':            handleCSRF();           break;
    case 'login':           handleLogin();          break;
    case 'logout':          handleLogout();         break;
    case 'register':        handleRegister();       break;
    case 'update_profile':  handleUpdateProfile();  break;
    case 'change_password': handleChangePassword(); break;
    case 'forgot_password': handleForgotPassword(); break;
    default:
        respond(['success' => false, 'message' => 'Unknown action: ' . xss($action)], 400);
}

// ── Session Check ─────────────────────────────────────────────
function handleSession(): void
{
    $csrf = getCSRFToken(); // ensure token exists
    if (!empty($_SESSION['user'])) {
        respond([
            'success'    => true,
            'loggedIn'   => true,
            'user'       => $_SESSION['user'],
            'csrf_token' => $csrf,
        ]);
    }
    respond(['success' => true, 'loggedIn' => false, 'csrf_token' => $csrf]);
}

// ── CSRF Token ────────────────────────────────────────────────
function handleCSRF(): void
{
    respond(['success' => true, 'csrf_token' => getCSRFToken()]);
}

// ── Login ─────────────────────────────────────────────────────
function handleLogin(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        respond(['success' => false, 'message' => 'POST required'], 405);
    }

    // Brute-force protection (5 attempts per 30s)
    $_SESSION['login_attempts'] = $_SESSION['login_attempts'] ?? 0;
    $_SESSION['login_last']     = $_SESSION['login_last']     ?? 0;
    if ($_SESSION['login_attempts'] >= 5 && (time() - $_SESSION['login_last']) < 30) {
        respond(['success' => false, 'message' => 'Too many failed attempts. Please wait 30 seconds.'], 429);
    }

    $email    = strtolower(post('email'));
    $password = post('password');
    $role     = post('role'); // 'student' | 'company' | 'admin'

    if (!isValidEmail($email)) {
        respond(['success' => false, 'message' => 'Invalid email address.'], 422);
    }
    if (empty($password)) {
        respond(['success' => false, 'message' => 'Password is required.'], 422);
    }

    $pdo  = getPDO();
    $stmt = $pdo->prepare(
        "SELECT u.id, u.uid, u.name, u.email, u.password_hash, u.role, u.avatar, u.status
         FROM users u WHERE u.email = ? LIMIT 1"
    );
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        $_SESSION['login_attempts']++;
        $_SESSION['login_last'] = time();
        respond(['success' => false, 'message' => 'Invalid email or password.'], 401);
    }

    if ((int)($user['status'] ?? 1) === 0) {
        respond(['success' => false, 'message' => 'Your account has been deactivated. Contact the administrator.'], 403);
    }

    // Role check
    if (!empty($role) && $user['role'] !== $role) {
        respond(['success' => false, 'message' => "This account is registered as '{$user['role']}', not '$role'."], 403);
    }

    // Reset brute force counter
    $_SESSION['login_attempts'] = 0;

    // Build session payload
    $sessionUser = buildSessionUser($pdo, $user);

    // Regenerate session ID (security)
    session_regenerate_id(true);
    $_SESSION['user']              = $sessionUser;
    $_SESSION['csrf_token']        = bin2hex(random_bytes(32));

    // Align with includes/auth.php variables so legacy checks also pass
    $_SESSION['tpms_user_id']      = $user['id'];
    $_SESSION['tpms_uid']          = $user['uid'];
    $_SESSION['tpms_name']         = $user['name'];
    $_SESSION['tpms_email']        = $user['email'];
    $_SESSION['tpms_role']         = $user['role'];
    $_SESSION['_tpms_last_active'] = time();

    respond([
        'success'    => true,
        'message'    => 'Login successful.',
        'user'       => $sessionUser,
        'csrf_token' => $_SESSION['csrf_token'],
    ]);
}

// ── Logout ────────────────────────────────────────────────────
function handleLogout(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
    respond(['success' => true, 'message' => 'Logged out successfully.']);
}

// ── Register ──────────────────────────────────────────────────
function handleRegister(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        respond(['success' => false, 'message' => 'POST required'], 405);
    }

    $type = post('type'); // 'student' | 'company'

    if ($type === 'student') {
        $name            = post('name');
        $email           = strtolower(post('email'));
        $pass            = post('password');
        $confirmPass     = post('confirm_password');
        $phone           = post('phone');
        $dept            = post('department') ?: post('branch'); // support both keys
        $year            = post('year');
        $rollNumber      = post('roll_number');

        if (empty($name) || strlen($name) < 2)
            respond(['success' => false, 'message' => 'Full name is required (min 2 chars).'], 422);
        if (!isValidEmail($email))
            respond(['success' => false, 'message' => 'Invalid email address.'], 422);
        if (!isStrongPassword($pass))
            respond(['success' => false, 'message' => 'Password must be 8+ chars with 1 uppercase and 1 digit.'], 422);
        if ($pass !== $confirmPass)
            respond(['success' => false, 'message' => 'Passwords do not match.'], 422);

        $pdo = getPDO();

        // Duplicate email check
        $chk = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $chk->execute([$email]);
        if ($chk->fetch()) respond(['success' => false, 'message' => 'This email is already registered.'], 409);

        // Generate UID
        $uid  = 'STU' . date('Y') . rand(10000, 99999);
        $hash = password_hash($pass, PASSWORD_BCRYPT, ['cost' => 12]);

        $pdo->prepare("INSERT INTO users (uid,name,email,password_hash,role) VALUES (?,?,?,?,?)")
            ->execute([$uid, $name, $email, $hash, 'student']);
        $userId = (int)$pdo->lastInsertId();

        $pdo->prepare("INSERT INTO students (user_id,student_uid,branch,placement_status,profile_completion,phone,year,roll_number) VALUES (?,?,?,?,?,?,?,?)")
            ->execute([$userId, $uid, $dept, 'In Progress', 40, $phone, $year, $rollNumber]);

        respond(['success' => true, 'message' => 'Student account created successfully! Please log in.']);

    } elseif ($type === 'company') {
        $companyName = post('company_name');
        $hrName      = post('hr_name') ?: post('name'); // support both keys
        $email       = strtolower(post('email'));
        $pass        = post('password');
        $confirmPass = post('confirm_password');
        $phone       = post('phone');
        $website     = post('website');
        $location    = post('location');

        if (empty($companyName)) respond(['success' => false, 'message' => 'Company name is required.'], 422);
        if (empty($hrName))      respond(['success' => false, 'message' => 'HR contact name is required.'], 422);
        if (!isValidEmail($email)) respond(['success' => false, 'message' => 'Invalid email address.'], 422);
        if (!isStrongPassword($pass))
            respond(['success' => false, 'message' => 'Password must be 8+ chars with 1 uppercase and 1 digit.'], 422);
        if ($pass !== $confirmPass)
            respond(['success' => false, 'message' => 'Passwords do not match.'], 422);

        $pdo = getPDO();

        $chk = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $chk->execute([$email]);
        if ($chk->fetch()) respond(['success' => false, 'message' => 'This email is already registered.'], 409);

        $userUid  = 'CUSER' . rand(1000, 9999);
        $compUid  = 'COMP'  . rand(10000, 99999);
        $hash     = password_hash($pass, PASSWORD_BCRYPT, ['cost' => 12]);

        $pdo->prepare("INSERT INTO users (uid,name,email,password_hash,role) VALUES (?,?,?,?,?)")
            ->execute([$userUid, $hrName, $email, $hash, 'company']);
        $userId = (int)$pdo->lastInsertId();

        $pdo->prepare("INSERT INTO companies (comp_uid,user_id,name,website,industry,contact,hr_name,location,registered_date) VALUES (?,?,?,?,?,?,?,?,CURDATE())")
            ->execute([$compUid, $userId, $companyName, $website, 'Technology', $phone, $hrName, $location]);

        respond(['success' => true, 'message' => 'Company account created successfully! Please log in.']);

    } else {
        respond(['success' => false, 'message' => 'Invalid registration type.'], 422);
    }
}

// ── Update Profile ────────────────────────────────────────────
function handleUpdateProfile(): void
{
    $user = requireAuth();
    validateCSRF();

    $name  = post('name');
    $email = strtolower(post('email'));

    if (empty($name) || strlen($name) < 2)
        respond(['success' => false, 'message' => 'Name must be at least 2 characters.'], 422);
    if (!isValidEmail($email))
        respond(['success' => false, 'message' => 'Invalid email address.'], 422);

    $pdo = getPDO();

    // Check email taken by another user
    $chk = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $chk->execute([$email, $user['id']]);
    if ($chk->fetch()) respond(['success' => false, 'message' => 'Email already in use by another account.'], 409);

    $pdo->prepare("UPDATE users SET name=?, email=?, updated_at=NOW() WHERE id=?")
        ->execute([$name, $email, $user['id']]);

    // Update session
    $_SESSION['user']['name']  = $name;
    $_SESSION['user']['email'] = $email;
    $_SESSION['tpms_name']     = $name;
    $_SESSION['tpms_email']    = $email;

    respond(['success' => true, 'message' => 'Profile updated successfully.', 'name' => $name, 'email' => $email]);
}

// ── Change Password ───────────────────────────────────────────
function handleChangePassword(): void
{
    $user = requireAuth();
    validateCSRF();

    $current = post('current_password');
    $new     = post('new_password');
    $confirm = post('confirm_password');

    if (empty($current) || empty($new) || empty($confirm))
        respond(['success' => false, 'message' => 'All password fields are required.'], 422);
    if ($new !== $confirm)
        respond(['success' => false, 'message' => 'New passwords do not match.'], 422);
    if (!isStrongPassword($new))
        respond(['success' => false, 'message' => 'Password must be 8+ chars with 1 uppercase and 1 digit.'], 422);

    $pdo  = getPDO();
    $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
    $stmt->execute([$user['id']]);
    $row  = $stmt->fetch();

    if (!$row || !password_verify($current, $row['password_hash'])) {
        respond(['success' => false, 'message' => 'Current password is incorrect.'], 401);
    }

    $pdo->prepare("UPDATE users SET password_hash=?, updated_at=NOW() WHERE id=?")
        ->execute([password_hash($new, PASSWORD_BCRYPT, ['cost' => 12]), $user['id']]);

    respond(['success' => true, 'message' => 'Password changed successfully.']);
}

// ── Forgot Password ───────────────────────────────────────────
function handleForgotPassword(): void
{
    $email = strtolower(post('email'));
    if (!isValidEmail($email)) {
        respond(['success' => false, 'message' => 'Invalid email address.'], 422);
    }

    $pdo  = getPDO();
    $stmt = $pdo->prepare("SELECT id, name FROM users WHERE email = ? AND status = 1 LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
        // Generic success response to avoid email enumeration
        respond([
            'success' => true,
            'message' => 'If this email is registered, a reset link has been generated.',
        ]);
    }

    $token   = bin2hex(random_bytes(32));
    $expires = date('Y-m-d H:i:s', time() + 3600);

    // Save token in primary table
    $upd = $pdo->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE id = ?");
    $upd->execute([$token, $expires, $user['id']]);

    // Save to password_resets
    $ins = $pdo->prepare(
        "INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)
         ON DUPLICATE KEY UPDATE token = VALUES(token), expires_at = VALUES(expires_at), used = 0"
    );
    $ins->execute([$email, $token, $expires]);

    // Build reset link
    $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
    $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $base   = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
    $link   = "{$scheme}://{$host}" . rtrim(dirname($base), '/') . "/reset_password.php?token={$token}";

    respond([
        'success'  => true,
        'message'  => 'Password reset token generated successfully.',
        'dev_link' => $link
    ]);
}

// ── Helper: Build full session user payload ───────────────────
function buildSessionUser(PDO $pdo, array $user): array
{
    $payload = [
        'id'         => $user['id'],
        'uid'        => $user['uid'],
        'name'       => $user['name'],
        'email'      => $user['email'],
        'role'       => $user['role'],
        'avatar'     => $user['avatar'],
    ];

    if ($user['role'] === 'student') {
        $stu = $pdo->prepare(
            "SELECT s.*, GROUP_CONCAT(sk.skill ORDER BY sk.id SEPARATOR '|') AS skills_raw
             FROM students s
             LEFT JOIN student_skills sk ON sk.student_id = s.id
             WHERE s.user_id = ? GROUP BY s.id"
        );
        $stu->execute([$user['id']]);
        $stuRow = $stu->fetch();

        if ($stuRow) {
            $payload['student_db_id']      = (int)$stuRow['id'];
            $payload['student_uid']         = $stuRow['student_uid'];
            $payload['branch']              = $stuRow['branch'];
            $payload['cgpa']                = (float)$stuRow['cgpa'];
            $payload['backlogs']            = (int)$stuRow['backlogs'];
            $payload['placement_status']    = $stuRow['placement_status'];
            $payload['profile_completion']  = (int)$stuRow['profile_completion'];
            $payload['resume_name']         = $stuRow['resume_name'];
            $payload['skills']              = $stuRow['skills_raw'] ? explode('|', $stuRow['skills_raw']) : [];
        }
    } elseif ($user['role'] === 'company') {
        $comp = $pdo->prepare("SELECT * FROM companies WHERE user_id = ? LIMIT 1");
        $comp->execute([$user['id']]);
        $compRow = $comp->fetch();
        if ($compRow) {
            $payload['company_id']   = $compRow['id'];
            $payload['comp_uid']     = $compRow['comp_uid'];
            $payload['company_name'] = $compRow['name'];
            $payload['industry']     = $compRow['industry'];
            $payload['website']      = $compRow['website'];
        }
    }

    return $payload;
}
