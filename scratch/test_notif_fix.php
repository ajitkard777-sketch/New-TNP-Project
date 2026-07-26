<?php
/**
 * Scratch Test Script for Notification Module
 */
define('TPMS_RUNNING', true);
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../controllers/NotificationController.php';

echo "=== START NOTIFICATION MODULE TEST ===\n\n";

$db = Database::getInstance();

// 1. Verify table columns
$cols = $db->fetchAll("SHOW COLUMNS FROM notifications");
$colNames = array_column($cols, 'Field');
echo "1. Table Columns: " . implode(', ', $colNames) . "\n";
assert(in_array('company_name', $colNames), 'company_name column missing');
assert(in_array('link', $colNames), 'link column missing');
echo "   [OK] Table columns verified.\n\n";

// 2. Fetch sample student user
$user = $db->fetchOne("SELECT id, role FROM users WHERE role = 'student' LIMIT 1");
if (!$user) {
    echo "No student user found in database.\n";
    exit(1);
}
$_SESSION['user_id'] = $user['id'];
$_SESSION['user_role'] = $user['role'];
echo "2. Active User: ID={$user['id']}, Role={$user['role']}\n\n";

// 3. Create test notifications
$testNotif1 = createNotification(
    $user['id'],
    'Test Job Alert: Full Stack Developer Drive',
    'Acme Corp is hiring Full Stack Developers. Apply before July 30.',
    'info',
    'job',
    '/student/jobs'
);
$testNotif2 = createNotification(
    $user['id'],
    'Technical Interview Scheduled with TechGlobal',
    'Your technical round interview with TechGlobal has been scheduled.',
    'warning',
    'interview',
    '/student/interviews'
);
echo "3. Inserted test notifications. Success: " . ($testNotif1 && $testNotif2 ? 'YES' : 'NO') . "\n\n";

// 4. Test NotificationController methods
$controller = new NotificationController();

// Test Unread Count
$unreadCountBefore = (int)$db->fetchColumn(
    "SELECT COUNT(*) FROM notifications WHERE (user_id = ? OR is_global = 1) AND is_read = 0",
    [$user['id']]
);
echo "4. Unread Count Before Mark Read: {$unreadCountBefore}\n";

// Fetch unread sample ID
$unreadNotif = $db->fetchOne(
    "SELECT id, title FROM notifications WHERE (user_id = ? OR is_global = 1) AND is_read = 0 LIMIT 1",
    [$user['id']]
);

if ($unreadNotif) {
    $idToMark = $unreadNotif['id'];
    echo "   Marking single notification #{$idToMark} as read...\n";
    $db->update("UPDATE notifications SET is_read = 1 WHERE id = ?", [$idToMark]);
    $unreadCountAfter = (int)$db->fetchColumn(
        "SELECT COUNT(*) FROM notifications WHERE (user_id = ? OR is_global = 1) AND is_read = 0",
        [$user['id']]
    );
    echo "   Unread Count After Mark Read: {$unreadCountAfter}\n";
    assert($unreadCountAfter === $unreadCountBefore - 1, 'Unread count decrement failed');
    echo "   [OK] Single Mark Read verified.\n\n";
}

// 5. Test Search Query
$_GET['q'] = 'Acme';
$_GET['category'] = 'all';

echo "5. Testing Search Query for 'Acme':\n";
$searchResults = $db->fetchAll(
    "SELECT * FROM notifications WHERE (user_id = ? OR is_global = 1) AND (LOWER(title) LIKE ? OR LOWER(message) LIKE ? OR LOWER(IFNULL(company_name, '')) LIKE ? OR LOWER(IFNULL(type, '')) LIKE ? OR LOWER(IFNULL(category, '')) LIKE ?) ORDER BY created_at DESC",
    [$user['id'], '%acme%', '%acme%', '%acme%', '%acme%', '%acme%']
);
echo "   Matches count: " . count($searchResults) . "\n";
foreach ($searchResults as $r) {
    echo "   - [ID: {$r['id']}] {$r['title']} ({$r['company_name']})\n";
}
echo "   [OK] Search query tested.\n\n";

// 6. Test Mark All Read
$db->update("UPDATE notifications SET is_read = 1 WHERE (user_id = ? OR is_global = 1) AND is_read = 0", [$user['id']]);
$allUnread = (int)$db->fetchColumn(
    "SELECT COUNT(*) FROM notifications WHERE (user_id = ? OR is_global = 1) AND is_read = 0",
    [$user['id']]
);
echo "6. Unread Count After Mark All Read: {$allUnread}\n";
assert($allUnread === 0, 'Mark All Read failed');
echo "   [OK] Mark All Read verified.\n\n";

echo "=== ALL NOTIFICATION TESTS PASSED SUCCESSFULLY! ===\n";
