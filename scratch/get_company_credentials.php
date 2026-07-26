<?php
define('TPMS_RUNNING', true);
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

$db = Database::getInstance();
$companies = $db->fetchAll("SELECT u.id as user_id, u.email, c.id as company_id, c.company_name, c.is_approved FROM users u JOIN companies c ON u.id = c.user_id WHERE u.role = 'company'");

echo "=== COMPANY ACCOUNTS IN SYSTEM ===\n\n";
foreach ($companies as $c) {
    echo "Company Name : {$c['company_name']}\n";
    echo "Company ID   : {$c['company_id']}\n";
    echo "User ID      : {$c['user_id']}\n";
    echo "Email / ID   : {$c['email']}\n";
    echo "Password     : Company@123\n";
    echo "Approved     : " . ($c['is_approved'] ? 'YES' : 'NO') . "\n";
    echo "-----------------------------------\n";
}
