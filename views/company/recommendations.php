<?php require_once ROOT_PATH . '/includes/header.php'; ?>

<div class="content-header d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
    <div>
        <h1 class="page-title"><i class="fas fa-robot text-primary me-2"></i>AI Student Recommendations</h1>
        <p class="subtitle">Top matched students for your active job postings, ranked by AI match score</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('/company/recommendations?refresh=1') ?>" class="btn btn-outline-primary btn-sm" id="refreshBtn">
            <i class="fas fa-sync-alt me-1"></i> Refresh Scores
        </a>
        <a href="<?= url('/company/jobs') ?>" class="btn btn-light border btn-sm">
            <i class="fas fa-briefcase me-1"></i> Manage Jobs
        </a>
    </div>
</div>


<!-- ── Score Color Legend ─────────────────────────────────────────── -->
<div class="d-flex flex-wrap align-items-center gap-3 mb-4 px-1">
    <span class="small fw-semibold text-muted">Score Legend:</span>
    <?php foreach (['success'=>['Excellent','≥75%'], 'primary'=>['Good','≥55%'], 'warning'=>['Fair','≥35%'], 'danger'=>['Low','<35%']] as $c=>[$lbl,$range]): ?>
    <span class="d-flex align-items-center gap-1">
        <span style="width:12px;height:12px;border-radius:50%;display:inline-block;background:var(--bs-<?= $c ?>);"></span>
        <span class="small"><?= $lbl ?> <span class="text-muted">(<?= $range ?>)</span></span>
    </span>
    <?php endforeach; ?>
</div>

<?php if (empty($recommendations)): ?>
<!-- Empty State -->
<div class="card shadow-sm">
    <div class="card-body text-center py-5">
        <div style="width:80px;height:80px;border-radius:50%;background:rgba(99,102,241,0.1);display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
            <i class="fas fa-robot text-primary fa-2x"></i>
        </div>
        <h5 class="fw-bold mb-2">No Recommendations Yet</h5>
        <p class="text-muted mb-3">Post an active job or click "Refresh Scores" to generate AI-based student recommendations.</p>
        <div class="d-flex justify-content-center gap-2">
            <a href="<?= url('/company/post-job') ?>" class="btn btn-primary btn-sm">
                <i class="fas fa-plus-circle me-1"></i> Post a Job
            </a>
            <a href="<?= url('/company/recommendations?refresh=1') ?>" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-sync-alt me-1"></i> Generate Recommendations
            </a>
        </div>
    </div>
</div>

<?php else: ?>

<?php foreach ($recommendations as $group): ?>
<?php $job = $group['job']; $students = $group['students']; ?>

<!-- ── Job Group Card ─────────────────────────────────────────────── -->
<div class="card shadow-sm mb-4">
    <!-- Job header -->
    <div class="card-header d-flex align-items-center justify-content-between py-3 flex-wrap gap-2"
         style="background:linear-gradient(135deg,rgba(37,99,235,0.06),rgba(99,102,241,0.04));border-bottom:1px solid var(--border-color);">
        <div class="d-flex align-items-center gap-3">
            <div style="width:40px;height:40px;border-radius:10px;background:rgba(37,99,235,0.1);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <i class="fas fa-briefcase text-primary"></i>
            </div>
            <div>
                <h6 class="fw-bold mb-0"><?= htmlspecialchars($job['title']) ?></h6>
                <div class="text-muted small">
                    <?= count($students) ?> top candidate<?= count($students) > 1 ? 's' : '' ?> found
                    &nbsp;·&nbsp;
                    <a href="<?= url('/company/applications/' . $job['id']) ?>" class="text-primary text-decoration-none">
                        View all applicants <i class="fas fa-arrow-right ms-1 fa-xs"></i>
                    </a>
                </div>
            </div>
        </div>
        <a href="<?= url('/company/applications/' . $job['id']) ?>" class="btn btn-sm btn-outline-primary">
            <i class="fas fa-users me-1"></i> Applicants
        </a>
    </div>

    <!-- Students grid -->
    <div class="card-body">
        <div class="row g-3">
            <?php foreach ($students as $s): ?>
            <?php
            $studentId   = (int)($s['student_id'] ?? $s['id'] ?? 0);
            $chatUserId  = (int)($s['user_id'] ?? 0);
            $score       = (float)($s['recommendation_score'] ?? 0);
            $level       = $s['recommendation_level'] ?? 'Fair Match';
            $reasons     = json_decode($s['reasons_json'] ?? '[]', true) ?: [];
            $matchedArr  = array_filter(array_map('trim', explode(',', $s['matched_skills'] ?? '')));
            $missingArr  = array_filter(array_map('trim', explode(',', $s['missing_skills'] ?? '')));
            $avatarUrl   = !empty($s['profile_photo']) ? uploadUrl('profile_photos/' . $s['profile_photo']) : asset('images/default-avatar.png');
            $scoreColor  = $score >= 75 ? 'success' : ($score >= 55 ? 'primary' : ($score >= 35 ? 'warning' : 'danger'));
            $levelIcon   = $score >= 75 ? 'fire' : ($score >= 55 ? 'thumbs-up' : ($score >= 35 ? 'minus-circle' : 'arrow-down'));
            ?>
            <div class="col-xl-6">
                <div class="border rounded-3 p-3 h-100 d-flex flex-column ai-reco-card"
                     style="border-color: var(--border-color) !important;">

                    <!-- Top row: avatar + name + score -->
                    <div class="d-flex align-items-start gap-3 mb-3">
                        <img src="<?= $avatarUrl ?>"
                             onerror="this.src='<?= asset('images/default-avatar.png') ?>'"
                             alt=""
                             style="width:50px;height:50px;border-radius:50%;object-fit:cover;border:2px solid var(--border-color);flex-shrink:0;">
                        <div class="flex-grow-1 min-w-0">
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <h6 class="fw-bold mb-0" style="font-size:0.92rem;">
                                    <?= htmlspecialchars($s['first_name'] . ' ' . $s['last_name']) ?>
                                </h6>
                                <span class="badge bg-<?= $scoreColor ?>-subtle text-<?= $scoreColor ?> border border-<?= $scoreColor ?>-subtle px-2 py-1" style="font-size:0.68rem;">
                                    <i class="fas fa-<?= $levelIcon ?> me-1"></i><?= $level ?>
                                </span>
                            </div>
                            <div class="text-muted small mt-1">
                                <?= htmlspecialchars($s['branch'] ?? '—') ?>
                                &nbsp;·&nbsp;
                                <span class="fw-semibold text-primary">CGPA <?= $s['cgpa'] ? number_format($s['cgpa'],2) : '—' ?></span>
                                &nbsp;·&nbsp;<?= $s['passing_year'] ?? '—' ?>
                            </div>
                        </div>
                        <!-- Score ring -->
                        <div class="flex-shrink-0 text-center">
                            <div style="position:relative;width:52px;height:52px;">
                                <svg width="52" height="52" viewBox="0 0 52 52" style="transform:rotate(-90deg);">
                                    <circle cx="26" cy="26" r="22" fill="none" stroke="var(--border-color)" stroke-width="4"/>
                                    <circle cx="26" cy="26" r="22" fill="none"
                                            stroke="var(--bs-<?= $scoreColor ?>)"
                                            stroke-width="4"
                                            stroke-linecap="round"
                                            stroke-dasharray="<?= round(138.16 * $score / 100, 1) ?> 138.16"/>
                                </svg>
                                <div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;font-size:0.72rem;font-weight:700;color:var(--bs-<?= $scoreColor ?>);">
                                    <?= round($score) ?>%
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Score progress bar -->
                    <div class="mb-2">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-muted" style="font-size:0.72rem;text-transform:uppercase;letter-spacing:.5px;font-weight:600;">AI Match Score</span>
                            <span class="fw-bold text-<?= $scoreColor ?>" style="font-size:0.82rem;"><?= round($score, 1) ?>%</span>
                        </div>
                        <div class="progress" style="height:6px;border-radius:4px;background:rgba(0,0,0,0.08);">
                            <div class="progress-bar bg-<?= $scoreColor ?>"
                                 style="width:<?= round($score, 1) ?>%;border-radius:4px;transition:width 1s ease;">
                            </div>
                        </div>
                    </div>

                    <!-- Matched skills -->
                    <?php if (!empty($matchedArr)): ?>
                    <div class="mb-2">
                        <div class="text-muted mb-1" style="font-size:0.72rem;font-weight:600;text-transform:uppercase;letter-spacing:.4px;">
                            <i class="fas fa-check-circle text-success me-1"></i>Matching Skills
                        </div>
                        <div class="d-flex flex-wrap gap-1">
                            <?php foreach (array_slice($matchedArr, 0, 5) as $sk): ?>
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2" style="font-size:0.7rem;"><?= htmlspecialchars($sk) ?></span>
                            <?php endforeach; ?>
                            <?php if (count($matchedArr) > 5): ?>
                            <span class="badge bg-light text-muted border" style="font-size:0.7rem;">+<?= count($matchedArr) - 5 ?> more</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Missing skills -->
                    <?php if (!empty($missingArr)): ?>
                    <div class="mb-2">
                        <div class="text-muted mb-1" style="font-size:0.72rem;font-weight:600;text-transform:uppercase;letter-spacing:.4px;">
                            <i class="fas fa-times-circle text-danger me-1"></i>Missing Skills
                        </div>
                        <div class="d-flex flex-wrap gap-1">
                            <?php foreach (array_slice($missingArr, 0, 4) as $ms): ?>
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2" style="font-size:0.7rem;"><?= htmlspecialchars($ms) ?></span>
                            <?php endforeach; ?>
                            <?php if (count($missingArr) > 4): ?>
                            <span class="badge bg-light text-muted border" style="font-size:0.7rem;">+<?= count($missingArr) - 4 ?> missing</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- AI Reasons collapsible -->
                    <?php if (!empty($reasons)): ?>
                    <div class="mb-2">
                        <button class="btn btn-xs btn-light border d-flex align-items-center gap-1 w-100"
                                style="font-size:0.72rem;padding:3px 8px;"
                                type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#reasons-<?= $studentId ?>-<?= (int)$job['id'] ?>">
                            <i class="fas fa-robot text-primary fa-xs"></i>
                            AI Recommendation Reasons
                            <i class="fas fa-chevron-down ms-auto fa-xs"></i>
                        </button>
                        <div class="collapse mt-2" id="reasons-<?= $studentId ?>-<?= (int)$job['id'] ?>">
                            <div class="d-flex flex-column gap-1">
                                <?php foreach ($reasons as $r): ?>
                                <div class="d-flex align-items-start gap-2 px-2 py-1 rounded-2" style="background:<?= $r['status']==='success'?'rgba(16,185,129,0.06)':($r['status']==='danger'?'rgba(239,68,68,0.06)':($r['status']==='warning'?'rgba(245,158,11,0.06)':'rgba(99,102,241,0.06)')) ?>;">
                                    <i class="fas fa-<?= $r['status']==='success'?'check-circle text-success':($r['status']==='danger'?'times-circle text-danger':($r['status']==='warning'?'exclamation-triangle text-warning':'info-circle text-info')) ?> mt-1 fa-xs flex-shrink-0"></i>
                                    <span style="font-size:0.75rem;"><?= htmlspecialchars($r['text']) ?></span>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Action buttons -->
                    <div class="mt-auto pt-2 d-flex gap-2 flex-wrap">
                        <?php if ($studentId > 0): ?>
                        <a href="<?= url('/company/view-applicant/' . $studentId) ?>"
                           class="btn btn-primary btn-sm flex-grow-1" style="font-size:0.78rem;">
                            <i class="fas fa-user me-1"></i> View Profile
                        </a>
                        <?php else: ?>
                        <button class="btn btn-secondary btn-sm flex-grow-1 disabled" style="font-size:0.78rem;" disabled>
                            <i class="fas fa-user me-1"></i> View Profile
                        </button>
                        <?php endif; ?>

                        <!-- Action Dropdown -->
                        <div class="dropdown">
                            <button class="btn btn-sm btn-light border dropdown-toggle fw-semibold" style="font-size:0.78rem;" data-bs-toggle="dropdown" data-bs-popper-config='{"strategy":"fixed"}'>
                                Action
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-lg" style="border-radius:10px;font-size:0.82rem;min-width:190px;">
                                <li><a class="dropdown-item py-2" href="<?= url('/company/view-applicant/' . $studentId) ?>"><i class="fas fa-user text-primary me-2"></i>View Profile</a></li>
                                <?php if (!empty($s['resume_path']) && $studentId > 0): ?>
                                <li><a class="dropdown-item py-2" href="<?= url('/company/serve-resume/' . $studentId) ?>" target="_blank"><i class="fas fa-file-pdf text-danger me-2"></i>View Resume</a></li>
                                <li><a class="dropdown-item py-2" href="<?= url('/company/serve-resume/' . $studentId . '?download=1') ?>"><i class="fas fa-download text-success me-2"></i>Download Resume</a></li>
                                <?php endif; ?>
                                <?php if ($chatUserId > 0): ?>
                                <li><a class="dropdown-item py-2" href="<?= url('/chat?user_id=' . $chatUserId) ?>"><i class="fas fa-comment-dots text-info me-2"></i>Send Message</a></li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endforeach; ?>
<?php endif; ?>

<style>
.ai-reco-card:hover {
    box-shadow: 0 4px 16px rgba(37,99,235,0.1) !important;
}
</style>

<script>

// Show spinner on refresh click
document.getElementById('refreshBtn')?.addEventListener('click', function () {
    this.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Refreshing...';
    this.classList.add('disabled');
});
</script>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>
