<?php require_once ROOT_PATH . '/includes/header.php'; ?>

<div class="content-header mb-4">
    <div>
        <h1 class="page-title"><i class="fas fa-briefcase text-primary me-2"></i>Browse Jobs</h1>
        <p class="subtitle">Discover verified campus placement opportunities matching your career profile</p>
    </div>
</div>

<!-- Filters Card -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body p-4">
        <form method="GET" action="<?= url('/student/jobs') ?>" class="row g-3 align-items-end">
            <div class="col-lg-4 col-md-6">
                <label class="form-label fw-bold text-secondary small">Search Keywords</label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" class="form-control border-start-0 ps-0" name="search" value="<?= htmlspecialchars($search ?? '') ?>" placeholder="Job title, company name, skills...">
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <label class="form-label fw-bold text-secondary small">Job Type</label>
                <select class="form-select" name="type">
                    <option value="">All Job Types</option>
                    <?php foreach (JOB_TYPES as $k => $v): ?>
                    <option value="<?= $k ?>" <?= ($type ?? '') === $k ? 'selected' : '' ?>><?= $v ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-lg-3 col-md-6">
                <label class="form-label fw-bold text-secondary small">Location</label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="fas fa-map-marker-alt text-muted"></i></span>
                    <input type="text" class="form-control border-start-0 ps-0" name="location" value="<?= htmlspecialchars($location ?? '') ?>" placeholder="City or Remote...">
                </div>
            </div>
            <div class="col-lg-2 col-md-6">
                <button type="submit" class="btn btn-primary w-100 fw-semibold">
                    <i class="fas fa-filter me-1"></i> Apply Filter
                </button>
            </div>
        </form>
    </div>
</div>

<div class="d-flex align-items-center justify-content-between mb-3">
    <div class="text-muted small">Showing <strong><?= $totalJobs ?></strong> job opportunities</div>
</div>

<?php if (empty($jobs)): ?>
<div class="card shadow-sm border-0">
    <div class="card-body text-center p-5">
        <i class="fas fa-briefcase text-muted mb-3" style="font-size:3rem; opacity:0.5;"></i>
        <h5 class="fw-bold">No Matching Jobs Found</h5>
        <p class="text-muted small">Try broadening your search query or reset filters.</p>
        <a href="<?= url('/student/jobs') ?>" class="btn btn-outline-primary btn-sm">Reset Filters</a>
    </div>
</div>
<?php else: ?>
<div class="row g-4">
    <?php foreach ($jobs as $job): ?>
    <?php
        $eligibility = checkStudentJobEligibility($job, $student);
        $isEligible = $eligibility['is_eligible'];
    ?>
    <div class="col-xl-6 col-lg-12">
        <div class="card shadow-sm border-0 h-100 position-relative animate-fade-in-up hover-lift">
            
            <!-- Card Header: Company Logo, Title, Company Name, Save/Bookmark -->
            <div class="card-body p-4">
                <div class="d-flex align-items-start gap-3 mb-3">
                    <img src="<?= $job['logo'] ? uploadUrl('company/' . $job['logo']) : asset('images/default-avatar.png') ?>" 
                         alt="<?= htmlspecialchars($job['company_name']) ?>" 
                         class="rounded border p-1 bg-white shadow-sm flex-shrink-0" 
                         style="width:56px; height:56px; object-fit:cover;"
                         onerror="this.src='<?= asset('images/default-avatar.png') ?>'">
                    
                    <div class="flex-grow-1 min-w-0">
                        <div class="d-flex align-items-center justify-content-between gap-2">
                            <h5 class="fw-bold text-dark mb-0 text-truncate" style="font-size:1.05rem;" title="<?= htmlspecialchars($job['title']) ?>">
                                <?= htmlspecialchars($job['title']) ?>
                            </h5>
                            
                            <!-- Save Job (Bookmark) Button -->
                            <button class="btn btn-icon btn-light btn-sm rounded-circle bookmark-btn" onclick="toggleBookmark(<?= $job['id'] ?>)" title="Save Job">
                                <i class="<?= !empty($job['is_bookmarked']) ? 'fas text-primary' : 'far' ?> fa-bookmark"></i>
                            </button>
                        </div>
                        
                        <div class="fw-semibold text-primary small mb-1">
                            <i class="fas fa-building me-1"></i><?= htmlspecialchars($job['company_name']) ?>
                        </div>

                        <!-- Eligibility Badge (Eligible / Not Eligible) -->
                        <div class="mt-1">
                            <?php if ($isEligible): ?>
                                <span class="badge bg-success-soft text-success fw-bold px-2 py-1" style="font-size:0.75rem;">
                                    <i class="fas fa-check-circle me-1"></i>Eligible
                                </span>
                            <?php else: ?>
                                <span class="badge bg-danger-soft text-danger fw-bold px-2 py-1" style="font-size:0.75rem;">
                                    <i class="fas fa-times-circle me-1"></i>Not Eligible
                                </span>
                            <?php endif; ?>

                            <?php if (isset($job['match_score'])): ?>
                                <span class="badge <?= $job['match_badge_class'] ?? 'bg-primary' ?> px-2 py-1 ms-1" style="font-size:0.75rem;">
                                    <i class="fas fa-robot me-1"></i><?= $job['match_score'] ?>% AI Match
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Meta Details: Location, Package, Work Mode, Openings -->
                <div class="row g-2 mb-3 bg-light p-2 rounded small text-secondary">
                    <div class="col-6">
                        <i class="fas fa-map-marker-alt text-danger me-1"></i>
                        <strong>Location:</strong> <?= htmlspecialchars($job['location'] ?: 'N/A') ?>
                    </div>
                    <div class="col-6">
                        <i class="fas fa-money-bill-wave text-success me-1"></i>
                        <strong>Package:</strong> <?= formatSalaryRange($job['salary_min'], $job['salary_max']) ?>
                    </div>
                    <div class="col-6">
                        <i class="fas fa-briefcase text-primary me-1"></i>
                        <strong>Type:</strong> <?= JOB_TYPES[$job['job_type']] ?? ucfirst($job['job_type']) ?>
                    </div>
                    <div class="col-6">
                        <i class="fas fa-users text-info me-1"></i>
                        <strong>Openings:</strong> <?= $job['openings'] ?? 1 ?>
                    </div>
                </div>

                <!-- Required Skills -->
                <?php if (!empty($job['skills_required'])): ?>
                <div class="mb-3">
                    <div class="small fw-semibold text-muted mb-1">Required Skills:</div>
                    <div class="d-flex flex-wrap gap-1">
                        <?php foreach (array_slice(explode(',', $job['skills_required']), 0, 5) as $skill): ?>
                        <span class="badge bg-light text-dark border font-mono" style="font-size:0.75rem; font-weight:500;">
                            <?= htmlspecialchars(trim($skill)) ?>
                        </span>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Eligibility Criteria Summary & Failure Reasons -->
                <div class="p-2 rounded bg-white border mb-3" style="font-size:0.78rem;">
                    <div class="d-flex justify-content-between text-muted">
                        <span><i class="fas fa-graduation-cap me-1"></i>Min CGPA: <strong><?= $job['eligibility_cgpa'] > 0 ? $job['eligibility_cgpa'] : 'No Min' ?></strong></span>
                        <span><i class="fas fa-code-branch me-1"></i>Branches: <strong><?= htmlspecialchars($job['eligibility_branches'] ?: 'All') ?></strong></span>
                    </div>

                    <?php if (!$isEligible && !empty($eligibility['reasons'])): ?>
                    <div class="mt-2 pt-2 border-top text-danger">
                        <strong>Reason:</strong>
                        <ul class="mb-0 ps-3">
                            <?php foreach ($eligibility['reasons'] as $reason): ?>
                            <li><?= htmlspecialchars($reason) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Action Footer: Chat Icon, Apply Button, Saved Status -->
                <div class="d-flex align-items-center justify-content-between border-top pt-3 mt-auto">
                    <!-- Chat HR Button -->
                    <?php if (!empty($job['company_user_id'])): ?>
                    <a href="<?= url('/student/messages?partner=' . $job['company_user_id']) ?>" 
                       class="btn btn-sm btn-outline-primary fw-semibold" 
                       title="Direct Message Company HR">
                        <i class="fas fa-comments me-1"></i>Chat HR
                    </a>
                    <?php else: ?>
                    <div></div>
                    <?php endif; ?>

                    <div class="d-flex gap-2">
                        <?php if (!empty($job['has_applied'])): ?>
                        <span class="badge bg-success px-3 py-2 fw-semibold" style="font-size:0.85rem;">
                            <i class="fas fa-check-circle me-1"></i>Applied
                        </span>
                        <?php elseif ($isEligible): ?>
                        <a href="<?= url('/student/apply/' . $job['id']) ?>" 
                           class="btn btn-primary btn-sm px-4 fw-semibold shadow-sm"
                           data-confirm="Are you sure you want to submit your application for <?= htmlspecialchars($job['title']) ?>?">
                            <i class="fas fa-paper-plane me-1"></i> Apply Now
                        </a>
                        <?php else: ?>
                        <button class="btn btn-light btn-sm text-muted border" disabled title="Ineligible to apply">
                            <i class="fas fa-ban me-1 text-danger"></i> Ineligible
                        </button>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<div class="mt-4">
    <?= renderPagination($pagination, url('/student/jobs')) ?>
</div>
<?php endif; ?>

<script>
function toggleBookmark(jobId) {
    $.post(TPMS.baseUrl + '/student/bookmark/' + jobId, {csrf_token: TPMS.csrfToken}, function(r) {
        if (r.success) { 
            TPMS.showToast(r.message, 'success'); 
            setTimeout(() => location.reload(), 400); 
        }
    }, 'json');
}
</script>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>
