<?php require_once ROOT_PATH . '/includes/header.php'; ?>
<div class="content-header">
    <div><h1 class="page-title">Browse Jobs</h1><p class="subtitle">Find and apply to jobs matching your profile</p></div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="<?= url('/student/jobs') ?>" class="row g-3 align-items-end">
            <div class="col-md-4"><label class="form-label">Search</label><input type="text" class="form-control" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Job title, company, skills..."></div>
            <div class="col-md-3"><label class="form-label">Job Type</label>
                <select class="form-select" name="type"><option value="">All Types</option>
                    <?php foreach (JOB_TYPES as $k => $v): ?><option value="<?= $k ?>" <?= $type === $k ? 'selected' : '' ?>><?= $v ?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3"><label class="form-label">Location</label><input type="text" class="form-control" name="location" value="<?= htmlspecialchars($location) ?>" placeholder="City..."></div>
            <div class="col-md-2"><button type="submit" class="btn btn-primary w-100"><i class="fas fa-search me-1"></i> Search</button></div>
        </form>
    </div>
</div>

<div class="mb-3 text-muted" style="font-size:0.85rem"><strong><?= $totalJobs ?></strong> jobs found</div>

<?php if (empty($jobs)): ?>
<div class="card"><div class="card-body empty-state"><i class="fas fa-briefcase"></i><h5>No Jobs Found</h5><p>Try adjusting your search filters or check back later.</p></div></div>
<?php else: ?>
<div class="row g-4">
    <?php foreach ($jobs as $job): ?>
    <div class="col-lg-6">
        <div class="job-card animate-fade-in-up">
            <div class="job-card-header">
                <div class="d-flex gap-3">
                    <img src="<?= $job['logo'] ? uploadUrl('company/' . $job['logo']) : asset('images/default-avatar.png') ?>" alt="" class="job-company-logo" onerror="this.src='<?= asset('images/default-avatar.png') ?>'">
                    <div>
                        <h5 class="job-title"><?= htmlspecialchars($job['title']) ?></h5>
                        <div class="job-company"><?= htmlspecialchars($job['company_name']) ?></div>
                    </div>
                </div>
                <button class="btn btn-icon btn-light btn-sm bookmark-btn" onclick="toggleBookmark(<?= $job['id'] ?>)" title="Bookmark">
                    <i class="<?= $job['is_bookmarked'] ? 'fas' : 'far' ?> fa-bookmark <?= $job['is_bookmarked'] ? 'text-primary' : '' ?>"></i>
                </button>
            </div>
            
            <div class="job-meta">
                <span class="job-meta-item"><i class="fas fa-map-marker-alt"></i><?= htmlspecialchars($job['location'] ?? 'N/A') ?></span>
                <span class="job-meta-item"><i class="fas fa-clock"></i><?= JOB_TYPES[$job['job_type']] ?? ucfirst($job['job_type']) ?></span>
                <span class="job-meta-item"><i class="fas fa-laptop-house"></i><?= ucfirst($job['work_mode'] ?? 'onsite') ?></span>
                <span class="job-meta-item"><i class="fas fa-users"></i><?= $job['openings'] ?> openings</span>
            </div>

            <?php if ($job['skills_required']): ?>
            <div class="job-tags">
                <?php foreach (array_slice(explode(',', $job['skills_required']), 0, 5) as $skill): ?>
                <span class="job-tag"><?= htmlspecialchars(trim($skill)) ?></span>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <div class="job-card-footer">
                <div>
                    <span class="job-salary"><?= formatSalaryRange($job['salary_min'], $job['salary_max']) ?></span>
                    <div class="job-deadline"><?= $job['application_deadline'] ? 'Deadline: ' . formatDate($job['application_deadline']) : 'Open' ?></div>
                </div>
                <div>
                    <?php if ($job['has_applied']): ?>
                    <span class="badge bg-success"><i class="fas fa-check me-1"></i>Applied</span>
                    <?php else: ?>
                    <?php
                    // Check eligibility
                    $eligible = true;
                    $reason = '';
                    if ($job['eligibility_cgpa'] > 0 && ($student['cgpa'] ?? 0) < $job['eligibility_cgpa']) { $eligible = false; $reason = 'CGPA ' . $job['eligibility_cgpa'] . '+ required'; }
                    if ($job['eligibility_branches'] && !in_array($student['branch'] ?? '', array_map('trim', explode(',', $job['eligibility_branches'])))) { $eligible = false; $reason = 'Branch not eligible'; }
                    ?>
                    <?php if ($eligible): ?>
                    <a href="<?= url('/student/apply/' . $job['id']) ?>" class="btn btn-primary btn-sm" data-confirm="Apply for <?= htmlspecialchars($job['title']) ?>?"><i class="fas fa-paper-plane me-1"></i> Apply</a>
                    <?php else: ?>
                    <span class="badge bg-danger" data-bs-toggle="tooltip" title="<?= $reason ?>"><i class="fas fa-times me-1"></i>Not Eligible</span>
                    <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Eligibility info -->
            <div class="mt-2 pt-2 border-top" style="font-size:0.72rem;color:var(--text-muted)">
                <?php if ($job['eligibility_cgpa'] > 0): ?>Min CGPA: <?= $job['eligibility_cgpa'] ?> | <?php endif; ?>
                <?= $job['eligibility_branches'] ? 'Branches: ' . htmlspecialchars($job['eligibility_branches']) : 'All branches' ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<div class="mt-4"><?= renderPagination($pagination, url('/student/jobs')) ?></div>
<?php endif; ?>

<script>
function toggleBookmark(jobId) {
    $.post(TPMS.baseUrl + '/student/bookmark/' + jobId, {csrf_token: TPMS.csrfToken}, function(r) {
        if (r.success) { TPMS.showToast(r.message, 'success'); setTimeout(() => location.reload(), 500); }
    }, 'json');
}
</script>
<?php require_once ROOT_PATH . '/includes/footer.php'; ?>
