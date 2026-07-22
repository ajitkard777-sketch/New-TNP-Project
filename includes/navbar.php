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
            <ul class="dropdown-menu dropdown-menu-end theme-selector-dropdown shadow-lg p-2" style="min-width:210px;border-radius:12px;">
                <li><h6 class="dropdown-header text-uppercase fw-bold" style="font-size:0.7rem;letter-spacing:1px;">Select Theme</h6></li>
                <li>
                    <button type="button" class="dropdown-item theme-opt-btn d-flex align-items-center justify-content-between rounded py-2 px-3" data-theme-val="light">
                        <span class="d-flex align-items-center gap-2"><span class="theme-swatch" style="background:#1e40af;border:2px solid #e2e8f0;width:14px;height:14px;border-radius:50%;display:inline-block;"></span> Light Classic</span>
                        <i class="fas fa-check check-icon text-primary d-none"></i>
                    </button>
                </li>
                <li>
                    <button type="button" class="dropdown-item theme-opt-btn d-flex align-items-center justify-content-between rounded py-2 px-3" data-theme-val="dark">
                        <span class="d-flex align-items-center gap-2"><span class="theme-swatch" style="background:#0f172a;border:2px solid #334155;width:14px;height:14px;border-radius:50%;display:inline-block;"></span> Dark Slate</span>
                        <i class="fas fa-check check-icon text-primary d-none"></i>
                    </button>
                </li>
                <li>
                    <button type="button" class="dropdown-item theme-opt-btn d-flex align-items-center justify-content-between rounded py-2 px-3" data-theme-val="blue">
                        <span class="d-flex align-items-center gap-2"><span class="theme-swatch" style="background:#2563eb;border:2px solid #93c5fd;width:14px;height:14px;border-radius:50%;display:inline-block;"></span> Ocean Blue</span>
                        <i class="fas fa-check check-icon text-primary d-none"></i>
                    </button>
                </li>
                <li>
                    <button type="button" class="dropdown-item theme-opt-btn d-flex align-items-center justify-content-between rounded py-2 px-3" data-theme-val="purple">
                        <span class="d-flex align-items-center gap-2"><span class="theme-swatch" style="background:#4f46e5;border:2px solid #c7d2fe;width:14px;height:14px;border-radius:50%;display:inline-block;"></span> Royal Purple</span>
                        <i class="fas fa-check check-icon text-primary d-none"></i>
                    </button>
                </li>
                <li>
                    <button type="button" class="dropdown-item theme-opt-btn d-flex align-items-center justify-content-between rounded py-2 px-3" data-theme-val="emerald">
                        <span class="d-flex align-items-center gap-2"><span class="theme-swatch" style="background:#059669;border:2px solid #a7f3d0;width:14px;height:14px;border-radius:50%;display:inline-block;"></span> Emerald Forest</span>
                        <i class="fas fa-check check-icon text-primary d-none"></i>
                    </button>
                </li>
                <li>
                    <button type="button" class="dropdown-item theme-opt-btn d-flex align-items-center justify-content-between rounded py-2 px-3" data-theme-val="sunset">
                        <span class="d-flex align-items-center gap-2"><span class="theme-swatch" style="background:#d97706;border:2px solid #fde68a;width:14px;height:14px;border-radius:50%;display:inline-block;"></span> Sunset Amber</span>
                        <i class="fas fa-check check-icon text-primary d-none"></i>
                    </button>
                </li>
                <li>
                    <button type="button" class="dropdown-item theme-opt-btn d-flex align-items-center justify-content-between rounded py-2 px-3" data-theme-val="midnight">
                        <span class="d-flex align-items-center gap-2"><span class="theme-swatch" style="background:#000000;border:2px solid #06b6d4;width:14px;height:14px;border-radius:50%;display:inline-block;"></span> OLED Midnight</span>
                        <i class="fas fa-check check-icon text-primary d-none"></i>
                    </button>
                </li>
                <li>
                    <button type="button" class="dropdown-item theme-opt-btn d-flex align-items-center justify-content-between rounded py-2 px-3" data-theme-val="glassmorphism">
                        <span class="d-flex align-items-center gap-2"><span class="theme-swatch" style="background:linear-gradient(135deg,#3b82f6,#ec4899);border:2px solid #fff;width:14px;height:14px;border-radius:50%;display:inline-block;"></span> Glassmorphism</span>
                        <i class="fas fa-check check-icon text-primary d-none"></i>
                    </button>
                </li>
            </ul>
        </div>
        
        <!-- Notifications -->
        <div class="position-relative">
            <button class="navbar-icon-btn notification-toggle" data-bs-toggle="tooltip" title="Notifications">
                <i class="fas fa-bell"></i>
                <span class="badge-dot" style="display:none"></span>
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
