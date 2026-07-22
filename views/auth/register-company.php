<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="base-url" content="<?= BASE_URL ?>">
    <meta name="csrf-token" content="<?= CsrfMiddleware::getToken() ?>">
    <?php require_once __DIR__ . '/../../includes/auth_theme_head.php'; ?>
    <title>Company Registration - <?= APP_NAME ?></title>
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
    <div class="auth-container auth-wide">
        <div class="auth-card">
            <div class="auth-logo">
                <div class="auth-logo-icon"><i class="fas fa-building"></i></div>
                <h2>Company Registration</h2>
                <p>Register your company for campus recruitment</p>
            </div>

            <?php $flash = getFlash(); ?>
            <?php if ($flash): ?>
            <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show" style="font-size:0.85rem">
                <?= $flash['message'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <form class="auth-form" action="<?= url('/register/company') ?>" method="POST" data-validate>
                <?= CsrfMiddleware::tokenField() ?>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Company Name *</label>
                            <input type="text" class="form-control" name="company_name" placeholder="Company name" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Industry</label>
                            <select class="form-select" name="industry">
                                <option value="">Select Industry</option>
                                <option value="Information Technology">Information Technology</option>
                                <option value="Finance">Finance & Banking</option>
                                <option value="Healthcare">Healthcare</option>
                                <option value="Manufacturing">Manufacturing</option>
                                <option value="Consulting">Consulting</option>
                                <option value="E-Commerce">E-Commerce</option>
                                <option value="Education">Education</option>
                                <option value="Automotive">Automotive</option>
                                <option value="Technology">Technology</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Email Address *</label>
                            <input type="email" class="form-control" id="email" name="email" placeholder="hr@company.com" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Website</label>
                            <input type="url" class="form-control" name="website" placeholder="https://company.com">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Contact Person *</label>
                            <input type="text" class="form-control" name="contact_person" placeholder="HR Manager name" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Contact Phone *</label>
                            <input type="text" class="form-control" name="contact_phone" placeholder="10-digit number" maxlength="10" required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Company Type</label>
                            <select class="form-select" name="company_type">
                                <option value="">Select Type</option>
                                <option value="product">Product Based</option>
                                <option value="service">Service Based</option>
                                <option value="startup">Startup</option>
                                <option value="mnc">MNC</option>
                                <option value="government">Government</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Employee Count</label>
                            <select class="form-select" name="employee_count">
                                <option value="">Select</option>
                                <option value="1-50">1-50</option>
                                <option value="50-200">50-200</option>
                                <option value="200-1000">200-1000</option>
                                <option value="1000-5000">1000-5000</option>
                                <option value="5000+">5000+</option>
                                <option value="50000+">50000+</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Password *</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="password" name="password" placeholder="Min 8 characters" required>
                                <span class="input-group-text toggle-password"><i class="fas fa-eye"></i></span>
                            </div>
                            <div class="password-strength">
                                <div class="password-strength-bar"><div class="password-strength-fill"></div></div>
                                <small class="password-strength-text"></small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Confirm Password *</label>
                            <input type="password" class="form-control" name="confirm_password" placeholder="Confirm password" required>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Company Description</label>
                    <textarea class="form-control" name="description" rows="3" placeholder="Brief description about your company"></textarea>
                </div>

                <div class="alert alert-info" style="font-size:0.82rem">
                    <i class="fas fa-info-circle me-2"></i>
                    Your registration will be reviewed by the admin. You'll receive a notification once approved.
                </div>

                <button type="submit" class="btn btn-login">
                    <i class="fas fa-paper-plane me-2"></i> Submit Registration
                </button>
            </form>

            <div class="auth-footer">
                Already registered? <a href="<?= url('/login') ?>">Sign In</a>
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
