<?php
define('TPMS_RUNNING', true);
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/Mailer.php';

echo "====================================================\n";
echo "TPMS EMAIL SYSTEM WORKFLOW & DELIVERY AUDIT\n";
echo "====================================================\n\n";

$targetEmail = 'kishorpanchal402@gmail.com';
$targetName = 'Test User';

$workflows = [
    'Student Registration OTP' => function() use ($targetEmail, $targetName) {
        return Mailer::sendOTP($targetEmail, $targetName, '123456', 10);
    },
    'Company Registration OTP' => function() use ($targetEmail) {
        return Mailer::sendOTP($targetEmail, 'Acme Corp', '654321', 10);
    },
    'Forgot Password' => function() use ($targetEmail, $targetName) {
        return Mailer::sendPasswordReset($targetEmail, $targetName, FULL_URL . '/reset-password?token=test_token_123');
    },
    'Job Application Confirmation' => function() use ($targetEmail, $targetName) {
        return Mailer::sendJobApplication($targetEmail, $targetName, 'Software Engineer', 'TechCorp Solutions');
    },
    'Candidate Selection' => function() use ($targetEmail, $targetName) {
        return Mailer::sendApplicationStatus($targetEmail, $targetName, 'Software Engineer', 'TechCorp Solutions', 'selected');
    },
    'Interview Schedule' => function() use ($targetEmail, $targetName) {
        return Mailer::sendInterviewScheduled($targetEmail, $targetName, 'Software Engineer', 'TechCorp Solutions', date('Y-m-d', strtotime('+2 days')), '10:30:00', 'online', 'https://meet.google.com/test');
    },
    'Interview Results' => function() use ($targetEmail, $targetName) {
        return Mailer::sendInterviewResult($targetEmail, $targetName, 'Software Engineer', 'TechCorp Solutions', 'passed');
    },
    'Training Registration' => function() use ($targetEmail, $targetName) {
        return Mailer::sendTrainingRegistered($targetEmail, $targetName, 'Full Stack Web Development', date('Y-m-d', strtotime('+5 days')));
    },
    'Admin Announcement' => function() use ($targetEmail, $targetName) {
        return Mailer::sendAdminAnnouncement($targetEmail, $targetName, 'Campus Placement Drive 2026', 'All final year students are requested to update their resumes.');
    },
    'Company Approval Notification' => function() use ($targetEmail) {
        return Mailer::sendApprovalNotification($targetEmail, 'Acme Corp', 'Company Registration', 'Acme Corp', true);
    }
];

$passed = 0;
$failed = 0;

foreach ($workflows as $name => $callback) {
    echo "Testing: [{$name}] ... ";
    try {
        $result = $callback();
        if ($result) {
            echo "PASS ✓\n";
            $passed++;
        } else {
            echo "FAIL ❌ (Error: " . Mailer::getLastError() . ")\n";
            $failed++;
        }
    } catch (Throwable $e) {
        echo "EXCEPTION ❌ (" . $e->getMessage() . ")\n";
        $failed++;
    }
}

echo "\n====================================================\n";
echo "SUMMARY: {$passed} Passed, {$failed} Failed out of " . count($workflows) . " Workflows\n";
echo "====================================================\n";
