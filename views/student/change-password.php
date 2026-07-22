<?php require_once ROOT_PATH . '/includes/header.php'; ?>
<div class="content-header"><div><h1 class="page-title">Change Password</h1><p class="subtitle">Keep your account secure</p></div></div>
<div class="row justify-content-center"><div class="col-lg-6">
<div class="card">
    <div class="card-body">
        <form action="<?= url('/student/change-password') ?>" method="POST" data-validate>
            <?= CsrfMiddleware::tokenField() ?>
            <div class="mb-3"><label class="form-label">Current Password *</label>
                <div class="input-group"><input type="password" class="form-control" name="current_password" required><span class="input-group-text toggle-password" style="cursor:pointer"><i class="fas fa-eye"></i></span></div></div>
            <div class="mb-3"><label class="form-label">New Password *</label>
                <div class="input-group"><input type="password" class="form-control" id="password" name="new_password" required><span class="input-group-text toggle-password" style="cursor:pointer"><i class="fas fa-eye"></i></span></div>
                <div class="password-strength"><div class="password-strength-bar"><div class="password-strength-fill"></div></div><small class="password-strength-text"></small></div></div>
            <div class="mb-4"><label class="form-label">Confirm New Password *</label><input type="password" class="form-control" name="confirm_password" required></div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-key me-2"></i> Change Password</button>
        </form>
    </div>
</div>
</div></div>
<script src="<?= asset('js/auth.js') ?>"></script>
<?php require_once ROOT_PATH . '/includes/footer.php'; ?>
