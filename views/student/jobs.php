<?php require_once ROOT_PATH . '/includes/header.php'; ?>

<div class="content-header">
    <div>
        <h1 class="page-title">Browse Jobs</h1>
        <p class="subtitle">Search and apply for jobs matching your skills and profile</p>
    </div>
</div>

<!-- Smart Filters & Search Jobs by Skills -->
<div class="card mb-4 border-0 shadow-sm" style="border-radius: 1rem;">
    <div class="card-body p-4">
        <form method="GET" action="<?= url('/student/jobs') ?>" class="row g-3 align-items-end" id="jobSearchForm">
            
            <!-- Smart Skill Search Bar -->
            <div class="col-lg-4">
                <label class="form-label fw-bold text-dark"><i class="fas fa-magic text-primary me-1"></i> Search jobs by your skills</label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" class="form-control border-start-0 ps-0" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="e.g. Java, React, Python, MySQL...">
                </div>
            </div>

            <!-- Job Type -->
            <div class="col-md-2">
                <label class="form-label font-semibold text-muted" style="font-size: 0.82rem;">Job Type</label>
                <select class="form-select form-select-sm" name="type">
                    <option value="">All Job Types</option>
                    <?php foreach (JOB_TYPES as $k => $v): ?>
                    <option value="<?= $k ?>" <?= ($type ?? '') === $k ? 'selected' : '' ?>><?= $v ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Work Mode / Remote -->
            <div class="col-md-2">
                <label class="form-label font-semibold text-muted" style="font-size: 0.82rem;">Work Mode</label>
                <select class="form-select form-select-sm" name="work_mode">
                    <option value="">All Modes</option>
                    <option value="remote" <?= ($workMode ?? '') === 'remote' ? 'selected' : '' ?>>Remote</option>
                    <option value="hybrid" <?= ($workMode ?? '') === 'hybrid' ? 'selected' : '' ?>>Hybrid</option>
                    <option value="onsite" <?= ($workMode ?? '') === 'onsite' ? 'selected' : '' ?>>On-site</option>
                </select>
            </div>

            <!-- Location -->
            <div class="col-md-2">
                <label class="form-label font-semibold text-muted" style="font-size: 0.82rem;">Location</label>
                <input type="text" class="form-control form-control-sm" name="location" value="<?= htmlspecialchars($location ?? '') ?>" placeholder="City / State...">
            </div>

            <!-- Submit Button -->
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary btn-sm w-100 py-2"><i class="fas fa-search me-1"></i> Search Jobs</button>
            </div>

        </form>

        <!-- Quick Skill Chips from Student Profile -->
        <?php if (!empty($student['skills'])): ?>
        <div class="mt-3 pt-3 border-top d-flex flex-wrap align-items-center gap-2" style="font-size: 0.8rem;">
            <span class="text-muted font-medium me-1"><i class="fas fa-user-tag text-info me-1"></i>Your Skills:</span>
            <?php foreach (array_map('trim', explode(',', $student['skills'])) as $sSkill): ?>
            <?php if (!empty($sSkill)): ?>
            <a href="<?= url('/student/jobs?search=' . urlencode($sSkill)) ?>" class="badge bg-light text-dark border hover-shadow" style="text-decoration:none;">
                + <?= htmlspecialchars($sSkill) ?>
            </a>
            <?php endif; ?>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div class="text-muted font-medium" style="font-size:0.9rem">Showing <strong><?= $totalJobs ?></strong> available jobs</div>
</div>

<?php if (empty($jobs)): ?>
<div class="card p-5 text-center empty-state border-0 shadow-sm">
    <i class="fas fa-briefcase text-muted" style="font-size: 3rem; opacity: 0.5;"></i>
    <h5 class="mt-3 fw-bold">No Matching Jobs Found</h5>
    <p class="text-muted">Try adjusting your skill search keywords or clear filters to see all available opportunities.</p>
    <div>
        <a href="<?= url('/student/jobs') ?>" class="btn btn-outline-secondary btn-sm"><i class="fas fa-redo me-1"></i> Clear Filters</a>
    </div>
</div>
<?php else: ?>
<div class="row g-4">
    <?php foreach ($jobs as $job): ?>
    <?php
        $recScore = (float)($job['recommendation_score'] ?? 0);
        if ($recScore >= 80) $scoreBadge = 'bg-success';
        elseif ($recScore >= 60) $scoreBadge = 'bg-primary';
        elseif ($recScore >= 40) $scoreBadge = 'bg-warning text-dark';
        else $scoreBadge = 'bg-secondary';
        
        $isBookmarked = (int)($job['is_bookmarked'] ?? 0) === 1;
    ?>
    <div class="col-lg-6">
        <div class="card h-100 border-0 shadow-sm hover-shadow animate-fade-in-up" style="border-radius: 1rem; transition: transform 0.2s;">
            <div class="card-body p-4 d-flex flex-direction-column justify-content-between">
                <div>
                    <!-- Header with Company Logo & Save Button -->
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="d-flex gap-3 align-items-center">
                            <img src="<?= $job['logo'] ? uploadUrl('company/' . $job['logo']) : asset('images/default-avatar.png') ?>" alt="" class="job-company-logo rounded-3 border" style="width: 50px; height: 50px; object-fit: contain; background: #fff; padding: 3px;" onerror="this.src='<?= asset('images/default-avatar.png') ?>'">
                            <div>
                                <h5 class="fw-bold mb-1" style="font-size: 1.05rem;"><?= htmlspecialchars($job['title']) ?></h5>
                                <div class="text-muted font-medium" style="font-size: 0.85rem;"><i class="fas fa-building me-1"></i><?= htmlspecialchars($job['company_name']) ?></div>
                            </div>
                        </div>

                        <!-- Save to Playlist / Bookmark Button -->
                        <button class="btn btn-sm <?= $isBookmarked ? 'btn-primary saved-active' : 'btn-outline-secondary' ?> px-3 rounded-pill" onclick="toggleSaveJob(<?= $job['id'] ?>, this)" title="<?= $isBookmarked ? 'Saved to Playlist' : 'Save to Playlist' ?>">
                            <i class="<?= $isBookmarked ? 'fas fa-bookmark text-white' : 'far fa-bookmark' ?> me-1"></i>
                            <span class="save-btn-text" style="font-size:0.8rem;"><?= $isBookmarked ? 'Saved' : 'Save to Playlist' ?></span>
                        </button>
                    </div>

                    <!-- Meta Tags -->
                    <div class="d-flex flex-wrap gap-2 mb-3 text-muted" style="font-size: 0.8rem;">
                        <span class="badge bg-light text-dark border"><i class="fas fa-map-marker-alt text-danger me-1"></i><?= htmlspecialchars($job['location'] ?: 'N/A') ?></span>
                        <span class="badge bg-light text-dark border"><i class="fas fa-clock text-info me-1"></i><?= JOB_TYPES[$job['job_type']] ?? ucfirst($job['job_type']) ?></span>
                        <span class="badge bg-light text-dark border"><i class="fas fa-laptop-house text-primary me-1"></i><?= ucfirst($job['work_mode'] ?: 'onsite') ?></span>
                        <span class="badge bg-light text-dark border"><i class="fas fa-users text-success me-1"></i><?= (int)$job['openings'] ?> openings</span>
                    </div>

                    <!-- Recommendation Score & Level Badge -->
                    <div class="mb-3 d-flex align-items-center gap-2">
                        <span class="badge <?= $scoreBadge ?> p-2 px-3 fw-bold" style="font-size:0.8rem;">
                            <i class="fas fa-sparkles me-1"></i><?= round($recScore) ?>% Match - <?= htmlspecialchars($job['recommendation_level'] ?? 'Good Match') ?>
                        </span>
                    </div>

                    <!-- Matched & Missing Skill Chips -->
                    <?php if (!empty($job['matched_skills'])): ?>
                    <div class="mb-2">
                        <div class="text-muted mb-1 font-semibold" style="font-size: 0.72rem;">MATCHED SKILLS:</div>
                        <div class="d-flex flex-wrap gap-1">
                            <?php foreach ($job['matched_skills'] as $mSkill): ?>
                            <span class="badge bg-success-subtle text-success border border-success me-1 mb-1" style="font-size: 0.75rem;"><i class="fas fa-check me-1"></i><?= htmlspecialchars($mSkill) ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($job['missing_skills'])): ?>
                    <div class="mb-3">
                        <div class="text-muted mb-1 font-semibold" style="font-size: 0.72rem;">MISSING SKILLS:</div>
                        <div class="d-flex flex-wrap gap-1">
                            <?php foreach (array_slice($job['missing_skills'], 0, 4) as $missSkill): ?>
                            <span class="badge bg-warning-subtle text-warning border border-warning me-1 mb-1" style="font-size: 0.75rem;"><i class="fas fa-exclamation-triangle me-1"></i><?= htmlspecialchars($missSkill) ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Footer Salary & Apply Action -->
                <div class="pt-3 border-top d-flex justify-content-between align-items-center mt-3">
                    <div>
                        <div class="fw-bold text-success" style="font-size: 1rem;"><?= formatSalaryRange($job['salary_min'], $job['salary_max']) ?></div>
                        <div class="text-muted" style="font-size: 0.75rem;"><?= $job['application_deadline'] ? 'Deadline: ' . formatDate($job['application_deadline']) : 'Open' ?></div>
                    </div>

                    <div>
                        <?php if ($job['has_applied']): ?>
                        <span class="badge bg-success p-2 px-3" style="font-size: 0.85rem;"><i class="fas fa-check me-1"></i> Applied</span>
                        <?php else: ?>
                        <?php
                            $eligible = true;
                            $reason = '';
                            if ($job['eligibility_cgpa'] > 0 && ($student['cgpa'] ?? 0) < $job['eligibility_cgpa']) { $eligible = false; $reason = 'CGPA ' . $job['eligibility_cgpa'] . '+ required'; }
                            if ($job['eligibility_branches'] && !in_array($student['branch'] ?? '', array_map('trim', explode(',', $job['eligibility_branches'])))) { $eligible = false; $reason = 'Branch not eligible'; }
                        ?>
                        <?php if ($eligible): ?>
                        <a href="<?= url('/student/apply/' . $job['id']) ?>" class="btn btn-primary btn-sm px-3 py-2" data-confirm="Apply for <?= htmlspecialchars($job['title']) ?>?"><i class="fas fa-paper-plane me-1"></i> Apply Now</a>
                        <?php else: ?>
                        <span class="badge bg-danger p-2" data-bs-toggle="tooltip" title="<?= $reason ?>"><i class="fas fa-times me-1"></i> Not Eligible</span>
                        <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<div class="mt-4"><?= renderPagination($pagination, url('/student/jobs')) ?></div>
<?php endif; ?>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>
