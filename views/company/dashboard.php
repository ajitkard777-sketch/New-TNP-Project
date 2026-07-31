<?php require_once ROOT_PATH . '/includes/header.php'; ?>

<div class="content-header">
    <div>
        <h1 class="page-title">Welcome, <?= htmlspecialchars($company['company_name']) ?>!</h1>
        <p class="subtitle">Company recruitment dashboard</p>
    </div>
    <a href="<?= url('/company/post-job') ?>" class="btn btn-primary btn-sm"><i class="fas fa-plus-circle me-1"></i> Post New Job</a>
</div>

<?php if (!$company['is_approved']): ?>
<div class="alert alert-warning"><i class="fas fa-clock me-2"></i><strong>Pending Approval</strong> — Your company registration is under review. You'll be able to post jobs once approved.</div>
<?php endif; ?>

<!-- Stats -->
<div class="row g-4 mb-4">
    <div class="col-xl-2 col-lg-4 col-md-4 col-sm-6 d-flex">
        <a href="<?= url('/company/jobs') ?>" class="stat-card-link w-100">
            <div class="stat-card gradient-primary">
                <div class="stat-card-icon bg-primary-soft"><i class="fas fa-briefcase"></i></div>
                <div>
                    <div class="stat-card-value"><?= $totalJobs ?></div>
                    <div class="stat-card-label">Total Jobs</div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-xl-2 col-lg-4 col-md-4 col-sm-6 d-flex">
        <a href="<?= url('/company/jobs') ?>" class="stat-card-link w-100">
            <div class="stat-card gradient-success">
                <div class="stat-card-icon bg-success-soft"><i class="fas fa-check-circle"></i></div>
                <div>
                    <div class="stat-card-value"><?= $activeJobs ?></div>
                    <div class="stat-card-label">Active Jobs</div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-xl-2 col-lg-4 col-md-4 col-sm-6 d-flex">
        <a href="<?= url('/company/applications/all?status=applied') ?>" class="stat-card-link w-100">
            <div class="stat-card gradient-info">
                <div class="stat-card-icon bg-info-soft"><i class="fas fa-users"></i></div>
                <div>
                    <div class="stat-card-value"><?= $uniqueApplicantsCount ?></div>
                    <div class="stat-card-label">Students Applied</div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-xl-2 col-lg-4 col-md-4 col-sm-6 d-flex">
        <a href="<?= url('/company/applications/all?status=shortlisted') ?>" class="stat-card-link w-100">
            <div class="stat-card gradient-warning">
                <div class="stat-card-icon bg-warning-soft"><i class="fas fa-star"></i></div>
                <div>
                    <div class="stat-card-value"><?= $shortlisted ?></div>
                    <div class="stat-card-label">Shortlisted</div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-xl-2 col-lg-4 col-md-4 col-sm-6 d-flex">
        <a href="<?= url('/company/applications/all?status=selected') ?>" class="stat-card-link w-100">
            <div class="stat-card gradient-violet">
                <div class="stat-card-icon bg-violet-soft"><i class="fas fa-trophy"></i></div>
                <div>
                    <div class="stat-card-value"><?= $selected ?></div>
                    <div class="stat-card-label">Selected</div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-xl-2 col-lg-4 col-md-4 col-sm-6 d-flex">
        <a href="<?= url('/company/interviews') ?>" class="stat-card-link w-100">
            <div class="stat-card gradient-danger">
                <div class="stat-card-icon bg-danger-soft"><i class="fas fa-calendar-check"></i></div>
                <div>
                    <div class="stat-card-value"><?= $interviewCount ?></div>
                    <div class="stat-card-label">Interviews</div>
                </div>
            </div>
        </a>
    </div>
</div>

<!-- Recruitment Analytics Chart -->
<div class="card mb-4">
    <div class="card-header"><h6><i class="fas fa-chart-bar me-2 text-primary"></i>Recruitment Analytics &amp; Applicant Pipeline</h6></div>
    <div class="card-body"><div class="chart-container"><canvas id="companyFunnelChart" height="220"></canvas></div></div>
</div>

<div class="row g-4">
    <!-- Jobs List -->
    <div class="col-lg-5">
        <div class="card">
            <div class="card-header"><h6><i class="fas fa-briefcase me-2 text-primary"></i>Your Jobs</h6><a href="<?= url('/company/jobs') ?>" class="btn btn-sm btn-outline-primary">View All</a></div>
            <div class="card-body p-0">
                <?php if (empty($jobs)): ?>
                <div class="empty-state py-4"><i class="fas fa-briefcase" style="font-size:2rem"></i><p class="mt-2"><small>No jobs posted yet.</small></p></div>
                <?php else: ?>
                <?php foreach (array_slice($jobs, 0, 5) as $job): ?>
                <div class="p-3 border-bottom">
                    <div class="d-flex justify-content-between align-items-start">
                        <div><div class="fw-bold" style="font-size:0.9rem"><?= htmlspecialchars($job['title']) ?></div>
                            <small class="text-muted"><?= $job['application_count'] ?> applications • <?= JOB_TYPES[$job['job_type']] ?? ucfirst($job['job_type']) ?></small></div>
                        <span class="badge <?= getStatusBadgeClass($job['status']) ?>"><?= ucfirst($job['status']) ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Recent Applications -->
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header"><h6><i class="fas fa-users me-2 text-primary"></i>Recent Applications</h6></div>
            <div class="card-body p-0">
                <?php if (empty($recentApps)): ?>
                <div class="empty-state py-4"><i class="fas fa-inbox" style="font-size:2rem"></i><p class="mt-2"><small>No applications yet.</small></p></div>
                <?php else: ?>
                <div class="table-responsive"><table class="table mb-0">
                    <thead><tr><th>Student</th><th>Job</th><th>Branch</th><th>CGPA</th><th>Status</th><th>Date</th></tr></thead>
                    <tbody>
                        <?php foreach ($recentApps as $a): ?>
                        <tr>
                            <td><div class="user-cell"><img src="<?= $a['profile_photo'] ? uploadUrl('profile_photos/' . $a['profile_photo']) : asset('images/default-avatar.png') ?>" alt="" class="user-avatar" onerror="this.src='<?= asset('images/default-avatar.png') ?>'"><span class="fw-medium"><?= htmlspecialchars($a['first_name'] . ' ' . $a['last_name']) ?></span></div></td>
                            <td><small><?= htmlspecialchars($a['job_title']) ?></small></td>
                            <td><small><?= htmlspecialchars($a['branch']) ?></small></td>
                            <td><span class="fw-bold text-primary"><?= $a['cgpa'] ?? 'N/A' ?></span></td>
                            <td><span class="badge <?= getStatusBadgeClass($a['status']) ?>"><?= ucfirst($a['status']) ?></span></td>
                            <td><small class="text-muted"><?= timeAgo($a['applied_at']) ?></small></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
$inlineJs = "
new Chart(document.getElementById('companyFunnelChart'), {
    type: 'bar',
    data: {
        labels: ['Unique Applicants', 'Total Applications', 'Shortlisted', 'Selected', 'Scheduled Interviews'],
        datasets: [{
            label: 'Count',
            data: [$uniqueApplicantsCount, $totalApplications, $shortlisted, $selected, $interviewCount],
            backgroundColor: ['#6366f1', '#06b6d4', '#f59e0b', '#10b981', '#ef4444'],
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

<!-- ── AI Recommendations Preview ──────────────────────────────── -->
<?php
require_once ROOT_PATH . '/models/Recommendation.php';
$recoModel = new Recommendation();
$topRecs   = $recoModel->getTopStudentsForCompany($company['id'], 3); // top 3 per job, show first job
$recoStats = $recoModel->getCompanyRecommendationStats($company['id']);
$firstRec  = $topRecs[0] ?? null;
?>
<div class="card mt-4">
    <div class="card-header d-flex align-items-center justify-content-between">
        <h6 class="mb-0"><i class="fas fa-robot text-primary me-2"></i>AI Student Recommendations</h6>
        <a href="<?= url('/company/recommendations') ?>" class="btn btn-sm btn-outline-primary">
            <i class="fas fa-expand-alt me-1"></i> View All
        </a>
    </div>
    <div class="card-body">
        <?php if (!$firstRec || empty($firstRec['students'])): ?>
        <!-- No recommendations yet -->
        <div class="text-center py-3">
            <i class="fas fa-robot fa-2x text-muted mb-2 d-block"></i>
            <p class="text-muted small mb-2">No recommendations computed yet.</p>
            <a href="<?= url('/company/recommendations?refresh=1') ?>" class="btn btn-sm btn-primary">
                <i class="fas fa-sync-alt me-1"></i> Generate Now
            </a>
        </div>
        <?php else: ?>
        <!-- Stats row -->
        <div class="row g-2 mb-3">
            <div class="col-4 text-center">
                <div class="fw-bold text-primary" style="font-size:1.2rem;"><?= (int)($recoStats['total_candidates'] ?? 0) ?></div>
                <div class="text-muted" style="font-size:0.7rem;">Analysed</div>
            </div>
            <div class="col-4 text-center border-start border-end">
                <div class="fw-bold text-success" style="font-size:1.2rem;"><?= (int)($recoStats['excellent_matches'] ?? 0) ?></div>
                <div class="text-muted" style="font-size:0.7rem;">Excellent</div>
            </div>
            <div class="col-4 text-center">
                <div class="fw-bold text-warning" style="font-size:1.2rem;"><?= round((float)($recoStats['avg_score'] ?? 0), 0) ?>%</div>
                <div class="text-muted" style="font-size:0.7rem;">Avg Score</div>
            </div>
        </div>
        <!-- Top students for first job -->
        <div class="text-muted mb-2" style="font-size:0.76rem;font-weight:600;text-transform:uppercase;letter-spacing:.4px;">
            <i class="fas fa-briefcase me-1 text-primary"></i><?= htmlspecialchars($firstRec['job']['title']) ?>
        </div>
        <div class="d-flex flex-column gap-2">
            <?php foreach (array_slice($firstRec['students'], 0, 3) as $s): ?>
            <?php
            $sc = (float)($s['recommendation_score'] ?? 0);
            $sc_color = $sc >= 75 ? 'success' : ($sc >= 55 ? 'primary' : ($sc >= 35 ? 'warning' : 'danger'));
            $avUrl = $s['profile_photo'] ? uploadUrl('profile_photos/' . $s['profile_photo']) : asset('images/default-avatar.png');
            ?>
            <div class="d-flex align-items-center gap-3 p-2 rounded-3 border">
                <img src="<?= $avUrl ?>" onerror="this.src='<?= asset('images/default-avatar.png') ?>'"
                     style="width:36px;height:36px;border-radius:50%;object-fit:cover;flex-shrink:0;">
                <div class="flex-grow-1 min-w-0">
                    <div class="fw-semibold text-truncate" style="font-size:0.83rem;"><?= htmlspecialchars($s['first_name'] . ' ' . $s['last_name']) ?></div>
                    <div class="d-flex align-items-center gap-2 mt-1">
                        <div class="progress flex-grow-1" style="height:4px;border-radius:4px;">
                            <div class="progress-bar bg-<?= $sc_color ?>" style="width:<?= round($sc) ?>%;"></div>
                        </div>
                        <span class="fw-bold text-<?= $sc_color ?>" style="font-size:0.75rem;flex-shrink:0;"><?= round($sc) ?>%</span>
                    </div>
                </div>
                <a href="<?= url('/company/view-applicant/' . $s['student_id']) ?>" class="btn btn-xs btn-primary flex-shrink-0" style="padding:3px 8px;font-size:0.72rem;">
                    View
                </a>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-3">
            <a href="<?= url('/company/recommendations') ?>" class="btn btn-sm btn-outline-primary w-100">
                <i class="fas fa-robot me-1"></i> See All Recommendations
            </a>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Recent Notifications Widget Card -->
<div class="card shadow-sm border-0 mt-4" style="border-radius:12px;">
    <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center pt-3 px-3 pb-0">
        <h6 class="fw-bold mb-0" style="font-size:0.9rem;">
            <i class="fas fa-bell text-warning me-2"></i>Recent Notifications
        </h6>
        <a href="<?= url('/company/notifications') ?>" class="text-primary fw-semibold" style="font-size:0.78rem;">View All</a>
    </div>
    <div class="card-body p-2">
        <?php if (empty($notifications)): ?>
        <div class="p-3 text-center text-muted"><small>No notifications yet.</small></div>
        <?php else: ?>
        <div class="d-flex flex-column gap-1">
            <?php foreach ($notifications as $n): ?>
            <?php
                $targetUrl = getNotificationUrl($n, 'company');
                $nData = [
                    'id' => (int)$n['id'],
                    'title' => $n['title'],
                    'message' => $n['message'],
                    'type' => $n['type'] ?? 'info',
                    'category' => $n['category'] ?? 'system',
                    'time_ago' => timeAgo($n['created_at']),
                    'is_read' => (int)($n['is_read'] ?? 0),
                    'link' => $targetUrl
                ];
                $jsonNotif = htmlspecialchars(json_encode($nData), ENT_QUOTES, 'UTF-8');
            ?>
            <a href="javascript:void(0)" onclick='TPMS.openNotificationFullView(<?= $jsonNotif ?>, event)' class="d-flex align-items-center gap-2 p-2 rounded-2 text-decoration-none <?= !$n['is_read'] ? 'fw-bold bg-light' : '' ?>">
                <div class="p-2 rounded-circle bg-<?= $n['type'] === 'success' ? 'success' : ($n['type'] === 'warning' ? 'warning' : 'primary') ?>-soft flex-shrink-0" style="width:32px;height:32px;display:flex;align-items:center;justify-content:center;">
                    <i class="fas fa-<?= $n['type'] === 'success' ? 'check-circle' : ($n['type'] === 'warning' ? 'exclamation-triangle' : 'bell') ?> text-<?= $n['type'] === 'success' ? 'success' : ($n['type'] === 'warning' ? 'warning' : 'primary') ?>" style="font-size:0.75rem;"></i>
                </div>
                <div class="flex-grow-1 min-w-0">
                    <div class="text-dark text-truncate" style="font-size:0.8rem;"><?= htmlspecialchars($n['title']) ?></div>
                    <div class="text-muted text-truncate" style="font-size:0.72rem;"><?= htmlspecialchars($n['message']) ?></div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>

