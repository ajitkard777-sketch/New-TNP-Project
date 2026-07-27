<?php
define('TPMS_RUNNING', true);
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/Mailer.php';

echo "Testing new Mailer methods...\n\n";

// Test sendCompanyPendingApproval
$r1 = Mailer::sendCompanyPendingApproval('ajitkard199@gmail.com', 'PALANTIR Technologies');
echo "sendCompanyPendingApproval to ajitkard199@gmail.com: " . ($r1 ? "PASS ✓" : "FAIL ❌ " . Mailer::getLastError()) . "\n";

// Test sendCompanyRegistrationAlert (admin notification)
$r2 = Mailer::sendCompanyRegistrationAlert('kishorpanchal402@gmail.com', 'PALANTIR Technologies', 'ajitkard199@gmail.com');
echo "sendCompanyRegistrationAlert to admin: " . ($r2 ? "PASS ✓" : "FAIL ❌ " . Mailer::getLastError()) . "\n";

echo "\nDone.\n";
