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
<div class="alert alert-warning" style="border-left:4px solid #f59e0b">
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
        <a href="<?= url('/student/applications') ?>" class="stat-card-link">
            <div class="stat-card gradient-primary">
                <div class="stat-card-icon bg-primary-soft"><i class="fas fa-paper-plane"></i></div>
                <div class="stat-card-value"><?= $applicationCount ?></div>
                <div class="stat-card-label">Applications</div>
            </div>
        </a>
    </div>
    <div class="col-xl-2 col-lg-4 col-md-4 col-sm-6">
        <a href="<?= url('/student/applications?status=shortlisted') ?>" class="stat-card-link">
            <div class="stat-card gradient-warning">
                <div class="stat-card-icon bg-warning-soft"><i class="fas fa-star"></i></div>
                <div class="stat-card-value"><?= $shortlistedCount ?></div>
                <div class="stat-card-label">Shortlisted</div>
            </div>
        </a>
    </div>
    <div class="col-xl-2 col-lg-4 col-md-4 col-sm-6">
        <a href="<?= url('/student/applications?status=selected') ?>" class="stat-card-link">
            <div class="stat-card gradient-success">
                <div class="stat-card-icon bg-success-soft"><i class="fas fa-check-circle"></i></div>
                <div class="stat-card-value"><?= $selectedCount ?></div>
                <div class="stat-card-label">Selected</div>
            </div>
        </a>
    </div>
    <div class="col-xl-2 col-lg-4 col-md-4 col-sm-6">
        <a href="<?= url('/student/interviews') ?>" class="stat-card-link">
            <div class="stat-card gradient-info">
                <div class="stat-card-icon bg-info-soft"><i class="fas fa-calendar-check"></i></div>
                <div class="stat-card-value"><?= $interviewCount ?></div>
                <div class="stat-card-label">Interviews</div>
            </div>
        </a>
    </div>
    <div class="col-xl-2 col-lg-4 col-md-4 col-sm-6">
        <a href="<?= url('/student/trainings') ?>" class="stat-card-link">
            <div class="stat-card gradient-violet">
                <div class="stat-card-icon bg-violet-soft"><i class="fas fa-chalkboard-teacher"></i></div>
                <div class="stat-card-value"><?= $trainingCount ?></div>
                <div class="stat-card-label">Trainings</div>
            </div>
        </a>
    </div>
    <div class="col-xl-2 col-lg-4 col-md-4 col-sm-6">
        <a href="<?= url('/student/higher-studies') ?>" class="stat-card-link">
            <div class="stat-card gradient-danger">
                <div class="stat-card-icon bg-danger-soft"><i class="fas fa-graduation-cap"></i></div>
                <div class="stat-card-value"><?= $higherStudiesCount ?></div>
                <div class="stat-card-label">Higher Studies</div>
            </div>
        </a>
    </div>
    <div class="col-xl-2 col-lg-4 col-md-4 col-sm-6">
        <a href="<?= url('/student/bookmarks') ?>" class="stat-card-link">
            <div class="stat-card" style="background:var(--card-bg)">
                <div class="stat-card-icon bg-primary-soft"><i class="fas fa-bookmark text-primary"></i></div>
                <div class="stat-card-value"><?= $bookmarkCount ?></div>
                <div class="stat-card-label">Bookmarks</div>
            </div>
        </a>
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

        <!-- Academic & Activity Analytics Chart -->
        <div class="card mt-4">
            <div class="card-header">
                <h6><i class="fas fa-chart-pie me-2 text-primary"></i>My Career &amp; Academic Activity Overview</h6>
            </div>
            <div class="card-body">
                <div class="chart-container"><canvas id="studentAnalyticsChart" height="200"></canvas></div>
            </div>
        </div>
    </div>
</div>

<?php
$inlineJs = "
new Chart(document.getElementById('studentAnalyticsChart'), {
    type: 'bar',
    data: {
        labels: ['Job Apps', 'Shortlisted', 'Selected', 'Interviews', 'Trainings', 'Higher Studies'],
        datasets: [{
            label: 'Count',
            data: [$applicationCount, $shortlistedCount, $selectedCount, $interviewCount, $trainingCount, $higherStudiesCount],
            backgroundColor: ['#6366f1', '#f59e0b', '#10b981', '#06b6d4', '#8b5cf6', '#ef4444'],
            borderRadius: 6
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, precision: 0 } }
    }
});";
?>

<!-- ── AI Job Recommendations ─────────────────────────────────── -->
<?php if (!empty($aiRecommendedJobs)): ?>
<div class="card mt-4">
    <div class="card-header d-flex align-items-center justify-content-between">
        <h6 class="mb-0">
            <i class="fas fa-robot text-primary me-2"></i>AI Job Recommendations
            <span class="badge bg-primary ms-1" style="font-size:0.65rem;">PERSONALIZED</span>
        </h6>
        <a href="<?= url('/student/jobs') ?>" class="btn btn-sm btn-outline-primary">Browse All Jobs</a>
    </div>
    <div class="card-body p-0">
        <div class="row g-0">
            <?php foreach ($aiRecommendedJobs as $rj): ?>
            <?php
            $sc     = (float)($rj['recommendation_score'] ?? 0);
            $scCol  = $sc >= 75 ? 'success' : ($sc >= 55 ? 'primary' : ($sc >= 35 ? 'warning' : 'danger'));
            $level  = $rj['recommendation_level'] ?? 'Fair Match';
            $matched = array_filter(array_map('trim', explode(',', $rj['matched_skills'] ?? '')));
            $logo   = $rj['logo'] ? uploadUrl('company/' . $rj['logo']) : asset('images/default-avatar.png');
            ?>
            <div class="col-md-6 p-3 border-bottom border-end-md">
                <div class="d-flex gap-3 align-items-start">
                    <!-- Company logo -->
                    <img src="<?= $logo ?>" onerror="this.src='<?= asset('images/default-avatar.png') ?>'"
                         style="width:44px;height:44px;border-radius:10px;object-fit:cover;border:1px solid var(--border-color);flex-shrink:0;">
                    <div class="flex-grow-1 min-w-0">
                        <div class="fw-bold text-truncate" style="font-size:0.88rem;"><?= htmlspecialchars($rj['title']) ?></div>
                        <div class="text-muted small"><?= htmlspecialchars($rj['company_name']) ?></div>
                        <div class="d-flex flex-wrap gap-1 mt-1">
                            <span class="badge bg-light text-dark border" style="font-size:0.68rem;">
                                <i class="fas fa-map-marker-alt me-1 text-muted"></i><?= htmlspecialchars($rj['location'] ?? 'N/A') ?>
                            </span>
                            <?php if ($rj['salary_min'] || $rj['salary_max']): ?>
                            <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size:0.68rem;">
                                <?= formatSalaryRange($rj['salary_min'], $rj['salary_max']) ?>
                            </span>
                            <?php endif; ?>
                        </div>
                        <!-- Match score bar -->
                        <div class="mt-2 d-flex align-items-center gap-2">
                            <div class="progress flex-grow-1" style="height:5px;border-radius:4px;">
                                <div class="progress-bar bg-<?= $scCol ?>" style="width:<?= round($sc) ?>%;transition:width 1s;"></div>
                            </div>
                            <span class="fw-bold text-<?= $scCol ?>" style="font-size:0.72rem;flex-shrink:0;"><?= round($sc) ?>%</span>
                            <span class="badge bg-<?= $scCol ?>-subtle text-<?= $scCol ?> border" style="font-size:0.62rem;"><?= $level ?></span>
                        </div>
                        <!-- Matching skills -->
                        <?php if (!empty($matched)): ?>
                        <div class="d-flex flex-wrap gap-1 mt-1">
                            <?php foreach (array_slice($matched, 0, 3) as $sk): ?>
                            <span class="badge bg-success-subtle text-success" style="font-size:0.62rem;"><?= htmlspecialchars($sk) ?></span>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <a href="<?= url('/student/jobs') ?>" class="btn btn-xs btn-primary flex-shrink-0" style="font-size:0.72rem;padding:4px 10px;white-space:nowrap;">Apply</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>

