<?php require_once ROOT_PATH . '/includes/header.php'; ?>

<div class="content-header d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
    <div>
        <h1 class="page-title"><i class="fas fa-robot text-primary me-2"></i>AI Job Matches &amp; Recommendations</h1>
        <p class="subtitle">Personalized job recommendations powered by multi-factor profile matching</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('/student/ai-jobs?refresh=1') ?>" class="btn btn-outline-primary btn-sm" id="refreshAiBtn">
            <i class="fas fa-sync-alt me-1"></i> Refresh Matches
        </a>
        <a href="<?= url('/student/jobs') ?>" class="btn btn-light border btn-sm">
            <i class="fas fa-briefcase me-1"></i> All Jobs
        </a>
    </div>
</div>

<!-- ── Filter & Search Toolbar ──────────────────────────────────────── -->
<div class="card shadow-sm mb-4">
    <div class="card-body py-3 px-4">
        <form method="GET" action="<?= url('/student/ai-jobs') ?>" class="row g-3 align-items-end">
            <div class="col-lg-5 col-md-4">
                <label class="form-label small fw-semibold">Search Keywords</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" class="form-control" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Job title, company, skills...">
                </div>
            </div>
            <div class="col-lg-3 col-md-4">
                <label class="form-label small fw-semibold">Job Type</label>
                <select class="form-select form-select-sm" name="type">
                    <option value="">All Types</option>
                    <?php foreach (JOB_TYPES as $k => $v): ?>
                    <option value="<?= $k ?>" <?= $type === $k ? 'selected' : '' ?>><?= $v ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-lg-2 col-md-4">
                <label class="form-label small fw-semibold">Min Score</label>
                <select class="form-select form-select-sm" name="min_score">
                    <option value="0" <?= $minScore == 0 ? 'selected' : '' ?>>All Matches</option>
                    <option value="75" <?= $minScore == 75 ? 'selected' : '' ?>>≥ 75% (Excellent)</option>
                    <option value="55" <?= $minScore == 55 ? 'selected' : '' ?>>≥ 55% (Good+)</option>
                    <option value="35" <?= $minScore == 35 ? 'selected' : '' ?>>≥ 35% (Fair+)</option>
                </select>
            </div>
            <div class="col-lg-2 col-md-12 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm w-100"><i class="fas fa-filter me-1"></i> Filter</button>
                <?php if ($search || $type || $minScore): ?>
                <a href="<?= url('/student/ai-jobs') ?>" class="btn btn-light border btn-sm" title="Clear Filters"><i class="fas fa-times"></i></a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- ── Recommendations Section ────────────────────────────────────── -->
<?php if (empty($recommendations)): ?>
<div class="card shadow-sm">
    <div class="card-body text-center py-5">
        <div style="width:80px;height:80px;border-radius:50%;background:rgba(99,102,241,0.1);display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
            <i class="fas fa-robot text-primary fa-2x"></i>
        </div>
        <h5 class="fw-bold mb-2">No Job Recommendations Available</h5>
        <p class="text-muted mb-3">Try completing more details in your profile or clearing search filters to see recommended jobs.</p>
        <div class="d-flex justify-content-center gap-2">
            <a href="<?= url('/student/profile/edit') ?>" class="btn btn-primary btn-sm">
                <i class="fas fa-user-edit me-1"></i> Update Skills &amp; Profile
            </a>
            <a href="<?= url('/student/jobs') ?>" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-briefcase me-1"></i> Browse All Jobs
            </a>
        </div>
    </div>
</div>
<?php else: ?>

<div class="row g-4">
    <?php foreach ($recommendations as $rj): ?>
    <?php
    $score       = (float)($rj['recommendation_score'] ?? 0);
    $level       = $rj['recommendation_level'] ?? 'Fair Match';
    $reasons     = json_decode($rj['reasons_json'] ?? '[]', true) ?: [];
    $matchedArr  = array_filter(array_map('trim', explode(',', $rj['matched_skills'] ?? '')));
    $missingArr  = array_filter(array_map('trim', explode(',', $rj['missing_skills'] ?? '')));
    $logoUrl     = $rj['logo'] ? uploadUrl('company/' . $rj['logo']) : asset('images/default-avatar.png');
    $scoreColor  = $score >= 75 ? 'success' : ($score >= 55 ? 'primary' : ($score >= 35 ? 'warning' : 'danger'));
    $levelIcon   = $score >= 75 ? 'fire' : ($score >= 55 ? 'thumbs-up' : ($score >= 35 ? 'minus-circle' : 'arrow-down'));
    ?>
    <div class="col-lg-6">
        <div class="card shadow-sm h-100 border-0 ai-job-card"
             style="border-top:4px solid var(--bs-<?= $scoreColor ?>) !important; border-radius:12px;">
            <div class="card-body d-flex flex-column p-4">
                
                <!-- Card Header: Company Logo, Title, Company Name, Score Badge -->
                <div class="d-flex align-items-start justify-content-between gap-3 mb-3">
                    <div class="d-flex align-items-start gap-3 min-w-0">
                        <img src="<?= $logoUrl ?>"
                             onerror="this.src='<?= asset('images/default-avatar.png') ?>'"
                             alt=""
                             style="width:52px;height:52px;border-radius:12px;object-fit:cover;border:1px solid var(--border-color);flex-shrink:0;">
                        <div class="min-w-0">
                            <h5 class="fw-bold mb-1 text-truncate" style="font-size:1.05rem;">
                                <a href="<?= url('/student/view-job/' . $rj['job_id']) ?>" class="text-dark text-decoration-none hover-primary">
                                    <?= htmlspecialchars($rj['title']) ?>
                                </a>
                            </h5>
                            <div class="text-primary fw-medium small mb-1"><?= htmlspecialchars($rj['company_name']) ?></div>
                            <div class="d-flex flex-wrap gap-2 text-muted" style="font-size:0.78rem;">
                                <span><i class="fas fa-map-marker-alt me-1"></i><?= htmlspecialchars($rj['location'] ?? 'N/A') ?></span>
                                <span><i class="fas fa-clock me-1"></i><?= JOB_TYPES[$rj['job_type']] ?? ucfirst($rj['job_type']) ?></span>
                                <span><i class="fas fa-laptop-house me-1"></i><?= ucfirst($rj['work_mode'] ?? 'onsite') ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- AI Score badge -->
                    <div class="text-end flex-shrink-0">
                        <div class="badge bg-<?= $scoreColor ?> px-3 py-2 rounded-pill shadow-sm" style="font-size:0.88rem;font-weight:700;">
                            <i class="fas fa-robot me-1"></i><?= round($score) ?>%
                        </div>
                        <div class="text-<?= $scoreColor ?> fw-bold mt-1" style="font-size:0.72rem;"><?= $level ?></div>
                    </div>
                </div>

                <!-- Match Progress Bar -->
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1" style="font-size:0.73rem;">
                        <span class="text-muted fw-semibold">AI Match Score</span>
                        <span class="fw-bold text-<?= $scoreColor ?>"><?= round($score, 1) ?>%</span>
                    </div>
                    <div class="progress" style="height:6px;border-radius:4px;background:rgba(0,0,0,0.06);">
                        <div class="progress-bar bg-<?= $scoreColor ?>" style="width:<?= round($score) ?>%;border-radius:4px;"></div>
                    </div>
                </div>

                <!-- Matched Skills -->
                <?php if (!empty($matchedArr)): ?>
                <div class="mb-2">
                    <div class="text-muted mb-1" style="font-size:0.72rem;font-weight:600;text-transform:uppercase;letter-spacing:.4px;">
                        <i class="fas fa-check-circle text-success me-1"></i>Matching Skills
                    </div>
                    <div class="d-flex flex-wrap gap-1">
                        <?php foreach (array_slice($matchedArr, 0, 5) as $sk): ?>
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1" style="font-size:0.72rem;"><?= htmlspecialchars($sk) ?></span>
                        <?php endforeach; ?>
                        <?php if (count($matchedArr) > 5): ?>
                        <span class="badge bg-light text-muted border px-2 py-1" style="font-size:0.72rem;">+<?= count($matchedArr) - 5 ?> more</span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Missing Skills -->
                <?php if (!empty($missingArr)): ?>
                <div class="mb-3">
                    <div class="text-muted mb-1" style="font-size:0.72rem;font-weight:600;text-transform:uppercase;letter-spacing:.4px;">
                        <i class="fas fa-exclamation-triangle text-warning me-1"></i>Skills to Acquire
                    </div>
                    <div class="d-flex flex-wrap gap-1">
                        <?php foreach (array_slice($missingArr, 0, 4) as $ms): ?>
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1" style="font-size:0.72rem;"><?= htmlspecialchars($ms) ?></span>
                        <?php endforeach; ?>
                        <?php if (count($missingArr) > 4): ?>
                        <span class="badge bg-light text-muted border px-2 py-1" style="font-size:0.72rem;">+<?= count($missingArr) - 4 ?> missing</span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- AI Reasons Collapsible -->
                <?php if (!empty($reasons)): ?>
                <div class="mb-3">
                    <button class="btn btn-xs btn-light border d-flex align-items-center gap-1 w-100"
                            style="font-size:0.75rem;padding:4px 10px;"
                            type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#reasons-<?= $rj['job_id'] ?>">
                        <i class="fas fa-lightbulb text-warning fa-xs"></i>
                        Why this job matches your profile
                        <i class="fas fa-chevron-down ms-auto fa-xs"></i>
                    </button>
                    <div class="collapse mt-2" id="reasons-<?= $rj['job_id'] ?>">
                        <div class="d-flex flex-column gap-1">
                            <?php foreach ($reasons as $r): ?>
                            <div class="d-flex align-items-start gap-2 px-2 py-1 rounded-2" style="background:<?= $r['status']==='success'?'rgba(16,185,129,0.06)':($r['status']==='danger'?'rgba(239,68,68,0.06)':($r['status']==='warning'?'rgba(245,158,11,0.06)':'rgba(99,102,241,0.06)')) ?>;">
                                <i class="fas fa-<?= $r['status']==='success'?'check-circle text-success':($r['status']==='danger'?'times-circle text-danger':($r['status']==='warning'?'exclamation-triangle text-warning':'info-circle text-info')) ?> mt-1 fa-xs flex-shrink-0"></i>
                                <span style="font-size:0.76rem;"><?= htmlspecialchars($r['text']) ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Footer Action Bar -->
                <div class="mt-auto pt-3 border-top d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div>
                        <div class="fw-bold text-success" style="font-size:0.92rem;">
                            <?= formatSalaryRange($rj['salary_min'], $rj['salary_max']) ?>
                        </div>
                        <div class="text-muted" style="font-size:0.72rem;">
                            <?= $rj['application_deadline'] ? 'Deadline: ' . formatDate($rj['application_deadline']) : 'Open' ?>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="<?= url('/student/view-job/' . $rj['job_id']) ?>" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-eye me-1"></i> Details
                        </a>
                        <?php if ($rj['has_applied']): ?>
                        <span class="badge bg-success py-2 px-3 align-self-center"><i class="fas fa-check me-1"></i>Applied</span>
                        <?php else: ?>
                        <a href="<?= url('/student/apply/' . $rj['job_id']) ?>"
                           class="btn btn-primary btn-sm px-3"
                           onclick="return confirm('Apply for <?= htmlspecialchars(addslashes($rj['title'])) ?> at <?= htmlspecialchars(addslashes($rj['company_name'])) ?>?');">
                            <i class="fas fa-paper-plane me-1"></i> Apply Now
                        </a>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php endif; ?>

<style>
.ai-job-card:hover {
    box-shadow: 0 4px 16px rgba(15,23,42,0.08) !important;
}
.hover-primary:hover {
    color: var(--primary) !important;
}
</style>

<script>
document.getElementById('refreshAiBtn')?.addEventListener('click', function () {
    this.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Refreshing...';
    this.classList.add('disabled');
});
</script>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>
