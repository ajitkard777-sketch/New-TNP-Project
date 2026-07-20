<?php
/**
 * ============================================================
 *  TPMS — reset_password.php
 *  Secure password reset page.
 *
 *  Verifies the token from the URL against users & password_resets tables.
 *  Allows updating the password securely with bcrypt hashing.
 * ============================================================
 */

require_once __DIR__ . '/includes/auth.php';

tpms_session_start();

$error   = '';
$success = '';
$token   = $_GET['token'] ?? '';
$user    = null;

if (empty($token)) {
    $error = 'Reset token is missing from the URL.';
} else {
    // Verify token validity
    $user = getResetUser($token);
    if (!$user) {
        $error = 'The password reset link is invalid or has expired.';
    }
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $user) {
    verifyCSRF();

    $newPass = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    $res = resetPassword($token, $newPass, $confirm);
    if ($res['success']) {
        $success = $res['message'];
        $user = null; // hide form on success
    } else {
        $error = $res['message'];
    }
}

$csrf = generateCSRF();
?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Reset Password — TPMS</title>
    <!-- CSS -->
    <link rel="stylesheet" href="styles.css" />
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>

    <style>
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
        .auth-page {
            width: 100%;
            max-width: 440px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1.5rem;
        }
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
        }
        .auth-card {
            width: 100%;
            background: var(--bg-card, #1a1d2e);
            border: 1px solid var(--border-color, rgba(255,255,255,.08));
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 25px 60px rgba(0,0,0,.45);
        }
        .auth-card-header { text-align: center; margin-bottom: 1.5rem; }
        .auth-card-header h2 {
            font-size: 1.3rem; font-weight: 800; margin: 0 0 .3rem;
            color: var(--text-primary, #f1f5f9);
        }
        .auth-card-header p {
            font-size: .8rem; color: var(--text-faint, #64748b); margin: 0;
        }
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
        .field { margin-bottom: 1.25rem; }
        .field label {
            display: block; font-size: .75rem; font-weight: 600;
            color: var(--text-secondary, #94a3b8); margin-bottom: .35rem;
        }
        .field .req { color: var(--danger, #f87171); margin-left: 2px; }
        .input-wrap { position: relative; }
        .input-wrap .icon {
            position: absolute; left: .85rem; top: 50%;
            transform: translateY(-50%); pointer-events: none;
            color: var(--text-faint, #64748b);
        }
        .input-wrap input {
            width: 100%; padding: .65rem .85rem .65rem 2.5rem;
            border-radius: 10px;
            border: 1px solid var(--border-color, rgba(255,255,255,.1));
            background: var(--bg-input, rgba(255,255,255,.05));
            color: var(--text-primary, #f1f5f9);
            font-size: .85rem; font-family: inherit;
            outline: none; transition: border-color .2s, box-shadow .2s;
        }
        .input-wrap input:focus {
            border-color: var(--primary, #7c3aed);
            box-shadow: 0 0 0 3px rgba(124,58,237,.18);
        }
        .input-wrap .eye-btn {
            position: absolute; right: .75rem; top: 50%;
            transform: translateY(-50%);
            background: none; border: none; cursor: pointer;
            color: var(--text-faint, #64748b);
            padding: 2px; line-height: 0;
        }
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
        .btn-auth:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }
        .auth-footer { text-align: center; margin-top: 1.25rem; }
        .auth-footer a { color: var(--primary, #7c3aed); text-decoration: none; font-weight: 600; }
        .auth-footer a:hover { text-decoration: underline; }
    </style>
</head>
<body>
<div class="auth-page">

    <div class="auth-brand">
        <div class="brand-logo">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/>
            </svg>
        </div>
        <h1>TPMS</h1>
        <p>Training &amp; Placement Management System</p>
    </div>

    <div class="auth-card">
        <div class="auth-card-header">
            <h2>Reset Password</h2>
            <?php if ($user): ?>
                <p>Set a new password for <?= htmlspecialchars($user['email']) ?></p>
            <?php else: ?>
                <p>Password reset request status</p>
            <?php endif; ?>
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

        <?php if ($user): ?>
        <form method="POST" id="resetForm" novalidate>
            <input type="hidden" name="csrf_token" value="<?= $csrf ?>" />

            <!-- New Password -->
            <div class="field">
                <label for="password">New Password <span class="req">*</span></label>
                <div class="input-wrap">
                    <i data-lucide="lock" style="width:15px;height:15px" class="icon"></i>
                    <input type="password" id="password" name="password"
                           placeholder="At least 8 characters, 1 upper, 1 number" required />
                    <button type="button" class="eye-btn" onclick="togglePassword('password', this)" aria-label="Toggle visibility">
                        <i data-lucide="eye" style="width:15px;height:15px"></i>
                    </button>
                </div>
            </div>

            <!-- Confirm Password -->
            <div class="field">
                <label for="confirm_password">Confirm Password <span class="req">*</span></label>
                <div class="input-wrap">
                    <i data-lucide="lock-keyhole" style="width:15px;height:15px" class="icon"></i>
                    <input type="password" id="confirm_password" name="confirm_password"
                           placeholder="Repeat the new password" required />
                    <button type="button" class="eye-btn" onclick="togglePassword('confirm_password', this)" aria-label="Toggle visibility">
                        <i data-lucide="eye" style="width:15px;height:15px"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-auth">
                <i data-lucide="check" style="width:17px;height:17px"></i>
                Update Password
            </button>
        </form>
        <?php endif; ?>

        <div class="auth-footer">
            <a href="login.php">Back to Sign In</a>
        </div>
    </div>

</div>

<script>
    if (typeof lucide !== 'undefined') lucide.createIcons();

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

    const resetForm = document.getElementById('resetForm');
    if (resetForm) {
        resetForm.addEventListener('submit', function(e) {
            const pass = document.getElementById('password').value;
            const conf = document.getElementById('confirm_password').value;
            if (pass.length < 8) {
                e.preventDefault();
                showError('Password must be at least 8 characters.');
            } else if (!/[A-Z]/.test(pass) || !/[0-9]/.test(pass)) {
                e.preventDefault();
                showError('Password must contain at least one uppercase letter and one number.');
            } else if (pass !== conf) {
                e.preventDefault();
                showError('Passwords do not match.');
            }
        });
    }

    function showError(msg) {
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
</script>
</body>
</html>
