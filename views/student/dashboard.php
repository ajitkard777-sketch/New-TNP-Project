<?php require_once ROOT_PATH . '/includes/header.php'; ?>

<div class="content-header">
    <div>
        <h1 class="page-title"><?= getGreeting() ?>, <?= htmlspecialchars($student['first_name'] ?? 'Student') ?>!</h1>
        <p class="subtitle">Welcome to your placement dashboard</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('/student/jobs') ?>" class="btn btn-primary btn-sm"><i class="fas fa-briefcase me-1"></i> Browse Jobs</a>
        <a href="<?= url('/student/profile/edit') ?>" class="btn btn-outline-primary btn-sm"><i class="fas fa-user-edit me-1"></i> Edit Profile</a>
    </div>
</div>

<!-- Profile Completion Alert -->
<?php if (($student['profile_completion'] ?? 0) < 80): ?>
<div class="alert alert-warning animate-fade-in-up" style="border-left:4px solid #f59e0b">
    <i class="fas fa-exclamation-triangle me-2"></i>
    <div>
        <strong>Complete your profile!</strong> Your profile is <?= $student['profile_completion'] ?? 0 ?>% complete. 
        <a href="<?= url('/student/profile/edit') ?>" class="fw-bold">Complete it now →</a> to increase your chances of getting placed.
    </div>
</div>
<?php endif; ?>

<!-- Stat Cards -->
<div class="row g-4 mb-4">
    <div class="col-xl-2 col-lg-4 col-md-4 col-sm-6">
        <div class="stat-card gradient-primary">
            <div class="stat-card-icon bg-primary-soft"><i class="fas fa-paper-plane"></i></div>
            <div class="stat-card-value"><?= $applicationCount ?></div>
            <div class="stat-card-label">Applications</div>
        </div>
    </div>
    <div class="col-xl-2 col-lg-4 col-md-4 col-sm-6">
        <div class="stat-card gradient-warning">
            <div class="stat-card-icon bg-warning-soft"><i class="fas fa-star"></i></div>
            <div class="stat-card-value"><?= $shortlistedCount ?></div>
            <div class="stat-card-label">Shortlisted</div>
        </div>
    </div>
    <div class="col-xl-2 col-lg-4 col-md-4 col-sm-6">
        <div class="stat-card gradient-success">
            <div class="stat-card-icon bg-success-soft"><i class="fas fa-check-circle"></i></div>
            <div class="stat-card-value"><?= $selectedCount ?></div>
            <div class="stat-card-label">Selected</div>
        </div>
    </div>
    <div class="col-xl-2 col-lg-4 col-md-4 col-sm-6">
        <div class="stat-card gradient-info">
            <div class="stat-card-icon bg-info-soft"><i class="fas fa-calendar-check"></i></div>
            <div class="stat-card-value"><?= $interviewCount ?></div>
            <div class="stat-card-label">Interviews</div>
        </div>
    </div>
    <div class="col-xl-2 col-lg-4 col-md-4 col-sm-6">
        <div class="stat-card gradient-violet">
            <div class="stat-card-icon bg-violet-soft"><i class="fas fa-chalkboard-teacher"></i></div>
            <div class="stat-card-value"><?= $trainingCount ?></div>
            <div class="stat-card-label">Trainings</div>
        </div>
    </div>
    <div class="col-xl-2 col-lg-4 col-md-4 col-sm-6">
        <div class="stat-card gradient-danger">
            <div class="stat-card-icon bg-danger-soft"><i class="fas fa-bookmark"></i></div>
            <div class="stat-card-value"><?= $bookmarkCount ?></div>
            <div class="stat-card-label">Bookmarks</div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Profile Card -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-body profile-card">
                <img src="<?= $student['profile_photo'] ? uploadUrl('profile_photos/' . $student['profile_photo']) : asset('images/default-avatar.png') ?>" 
                     alt="Profile" class="profile-avatar" onerror="this.src='<?= asset('images/default-avatar.png') ?>'">
                <h5 class="profile-name"><?= htmlspecialchars(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? '')) ?></h5>
                <p class="profile-role"><?= htmlspecialchars($student['branch'] ?? 'Student') ?> | <?= htmlspecialchars($student['enrollment_no'] ?? 'N/A') ?></p>
                
                <div class="profile-completion mt-3">
                    <div class="profile-completion-label">
                        <span>Profile Completion</span>
                        <span class="text-primary fw-bold"><?= $student['profile_completion'] ?? 0 ?>%</span>
                    </div>
                    <div class="progress">
                        <div class="progress-bar" style="width: <?= $student['profile_completion'] ?? 0 ?>%"></div>
                    </div>
                </div>
                
                <div class="mt-3">
                    <?php if ($student['cgpa']): ?>
                    <span class="badge bg-primary me-1">CGPA: <?= $student['cgpa'] ?></span>
                    <?php endif; ?>
                    <?php if ($student['is_placed']): ?>
                    <span class="badge bg-success"><i class="fas fa-check me-1"></i>Placed</span>
                    <?php endif; ?>
                </div>
                
                <div class="mt-3 d-grid gap-2">
                    <a href="<?= url('/student/profile') ?>" class="btn btn-primary btn-sm"><i class="fas fa-user me-1"></i> View Profile</a>
                    <?php if ($student['resume_path']): ?>
                    <a href="<?= url('/student/preview-resume') ?>" class="btn btn-outline-primary btn-sm" target="_blank"><i class="fas fa-file-pdf me-1"></i> View Resume</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Upcoming Interviews -->
        <?php if (!empty($upcomingInterviews)): ?>
        <div class="card mt-4">
            <div class="card-header">
                <h6><i class="fas fa-calendar-check me-2 text-primary"></i>Upcoming Interviews</h6>
            </div>
            <div class="card-body p-0">
                <?php foreach ($upcomingInterviews as $interview): ?>
                <div class="p-3 border-bottom">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="fw-bold" style="font-size:0.9rem"><?= htmlspecialchars($interview['job_title']) ?></div>
                            <div class="text-muted" style="font-size:0.8rem"><?= htmlspecialchars($interview['company_name']) ?></div>
                        </div>
                        <span class="badge <?= getStatusBadgeClass($interview['status']) ?>"><?= ucfirst($interview['status']) ?></span>
                    </div>
                    <div class="mt-2" style="font-size:0.78rem">
                        <i class="far fa-calendar me-1 text-primary"></i><?= formatDate($interview['interview_date']) ?>
                        <i class="far fa-clock ms-2 me-1 text-primary"></i><?= date('h:i A', strtotime($interview['interview_time'])) ?>
                        <?php if ($interview['venue']): ?>
                        <br><i class="fas fa-map-marker-alt me-1 text-danger mt-1"></i><?= htmlspecialchars($interview['venue']) ?>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Recent Jobs -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h6><i class="fas fa-briefcase me-2 text-primary"></i>Latest Job Openings</h6>
                <a href="<?= url('/student/jobs') ?>" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($recentJobs)): ?>
                <div class="empty-state">
                    <i class="fas fa-briefcase"></i>
                    <h5>No Active Jobs</h5>
                    <p>New job postings will appear here when companies post them.</p>
                </div>
                <?php else: ?>
                <?php foreach ($recentJobs as $job): ?>
                <div class="p-3 border-bottom d-flex align-items-center gap-3 hover-scale" style="cursor:pointer" onclick="window.location='<?= url('/student/jobs') ?>'">
                    <img src="<?= $job['logo'] ? uploadUrl('company/' . $job['logo']) : asset('images/default-avatar.png') ?>" 
                         alt="" class="rounded" style="width:44px;height:44px;object-fit:cover;border:1px solid var(--border-color)" 
                         onerror="this.src='<?= asset('images/default-avatar.png') ?>'">
                    <div class="flex-grow-1">
                        <div class="fw-bold" style="font-size:0.9rem"><?= htmlspecialchars($job['title']) ?></div>
                        <div style="font-size:0.8rem">
                            <span class="text-primary"><?= htmlspecialchars($job['company_name']) ?></span>
                            <span class="text-muted ms-2"><i class="fas fa-map-marker-alt me-1"></i><?= htmlspecialchars($job['location'] ?? 'N/A') ?></span>
                        </div>
                    </div>
                    <div class="text-end">
                        <div class="fw-bold text-success" style="font-size:0.85rem"><?= formatSalaryRange($job['salary_min'], $job['salary_max']) ?></div>
                        <div style="font-size:0.72rem" class="text-muted"><?= $job['application_deadline'] ? 'Due: ' . formatDate($job['application_deadline']) : 'Open' ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Notifications -->
        <div class="card mt-4">
            <div class="card-header">
                <h6><i class="fas fa-bell me-2 text-warning"></i>Recent Notifications</h6>
                <a href="<?= url('/student/notifications') ?>" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($notifications)): ?>
                <div class="p-4 text-center text-muted"><small>No notifications yet.</small></div>
                <?php else: ?>
                <?php foreach (array_slice($notifications, 0, 5) as $notif): ?>
                <div class="notification-item">
                    <div class="n-icon bg-<?= $notif['type'] === 'success' ? 'success' : ($notif['type'] === 'warning' ? 'warning' : ($notif['type'] === 'danger' ? 'danger' : 'primary')) ?>-soft">
                        <i class="fas fa-<?= $notif['type'] === 'success' ? 'check-circle text-success' : ($notif['type'] === 'warning' ? 'exclamation-triangle text-warning' : ($notif['type'] === 'announcement' ? 'bullhorn text-info' : 'info-circle text-primary')) ?>"></i>
                    </div>
                    <div class="n-content">
                        <div class="n-title"><?= htmlspecialchars($notif['title']) ?></div>
                        <div class="n-text"><?= htmlspecialchars(truncateText($notif['message'], 80)) ?></div>
                        <div class="n-time"><i class="far fa-clock me-1"></i><?= timeAgo($notif['created_at']) ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>
