<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="base-url" content="<?= BASE_URL ?>">
    <meta name="csrf-token" content="<?= CsrfMiddleware::getToken() ?>">
    <?php require_once __DIR__ . '/../../includes/auth_theme_head.php'; ?>
    <title>Student Registration - <?= APP_NAME ?></title>
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
                <div class="auth-logo-icon"><i class="fas fa-user-graduate"></i></div>
                <h2>Student Registration</h2>
                <p>Create your account to get started</p>
            </div>

            <?php $flash = getFlash(); ?>
            <?php if ($flash): ?>
            <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show" role="alert" style="font-size:0.85rem">
                <?= $flash['message'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <form class="auth-form" action="<?= url('/register/student') ?>" method="POST" data-ajax="true" data-validate>
                <?= CsrfMiddleware::tokenField() ?>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label" for="first_name">First Name *</label>
                            <input type="text" class="form-control" id="first_name" name="first_name" placeholder="First name" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label" for="last_name">Last Name *</label>
                            <input type="text" class="form-control" id="last_name" name="last_name" placeholder="Last name" required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label" for="email">Email Address *</label>
                            <input type="email" class="form-control" id="email" name="email" placeholder="your@email.com" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label" for="phone">Phone Number *</label>
                            <input type="text" class="form-control" id="phone" name="phone" placeholder="10-digit number" maxlength="10" required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label" for="password">Password *</label>
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
                            <label class="form-label" for="confirm_password">Confirm Password *</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm password" required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label" for="branch">Branch *</label>
                            <select class="form-select" id="branch" name="branch" required>
                                <option value="">Select Branch</option>
                                <?php foreach (BRANCHES as $branch): ?>
                                <option value="<?= $branch ?>"><?= $branch ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label" for="enrollment_no">Enrollment No</label>
                            <input type="text" class="form-control" id="enrollment_no" name="enrollment_no" placeholder="e.g. CS2020001">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label" for="dob">Date of Birth</label>
                            <input type="date" class="form-control" id="dob" name="dob">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label" for="gender">Gender</label>
                            <select class="form-select" id="gender" name="gender">
                                <option value="">Select</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label" for="cgpa">CGPA</label>
                            <input type="number" class="form-control" id="cgpa" name="cgpa" step="0.01" min="0" max="10" placeholder="e.g. 8.50">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label" for="admission_year">Admission Year</label>
                            <select class="form-select" id="admission_year" name="admission_year">
                                <option value="">Select</option>
                                <?php for ($y = 2015; $y <= date('Y'); $y++): ?>
                                <option value="<?= $y ?>"><?= $y ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label" for="passing_year">Expected Passing Year</label>
                            <select class="form-select" id="passing_year" name="passing_year">
                                <option value="">Select</option>
                                <?php for ($y = 2019; $y <= 2030; $y++): ?>
                                <option value="<?= $y ?>"><?= $y ?></option>
                                <?php endfor; ?>
                            </select>
                            <small class="text-muted" style="font-size:0.78rem">Auto-filled when you select Admission Year</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label" for="tenth_percentage">10th %</label>
                            <input type="number" class="form-control" id="tenth_percentage" name="tenth_percentage" step="0.01" min="0" max="100" placeholder="e.g. 90.50">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label" for="twelfth_percentage">12th %</label>
                            <input type="number" class="form-control" id="twelfth_percentage" name="twelfth_percentage" step="0.01" min="0" max="100" placeholder="e.g. 88.00">
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-login mt-2">
                    <i class="fas fa-user-plus me-2"></i> Create Account
                </button>
            </form>

            <div class="auth-footer">
                Already have an account? <a href="<?= url('/login') ?>">Sign In</a>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="<?= asset('js/app.js') ?>"></script>
<script src="<?= asset('js/auth.js') ?>"></script>
<script>
// Auto-compute passing year from admission year
document.getElementById('admission_year')?.addEventListener('change', function() {
    const admYear = parseInt(this.value);
    if (!isNaN(admYear)) {
        const passYear = admYear + 4;
        const sel = document.getElementById('passing_year');
        for (let i = 0; i < sel.options.length; i++) {
            if (parseInt(sel.options[i].value) === passYear) {
                sel.selectedIndex = i;
                break;
            }
        }
    }
});
</script>
</body>
</html>
