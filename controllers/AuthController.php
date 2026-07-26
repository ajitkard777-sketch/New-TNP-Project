<?php
/**
 * TPMS - Authentication Controller
 */

require_once ROOT_PATH . '/models/User.php';
require_once ROOT_PATH . '/includes/Mailer.php';

class AuthController {
    private User $userModel;
    private Database $db;

    public function __construct() {
        $this->userModel = new User();
        $this->db = Database::getInstance();
    }

    /**
     * Show login page
     */
    public function loginPage(): void {
        $pageTitle = 'Login';
        require_once VIEWS_PATH . '/auth/login.php';
    }

    /**
     * Process login
     */
    public function login(): void {
        CsrfMiddleware::requireValidToken();

        $email = sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = sanitize($_POST['role'] ?? 'student');
        $rememberMe = isset($_POST['remember_me']);

        // Validation
        $errors = [];
        if (empty($email)) $errors[] = 'Email is required.';
        if (empty($password)) $errors[] = 'Password is required.';
        if (!isValidEmail($email)) $errors[] = 'Invalid email format.';

        if (!empty($errors)) {
            if (isAjax()) {
                jsonResponse(['success' => false, 'errors' => $errors]);
            }
            setFlash('danger', implode('<br>', $errors));
            redirect('/login');
            return;
        }

        // Find user
        $user = $this->userModel->findByEmail($email);

        if (!$user) {
            if (isAjax()) {
                jsonResponse(['success' => false, 'message' => 'Invalid email or password.']);
            }
            setFlash('danger', 'Invalid email or password.');
            redirect('/login');
            return;
        }

        // Check role
        if ($user['role'] !== $role) {
            if (isAjax()) {
                jsonResponse(['success' => false, 'message' => 'Invalid login type selected.']);
            }
            setFlash('danger', 'Invalid login type selected. Please choose the correct role.');
            redirect('/login');
            return;
        }

        // Check if locked
        if ($this->userModel->isLocked($user['id'])) {
            if (isAjax()) {
                jsonResponse(['success' => false, 'message' => 'Account locked. Try again later.']);
            }
            setFlash('danger', 'Account is temporarily locked due to too many failed login attempts. Please try again later.');
            redirect('/login');
            return;
        }

        // ── Company-specific status checks ────────────────────────────────────
        if ($user['role'] === 'company') {
            $company = $this->db->fetchOne(
                "SELECT is_approved, COALESCE(is_rejected,0) as is_rejected, rejection_reason FROM companies WHERE user_id = ?",
                [$user['id']]
            );

            // Rejected — blocked at account level but give specific message
            if ($company && $company['is_rejected']) {
                $msg = 'Your registration has been rejected by the administrator.';
                if (!empty($company['rejection_reason'])) {
                    $msg .= ' Reason: ' . htmlspecialchars($company['rejection_reason']);
                }
                $msg .= ' Please contact the administrator for further assistance.';
                if (isAjax()) { jsonResponse(['success' => false, 'message' => $msg]); }
                setFlash('danger', $msg);
                redirect('/login');
                return;
            }

            // Suspended (approved but blocked)
            if ($company && $company['is_approved'] && $user['status'] === 'blocked') {
                if (isAjax()) { jsonResponse(['success' => false, 'message' => 'Your company account has been suspended. Contact administrator.']); }
                setFlash('danger', 'Your company account has been suspended. Please contact the administrator.');
                redirect('/login');
                return;
            }

            // Pending approval
            if (!$company || !$company['is_approved']) {
                if (isAjax()) { jsonResponse(['success' => false, 'message' => 'Your company registration is awaiting admin verification.']); }
                setFlash('warning', 'Your company is awaiting admin verification. You will be notified once approved.');
                redirect('/login');
                return;
            }
        }

        // ── General status checks ─────────────────────────────────────────────
        if ($user['status'] === 'blocked') {
            if (isAjax()) { jsonResponse(['success' => false, 'message' => 'Your account has been blocked. Contact administrator.']); }
            setFlash('danger', 'Your account has been blocked. Please contact the administrator.');
            redirect('/login');
            return;
        }

        // Verify password
        if (!$this->userModel->verifyPassword($password, $user['password'])) {
            $this->userModel->incrementLoginAttempts($user['id']);
            if (isAjax()) {
                jsonResponse(['success' => false, 'message' => 'Invalid email or password.']);
            }
            setFlash('danger', 'Invalid email or password.');
            redirect('/login');
            return;
        }

        // Check email verification status
        if (!$user['email_verified']) {
            $_SESSION['verify_user_id'] = $user['id'];
            $_SESSION['verify_email']   = $user['email'];
            $_SESSION['verify_role']    = $user['role'];

            $cooldownCheck = $this->userModel->canResendOTP($user['id']);
            if ($cooldownCheck['can_resend']) {
                $otp = sprintf('%06d', random_int(100000, 999999));
                $this->userModel->setOTP($user['id'], $otp);
                $name = $user['email'];
                if ($user['role'] === 'student') {
                    $st = $this->db->fetchOne("SELECT first_name, last_name FROM students WHERE user_id = ?", [$user['id']]);
                    if ($st) $name = $st['first_name'] . ' ' . $st['last_name'];
                } elseif ($user['role'] === 'company') {
                    $cp = $this->db->fetchOne("SELECT contact_person FROM companies WHERE user_id = ?", [$user['id']]);
                    if ($cp) $name = $cp['contact_person'];
                }
                Mailer::sendOtp($user['email'], $name, $otp, $user['role']);
            }

            if (isAjax()) {
                jsonResponse([
                    'success' => false,
                    'message' => 'Your email address is not verified yet. A 6-digit OTP code has been sent to your email.',
                    'redirect' => url('/verify-email')
                ]);
            }

            setFlash('warning', 'Your email address is not verified yet. A 6-digit OTP code has been sent to your email.');
            redirect('/verify-email');
            return;
        }

        if ($user['status'] !== 'active') {
            if (isAjax()) { jsonResponse(['success' => false, 'message' => 'Your account is not active. Contact administrator.']); }
            setFlash('danger', 'Your account is not active. Please contact the administrator.');
            redirect('/login');
            return;
        }

        // Login successful
        $this->createSession($user);
        $this->userModel->updateLastLogin($user['id']);

        // Remember me
        if ($rememberMe) {
            $token = generateRandomString(64);
            $this->userModel->setRememberToken($user['id'], $token);
            setcookie('tpms_remember', $token, time() + REMEMBER_ME_DURATION, BASE_URL . '/', '', false, true);
        }

        // Log activity
        logActivity('login', 'auth', 'User logged in: ' . $user['email']);

        if (isAjax()) {
            jsonResponse([
                'success' => true,
                'message' => 'Login successful!',
                'redirect' => url("/{$user['role']}/dashboard")
            ]);
        }

        setFlash('success', 'Welcome back! Login successful.');
        redirect("/{$user['role']}/dashboard");
    }

    /**
     * Show student registration page
     */
    public function registerStudentPage(): void {
        $pageTitle = 'Student Registration';
        require_once VIEWS_PATH . '/auth/register-student.php';
    }

    /**
     * Process student registration
     */
    public function registerStudent(): void {
        CsrfMiddleware::requireValidToken();

        $data = sanitizeArray($_POST);
        $errors = [];

        // Validation
        if (empty($data['first_name'])) $errors[] = 'First name is required.';
        if (empty($data['last_name'])) $errors[] = 'Last name is required.';
        if (empty($data['email'])) $errors[] = 'Email is required.';
        if (!isValidEmail($data['email'] ?? '')) $errors[] = 'Invalid email format.';
        if (empty($data['password'])) $errors[] = 'Password is required.';
        if (!isStrongPassword($data['password'] ?? '')) $errors[] = 'Password must be at least 8 characters with uppercase, lowercase, number, and special character.';
        if ($data['password'] !== ($data['confirm_password'] ?? '')) $errors[] = 'Passwords do not match.';
        if (empty($data['phone'])) $errors[] = 'Phone number is required.';
        if (empty($data['branch'])) $errors[] = 'Branch is required.';

        // Check duplicate email
        if ($this->userModel->emailExists($data['email'] ?? '')) {
            $errors[] = 'Email already registered.';
        }

        // Check duplicate phone
        if (!empty($data['phone'])) {
            $existing = $this->db->fetchColumn(
                "SELECT COUNT(*) FROM students WHERE phone = ?",
                [$data['phone']]
            );
            if ($existing > 0) {
                $errors[] = 'Phone number already registered.';
            }
        }

        if (!empty($errors)) {
            if (isAjax()) {
                jsonResponse(['success' => false, 'errors' => $errors]);
            }
            setFlash('danger', implode('<br>', $errors));
            redirect('/register/student');
            return;
        }

        try {
            $this->db->beginTransaction();

            // Create user (unverified & pending until OTP verification)
            $userId = $this->userModel->create([
                'email' => $data['email'],
                'password' => $data['password'],
                'role' => 'student',
                'status' => 'pending',
                'email_verified' => 0
            ]);

            // Create student profile
            $this->db->insert(
                "INSERT INTO students (user_id, first_name, last_name, phone, dob, gender, branch, enrollment_no, admission_year, passing_year, tenth_percentage, twelfth_percentage, degree, cgpa, city, state, profile_completion) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                [
                    $userId,
                    $data['first_name'],
                    $data['last_name'],
                    $data['phone'],
                    $data['dob'] ?? null,
                    $data['gender'] ?? null,
                    $data['branch'],
                    $data['enrollment_no'] ?? null,
                    $data['admission_year'] ?? null,
                    $data['passing_year'] ?? null,
                    $data['tenth_percentage'] ?? null,
                    $data['twelfth_percentage'] ?? null,
                    $data['degree'] ?? 'B.Tech',
                    $data['cgpa'] ?? null,
                    $data['city'] ?? null,
                    $data['state'] ?? null,
                    30
                ]
            );

            // Create welcome notification
            $this->db->insert(
                "INSERT INTO notifications (user_id, title, message, type, category) VALUES (?, ?, ?, ?, ?)",
                [$userId, 'Welcome to TPMS!', 'Your registration is successful. Complete your profile to increase your visibility to companies.', 'success', 'system']
            );

            $this->db->commit();

            // Generate secure 6-digit OTP and send email
            $otp = sprintf('%06d', random_int(100000, 999999));
            $this->userModel->setOTP($userId, $otp);

            $fullName = $data['first_name'] . ' ' . $data['last_name'];
            Mailer::sendOtp($data['email'], $fullName, $otp, 'student');

            $_SESSION['verify_user_id'] = $userId;
            $_SESSION['verify_email']   = $data['email'];
            $_SESSION['verify_role']    = 'student';

            logActivity('register', 'auth', 'New student registered (pending OTP verification): ' . $data['email']);

            if (isAjax()) {
                jsonResponse([
                    'success' => true,
                    'message' => 'Registration successful! An OTP code has been sent to your email. Please verify your email.',
                    'redirect' => url('/verify-email')
                ]);
            }

            setFlash('info', 'Registration successful! An OTP code has been sent to ' . htmlspecialchars($data['email']) . '. Please enter it below to complete your registration.');
            redirect('/verify-email');

        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Registration Error: " . $e->getMessage());
            
            if (isAjax()) {
                jsonResponse(['success' => false, 'message' => 'Registration failed. Please try again.']);
            }
            setFlash('danger', 'Registration failed. Please try again.');
            redirect('/register/student');
        }
    }

    /**
     * Show company registration page
     */
    public function registerCompanyPage(): void {
        $pageTitle = 'Company Registration';
        require_once VIEWS_PATH . '/auth/register-company.php';
    }

    /**
     * Process company registration
     */
    public function registerCompany(): void {
        CsrfMiddleware::requireValidToken();

        $data = sanitizeArray($_POST);
        $errors = [];

        // Validation
        if (empty($data['company_name']))   $errors[] = 'Company name is required.';
        if (empty($data['email']))           $errors[] = 'Email is required.';
        if (!isValidEmail($data['email'] ?? '')) $errors[] = 'Invalid email format.';
        if (empty($data['password']))        $errors[] = 'Password is required.';
        if (!isStrongPassword($data['password'] ?? '')) {
            $errors[] = 'Password must be at least 8 characters with uppercase, lowercase, number, and special character.';
        }
        if ($data['password'] !== ($data['confirm_password'] ?? '')) $errors[] = 'Passwords do not match.';
        if (empty($data['contact_person']))  $errors[] = 'HR Name is required.';
        if (empty($data['contact_phone']))   $errors[] = 'Mobile number is required.';
        if (!empty($data['contact_phone']) && !preg_match('/^[0-9]{10}$/', $data['contact_phone'])) {
            $errors[] = 'Mobile number must be exactly 10 digits.';
        }

        // Email uniqueness
        if ($this->userModel->emailExists($data['email'] ?? '')) {
            $errors[] = 'This email address is already registered.';
        }

        // Mobile uniqueness — check companies table
        if (!empty($data['contact_phone'])) {
            $phoneUsed = $this->db->fetchColumn(
                "SELECT COUNT(*) FROM companies WHERE contact_phone = ?",
                [$data['contact_phone']]
            );
            if ($phoneUsed > 0) {
                $errors[] = 'This mobile number is already registered with another company.';
            }
        }

        if (!empty($errors)) {
            if (isAjax()) {
                jsonResponse(['success' => false, 'errors' => $errors]);
            }
            setFlash('danger', implode('<br>', $errors));
            redirect('/register/company');
            return;
        }

        try {
            $this->db->beginTransaction();

            $userId = $this->userModel->create([
                'email'          => $data['email'],
                'password'       => $data['password'],
                'role'           => 'company',
                'status'         => 'pending',
                'email_verified' => 0,
            ]);

            $this->db->insert(
                "INSERT INTO companies
                 (user_id, company_name, industry, company_type, website, description,
                  contact_person, contact_email, contact_phone, address, city, state,
                  employee_count, established_year, is_approved)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                [
                    $userId,
                    $data['company_name'],
                    $data['industry']       ?? null,
                    $data['company_type']   ?? 'other',
                    $data['website']        ?? null,
                    $data['description']    ?? null,
                    $data['contact_person'],
                    $data['email'],
                    $data['contact_phone'],
                    $data['address']        ?? null,
                    $data['city']           ?? null,
                    $data['state']          ?? null,
                    $data['employee_count'] ?? null,
                    !empty($data['established_year']) ? (int)$data['established_year'] : null,
                    0,
                ]
            );

            // Notify admin
            $this->db->insert(
                "INSERT INTO notifications (user_id, title, message, type, category) VALUES ((SELECT id FROM users WHERE role = 'admin' LIMIT 1), ?, ?, ?, ?)",
                ['New Company Registration', "Company '{$data['company_name']}' has registered and is pending approval.", 'warning', 'system']
            );

            $this->db->commit();

            // Generate secure 6-digit OTP and send email
            $otp = sprintf('%06d', random_int(100000, 999999));
            $this->userModel->setOTP($userId, $otp);

            $contactPerson = $data['contact_person'] ?? $data['company_name'];
            Mailer::sendOtp($data['email'], $contactPerson, $otp, 'company');

            $_SESSION['verify_user_id'] = $userId;
            $_SESSION['verify_email']   = $data['email'];
            $_SESSION['verify_role']    = 'company';

            logActivity('register', 'auth', 'New company registered (pending OTP verification): ' . $data['company_name']);

            if (isAjax()) {
                jsonResponse([
                    'success' => true,
                    'message' => 'Registration submitted! An OTP code has been sent to your email. Please verify your email address.',
                    'redirect' => url('/verify-email')
                ]);
            }

            setFlash('info', 'Registration submitted! An OTP code has been sent to ' . htmlspecialchars($data['email']) . '. Please enter it below to verify your email address.');
            redirect('/verify-email');

        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Company Registration Error: " . $e->getMessage());
            
            if (isAjax()) {
                jsonResponse(['success' => false, 'message' => 'Registration failed. Please try again.']);
            }
            setFlash('danger', 'Registration failed. Please try again.');
            redirect('/register/company');
        }
    }

    /**
     * Show forgot password page
     */
    public function forgotPasswordPage(): void {
        $pageTitle = 'Forgot Password';
        require_once VIEWS_PATH . '/auth/forgot-password.php';
    }

    /**
     * Process forgot password
     */
    public function forgotPassword(): void {
        CsrfMiddleware::requireValidToken();

        $email = sanitize($_POST['email'] ?? '');

        if (empty($email) || !isValidEmail($email)) {
            setFlash('danger', 'Please enter a valid email address.');
            redirect('/forgot-password');
            return;
        }

        $user = $this->userModel->findByEmail($email);

        if ($user) {
            $token = generateRandomString(64);
            $this->userModel->createPasswordReset($user['id'], $token);

            $resetLink = FULL_URL . '/reset-password?token=' . $token;

            // Send password reset email
            $sent = Mailer::sendPasswordReset(
                $user['email'],
                $user['email'], // name fallback to email since users table has no name
                $resetLink
            );

            if ($sent) {
                setFlash('success', '<i class="fas fa-check-circle me-2"></i>Password reset link has been sent to <strong>' . htmlspecialchars($email) . '</strong>. Please check your inbox (and spam folder).');
            } else {
                // Fallback: show link if email fails (dev mode)
                if (APP_ENV === 'development') {
                    setFlash('warning', '<i class="fas fa-exclamation-triangle me-2"></i>Email sending failed. Dev fallback — <a href="' . $resetLink . '">Click here to reset</a>.');
                } else {
                    setFlash('danger', 'Failed to send email. Please try again later or contact support.');
                }
            }

            // Send SMS notification for password reset if phone number exists
            require_once ROOT_PATH . '/services/SmsService.php';
            $phone = '';
            if ($user['role'] === 'student') {
                $phone = $this->db->fetchColumn("SELECT phone FROM students WHERE user_id = ?", [$user['id']]) ?: '';
            } elseif ($user['role'] === 'company') {
                $phone = $this->db->fetchColumn("SELECT contact_phone FROM companies WHERE user_id = ?", [$user['id']]) ?: '';
            }
            if (!empty($phone)) {
                $otpCode = sprintf('%06d', mt_rand(100000, 999999));
                SmsService::getInstance()->sendPasswordReset($phone, $otpCode, (int)$user['id']);
            }

            logActivity('forgot_password', 'auth', 'Password reset requested for: ' . $email);
        } else {
            // Don't reveal if email exists or not (security best practice)
            setFlash('success', '<i class="fas fa-check-circle me-2"></i>If that email is registered, a password reset link has been sent.');
        }

        redirect('/forgot-password');
    }

    /**
     * Show reset password page
     */
    public function resetPasswordPage(): void {
        $token = $_GET['token'] ?? '';
        
        if (empty($token)) {
            setFlash('danger', 'Invalid password reset link.');
            redirect('/forgot-password');
            return;
        }

        $reset = $this->userModel->verifyPasswordReset($token);
        if (!$reset) {
            setFlash('danger', 'Invalid or expired password reset link.');
            redirect('/forgot-password');
            return;
        }

        $pageTitle = 'Reset Password';
        require_once VIEWS_PATH . '/auth/reset-password.php';
    }

    /**
     * Process password reset
     */
    public function resetPassword(): void {
        CsrfMiddleware::requireValidToken();

        $token = sanitize($_POST['token'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        $errors = [];
        if (empty($password)) $errors[] = 'Password is required.';
        if (!isStrongPassword($password)) $errors[] = 'Password must be at least 8 characters with uppercase, lowercase, number, and special character.';
        if ($password !== $confirmPassword) $errors[] = 'Passwords do not match.';

        if (!empty($errors)) {
            setFlash('danger', implode('<br>', $errors));
            redirect('/reset-password?token=' . $token);
            return;
        }

        $reset = $this->userModel->verifyPasswordReset($token);
        if (!$reset) {
            setFlash('danger', 'Invalid or expired reset link.');
            redirect('/forgot-password');
            return;
        }

        $this->userModel->updatePassword($reset['user_id'], $password);
        $this->userModel->usePasswordReset($token);

        logActivity('password_reset', 'auth', 'Password reset for user ID: ' . $reset['user_id']);

        setFlash('success', 'Password has been reset successfully! Please login with your new password.');
        redirect('/login');
    }

    /**
     * Show email verification page
     */
    public function verifyEmailPage(): void {
        if (!isset($_SESSION['verify_user_id'])) {
            redirect('/login');
            return;
        }
        $pageTitle = 'Verify Email';
        $verifyEmail = $_SESSION['verify_email'] ?? '';
        $verifyRole  = $_SESSION['verify_role'] ?? 'user';
        require_once VIEWS_PATH . '/auth/verify-email.php';
    }

    /**
     * Process email verification
     */
    public function verifyEmail(): void {
        CsrfMiddleware::requireValidToken();

        $otp = sanitize($_POST['otp'] ?? '');
        $userId = $_SESSION['verify_user_id'] ?? null;

        if (!$userId) {
            if (isAjax()) {
                jsonResponse(['success' => false, 'message' => 'Session expired. Please register or login again.', 'redirect' => url('/login')]);
            }
            setFlash('danger', 'Session expired. Please register or login again.');
            redirect('/login');
            return;
        }

        if (empty($otp) || strlen($otp) !== 6 || !ctype_digit($otp)) {
            if (isAjax()) {
                jsonResponse(['success' => false, 'message' => 'Please enter a valid 6-digit numeric OTP.']);
            }
            setFlash('danger', 'Please enter a valid 6-digit numeric OTP.');
            redirect('/verify-email');
            return;
        }

        $result = $this->userModel->verifyOTP($userId, $otp);

        if (!$result['success']) {
            if (isAjax()) {
                jsonResponse(['success' => false, 'message' => $result['message']]);
            }
            setFlash('danger', $result['message']);
            redirect('/verify-email');
            return;
        }

        // OTP Verified successfully
        $user = $this->userModel->findById($userId);
        unset($_SESSION['verify_user_id'], $_SESSION['verify_email'], $_SESSION['verify_role']);

        if ($user && $user['role'] === 'student') {
            $this->userModel->activate($userId);
            logActivity('email_verified', 'auth', 'Student email verified: ' . $user['email']);

            if (isAjax()) {
                jsonResponse([
                    'success' => true,
                    'message' => 'Email verified successfully! Your student account is now active.',
                    'redirect' => url('/login')
                ]);
            }

            setFlash('success', 'Email verified successfully! Your account is active. Please login with your credentials.');
            redirect('/login');
        } else {
            // Company account keeps status pending for Admin Approval
            logActivity('email_verified', 'auth', 'Company email verified: ' . ($user['email'] ?? ''));

            if (isAjax()) {
                jsonResponse([
                    'success' => true,
                    'message' => 'Email verified successfully! Your company registration is now awaiting Admin approval.',
                    'redirect' => url('/login')
                ]);
            }

            setFlash('success', 'Email verified successfully! Your company account is now awaiting Admin verification before you can login.');
            redirect('/login');
        }
    }

    /**
     * Resend OTP
     */
    public function resendOtp(): void {
        CsrfMiddleware::requireValidToken();

        $userId = $_SESSION['verify_user_id'] ?? null;

        if (!$userId) {
            if (isAjax()) {
                jsonResponse(['success' => false, 'message' => 'Session expired. Please register or login again.', 'redirect' => url('/login')]);
            }
            setFlash('danger', 'Session expired. Please register or login again.');
            redirect('/login');
            return;
        }

        $cooldown = $this->userModel->canResendOTP($userId);
        if (!$cooldown['can_resend']) {
            $msg = "Please wait {$cooldown['remaining_seconds']} second(s) before requesting a new OTP.";
            if (isAjax()) {
                jsonResponse(['success' => false, 'message' => $msg, 'remaining_seconds' => $cooldown['remaining_seconds']]);
            }
            setFlash('warning', $msg);
            redirect('/verify-email');
            return;
        }

        $user = $this->userModel->findById($userId);
        if (!$user) {
            if (isAjax()) {
                jsonResponse(['success' => false, 'message' => 'User not found.', 'redirect' => url('/login')]);
            }
            redirect('/login');
            return;
        }

        $otp = sprintf('%06d', random_int(100000, 999999));
        $this->userModel->setOTP($userId, $otp);

        $name = $user['email'];
        if ($user['role'] === 'student') {
            $st = $this->db->fetchOne("SELECT first_name, last_name FROM students WHERE user_id = ?", [$userId]);
            if ($st) $name = $st['first_name'] . ' ' . $st['last_name'];
        } elseif ($user['role'] === 'company') {
            $cp = $this->db->fetchOne("SELECT contact_person FROM companies WHERE user_id = ?", [$userId]);
            if ($cp) $name = $cp['contact_person'];
        }

        $sent = Mailer::sendOtp($user['email'], $name, $otp, $user['role']);

        if ($sent) {
            $msg = 'A new 6-digit OTP code has been sent to ' . htmlspecialchars($user['email']) . '.';
            if (isAjax()) {
                jsonResponse(['success' => true, 'message' => $msg, 'cooldown' => 60]);
            }
            setFlash('success', $msg);
        } else {
            $msg = 'Failed to send OTP email via SMTP. Please try again later.';
            if (isAjax()) {
                jsonResponse(['success' => false, 'message' => $msg]);
            }
            setFlash('danger', $msg);
        }

        redirect('/verify-email');
    }

    /**
     * Logout
     */
    public function logout(): void {
        $userId = $_SESSION['user_id'] ?? null;
        
        if ($userId) {
            $this->userModel->clearRememberToken($userId);
            logActivity('logout', 'auth', 'User logged out');
        }

        // Clear session
        $_SESSION = [];
        
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        session_destroy();

        // Clear remember me cookie
        if (isset($_COOKIE['tpms_remember'])) {
            setcookie('tpms_remember', '', time() - 3600, BASE_URL . '/');
        }

        setFlash('success', 'You have been logged out successfully.');
        redirect('/login');
    }

    /**
     * Create user session
     */
    private function createSession(array $user): void {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['login_time'] = time();
        // Guard: theme_preference column may not exist on older DB versions
        if (array_key_exists('theme_preference', $user) && !empty($user['theme_preference'])) {
            $_SESSION['user_theme'] = $user['theme_preference'];
            setcookie('tpms_theme', $user['theme_preference'], time() + 31536000, '/', '', false, false);
        }
    }
}
