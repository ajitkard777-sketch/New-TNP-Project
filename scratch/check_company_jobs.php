<?php
define('TPMS_RUNNING', true);
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

$db = Database::getInstance();

echo "=== CHECKING JOBS AND APPLICATIONS ===\n\n";

$jobs = $db->fetchAll("SELECT j.*, c.company_name, (SELECT COUNT(*) FROM applications a WHERE a.job_id = j.id) as app_count FROM jobs j JOIN companies c ON j.company_id = c.id");
echo "Total Jobs in DB: " . count($jobs) . "\n";

foreach ($jobs as $j) {
    echo "Job ID: {$j['id']} | Title: {$j['title']} | Company: {$j['company_name']} | Applications: {$j['app_count']}\n";
}

$apps = $db->fetchAll("SELECT a.*, s.first_name, s.last_name, j.title as job_title FROM applications a JOIN students s ON a.student_id = s.id JOIN jobs j ON a.job_id = j.id");
echo "\nTotal Applications in DB: " . count($apps) . "\n";
foreach ($apps as $ap) {
    echo "App ID: {$ap['id']} | Student: {$ap['first_name']} {$ap['last_name']} | Job: {$ap['job_title']} (Job ID: {$ap['job_id']}) | Status: {$ap['status']}\n";
}
