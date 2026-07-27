<?php
define('TPMS_RUNNING', true);
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/Mailer.php';

echo "Testing SMTP Connection and Mailer...\n";
echo "Host: " . SMTP_HOST . ":" . SMTP_PORT . "\n";
echo "Username: " . SMTP_USERNAME . "\n";
echo "From: " . SMTP_FROM_EMAIL . "\n";

$result = Mailer::sendApplicationStatus(
    'kishorpanchal402@gmail.com',
    'Rahul Sharma',
    'Senior Software Engineer',
    'TechCorp Solutions',
    'selected',
    [
        'interview_date' => '2026-08-01',
        'interview_time' => '10:00:00',
        'round' => 'Final Technical Round',
        'mode' => 'online',
        'meeting_link' => 'https://meet.google.com/abc-defg-hij'
    ]
);

if ($result) {
    echo "SUCCESS: Email delivered successfully!\n";
} else {
    echo "FAILURE: " . Mailer::getLastError() . "\n";
}
