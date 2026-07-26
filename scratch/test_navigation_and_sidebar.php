<?php
/**
 * Scratch Test Script for Notification Navigation & Sidebar Key Resolution
 */
define('TPMS_RUNNING', true);
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../middleware/CsrfMiddleware.php';

echo "=== START NAVIGATION & SIDEBAR TEST ===\n\n";

$_SESSION['user_id'] = 1;
$_SESSION['user_role'] = 'student';

// Require header function definition
require_once __DIR__ . '/../includes/header.php';

// Test resolveCurrentPageKey for various routes and roles
$tests = [
    ['student', ['student', 'dashboard'], 'dashboard'],
    ['student', ['student', 'notifications'], 'notifications'],
    ['student', ['student', 'jobs'], 'jobs'],
    ['student', ['student', 'applications'], 'applications'],
    ['student', ['student', 'trainings'], 'trainings'],
    ['student', ['student', 'interviews'], 'interviews'],
    ['student', ['student', 'calendar'], 'calendar'],
    ['student', ['student', 'resume-builder'], 'resume-builder'],
    ['student', ['student', 'achievements'], 'achievements'],
    ['student', ['student', 'messages'], 'messages'],
    ['student', ['student', 'change-password'], 'change-password'],
    ['admin', ['admin', 'notifications'], 'notifications'],
    ['company', ['company', 'notifications'], 'notifications']
];

foreach ($tests as $t) {
    [$role, $urlParts, $expectedKey] = $t;
    $resolved = resolveCurrentPageKey($role, $urlParts);
    echo "Role: {$role}, URL: " . implode('/', $urlParts) . " => Key: {$resolved} (Expected: {$expectedKey})\n";
    assert($resolved === $expectedKey, "Key mismatch for " . implode('/', $urlParts));
}

echo "\n[OK] All route keys resolved successfully!\n\n";

// Verify sidebar configuration loads cleanly for all roles
foreach (['student', 'company', 'admin'] as $currentRole) {
    $userAvatar = '';
    $userName = 'Test User';
    $currentPage = 'notifications';
    
    ob_start();
    require __DIR__ . '/../includes/sidebar.php';
    $html = ob_get_clean();
    
    echo "Sidebar rendering for role '{$currentRole}': " . strlen($html) . " bytes\n";
    assert(str_contains($html, 'Notifications'), "Sidebar for {$currentRole} must contain Notifications menu");
    assert(str_contains($html, 'active'), "Sidebar for {$currentRole} must highlight active item");
}

echo "\n=== ALL NAVIGATION & SIDEBAR TESTS PASSED! ===\n";
