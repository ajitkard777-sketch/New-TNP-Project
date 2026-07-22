<?php
/**
 * TPMS - Sidebar Navigation
 * Professional ERP / Admin Dashboard Sidebar with Collapsible Submenus
 */

$sidebarMenus = [];

// Student Sidebar Configuration
if ($currentRole === 'student') {
    $sidebarMenus = [
        'MAIN' => [
            ['icon' => 'fas fa-th-large', 'label' => 'Dashboard', 'url' => '/student/dashboard', 'key' => 'dashboard'],
            ['icon' => 'fas fa-user-circle', 'label' => 'My Profile', 'url' => '/student/profile', 'key' => 'profile'],
        ],
        'CAREER' => [
            ['icon' => 'fas fa-briefcase', 'label' => 'Browse Jobs', 'url' => '/student/jobs', 'key' => 'jobs'],
            ['icon' => 'fas fa-paper-plane', 'label' => 'My Applications', 'url' => '/student/applications', 'key' => 'applications'],
            ['icon' => 'fas fa-bookmark', 'label' => 'Bookmarks', 'url' => '/student/bookmarks', 'key' => 'bookmarks'],
            ['icon' => 'fas fa-calendar-check', 'label' => 'Interviews', 'url' => '/student/interviews', 'key' => 'interviews'],
        ],
        'DEVELOPMENT' => [
            ['icon' => 'fas fa-chalkboard-teacher', 'label' => 'Trainings', 'url' => '/student/trainings', 'key' => 'trainings'],
            ['icon' => 'fas fa-graduation-cap', 'label' => 'Higher Studies', 'url' => '/student/higher-studies', 'key' => 'higher-studies'],
        ],
        'ACCOUNT' => [
            ['icon' => 'fas fa-file-alt', 'label' => 'Resume', 'url' => '/student/profile/edit', 'key' => 'resume'],
            ['icon' => 'fas fa-bell', 'label' => 'Notifications', 'url' => '/student/notifications', 'key' => 'notifications'],
            ['icon' => 'fas fa-key', 'label' => 'Change Password', 'url' => '/student/change-password', 'key' => 'change-password'],
        ]
    ];
}

// Company Sidebar Configuration
if ($currentRole === 'company') {
    $sidebarMenus = [
        'MAIN' => [
            ['icon' => 'fas fa-th-large', 'label' => 'Dashboard', 'url' => '/company/dashboard', 'key' => 'dashboard'],
            ['icon' => 'fas fa-building', 'label' => 'Company Profile', 'url' => '/company/profile', 'key' => 'profile'],
        ],
        'RECRUITMENT' => [
            ['icon' => 'fas fa-plus-circle', 'label' => 'Post New Job', 'url' => '/company/post-job', 'key' => 'post-job'],
            ['icon' => 'fas fa-briefcase', 'label' => 'Manage Jobs', 'url' => '/company/jobs', 'key' => 'jobs'],
            ['icon' => 'fas fa-calendar-alt', 'label' => 'Interviews', 'url' => '/company/interviews', 'key' => 'interviews'],
        ]
    ];
}

// Admin Sidebar Configuration with Collapsible Submenus
if ($currentRole === 'admin') {
    $sidebarMenus = [
        'OVERVIEW' => [
            ['icon' => 'fas fa-th-large', 'label' => 'Dashboard', 'url' => '/admin/dashboard', 'key' => 'dashboard'],
            ['icon' => 'fas fa-chart-line', 'label' => 'Reports & Analytics', 'url' => '/admin/reports', 'key' => 'reports'],
        ],
        'DIRECTORY' => [
            [
                'icon' => 'fas fa-users-cog',
                'label' => 'User Management',
                'key' => 'user_mgmt',
                'submenu' => [
                    ['icon' => 'fas fa-user-graduate', 'label' => 'Students', 'url' => '/admin/students', 'key' => 'students'],
                    ['icon' => 'fas fa-building', 'label' => 'Companies', 'url' => '/admin/companies', 'key' => 'companies'],
                    ['icon' => 'fas fa-chalkboard-teacher', 'label' => 'Faculty', 'url' => '/admin/faculty', 'key' => 'faculty'],
                ]
            ],
            [
                'icon' => 'fas fa-briefcase',
                'label' => 'Placement Drive',
                'key' => 'drive_mgmt',
                'submenu' => [
                    ['icon' => 'fas fa-layer-group', 'label' => 'Manage Jobs', 'url' => '/admin/jobs', 'key' => 'jobs'],
                    ['icon' => 'fas fa-trophy', 'label' => 'Placements Record', 'url' => '/admin/placements', 'key' => 'placements'],
                    ['icon' => 'fas fa-calendar-check', 'label' => 'Interviews Schedule', 'url' => '/admin/interviews', 'key' => 'interviews'],
                ]
            ],
            [
                'icon' => 'fas fa-graduation-cap',
                'label' => 'Academic Programs',
                'key' => 'prog_mgmt',
                'submenu' => [
                    ['icon' => 'fas fa-laptop-code', 'label' => 'Training Programs', 'url' => '/admin/trainings', 'key' => 'trainings'],
                    ['icon' => 'fas fa-university', 'label' => 'Higher Studies', 'url' => '/admin/higher-studies', 'key' => 'higher-studies'],
                ]
            ]
        ],
        'SYSTEM CONTROL' => [
            ['icon' => 'fas fa-check-double', 'label' => 'Pending Approvals', 'url' => '/admin/approvals', 'key' => 'approvals'],
            ['icon' => 'fas fa-bell', 'label' => 'Notifications', 'url' => '/admin/notifications', 'key' => 'notifications'],
            ['icon' => 'fas fa-history', 'label' => 'Activity Logs', 'url' => '/admin/logs', 'key' => 'logs'],
            ['icon' => 'fas fa-cog', 'label' => 'System Settings', 'url' => '/admin/settings', 'key' => 'settings'],
        ]
    ];
}
?>

<aside class="sidebar" id="sidebar">
    <!-- Sidebar Header -->
    <div class="sidebar-header">
        <div class="sidebar-logo">
            <i class="fas fa-graduation-cap"></i>
        </div>
        <div class="sidebar-brand">
            <div class="brand-title">TPMS</div>
            <div class="brand-sub">Training &amp; Placement</div>
        </div>
    </div>
    
    <!-- User Profile Badge -->
    <div class="sidebar-user">
        <img src="<?= $userAvatar ?>" alt="Avatar" class="sidebar-user-avatar" onerror="this.src='<?= asset('images/default-avatar.png') ?>'">
        <div class="sidebar-user-info">
            <div class="sidebar-user-name"><?= htmlspecialchars($userName ?: 'User') ?></div>
            <div class="sidebar-user-role"><?= ucfirst($currentRole) ?></div>
        </div>
    </div>
    
    <!-- Navigation -->
    <nav class="sidebar-nav">
        <?php foreach ($sidebarMenus as $section => $items): ?>
            <div class="sidebar-nav-title"><?= $section ?></div>
            <?php foreach ($items as $item): ?>
                <?php if (isset($item['submenu'])): ?>
                    <?php
                    $isSubActive = false;
                    foreach ($item['submenu'] as $sub) {
                        if ($currentPage === $sub['key']) {
                            $isSubActive = true;
                            break;
                        }
                    }
                    ?>
                    <div class="sidebar-nav-item has-submenu <?= $isSubActive ? 'open' : '' ?>">
                        <a href="javascript:void(0)" class="sidebar-nav-link submenu-toggle <?= $isSubActive ? 'active' : '' ?>">
                            <span class="nav-icon"><i class="<?= $item['icon'] ?>"></i></span>
                            <span class="nav-text"><?= $item['label'] ?></span>
                            <i class="fas fa-chevron-right submenu-arrow"></i>
                        </a>
                        <div class="sidebar-submenu" style="<?= $isSubActive ? 'display: block;' : '' ?>">
                            <?php foreach ($item['submenu'] as $sub): ?>
                                <a href="<?= url($sub['url']) ?>" class="sidebar-submenu-link <?= $currentPage === $sub['key'] ? 'active' : '' ?>">
                                    <span class="sub-icon"><i class="<?= $sub['icon'] ?>"></i></span>
                                    <span class="sub-text"><?= $sub['label'] ?></span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="sidebar-nav-item">
                        <a href="<?= url($item['url']) ?>" class="sidebar-nav-link <?= $currentPage === $item['key'] ? 'active' : '' ?>">
                            <span class="nav-icon"><i class="<?= $item['icon'] ?>"></i></span>
                            <span class="nav-text"><?= $item['label'] ?></span>
                            <?php if (isset($item['badge'])): ?>
                                <span class="badge bg-danger ms-auto"><?= $item['badge'] ?></span>
                            <?php endif; ?>
                        </a>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endforeach; ?>
        
        <!-- Logout Session Link -->
        <div class="sidebar-nav-title">SESSION</div>
        <div class="sidebar-nav-item">
            <a href="<?= url('/logout') ?>" class="sidebar-nav-link logout-link">
                <span class="nav-icon"><i class="fas fa-sign-out-alt"></i></span>
                <span class="nav-text">Logout</span>
            </a>
        </div>
    </nav>
</aside>
