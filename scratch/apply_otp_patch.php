<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

$db = Database::getInstance()->getConnection();

$queries = [
    "ALTER TABLE `users` MODIFY COLUMN `otp` VARCHAR(255) NULL",
    "ALTER TABLE `users` ADD COLUMN IF NOT EXISTS `otp_resend_count` INT NOT NULL DEFAULT 0",
    "ALTER TABLE `users` ADD COLUMN IF NOT EXISTS `otp_last_sent_at` DATETIME NULL",
    "ALTER TABLE `users` ADD COLUMN IF NOT EXISTS `otp_attempts` INT NOT NULL DEFAULT 0"
];

foreach ($queries as $q) {
    try {
        $db->exec($q);
        echo "EXECUTED: {$q}\n";
    } catch (Exception $e) {
        echo "INFO: " . $e->getMessage() . "\n";
    }
}
echo "OTP Columns Patch Applied Successfully!\n";
