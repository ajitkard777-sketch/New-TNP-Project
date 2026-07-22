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

        // Check status
        if ($user['status'] === 'blocked') {
            setFlash('danger', 'Your account has been blocked. Please contact the administrator.');
            redirect('/login');
            return;
        }

        if ($user['status'] === 'pending') {
            // For companies, check approval
            if ($user['role'] === 'company') {
                $company = $this->db->fetchOne("SELECT is_approved FROM companies WHERE user_id = ?", [$user['id']]);
                if (!$company || !$company['is_approved']) {
                    setFlash('warning', 'Your company registration is pending approval. Please wait for admin approval.');
                    redirect('/login');
                    return;
                }
            }
            
            // Check email verification
            if (!$user['email_verified']) {
                $_SESSION['verify_user_id'] = $user['id'];
                // Generate and set OTP
                $otp = generateOTP();
                $this->userModel->setOTP($user['id'], $otp);
                setFlash('info', 'Please verify your email first. OTP: ' . $otp . ' (In production, this would be sent via email)');
                redirect('/verify-email');
                return;
            }
        }

        if ($user['status'] !== 'active') {
            setFlash('danger', 'Your account is not active. Please contact the administrator.');
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

            // Create user
            $userId = $this->userModel->create([
                'email' => $data['email'],
                'password' => $data['password'],
                'role' => 'student',
                'status' => 'active',
                'email_verified' => 1
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

            logActivity('register', 'auth', 'New student registered: ' . $data['email']);

            if (isAjax()) {
                jsonResponse(['success' => true, 'message' => 'Registration successful! Please login.', 'redirect' => url('/login')]);
            }

            setFlash('success', 'Registration successful! Please login with your credentials.');
            redirect('/login');

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
                'email_verified' => 1
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

            // Notify admin
            $this->db->insert(
                "INSERT INTO notifications (user_id, title, message, type, category) VALUES ((SELECT id FROM users WHERE role = 'admin' LIMIT 1), ?, ?, ?, ?)",
                ['New Company Registration', "Company '{$data['company_name']}' has registered and is pending approval.", 'warning', 'system']
            );

            $this->db->commit();

            logActivity('register', 'auth', 'New company registered: ' . $data['company_name']);

            if (isAjax()) {
                jsonResponse(['success' => true, 'message' => 'Registration submitted! Awaiting admin approval.', 'redirect' => url('/login')]);
            }

            setFlash('success', 'Registration submitted successfully! Your account will be activated after admin approval.');
            redirect('/login');

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
        $userId = $_SESSION['verify_user_id'] ?? null;

        if (!$userId) {
            redirect('/login');
            return;
        }

        if (empty($otp)) {
            setFlash('danger', 'Please enter the OTP.');
            redirect('/verify-email');
            return;
        }

        if ($this->userModel->verifyOTP($userId, $otp)) {
            $this->userModel->activate($userId);
            unset($_SESSION['verify_user_id']);
            setFlash('success', 'Email verified successfully! Please login.');
            redirect('/login');
        } else {
            setFlash('danger', 'Invalid or expired OTP. Please try again.');
            redirect('/verify-email');
        }
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
