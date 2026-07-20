<?php
/**
 * ============================================================
 *  TPMS — login.php  (v3.0)
 *  Multi-view Authentication Page
 *
 *  Views (via ?view=):
 *    login              – Sign in form (Admin / Student / Company)
 *    register-student   – Student registration form
 *    register-company   – Company registration form
 *    forgot-password    – Request password reset link
 *
 *  Security: CSRF, XSS prevention, prepared statements,
 *            password_hash/verify, session regeneration.
 * ============================================================
 */

require_once __DIR__ . '/includes/auth.php';

tpms_session_start();
seedDefaultUsers(); // backward-compat (now a no-op stub)

// Already logged in → jump to dashboard
if (!empty($_SESSION['tpms_role'])) {
    redirect('index.php');
}

// ── View routing ──────────────────────────────────────────────
$allowedViews = ['login', 'register-student', 'register-company', 'forgot-password'];
$view = $_GET['view'] ?? 'login';
if (!in_array($view, $allowedViews, true)) $view = 'login';

// ── State ─────────────────────────────────────────────────────
$error   = '';
$success = '';
$devLink = '';
$old     = [];

// System messages from redirects
$sysMsg = [
    'loggedout'       => ['ok', 'You have been signed out successfully.'],
    'session_expired' => ['err', 'Your session expired. Please sign in again.'],
    'unauthorized'    => ['err', 'Access denied. Please log in with the correct account.'],
    'registered'      => ['ok',  'Registration successful! Please sign in.'],
];
if (isset($_GET['msg']) && isset($sysMsg[$_GET['msg']])) {
    [$type, $txt] = $sysMsg[$_GET['msg']];
    if ($type === 'ok') $success = $txt; else $error = $txt;
}
if (isset($_GET['error']) && isset($sysMsg[$_GET['error']])) {
    [$type, $txt] = $sysMsg[$_GET['error']];
    if ($type === 'ok') $success = $txt; else $error = $txt;
}

// ── POST Handler ──────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCSRF();
    $old    = $_POST;
    $action = $_POST['_action'] ?? $view;

    switch ($action) {

        // ── Login ─────────────────────────────────────────────
        case 'do_login':
            $email = trim($_POST['email'] ?? '');
            $pass  = $_POST['password']   ?? '';
            $role  = trim($_POST['role']  ?? '');

            if (!in_array($role, ['admin', 'student', 'company'], true)) {
                $error = 'Please select a valid role.';
            } else {
                $res = loginUser($email, $pass, $role);
                if ($res['success']) {
                    redirect('index.php');
                }
                $error = $res['message'];
                $view  = 'login';
            }
            break;

        // ── Student Registration ───────────────────────────────
        case 'do_register_student':
            $res = registerStudent($_POST);
            if ($res['success']) {
                $old     = [];
                $view    = 'login';
                $success = $res['message'];
            } else {
                $error = $res['message'];
                $view  = 'register-student';
            }
            break;

        // ── Company Registration ───────────────────────────────
        case 'do_register_company':
            $res = registerCompany($_POST);
            if ($res['success']) {
                $old     = [];
                $view    = 'login';
                $success = $res['message'];
            } else {
                $error = $res['message'];
                $view  = 'register-company';
            }
            break;

        // ── Forgot Password ───────────────────────────────────
        case 'do_forgot_password':
            $email = trim($_POST['email'] ?? '');
            $res   = initiateForgotPassword($email);
            if ($res['success']) {
                $success = $res['message'];
                if (!empty($res['dev_link'])) $devLink = $res['dev_link'];
            } else {
                $error = $res['message'];
            }
            $view = 'forgot-password';
            break;
    }
}

// ── Helpers ───────────────────────────────────────────────────
$csrf = generateCSRF();

// Return old input value (HTML-escaped)
function old(string $key, string $default = ''): string
{
    global $old;
    return htmlspecialchars($old[$key] ?? $default, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

// Card width
$cardWide = in_array($view, ['register-student', 'register-company']);

// View meta
$viewMeta = [
    'login'            => ['title' => 'Welcome Back',     'sub' => 'Sign in to your TPMS account'],
    'register-student' => ['title' => 'Student Register',  'sub' => 'Create your student account'],
    'register-company' => ['title' => 'Company Register',  'sub' => 'Create your company account'],
    'forgot-password'  => ['title' => 'Reset Password',    'sub' => 'Enter your registered email'],
];
$meta = $viewMeta[$view];
?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="TPMS Authentication — Sign in or register for the Training & Placement Management System." />
    <title><?= htmlspecialchars($meta['title']) ?> — TPMS</title>

    <!-- Existing project stylesheet -->
    <link rel="stylesheet" href="styles.css" />
    <!-- Google Fonts (Inter) -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet" />
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>

    <style>
        /* ── Auth page shell ───────────────────────────────── */
        *, *::before, *::after { box-sizing: border-box; }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
            font-family: 'Inter', sans-serif;
            background: var(--bg-base, #0f1117);
            color: var(--text-primary, #f1f5f9);
        }

        /* ── Page wrapper ──────────────────────────────────── */
        .auth-page {
            width: 100%;
            max-width: <?= $cardWide ? '560px' : '440px' ?>;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1.5rem;
        }

        /* ── Brand header ──────────────────────────────────── */
        .auth-brand {
            text-align: center;
        }
        .auth-brand .brand-logo {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 54px; height: 54px;
            background: linear-gradient(135deg, var(--primary, #7c3aed), var(--primary-light, #a78bfa));
            border-radius: 14px;
            margin-bottom: .6rem;
            box-shadow: 0 8px 24px rgba(124,58,237,.35);
        }
        .auth-brand h1 {
            font-size: 1.05rem;
            font-weight: 800;
            letter-spacing: .08em;
            text-transform: uppercase;
            background: linear-gradient(135deg, var(--primary, #7c3aed), var(--primary-light, #a78bfa));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin: 0 0 .2rem;
        }
        .auth-brand p {
            font-size: .75rem;
            color: var(--text-faint, #64748b);
            margin: 0;
            letter-spacing: .04em;
        }

        /* ── Auth card ─────────────────────────────────────── */
        .auth-card {
            width: 100%;
            background: var(--bg-card, #1a1d2e);
            border: 1px solid var(--border-color, rgba(255,255,255,.08));
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 25px 60px rgba(0,0,0,.45);
        }

        /* ── Card header ───────────────────────────────────── */
        .auth-card-header { text-align: center; margin-bottom: 1.5rem; }
        .auth-card-header h2 {
            font-size: 1.3rem; font-weight: 800; margin: 0 0 .3rem;
            color: var(--text-primary, #f1f5f9);
        }
        .auth-card-header p {
            font-size: .8rem; color: var(--text-faint, #64748b); margin: 0;
        }

        /* ── Alerts ────────────────────────────────────────── */
        .alert {
            padding: .7rem 1rem; border-radius: 10px;
            font-size: .8rem; font-weight: 500;
            display: flex; align-items: flex-start; gap: .5rem;
            margin-bottom: 1.25rem; line-height: 1.5;
        }
        .alert-error {
            background: rgba(239,68,68,.12);
            border: 1px solid rgba(239,68,68,.3);
            color: #f87171;
        }
        .alert-success {
            background: rgba(34,197,94,.1);
            border: 1px solid rgba(34,197,94,.25);
            color: #4ade80;
        }
        .alert-dev {
            background: rgba(251,191,36,.1);
            border: 1px solid rgba(251,191,36,.25);
            color: #fbbf24;
        }

        /* ── Role tabs ─────────────────────────────────────── */
        .role-tabs {
            display: flex;
            background: var(--bg-subtle, rgba(255,255,255,.04));
            border: 1px solid var(--border-color, rgba(255,255,255,.08));
            border-radius: 12px;
            padding: 4px;
            margin-bottom: 1.5rem;
            gap: 4px;
        }
        .role-tab {
            flex: 1; padding: .45rem .5rem; border: none; border-radius: 9px;
            cursor: pointer; font-size: .78rem; font-weight: 600;
            font-family: inherit;
            transition: all .2s ease;
            background: transparent;
            color: var(--text-faint, #64748b);
            display: flex; align-items: center; justify-content: center; gap: .3rem;
        }
        .role-tab.active, .role-tab:hover {
            background: var(--bg-card, #1a1d2e);
            color: var(--text-primary, #f1f5f9);
            box-shadow: 0 2px 8px rgba(0,0,0,.3);
        }
        .role-tab.active {
            background: var(--primary, #7c3aed);
            color: #fff;
        }

        /* ── Form elements ─────────────────────────────────── */
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: .85rem; }
        @media (max-width: 480px) { .form-row { grid-template-columns: 1fr; } }

        .field { margin-bottom: 1rem; }
        .field label {
            display: block; font-size: .75rem; font-weight: 600;
            color: var(--text-secondary, #94a3b8); margin-bottom: .35rem;
            letter-spacing: .03em;
        }
        .field .req { color: var(--danger, #f87171); margin-left: 2px; }

        .input-wrap { position: relative; }
        .input-wrap .icon {
            position: absolute; left: .85rem; top: 50%;
            transform: translateY(-50%); pointer-events: none;
            color: var(--text-faint, #64748b);
        }
        .input-wrap input, .input-wrap select {
            width: 100%; padding: .65rem .85rem .65rem 2.5rem;
            border-radius: 10px;
            border: 1px solid var(--border-color, rgba(255,255,255,.1));
            background: var(--bg-input, rgba(255,255,255,.05));
            color: var(--text-primary, #f1f5f9);
            font-size: .85rem; font-family: inherit;
            outline: none; transition: border-color .2s, box-shadow .2s;
        }
        .input-wrap input::placeholder { color: var(--text-faint, #475569); }
        .input-wrap input:focus, .input-wrap select:focus {
            border-color: var(--primary, #7c3aed);
            box-shadow: 0 0 0 3px rgba(124,58,237,.18);
        }
        .input-wrap input.error { border-color: #ef4444; }

        .input-wrap .eye-btn {
            position: absolute; right: .75rem; top: 50%;
            transform: translateY(-50%);
            background: none; border: none; cursor: pointer;
            color: var(--text-faint, #64748b);
            padding: 2px; line-height: 0;
            transition: color .2s;
        }
        .input-wrap .eye-btn:hover { color: var(--text-primary, #f1f5f9); }

        /* No-icon input (no left padding) */
        .input-wrap.no-icon input, .input-wrap.no-icon select {
            padding-left: .85rem;
        }

        /* ── Password strength bar ─────────────────────────── */
        .strength-bar { display: flex; gap: 4px; margin-top: .35rem; }
        .strength-bar span {
            flex: 1; height: 3px; border-radius: 99px;
            background: var(--border-color, rgba(255,255,255,.08));
            transition: background .3s;
        }
        .strength-label { font-size: .68rem; color: var(--text-faint, #64748b); margin-top: .2rem; }

        /* ── Forgot link ───────────────────────────────────── */
        .forgot-link {
            display: block; text-align: right; font-size: .75rem;
            color: var(--primary, #7c3aed); text-decoration: none;
            margin-top: .25rem; margin-bottom: -.5rem;
        }
        .forgot-link:hover { text-decoration: underline; }

        /* ── Submit button ─────────────────────────────────── */
        .btn-auth {
            width: 100%; padding: .75rem 1rem; border: none; border-radius: 12px;
            background: linear-gradient(135deg, var(--primary, #7c3aed), #9333ea);
            color: #fff; font-size: .9rem; font-weight: 700;
            font-family: inherit; cursor: pointer; margin-top: 1.25rem;
            transition: all .2s ease;
            box-shadow: 0 4px 15px rgba(124,58,237,.35);
            display: flex; align-items: center; justify-content: center; gap: .5rem;
        }
        .btn-auth:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 25px rgba(124,58,237,.5);
        }
        .btn-auth:active { transform: translateY(0); }

        /* ── Divider ───────────────────────────────────────── */
        .divider {
            display: flex; align-items: center; gap: .75rem;
            font-size: .72rem; color: var(--text-faint, #64748b);
            margin: 1.25rem 0;
        }
        .divider::before, .divider::after {
            content: ''; flex: 1; height: 1px;
            background: var(--border-color, rgba(255,255,255,.08));
        }

        /* ── Auth nav links ────────────────────────────────── */
        .auth-footer { text-align: center; margin-top: 1.25rem; }
        .auth-footer p { font-size: .78rem; color: var(--text-faint, #64748b); margin: .35rem 0; }
        .auth-footer a { color: var(--primary, #7c3aed); text-decoration: none; font-weight: 600; }
        .auth-footer a:hover { text-decoration: underline; }
        .auth-footer .sep { margin: 0 .4rem; color: var(--border-color, rgba(255,255,255,.15)); }

        /* ── Demo credentials block ────────────────────────── */
        .demo-creds {
            background: var(--bg-subtle, rgba(255,255,255,.03));
            border: 1px solid var(--border-color, rgba(255,255,255,.06));
            border-radius: 12px; padding: .85rem 1rem; margin-top: .5rem;
        }
        .demo-creds p { margin: 0 0 .5rem; font-size: .72rem; font-weight: 700; color: var(--text-faint, #64748b); letter-spacing: .08em; text-transform: uppercase; }
        .demo-creds table { width: 100%; border-collapse: collapse; font-size: .73rem; }
        .demo-creds td { padding: .18rem .3rem; color: var(--text-secondary, #94a3b8); }
        .demo-creds td:first-child { color: var(--text-faint, #64748b); width: 5rem; }
        .demo-creds code { background: var(--bg-hover, rgba(255,255,255,.07)); border-radius: 4px; padding: 1px 5px; color: var(--primary, #a78bfa); font-size: .7rem; }

        /* ── Responsive ────────────────────────────────────── */
        @media (max-width: 500px) {
            .auth-card { padding: 1.5rem 1.25rem; border-radius: 16px; }
        }
    </style>
</head>
<body>
<div class="auth-page">

    <!-- ── Brand ─────────────────────────────────────────────── -->
    <div class="auth-brand">
        <div class="brand-logo">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/>
            </svg>
        </div>
        <h1>TPMS</h1>
        <p>Training &amp; Placement Management System</p>
    </div>

    <!-- ── Auth Card ─────────────────────────────────────────── -->
    <div class="auth-card">
        <div class="auth-card-header">
            <h2><?= htmlspecialchars($meta['title']) ?></h2>
            <p><?= htmlspecialchars($meta['sub']) ?></p>
        </div>

        <?php if ($error): ?>
        <div class="alert alert-error" role="alert">
            <i data-lucide="alert-circle" style="width:16px;height:16px;flex-shrink:0;margin-top:1px"></i>
            <span><?= htmlspecialchars($error) ?></span>
        </div>
        <?php endif; ?>

        <?php if ($success): ?>
        <div class="alert alert-success" role="alert">
            <i data-lucide="check-circle-2" style="width:16px;height:16px;flex-shrink:0;margin-top:1px"></i>
            <span><?= htmlspecialchars($success) ?></span>
        </div>
        <?php endif; ?>

        <?php if ($devLink): ?>
        <div class="alert alert-dev" role="alert">
            <i data-lucide="terminal" style="width:16px;height:16px;flex-shrink:0;margin-top:1px"></i>
            <span><strong>Dev Reset Link:</strong><br><a href="<?= htmlspecialchars($devLink) ?>" style="color:#fbbf24;word-break:break-all"><?= htmlspecialchars($devLink) ?></a></span>
        </div>
        <?php endif; ?>

        <!-- ════════════════════════════════════════════════════
             VIEW: LOGIN
        ════════════════════════════════════════════════════ -->
        <?php if ($view === 'login'): ?>
        <form method="POST" action="login.php" id="loginForm" novalidate>
            <input type="hidden" name="_action"    value="do_login" />
            <input type="hidden" name="csrf_token" value="<?= $csrf ?>" />
            <input type="hidden" name="role"       id="selectedRole" value="<?= old('role', 'student') ?>" />

            <!-- Role Tabs -->
            <div class="role-tabs" role="tablist">
                <button type="button" class="role-tab <?= old('role','student')==='admin'?'active':'' ?>"
                        onclick="selectRole('admin',this)" role="tab" aria-label="Admin login">
                    <i data-lucide="shield" style="width:13px;height:13px"></i> Admin
                </button>
                <button type="button" class="role-tab <?= (old('role','student')==='student'||old('role','')==='')?'active':'' ?>"
                        onclick="selectRole('student',this)" role="tab" aria-label="Student login">
                    <i data-lucide="graduation-cap" style="width:13px;height:13px"></i> Student
                </button>
                <button type="button" class="role-tab <?= old('role','student')==='company'?'active':'' ?>"
                        onclick="selectRole('company',this)" role="tab" aria-label="Company login">
                    <i data-lucide="building-2" style="width:13px;height:13px"></i> Company
                </button>
            </div>

            <!-- Email -->
            <div class="field">
                <label for="login_email">Email Address <span class="req">*</span></label>
                <div class="input-wrap">
                    <i data-lucide="mail" style="width:15px;height:15px" class="icon"></i>
                    <input type="email" id="login_email" name="email"
                           value="<?= old('email') ?>"
                           placeholder="you@example.com"
                           autocomplete="email" required />
                </div>
            </div>

            <!-- Password -->
            <div class="field">
                <label for="login_password">Password <span class="req">*</span></label>
                <a href="login.php?view=forgot-password" class="forgot-link">Forgot password?</a>
                <div class="input-wrap">
                    <i data-lucide="lock" style="width:15px;height:15px" class="icon"></i>
                    <input type="password" id="login_password" name="password"
                           placeholder="••••••••" autocomplete="current-password" required />
                    <button type="button" class="eye-btn" onclick="togglePassword('login_password',this)" aria-label="Toggle password visibility">
                        <i data-lucide="eye" style="width:15px;height:15px"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-auth" id="loginBtn">
                <i data-lucide="log-in" style="width:17px;height:17px"></i>
                Sign In to Dashboard
            </button>
        </form>

        <!-- Demo credentials -->
        <div class="divider">Demo Accounts</div>
        <div class="demo-creds">
            <p>Default Login Credentials</p>
            <table>
                <tr><td>Admin</td>    <td><code>admin@tpms.com</code> / <code>Admin@123</code></td></tr>
                <tr><td>Student</td>  <td><code>student@tpms.com</code> / <code>Student@123</code></td></tr>
                <tr><td>Company</td>  <td><code>company@tpms.com</code> / <code>Company@123</code></td></tr>
            </table>
        </div>

        <!-- Footer nav -->
        <div class="auth-footer">
            <p>New user?
                <a href="login.php?view=register-student">Register as Student</a>
                <span class="sep">·</span>
                <a href="login.php?view=register-company">Register as Company</a>
            </p>
        </div>

        <!-- ════════════════════════════════════════════════════
             VIEW: STUDENT REGISTRATION
        ════════════════════════════════════════════════════ -->
        <?php elseif ($view === 'register-student'): ?>
        <form method="POST" action="login.php?view=register-student" id="stuRegForm" novalidate>
            <input type="hidden" name="_action"    value="do_register_student" />
            <input type="hidden" name="csrf_token" value="<?= $csrf ?>" />

            <div class="form-row">
                <!-- Full Name -->
                <div class="field">
                    <label for="stu_name">Full Name <span class="req">*</span></label>
                    <div class="input-wrap">
                        <i data-lucide="user" style="width:15px;height:15px" class="icon"></i>
                        <input type="text" id="stu_name" name="name"
                               value="<?= old('name') ?>"
                               placeholder="Ajit Kumar" autocomplete="name" required />
                    </div>
                </div>

                <!-- Roll Number -->
                <div class="field">
                    <label for="stu_roll">Roll Number</label>
                    <div class="input-wrap">
                        <i data-lucide="hash" style="width:15px;height:15px" class="icon"></i>
                        <input type="text" id="stu_roll" name="roll_number"
                               value="<?= old('roll_number') ?>"
                               placeholder="2021CSE001" />
                    </div>
                </div>
            </div>

            <!-- Email -->
            <div class="field">
                <label for="stu_email">Email Address <span class="req">*</span></label>
                <div class="input-wrap">
                    <i data-lucide="mail" style="width:15px;height:15px" class="icon"></i>
                    <input type="email" id="stu_email" name="email"
                           value="<?= old('email') ?>"
                           placeholder="student@college.edu" autocomplete="email" required />
                </div>
            </div>

            <div class="form-row">
                <!-- Department -->
                <div class="field">
                    <label for="stu_dept">Department</label>
                    <div class="input-wrap">
                        <i data-lucide="book-open" style="width:15px;height:15px" class="icon"></i>
                        <input type="text" id="stu_dept" name="department"
                               value="<?= old('department') ?>"
                               placeholder="Computer Science" />
                    </div>
                </div>

                <!-- Year -->
                <div class="field">
                    <label for="stu_year">Year</label>
                    <div class="input-wrap no-icon">
                        <select id="stu_year" name="year">
                            <option value="" <?= old('year')===''?'selected':'' ?>>— Select Year —</option>
                            <?php foreach(['1st Year','2nd Year','3rd Year','4th Year','Graduated'] as $y): ?>
                            <option value="<?= $y ?>" <?= old('year')===$y?'selected':'' ?>><?= $y ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Phone -->
            <div class="field">
                <label for="stu_phone">Phone Number</label>
                <div class="input-wrap">
                    <i data-lucide="phone" style="width:15px;height:15px" class="icon"></i>
                    <input type="tel" id="stu_phone" name="phone"
                           value="<?= old('phone') ?>"
                           placeholder="+91 98765 43210" />
                </div>
            </div>

            <div class="form-row">
                <!-- Password -->
                <div class="field">
                    <label for="stu_pass">Password <span class="req">*</span></label>
                    <div class="input-wrap">
                        <i data-lucide="lock" style="width:15px;height:15px" class="icon"></i>
                        <input type="password" id="stu_pass" name="password"
                               placeholder="Min 8 chars" autocomplete="new-password" required
                               oninput="checkStrength(this)" />
                        <button type="button" class="eye-btn" onclick="togglePassword('stu_pass',this)">
                            <i data-lucide="eye" style="width:14px;height:14px"></i>
                        </button>
                    </div>
                    <div class="strength-bar" id="strengthBar">
                        <span id="s1"></span><span id="s2"></span>
                        <span id="s3"></span><span id="s4"></span>
                    </div>
                    <div class="strength-label" id="strengthLabel">Password strength</div>
                </div>

                <!-- Confirm Password -->
                <div class="field">
                    <label for="stu_confirm">Confirm Password <span class="req">*</span></label>
                    <div class="input-wrap">
                        <i data-lucide="lock-keyhole" style="width:15px;height:15px" class="icon"></i>
                        <input type="password" id="stu_confirm" name="confirm_password"
                               placeholder="Repeat password" autocomplete="new-password" required />
                        <button type="button" class="eye-btn" onclick="togglePassword('stu_confirm',this)">
                            <i data-lucide="eye" style="width:14px;height:14px"></i>
                        </button>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn-auth">
                <i data-lucide="user-plus" style="width:17px;height:17px"></i>
                Create Student Account
            </button>
        </form>

        <div class="auth-footer">
            <p>Already have an account? <a href="login.php?view=login">Sign In</a></p>
            <p><a href="login.php?view=register-company">Register as Company instead →</a></p>
        </div>

        <!-- ════════════════════════════════════════════════════
             VIEW: COMPANY REGISTRATION
        ════════════════════════════════════════════════════ -->
        <?php elseif ($view === 'register-company'): ?>
        <form method="POST" action="login.php?view=register-company" id="compRegForm" novalidate>
            <input type="hidden" name="_action"    value="do_register_company" />
            <input type="hidden" name="csrf_token" value="<?= $csrf ?>" />

            <div class="form-row">
                <!-- Company Name -->
                <div class="field">
                    <label for="comp_name">Company Name <span class="req">*</span></label>
                    <div class="input-wrap">
                        <i data-lucide="building-2" style="width:15px;height:15px" class="icon"></i>
                        <input type="text" id="comp_name" name="company_name"
                               value="<?= old('company_name') ?>"
                               placeholder="Acme Corp" required />
                    </div>
                </div>

                <!-- HR Name -->
                <div class="field">
                    <label for="comp_hr">HR Contact Name</label>
                    <div class="input-wrap">
                        <i data-lucide="user-tie" style="width:15px;height:15px" class="icon"></i>
                        <input type="text" id="comp_hr" name="hr_name"
                               value="<?= old('hr_name') ?>"
                               placeholder="Priya Sharma" />
                    </div>
                </div>
            </div>

            <!-- Email -->
            <div class="field">
                <label for="comp_email">Official Email <span class="req">*</span></label>
                <div class="input-wrap">
                    <i data-lucide="mail" style="width:15px;height:15px" class="icon"></i>
                    <input type="email" id="comp_email" name="email"
                           value="<?= old('email') ?>"
                           placeholder="hr@company.com" autocomplete="email" required />
                </div>
            </div>

            <div class="form-row">
                <!-- Phone -->
                <div class="field">
                    <label for="comp_phone">Phone</label>
                    <div class="input-wrap">
                        <i data-lucide="phone" style="width:15px;height:15px" class="icon"></i>
                        <input type="tel" id="comp_phone" name="phone"
                               value="<?= old('phone') ?>"
                               placeholder="+91 98765 43210" />
                    </div>
                </div>

                <!-- Website -->
                <div class="field">
                    <label for="comp_web">Website</label>
                    <div class="input-wrap">
                        <i data-lucide="globe" style="width:15px;height:15px" class="icon"></i>
                        <input type="url" id="comp_web" name="website"
                               value="<?= old('website') ?>"
                               placeholder="https://company.com" />
                    </div>
                </div>
            </div>

            <!-- Location -->
            <div class="field">
                <label for="comp_loc">Location / City</label>
                <div class="input-wrap">
                    <i data-lucide="map-pin" style="width:15px;height:15px" class="icon"></i>
                    <input type="text" id="comp_loc" name="location"
                           value="<?= old('location') ?>"
                           placeholder="Mumbai, Maharashtra" />
                </div>
            </div>

            <div class="form-row">
                <!-- Password -->
                <div class="field">
                    <label for="comp_pass">Password <span class="req">*</span></label>
                    <div class="input-wrap">
                        <i data-lucide="lock" style="width:15px;height:15px" class="icon"></i>
                        <input type="password" id="comp_pass" name="password"
                               placeholder="Min 8 chars" autocomplete="new-password" required
                               oninput="checkStrength(this)" />
                        <button type="button" class="eye-btn" onclick="togglePassword('comp_pass',this)">
                            <i data-lucide="eye" style="width:14px;height:14px"></i>
                        </button>
                    </div>
                    <div class="strength-bar" id="strengthBar">
                        <span id="s1"></span><span id="s2"></span>
                        <span id="s3"></span><span id="s4"></span>
                    </div>
                    <div class="strength-label" id="strengthLabel">Password strength</div>
                </div>

                <!-- Confirm Password -->
                <div class="field">
                    <label for="comp_confirm">Confirm Password <span class="req">*</span></label>
                    <div class="input-wrap">
                        <i data-lucide="lock-keyhole" style="width:15px;height:15px" class="icon"></i>
                        <input type="password" id="comp_confirm" name="confirm_password"
                               placeholder="Repeat password" autocomplete="new-password" required />
                        <button type="button" class="eye-btn" onclick="togglePassword('comp_confirm',this)">
                            <i data-lucide="eye" style="width:14px;height:14px"></i>
                        </button>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn-auth">
                <i data-lucide="building-2" style="width:17px;height:17px"></i>
                Register Company Account
            </button>
        </form>

        <div class="auth-footer">
            <p>Already have an account? <a href="login.php?view=login">Sign In</a></p>
            <p><a href="login.php?view=register-student">Register as Student instead →</a></p>
        </div>

        <!-- ════════════════════════════════════════════════════
             VIEW: FORGOT PASSWORD
        ════════════════════════════════════════════════════ -->
        <?php elseif ($view === 'forgot-password'): ?>
        <p style="font-size:.8rem;color:var(--text-faint);text-align:center;margin-bottom:1.25rem;line-height:1.7">
            Enter your registered email address. A password reset link will be generated below.
            <br><em style="font-size:.72rem">(In production this would be sent to your inbox.)</em>
        </p>

        <form method="POST" action="login.php?view=forgot-password" id="forgotForm" novalidate>
            <input type="hidden" name="_action"    value="do_forgot_password" />
            <input type="hidden" name="csrf_token" value="<?= $csrf ?>" />

            <div class="field">
                <label for="forgot_email">Email Address <span class="req">*</span></label>
                <div class="input-wrap">
                    <i data-lucide="mail" style="width:15px;height:15px" class="icon"></i>
                    <input type="email" id="forgot_email" name="email"
                           value="<?= old('email') ?>"
                           placeholder="you@example.com" autocomplete="email" required />
                </div>
            </div>

            <button type="submit" class="btn-auth">
                <i data-lucide="send" style="width:17px;height:17px"></i>
                Generate Reset Link
            </button>
        </form>

        <div class="auth-footer">
            <p><a href="login.php?view=login">← Back to Sign In</a></p>
        </div>

        <?php endif; ?>
    </div><!-- /.auth-card -->

</div><!-- /.auth-page -->

<!-- ── Scripts ─────────────────────────────────────────────── -->
<script>
    // Init Lucide icons
    if (typeof lucide !== 'undefined') lucide.createIcons();

    // ── Role tab selection ────────────────────────────────────
    function selectRole(role, btn) {
        document.getElementById('selectedRole').value = role;
        document.querySelectorAll('.role-tab').forEach(t => t.classList.remove('active'));
        btn.classList.add('active');
    }

    // ── Password show/hide toggle ─────────────────────────────
    function togglePassword(fieldId, btn) {
        const input = document.getElementById(fieldId);
        if (!input) return;
        const isText = input.type === 'text';
        input.type = isText ? 'password' : 'text';
        const icon = btn.querySelector('i');
        if (icon) {
            icon.setAttribute('data-lucide', isText ? 'eye' : 'eye-off');
            lucide.createIcons();
        }
    }

    // ── Password strength meter ───────────────────────────────
    function checkStrength(input) {
        const v  = input.value;
        const s1 = document.getElementById('s1');
        const s2 = document.getElementById('s2');
        const s3 = document.getElementById('s3');
        const s4 = document.getElementById('s4');
        const lbl= document.getElementById('strengthLabel');
        if (!s1) return;

        let score = 0;
        if (v.length >= 8)            score++;
        if (/[A-Z]/.test(v))          score++;
        if (/[0-9]/.test(v))          score++;
        if (/[^A-Za-z0-9]/.test(v))   score++;

        const colors = ['transparent','#ef4444','#f97316','#eab308','#22c55e'];
        const labels = ['','Weak','Fair','Good','Strong'];
        [s1,s2,s3,s4].forEach((el, i) => {
            el.style.background = i < score ? colors[score] : 'var(--border-color,rgba(255,255,255,.08))';
        });
        lbl.textContent = labels[score] || 'Password strength';
        lbl.style.color = colors[score] || 'var(--text-faint,#64748b)';
    }

    // ── Client-side validation before submit ──────────────────
    const stuForm  = document.getElementById('stuRegForm');
    const compForm = document.getElementById('compRegForm');
    const loginForm= document.getElementById('loginForm');

    if (stuForm) {
        stuForm.addEventListener('submit', function(e) {
            const pass = document.getElementById('stu_pass').value;
            const conf = document.getElementById('stu_confirm').value;
            if (pass !== conf) {
                e.preventDefault();
                showInlineError('Passwords do not match.');
            } else if (pass.length < 8) {
                e.preventDefault();
                showInlineError('Password must be at least 8 characters.');
            }
        });
    }

    if (compForm) {
        compForm.addEventListener('submit', function(e) {
            const pass = document.getElementById('comp_pass').value;
            const conf = document.getElementById('comp_confirm').value;
            if (pass !== conf) {
                e.preventDefault();
                showInlineError('Passwords do not match.');
            } else if (pass.length < 8) {
                e.preventDefault();
                showInlineError('Password must be at least 8 characters.');
            }
        });
    }

    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            const role = document.getElementById('selectedRole').value;
            if (!role) {
                e.preventDefault();
                showInlineError('Please select a role (Admin, Student, or Company).');
            }
        });
    }

    function showInlineError(msg) {
        let existing = document.querySelector('.alert-js');
        if (existing) existing.remove();
        const div = document.createElement('div');
        div.className = 'alert alert-error alert-js';
        div.innerHTML = '<i data-lucide="alert-circle" style="width:16px;height:16px;flex-shrink:0;margin-top:1px"></i><span>' + msg + '</span>';
        const card = document.querySelector('.auth-card');
        const header = card.querySelector('.auth-card-header');
        card.insertBefore(div, header.nextSibling);
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    // ── Loading state on submit ───────────────────────────────
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function() {
            const btn = form.querySelector('.btn-auth');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<svg style="width:16px;height:16px;animation:spin 1s linear infinite" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12a9 9 0 1 1-6.22-8.56"/></svg> Please wait…';
            }
        });
    });
</script>
<style>
    @keyframes spin { to { transform: rotate(360deg); } }
</style>
</body>
</html>
