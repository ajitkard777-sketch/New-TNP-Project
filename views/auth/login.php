<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="base-url" content="<?= BASE_URL ?>">
    <meta name="csrf-token" content="<?= CsrfMiddleware::getToken() ?>">
    <?php require_once __DIR__ . '/../../includes/auth_theme_head.php'; ?>
    <title>Login - <?= APP_NAME ?></title>
    <meta name="description" content="Login to TPMS - Training &amp; Placement Management System">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <link href="<?= asset('css/style.css') ?>" rel="stylesheet">
    <link href="<?= asset('css/dark-mode.css') ?>" rel="stylesheet">
    <link href="<?= asset('css/auth.css') ?>" rel="stylesheet">
</head>
<body>
<div class="auth-wrapper">
    <div class="auth-container">
        <div class="auth-card">
            <!-- Logo -->
            <div class="auth-logo">
                <div class="auth-logo-icon">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <h2>TPMS</h2>
                <p>Training & Placement Management System</p>
            </div>

            <?php $flash = getFlash(); ?>
            <?php if ($flash): ?>
            <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show" role="alert" style="font-size:0.85rem">
                <?= $flash['message'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <!-- Role Tabs -->
            <div class="auth-role-tabs">
                <button type="button" class="auth-role-tab active" data-role="student">
                    <i class="fas fa-user-graduate me-1"></i> Student
                </button>
                <button type="button" class="auth-role-tab" data-role="company">
                    <i class="fas fa-building me-1"></i> Company
                </button>
                <button type="button" class="auth-role-tab" data-role="admin">
                    <i class="fas fa-user-shield me-1"></i> Admin
                </button>
            </div>

            <!-- Login Form -->
            <form class="auth-form" action="<?= url('/login') ?>" method="POST" data-ajax="true" data-validate>
                <?= CsrfMiddleware::tokenField() ?>
                <input type="hidden" name="role" id="role-input" value="student">

                <div class="form-group">
                    <label class="form-label" for="email">Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                        <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" required autofocus>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">
                        Password
                        <a href="<?= url('/forgot-password') ?>" class="forgot-link">Forgot password?</a>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
                        <span class="input-group-text toggle-password"><i class="fas fa-eye"></i></span>
                    </div>
                </div>

                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="remember_me" id="remember_me">
                    <label class="form-check-label" for="remember_me">Remember me for 30 days</label>
                </div>

                <button type="submit" class="btn btn-login">
                    <i class="fas fa-sign-in-alt me-2"></i> Sign In
                </button>
            </form>

            <div class="auth-divider">
                <span>New here?</span>
            </div>

            <div class="auth-footer">
                <a href="<?= url('/register/student') ?>" class="btn btn-outline-primary btn-sm me-2">
                    <i class="fas fa-user-graduate me-1"></i> Register as Student
                </a>
                <a href="<?= url('/register/company') ?>" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-building me-1"></i> Register as Company
                </a>
            </div>


        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="<?= asset('js/app.js') ?>"></script>
<script src="<?= asset('js/auth.js') ?>"></script>
</body>
</html>
