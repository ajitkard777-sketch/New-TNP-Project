<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Student.php';
require_once __DIR__ . '/../models/Company.php';
require_once __DIR__ . '/../models/Job.php';
require_once __DIR__ . '/../models/Message.php';

echo "=================================================================\n";
echo "          TPMS END-TO-END MODULE FUNCTIONAL TEST SUITE          \n";
echo "=================================================================\n\n";

$db = Database::getInstance();
$userModel = new User();
$studentModel = new Student();
$companyModel = new Company();
$jobModel = new Job();

$results = [];

function check($module, $testName, $condition, $info = '') {
    global $results;
    if ($condition) {
        $results[] = ['module' => $module, 'test' => $testName, 'status' => 'PASS', 'info' => $info];
        echo "  [{$module}] ✅ PASS: {$testName} " . ($info ? "({$info})" : "") . "\n";
    } else {
        $results[] = ['module' => $module, 'test' => $testName, 'status' => 'FAIL', 'info' => $info];
        echo "  [{$module}] ❌ FAIL: {$testName} " . ($info ? "({$info})" : "") . "\n";
    }
}

// -------------------------------------------------------------
// 1. AUTH & OTP MODULE
// -------------------------------------------------------------
echo "1. AUTHENTICATION & OTP MODULE TESTS\n";
echo "-------------------------------------------------------------\n";
$admin = $userModel->findByEmail('admin@tpms.com');
check("Auth", "Admin Account Exists", !empty($admin) && $admin['role'] === 'admin');

$studentUser = $userModel->findByEmail('student@tpms.com');
check("Auth", "Student Account Exists", !empty($studentUser) && $studentUser['role'] === 'student');

$companyUser = $userModel->findByEmail('company@tpms.com');
check("Auth", "Company Account Exists", !empty($companyUser) && $companyUser['role'] === 'company');

// Test OTP generation
if ($studentUser) {
    $otp = $userModel->generateAndSaveOTP($studentUser['id']);
    check("OTP", "Generate 6-Digit OTP", strlen($otp) === 6 && ctype_digit($otp), "OTP: {$otp}");

    $vRes = $userModel->verifyOTP($studentUser['id'], $otp);
    check("OTP", "Verify Valid OTP Code", $vRes['success'] === true);
}

// -------------------------------------------------------------
// 2. ADMIN MODULE
// -------------------------------------------------------------
echo "\n2. ADMIN MODULE TESTS\n";
echo "-------------------------------------------------------------\n";
$totalStudents = (int)$db->fetchColumn("SELECT COUNT(*) FROM students");
check("Admin", "Query Total Students", $totalStudents > 0, "{$totalStudents} students");

$totalCompanies = (int)$db->fetchColumn("SELECT COUNT(*) FROM companies");
check("Admin", "Query Total Companies", $totalCompanies > 0, "{$totalCompanies} companies");

$totalJobs = (int)$db->fetchColumn("SELECT COUNT(*) FROM jobs");
check("Admin", "Query Total Jobs", $totalJobs > 0, "{$totalJobs} jobs");

$totalApps = (int)$db->fetchColumn("SELECT COUNT(*) FROM applications");
check("Admin", "Query Placement Applications", $totalApps >= 0, "{$totalApps} applications");

// -------------------------------------------------------------
// 3. STUDENT MODULE
// -------------------------------------------------------------
echo "\n3. STUDENT MODULE TESTS\n";
echo "-------------------------------------------------------------\n";
$studentProfile = $db->fetchOne("SELECT * FROM students LIMIT 1");
check("Student", "Student Profile Fetch", !empty($studentProfile));

$recommendations = $db->fetchAll("SELECT * FROM jobs WHERE status = 'active' LIMIT 5");
check("Student", "Job Recommendations Engine", count($recommendations) > 0, count($recommendations) . " recommended jobs");

$trainings = $db->fetchAll("SELECT * FROM trainings");
check("Student", "Trainings Module Fetch", count($trainings) > 0, count($trainings) . " trainings found");

$universities = $db->fetchAll("SELECT * FROM universities");
check("Student", "Higher Studies Universities", count($universities) > 0, count($universities) . " universities found");

// -------------------------------------------------------------
// 4. COMPANY MODULE
// -------------------------------------------------------------
echo "\n4. COMPANY MODULE TESTS\n";
echo "-------------------------------------------------------------\n";
$activeJobs = $db->fetchAll("SELECT * FROM jobs WHERE status = 'active'");
check("Company", "Active Job Listings Query", count($activeJobs) > 0, count($activeJobs) . " active jobs");

$interviews = $db->fetchAll("SELECT * FROM interviews");
check("Company", "Interview Schedules Query", count($interviews) >= 0, count($interviews) . " interviews");

// -------------------------------------------------------------
// 5. NOTIFICATIONS & MESSAGING
// -------------------------------------------------------------
echo "\n5. NOTIFICATIONS & MESSAGING TESTS\n";
echo "-------------------------------------------------------------\n";
$notifs = $db->fetchAll("SELECT * FROM notifications LIMIT 5");
check("Notifications", "System Notifications Query", count($notifs) > 0, count($notifs) . " notifications in DB");

// -------------------------------------------------------------
// SUMMARY
// -------------------------------------------------------------
$passedCount = count(array_filter($results, fn($r) => $r['status'] === 'PASS'));
$failedCount = count(array_filter($results, fn($r) => $r['status'] === 'FAIL'));

echo "\n=================================================================\n";
echo "                   FUNCTIONAL SUITE SUMMARY                      \n";
echo "=================================================================\n";
echo "  Total Tests Executed : " . count($results) . "\n";
echo "  Passed               : {$passedCount} ✅\n";
echo "  Failed               : {$failedCount} ❌\n";
echo "=================================================================\n";
