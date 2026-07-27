<?php
define('TPMS_RUNNING', true);
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/Mailer.php';
require_once __DIR__ . '/../models/Job.php';
require_once __DIR__ . '/../models/Company.php';

echo "====================================================\n";
echo "TPMS END-TO-END CANDIDATE SELECTION WORKFLOW TEST\n";
echo "====================================================\n\n";

$db = Database::getInstance();

// 1. Get or create a sample company
$company = $db->fetchOne("SELECT c.*, u.email as user_email FROM companies c JOIN users u ON c.user_id = u.id WHERE c.is_approved = 1 LIMIT 1");
if (!$company) {
    die("ERROR: No approved company found in database.\n");
}
echo "✓ 1. Company Identified: {$company['company_name']} ({$company['user_email']})\n";

// 2. Get or create a sample job
$job = $db->fetchOne("SELECT * FROM jobs WHERE company_id = ? AND status = 'active' LIMIT 1", [$company['id']]);
if (!$job) {
    $db->insert(
        "INSERT INTO jobs (company_id, title, description, requirements, job_type, location, salary_min, salary_max, status) VALUES (?, 'Senior Software Engineer', 'Full stack role', 'Java, React', 'full_time', 'Bangalore', 12.00, 18.00, 'active')",
        [$company['id']]
    );
    $job = $db->fetchOne("SELECT * FROM jobs WHERE company_id = ? ORDER BY id DESC LIMIT 1", [$company['id']]);
}
echo "✓ 2. Job Posting Identified: {$job['title']} (ID: {$job['id']})\n";

// 3. Get student & user
$student = $db->fetchOne("SELECT s.*, u.email FROM students s JOIN users u ON s.user_id = u.id LIMIT 1");
if (!$student) {
    die("ERROR: No student found in database.\n");
}
echo "✓ 3. Selected Student Identified: {$student['first_name']} {$student['last_name']} ({$student['email']})\n";

// 4. Create or fetch application
$app = $db->fetchOne("SELECT * FROM applications WHERE student_id = ? AND job_id = ?", [$student['id'], $job['id']]);
if (!$app) {
    $db->insert("INSERT INTO applications (student_id, job_id, status) VALUES (?, ?, 'applied')", [$student['id'], $job['id']]);
    $app = $db->fetchOne("SELECT * FROM applications WHERE student_id = ? AND job_id = ?", [$student['id'], $job['id']]);
}
echo "✓ 4. Application Record Created/Fetched (App ID: {$app['id']}, Initial Status: {$app['status']})\n";

// 5. Simulate Candidate Selection Workflow
echo "\n--- Executing Candidate Selection & Workflow Triggers ---\n";

// Update status in DB
$jobModel = new Job();
$jobModel->updateApplicationStatus($app['id'], 'selected');

// Update student placement
$db->update("UPDATE students SET is_placed = 1, placed_company = ?, placed_package = ?, placed_date = CURDATE() WHERE id = ?",
    [$company['company_name'], $job['salary_max'] ?? $job['salary_min'], $student['id']]);

// Create In-App Notification
$notifTitle = "Selected for Position! 🎉";
$notifMsg   = "Congratulations! You have been selected for {$job['title']} at {$company['company_name']}.";
$db->insert(
    "INSERT INTO notifications (user_id, title, message, type, category, link) VALUES (?, ?, ?, 'success', 'job', ?)",
    [$student['user_id'], $notifTitle, $notifMsg, '/team1/student/applications']
);
echo "✓ 5a. Database Updated: Application set to 'selected', Student marked placed.\n";
echo "✓ 5b. In-App Notification Created in 'notifications' table (User ID: {$student['user_id']}).\n";

// 6. Fetch interview details (if any)
$interviewDetails = [
    'round' => 'Final Technical Round',
    'interview_date' => date('Y-m-d', strtotime('+3 days')),
    'interview_time' => '11:00:00',
    'mode' => 'online',
    'meeting_link' => 'https://meet.google.com/tpms-selection-interview'
];

// 7. Send Selection Email via Mailer (PHPMailer SMTP)
echo "✓ 5c. Initiating SMTP Email Delivery via PHPMailer...\n";
$emailSent = Mailer::sendApplicationStatus(
    $student['email'],
    $student['first_name'] . ' ' . $student['last_name'],
    $job['title'],
    $company['company_name'],
    'selected',
    $interviewDetails
);

// 8. Verification Results
echo "\n====================================================\n";
echo "VERIFICATION & AUDIT SUMMARY\n";
echo "====================================================\n";

$updatedApp = $db->fetchOne("SELECT status FROM applications WHERE id = ?", [$app['id']]);
echo "[DB Check] Application Status: " . ($updatedApp['status'] === 'selected' ? "PASS ('selected')" : "FAIL") . "\n";

$latestNotif = $db->fetchOne("SELECT * FROM notifications WHERE user_id = ? ORDER BY id DESC LIMIT 1", [$student['user_id']]);
echo "[DB Check] Notification Saved: " . ($latestNotif ? "PASS ('{$latestNotif['title']}')" : "FAIL") . "\n";
echo "[DB Check] Notification Link: " . ($latestNotif['link'] ? "PASS ('{$latestNotif['link']}')" : "FAIL") . "\n";

if ($emailSent) {
    echo "[SMTP Check] Real Email Delivery: PASS (Delivered to {$student['email']})\n";
} else {
    echo "[SMTP Check] Real Email Delivery: FAIL (Error: " . Mailer::getLastError() . ")\n";
}

echo "\nLog file 'logs/email.log' last entries:\n";
$logFile = ROOT_PATH . '/logs/email.log';
if (file_exists($logFile)) {
    $lines = file($logFile);
    $lastLines = array_slice($lines, -4);
    echo implode("", $lastLines);
} else {
    echo "No log file found.\n";
}
echo "====================================================\n";
