<?php require_once ROOT_PATH . '/includes/header.php'; ?>
<div class="content-header"><div><h1 class="page-title">Reports & Analytics</h1><p class="subtitle">Comprehensive placement analytics</p></div></div>

<!-- Key Metrics -->
<div class="row g-4 mb-4">
    <div class="col-md-3"><div class="stat-card gradient-success"><div class="stat-card-icon bg-success-soft"><i class="fas fa-percentage"></i></div><div class="stat-card-value"><?= $placementRate ?>%</div><div class="stat-card-label">Placement Rate</div></div></div>
    <div class="col-md-3"><div class="stat-card gradient-primary"><div class="stat-card-icon bg-primary-soft"><i class="fas fa-user-graduate"></i></div><div class="stat-card-value"><?= $this->studentModel->getTotalCount() ?></div><div class="stat-card-label">Total Students</div></div></div>
    <div class="col-md-3"><div class="stat-card gradient-info"><div class="stat-card-icon bg-info-soft"><i class="fas fa-building"></i></div><div class="stat-card-value"><?= $this->companyModel->getApprovedCount() ?></div><div class="stat-card-label">Active Companies</div></div></div>
    <div class="col-md-3"><div class="stat-card gradient-warning"><div class="stat-card-icon bg-warning-soft"><i class="fas fa-briefcase"></i></div><div class="stat-card-value"><?= $this->jobModel->getActiveCount() ?></div><div class="stat-card-label">Active Jobs</div></div></div>
</div>

<div class="row g-4">
    <!-- Branch-wise Stats -->
    <div class="col-lg-6">
        <div class="card"><div class="card-header"><h6><i class="fas fa-chart-bar me-2 text-primary"></i>Branch-wise Placement Stats</h6></div>
        <div class="card-body p-0"><div class="table-responsive"><table class="table mb-0">
            <thead><tr><th>Branch</th><th>Total</th><th>Placed</th><th>Rate</th><th>Progress</th></tr></thead><tbody>
            <?php foreach ($branchStats as $bs): $rate = $bs['total'] > 0 ? round(($bs['placed'] / $bs['total']) * 100, 1) : 0; ?>
            <tr><td class="fw-medium"><?= htmlspecialchars($bs['branch']) ?></td><td><?= $bs['total'] ?></td><td class="text-success fw-bold"><?= $bs['placed'] ?></td><td><?= $rate ?>%</td>
            <td style="min-width:120px"><div class="progress" style="height:6px"><div class="progress-bar bg-<?= $rate >= 75 ? 'success' : ($rate >= 50 ? 'primary' : ($rate >= 25 ? 'warning' : 'danger')) ?>" style="width:<?= $rate ?>%"></div></div></td></tr>
            <?php endforeach; ?>
        </tbody></table></div></div></div>
    </div>

    <!-- Top Recruiters -->
    <div class="col-lg-6">
        <div class="card"><div class="card-header"><h6><i class="fas fa-trophy me-2 text-warning"></i>Top Recruiters</h6></div>
        <div class="card-body p-0"><div class="table-responsive"><table class="table mb-0">
            <thead><tr><th>#</th><th>Company</th><th>Hires</th></tr></thead><tbody>
            <?php foreach ($topRecruiters as $i => $tr): ?>
            <tr><td><span class="badge bg-<?= $i < 3 ? 'warning' : 'light' ?> <?= $i >= 3 ? 'text-dark' : '' ?>"><?= $i + 1 ?></span></td>
            <td><div class="user-cell"><img src="<?= $tr['logo'] ? uploadUrl('company/' . $tr['logo']) : asset('images/default-avatar.png') ?>" alt="" class="user-avatar" style="border-radius:var(--radius-sm)" onerror="this.src='<?= asset('images/default-avatar.png') ?>'"><span class="fw-medium"><?= htmlspecialchars($tr['company_name']) ?></span></div></td>
            <td><span class="badge bg-success"><?= $tr['hires'] ?></span></td></tr>
            <?php endforeach; ?>
        </tbody></table></div></div></div>
    </div>

    <!-- Company-wise Breakdown -->
    <div class="col-12">
        <div class="card"><div class="card-header"><h6><i class="fas fa-building me-2 text-info"></i>Company-wise Placement Breakdown</h6></div>
        <div class="card-body p-0"><div class="table-responsive"><table class="table mb-0" id="companyReport">
            <thead><tr><th>Company</th><th>Placements</th><th>Avg Package (LPA)</th><th>Max Package (LPA)</th></tr></thead><tbody>
            <?php foreach ($companyWise as $cw): ?>
            <tr><td class="fw-medium"><?= htmlspecialchars($cw['company_name']) ?></td><td><span class="badge bg-primary"><?= $cw['placements'] ?></span></td><td class="text-success fw-bold"><?= number_format($cw['avg_package'], 2) ?></td><td class="text-primary fw-bold"><?= number_format($cw['max_package'], 2) ?></td></tr>
            <?php endforeach; ?>
        </tbody></table></div></div></div>
    </div>

    <!-- Year-wise Stats -->
    <div class="col-lg-6">
        <div class="card"><div class="card-header"><h6><i class="fas fa-calendar me-2 text-primary"></i>Year-wise Stats</h6></div>
        <div class="card-body"><canvas id="yearChart" height="250"></canvas></div></div>
    </div>
    <div class="col-lg-6">
        <div class="card"><div class="card-header"><h6><i class="fas fa-chart-pie me-2 text-primary"></i>Branch Distribution</h6></div>
        <div class="card-body"><canvas id="branchPieChart" height="250"></canvas></div></div>
    </div>
</div>

<?php
$inlineJs = "
new Chart(document.getElementById('yearChart'), {
    type: 'bar', data: { labels: " . json_encode(array_column($yearlyStats, 'year')) . ",
    datasets: [{ label: 'Total', data: " . json_encode(array_column($yearlyStats, 'total')) . ", backgroundColor: 'rgba(99,102,241,0.7)', borderRadius: 6 },
    { label: 'Placed', data: " . json_encode(array_column($yearlyStats, 'placed')) . ", backgroundColor: 'rgba(16,185,129,0.7)', borderRadius: 6 }] },
    options: { responsive: true, scales: { y: { beginAtZero: true } } }
});
new Chart(document.getElementById('branchPieChart'), {
    type: 'pie', data: { labels: " . json_encode(array_column($branchStats, 'branch')) . ",
    datasets: [{ data: " . json_encode(array_column($branchStats, 'total')) . ",
    backgroundColor: ['#6366f1','#10b981','#f59e0b','#f43f5e','#06b6d4','#8b5cf6','#ec4899','#14b8a6'] }] },
    options: { responsive: true }
});
if($.fn.DataTable){ $('#companyReport').DataTable({paging:false,searching:false,info:false,order:[[1,'desc']]}); }
";
?>
<?php require_once ROOT_PATH . '/includes/footer.php'; ?>
