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

        // Check email verification first
        if (!$user['email_verified']) {
            $_SESSION['verify_user_id'] = $user['id'];
            $_SESSION['verify_user_email'] = $user['email'];
            $_SESSION['verify_user_role'] = $user['role'];

            // Determine display name: use company name for companies, email as fallback
            $recipientName = $user['email'];
            if ($user['role'] === 'company') {
                $companyRow = $this->db->fetchOne("SELECT company_name FROM companies WHERE user_id = ?", [$user['id']]);
                if ($companyRow && !empty($companyRow['company_name'])) {
                    $recipientName = $companyRow['company_name'];
                }
            }

            // Generate fresh OTP and send
            $otp = generateOTP();
            $this->userModel->setOTP($user['id'], $otp);
            $otpSent = Mailer::sendOTP($user['email'], $recipientName, $otp);

            if (isAjax()) {
                jsonResponse(['success' => false, 'message' => 'Email not verified. OTP sent to your email.', 'email_sent' => $otpSent, 'redirect' => url('/verify-email')]);
            }

            if ($otpSent) {
                setFlash('warning', 'Please verify your email before logging in. A new 6-digit OTP has been sent to <strong>' . htmlspecialchars($user['email']) . '</strong>.');
            } else {
                $mailErr = Mailer::getLastError();
                $errDetail = !empty($mailErr) ? htmlspecialchars($mailErr) : 'SMTP connection failed.';
                setFlash('warning', 'Please verify your email. OTP email delivery failed: <strong>' . $errDetail . '</strong>. Please use Resend OTP on the next page.');
            }
            redirect('/verify-email');
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
            if (isAjax()) {
                jsonResponse(['success' => false, 'message' => 'Your account has been blocked. Please contact admin.']);
            }
            setFlash('danger', 'Your account has been blocked. Please contact the administrator.');
            redirect('/login');
            return;
        }

        if ($user['status'] === 'pending') {
            // For companies, check admin approval
            if ($user['role'] === 'company') {
                $company = $this->db->fetchOne("SELECT is_approved FROM companies WHERE user_id = ?", [$user['id']]);
                if (!$company || !$company['is_approved']) {
                    if (isAjax()) {
                        jsonResponse(['success' => false, 'message' => 'Your company registration is pending approval by the administrator.']);
                    }
                    setFlash('warning', 'Your company registration has been email verified, but is pending approval by the administrator. Please wait for admin approval.');
                    redirect('/login');
                    return;
                }
            }
        }

        if ($user['status'] !== 'active' && $user['status'] !== 'pending') {
            if (isAjax()) {
                jsonResponse(['success' => false, 'message' => 'Your account is not active. Please contact administrator.']);
            }
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
        if (empty($data['branch'])) $errors[] = 'Branch is required.';

        // Phone validation — required, exactly 10 digits
        $phoneResult = Validator::phone($data['phone'] ?? '');
        if (!$phoneResult['valid']) $errors[] = $phoneResult['message'];

        // Optional city/state validation
        if (!empty($data['city'])) {
            $cityResult = Validator::city($data['city']);
            if (!$cityResult['valid']) $errors[] = $cityResult['message'];
        }
        if (!empty($data['state'])) {
            $stateResult = Validator::state($data['state']);
            if (!$stateResult['valid']) $errors[] = $stateResult['message'];
        }

        // Check duplicate email
        if ($this->userModel->emailExists($data['email'] ?? '')) {
            $errors[] = 'Email already registered.';
        }

        // Check duplicate phone (only if phone format is valid)
        if ($phoneResult['valid'] && !empty($data['phone'])) {
            $existing = $this->db->fetchColumn(
                "SELECT COUNT(*) FROM students WHERE phone = ?",
                [trim($data['phone'])]
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

            // Create user (unverified until OTP checked)
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

            // Generate secure 6-digit OTP & send email
            $otp = generateOTP();
            $this->userModel->setOTP($userId, $otp);
            $studentName = $data['first_name'] . ' ' . $data['last_name'];
            $emailSent = Mailer::sendOTP($data['email'], $studentName, $otp);

            $this->db->commit();

            logActivity('register', 'auth', 'New student registered (pending OTP): ' . $data['email']);

            // Set session verification context
            $_SESSION['verify_user_id'] = $userId;
            $_SESSION['verify_user_email'] = $data['email'];
            $_SESSION['verify_user_role'] = 'student';

            if ($emailSent) {
                $msg = 'Registration successful! Verification code sent to <strong>' . htmlspecialchars($data['email']) . '</strong>.';
                setFlash('success', $msg);
            } else {
                $mailErr = Mailer::getLastError();
                $errDetail = !empty($mailErr) ? htmlspecialchars($mailErr) : 'Failed to connect to SMTP server.';
                $msg = 'Registration completed, but verification email failed to deliver: <strong>' . $errDetail . '</strong>.';
                if (APP_ENV === 'development') {
                    $msg .= ' (Dev Mode OTP: <strong>' . $otp . '</strong>)';
                }
                setFlash('warning', $msg);
            }

            if (isAjax()) {
                jsonResponse(['success' => true, 'message' => $msg, 'email_sent' => $emailSent, 'redirect' => url('/verify-email')]);
            }

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

        // Phone validation — required, exactly 10 digits
        $phoneResult = Validator::phone($data['contact_phone'] ?? '');
        if (!$phoneResult['valid']) $errors[] = $phoneResult['message'];

        // Website URL validation — optional but must be valid if provided
        if (!empty($data['website'])) {
            $websiteResult = Validator::optionalUrl($data['website'], 'Website URL');
            if (!$websiteResult['valid']) $errors[] = $websiteResult['message'];
        }

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

            // Create company user (unverified and pending admin approval)
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

            // Generate secure OTP & send email
            $otp = generateOTP();
            $this->userModel->setOTP($userId, $otp);
            $emailSent = Mailer::sendOTP($data['email'], $data['company_name'], $otp);

            $this->db->commit();

            logActivity('register', 'auth', 'New company registered (pending OTP & Admin approval): ' . $data['company_name']);

            // Set session verification context
            $_SESSION['verify_user_id'] = $userId;
            $_SESSION['verify_user_email'] = $data['email'];
            $_SESSION['verify_user_role'] = 'company';

            if ($emailSent) {
                $msg = 'Registration submitted! Verification code sent to <strong>' . htmlspecialchars($data['email']) . '</strong>.';
                setFlash('success', $msg);
            } else {
                $mailErr = Mailer::getLastError();
                $errDetail = !empty($mailErr) ? htmlspecialchars($mailErr) : 'Failed to connect to SMTP server.';
                $msg = 'Registration submitted, but verification email failed to deliver: <strong>' . $errDetail . '</strong>.';
                if (APP_ENV === 'development') {
                    $msg .= ' (Dev Mode OTP: <strong>' . $otp . '</strong>)';
                }
                setFlash('warning', $msg);
            }

            if (isAjax()) {
                jsonResponse(['success' => true, 'message' => $msg, 'email_sent' => $emailSent, 'redirect' => url('/verify-email')]);
            }

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
                logActivity('forgot_password', 'auth', 'Password reset email sent to: ' . $email);
            } else {
                $mailErr = Mailer::getLastError();
                $errDetail = !empty($mailErr) ? htmlspecialchars($mailErr) : 'Failed to connect to SMTP server.';
                
                if (APP_ENV === 'development') {
                    setFlash('danger', '<i class="fas fa-exclamation-triangle me-2"></i>Email delivery failed: <strong>' . $errDetail . '</strong><br><small class="mt-1 d-block">Dev link: <a href="' . $resetLink . '" target="_blank">' . $resetLink . '</a></small>');
                } else {
                    setFlash('danger', '<i class="fas fa-exclamation-triangle me-2"></i>Failed to send reset email. Please try again later or contact support. Details: ' . $errDetail);
                }
                logActivity('forgot_password_failed', 'auth', 'Password reset email failed for ' . $email . ': ' . $mailErr);
            }
        } else {
            setFlash('danger', '<i class="fas fa-exclamation-circle me-2"></i>No account found with email <strong>' . htmlspecialchars($email) . '</strong>. Please check your spelling or register for a new account.');
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
        $userId = $_SESSION['verify_user_id'] ?? null;
        if (!$userId) {
            redirect('/login');
            return;
        }

        $user = $this->userModel->findById($userId);
        if (!$user) {
            unset($_SESSION['verify_user_id'], $_SESSION['verify_user_email'], $_SESSION['verify_user_role']);
            setFlash('danger', 'User account not found. Please register or log in.');
            redirect('/login');
            return;
        }

        if ($user['email_verified']) {
            unset($_SESSION['verify_user_id'], $_SESSION['verify_user_email'], $_SESSION['verify_user_role']);
            setFlash('info', 'Your email is already verified. Please log in.');
            redirect('/login');
            return;
        }

        // Calculate timer countdown values
        $resendInfo = $this->userModel->canResendOTP($userId);
        $cooldownSeconds = $resendInfo['seconds_remaining'];

        $expiresAt = !empty($user['otp_expires_at']) ? strtotime($user['otp_expires_at']) : (time() + OTP_EXPIRY);
        $expirySeconds = max(0, $expiresAt - time());

        $userEmail = $user['email'];
        $userRole  = $user['role'];
        $pageTitle = 'Verify Email';

        require_once VIEWS_PATH . '/auth/verify-email.php';
    }

    /**
     * Process email verification
     */
    public function verifyEmail(): void {
        CsrfMiddleware::requireValidToken();

        $userId = $_SESSION['verify_user_id'] ?? null;
        if (!$userId) {
            if (isAjax()) {
                jsonResponse(['success' => false, 'message' => 'Session expired. Please log in or register again.', 'redirect' => url('/login')]);
            }
            setFlash('danger', 'Session expired. Please log in or register again.');
            redirect('/login');
            return;
        }

        // OTP code could come as string 'otp' or array of 6 digits 'otp_digit'
        $otp = '';
        if (isset($_POST['otp']) && is_string($_POST['otp'])) {
            $otp = trim($_POST['otp']);
        } elseif (isset($_POST['otp_digit']) && is_array($_POST['otp_digit'])) {
            $otp = implode('', array_map('trim', $_POST['otp_digit']));
        }

        $otp = sanitize($otp);

        if (empty($otp) || strlen($otp) !== 6 || !ctype_digit($otp)) {
            if (isAjax()) {
                jsonResponse(['success' => false, 'message' => 'Please enter a valid 6-digit numeric OTP code.']);
            }
            setFlash('danger', 'Please enter a valid 6-digit numeric OTP code.');
            redirect('/verify-email');
            return;
        }

        $verifyResult = $this->userModel->verifyOTPResult($userId, $otp);

        if ($verifyResult['success']) {
            $user = $this->userModel->findById($userId);
            logActivity('verify_email', 'auth', 'Email verified successfully for: ' . $user['email']);

            if ($user['role'] === 'student') {
                // Activate student account
                $this->userModel->activate($userId);
                $student = $this->db->fetchOne("SELECT first_name, last_name FROM students WHERE user_id = ?", [$userId]);
                $studentName = $student ? $student['first_name'] . ' ' . $student['last_name'] : $user['email'];

                Mailer::sendWelcome($user['email'], $studentName);

                unset($_SESSION['verify_user_id'], $_SESSION['verify_user_email'], $_SESSION['verify_user_role']);

                $msg = 'Email verified successfully! Your account is now active. Please log in.';
                if (isAjax()) {
                    jsonResponse(['success' => true, 'message' => $msg, 'redirect' => url('/login')]);
                }
                setFlash('success', $msg);
                redirect('/login');
                return;
            } elseif ($user['role'] === 'company') {
                // Company remains pending admin approval after email verification
                $this->db->update("UPDATE users SET email_verified = 1, status = 'pending' WHERE id = ?", [$userId]);

                // Get company details
                $company = $this->db->fetchOne("SELECT company_name FROM companies WHERE user_id = ?", [$userId]);
                $companyName = $company['company_name'] ?? $user['email'];

                // Notify admin in-app
                $this->db->insert(
                    "INSERT INTO notifications (user_id, title, message, type, category) VALUES ((SELECT id FROM users WHERE role = 'admin' LIMIT 1), ?, ?, ?, ?)",
                    ['Company Pending Approval', "Company '{$companyName}' has verified their email and is pending admin approval.", 'warning', 'system']
                );

                // Send confirmation email to company HR
                Mailer::sendCompanyPendingApproval($user['email'], $companyName);

                // Email admin as well about pending company
                $adminUser = $this->db->fetchOne("SELECT email FROM users WHERE role = 'admin' LIMIT 1");
                if ($adminUser && !empty($adminUser['email'])) {
                    Mailer::sendCompanyRegistrationAlert($adminUser['email'], $companyName, $user['email']);
                }

                unset($_SESSION['verify_user_id'], $_SESSION['verify_user_email'], $_SESSION['verify_user_role']);

                $msg = 'Email verified successfully! Your company registration is now <strong>pending admin approval</strong>. A confirmation email has been sent to <strong>' . htmlspecialchars($user['email']) . '</strong>.';
                if (isAjax()) {
                    jsonResponse(['success' => true, 'message' => $msg, 'redirect' => url('/login')]);
                }
                setFlash('success', $msg);
                redirect('/login');
                return;
            }
        } else {
            $reason = $verifyResult['reason'] ?? 'Invalid OTP code.';
            if (isAjax()) {
                jsonResponse(['success' => false, 'message' => $reason]);
            }
            setFlash('danger', $reason);
            redirect('/verify-email');
            return;
        }
    }

    /**
     * Resend OTP with 60-second cooldown
     */
    public function resendOTP(): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            CsrfMiddleware::requireValidToken();
        }

        $userId = $_SESSION['verify_user_id'] ?? null;
        if (!$userId) {
            if (isAjax()) {
                jsonResponse(['success' => false, 'message' => 'Session expired. Please log in or register again.', 'redirect' => url('/login')]);
            }
            setFlash('danger', 'Session expired. Please log in or register again.');
            redirect('/login');
            return;
        }

        $cooldown = $this->userModel->canResendOTP($userId);
        if (!$cooldown['can_resend']) {
            $msg = "Please wait {$cooldown['seconds_remaining']} seconds before requesting a new OTP.";
            if (isAjax()) {
                jsonResponse(['success' => false, 'message' => $msg, 'cooldown' => $cooldown['seconds_remaining']]);
            }
            setFlash('warning', $msg);
            redirect('/verify-email');
            return;
        }

        $user = $this->userModel->findById($userId);
        if (!$user) {
            redirect('/login');
            return;
        }

        $otp = generateOTP();
        $this->userModel->setOTP($userId, $otp);

        // Determine recipient name
        $name = $user['email'];
        if ($user['role'] === 'student') {
            $student = $this->db->fetchOne("SELECT first_name, last_name FROM students WHERE user_id = ?", [$userId]);
            if ($student) $name = $student['first_name'] . ' ' . $student['last_name'];
        } elseif ($user['role'] === 'company') {
            $company = $this->db->fetchOne("SELECT company_name FROM companies WHERE user_id = ?", [$userId]);
            if ($company) $name = $company['company_name'];
        }

        $sent = Mailer::sendOTP($user['email'], $name, $otp);
        logActivity('resend_otp', 'auth', 'OTP resent to: ' . $user['email']);

        if ($sent) {
            $msg = 'A new 6-digit OTP verification code has been sent to <strong>' . htmlspecialchars($user['email']) . '</strong>.';
            setFlash('success', $msg);
        } else {
            $mailErr = Mailer::getLastError();
            $errDetail = !empty($mailErr) ? htmlspecialchars($mailErr) : 'Failed to connect to SMTP server.';
            $msg = 'Failed to deliver OTP email: <strong>' . $errDetail . '</strong>.';
            if (APP_ENV === 'development') {
                $msg .= ' (Dev Mode OTP: <strong>' . $otp . '</strong>)';
            }
            setFlash('warning', $msg);
        }

        if (isAjax()) {
            jsonResponse([
                'success' => $sent,
                'message' => $msg,
                'email_sent' => $sent,
                'cooldown' => 60,
                'expiry' => OTP_EXPIRY
            ]);
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
        if (!empty($user['theme_preference'])) {
            $allowedThemes = ['light', 'midnight'];
            $theme = in_array($user['theme_preference'], $allowedThemes, true) ? $user['theme_preference'] : 'light';
            $_SESSION['user_theme'] = $theme;
            setcookie('tpms_theme', $theme, time() + 31536000, '/', '', false, false);
        }
    }
}
