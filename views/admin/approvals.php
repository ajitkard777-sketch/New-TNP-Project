<?php require_once ROOT_PATH . '/includes/header.php'; ?>
<div class="content-header"><div><h1 class="page-title">Pending Approvals</h1><p class="subtitle">Review and approve registrations and job postings</p></div></div>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card"><div class="card-header"><h6><i class="fas fa-building me-2 text-info"></i>Pending Companies (<?= count($pendingCompanies) ?>)</h6></div>
        <div class="card-body p-0">
            <?php if (empty($pendingCompanies)): ?><div class="p-4 text-center text-muted"><i class="fas fa-check-circle text-success mb-2 d-block" style="font-size:2rem"></i><small>All companies approved!</small></div>
            <?php else: ?>
            <?php foreach ($pendingCompanies as $c): ?>
            <div class="p-3 border-bottom">
                <div class="d-flex justify-content-between align-items-start">
                    <div><div class="fw-bold"><?= htmlspecialchars($c['company_name']) ?></div><small class="text-muted"><?= htmlspecialchars($c['email']) ?> • <?= htmlspecialchars($c['industry'] ?? '') ?></small><br><small class="text-muted">Contact: <?= htmlspecialchars($c['contact_person'] ?? '') ?> | <?= htmlspecialchars($c['contact_phone'] ?? '') ?></small></div>
                    <div class="d-flex gap-1"><a href="<?= url('/admin/approve-company/' . $c['id']) ?>" class="btn btn-sm btn-success"><i class="fas fa-check me-1"></i>Approve</a><a href="<?= url('/admin/reject-company/' . $c['id']) ?>" class="btn btn-sm btn-danger" data-confirm="Reject this company?"><i class="fas fa-times"></i></a></div>
                </div>
            </div>
            <?php endforeach; ?><?php endif; ?>
        </div></div>
    </div>
    <div class="col-lg-6">
        <div class="card"><div class="card-header"><h6><i class="fas fa-briefcase me-2 text-primary"></i>Pending Jobs (<?= count($pendingJobs) ?>)</h6></div>
        <div class="card-body p-0">
            <?php if (empty($pendingJobs)): ?><div class="p-4 text-center text-muted"><i class="fas fa-check-circle text-success mb-2 d-block" style="font-size:2rem"></i><small>All jobs approved!</small></div>
            <?php else: ?>
            <?php foreach ($pendingJobs as $j): ?>
            <div class="p-3 border-bottom">
                <div class="d-flex justify-content-between align-items-start">
                    <div><div class="fw-bold"><?= htmlspecialchars($j['title']) ?></div><small class="text-primary"><?= htmlspecialchars($j['company_name']) ?></small><br><small class="text-muted"><?= JOB_TYPES[$j['job_type']] ?? ucfirst($j['job_type']) ?> • <?= formatSalaryRange($j['salary_min'], $j['salary_max']) ?></small></div>
                    <div class="d-flex gap-1"><a href="<?= url('/admin/approve-job/' . $j['id']) ?>" class="btn btn-sm btn-success"><i class="fas fa-check me-1"></i>Approve</a><a href="<?= url('/admin/delete-job/' . $j['id']) ?>" class="btn btn-sm btn-danger" data-confirm="Delete?"><i class="fas fa-trash"></i></a></div>
                </div>
            </div>
            <?php endforeach; ?><?php endif; ?>
        </div></div>
    </div>
</div>
<?php require_once ROOT_PATH . '/includes/footer.php'; ?>
