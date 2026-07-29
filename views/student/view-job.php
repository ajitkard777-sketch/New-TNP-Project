<?php require_once ROOT_PATH . '/includes/header.php'; ?>

<?php
$score       = (float)($aiMatch['score'] ?? 0);
$level       = $aiMatch['level'] ?? 'Fair Match';
$reasons     = $aiMatch['reasons'] ?? [];
$matchedArr  = array_filter(array_map('trim', explode(',', $aiMatch['matched_skills'] ?? '')));
$missingArr  = array_filter(array_map('trim', explode(',', $aiMatch['missing_skills'] ?? '')));
$scoreColor  = $score >= 75 ? 'success' : ($score >= 55 ? 'primary' : ($score >= 35 ? 'warning' : 'danger'));
$logoUrl     = $job['logo'] ? uploadUrl('company/' . $job['logo']) : asset('images/default-avatar.png');
?>

<!-- ── Navigation Breadcrumb ────────────────────────────────────────── -->
<div class="content-header d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= url('/student/jobs') ?>">Browse Jobs</a></li>
                <li class="breadcrumb-item"><a href="<?= url('/student/ai-jobs') ?>">AI Job Matches</a></li>
                <li class="breadcrumb-item active"><?= htmlspecialchars($job['title']) ?></li>
            </ol>
        </nav>
        <h1 class="page-title"><?= htmlspecialchars($job['title']) ?></h1>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('/student/ai-jobs') ?>" class="btn btn-light border btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Back to AI Matches
        </a>
        <a href="<?= url('/student/jobs') ?>" class="btn btn-outline-primary btn-sm">
            <i class="fas fa-briefcase me-1"></i> Browse All Jobs
        </a>
    </div>
</div>

<div class="row g-4">

    <!-- ══════════════════════════════════════════════════════════════════
         LEFT COLUMN — Job Details & Description
         ══════════════════════════════════════════════════════════════════ -->
    <div class="col-lg-8">

        <!-- ── Main Job Header Card ──────────────────────────────────── -->
        <div class="card shadow-sm mb-4">
            <div class="card-body p-4">
                <div class="d-flex align-items-start gap-3 flex-wrap mb-4">
                    <img src="<?= $logoUrl ?>"
                         onerror="this.src='<?= asset('images/default-avatar.png') ?>'"
                         alt=""
                         style="width:64px;height:64px;border-radius:14px;object-fit:cover;border:1px solid var(--border-color);flex-shrink:0;">
                    <div class="flex-grow-1 min-w-0">
                        <h4 class="fw-bold mb-1"><?= htmlspecialchars($job['title']) ?></h4>
                        <div class="text-primary fw-semibold fs-6 mb-2"><?= htmlspecialchars($job['company_name']) ?></div>
                        
                        <div class="d-flex flex-wrap gap-3 text-muted small">
                            <span><i class="fas fa-map-marker-alt me-1 text-primary"></i><?= htmlspecialchars($job['location'] ?? 'N/A') ?></span>
                            <span><i class="fas fa-clock me-1 text-primary"></i><?= JOB_TYPES[$job['job_type']] ?? ucfirst($job['job_type']) ?></span>
                            <span><i class="fas fa-laptop-house me-1 text-primary"></i><?= ucfirst($job['work_mode'] ?? 'onsite') ?></span>
                            <span><i class="fas fa-users me-1 text-primary"></i><?= $job['openings'] ?> openings</span>
                            <span><i class="fas fa-calendar-alt me-1 text-primary"></i>Posted <?= timeAgo($job['created_at']) ?></span>
                        </div>
                    </div>
                </div>

                <div class="row g-3 p-3 rounded-3 bg-light border mb-4">
                    <div class="col-sm-6 col-md-3 border-end">
                        <div class="text-muted small" style="font-size:0.75rem;text-transform:uppercase;font-weight:600;">Package / Salary</div>
                        <div class="fw-bold text-success" style="font-size:1.05rem;"><?= formatSalaryRange($job['salary_min'], $job['salary_max']) ?></div>
                    </div>
                    <div class="col-sm-6 col-md-3 border-end">
                        <div class="text-muted small" style="font-size:0.75rem;text-transform:uppercase;font-weight:600;">Application Deadline</div>
                        <div class="fw-bold text-dark" style="font-size:0.95rem;"><?= $job['application_deadline'] ? formatDate($job['application_deadline']) : 'Open' ?></div>
                    </div>
                    <div class="col-sm-6 col-md-3 border-end">
                        <div class="text-muted small" style="font-size:0.75rem;text-transform:uppercase;font-weight:600;">Total Applicants</div>
                        <div class="fw-bold text-primary" style="font-size:0.95rem;"><?= (int)($job['total_applications'] ?? 0) ?> candidates</div>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <div class="text-muted small" style="font-size:0.75rem;text-transform:uppercase;font-weight:600;">Work Mode</div>
                        <div class="fw-bold text-dark" style="font-size:0.95rem;"><?= ucfirst($job['work_mode'] ?? 'onsite') ?></div>
                    </div>
                </div>

                <!-- Action Bar -->
                <div class="d-flex align-items-center gap-3">
                    <?php if ($job['has_applied']): ?>
                    <button class="btn btn-success btn-lg disabled px-4"><i class="fas fa-check-circle me-2"></i> Application Submitted</button>
                    <?php else: ?>
                    <a href="<?= url('/student/apply/' . $job['id']) ?>"
                       class="btn btn-primary btn-lg px-4"
                       onclick="return confirm('Confirm application for <?= htmlspecialchars(addslashes($job['title'])) ?> at <?= htmlspecialchars(addslashes($job['company_name'])) ?>?');">
                        <i class="fas fa-paper-plane me-2"></i> Apply for this Position
                    </a>
                    <?php endif; ?>

                    <button class="btn btn-outline-primary btn-lg" onclick="toggleBookmark(<?= $job['id'] ?>)" title="Bookmark Job">
                        <i class="<?= $job['is_bookmarked'] ? 'fas' : 'far' ?> fa-bookmark me-1"></i>
                        <?= $job['is_bookmarked'] ? 'Bookmarked' : 'Save Job' ?>
                    </button>
                </div>
            </div>
        </div>

        <!-- ── Job Description Card ──────────────────────────────────── -->
        <div class="card shadow-sm mb-4">
            <div class="card-header py-3 px-4 bg-transparent border-0">
                <h5 class="fw-bold mb-0"><i class="fas fa-align-left text-primary me-2"></i>Job Description</h5>
            </div>
            <div class="card-body pt-0 px-4 pb-4">
                <div class="lh-lg text-secondary" style="font-size:0.95rem;white-space:pre-line;">
                    <?= htmlspecialchars($job['description'] ?: 'No detailed description provided.') ?>
                </div>
            </div>
        </div>

        <!-- ── Required Skills & Qualifications Card ────────────────── -->
        <div class="card shadow-sm mb-4">
            <div class="card-header py-3 px-4 bg-transparent border-0">
                <h5 class="fw-bold mb-0"><i class="fas fa-tools text-primary me-2"></i>Required Skills &amp; Technology Stack</h5>
            </div>
            <div class="card-body pt-0 px-4 pb-4">
                <?php if ($job['skills_required']): ?>
                <div class="d-flex flex-wrap gap-2">
                    <?php foreach (array_map('trim', explode(',', $job['skills_required'])) as $sk): ?>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 fs-6" style="border-radius:20px;">
                        <?= htmlspecialchars($sk) ?>
                    </span>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <p class="text-muted mb-0">No specific skills listed.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- ── Eligibility Criteria Card ────────────────────────────── -->
        <div class="card shadow-sm mb-4">
            <div class="card-header py-3 px-4 bg-transparent border-0">
                <h5 class="fw-bold mb-0"><i class="fas fa-graduation-cap text-primary me-2"></i>Eligibility Criteria</h5>
            </div>
            <div class="card-body pt-0 px-4 pb-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="p-3 border rounded-3 bg-light">
                            <div class="text-muted small fw-semibold text-uppercase">Minimum CGPA</div>
                            <div class="fw-bold text-dark mt-1"><?= $job['eligibility_cgpa'] > 0 ? number_format($job['eligibility_cgpa'], 2) . ' / 10.0' : 'No CGPA cut-off' ?></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 border rounded-3 bg-light">
                            <div class="text-muted small fw-semibold text-uppercase">Eligible Branches</div>
                            <div class="fw-bold text-dark mt-1"><?= $job['eligibility_branches'] ? htmlspecialchars($job['eligibility_branches']) : 'Open to All Branches' ?></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 border rounded-3 bg-light">
                            <div class="text-muted small fw-semibold text-uppercase">Max Allowed Active Backlogs</div>
                            <div class="fw-bold text-dark mt-1"><?= $job['eligibility_backlogs'] ?> backlog(s)</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 border rounded-3 bg-light">
                            <div class="text-muted small fw-semibold text-uppercase">Required Experience</div>
                            <div class="fw-bold text-dark mt-1"><?= htmlspecialchars($job['experience_required'] ?: 'Fresher / Entry level') ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- ══════════════════════════════════════════════════════════════════
         RIGHT COLUMN — AI Match Score & Company Overview
         ══════════════════════════════════════════════════════════════════ -->
    <div class="col-lg-4">

        <!-- ── AI Match Score Box ────────────────────────────────────── -->
        <div class="card shadow-sm mb-4 border-0" style="border-top:5px solid var(--bs-<?= $scoreColor ?>) !important; border-radius:12px;">
            <div class="card-header bg-transparent border-0 py-3 px-4">
                <h5 class="fw-bold mb-0"><i class="fas fa-robot text-<?= $scoreColor ?> me-2"></i>AI Profile Match Analysis</h5>
            </div>
            <div class="card-body pt-0 px-4 pb-4">
                
                <!-- Score display -->
                <div class="text-center py-3 my-2 rounded-3" style="background:rgba(var(--bs-<?= $scoreColor ?>-rgb), 0.08);">
                    <div class="display-5 fw-bold text-<?= $scoreColor ?>"><?= round($score) ?>%</div>
                    <div class="badge bg-<?= $scoreColor ?> px-3 py-1 mt-1 fs-6"><?= $level ?></div>
                </div>

                <!-- Progress bar -->
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-1" style="font-size:0.75rem;">
                        <span class="text-muted fw-semibold">Match Score</span>
                        <span class="fw-bold text-<?= $scoreColor ?>"><?= round($score, 1) ?>%</span>
                    </div>
                    <div class="progress" style="height:8px;border-radius:4px;background:rgba(0,0,0,0.06);">
                        <div class="progress-bar bg-<?= $scoreColor ?>" style="width:<?= round($score) ?>%;border-radius:4px;"></div>
                    </div>
                </div>

                <!-- Matching Skills -->
                <?php if (!empty($matchedArr)): ?>
                <div class="mb-3">
                    <div class="text-muted mb-2 small fw-semibold text-uppercase">
                        <i class="fas fa-check-circle text-success me-1"></i>Matching Skills (<?= count($matchedArr) ?>)
                    </div>
                    <div class="d-flex flex-wrap gap-1">
                        <?php foreach ($matchedArr as $sk): ?>
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 small"><?= htmlspecialchars($sk) ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Missing Skills -->
                <?php if (!empty($missingArr)): ?>
                <div class="mb-3">
                    <div class="text-muted mb-2 small fw-semibold text-uppercase">
                        <i class="fas fa-exclamation-triangle text-warning me-1"></i>Missing Skills (<?= count($missingArr) ?>)
                    </div>
                    <div class="d-flex flex-wrap gap-1">
                        <?php foreach ($missingArr as $ms): ?>
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 small"><?= htmlspecialchars($ms) ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- AI Reasons -->
                <?php if (!empty($reasons)): ?>
                <div>
                    <div class="text-muted mb-2 small fw-semibold text-uppercase"><i class="fas fa-list-check me-1"></i>AI Recommendation Factors</div>
                    <div class="d-flex flex-column gap-2">
                        <?php foreach ($reasons as $r): ?>
                        <div class="d-flex align-items-start gap-2 p-2 rounded-2" style="background:<?= $r['status']==='success'?'rgba(16,185,129,0.06)':($r['status']==='danger'?'rgba(239,68,68,0.06)':($r['status']==='warning'?'rgba(245,158,11,0.06)':'rgba(99,102,241,0.06)')) ?>;">
                            <i class="fas fa-<?= $r['status']==='success'?'check-circle text-success':($r['status']==='danger'?'times-circle text-danger':($r['status']==='warning'?'exclamation-triangle text-warning':'info-circle text-info')) ?> mt-1 fa-xs flex-shrink-0"></i>
                            <span style="font-size:0.8rem;"><?= htmlspecialchars($r['text']) ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

            </div>
        </div>

        <!-- ── Company Info Card ─────────────────────────────────────── -->
        <div class="card shadow-sm mb-4">
            <div class="card-header py-3 px-4 bg-transparent border-0">
                <h5 class="fw-bold mb-0"><i class="fas fa-building text-primary me-2"></i>About Company</h5>
            </div>
            <div class="card-body pt-0 px-4 pb-4">
                <div class="fw-bold text-dark mb-1"><?= htmlspecialchars($job['company_name']) ?></div>
                <?php if ($job['industry']): ?>
                <div class="text-muted small mb-2"><i class="fas fa-industry me-1"></i><?= htmlspecialchars($job['industry']) ?></div>
                <?php endif; ?>
                <?php if ($job['company_description']): ?>
                <p class="text-secondary small mb-3 lh-base"><?= htmlspecialchars(truncateText($job['company_description'], 200)) ?></p>
                <?php endif; ?>
                <?php if ($job['website']): ?>
                <a href="<?= htmlspecialchars($job['website']) ?>" target="_blank" class="btn btn-sm btn-outline-secondary w-100">
                    <i class="fas fa-globe me-1"></i> Visit Company Website
                </a>
                <?php endif; ?>
            </div>
        </div>

    </div>

</div>

<script>
function toggleBookmark(jobId) {
    $.post(TPMS.baseUrl + '/student/bookmark/' + jobId, {csrf_token: TPMS.csrfToken}, function(r) {
        if (r.success) {
            TPMS.showToast(r.message, 'success');
            setTimeout(() => location.reload(), 500);
        }
    });
}
</script>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>
