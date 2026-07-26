<?php require_once ROOT_PATH . '/includes/header.php'; ?>
<div class="content-header mb-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
    <div>
        <h1 class="page-title mb-1"><i class="fas fa-briefcase text-primary me-2"></i>Manage Jobs</h1>
        <p class="subtitle mb-0">View, manage, edit job postings and chat directly with applicants</p>
    </div>
    <a href="<?= url('/company/post-job') ?>" class="btn btn-primary btn-sm fw-semibold shadow-sm">
        <i class="fas fa-plus me-1"></i> Post New Job
    </a>
</div>

<?php if (empty($jobs)): ?>
<div class="card border-0 shadow-sm">
    <div class="card-body text-center p-5">
        <i class="fas fa-briefcase text-muted mb-3" style="font-size:3.5rem; opacity:0.4;"></i>
        <h5 class="fw-bold text-dark mb-1">No Jobs Posted Yet</h5>
        <p class="text-muted small mb-3">Create your first job posting to start receiving applications from students.</p>
        <a href="<?= url('/company/post-job') ?>" class="btn btn-primary btn-sm fw-semibold">Post a Job</a>
    </div>
</div>
<?php else: ?>
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
        <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-list-ul text-primary me-2"></i>Job Postings Overview</h6>
        <span class="badge bg-primary-soft text-primary font-mono fw-bold"><?= count($jobs) ?> Total Posted</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Job Details</th>
                        <th>Job Type</th>
                        <th>Salary Package</th>
                        <th>Applications</th>
                        <th>Deadline</th>
                        <th>Status</th>
                        <th class="text-end pe-3">Actions &amp; Chatbox</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($jobs as $job): ?>
                    <tr>
                        <td class="ps-3">
                            <div class="fw-bold text-dark mb-0"><?= htmlspecialchars($job['title']) ?></div>
                            <small class="text-muted">
                                <i class="fas fa-map-marker-alt me-1 text-primary"></i><?= htmlspecialchars($job['location'] ?? 'Flexible') ?> &bull; <?= ucfirst($job['work_mode'] ?? 'onsite') ?>
                            </small>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border">
                                <?= JOB_TYPES[$job['job_type']] ?? ucfirst($job['job_type']) ?>
                            </span>
                        </td>
                        <td class="fw-bold text-success">
                            <?= formatSalaryRange($job['salary_min'], $job['salary_max']) ?>
                        </td>
                        <td>
                            <a href="<?= url('/company/applications/' . $job['id']) ?>" class="btn btn-sm btn-outline-primary fw-semibold">
                                <i class="fas fa-users me-1"></i><?= $job['application_count'] ?> Applications
                            </a>
                        </td>
                        <td>
                            <small class="text-muted">
                                <i class="far fa-calendar-alt me-1"></i><?= $job['application_deadline'] ? formatDate($job['application_deadline']) : 'Open' ?>
                            </small>
                        </td>
                        <td>
                            <span class="badge <?= getStatusBadgeClass($job['status']) ?>">
                                <?= ucfirst($job['status']) ?>
                            </span>
                        </td>
                        <td class="text-end pe-3">
                            <div class="d-inline-flex gap-1 align-items-center">
                                <a href="<?= url('/company/applications/' . $job['id']) ?>" 
                                   class="btn btn-sm btn-outline-primary fw-semibold px-2 py-1" 
                                   title="Open Applications & Applicant Chatbox">
                                    <i class="fas fa-comments me-1"></i> Chatbox
                                </a>
                                <a href="<?= url('/company/edit-job/' . $job['id']) ?>" 
                                   class="btn btn-sm btn-light border px-2 py-1" 
                                   title="Edit Job">
                                    <i class="fas fa-edit text-secondary"></i>
                                </a>
                                <a href="<?= url('/company/delete-job/' . $job['id']) ?>" 
                                   class="btn btn-sm btn-light border text-danger px-2 py-1" 
                                   data-confirm="Are you sure you want to delete this job posting?" 
                                   title="Delete Job">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>
