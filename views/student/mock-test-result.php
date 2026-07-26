<?php require_once ROOT_PATH . '/includes/header.php'; ?>

<div class="content-header mb-4">
    <div>
        <h1 class="page-title"><i class="fas fa-chart-bar text-primary me-2"></i>Assessment Scorecard</h1>
        <p class="subtitle">Comprehensive performance breakdown and question explanation analysis</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('/student/mock-test/' . $result['test_id']) ?>" class="btn btn-outline-primary btn-sm fw-semibold">
            <i class="fas fa-redo me-1"></i> Retake Test
        </a>
        <a href="<?= url('/student/mock-tests') ?>" class="btn btn-primary btn-sm fw-semibold">
            <i class="fas fa-th me-1"></i> All Practice Tests
        </a>
    </div>
</div>

<!-- Score Summary Header Cards -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body p-4">
        <div class="row align-items-center g-4">
            
            <!-- Overall Score Circle -->
            <div class="col-md-3 text-center border-end">
                <div class="text-muted small fw-bold text-uppercase mb-1">Total Score</div>
                <div class="fw-bold fs-1 text-primary mb-1">
                    <?= $result['score'] ?> <span class="fs-5 text-muted">/ <?= $result['total_questions'] ?></span>
                </div>
                <span class="badge <?= $result['percentage'] >= 60 ? 'bg-success-soft text-success' : 'bg-warning-soft text-dark' ?> px-3 py-2 fw-bold fs-6">
                    <?= $result['percentage'] ?>% Score
                </span>
            </div>

            <!-- Stats Metrics -->
            <div class="col-md-9">
                <div class="row g-3 text-center">
                    <div class="col-sm-3 col-6">
                        <div class="p-3 bg-light rounded">
                            <i class="fas fa-check-circle text-success mb-2 fs-4"></i>
                            <div class="small text-muted fw-semibold">Correct</div>
                            <div class="fw-bold fs-5 text-success"><?= $result['correct_answers'] ?></div>
                        </div>
                    </div>
                    <div class="col-sm-3 col-6">
                        <div class="p-3 bg-light rounded">
                            <i class="fas fa-times-circle text-danger mb-2 fs-4"></i>
                            <div class="small text-muted fw-semibold">Wrong</div>
                            <div class="fw-bold fs-5 text-danger"><?= $result['wrong_answers'] ?></div>
                        </div>
                    </div>
                    <div class="col-sm-3 col-6">
                        <div class="p-3 bg-light rounded">
                            <i class="fas fa-minus-circle text-secondary mb-2 fs-4"></i>
                            <div class="small text-muted fw-semibold">Unanswered</div>
                            <div class="fw-bold fs-5 text-secondary"><?= $result['unanswered'] ?></div>
                        </div>
                    </div>
                    <div class="col-sm-3 col-6">
                        <div class="p-3 bg-light rounded">
                            <i class="fas fa-stopwatch text-info mb-2 fs-4"></i>
                            <div class="small text-muted fw-semibold">Time Taken</div>
                            <div class="fw-bold fs-5 text-dark">
                                <?= floor($result['time_taken_seconds'] / 60) ?>m <?= $result['time_taken_seconds'] % 60 ?>s
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Detailed Question Breakdown -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
        <h6 class="mb-0 fw-bold"><i class="fas fa-list-check text-primary me-2"></i>Question-by-Question Detailed Review</h6>
        <span class="text-muted small">Total Questions: <?= count($questions) ?></span>
    </div>
    <div class="card-body p-4">
        <div class="d-flex flex-column gap-4">
            <?php foreach ($questions as $idx => $q): ?>
            <?php
                $userAns = isset($userAnswers[$q['id']]) ? strtolower($userAnswers[$q['id']]) : null;
                $correctAns = strtolower($q['correct_option']);
                $isCorrect = ($userAns === $correctAns);
                $isUnanswered = ($userAns === null || $userAns === '');
            ?>
            <div class="p-3 border rounded <?= $isCorrect ? 'border-success bg-success-soft-light' : ($isUnanswered ? 'bg-light' : 'border-danger bg-danger-soft-light') ?>">
                <div class="d-flex align-items-start justify-content-between gap-3 mb-3">
                    <h6 class="fw-bold text-dark mb-0" style="line-height:1.5;">
                        <span class="text-primary me-2">Q<?= $idx + 1 ?>.</span><?= htmlspecialchars($q['question_text']) ?>
                    </h6>
                    <?php if ($isCorrect): ?>
                        <span class="badge bg-success text-white px-3 py-2 fw-semibold flex-shrink-0">
                            <i class="fas fa-check me-1"></i> Correct (+1)
                        </span>
                    <?php elseif ($isUnanswered): ?>
                        <span class="badge bg-secondary text-white px-3 py-2 fw-semibold flex-shrink-0">
                            <i class="fas fa-minus me-1"></i> Unanswered (0)
                        </span>
                    <?php else: ?>
                        <span class="badge bg-danger text-white px-3 py-2 fw-semibold flex-shrink-0">
                            <i class="fas fa-times me-1"></i> Incorrect (0)
                        </span>
                    <?php endif; ?>
                </div>

                <!-- Options -->
                <div class="row g-2 mb-3">
                    <?php foreach (['a' => $q['option_a'], 'b' => $q['option_b'], 'c' => $q['option_c'], 'd' => $q['option_d']] as $optKey => $optVal): ?>
                    <?php
                        $isThisCorrect = ($optKey === $correctAns);
                        $isThisSelected = ($optKey === $userAns);
                        $optClass = 'border bg-white text-secondary';
                        if ($isThisCorrect) {
                            $optClass = 'border-success bg-success text-white fw-bold';
                        } elseif ($isThisSelected && !$isThisCorrect) {
                            $optClass = 'border-danger bg-danger text-white fw-bold';
                        }
                    ?>
                    <div class="col-md-6">
                        <div class="p-2 rounded small <?= $optClass ?> d-flex align-items-center justify-content-between">
                            <span><strong><?= strtoupper($optKey) ?>.</strong> <?= htmlspecialchars($optVal) ?></span>
                            <?php if ($isThisSelected): ?>
                                <small class="badge bg-white text-dark ms-2">Your Answer</small>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Explanation -->
                <?php if (!empty($q['explanation'])): ?>
                <div class="p-2 rounded bg-white border text-secondary small">
                    <i class="fas fa-lightbulb text-warning me-1"></i><strong>Explanation:</strong> <?= htmlspecialchars($q['explanation']) ?>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>
