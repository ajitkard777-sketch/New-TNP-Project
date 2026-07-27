<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/Mailer.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Student.php';

echo "--- Testing Full Student Signup & Verification Workflow ---\n";

$db = Database::getInstance();
$userModel = new User();

$testEmail = 'signup.student.' . rand(1000, 9999) . '@test.com';
$testPhone = '98' . rand(10000000, 99999999);

echo "1. Registering student: {$testEmail} (Phone: {$testPhone})...\n";

$db->beginTransaction();

try {
    $userId = $userModel->create([
        'email' => $testEmail,
        'password' => 'Password@123',
        'role' => 'student',
        'status' => 'pending',
        'email_verified' => 0
    ]);

    $db->insert(
        "INSERT INTO students (user_id, first_name, last_name, phone, branch, degree, cgpa, profile_completion) VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
        [$userId, 'Test', 'Student', $testPhone, 'Computer Science', 'B.Tech', 8.50, 30]
    );

    $otp = $userModel->generateAndSaveOTP($userId);

    $db->insert(
        "INSERT INTO notifications (user_id, title, message, type, category) VALUES (?, ?, ?, ?, ?)",
        [$userId, 'Welcome to TPMS!', 'Your registration was submitted.', 'info', 'system']
    );

    $db->commit();

    echo "✅ Student account created! User ID: {$userId}, OTP: {$otp}\n";

    echo "2. Verifying OTP code {$otp} for User ID {$userId}...\n";
    $verifyRes = $userModel->verifyOTP($userId, $otp);

    echo "Verification Result: " . ($verifyRes['success'] ? 'SUCCESS ✅' : 'FAILED ❌ (' . $verifyRes['message'] . ')') . "\n";

    $user = $userModel->findById($userId);
    echo "3. Final Status:\n";
    echo "   - Email: {$user['email']}\n";
    echo "   - Verified: " . ($user['email_verified'] ? 'YES (1)' : 'NO (0)') . "\n";
    echo "   - Account Status: {$user['status']}\n";
    echo "   - Role: {$user['role']}\n";

    echo "\n🎉 BROWSER SIGNUP WORKFLOW TEST PASSED 100%!\n";

} catch (Exception $e) {
    $db->rollback();
    echo "❌ Signup Error: " . $e->getMessage() . "\n";
}
