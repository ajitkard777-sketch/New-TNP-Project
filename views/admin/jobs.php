<?php require_once ROOT_PATH . '/includes/header.php'; ?>
<div class="content-header"><div><h1 class="page-title">Manage Jobs</h1><p class="subtitle"><?= count($jobs) ?> total jobs</p></div></div>

<div class="card mb-4"><div class="card-body"><form method="GET" class="row g-3 align-items-end">
    <div class="col-md-5"><input type="text" class="form-control" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Job title, company..."></div>
    <div class="col-md-4"><select class="form-select" name="status"><option value="">All Status</option><option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Pending</option><option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Active</option><option value="closed" <?= $status === 'closed' ? 'selected' : '' ?>>Closed</option></select></div>
    <div class="col-md-3"><button type="submit" class="btn btn-primary w-100"><i class="fas fa-search me-1"></i>Filter</button></div>
</form></div></div>

<div class="card"><div class="card-body p-0"><div class="table-responsive">
    <table class="table mb-0">
        <thead><tr><th>Job</th><th>Company</th><th>Type</th><th>Salary</th><th>Apps</th><th>Deadline</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
            <?php foreach ($jobs as $j): ?>
            <tr>
                <td><div class="fw-bold" style="font-size:0.9rem"><?= htmlspecialchars($j['title']) ?></div><small class="text-muted"><?= htmlspecialchars($j['location'] ?? '') ?></small></td>
                <td><small><?= htmlspecialchars($j['company_name']) ?></small></td>
                <td><span class="badge bg-light text-dark"><?= JOB_TYPES[$j['job_type']] ?? ucfirst($j['job_type']) ?></span></td>
                <td class="fw-bold text-success"><?= formatSalaryRange($j['salary_min'], $j['salary_max']) ?></td>
                <td><span class="badge bg-primary"><?= $j['app_count'] ?></span></td>
                <td><small><?= $j['application_deadline'] ? formatDate($j['application_deadline']) : 'Open' ?></small></td>
                <td><span class="badge <?= getStatusBadgeClass($j['status']) ?>"><?= ucfirst($j['status']) ?></span></td>
                <td>
                    <div class="d-flex gap-1">
                        <?php if ($j['status'] === 'pending'): ?><a href="<?= url('/admin/approve-job/' . $j['id']) ?>" class="btn btn-sm btn-success" title="Approve"><i class="fas fa-check"></i></a><?php endif; ?>
                        <?php if ($j['status'] === 'active'): ?><a href="<?= url('/admin/close-job/' . $j['id']) ?>" class="btn btn-sm btn-warning" title="Close"><i class="fas fa-ban"></i></a><?php endif; ?>
                        <a href="<?= url('/admin/delete-job/' . $j['id']) ?>" class="btn btn-sm btn-danger" data-confirm="Delete this job?"><i class="fas fa-trash"></i></a>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div></div></div>
<?php require_once ROOT_PATH . '/includes/footer.php'; ?>
