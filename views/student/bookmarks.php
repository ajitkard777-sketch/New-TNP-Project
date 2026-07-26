<?php require_once ROOT_PATH . '/includes/header.php'; ?>
<div class="content-header">
    <div>
        <h1 class="page-title">Bookmarked Jobs</h1>
        <p class="subtitle">Jobs you've saved for later (<?= count($bookmarks) ?> saved)</p>
    </div>
    <?php if (!empty($bookmarks)): ?>
    <a href="<?= url('/student/jobs') ?>" class="btn btn-outline-primary btn-sm">
        <i class="fas fa-search me-1"></i> Browse More Jobs
    </a>
    <?php endif; ?>
</div>

<?php if (empty($bookmarks)): ?>
<div class="card">
    <div class="card-body empty-state py-5">
        <i class="fas fa-bookmark text-muted" style="font-size:3rem;"></i>
        <h5 class="mt-3">No Bookmarked Jobs</h5>
        <p class="text-muted mb-4">Save interesting job postings by clicking the bookmark icon on any job card.</p>
        <a href="<?= url('/student/jobs') ?>" class="btn btn-primary">
            <i class="fas fa-briefcase me-1"></i> Browse Jobs
        </a>
    </div>
</div>
<?php else: ?>
<div class="row g-4">
    <?php foreach ($bookmarks as $job): ?>
    <div class="col-lg-6">
        <div class="job-card h-100 d-flex flex-column justify-content-between">
            <div>
                <div class="job-card-header">
                    <div class="d-flex gap-3">
                        <img src="<?= $job['logo'] ? uploadUrl('company/' . $job['logo']) : asset('images/default-avatar.png') ?>" alt="" class="job-company-logo" onerror="this.src='<?= asset('images/default-avatar.png') ?>'">
                        <div>
                            <h5 class="job-title"><?= htmlspecialchars($job['title']) ?></h5>
                            <div class="job-company"><?= htmlspecialchars($job['company_name']) ?></div>
                        </div>
                    </div>
                    <button class="btn btn-icon btn-light btn-sm text-danger" onclick="removeBookmark(<?= $job['id'] ?>)" title="Remove Bookmark">
                        <i class="fas fa-bookmark"></i>
                    </button>
                </div>
                
                <div class="job-meta">
                    <span class="job-meta-item"><i class="fas fa-map-marker-alt text-primary"></i><?= htmlspecialchars($job['location'] ?? 'N/A') ?></span>
                    <span class="job-meta-item"><i class="fas fa-clock text-primary"></i><?= JOB_TYPES[$job['job_type']] ?? ucfirst($job['job_type']) ?></span>
                    <span class="job-meta-item"><i class="fas fa-laptop-house text-primary"></i><?= ucfirst($job['work_mode'] ?? 'onsite') ?></span>
                    <span class="job-meta-item"><i class="fas fa-users text-primary"></i><?= $job['openings'] ?? 1 ?> openings</span>
                </div>

                <?php if (!empty($job['skills_required'])): ?>
                <div class="job-tags mt-2">
                    <?php foreach (array_slice(explode(',', $job['skills_required']), 0, 5) as $skill): ?>
                    <span class="job-tag"><?= htmlspecialchars(trim($skill)) ?></span>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <div>
                <div class="job-card-footer mt-3 pt-3 border-top d-flex justify-content-between align-items-center">
                    <div>
                        <div class="job-salary text-success fw-bold"><?= formatSalaryRange($job['salary_min'], $job['salary_max']) ?></div>
                        <div class="job-deadline text-muted" style="font-size:0.78rem;">
                            <?= $job['application_deadline'] ? 'Deadline: ' . formatDate($job['application_deadline']) : 'Open' ?>
                        </div>
                    </div>
                    <div>
                        <?php if ($job['has_applied']): ?>
                        <span class="badge bg-success" style="font-size:0.8rem;"><i class="fas fa-check me-1"></i>Applied</span>
                        <?php else: ?>
                            <?php
                            $eligible = true;
                            $reason = '';
                            if ($job['eligibility_cgpa'] > 0 && ($student['cgpa'] ?? 0) < $job['eligibility_cgpa']) {
                                $eligible = false;
                                $reason = 'CGPA ' . $job['eligibility_cgpa'] . '+ required';
                            }
                            if (!empty($job['eligibility_branches']) && !in_array($student['branch'] ?? '', array_map('trim', explode(',', $job['eligibility_branches'])))) {
                                $eligible = false;
                                $reason = 'Branch not eligible';
                            }
                            ?>
                            <?php if ($eligible): ?>
                            <a href="<?= url('/student/apply/' . $job['id']) ?>" class="btn btn-primary btn-sm" data-confirm="Apply for <?= htmlspecialchars($job['title']) ?>?">
                                <i class="fas fa-paper-plane me-1"></i> Apply Now
                            </a>
                            <?php else: ?>
                            <span class="badge bg-danger" data-bs-toggle="tooltip" title="<?= $reason ?>">
                                <i class="fas fa-times me-1"></i>Not Eligible
                            </span>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="mt-2 text-muted" style="font-size:0.72rem;">
                    <?php if ($job['eligibility_cgpa'] > 0): ?>Min CGPA: <?= $job['eligibility_cgpa'] ?> | <?php endif; ?>
                    <?= !empty($job['eligibility_branches']) ? 'Branches: ' . htmlspecialchars($job['eligibility_branches']) : 'All branches' ?>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<script>
function removeBookmark(jobId) {
    if (!confirm('Remove this job from your bookmarks?')) return;
    $.post(TPMS.baseUrl + '/student/bookmark/' + jobId, {csrf_token: TPMS.csrfToken}, function(r) {
        if (r.success) {
            TPMS.showToast(r.message, 'success');
            setTimeout(() => location.reload(), 400);
        }
    }, 'json');
}
</script>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>
