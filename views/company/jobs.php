<?php require_once ROOT_PATH . '/includes/header.php'; ?>
<div class="content-header"><div><h1 class="page-title">Manage Jobs</h1><p class="subtitle">View and manage your job postings</p></div><a href="<?= url('/company/post-job') ?>" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i> Post New Job</a></div>

<?php if (empty($jobs)): ?>
<div class="card"><div class="card-body empty-state"><i class="fas fa-briefcase"></i><h5>No Jobs Posted</h5><p>Create your first job posting to start receiving applications.</p><a href="<?= url('/company/post-job') ?>" class="btn btn-primary">Post a Job</a></div></div>
<?php else: ?>
<div class="card"><div class="card-body p-0"><div class="table-responsive">
    <table class="table mb-0">
        <thead><tr><th>Job Title</th><th>Type</th><th>Salary</th><th>Applications</th><th>Deadline</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
            <?php foreach ($jobs as $job): ?>
            <tr>
                <td><div class="fw-bold"><?= htmlspecialchars($job['title']) ?></div><small class="text-muted"><?= htmlspecialchars($job['location'] ?? '') ?> • <?= ucfirst($job['work_mode'] ?? 'onsite') ?></small></td>
                <td><span class="badge bg-light text-dark"><?= JOB_TYPES[$job['job_type']] ?? ucfirst($job['job_type']) ?></span></td>
                <td class="fw-bold text-success"><?= formatSalaryRange($job['salary_min'], $job['salary_max']) ?></td>
                <td><a href="<?= url('/company/applications/' . $job['id']) ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-users me-1"></i><?= $job['application_count'] ?></a></td>
                <td><small><?= $job['application_deadline'] ? formatDate($job['application_deadline']) : 'Open' ?></small></td>
                <td><span class="badge <?= getStatusBadgeClass($job['status']) ?>"><?= ucfirst($job['status']) ?></span></td>
                <td>
                    <div class="d-flex gap-1">
                        <a href="<?= url('/company/edit-job/' . $job['id']) ?>" class="btn btn-sm btn-icon btn-light" title="Edit"><i class="fas fa-edit"></i></a>
                        <a href="<?= url('/company/applications/' . $job['id']) ?>" class="btn btn-sm btn-icon btn-light" title="Applications"><i class="fas fa-users"></i></a>
                        <a href="<?= url('/company/delete-job/' . $job['id']) ?>" class="btn btn-sm btn-icon btn-danger" data-confirm="Delete this job and all its applications?"><i class="fas fa-trash"></i></a>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div></div></div>
<?php endif; ?>
<?php require_once ROOT_PATH . '/includes/footer.php'; ?>
