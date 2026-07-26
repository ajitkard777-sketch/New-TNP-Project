<?php require_once ROOT_PATH . '/includes/header.php'; ?>

<style>
.change-password-wrapper {
    max-width: 580px;
    width: 100%;
    margin: 1.5rem auto 3rem;
}

.change-password-card {
    border-radius: 16px;
    border: 1px solid var(--border-color, #e2e8f0);
    background: var(--card-bg, #ffffff);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.06);
    padding: 36px 40px;
    transition: all 0.25s ease;
}

[data-theme="midnight"] .change-password-card {
    background: #121824;
    border-color: #1e293b;
    box-shadow: 0 12px 35px rgba(0, 0, 0, 0.4);
}

.change-password-header {
    text-align: center;
    margin-bottom: 28px;
}

.change-password-icon {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background: linear-gradient(135deg, rgba(79, 70, 229, 0.12) 0%, rgba(124, 58, 237, 0.12) 100%);
    color: var(--primary-color, #4f46e5);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    margin-bottom: 14px;
}

[data-theme="midnight"] .change-password-icon {
    background: linear-gradient(135deg, rgba(99, 102, 241, 0.2) 0%, rgba(139, 92, 246, 0.2) 100%);
    color: #818cf8;
}

.change-password-title {
    font-size: 22px;
    font-weight: 700;
    margin: 0 0 6px;
    color: var(--text-color, #0f172a);
    letter-spacing: -0.3px;
}

.change-password-subtitle {
    font-size: 14px;
    color: var(--text-secondary, #64748b);
    margin: 0;
    line-height: 1.5;
}

.change-password-card .form-group {
    margin-bottom: 22px;
}

.change-password-card .form-label {
    font-size: 14px;
    font-weight: 600;
    color: var(--text-color, #1e293b);
    margin-bottom: 8px;
    display: block;
}

.change-password-card .input-group {
    border-radius: 10px;
    overflow: hidden;
}

.change-password-card .form-control {
    min-height: 48px;
    font-size: 15px;
    padding: 10px 16px;
    border-radius: 10px 0 0 10px !important;
    border: 1px solid var(--border-color, #cbd5e1);
    background: var(--input-bg, #ffffff);
    color: var(--text-color, #0f172a);
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

[data-theme="midnight"] .change-password-card .form-control {
    background: #0b0f19;
    border-color: #334155;
    color: #f8fafc;
}

.change-password-card .form-control:focus {
    border-color: var(--primary-color, #4f46e5);
    box-shadow: 0 0 0 3.5px rgba(79, 70, 229, 0.15);
    outline: none;
    z-index: 3;
}

.change-password-card .input-group-text.toggle-password {
    min-height: 48px;
    padding: 0 16px;
    background: var(--input-group-bg, #f8fafc);
    border: 1px solid var(--border-color, #cbd5e1);
    border-left: none;
    border-radius: 0 10px 10px 0 !important;
    color: var(--text-secondary, #64748b);
    transition: all 0.2s ease;
}

[data-theme="midnight"] .change-password-card .input-group-text.toggle-password {
    background: #1e293b;
    border-color: #334155;
    color: #94a3b8;
}

.change-password-card .input-group-text.toggle-password:hover {
    color: var(--primary-color, #4f46e5);
    background: var(--input-group-hover-bg, #f1f5f9);
}

[data-theme="midnight"] .change-password-card .input-group-text.toggle-password:hover {
    background: #334155;
    color: #818cf8;
}

.password-policy-box {
    background: var(--policy-bg, #f8fafc);
    border: 1px solid var(--border-color, #e2e8f0);
    border-radius: 10px;
    padding: 14px 18px;
    margin-bottom: 24px;
}

[data-theme="midnight"] .password-policy-box {
    background: #0f172a;
    border-color: #1e293b;
}

.password-policy-title {
    font-size: 13px;
    font-weight: 700;
    color: var(--text-color, #334155);
    margin-bottom: 6px;
    display: flex;
    align-items: center;
    gap: 6px;
}

.password-policy-list {
    margin: 0;
    padding-left: 20px;
    font-size: 12.5px;
    color: var(--text-secondary, #64748b);
    line-height: 1.6;
}

.btn-change-password {
    width: 100%;
    min-height: 50px;
    font-size: 16px;
    font-weight: 700;
    border-radius: 10px;
    background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
    border: none;
    color: #ffffff;
    box-shadow: 0 4px 14px rgba(79, 70, 229, 0.3);
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.btn-change-password:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 20px rgba(79, 70, 229, 0.4);
    background: linear-gradient(135deg, #4338ca 0%, #4f46e5 100%);
    color: #ffffff;
}

.btn-change-password:active {
    transform: translateY(0);
}

@media (max-width: 576px) {
    .change-password-wrapper {
        margin: 1rem auto 2rem;
    }
    .change-password-card {
        padding: 24px 20px;
        border-radius: 12px;
    }
    .change-password-title {
        font-size: 20px;
    }
}
</style>

<div class="content-header">
    <div>
        <h1 class="page-title">Account Security</h1>
        <p class="subtitle">Manage your password and security credentials</p>
    </div>
</div>

<div class="change-password-wrapper">
    <div class="card change-password-card">
        <!-- Header -->
        <div class="change-password-header">
            <div class="change-password-icon">
                <i class="fas fa-lock"></i>
            </div>
            <h2 class="change-password-title">Change Password</h2>
            <p class="change-password-subtitle">Update your password to keep your account safe and secure</p>
        </div>

        <!-- Form -->
        <form action="<?= url('/student/change-password') ?>" method="POST" data-validate>
            <?= CsrfMiddleware::tokenField() ?>

            <!-- Current Password -->
            <div class="form-group">
                <label class="form-label" for="current_password">Current Password <span class="text-danger">*</span></label>
                <div class="input-group">
                    <input type="password" class="form-control" id="current_password" name="current_password" placeholder="Enter your current password" required autocomplete="current-password">
                    <span class="input-group-text toggle-password" style="cursor:pointer" title="Toggle visibility">
                        <i class="fas fa-eye"></i>
                    </span>
                </div>
            </div>

            <!-- New Password -->
            <div class="form-group">
                <label class="form-label" for="password">New Password <span class="text-danger">*</span></label>
                <div class="input-group">
                    <input type="password" class="form-control" id="password" name="new_password" placeholder="Enter your new password" required autocomplete="new-password">
                    <span class="input-group-text toggle-password" style="cursor:pointer" title="Toggle visibility">
                        <i class="fas fa-eye"></i>
                    </span>
                </div>
                <div class="password-strength">
                    <div class="password-strength-bar"><div class="password-strength-fill"></div></div>
                    <small class="password-strength-text"></small>
                </div>
            </div>

            <!-- Confirm New Password -->
            <div class="form-group">
                <label class="form-label" for="confirm_password">Confirm New Password <span class="text-danger">*</span></label>
                <div class="input-group">
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm your new password" required autocomplete="new-password">
                    <span class="input-group-text toggle-password" style="cursor:pointer" title="Toggle visibility">
                        <i class="fas fa-eye"></i>
                    </span>
                </div>
            </div>

            <!-- Password Policy Hint Box -->
            <div class="password-policy-box">
                <div class="password-policy-title">
                    <i class="fas fa-shield-alt text-primary"></i> Password Requirements
                </div>
                <ul class="password-policy-list">
                    <li>Minimum 8 characters in length</li>
                    <li>Must include uppercase and lowercase letters</li>
                    <li>Must include at least one number and one special character</li>
                </ul>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn btn-change-password">
                <i class="fas fa-key"></i> Update Password
            </button>
        </form>
    </div>
</div>

<script src="<?= asset('js/auth.js') ?>"></script>
<?php require_once ROOT_PATH . '/includes/footer.php'; ?>
