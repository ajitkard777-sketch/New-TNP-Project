<?php
/**
 * TPMS - Top Navbar
 */
?>
<header class="top-navbar">
    <div class="navbar-left">
        <button class="menu-toggle" id="menuToggle">
            <i class="fas fa-bars"></i>
        </button>

        <div class="navbar-brand-header">
            <span class="brand-acronym">TPMS</span>
            <span class="brand-divider">|</span>
            <span class="brand-fullname">Training &amp; Placement Management System</span>
        </div>

        <div class="global-search">
            <i class="fas fa-search"></i>
            <input type="text" placeholder="Search students, jobs, companies..." autocomplete="off">
            <div class="search-results-dropdown"></div>
        </div>
    </div>
    
    <div class="navbar-right">
        <!-- Theme Selector Dropdown -->
        <div class="dropdown me-1">
            <button class="navbar-icon-btn dropdown-toggle no-caret" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Select Theme">
                <i class="fas fa-palette text-primary"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end theme-selector-dropdown shadow-lg p-2" style="min-width:200px;border-radius:12px;">
                <li><h6 class="dropdown-header text-uppercase fw-bold" style="font-size:0.7rem;letter-spacing:1px;">Select Theme</h6></li>
                <li>
                    <button type="button" class="dropdown-item theme-opt-btn d-flex align-items-center justify-content-between rounded py-2 px-3" data-theme-val="light">
                        <span class="d-flex align-items-center gap-2"><span class="theme-swatch" style="background:#2563EB;border:2px solid #e2e8f0;width:14px;height:14px;border-radius:50%;display:inline-block;"></span> Light</span>
                        <i class="fas fa-check check-icon text-primary d-none"></i>
                    </button>
                </li>
                <li>
                    <button type="button" class="dropdown-item theme-opt-btn d-flex align-items-center justify-content-between rounded py-2 px-3" data-theme-val="midnight">
                        <span class="d-flex align-items-center gap-2"><span class="theme-swatch" style="background:#000000;border:2px solid #38BDF8;width:14px;height:14px;border-radius:50%;display:inline-block;"></span> OLED Midnight</span>
                        <i class="fas fa-check check-icon text-primary d-none"></i>
                    </button>
                </li>
        </div>
        
        <!-- Chat & Messages Button -->
        <div class="position-relative">
            <a href="<?= url('/chat') ?>" class="navbar-icon-btn" id="navChatBtn" title="Chat & Messages">
                <i class="fas fa-comment-dots text-primary"></i>
                <span class="chat-unread-badge" id="navChatUnreadBadge" style="position:absolute;top:2px;right:2px;min-width:18px;height:18px;border-radius:9px;background:#6366f1;color:#fff;font-size:0.65rem;font-weight:700;display:none;align-items:center;justify-content:center;line-height:1;padding:0 4px;"></span>
            </a>
        </div>

        <!-- Notifications -->
        <div class="position-relative">

            <button class="navbar-icon-btn notification-toggle" id="notificationToggleBtn" title="Notifications">
                <i class="fas fa-bell"></i>
                <span class="badge-dot" style="display:none"></span>
                <span class="notification-count" style="position:absolute;top:2px;right:2px;min-width:18px;height:18px;border-radius:9px;background:#dc2626;color:#fff;font-size:0.65rem;font-weight:700;display:none;align-items:center;justify-content:center;line-height:1;padding:0 4px;"></span>
            </button>
            
            <div class="notification-dropdown">
                <div class="notification-dropdown-header">
                    <h6>Notifications</h6>
                    <a href="javascript:void(0)" onclick="TPMS.markAllNotificationsRead()" class="text-primary" style="font-size:0.78rem;font-weight:600">
                        Mark all read
                    </a>
                </div>
                <div class="notification-list">
                    <div class="p-4 text-center text-muted">
                        <i class="fas fa-bell-slash mb-2 d-block" style="font-size:2rem"></i>
                        <small>Loading notifications...</small>
                    </div>
                </div>
                <div class="p-2 text-center border-top">
                    <a href="<?= url('/' . $currentRole . '/notifications') ?>" class="text-primary" style="font-size:0.82rem;font-weight:600">
                        View All Notifications
                    </a>
                </div>
            </div>
        </div>
        
        <!-- User Dropdown -->
        <div class="dropdown">
            <button class="btn btn-light btn-sm dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown">
                <img src="<?= $userAvatar ?>" alt="" style="width:28px;height:28px;border-radius:50%;object-fit:cover" onerror="this.src='<?= asset('images/default-avatar.png') ?>'">
                <span class="d-none d-md-inline"><?= htmlspecialchars($userName ?: 'User') ?></span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li>
                    <a class="dropdown-item" href="<?= url('/' . $currentRole . '/profile') ?>">
                        <i class="fas fa-user me-2 text-primary"></i> My Profile
                    </a>
                </li>
                <?php if ($currentRole === 'student'): ?>
                <li>
                    <a class="dropdown-item" href="<?= url('/student/change-password') ?>">
                        <i class="fas fa-key me-2 text-warning"></i> Change Password
                    </a>
                </li>
                <?php endif; ?>
                <?php if ($currentRole === 'admin'): ?>
                <li>
                    <a class="dropdown-item" href="<?= url('/admin/settings') ?>">
                        <i class="fas fa-cog me-2 text-secondary"></i> Settings
                    </a>
                </li>
                <?php endif; ?>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <a class="dropdown-item text-danger" href="<?= url('/logout') ?>">
                        <i class="fas fa-sign-out-alt me-2"></i> Logout
                    </a>
                </li>
            </ul>
        </div>
    </div>
</header>
