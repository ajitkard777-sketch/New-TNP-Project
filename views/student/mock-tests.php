<?php require_once ROOT_PATH . '/includes/header.php'; ?>

<div class="content-header mb-4">
    <div>
        <h1 class="page-title"><i class="fas fa-vial text-primary me-2"></i>Mock Tests &amp; Practice Portal</h1>
        <p class="subtitle">Evaluate your placement readiness with AI-curated practice assessments and interview drills</p>
    </div>
</div>

<!-- Active Mock Test Drives Card -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
        <h6 class="mb-0 fw-bold"><i class="fas fa-list-alt text-primary me-2"></i>Available Placement Practice Modules</h6>
        <span class="badge bg-success-soft text-success"><i class="fas fa-signal me-1"></i>Live Portal</span>
    </div>
    <div class="card-body p-0">
        <?php if (empty($tests)): ?>
        <div class="p-5 text-center text-muted">
            <i class="fas fa-inbox mb-3 fs-1 opacity-50"></i>
            <h5>No Mock Tests Available</h5>
            <p class="small">Check back later for new placement practice modules.</p>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">Assessment Title</th>
                        <th>Category</th>
                        <th>Questions</th>
                        <th>Duration</th>
                        <th>Target Branch</th>
                        <th class="pe-4 text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tests as $t): ?>
                    <tr>
                        <td class="ps-4">
                            <div class="fw-bold text-dark"><?= htmlspecialchars($t['title']) ?></div>
                            <small class="text-muted"><?= htmlspecialchars($t['description'] ?? '') ?></small>
                        </td>
                        <td>
                            <span class="badge bg-primary-soft text-primary px-3 py-2 fw-semibold" style="font-size:0.78rem;">
                                <?= htmlspecialchars($t['category']) ?>
                            </span>
                        </td>
                        <td class="fw-semibold text-secondary"><?= $t['question_count'] ?? $t['total_questions'] ?> Qs</td>
                        <td class="fw-semibold text-secondary"><?= $t['duration_minutes'] ?> Mins</td>
                        <td><small class="text-muted"><i class="fas fa-graduation-cap me-1"></i><?= htmlspecialchars($t['target_branch']) ?></small></td>
                        <td class="pe-4 text-end">
                            <a href="<?= url('/student/mock-test/' . $t['id']) ?>" class="btn btn-sm btn-primary fw-semibold px-3">
                                <i class="fas fa-play me-1"></i> Start Test
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Recent Test Attempts History -->
<?php if (!empty($results)): ?>
<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
        <h6 class="mb-0 fw-bold"><i class="fas fa-history text-info me-2"></i>Your Attempt History &amp; Scorecards</h6>
        <span class="badge bg-info-soft text-info fw-bold"><?= count($results) ?> Attempted</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">Test Name</th>
                        <th>Category</th>
                        <th>Score</th>
                        <th>Accuracy</th>
                        <th>Date Taken</th>
                        <th class="pe-4 text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($results as $r): ?>
                    <tr>
                        <td class="ps-4 fw-bold text-dark"><?= htmlspecialchars($r['test_title']) ?></td>
                        <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($r['category']) ?></span></td>
                        <td>
                            <span class="fw-bold text-primary"><?= $r['correct_answers'] ?> / <?= $r['total_questions'] ?></span>
                        </td>
                        <td>
                            <span class="badge <?= $r['percentage'] >= 60 ? 'bg-success-soft text-success' : 'bg-warning-soft text-dark' ?> fw-bold">
                                <?= $r['percentage'] ?>%
                            </span>
                        </td>
                        <td class="text-muted small"><?= formatDate($r['submitted_at']) ?></td>
                        <td class="pe-4 text-end">
                            <a href="<?= url('/student/mock-test-result/' . $r['id']) ?>" class="btn btn-sm btn-outline-info fw-semibold">
                                <i class="fas fa-chart-bar me-1"></i> View Result
                            </a>
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
