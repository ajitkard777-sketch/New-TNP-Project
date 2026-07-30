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
        .email-badge {
            background: rgba(79, 70, 229, 0.1);
            color: #4f46e5;
            padding: 8px 16px;
            border-radius: 50px;
            font-size: 0.88rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 20px;
        }
        .otp-timer-box {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: rgba(243, 244, 246, 0.7);
            border-radius: 12px;
            padding: 10px 16px;
            margin-bottom: 20px;
            font-size: 0.85rem;
            color: var(--text-secondary, #4b5563);
        }
        .otp-timer-box i {
            color: #4f46e5;
        }
        .timer-text {
            font-weight: 700;
            color: #1f2937;
        }
        .resend-box {
            text-align: center;
            margin-top: 24px;
            font-size: 0.9rem;
            color: var(--text-secondary, #6b7280);
        }
        .btn-resend {
            background: none;
            border: none;
            color: #4f46e5;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            padding: 0;
            transition: opacity 0.2s;
        }
        .btn-resend:disabled {
            color: #9ca3af;
            cursor: not-allowed;
        }
    </style>
</head>
<body>
<div class="auth-wrapper">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-logo">
                <div class="auth-logo-icon"><i class="fas fa-shield-check"></i></div>
                <h2>Email Verification</h2>
                <p>We sent a 6-digit OTP code to your registered email</p>
                <?php if (!empty($userEmail)): ?>
                    <div class="email-badge">
                        <i class="fas fa-envelope"></i> <?= htmlspecialchars($userEmail) ?>
                    </div>
                <?php endif; ?>
            </div>

            <?php $flash = getFlash(); ?>
            <?php if ($flash): ?>
            <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show" id="alert-banner" style="font-size:0.88rem; border-radius:10px;">
                <?= $flash['message'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <div id="ajax-alert-container"></div>

            <form class="auth-form" id="otp-form" action="<?= url('/verify-email') ?>" method="POST">
                <?= CsrfMiddleware::tokenField() ?>
                <input type="hidden" name="otp" id="otp">

                <div class="otp-inputs">
                    <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric" autocomplete="one-time-code" required autofocus>
                    <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric" autocomplete="one-time-code" required>
                    <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric" autocomplete="one-time-code" required>
                    <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric" autocomplete="one-time-code" required>
                    <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric" autocomplete="one-time-code" required>
                    <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric" autocomplete="one-time-code" required>
                </div>

                <div class="otp-timer-box">
                    <span><i class="far fa-clock me-1"></i> Code Expiry:</span>
                    <span class="timer-text" id="expiry-timer">10:00</span>
                </div>

                <button type="submit" class="btn btn-login w-100" id="btn-verify">
                    <i class="fas fa-check-circle me-2"></i> Verify OTP Code
                </button>
            </form>

            <div class="resend-box">
                Didn't receive the code? 
                <button type="button" class="btn-resend" id="btn-resend" disabled>
                    Resend OTP (<span id="resend-cooldown"><?= $cooldownSeconds ?? 60 ?></span>s)
                </button>
            </div>

            <div class="auth-footer mt-4">
                Need to change account? <a href="<?= url('/login') ?>">Return to Login</a>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= asset('js/auth.js') ?>"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    let cooldownSec = <?= (int)($cooldownSeconds ?? 0) ?>;
    let expirySec   = <?= (int)($expirySeconds ?? 600) ?>;

    const btnResend       = document.getElementById('btn-resend');
    const resendCooldown  = document.getElementById('resend-cooldown');
    const expiryTimer     = document.getElementById('expiry-timer');
    const otpForm         = document.getElementById('otp-form');
    const btnVerify       = document.getElementById('btn-verify');
    const otpInputs       = document.querySelectorAll('.otp-input');
    const otpHidden       = document.getElementById('otp');
    const alertContainer  = document.getElementById('ajax-alert-container');

    function showAlert(type, msg) {
        alertContainer.innerHTML = `
            <div class="alert alert-${type} alert-dismissible fade show mb-3" style="font-size:0.88rem; border-radius:10px;">
                ${msg}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>`;
    }

    // Handle Expiry Countdown
    function updateExpiryTimer() {
        if (expirySec <= 0) {
            expiryTimer.textContent = 'Expired';
            expiryTimer.style.color = '#ef4444';
            return;
        }
        const m = Math.floor(expirySec / 60).toString().padStart(2, '0');
        const s = (expirySec % 60).toString().padStart(2, '0');
        expiryTimer.textContent = `${m}:${s}`;
        expirySec--;
    }
    updateExpiryTimer();
    const expiryInterval = setInterval(updateExpiryTimer, 1000);

    // Handle Resend Cooldown Countdown
    function updateResendTimer() {
        if (cooldownSec <= 0) {
            btnResend.disabled = false;
            btnResend.innerHTML = '<i class="fas fa-redo-alt me-1"></i> Resend OTP';
        } else {
            btnResend.disabled = true;
            btnResend.innerHTML = `Resend OTP (${cooldownSec}s)`;
            cooldownSec--;
        }
    }
    updateResendTimer();
    const cooldownInterval = setInterval(updateResendTimer, 1000);

    // Handle OTP input navigation
    otpInputs.forEach((input, index) => {
        input.addEventListener('input', (e) => {
            input.value = input.value.replace(/[^0-9]/g, '');
            if (input.value && index < otpInputs.length - 1) {
                otpInputs[index + 1].focus();
            }
            updateHiddenOTP();
        });

        input.addEventListener('keydown', (e) => {
            if (e.key === 'Backspace' && !input.value && index > 0) {
                otpInputs[index - 1].focus();
            }
        });

        input.addEventListener('paste', (e) => {
            e.preventDefault();
            const pasteData = (e.clipboardData || window.clipboardData).getData('text');
            const digits = pasteData.replace(/[^0-9]/g, '').split('');
            otpInputs.forEach((inp, idx) => {
                if (digits[idx]) {
                    inp.value = digits[idx];
                }
            });
            if (digits.length >= 6) {
                otpInputs[5].focus();
            } else if (digits.length > 0) {
                const nextIndex = Math.min(digits.length, 5);
                otpInputs[nextIndex].focus();
            }
            updateHiddenOTP();
        });
    });

    function updateHiddenOTP() {
        let code = '';
        otpInputs.forEach(i => code += i.value);
        otpHidden.value = code;
    }

    // Handle Resend Click via AJAX
    btnResend.addEventListener('click', () => {
        if (btnResend.disabled) return;

        btnResend.disabled = true;
        btnResend.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Sending...';

        const formData = new FormData();
        const csrfToken = document.querySelector('input[name="csrf_token"]');
        if (csrfToken) formData.append('csrf_token', csrfToken.value);

        fetch('<?= url("/resend-otp") ?>', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showAlert('success', data.message);
                cooldownSec = data.cooldown || 60;
                expirySec   = data.expiry || 600;
                updateResendTimer();
                updateExpiryTimer();
                // Clear OTP inputs
                otpInputs.forEach(inp => inp.value = '');
                otpHidden.value = '';
                otpInputs[0].focus();
            } else {
                showAlert('danger', data.message);
                if (data.cooldown) {
                    cooldownSec = data.cooldown;
                    updateResendTimer();
                } else {
                    btnResend.disabled = false;
                    btnResend.innerHTML = '<i class="fas fa-redo-alt me-1"></i> Resend OTP';
                }
            }
        })
        .catch(err => {
            showAlert('danger', 'Failed to resend OTP. Please try again.');
            btnResend.disabled = false;
            btnResend.innerHTML = '<i class="fas fa-redo-alt me-1"></i> Resend OTP';
        });
    });

    // Handle Form Submit via AJAX
    otpForm.addEventListener('submit', (e) => {
        e.preventDefault();
        updateHiddenOTP();

        if (otpHidden.value.length !== 6) {
            showAlert('danger', 'Please enter a complete 6-digit OTP code.');
            return;
        }

        btnVerify.disabled = true;
        const origContent = btnVerify.innerHTML;
        btnVerify.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Verifying...';

        const formData = new FormData(otpForm);

        fetch(otpForm.action, {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showAlert('success', data.message);
                btnVerify.innerHTML = '<i class="fas fa-check me-2"></i> Verified!';
                if (data.redirect) {
                    setTimeout(() => { window.location.href = data.redirect; }, 1200);
                }
            } else {
                showAlert('danger', data.message);
                btnVerify.disabled = false;
                btnVerify.innerHTML = origContent;
            }
        })
        .catch(err => {
            showAlert('danger', 'Verification request failed. Please try again.');
            btnVerify.disabled = false;
            btnVerify.innerHTML = origContent;
        });
    });
});
</script>
</body>
</html>
