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
    <div class="col-xl-2 col-lg-4 col-sm-6"><div class="stat-card gradient-primary"><div class="stat-card-icon bg-primary-soft"><i class="fas fa-user-graduate"></i></div><div class="stat-card-value"><?= $totalStudents ?></div><div class="stat-card-label">Students</div></div></div>
    <div class="col-xl-2 col-lg-4 col-sm-6"><div class="stat-card gradient-success"><div class="stat-card-icon bg-success-soft"><i class="fas fa-trophy"></i></div><div class="stat-card-value"><?= $placedStudents ?></div><div class="stat-card-label">Placed</div></div></div>
    <div class="col-xl-2 col-lg-4 col-sm-6"><div class="stat-card gradient-info"><div class="stat-card-icon bg-info-soft"><i class="fas fa-building"></i></div><div class="stat-card-value"><?= $totalCompanies ?></div><div class="stat-card-label">Companies</div></div></div>
    <div class="col-xl-2 col-lg-4 col-sm-6"><div class="stat-card gradient-warning"><div class="stat-card-icon bg-warning-soft"><i class="fas fa-briefcase"></i></div><div class="stat-card-value"><?= $activeJobs ?></div><div class="stat-card-label">Active Jobs</div></div></div>
    <div class="col-xl-2 col-lg-4 col-sm-6"><div class="stat-card gradient-violet"><div class="stat-card-icon bg-violet-soft"><i class="fas fa-rupee-sign"></i></div><div class="stat-card-value"><?= $highestPackage ? number_format($highestPackage, 1) : '0' ?></div><div class="stat-card-label">Highest LPA</div></div></div>
    <div class="col-xl-2 col-lg-4 col-sm-6"><div class="stat-card gradient-danger"><div class="stat-card-icon bg-danger-soft"><i class="fas fa-chart-line"></i></div><div class="stat-card-value"><?= $averagePackage ? number_format($averagePackage, 1) : '0' ?></div><div class="stat-card-label">Average LPA</div></div></div>
</div>

<div class="row g-4">
    <!-- Charts -->
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header"><h6><i class="fas fa-chart-bar me-2 text-primary"></i>Branch-wise Placements</h6></div>
            <div class="card-body"><div class="chart-container"><canvas id="branchChart" height="300"></canvas></div></div>
        </div>

        <!-- Monthly Placement Trend -->
        <div class="card mb-4">
            <div class="card-header"><h6><i class="fas fa-chart-line me-2 text-success"></i>Monthly Placement Trend</h6></div>
            <div class="card-body"><div class="chart-container"><canvas id="trendChart" height="250"></canvas></div></div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Placement Rate Donut -->
        <div class="card mb-4">
            <div class="card-header"><h6><i class="fas fa-chart-pie me-2 text-primary"></i>Placement Rate</h6></div>
            <div class="card-body text-center"><canvas id="placementPie" height="250"></canvas>
                <div class="mt-2"><span class="fs-3 fw-bold text-primary"><?= $totalStudents > 0 ? round(($placedStudents / $totalStudents) * 100, 1) : 0 ?>%</span><br><small class="text-muted"><?= $placedStudents ?> of <?= $totalStudents ?> placed</small></div>
            </div>
        </div>

        <!-- Pending Approvals -->
        <div class="card mb-4">
            <div class="card-header"><h6><i class="fas fa-clock me-2 text-warning"></i>Pending Approvals</h6><a href="<?= url('/admin/approvals') ?>" class="btn btn-sm btn-outline-warning">View All</a></div>
            <div class="card-body p-0">
                <?php if (empty($pendingCompanyList) && empty($pendingJobs)): ?>
                <div class="p-3 text-center text-muted"><small>Nothing pending!</small></div>
                <?php else: ?>
                <?php foreach (array_slice($pendingCompanyList, 0, 3) as $pc): ?>
                <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
                    <div><small class="badge bg-info me-1">Company</small><span class="fw-medium" style="font-size:0.85rem"><?= htmlspecialchars($pc['company_name']) ?></span></div>
                    <a href="<?= url('/admin/approve-company/' . $pc['id']) ?>" class="btn btn-sm btn-success"><i class="fas fa-check"></i></a>
                </div>
                <?php endforeach; ?>
                <?php foreach (array_slice($pendingJobs, 0, 3) as $pj): ?>
                <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
                    <div><small class="badge bg-primary me-1">Job</small><span class="fw-medium" style="font-size:0.85rem"><?= htmlspecialchars($pj['title']) ?></span></div>
                    <a href="<?= url('/admin/approve-job/' . $pj['id']) ?>" class="btn btn-sm btn-success"><i class="fas fa-check"></i></a>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity -->
<div class="card">
    <div class="card-header"><h6><i class="fas fa-history me-2 text-primary"></i>Recent Activity</h6><a href="<?= url('/admin/logs') ?>" class="btn btn-sm btn-outline-primary">View All</a></div>
    <div class="card-body p-0"><div class="table-responsive">
        <table class="table mb-0"><thead><tr><th>User</th><th>Action</th><th>Description</th><th>Time</th></tr></thead><tbody>
            <?php foreach ($recentActivities as $a): ?>
            <tr><td><small><?= htmlspecialchars($a['email'] ?? 'System') ?></small></td><td><span class="badge bg-light text-dark"><?= htmlspecialchars($a['action']) ?></span></td><td><small><?= htmlspecialchars($a['description'] ?? '') ?></small></td><td><small class="text-muted"><?= timeAgo($a['created_at']) ?></small></td></tr>
            <?php endforeach; ?>
        </tbody></table>
    </div></div>
</div>

<?php
$inlineJs = "
// Branch Chart
const branchLabels = " . json_encode(array_column($branchStats, 'branch')) . ";
const branchTotal = " . json_encode(array_column($branchStats, 'total')) . ";
const branchPlaced = " . json_encode(array_column($branchStats, 'placed')) . ";
new Chart(document.getElementById('branchChart'), {
    type: 'bar', data: { labels: branchLabels, datasets: [
        { label: 'Total', data: branchTotal, backgroundColor: 'rgba(99,102,241,0.7)', borderRadius: 6 },
        { label: 'Placed', data: branchPlaced, backgroundColor: 'rgba(16,185,129,0.7)', borderRadius: 6 }
    ]}, options: { responsive: true, plugins: { legend: { position: 'top' } }, scales: { y: { beginAtZero: true } } }
});

// Placement Pie
new Chart(document.getElementById('placementPie'), {
    type: 'doughnut', data: { labels: ['Placed', 'Unplaced'], datasets: [{ data: [$placedStudents, " . ($totalStudents - $placedStudents) . "],
    backgroundColor: ['#10b981', '#e2e8f0'], borderWidth: 0 }] },
    options: { responsive: true, cutout: '70%', plugins: { legend: { display: false } } }
});

// Trend Chart
const months = " . json_encode(array_column($monthlyPlacements, 'month')) . ";
const counts = " . json_encode(array_column($monthlyPlacements, 'count')) . ";
new Chart(document.getElementById('trendChart'), {
    type: 'line', data: { labels: months, datasets: [{ label: 'Placements', data: counts,
    borderColor: '#6366f1', backgroundColor: 'rgba(99,102,241,0.1)', fill: true, tension: 0.4, pointRadius: 4 }] },
    options: { responsive: true, scales: { y: { beginAtZero: true } } }
});";
?>
<?php require_once ROOT_PATH . '/includes/footer.php'; ?>
