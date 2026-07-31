<?php
define('APP_ENV', 'development');
require_once __DIR__ . '/../config/database.php';

$db = Database::getInstance();
$stmt = $db->query("DELETE FROM higher_studies_applications WHERE university_id = 5");
echo "Deleted " . $stmt->rowCount() . " orphan applications linked to university 5.\n";
