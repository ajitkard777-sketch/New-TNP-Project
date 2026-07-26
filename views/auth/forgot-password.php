<?php
/**
 * TPMS - Forgot Password View
 */
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php require_once __DIR__ . '/../../includes/auth_theme_head.php'; ?>
    <title>Forgot Password - <?= APP_NAME ?></title>
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

            <?php $flash = getFlash(); ?>

            <?php if ($flash && $flash['type'] === 'success'): ?>
            <!-- Professional Email-Sent Success Card -->
            <div class="text-center">
                <div style="width:72px;height:72px;background:linear-gradient(135deg,#10b981,#059669);border-radius:50%;display:inline-flex;align-items:center;justify-content:center;margin-bottom:20px;box-shadow:0 8px 24px rgba(16,185,129,0.35);">
                    <i class="fas fa-envelope-open-text" style="font-size:28px;color:#fff;"></i>
                </div>
                <h4 class="fw-bold mb-2" style="color:#0f172a;">Check Your Inbox!</h4>
                <p class="text-muted mb-4" style="font-size:0.95rem;line-height:1.7;">
                    A password reset link has been sent to your registered email address.<br>
                    Please check your <strong>inbox</strong> and <strong>spam/junk folder</strong>.
                </p>
                <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:12px;padding:16px 20px;margin-bottom:24px;text-align:left;">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="fas fa-circle-check text-success"></i>
                        <span style="font-size:0.85rem;font-weight:600;color:#166534;">Reset link has been sent</span>
                    </div>
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="fas fa-clock text-warning"></i>
                        <span style="font-size:0.85rem;color:#374151;">Link expires in <strong>1 hour</strong></span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <i class="fas fa-shield-alt text-primary"></i>
                        <span style="font-size:0.85rem;color:#374151;">If you didn't request this, you can safely ignore the email</span>
                    </div>
                </div>
                <a href="<?= url('/forgot-password') ?>" class="btn btn-outline-primary btn-sm me-2">
                    <i class="fas fa-redo me-1"></i> Resend Email
                </a>
                <a href="<?= url('/login') ?>" class="btn btn-primary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i> Back to Login
                </a>
            </div>

            <?php else: ?>

            <?php if ($flash): ?>
            <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show" style="font-size:0.85rem">
                <?= $flash['message'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <div class="auth-logo">
                <div class="auth-logo-icon"><i class="fas fa-key"></i></div>
                <h2>Forgot Password?</h2>
                <p>Enter your registered email to receive a reset link</p>
            </div>

            <form class="auth-form" action="<?= url('/forgot-password') ?>" method="POST" data-validate>
                <?= CsrfMiddleware::tokenField() ?>

                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                        <input type="email" class="form-control" name="email" placeholder="Enter your registered email" required autofocus>
                    </div>
                </div>

                <button type="submit" class="btn btn-login">
                    <i class="fas fa-paper-plane me-2"></i> Send Reset Link
                </button>
            </form>

            <div class="auth-footer">
                Remember your password? <a href="<?= url('/login') ?>">Back to Login</a>
            </div>

            <?php endif; ?>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
