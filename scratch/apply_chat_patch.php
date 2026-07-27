<?php
define('TPMS_RUNNING', true);
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();

    $sql = file_get_contents(__DIR__ . '/../database/patch_chat_history.sql');
    $pdo->exec($sql);

    echo "Chat history patch applied successfully!\n";
    $stmt = $pdo->query("SHOW TABLES LIKE 'chat_history'");
    echo "chat_history table: " . ($stmt->fetch() ? "EXISTS" : "MISSING") . "\n";
} catch (Exception $e) {
    echo "Patch Error: " . $e->getMessage() . "\n";
}
