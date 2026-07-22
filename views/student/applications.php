<?php require_once ROOT_PATH . '/includes/header.php'; ?>
<div class="content-header"><div><h1 class="page-title">My Applications</h1><p class="subtitle">Track all your job applications</p></div></div>

<?php if (empty($applications)): ?>
<div class="card"><div class="card-body empty-state"><i class="fas fa-paper-plane"></i><h5>No Applications Yet</h5><p>Start applying to jobs from the Jobs section.</p><a href="<?= url('/student/jobs') ?>" class="btn btn-primary">Browse Jobs</a></div></div>
<?php else: ?>
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead><tr><th>Job</th><th>Company</th><th>Type</th><th>Salary</th><th>Status</th><th>Applied On</th><th>Action</th></tr></thead>
                <tbody>
                    <?php foreach ($applications as $app): ?>
                    <tr>
                        <td>
                            <div class="fw-bold"><?= htmlspecialchars($app['job_title']) ?></div>
                            <small class="text-muted"><i class="fas fa-map-marker-alt me-1"></i><?= htmlspecialchars($app['location'] ?? 'N/A') ?></small>
                        </td>
                        <td>
                            <div class="user-cell">
                                <img src="<?= $app['logo'] ? uploadUrl('company/' . $app['logo']) : asset('images/default-avatar.png') ?>" alt="" class="user-avatar" onerror="this.src='<?= asset('images/default-avatar.png') ?>'">
                                <span><?= htmlspecialchars($app['company_name']) ?></span>
                            </div>
                        </td>
                        <td><span class="badge bg-light text-dark"><?= JOB_TYPES[$app['job_type']] ?? ucfirst($app['job_type']) ?></span></td>
                        <td class="fw-bold text-success"><?= formatSalaryRange($app['salary_min'], $app['salary_max']) ?></td>
                        <td><span class="badge <?= getStatusBadgeClass($app['status']) ?>"><?= ucfirst($app['status']) ?></span></td>
                        <td><small><?= formatDate($app['applied_at']) ?></small></td>
                        <td>
                            <?php if ($app['status'] === 'applied'): ?>
                            <a href="<?= url('/student/withdraw/' . $app['id']) ?>" class="btn btn-sm btn-outline-danger" data-confirm="Withdraw application?"><i class="fas fa-times"></i></a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Application Stats -->
<div class="row g-4 mt-2">
    <?php
    $statusCounts = [];
    foreach ($applications as $a) { $statusCounts[$a['status']] = ($statusCounts[$a['status']] ?? 0) + 1; }
    $statColors = ['applied' => 'primary', 'shortlisted' => 'warning', 'interview' => 'info', 'selected' => 'success', 'rejected' => 'danger', 'withdrawn' => 'secondary'];
    foreach ($statColors as $status => $color): ?>
    <div class="col-md-2 col-sm-4">
        <div class="text-center p-3 rounded" style="background: var(--gray-50)">
            <div class="fw-bold fs-4 text-<?= $color ?>"><?= $statusCounts[$status] ?? 0 ?></div>
            <small class="text-muted"><?= ucfirst($status) ?></small>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>
<?php require_once ROOT_PATH . '/includes/footer.php'; ?>
