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

        // Verify password FIRST
        if (!$this->userModel->verifyPassword($password, $user['password'])) {
            $this->userModel->incrementLoginAttempts($user['id']);
            if (isAjax()) {
                jsonResponse(['success' => false, 'message' => 'Invalid email or password.']);
            }
            setFlash('danger', 'Invalid email or password.');
            redirect('/login');
            return;
        }

        // Check if email is verified
        if (!$user['email_verified']) {
            $_SESSION['verify_user_id'] = $user['id'];
            $_SESSION['verify_email'] = $user['email'];

            // Resend OTP if missing or expired
            if (empty($user['otp']) || empty($user['otp_expires_at']) || strtotime($user['otp_expires_at']) < time()) {
                $otp = $this->userModel->generateAndSaveOTP($user['id']);
                $name = $user['email'];
                if ($user['role'] === 'student') {
                    $stu = $this->db->fetchOne("SELECT first_name FROM students WHERE user_id = ?", [$user['id']]);
                    if ($stu) $name = $stu['first_name'];
                } elseif ($user['role'] === 'company') {
                    $comp = $this->db->fetchOne("SELECT contact_person FROM companies WHERE user_id = ?", [$user['id']]);
                    if ($comp) $name = $comp['contact_person'];
                }
                Mailer::sendOtpVerification($user['email'], $name, $otp);
            }

            $msg = 'Please verify your email address to continue. A 6-digit OTP code has been sent to your email.';
            if (isAjax()) {
                jsonResponse(['success' => false, 'message' => $msg, 'redirect' => url('/verify-email')]);
            }
            setFlash('warning', $msg);
            redirect('/verify-email');
            return;
        }

        // Check status
        if ($user['status'] === 'blocked') {
            setFlash('danger', 'Your account has been blocked. Please contact the administrator.');
            redirect('/login');
            return;
        }

        // For companies, check admin approval
        if ($user['role'] === 'company') {
            $company = $this->db->fetchOne("SELECT is_approved FROM companies WHERE user_id = ?", [$user['id']]);
            if (!$company || !$company['is_approved']) {
                setFlash('warning', 'Your email is verified, but your company account is pending Admin approval.');
                redirect('/login');
                return;
            }
        }

        if ($user['status'] !== 'active' && $user['role'] !== 'company') {
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

            // Create user (pending email verification)
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

            // Generate 6-digit OTP
            $otp = $this->userModel->generateAndSaveOTP($userId);

            // Create welcome notification
            $this->db->insert(
                "INSERT INTO notifications (user_id, title, message, type, category) VALUES (?, ?, ?, ?, ?)",
                [$userId, 'Welcome to TPMS!', 'Your registration was submitted. Please verify your email with the 6-digit OTP code sent to you.', 'info', 'system']
            );

            $this->db->commit();

            // Send OTP email
            $fullName = trim($data['first_name'] . ' ' . $data['last_name']);
            Mailer::sendOtpVerification($data['email'], $fullName, $otp);

            logActivity('register', 'auth', 'New student registered (OTP pending): ' . $data['email']);

            $_SESSION['verify_user_id'] = $userId;
            $_SESSION['verify_email'] = $data['email'];

            if (isAjax()) {
                jsonResponse([
                    'success' => true,
                    'message' => 'Registration successful! A 6-digit verification code has been sent to your email.',
                    'redirect' => url('/verify-email')
                ]);
            }

            setFlash('info', 'Registration successful! Please enter the 6-digit verification code sent to your email.');
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
        if (empty($data['company_name'])) $errors[] = 'Company name is required.';
        if (empty($data['email'])) $errors[] = 'Email is required.';
        if (!isValidEmail($data['email'] ?? '')) $errors[] = 'Invalid email format.';
        if (empty($data['password'])) $errors[] = 'Password is required.';
        if (!isStrongPassword($data['password'] ?? '')) $errors[] = 'Password must be at least 8 characters with uppercase, lowercase, number, and special character.';
        if ($data['password'] !== ($data['confirm_password'] ?? '')) $errors[] = 'Passwords do not match.';
        if (empty($data['contact_person'])) $errors[] = 'Contact person name is required.';
        if (empty($data['contact_phone'])) $errors[] = 'Contact phone is required.';

        if ($this->userModel->emailExists($data['email'] ?? '')) {
            $errors[] = 'Email already registered.';
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
                'email' => $data['email'],
                'password' => $data['password'],
                'role' => 'company',
                'status' => 'pending',
                'email_verified' => 0
            ]);

            $this->db->insert(
                "INSERT INTO companies (user_id, company_name, industry, company_type, website, description, contact_person, contact_email, contact_phone, city, state, employee_count, established_year, is_approved) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                [
                    $userId,
                    $data['company_name'],
                    $data['industry'] ?? null,
                    $data['company_type'] ?? 'other',
                    $data['website'] ?? null,
                    $data['description'] ?? null,
                    $data['contact_person'],
                    $data['email'],
                    $data['contact_phone'],
                    $data['city'] ?? null,
                    $data['state'] ?? null,
                    $data['employee_count'] ?? null,
                    $data['established_year'] ?? null,
                    0
                ]
            );

            // Generate 6-digit OTP
            $otp = $this->userModel->generateAndSaveOTP($userId);

            // Notify admin
            $this->db->insert(
                "INSERT INTO notifications (user_id, title, message, type, category) VALUES ((SELECT id FROM users WHERE role = 'admin' LIMIT 1), ?, ?, ?, ?)",
                ['New Company Registration', "Company '{$data['company_name']}' registered and is pending email verification & approval.", 'info', 'system']
            );

            $this->db->commit();

            // Send OTP email
            Mailer::sendOtpVerification($data['email'], $data['contact_person'] ?? $data['company_name'], $otp);

            logActivity('register', 'auth', 'New company registered (OTP pending): ' . $data['company_name']);

            $_SESSION['verify_user_id'] = $userId;
            $_SESSION['verify_email'] = $data['email'];

            if (isAjax()) {
                jsonResponse([
                    'success' => true,
                    'message' => 'Company registration submitted! Please enter the 6-digit OTP code sent to your email.',
                    'redirect' => url('/verify-email')
                ]);
            }

            setFlash('info', 'Registration submitted! Please verify your email with the 6-digit OTP sent to your email.');
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
        require_once VIEWS_PATH . '/auth/verify-email.php';
    }

    /**
     * Process email verification
     */
    public function verifyEmail(): void {
        CsrfMiddleware::requireValidToken();

        $otp = sanitize($_POST['otp'] ?? '');
        $userId = $_SESSION['verify_user_id'] ?? (is_numeric($_POST['user_id'] ?? null) ? (int)$_POST['user_id'] : null);

        if (!$userId) {
            if (isAjax()) {
                jsonResponse(['success' => false, 'message' => 'Verification session expired. Please log in to request a new OTP.']);
            }
            setFlash('danger', 'Verification session expired. Please log in to verify your email.');
            redirect('/login');
            return;
        }

        if (empty($otp) || strlen(trim($otp)) < 6) {
            if (isAjax()) {
                jsonResponse(['success' => false, 'message' => 'Please enter the complete 6-digit OTP code.']);
            }
            setFlash('danger', 'Please enter the complete 6-digit OTP code.');
            redirect('/verify-email');
            return;
        }

        $res = $this->userModel->verifyOTP($userId, $otp);

        if ($res['success']) {
            unset($_SESSION['verify_user_id'], $_SESSION['verify_email']);
            logActivity('verify_email', 'auth', 'Email verified for user ID: ' . $userId);

            if (isAjax()) {
                jsonResponse([
                    'success' => true,
                    'message' => $res['message'],
                    'redirect' => url('/login')
                ]);
            }
            setFlash('success', $res['message']);
            redirect('/login');
        } else {
            if (isAjax()) {
                jsonResponse(['success' => false, 'message' => $res['message']]);
            }
            setFlash('danger', $res['message']);
            redirect('/verify-email');
        }
    }

    /**
     * Resend OTP code with 60s cooldown check
     */
    public function resendOTP(): void {
        $userId = $_SESSION['verify_user_id'] ?? (is_numeric($_POST['user_id'] ?? $_GET['user_id'] ?? null) ? (int)($_POST['user_id'] ?? $_GET['user_id']) : null);

        if (!$userId) {
            if (isAjax()) jsonResponse(['success' => false, 'message' => 'Verification session expired. Please log in again.']);
            setFlash('danger', 'Verification session expired. Please log in again.');
            redirect('/login');
            return;
        }

        $check = $this->userModel->canResendOTP($userId);
        if (!$check['allowed']) {
            if (isAjax()) jsonResponse(['success' => false, 'message' => $check['message'], 'wait' => $check['wait']]);
            setFlash('warning', $check['message']);
            redirect('/verify-email');
            return;
        }

        $user = $this->userModel->findById($userId);
        if (!$user) {
            if (isAjax()) jsonResponse(['success' => false, 'message' => 'User not found.']);
            setFlash('danger', 'User account not found.');
            redirect('/login');
            return;
        }

        $otp = $this->userModel->generateAndSaveOTP($userId);

        $name = $user['email'];
        if ($user['role'] === 'student') {
            $stu = $this->db->fetchOne("SELECT first_name, last_name FROM students WHERE user_id = ?", [$userId]);
            if ($stu) $name = trim($stu['first_name'] . ' ' . $stu['last_name']);
        } elseif ($user['role'] === 'company') {
            $comp = $this->db->fetchOne("SELECT contact_person, company_name FROM companies WHERE user_id = ?", [$userId]);
            if ($comp) $name = $comp['contact_person'] ?: $comp['company_name'];
        }

        $sent = Mailer::sendOtpVerification($user['email'], $name, $otp);

        logActivity('resend_otp', 'auth', 'OTP resent to: ' . $user['email']);

        $msg = 'A new 6-digit OTP code has been sent to ' . htmlspecialchars($user['email']) . '.';
        if (isAjax()) {
            jsonResponse(['success' => true, 'message' => $msg, 'cooldown' => 60]);
        }

        setFlash('success', $msg);
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
        if (!empty($user['theme_preference'])) {
            $_SESSION['user_theme'] = $user['theme_preference'];
            setcookie('tpms_theme', $user['theme_preference'], time() + 31536000, '/', '', false, false);
        }
    }
}
