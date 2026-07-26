<?php require_once ROOT_PATH . '/includes/header.php'; ?>

<div class="content-header mb-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
    <div>
        <h1 class="page-title mb-1"><i class="fas fa-bell text-primary me-2"></i>Notification Center</h1>
        <p class="subtitle mb-0">Real-time updates on placement drives, interview schedules, application statuses, and trainings</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <button type="button" onclick="TPMS.markAllNotificationsRead(this)" class="btn btn-outline-primary btn-sm fw-semibold shadow-sm">
            <i class="fas fa-check-double me-1"></i> Mark All as Read
        </button>
    </div>
</div>

<!-- Search and Filter Bar -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body p-3">
        <div class="row g-3 align-items-center">
            <div class="col-md-7 col-lg-8">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 text-muted">
                        <i class="fas fa-search"></i>
                    </span>
                    <input type="text" id="notifSearchInput" class="form-control border-start-0 ps-0" 
                           placeholder="Search by title, message, company name, or type..." 
                           autocomplete="off">
                </div>
            </div>
            <div class="col-md-5 col-lg-4">
                <div class="d-flex align-items-center gap-2">
                    <label class="form-label mb-0 text-muted small flex-shrink-0 fw-semibold">Filter:</label>
                    <select id="notifCategoryFilter" class="form-select form-select-sm">
                        <option value="all">All Categories</option>
                        <option value="job">Job Postings</option>
                        <option value="interview">Interview Schedules</option>
                        <option value="placement">Application Status</option>
                        <option value="training">Trainings & Workshops</option>
                        <option value="announcement">Announcements</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (empty($notifications)): ?>
<div class="card shadow-sm border-0" id="notifEmptyState">
    <div class="card-body text-center p-5">
        <i class="fas fa-bell-slash text-muted mb-3" style="font-size:3.5rem; opacity:0.4;"></i>
        <h5 class="fw-bold text-dark mb-1">No Notifications Yet</h5>
        <p class="text-muted small mb-0">You are all caught up! Drive and interview updates will appear here.</p>
    </div>
</div>
<?php else: ?>

<!-- Notification List -->
<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
        <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-stream me-2 text-primary"></i>Recent Notifications</h6>
        <span class="badge bg-primary-soft text-primary font-mono fw-bold px-2 py-1"><?= count($notifications) ?> Total</span>
    </div>
    <div class="card-body p-0">
        <div class="list-group list-group-flush" id="notifListContainer">
            <?php foreach ($notifications as $n): ?>
            <?php
                $titleLower = strtolower($n['title'] ?? '');
                $categoryLower = strtolower($n['category'] ?? '');
                $companyName = htmlspecialchars($n['company_name'] ?? '');
                
                $typeClass = 'bg-primary-soft text-primary';
                $iconClass = 'fas fa-info-circle';
                $categoryLabel = 'General Update';
                $matchedCat = 'system';

                if ($categoryLower === 'job' || str_contains($titleLower, 'job') || str_contains($titleLower, 'opening') || str_contains($titleLower, 'post')) {
                    $typeClass = 'bg-primary-soft text-primary';
                    $iconClass = 'fas fa-briefcase';
                    $categoryLabel = 'Job Posted';
                    $matchedCat = 'job';
                } elseif ($categoryLower === 'placement' || str_contains($titleLower, 'application') || str_contains($titleLower, 'status') || str_contains($titleLower, 'shortlist') || str_contains($titleLower, 'select')) {
                    $typeClass = 'bg-success-soft text-success';
                    $iconClass = 'fas fa-paper-plane';
                    $categoryLabel = 'Application Status';
                    $matchedCat = 'placement';
                } elseif ($categoryLower === 'interview' || str_contains($titleLower, 'interview') || str_contains($titleLower, 'schedule')) {
                    $typeClass = 'bg-warning-soft text-warning';
                    $iconClass = 'fas fa-calendar-check';
                    $categoryLabel = 'Interview Schedule';
                    $matchedCat = 'interview';
                } elseif (str_contains($titleLower, 'message') || str_contains($titleLower, 'chat') || str_contains($titleLower, 'hr')) {
                    $typeClass = 'bg-info-soft text-info';
                    $iconClass = 'fas fa-comments';
                    $categoryLabel = 'Company Message';
                    $matchedCat = 'message';
                } elseif ($categoryLower === 'training' || str_contains($titleLower, 'training') || str_contains($titleLower, 'workshop') || str_contains($titleLower, 'course')) {
                    $typeClass = 'bg-violet-soft text-violet';
                    $iconClass = 'fas fa-chalkboard-teacher';
                    $categoryLabel = 'Training Notification';
                    $matchedCat = 'training';
                } elseif ($categoryLower === 'announcement' || str_contains($titleLower, 'announcement')) {
                    $typeClass = 'bg-info-soft text-info';
                    $iconClass = 'fas fa-bullhorn';
                    $categoryLabel = 'Announcement';
                    $matchedCat = 'announcement';
                }

                $isUnread = !$n['is_read'];
                $redirectUrl = url('/notifications/read-redirect/' . $n['id']);
            ?>
            <div id="notif-item-<?= $n['id'] ?>" 
                 class="list-group-item p-3 border-bottom notif-card <?= $isUnread ? 'bg-light border-start border-primary border-3 unread' : 'bg-white' ?>"
                 data-id="<?= $n['id'] ?>"
                 data-title="<?= htmlspecialchars($n['title']) ?>"
                 data-message="<?= htmlspecialchars($n['message']) ?>"
                 data-company="<?= $companyName ?>"
                 data-category="<?= $matchedCat ?>"
                 data-type="<?= htmlspecialchars($n['type'] ?? '') ?>"
                 onclick="if (!event.target.closest('button, a')) window.location.href='<?= $redirectUrl ?>';"
                 style="cursor: pointer; transition: all 0.2s ease;">
                
                <div class="d-flex align-items-start gap-3">
                    <!-- Icon -->
                    <div class="rounded-circle p-2 d-flex align-items-center justify-content-center flex-shrink-0 <?= $typeClass ?>" 
                         style="width:44px; height:44px; font-size:1.15rem;">
                        <i class="<?= $iconClass ?>"></i>
                    </div>

                    <!-- Content -->
                    <div class="flex-grow-1 min-w-0">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-1">
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <a href="<?= $redirectUrl ?>" class="fw-bold text-dark text-decoration-none hover-primary mb-0" style="font-size:0.95rem;">
                                    <?= htmlspecialchars($n['title']) ?>
                                </a>
                                <span class="badge bg-light text-secondary border" style="font-size:0.7rem;"><?= $categoryLabel ?></span>
                                
                                <?php if (!empty($companyName)): ?>
                                <span class="badge bg-primary-soft text-primary" style="font-size:0.7rem;">
                                    <i class="fas fa-building me-1"></i><?= $companyName ?>
                                </span>
                                <?php endif; ?>

                                <?php if ($isUnread): ?>
                                <span class="badge bg-danger rounded-pill unread-pill" style="font-size:0.65rem;">New</span>
                                <?php endif; ?>
                            </div>
                            
                            <span class="text-muted small flex-shrink-0" style="font-size:0.75rem;">
                                <i class="far fa-clock me-1"></i><?= timeAgo($n['created_at']) ?>
                            </span>
                        </div>

                        <div class="text-secondary small mb-2" style="white-space:normal; line-height:1.5;">
                            <?= htmlspecialchars($n['message']) ?>
                        </div>

                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <div>
                                <?php if ($n['is_global']): ?>
                                <span class="badge bg-info-soft text-info" style="font-size:0.68rem;">
                                    <i class="fas fa-globe me-1"></i>Global System Alert
                                </span>
                                <?php endif; ?>
                            </div>

                            <div class="d-flex align-items-center gap-2">
                                <?php if ($isUnread): ?>
                                <button type="button" 
                                        onclick="event.stopPropagation(); TPMS.markNotificationRead(<?= $n['id'] ?>, this);" 
                                        class="btn btn-sm btn-outline-secondary py-1 px-2 btn-mark-read" 
                                        style="font-size:0.75rem; z-index:2;">
                                    <i class="fas fa-check me-1"></i> Mark as Read
                                </button>
                                <?php else: ?>
                                <span class="text-muted small border-0 py-1 px-2" style="font-size:0.75rem;">
                                    <i class="fas fa-check text-success me-1"></i> Read
                                </span>
                                <?php endif; ?>

                                <a href="<?= $redirectUrl ?>" class="btn btn-sm btn-primary py-1 px-2 fw-semibold" style="font-size:0.75rem; z-index:2;">
                                    View Destination <i class="fas fa-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Search Empty State -->
<div class="card shadow-sm border-0" id="notifSearchEmptyState" style="display:none;">
    <div class="card-body text-center p-5">
        <i class="fas fa-search text-muted mb-3" style="font-size:3rem; opacity:0.4;"></i>
        <h5 class="fw-bold text-dark mb-1">No Notifications Found</h5>
        <p class="text-muted small mb-3">No notifications matched your search query or selected category filter.</p>
        <button type="button" onclick="document.getElementById('notifSearchInput').value=''; document.getElementById('notifCategoryFilter').value='all'; TPMS.initNotificationSearch();" class="btn btn-sm btn-outline-primary">
            <i class="fas fa-times me-1"></i> Clear Search Filters
        </button>
    </div>
</div>

<?php endif; ?>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>
