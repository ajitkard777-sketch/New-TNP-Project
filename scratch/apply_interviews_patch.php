<?php
define('TPMS_RUNNING', true);
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();

    $sql = file_get_contents(__DIR__ . '/../database/patch_interviews.sql');
    $pdo->exec($sql);

    echo "Interviews patch applied successfully!\n";
    $stmt1 = $pdo->query("SHOW TABLES LIKE 'interviews'");
    $stmt2 = $pdo->query("SHOW TABLES LIKE 'interview_feedback'");

    echo "interviews: " . ($stmt1->fetch() ? "EXISTS" : "MISSING") . "\n";
    echo "interview_feedback: " . ($stmt2->fetch() ? "EXISTS" : "MISSING") . "\n";
} catch (Exception $e) {
    echo "Patch Error: " . $e->getMessage() . "\n";
}
