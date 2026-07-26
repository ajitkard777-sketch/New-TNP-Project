<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php require_once __DIR__ . '/../../includes/auth_theme_head.php'; ?>
    <title>Verify Email - <?= APP_NAME ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="<?= asset('css/style.css') ?>" rel="stylesheet">
    <link href="<?= asset('css/dark-mode.css') ?>" rel="stylesheet">
    <link href="<?= asset('css/auth.css') ?>" rel="stylesheet">
    <style>
        .otp-inputs {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin: 24px 0;
        }
        .otp-input {
            width: 48px;
            height: 56px;
            font-size: 24px;
            font-weight: 700;
            text-align: center;
            border: 2px solid #cbd5e1;
            border-radius: 12px;
            background: #f8fafc;
            color: #1e293b;
            transition: all 0.2s ease;
        }
        .otp-input:focus {
            border-color: #4f46e5;
            background: #ffffff;
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.15);
            outline: none;
        }
        .otp-email-badge {
            background: rgba(79, 70, 229, 0.08);
            color: #4f46e5;
            border-radius: 50px;
            padding: 6px 16px;
            font-size: 0.88rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-top: 8px;
        }
        .timer-badge {
            font-size: 0.82rem;
            color: #64748b;
            font-weight: 500;
        }
        .resend-container {
            margin-top: 20px;
            padding-top: 16px;
            border-top: 1px dashed #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 0.88rem;
        }
        .btn-resend {
            background: none;
            border: none;
            color: #4f46e5;
            font-weight: 600;
            cursor: pointer;
            padding: 0;
            text-decoration: underline;
            transition: opacity 0.2s;
        }
        .btn-resend:disabled {
            color: #94a3b8;
            cursor: not-allowed;
            text-decoration: none;
        }
    </style>
</head>
<body>
<div class="auth-wrapper">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-logo text-center">
                <div class="auth-logo-icon" style="background: linear-gradient(135deg,#4f46e5,#3b82f6); color:#fff; width:64px; height:64px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; font-size:26px; margin-bottom:12px; box-shadow:0 8px 20px rgba(79,70,229,0.3);">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h2 class="fw-extrabold text-dark mb-1">OTP Verification</h2>
                <p class="text-muted small mb-2">We have sent a 6-digit verification code to</p>
                <div class="otp-email-badge">
                    <i class="fas fa-envelope"></i>
                    <span><?= htmlspecialchars($verifyEmail ?? 'your registered email') ?></span>
                </div>
            </div>

            <!-- Dynamic Alert Box -->
            <div id="alert-container" class="mt-3">
                <?php $flash = getFlash(); ?>
                <?php if ($flash): ?>
                <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show" style="font-size:0.85rem">
                    <?= $flash['message'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>
            </div>

            <!-- OTP Verification Form -->
            <form class="auth-form mt-2" id="otp-form" action="<?= url('/verify-email') ?>" method="POST" data-ajax="true">
                <?= CsrfMiddleware::tokenField() ?>
                <input type="hidden" name="otp" id="otp">

                <div class="otp-inputs">
                    <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric" autocomplete="one-time-code" required autofocus>
                    <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                    <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                    <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                    <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                    <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-3 px-1">
                    <span class="timer-badge">
                        <i class="far fa-clock me-1 text-warning"></i> OTP Expires in: <strong id="expiry-timer">10:00</strong>
                    </span>
                    <span class="timer-badge" id="attempts-left">
                        <i class="fas fa-lock me-1 text-primary"></i> Max 5 attempts
                    </span>
                </div>

                <button type="submit" class="btn btn-login w-100 py-3 fw-bold fs-6">
                    <i class="fas fa-check-circle me-2"></i> Verify & Continue
                </button>
            </form>

            <!-- Resend Section -->
            <div class="resend-container">
                <span class="text-muted">Didn't receive the code?</span>
                <form id="resend-form" action="<?= url('/resend-otp') ?>" method="POST" style="display:inline;">
                    <?= CsrfMiddleware::tokenField() ?>
                    <button type="submit" id="resend-btn" class="btn-resend" disabled>
                        Resend OTP <span id="cooldown-timer">(60s)</span>
                    </button>
                </form>
            </div>

            <div class="auth-footer text-center mt-3 pt-2">
                Need to change email or login as a different user? <a href="<?= url('/login') ?>" class="fw-semibold text-primary">Back to Login</a>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= asset('js/auth.js') ?>"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    // ── Resend Cooldown Countdown (60 seconds) ───────────────────────────
    let cooldownSeconds = 60;
    const resendBtn = document.getElementById('resend-btn');
    const cooldownSpan = document.getElementById('cooldown-timer');
    let cooldownInterval = null;

    function startCooldown(seconds) {
        cooldownSeconds = seconds;
        resendBtn.disabled = true;
        cooldownSpan.textContent = `(${cooldownSeconds}s)`;

        if (cooldownInterval) clearInterval(cooldownInterval);
        cooldownInterval = setInterval(() => {
            cooldownSeconds--;
            if (cooldownSeconds > 0) {
                cooldownSpan.textContent = `(${cooldownSeconds}s)`;
            } else {
                clearInterval(cooldownInterval);
                resendBtn.disabled = false;
                cooldownSpan.textContent = '';
            }
        }, 1000);
    }
    startCooldown(60);

    // ── Overall OTP Expiry Timer (10 minutes) ───────────────────────────
    let expirySeconds = 600;
    const expirySpan = document.getElementById('expiry-timer');
    const expiryInterval = setInterval(() => {
        expirySeconds--;
        if (expirySeconds <= 0) {
            clearInterval(expiryInterval);
            expirySpan.textContent = '00:00 (Expired)';
            expirySpan.classList.add('text-danger');
            showAlert('danger', 'OTP has expired! Please click "Resend OTP" for a new code.');
        } else {
            const mins = String(Math.floor(expirySeconds / 60)).padStart(2, '0');
            const secs = String(expirySeconds % 60).padStart(2, '0');
            expirySpan.textContent = `${mins}:${secs}`;
        }
    }, 1000);

    // ── Helper to display dynamic alerts ───────────────────────────────
    function showAlert(type, message) {
        const container = document.getElementById('alert-container');
        container.innerHTML = `
            <div class="alert alert-${type} alert-dismissible fade show" style="font-size:0.85rem">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
    }

    // ── AJAX Resend Form Handler ─────────────────────────────────────────
    const resendForm = document.getElementById('resend-form');
    if (resendForm) {
        resendForm.addEventListener('submit', function(e) {
            e.preventDefault();
            if (resendBtn.disabled) return;

            resendBtn.disabled = true;
            resendBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Sending...';

            const formData = new FormData(this);
            fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', data.message);
                    resendBtn.innerHTML = 'Resend OTP <span id="cooldown-timer">(60s)</span>';
                    startCooldown(60);
                    // Reset expiry timer
                    expirySeconds = 600;
                    expirySpan.classList.remove('text-danger');
                } else {
                    showAlert('danger', data.message);
                    resendBtn.innerHTML = 'Resend OTP';
                    if (data.remaining_seconds) {
                        startCooldown(data.remaining_seconds);
                    } else {
                        resendBtn.disabled = false;
                    }
                }
            })
            .catch(() => {
                showAlert('danger', 'Network error while sending OTP. Please try again.');
                resendBtn.disabled = false;
                resendBtn.innerHTML = 'Resend OTP';
            });
        });
    }
});
</script>
</body>
</html>
