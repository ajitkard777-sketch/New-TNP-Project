<?php require_once ROOT_PATH . '/includes/header.php'; ?>

<style>
.change-password-card {
    border: 1px solid rgba(0, 0, 0, 0.08);
    border-radius: 1rem;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.06);
    transition: all 0.3s ease;
}

.change-password-icon-box {
    width: 60px;
    height: 60px;
    border-radius: 16px;
    background: linear-gradient(135deg, rgba(99, 102, 241, 0.12), rgba(168, 85, 247, 0.12));
    color: var(--primary, #6366f1);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    box-shadow: 0 4px 12px rgba(99, 102, 241, 0.15);
}

.change-password-card .form-control {
    padding: 0.75rem 1rem;
    font-size: 0.95rem;
    border-radius: 0.5rem;
}

.change-password-card .input-group-text {
    padding: 0.75rem 1rem;
    font-size: 0.95rem;
    border-radius: 0.5rem;
}

.change-password-card .input-group .form-control:not(:first-child) {
    border-top-left-radius: 0;
    border-bottom-left-radius: 0;
}

.change-password-card .input-group .form-control:not(:last-child) {
    border-top-right-radius: 0;
    border-bottom-right-radius: 0;
}

.change-password-card .btn-submit {
    padding: 0.8rem 1.5rem;
    font-size: 1rem;
    font-weight: 600;
    border-radius: 0.6rem;
    letter-spacing: 0.3px;
    transition: all 0.2s ease;
}

.change-password-card .btn-submit:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(99, 102, 241, 0.3);
}
</style>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-xl-6 col-lg-7 col-md-9 col-12">
            <div class="card change-password-card border-0 bg-white">
                
                <!-- Internal Heading & Subtitle -->
                <div class="card-header bg-transparent border-bottom-0 pt-4 px-4 px-md-5 pb-0 text-center">
                    <div class="change-password-icon-box mb-3">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3 class="fw-bold text-dark mb-1" style="letter-spacing: -0.5px;">Change Password</h3>
                    <p class="text-muted small mb-0">Update your password regularly to keep your account secure</p>
                </div>

                <div class="card-body p-4 p-md-5">
                    
                    <?php $flash = getFlash(); ?>
                    <?php if ($flash): ?>
                    <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show mb-4" role="alert" style="font-size:0.88rem; border-radius: 0.5rem;">
                        <i class="fas <?= $flash['type'] === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle' ?> me-2"></i>
                        <?= $flash['message'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php endif; ?>

                    <form class="auth-form" action="<?= url('/student/change-password') ?>" method="POST" data-validate>
                        <?= CsrfMiddleware::tokenField() ?>

                        <!-- Current Password -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold text-dark mb-2" for="current_password" style="font-size: 0.9rem;">
                                Current Password <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted border-end-0">
                                    <i class="fas fa-lock"></i>
                                </span>
                                <input type="password" class="form-control border-start-0 border-end-0" id="current_password" name="current_password" placeholder="Enter current password" required>
                                <span class="input-group-text bg-light text-muted border-start-0 toggle-password" style="cursor:pointer" title="Toggle visibility">
                                    <i class="fas fa-eye"></i>
                                </span>
                            </div>
                        </div>

                        <!-- New Password -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold text-dark mb-2" for="password" style="font-size: 0.9rem;">
                                New Password <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted border-end-0">
                                    <i class="fas fa-key"></i>
                                </span>
                                <input type="password" class="form-control border-start-0 border-end-0" id="password" name="new_password" placeholder="Min 8 chars (uppercase, number, symbol)" required>
                                <span class="input-group-text bg-light text-muted border-start-0 toggle-password" style="cursor:pointer" title="Toggle visibility">
                                    <i class="fas fa-eye"></i>
                                </span>
                            </div>
                            <div class="password-strength mt-2">
                                <div class="password-strength-bar" style="height: 5px; border-radius: 3px; background: #e2e8f0; overflow: hidden;">
                                    <div class="password-strength-fill" style="height: 100%; transition: all 0.3s ease; width: 0;"></div>
                                </div>
                                <small class="password-strength-text fw-semibold" style="font-size: 0.78rem;"></small>
                            </div>
                        </div>

                        <!-- Confirm New Password -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold text-dark mb-2" for="confirm_password" style="font-size: 0.9rem;">
                                Confirm New Password <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted border-end-0">
                                    <i class="fas fa-check-double"></i>
                                </span>
                                <input type="password" class="form-control border-start-0 border-end-0" id="confirm_password" name="confirm_password" placeholder="Re-enter new password" required>
                                <span class="input-group-text bg-light text-muted border-start-0 toggle-password" style="cursor:pointer" title="Toggle visibility">
                                    <i class="fas fa-eye"></i>
                                </span>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="d-grid mt-4 pt-2">
                            <button type="submit" class="btn btn-primary btn-submit shadow-sm">
                                <i class="fas fa-save me-2"></i> Update Password
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?= asset('js/auth.js') ?>"></script>
<?php require_once ROOT_PATH . '/includes/footer.php'; ?>
