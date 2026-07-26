<?php
/**
 * Scratch Test Script for Job Applications & Manage Jobs Chatbox Integration
 */
define('TPMS_RUNNING', true);
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../models/Job.php';
require_once __DIR__ . '/../models/Message.php';

echo "=== START JOB APPLICATIONS & CHATBOX TEST ===\n\n";

$db = Database::getInstance();
$jobModel = new Job();
$messageModel = new Message();

// 1. Fetch applications for microsofrt (Job ID 6)
$jobId = 6;
$applications = $jobModel->getApplications($jobId);
echo "1. Applications fetched for Job ID #{$jobId} (microsofrt): " . count($applications) . " applicants\n";
assert(count($applications) > 0, "Applications for microsofrt job should be seeded and present");

foreach ($applications as $a) {
    echo "   - Applicant: {$a['first_name']} {$a['last_name']} | Email: {$a['email']} | Student UserID: {$a['student_user_id']} | Status: {$a['status']}\n";
    assert(!empty($a['student_user_id']), "student_user_id must be populated");
}

echo "\n[OK] Job applications query verified!\n\n";

// 2. Test Message creation between Company User and Student Applicant
$companyUser = $db->fetchOne("SELECT u.id FROM users u JOIN companies c ON u.id = c.user_id LIMIT 1");
$studentUser = $db->fetchOne("SELECT u.id FROM users u JOIN students s ON u.id = s.user_id LIMIT 1");

if ($companyUser && $studentUser) {
    echo "2. Testing Chat Messaging between Company User #{$companyUser['id']} and Student User #{$studentUser['id']}...\n";
    
    $msgId = $messageModel->sendMessage($companyUser['id'], $studentUser['id'], 'Hello! We reviewed your job application.');
    echo "   Sent Message ID: {$msgId}\n";
    assert($msgId > 0, "Message should be saved to database");

    $history = $messageModel->getMessages($companyUser['id'], $studentUser['id']);
    echo "   Conversation History Messages Count: " . count($history) . "\n";
    assert(count($history) > 0, "Conversation history should return sent messages");
}

echo "\n=== ALL JOB APPLICATIONS & CHATBOX TESTS PASSED! ===\n";
