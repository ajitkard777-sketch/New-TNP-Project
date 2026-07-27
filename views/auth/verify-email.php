<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php require_once __DIR__ . '/../../includes/auth_theme_head.php'; ?>
    <title>Verify OTP - <?= APP_NAME ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="<?= asset('css/style.css') ?>" rel="stylesheet">
    <link href="<?= asset('css/dark-mode.css') ?>" rel="stylesheet">
    <link href="<?= asset('css/auth.css') ?>" rel="stylesheet">
</head>
<body>
<?php
$verifyEmail = $_SESSION['verify_email'] ?? '';
$maskedEmail = '';
if (!empty($verifyEmail)) {
    $parts = explode('@', $verifyEmail);
    $name = $parts[0];
    $domain = $parts[1] ?? '';
    $maskedName = strlen($name) > 2 ? substr($name, 0, 1) . str_repeat('*', max(2, strlen($name) - 2)) . substr($name, -1) : $name . '***';
    $maskedEmail = $maskedName . '@' . $domain;
}
?>
<div class="auth-wrapper">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-logo">
                <div class="auth-logo-icon" style="background: linear-gradient(135deg, #4f46e5, #7c3aed); color: white;">
                    <i class="fas fa-user-shield"></i>
                </div>
                <h2>Email Verification</h2>
                <p>We've sent a 6-digit OTP code to</p>
                <?php if ($maskedEmail): ?>
                    <div class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 rounded-pill mt-1" style="font-size: 0.9rem;">
                        <i class="fas fa-envelope me-1"></i> <?= htmlspecialchars($maskedEmail) ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Alert Container -->
            <div id="alert-container">
                <?php $flash = getFlash(); ?>
                <?php if ($flash): ?>
                <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show" style="font-size:0.88rem; border-radius: 10px;">
                    <?= $flash['message'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>
            </div>

            <form class="auth-form mt-3" id="otp-form" action="<?= url('/verify-email') ?>" method="POST">
                <?= CsrfMiddleware::tokenField() ?>
                <input type="hidden" name="otp" id="otp">
                <?php if (isset($_SESSION['verify_user_id'])): ?>
                    <input type="hidden" name="user_id" value="<?= (int)$_SESSION['verify_user_id'] ?>">
                <?php endif; ?>

                <div class="mb-3 text-center">
                    <label class="form-label text-muted fw-semibold small uppercase tracking-wider mb-3">Enter 6-Digit Verification Code</label>
                    <div class="otp-inputs d-flex justify-content-center gap-2">
                        <input type="text" class="otp-input text-center font-bold" maxlength="1" pattern="[0-9]" inputmode="numeric" autocomplete="one-time-code" required autofocus style="width: 48px; height: 56px; font-size: 1.4rem; border-radius: 10px; border: 2px solid #e2e8f0;">
                        <input type="text" class="otp-input text-center font-bold" maxlength="1" pattern="[0-9]" inputmode="numeric" required style="width: 48px; height: 56px; font-size: 1.4rem; border-radius: 10px; border: 2px solid #e2e8f0;">
                        <input type="text" class="otp-input text-center font-bold" maxlength="1" pattern="[0-9]" inputmode="numeric" required style="width: 48px; height: 56px; font-size: 1.4rem; border-radius: 10px; border: 2px solid #e2e8f0;">
                        <input type="text" class="otp-input text-center font-bold" maxlength="1" pattern="[0-9]" inputmode="numeric" required style="width: 48px; height: 56px; font-size: 1.4rem; border-radius: 10px; border: 2px solid #e2e8f0;">
                        <input type="text" class="otp-input text-center font-bold" maxlength="1" pattern="[0-9]" inputmode="numeric" required style="width: 48px; height: 56px; font-size: 1.4rem; border-radius: 10px; border: 2px solid #e2e8f0;">
                        <input type="text" class="otp-input text-center font-bold" maxlength="1" pattern="[0-9]" inputmode="numeric" required style="width: 48px; height: 56px; font-size: 1.4rem; border-radius: 10px; border: 2px solid #e2e8f0;">
                    </div>
                </div>

                <div class="d-grid mt-4">
                    <button type="submit" class="btn btn-primary py-2.5 fw-bold shadow-sm" style="border-radius: 10px; font-size: 1rem;">
                        <i class="fas fa-shield-alt me-2"></i> Verify &amp; Activate Account
                    </button>
                </div>
            </form>

            <div class="auth-footer mt-4 pt-3 border-top text-center">
                <p class="text-muted small mb-2">Didn't receive the OTP code or code expired?</p>
                <div class="d-flex align-items-center justify-content-center gap-2">
                    <button type="button" id="btn-resend-otp" class="btn btn-outline-secondary btn-sm rounded-pill px-3" disabled>
                        <i class="fas fa-redo-alt me-1"></i> Resend Code <span id="cooldown-timer">(60s)</span>
                    </button>
                    <a href="<?= url('/login') ?>" class="btn btn-link btn-sm text-decoration-none small text-muted">
                        <i class="fas fa-arrow-left me-1"></i> Back to Login
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= asset('js/auth.js') ?>"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    let timerSeconds = 60;
    const resendBtn = document.getElementById('btn-resend-otp');
    const timerSpan = document.getElementById('cooldown-timer');
    const alertContainer = document.getElementById('alert-container');
    let interval = null;

    function showAlert(message, type = 'info') {
        alertContainer.innerHTML = `
            <div class="alert alert-${type} alert-dismissible fade show" style="font-size:0.88rem; border-radius: 10px;">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>`;
    }

    function startCooldown(duration = 60) {
        timerSeconds = duration;
        resendBtn.disabled = true;
        timerSpan.textContent = `(${timerSeconds}s)`;
        
        if (interval) clearInterval(interval);
        
        interval = setInterval(function() {
            timerSeconds--;
            if (timerSeconds <= 0) {
                clearInterval(interval);
                resendBtn.disabled = false;
                timerSpan.textContent = '';
                resendBtn.innerHTML = '<i class="fas fa-redo-alt me-1"></i> Resend OTP';
            } else {
                timerSpan.textContent = `(${timerSeconds}s)`;
            }
        }, 1000);
    }

    // Start 60-second timer on page load
    startCooldown(60);

    // Resend OTP click handler
    if (resendBtn) {
        resendBtn.addEventListener('click', function() {
            if (resendBtn.disabled) return;
            
            resendBtn.disabled = true;
            resendBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Sending...';

            fetch('<?= url('/resend-otp') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: new URLSearchParams({
                    '_token': '<?= CsrfMiddleware::getToken() ?>'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('<i class="fas fa-check-circle me-1"></i> ' + data.message, 'success');
                    startCooldown(data.cooldown || 60);
                } else {
                    showAlert('<i class="fas fa-exclamation-triangle me-1"></i> ' + data.message, 'warning');
                    if (data.wait && data.wait > 0) {
                        startCooldown(data.wait);
                    } else {
                        resendBtn.disabled = false;
                        resendBtn.innerHTML = '<i class="fas fa-redo-alt me-1"></i> Resend OTP';
                    }
                }
            })
            .catch(err => {
                showAlert('Network error while resending OTP. Please try again.', 'danger');
                resendBtn.disabled = false;
                resendBtn.innerHTML = '<i class="fas fa-redo-alt me-1"></i> Resend OTP';
            });
        });
    }
});
</script>
</body>
</html>
