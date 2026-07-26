<?php
/**
 * Edge Case Tests — Expiry, Max Attempts, Resend Logic
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

$db = Database::getInstance();
$userModel = new User();

echo "=== 1. TESTING MAX ATTEMPTS (5 ATTEMPTS LIMIT) ===\n";
$userId = $userModel->create([
    'email' => 'edge_case_user_' . time() . '@example.com',
    'password' => 'Password123!',
    'role' => 'student',
    'status' => 'pending',
    'email_verified' => 0
]);

$otp = '123456';
$userModel->setOTP($userId, $otp);

for ($i = 1; $i <= 5; $i++) {
    $res = $userModel->verifyOTP($userId, '999999'); // wrong OTP
    echo "Attempt {$i}: {$res['message']}\n";
}

// 6th attempt should be blocked due to max attempts exceeded
$res6 = $userModel->verifyOTP($userId, '123456'); // even with correct OTP
echo "6th Attempt (with correct OTP): {$res6['message']}\n";
if (isset($res6['exceeded']) && $res6['exceeded'] === true) {
    echo "[OK] Max attempts limit (5) strictly enforced!\n";
} else {
    echo "[FAIL] Max attempts check failed.\n";
}

echo "\n=== 2. TESTING EXPIRED OTP ===\n";
$userModel->setOTP($userId, '654321');
// Force expiry in database using PHP formatted date
$pastDate = date('Y-m-d H:i:s', time() - 60);
$db->update("UPDATE users SET otp_expires_at = ? WHERE id = ?", [$pastDate, $userId]);

$expiredRes = $userModel->verifyOTP($userId, '654321');
echo "Expired OTP result: {$expiredRes['message']}\n";
if (isset($expiredRes['expired']) && $expiredRes['expired'] === true) {
    echo "[OK] Expired OTP check strictly enforced!\n";
} else {
    echo "[FAIL] Expired OTP check failed.\n";
}

// Clean up
$userModel->delete($userId);
echo "\n[OK] Edge case tests complete!\n";
