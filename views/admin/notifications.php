<?php require_once ROOT_PATH . '/includes/header.php'; ?>

<div class="content-header mb-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
    <div>
        <h1 class="page-title mb-1"><i class="fas fa-bullhorn text-primary me-2"></i>Notifications Center</h1>
        <p class="subtitle mb-0">Broadcast alerts and view historical system notifications</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <button type="button" onclick="TPMS.markAllNotificationsRead(this)" class="btn btn-outline-primary btn-sm fw-semibold">
            <i class="fas fa-check-double me-1"></i> Mark All as Read
        </button>
        <button class="btn btn-primary btn-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#sendNotifModal">
            <i class="fas fa-paper-plane me-1"></i> Send Notification
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
                           placeholder="Search notifications by title, content, or type..." 
                           autocomplete="off">
                </div>
            </div>
            <div class="col-md-5 col-lg-4">
                <div class="d-flex align-items-center gap-2">
                    <label class="form-label mb-0 text-muted small flex-shrink-0 fw-semibold">Filter:</label>
                    <select id="notifCategoryFilter" class="form-select form-select-sm">
                        <option value="all">All Categories & Types</option>
                        <option value="job">Job Postings</option>
                        <option value="interview">Interview Schedules</option>
                        <option value="placement">Placements & Applications</option>
                        <option value="training">Trainings</option>
                        <option value="announcement">Announcements</option>
                        <option value="info">Info Alerts</option>
                        <option value="success">Success Alerts</option>
                        <option value="warning">Warning Alerts</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (empty($notifications)): ?>
<div class="card shadow-sm border-0" id="notifEmptyState">
    <div class="card-body text-center p-5">
        <i class="fas fa-bell-slash text-muted mb-3" style="font-size:3rem; opacity:0.4;"></i>
        <h5 class="fw-bold text-dark">No Notifications</h5>
        <p class="text-muted small mb-0">No system or admin broadcast notifications available.</p>
    </div>
</div>
<?php else: ?>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
        <h6 class="mb-0 fw-bold"><i class="fas fa-list me-2 text-primary"></i>System Notifications Log</h6>
        <span class="badge bg-primary-soft text-primary font-mono fw-bold"><?= count($notifications) ?> Total</span>
    </div>
    <div class="card-body p-0">
        <div class="list-group list-group-flush" id="notifListContainer">
            <?php foreach ($notifications as $n): ?>
            <?php
                $isUnread = !$n['is_read'];
                $type = $n['type'] ?? 'info';
                $color = $type === 'success' ? 'success' : ($type === 'warning' ? 'warning' : ($type === 'danger' ? 'danger' : 'primary'));
                $icon = $type === 'announcement' ? 'bullhorn' : ($type === 'success' ? 'check-circle' : ($type === 'warning' ? 'exclamation-triangle' : 'info-circle'));
                $redirectUrl = url('/notifications/read-redirect/' . $n['id']);
                $companyName = htmlspecialchars($n['company_name'] ?? '');
            ?>
            <div id="notif-item-<?= $n['id'] ?>" 
                 class="list-group-item p-3 border-bottom notif-card <?= $isUnread ? 'bg-light border-start border-primary border-3 unread' : 'bg-white' ?>"
                 data-id="<?= $n['id'] ?>"
                 data-title="<?= htmlspecialchars($n['title']) ?>"
                 data-message="<?= htmlspecialchars($n['message']) ?>"
                 data-company="<?= $companyName ?>"
                 data-category="<?= htmlspecialchars($n['category'] ?? 'system') ?>"
                 data-type="<?= htmlspecialchars($type) ?>"
                 onclick="if (!event.target.closest('button, a')) window.location.href='<?= $redirectUrl ?>';"
                 style="cursor: pointer; transition: all 0.2s ease;">

                <div class="d-flex align-items-start gap-3">
                    <div class="rounded-circle p-2 d-flex align-items-center justify-content-center flex-shrink-0 bg-<?= $color ?>-soft text-<?= $color ?>" 
                         style="width:42px; height:42px; font-size:1.1rem;">
                        <i class="fas fa-<?= $icon ?>"></i>
                    </div>

                    <div class="flex-grow-1 min-w-0">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-1">
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <a href="<?= $redirectUrl ?>" class="fw-bold text-dark text-decoration-none hover-primary mb-0" style="font-size:0.93rem;">
                                    <?= htmlspecialchars($n['title']) ?>
                                </a>
                                <span class="badge bg-light text-secondary border text-capitalize" style="font-size:0.7rem;"><?= htmlspecialchars($type) ?></span>
                                
                                <?php if (!empty($companyName)): ?>
                                <span class="badge bg-primary-soft text-primary" style="font-size:0.7rem;">
                                    <i class="fas fa-building me-1"></i><?= $companyName ?>
                                </span>
                                <?php endif; ?>

                                <?php if ($n['is_global']): ?>
                                <span class="badge bg-info-soft text-info" style="font-size:0.68rem;"><i class="fas fa-globe me-1"></i>Global</span>
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
                            <div></div>
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
        <p class="text-muted small mb-3">No notifications match your search query.</p>
        <button type="button" onclick="document.getElementById('notifSearchInput').value=''; document.getElementById('notifCategoryFilter').value='all'; TPMS.initNotificationSearch();" class="btn btn-sm btn-outline-primary">
            <i class="fas fa-times me-1"></i> Clear Search
        </button>
    </div>
</div>

<?php endif; ?>

<!-- Send Notification Modal -->
<div class="modal fade" id="sendNotifModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="fas fa-paper-plane text-primary me-2"></i>Send Broadcast Notification</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= url('/admin/send-notification') ?>" method="POST"><?= CsrfMiddleware::tokenField() ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Target Audience *</label>
                        <select class="form-select" name="target">
                            <option value="all">All Users (Global System Alert)</option>
                            <option value="students">All Registered Students</option>
                            <option value="companies">All Registered Companies</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Notification Title *</label>
                        <input type="text" class="form-control" name="title" placeholder="e.g. Placement Drive Schedule Updated" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Message Body *</label>
                        <textarea class="form-control" name="message" rows="3" placeholder="Enter detailed message text..." required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Type</label>
                        <select class="form-select" name="type">
                            <option value="info">Info (Blue)</option>
                            <option value="success">Success (Green)</option>
                            <option value="warning">Warning (Yellow)</option>
                            <option value="announcement">Announcement (Purple/Cyan)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane me-1"></i>Send Now</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>
