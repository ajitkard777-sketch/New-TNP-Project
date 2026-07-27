<?php
define('TPMS_RUNNING', true);
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

try {
    $pdo = Database::getInstance()->getConnection();
    echo "=== PASSWORD_RESETS TABLE SCHEMA ===\n";
    $stmt = $pdo->query("DESCRIBE password_resets");
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $col) {
        echo "{$col['Field']} | {$col['Type']} | Null: {$col['Null']} | Key: {$col['Key']} | Default: {$col['Default']}\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
