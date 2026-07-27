<?php
define('TPMS_RUNNING', true);
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();

    $sql = file_get_contents(__DIR__ . '/../database/patch_admin_chat.sql');
    $pdo->exec($sql);

    echo "Admin chat & shortlist patch applied successfully!\n";
    $stmt1 = $pdo->query("SHOW TABLES LIKE 'admin_chat_history'");
    $stmt2 = $pdo->query("SHOW TABLES LIKE 'student_shortlists'");

    echo "admin_chat_history: " . ($stmt1->fetch() ? "EXISTS" : "MISSING") . "\n";
    echo "student_shortlists: " . ($stmt2->fetch() ? "EXISTS" : "MISSING") . "\n";
} catch (Exception $e) {
    echo "Patch Error: " . $e->getMessage() . "\n";
}
