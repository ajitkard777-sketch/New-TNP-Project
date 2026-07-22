<?php
/**
 * TPMS - Dashboard Header Include
 * Included in all dashboard pages
 */

$profile = AuthMiddleware::getCurrentProfile();
$currentRole = getCurrentUserRole();

// Safely initialize $urlParts from global scope or request URI
if (!isset($urlParts) || !is_array($urlParts)) {
    if (isset($GLOBALS['urlParts']) && is_array($GLOBALS['urlParts'])) {
        $urlParts = $GLOBALS['urlParts'];
    } else {
        $rawUrl = isset($_GET['url']) ? rtrim($_GET['url'], '/') : '';
        if (!$rawUrl && isset($_SERVER['REQUEST_URI'])) {
            $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            if (defined('BASE_URL') && strpos($path, BASE_URL) === 0) {
                $path = substr($path, strlen(BASE_URL));
            }
            $rawUrl = trim($path, '/');
        }
        $rawUrl = filter_var($rawUrl, FILTER_SANITIZE_URL);
        $urlParts = $rawUrl ? explode('/', $rawUrl) : [];
    }
}

// Map route actions & sub-routes to active sidebar menu keys defensively
if (!function_exists('resolveCurrentPageKey')) {
    function resolveCurrentPageKey(?string $role = null, ?array $urlParts = []): string {
        $role = $role ?? getCurrentUserRole();
        $urlParts = is_array($urlParts) ? $urlParts : [];
        $action = $urlParts[1] ?? 'dashboard';
        $subAction = $urlParts[2] ?? '';

        if ($role === 'admin') {
            switch ($action) {
                case 'view-student':
                case 'update-student-status':
                case 'mark-placed':
                case 'delete-student':
                    return 'students';
                case 'approve-company':
                case 'reject-company':
                case 'delete-company':
                    return 'companies';
                case 'approve-job':
                case 'close-job':
                case 'delete-job':
                    return 'jobs';
                case 'create-training':
                case 'delete-training':
                    return 'trainings';
                case 'create-university':
                    return 'higher-studies';
                case 'create-faculty':
                case 'delete-faculty':
                    return 'faculty';
                case 'schedule-interview':
                case 'edit-interview':
                case 'cancel-interview':
                case 'interview-result':
                    return 'interviews';
                case 'send-notification':
                    return 'notifications';
                default:
                    return $action ?: 'dashboard';
            }
        }

        if ($role === 'company') {
            switch ($action) {
                case 'edit-job':
                case 'delete-job':
                case 'applications':
                case 'update-application':
                    return 'jobs';
                case 'schedule-interview':
                case 'edit-interview':
                case 'cancel-interview':
                case 'interview-result':
                    return 'interviews';
                default:
                    return $action ?: 'dashboard';
            }
        }

        if ($role === 'student') {
            if ($action === 'profile' && $subAction === 'edit') {
                return 'resume';
            }
            switch ($action) {
                case 'apply':
                case 'bookmark':
                    return 'jobs';
                case 'withdraw':
                    return 'applications';
                case 'register-training':
                    return 'trainings';
                case 'register-higher-study':
                    return 'higher-studies';
                default:
                    return $action ?: 'dashboard';
            }
        }

        return $action ?: 'dashboard';
    }
}

$currentPage = resolveCurrentPageKey($currentRole, $urlParts);
$userName = '';
$userAvatar = '';

if ($currentRole === 'student' && $profile) {
    $userName = ($profile['first_name'] ?? '') . ' ' . ($profile['last_name'] ?? '');
    $userAvatar = $profile['profile_photo'] ? uploadUrl('profile_photos/' . $profile['profile_photo']) : asset('images/default-avatar.png');
} elseif ($currentRole === 'company' && $profile) {
    $userName = $profile['company_name'] ?? '';
    $userAvatar = $profile['logo'] ? uploadUrl('company/' . $profile['logo']) : asset('images/default-avatar.png');
} elseif ($currentRole === 'admin' && $profile) {
    $userName = $profile['email'] ?? 'Admin';
    $userAvatar = asset('images/default-avatar.png');
}
$userTheme = $_SESSION['user_theme'] ?? $_COOKIE['tpms_theme'] ?? 'light';
$allowedThemes = ['light','dark','blue','purple','emerald','sunset','midnight','glassmorphism'];
if (!in_array($userTheme, $allowedThemes)) $userTheme = 'light';
?>
<!DOCTYPE html>
<html lang="en" data-theme="<?= $userTheme ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="base-url" content="<?= BASE_URL ?>">
    <meta name="csrf-token" content="<?= CsrfMiddleware::getToken() ?>">
    <script>
    /* Instant theme restore – runs before CSS is applied to prevent flicker */
    (function() {
        var allowed = ['light','dark','blue','purple','emerald','sunset','midnight','glassmorphism'];
        var saved = null;
        // 1. Try localStorage (fastest client-side source)
        try { saved = localStorage.getItem('tpms_theme'); } catch(e) {}
        // 2. Fall back to cookie
        if (!saved) {
            var m = document.cookie.match(/(?:^|;\s*)tpms_theme=([^;]+)/);
            saved = m ? m[1] : null;
        }
        // 3. Fall back to PHP-resolved value
        if (!saved || allowed.indexOf(saved) === -1) {
            saved = '<?= $userTheme ?>';
        }
        document.documentElement.setAttribute('data-theme', saved);
        // Sync localStorage and cookie so future loads are instant
        try { localStorage.setItem('tpms_theme', saved); } catch(e) {}
        document.cookie = 'tpms_theme=' + saved + '; path=/; max-age=31536000; SameSite=Lax';
    })();
    </script>
    <title><?= htmlspecialchars($pageTitle ?? 'Dashboard') ?> - <?= APP_NAME ?></title>
    <meta name="description" content="<?= APP_FULL_NAME ?> - Manage placements, trainings, and student careers efficiently.">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome 6 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    
    <!-- DataTables -->
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    
    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    
    <!-- App CSS -->
    <link href="<?= asset('css/style.css') ?>" rel="stylesheet">
    <link href="<?= asset('css/dark-mode.css') ?>" rel="stylesheet">
    
    <?php if (isset($extraCss)): ?>
        <?php foreach ((array)$extraCss as $css): ?>
            <link href="<?= asset('css/' . $css) ?>" rel="stylesheet">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>
<div class="app-wrapper">
    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay"></div>
    
    <!-- Sidebar -->
    <?php require_once __DIR__ . '/sidebar.php'; ?>
    
    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Navbar -->
        <?php require_once __DIR__ . '/navbar.php'; ?>
        
        <!-- Flash Messages -->
        <?php require_once __DIR__ . '/alerts.php'; ?>
        
        <!-- Content -->
        <div class="content-wrapper">
