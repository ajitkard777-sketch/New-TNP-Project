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
</head>
<body>
<div class="auth-wrapper">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-logo">
                <div class="auth-logo-icon"><i class="fas fa-envelope-open-text"></i></div>
                <h2>Verify Email</h2>
                <p>Enter the 6-digit OTP sent to your email</p>
            </div>

            <?php $flash = getFlash(); ?>
            <?php if ($flash): ?>
            <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show" style="font-size:0.85rem">
                <?= $flash['message'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <form class="auth-form" id="otp-form" action="<?= url('/verify-email') ?>" method="POST" data-validate>
                <?= CsrfMiddleware::tokenField() ?>
                <input type="hidden" name="otp" id="otp">

                <div class="otp-inputs">
                    <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" required>
                    <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" required>
                    <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" required>
                    <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" required>
                    <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" required>
                    <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" required>
                </div>

                <button type="submit" class="btn btn-login">
                    <i class="fas fa-check-circle me-2"></i> Verify OTP
                </button>
            </form>

            <div class="auth-footer">
                Didn't receive the OTP? <a href="<?= url('/login') ?>">Try Again</a>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= asset('js/auth.js') ?>"></script>
</body>
</html>
