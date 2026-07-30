<?php
define('APP_ENV', 'development');
require_once __DIR__ . '/../config/database.php';

$db = Database::getInstance();
$universities = $db->fetchAll("SELECT id, name FROM universities");

echo "Current Universities:\n";
foreach ($universities as $u) {
    echo "- ID: {$u['id']} | Name: {$u['name']}\n";
}

// Delete universities matching nxasijxncdsc or non-standard test inputs
$stmt = $db->query("DELETE FROM universities WHERE name LIKE '%nxasijxncdsc%' OR name LIKE '%nxas%'");
$count = $stmt->rowCount();

echo "\nDeleted {$count} matching university records.\n";

$remaining = $db->fetchAll("SELECT id, name FROM universities");
echo "\nRemaining Universities:\n";
foreach ($remaining as $u) {
    echo "- ID: {$u['id']} | Name: {$u['name']}\n";
}
