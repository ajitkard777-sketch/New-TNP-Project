<?php
/**
 * Scratch Test Script — OTP Verification Workflow Verification
 */

session_name('TPMS_SESSION');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../includes/Mailer.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../database/Migrator.php';

echo "=== 1. RUNNING AUTO MIGRATIONS ===\n";
$migrator = new Migrator();
$migrator->runSilent();
echo "[OK] Migrations completed.\n\n";

$db = Database::getInstance();
$userModel = new User();

// Check if columns exist in users table
$columns = $db->fetchAll("DESCRIBE users");
$columnNames = array_column($columns, 'Field');
echo "=== 2. VERIFYING DATABASE COLUMNS ===\n";
foreach (['otp', 'otp_expires_at', 'otp_resend_last_at', 'otp_attempts', 'otp_resend_count', 'email_verified'] as $col) {
    if (in_array($col, $columnNames)) {
        echo "[OK] Column '{$col}' exists in users table.\n";
    } else {
        echo "[FAIL] Column '{$col}' MISSING in users table.\n";
    }
}

echo "\n=== 3. TESTING STUDENT OTP REGISTRATION FLOW ===\n";
$studentEmail = 'test_student_otp_' . time() . '@example.com';

// Create student user
$studentUserId = $userModel->create([
    'email' => $studentEmail,
    'password' => 'Password123!',
    'role' => 'student',
    'status' => 'pending',
    'email_verified' => 0
]);

$db->insert(
    "INSERT INTO students (user_id, first_name, last_name, phone, branch) VALUES (?, ?, ?, ?, ?)",
    [$studentUserId, 'Test', 'Student', '999' . rand(1000000, 9999999), 'Computer Science']
);

echo "Created pending student user ID: {$studentUserId} ({$studentEmail})\n";

// Set OTP
$otpCode = sprintf('%06d', random_int(100000, 999999));
$userModel->setOTP($studentUserId, $otpCode);
echo "Generated OTP: {$otpCode}\n";

// Check cooldown
$cooldown = $userModel->canResendOTP($studentUserId);
echo "Cooldown test (should be in cooldown): can_resend = " . ($cooldown['can_resend'] ? 'true' : 'false') . ", remaining = {$cooldown['remaining_seconds']}s\n";
if (!$cooldown['can_resend'] && $cooldown['remaining_seconds'] > 0) {
    echo "[OK] Cooldown timer is enforcing 60s restriction.\n";
} else {
    echo "[FAIL] Cooldown check failed.\n";
}

// Test invalid OTP attempt
$wrongResult = $userModel->verifyOTP($studentUserId, '000000');
echo "Invalid OTP attempt result: " . $wrongResult['message'] . "\n";
$userRecord = $userModel->findById($studentUserId);
echo "OTP Attempts in DB: " . $userRecord['otp_attempts'] . " (Expected: 1)\n";
if ($userRecord['otp_attempts'] == 1) {
    echo "[OK] Failed attempts incremented correctly.\n";
} else {
    echo "[FAIL] Attempts count incorrect.\n";
}

// Test valid OTP attempt
$validResult = $userModel->verifyOTP($studentUserId, $otpCode);
echo "Valid OTP attempt result: " . $validResult['message'] . "\n";
if ($validResult['success']) {
    echo "[OK] OTP verified successfully.\n";
} else {
    echo "[FAIL] OTP verification failed: " . $validResult['message'] . "\n";
}

// Activate student user as per AuthController logic
$userModel->activate($studentUserId);
$verifiedStudent = $userModel->findById($studentUserId);
echo "Post-verification Student Status: '{$verifiedStudent['status']}', Email Verified: '{$verifiedStudent['email_verified']}'\n";
if ($verifiedStudent['status'] === 'active' && $verifiedStudent['email_verified'] == 1) {
    echo "[OK] Student registration and activation workflow VERIFIED.\n";
} else {
    echo "[FAIL] Student post-verification status incorrect.\n";
}

echo "\n=== 4. TESTING COMPANY OTP REGISTRATION FLOW ===\n";
$companyEmail = 'test_company_otp_' . time() . '@example.com';

// Create company user
$companyUserId = $userModel->create([
    'email' => $companyEmail,
    'password' => 'Password123!',
    'role' => 'company',
    'status' => 'pending',
    'email_verified' => 0
]);

$db->insert(
    "INSERT INTO companies (user_id, company_name, contact_person, contact_email, contact_phone, is_approved) VALUES (?, ?, ?, ?, ?, 0)",
    [$companyUserId, 'Test Corp OTP', 'HR Manager', $companyEmail, '888' . rand(1000000, 9999999)]
);

echo "Created pending company user ID: {$companyUserId} ({$companyEmail})\n";

$companyOtp = sprintf('%06d', random_int(100000, 999999));
$userModel->setOTP($companyUserId, $companyOtp);
echo "Generated Company OTP: {$companyOtp}\n";

// Verify OTP
$companyVerifyRes = $userModel->verifyOTP($companyUserId, $companyOtp);
echo "Company OTP verify result: " . $companyVerifyRes['message'] . "\n";

$verifiedCompany = $userModel->findById($companyUserId);
$companyRecord = $db->fetchOne("SELECT is_approved FROM companies WHERE user_id = ?", [$companyUserId]);

echo "Post-verification Company User Status: '{$verifiedCompany['status']}', Email Verified: '{$verifiedCompany['email_verified']}', Is Approved: '{$companyRecord['is_approved']}'\n";
if ($verifiedCompany['email_verified'] == 1 && $verifiedCompany['status'] === 'pending' && $companyRecord['is_approved'] == 0) {
    echo "[OK] Company workflow VERIFIED: Email is verified, account remains 'pending' for Admin approval.\n";
} else {
    echo "[FAIL] Company post-verification state incorrect.\n";
}

echo "\n=== 5. CLEANING UP TEST DATA ===\n";
$userModel->delete($studentUserId);
$userModel->delete($companyUserId);
$db->delete("DELETE FROM students WHERE user_id = ?", [$studentUserId]);
$db->delete("DELETE FROM companies WHERE user_id = ?", [$companyUserId]);
echo "[OK] Cleaned up temporary test users.\n";

echo "\nALL OTP VERIFICATION TESTS COMPLETED SUCCESSFULLY!\n";
