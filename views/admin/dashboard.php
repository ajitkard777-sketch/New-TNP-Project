<?php require_once ROOT_PATH . '/includes/header.php'; ?>

<div class="content-header">
    <div><h1 class="page-title">Admin Dashboard</h1><p class="subtitle">System overview and analytics</p></div>
    <div class="d-flex gap-2">
        <a href="<?= url('/admin/reports') ?>" class="btn btn-primary btn-sm"><i class="fas fa-chart-bar me-1"></i> Reports</a>
        <a href="<?= url('/admin/approvals') ?>" class="btn btn-warning btn-sm"><i class="fas fa-check-double me-1"></i> Approvals <?php if ($pendingCompanies > 0): ?><span class="badge bg-danger ms-1"><?= $pendingCompanies + count($pendingJobs) ?></span><?php endif; ?></a>
    </div>
</div>

<!-- Key Stats -->
<div class="row g-4 mb-4">
    <div class="col-xl-2 col-lg-4 col-md-4 col-sm-6 d-flex">
        <a href="<?= url('/admin/students') ?>" class="stat-card-link w-100">
            <div class="stat-card">
                <div class="stat-card-icon text-primary"><i class="fas fa-user-graduate"></i></div>
                <div>
                    <div class="stat-card-value"><?= $totalStudents ?></div>
                    <div class="stat-card-label">Total Students</div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-xl-2 col-lg-4 col-md-4 col-sm-6 d-flex">
        <a href="<?= url('/admin/students?status=placed') ?>" class="stat-card-link w-100">
            <div class="stat-card">
                <div class="stat-card-icon text-success"><i class="fas fa-trophy"></i></div>
                <div>
                    <div class="stat-card-value"><?= $placedStudents ?></div>
                    <div class="stat-card-label">Placed Students</div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-xl-2 col-lg-4 col-md-4 col-sm-6 d-flex">
        <a href="<?= url('/admin/companies') ?>" class="stat-card-link w-100">
            <div class="stat-card">
                <div class="stat-card-icon text-info"><i class="fas fa-building"></i></div>
                <div>
                    <div class="stat-card-value"><?= $totalCompanies ?></div>
                    <div class="stat-card-label">Companies</div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-xl-2 col-lg-4 col-md-4 col-sm-6 d-flex">
        <a href="<?= url('/admin/jobs') ?>" class="stat-card-link w-100">
            <div class="stat-card">
                <div class="stat-card-icon text-warning"><i class="fas fa-briefcase"></i></div>
                <div>
                    <div class="stat-card-value"><?= $activeJobs ?></div>
                    <div class="stat-card-label">Active Jobs</div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-xl-2 col-lg-4 col-md-4 col-sm-6 d-flex">
        <a href="<?= url('/admin/placements') ?>" class="stat-card-link w-100">
            <div class="stat-card">
                <div class="stat-card-icon text-violet"><i class="fas fa-rupee-sign"></i></div>
                <div>
                    <div class="stat-card-value"><?= $highestPackage ? number_format($highestPackage, 1) : '0' ?> <small style="font-size:0.75rem" class="text-muted fw-normal">LPA</small></div>
                    <div class="stat-card-label">Highest Package</div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-xl-2 col-lg-4 col-md-4 col-sm-6 d-flex">
        <a href="<?= url('/admin/placements') ?>" class="stat-card-link w-100">
            <div class="stat-card">
                <div class="stat-card-icon text-danger"><i class="fas fa-chart-line"></i></div>
                <div>
                    <div class="stat-card-value"><?= $averagePackage ? number_format($averagePackage, 1) : '0' ?> <small style="font-size:0.75rem" class="text-muted fw-normal">LPA</small></div>
                    <div class="stat-card-label">Avg Package</div>
                </div>
            </div>
        </a>
    </div>
</div>

<!-- Academic & Engagement Analytics Cards -->
<div class="row g-4 mb-4">
    <div class="col-lg-4 col-md-4 col-12 d-flex">
        <a href="<?= url('/admin/students') ?>" class="stat-card-link w-100">
            <div class="stat-card">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-card-icon text-primary bg-light mb-0 flex-shrink-0" style="width:48px;height:48px;border-radius:12px;"><i class="fas fa-paper-plane"></i></div>
                    <div class="min-w-0 flex-grow-1">
                        <div class="stat-card-value" style="font-size:1.4rem;"><?= $appliedStudentsCount ?></div>
                        <div class="stat-card-label text-truncate text-muted fw-medium" title="Students Applied for Jobs">Students Applied</div>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-lg-4 col-md-4 col-12 d-flex">
        <a href="<?= url('/admin/trainings') ?>" class="stat-card-link w-100">
            <div class="stat-card">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-card-icon text-violet bg-light mb-0 flex-shrink-0" style="width:48px;height:48px;border-radius:12px;"><i class="fas fa-chalkboard-teacher"></i></div>
                    <div class="min-w-0 flex-grow-1">
                        <div class="stat-card-value" style="font-size:1.4rem;"><?= $trainingEnrolledCount ?></div>
                        <div class="stat-card-label text-truncate text-muted fw-medium" title="Students Enrolled in Training">Training Enrolled</div>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-lg-4 col-md-4 col-12 d-flex">
        <a href="<?= url('/admin/higher-studies') ?>" class="stat-card-link w-100">
            <div class="stat-card">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-card-icon text-danger bg-light mb-0 flex-shrink-0" style="width:48px;height:48px;border-radius:12px;"><i class="fas fa-graduation-cap"></i></div>
                    <div class="min-w-0 flex-grow-1">
                        <div class="stat-card-value" style="font-size:1.4rem;"><?= $higherStudiesCount ?></div>
                        <div class="stat-card-label text-truncate text-muted fw-medium" title="Students Registered for Higher Studies">Higher Studies</div>
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>

<!-- Placement Drive Module Navigation Cards -->
<div class="mb-4">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h6 class="mb-0 fw-bold text-dark" style="font-size:0.8rem;text-transform:uppercase;letter-spacing:0.6px;color:var(--text-muted)!important;">
                <i class="fas fa-briefcase me-2" style="color:var(--primary);"></i>Placement Drive
            </h6>
        </div>
        <a href="<?= url('/admin/jobs') ?>" class="text-primary text-decoration-none" style="font-size:0.82rem;font-weight:600;">View all &rarr;</a>
    </div>
    <div class="row g-3">
        <!-- Placement Drives card → /admin/jobs -->
        <div class="col-lg-4 col-md-4 col-sm-12 d-flex">
            <a href="<?= url('/admin/jobs') ?>"
               class="drive-nav-card w-100"
               id="cardPlacementDrives"
               aria-label="Manage Placement Drives — <?= $activeJobs ?> active jobs"
               role="button"
               tabindex="0">
                <div class="drive-nav-card-inner">
                    <div class="drive-nav-icon" style="background:rgba(99,102,241,0.10);color:#6366f1;">
                        <i class="fas fa-layer-group"></i>
                    </div>
                    <div class="drive-nav-body">
                        <div class="drive-nav-value"><?= $activeJobs ?></div>
                        <div class="drive-nav-label">Placement Drives</div>
                        <div class="drive-nav-sub"><?= $totalJobs ?> total &middot; <?= $activeJobs ?> active</div>
                    </div>
                    <div class="drive-nav-arrow">
                        <i class="fas fa-chevron-right"></i>
                    </div>
                </div>
            </a>
        </div>

        <!-- Placement Records card → /admin/placements -->
        <div class="col-lg-4 col-md-4 col-sm-12 d-flex">
            <a href="<?= url('/admin/placements') ?>"
               class="drive-nav-card w-100"
               id="cardPlacementRecords"
               aria-label="Placement Records — <?= $totalPlacements ?> confirmed placements"
               role="button"
               tabindex="0">
                <div class="drive-nav-card-inner">
                    <div class="drive-nav-icon" style="background:rgba(16,185,129,0.10);color:#10b981;">
                        <i class="fas fa-trophy"></i>
                    </div>
                    <div class="drive-nav-body">
                        <div class="drive-nav-value"><?= $totalPlacements ?></div>
                        <div class="drive-nav-label">Placement Records</div>
                        <div class="drive-nav-sub"><?= $placedStudents ?> students placed</div>
                    </div>
                    <div class="drive-nav-arrow">
                        <i class="fas fa-chevron-right"></i>
                    </div>
                </div>
            </a>
        </div>

        <!-- Interview Schedule card → /admin/interviews -->
        <div class="col-lg-4 col-md-4 col-sm-12 d-flex">
            <a href="<?= url('/admin/interviews') ?>"
               class="drive-nav-card w-100"
               id="cardInterviewSchedule"
               aria-label="Interview Schedule — <?= $scheduledInterviews ?> upcoming interviews"
               role="button"
               tabindex="0">
                <div class="drive-nav-card-inner">
                    <div class="drive-nav-icon" style="background:rgba(245,158,11,0.10);color:#f59e0b;">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="drive-nav-body">
                        <div class="drive-nav-value"><?= $scheduledInterviews ?></div>
                        <div class="drive-nav-label">Interview Schedule</div>
                        <div class="drive-nav-sub"><?= $totalInterviews ?> total &middot; <?= $scheduledInterviews ?> upcoming</div>
                    </div>
                    <div class="drive-nav-arrow">
                        <i class="fas fa-chevron-right"></i>
                    </div>
                </div>
            </a>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Charts -->
    <div class="col-lg-8">
        <div class="card mb-4 border-0 shadow-sm">
            <div class="card-header bg-transparent border-bottom-0 pt-4 pb-2">
                <h6 class="mb-0 fw-bold text-dark">Branch-wise Placements</h6>
            </div>
            <div class="card-body"><div class="chart-container"><canvas id="branchChart" height="280"></canvas></div></div>
        </div>

        <!-- Student Engagement & Career Tracks Chart -->
        <div class="card mb-4 border-0 shadow-sm">
            <div class="card-header bg-transparent border-bottom-0 pt-4 pb-2">
                <h6 class="mb-0 fw-bold text-dark">Student Career Tracks</h6>
            </div>
            <div class="card-body"><div class="chart-container"><canvas id="engagementChart" height="240"></canvas></div></div>
        </div>

        <!-- Monthly Placement Trend -->
        <div class="card mb-4 border-0 shadow-sm">
            <div class="card-header bg-transparent border-bottom-0 pt-4 pb-2">
                <h6 class="mb-0 fw-bold text-dark">Placement Trend</h6>
            </div>
            <div class="card-body"><div class="chart-container"><canvas id="trendChart" height="240"></canvas></div></div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Placement Rate Donut -->
        <div class="card mb-4 border-0 shadow-sm">
            <div class="card-header bg-transparent border-bottom-0 pt-4 pb-2">
                <h6 class="mb-0 fw-bold text-dark">Placement Rate</h6>
            </div>
            <div class="card-body text-center pt-2">
                <div style="position:relative; width: 100%; display: flex; justify-content: center;">
                    <canvas id="placementPie" height="220" style="max-height: 220px;"></canvas>
                    <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center;">
                        <span class="fs-2 fw-bolder text-dark lh-1"><?= $totalStudents > 0 ? round(($placedStudents / $totalStudents) * 100, 1) : 0 ?>%</span>
                        <div class="small text-muted fw-medium mt-1">Placed</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Approvals -->
        <div class="card mb-4 border-0 shadow-sm">
            <div class="card-header bg-transparent border-bottom-0 pt-4 pb-2 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold text-dark">Action Required</h6>
                <a href="<?= url('/admin/approvals') ?>" class="text-primary text-decoration-none small fw-semibold">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    <?php if (empty($pendingCompanyList) && empty($pendingJobs)): ?>
                    <div class="list-group-item p-4 text-center text-muted border-0"><small>All caught up!</small></div>
                    <?php else: ?>
                    <?php foreach (array_slice($pendingCompanyList, 0, 3) as $pc): ?>
                    <div class="list-group-item d-flex justify-content-between align-items-center p-3 border-light">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center" style="width:36px;height:36px;"><i class="fas fa-building small"></i></div>
                            <div>
                                <div class="fw-semibold text-dark text-truncate" style="max-width:180px;font-size:0.9rem;"><?= htmlspecialchars($pc['company_name']) ?></div>
                                <div class="text-muted" style="font-size:0.75rem;">Company Approval</div>
                            </div>
                        </div>
                        <a href="<?= url('/admin/approve-company/' . $pc['id']) ?>" class="btn btn-sm btn-light text-success px-2 py-1"><i class="fas fa-check"></i></a>
                    </div>
                    <?php endforeach; ?>
                    <?php foreach (array_slice($pendingJobs, 0, 3) as $pj): ?>
                    <div class="list-group-item d-flex justify-content-between align-items-center p-3 border-light">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-success-subtle text-success rounded-circle d-flex align-items-center justify-content-center" style="width:36px;height:36px;"><i class="fas fa-briefcase small"></i></div>
                            <div>
                                <div class="fw-semibold text-dark text-truncate" style="max-width:180px;font-size:0.9rem;"><?= htmlspecialchars($pj['title']) ?></div>
                                <div class="text-muted" style="font-size:0.75rem;">Job Approval</div>
                            </div>
                        </div>
                        <a href="<?= url('/admin/approve-job/' . $pj['id']) ?>" class="btn btn-sm btn-light text-success px-2 py-1"><i class="fas fa-check"></i></a>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-transparent border-bottom-0 pt-4 pb-2 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold text-dark">Audit Log</h6>
        <a href="<?= url('/admin/logs') ?>" class="text-primary text-decoration-none small fw-semibold">View Full Log</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-borderless table-hover mb-0 align-middle">
                <thead class="bg-light text-muted">
                    <tr>
                        <th class="ps-4 fw-medium" style="font-size:0.8rem;text-transform:uppercase;letter-spacing:0.5px;">User</th>
                        <th class="fw-medium" style="font-size:0.8rem;text-transform:uppercase;letter-spacing:0.5px;">Action</th>
                        <th class="fw-medium" style="font-size:0.8rem;text-transform:uppercase;letter-spacing:0.5px;">Detail</th>
                        <th class="pe-4 text-end fw-medium" style="font-size:0.8rem;text-transform:uppercase;letter-spacing:0.5px;">Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentActivities as $a): ?>
                    <tr class="border-bottom border-light">
                        <td class="ps-4 text-dark fw-medium" style="font-size:0.85rem;"><?= htmlspecialchars($a['email'] ?? 'System') ?></td>
                        <td><span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 rounded-pill px-2 py-1 fw-normal"><?= htmlspecialchars($a['action']) ?></span></td>
                        <td class="text-muted" style="font-size:0.85rem;"><?= htmlspecialchars($a['description'] ?? '') ?></td>
                        <td class="pe-4 text-end text-muted" style="font-size:0.8rem;"><?= timeAgo($a['created_at']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
$inlineJs = "
const commonOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { labels: { font: { family: \"'Inter', sans-serif\", size: 12 }, usePointStyle: true, boxWidth: 8 } } },
    scales: {
        x: { grid: { display: false }, ticks: { font: { family: \"'Inter', sans-serif\", size: 11 }, color: '#6b7280' } },
        y: { grid: { borderDash: [4, 4], color: '#f3f4f6', drawBorder: false }, ticks: { font: { family: \"'Inter', sans-serif\", size: 11 }, color: '#6b7280', padding: 8 } }
    },
    layout: { padding: 10 }
};

// Branch Chart
const branchLabels = " . json_encode(array_column($branchStats, 'branch')) . ";
const branchTotal = " . json_encode(array_column($branchStats, 'total')) . ";
const branchPlaced = " . json_encode(array_column($branchStats, 'placed')) . ";
new Chart(document.getElementById('branchChart'), {
    type: 'bar', data: { labels: branchLabels, datasets: [
        { label: 'Total', data: branchTotal, backgroundColor: '#cbd5e1', borderRadius: 4, barPercentage: 0.6, categoryPercentage: 0.8 },
        { label: 'Placed', data: branchPlaced, backgroundColor: '#3b82f6', borderRadius: 4, barPercentage: 0.6, categoryPercentage: 0.8 }
    ]}, options: { ...commonOptions, plugins: { legend: { position: 'top', align: 'end' } } }
});

// Student Engagement Chart
new Chart(document.getElementById('engagementChart'), {
    type: 'bar',
    data: {
        labels: ['Job Applicants', 'Training Enrolled', 'Higher Studies', 'Placed Students'],
        datasets: [{
            label: 'Student Count',
            data: [$appliedStudentsCount, $trainingEnrolledCount, $higherStudiesCount, $placedStudents],
            backgroundColor: ['#3b82f6', '#8b5cf6', '#f43f5e', '#10b981'],
            borderRadius: 4, maxBarThickness: 40
        }]
    },
    options: { ...commonOptions, plugins: { legend: { display: false } } }
});

// Placement Pie
new Chart(document.getElementById('placementPie'), {
    type: 'doughnut', data: { labels: ['Placed', 'Unplaced'], datasets: [{ data: [$placedStudents, " . ($totalStudents - $placedStudents) . "],
    backgroundColor: ['#3b82f6', '#f1f5f9'], borderWidth: 0, hoverOffset: 4 }] },
    options: { responsive: true, maintainAspectRatio: false, cutout: '80%', plugins: { legend: { display: false }, tooltip: { enabled: true } } }
});

// Trend Chart
const months = " . json_encode(array_column($monthlyPlacements, 'month')) . ";
const counts = " . json_encode(array_column($monthlyPlacements, 'count')) . ";
new Chart(document.getElementById('trendChart'), {
    type: 'line', data: { labels: months, datasets: [{ label: 'Placements', data: counts,
    borderColor: '#3b82f6', backgroundColor: 'rgba(59,130,246,0.1)', fill: true, tension: 0.4, borderWidth: 2, pointRadius: 0, pointHoverRadius: 6, pointBackgroundColor: '#3b82f6' }] },
    options: { ...commonOptions, plugins: { legend: { display: false } } }
});";
?>
<?php require_once ROOT_PATH . '/includes/footer.php'; ?>
