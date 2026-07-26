<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
$db = Database::getInstance();
$rows = $db->fetchAll("SELECT id, title, category, company_name, is_read, link FROM notifications LIMIT 10");
echo "Notifications count: " . count($rows) . "\n";
foreach ($rows as $r) {
    echo "ID: {$r['id']} | Category: {$r['category']} | Title: {$r['title']} | Company: {$r['company_name']} | Link: {$r['link']}\n";
}
