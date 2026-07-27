<?php require_once ROOT_PATH . '/includes/header.php'; ?>

<style>
/* Glassmorphism & Dashboard Styling */
.glass-header-card {
    background: linear-gradient(135deg, rgba(99, 102, 241, 0.08), rgba(168, 85, 247, 0.08));
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border: 1px solid rgba(99, 102, 241, 0.2);
    border-radius: 1.25rem;
}

.circular-dial-box {
    position: relative;
    width: 130px;
    height: 130px;
    border-radius: 50%;
    background: conic-gradient(#6366f1 <?= $insights['readiness_score'] ?>%, #e2e8f0 0);
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 10px 25px -5px rgba(99, 102, 241, 0.3);
}

.circular-dial-inner {
    width: 106px;
    height: 106px;
    border-radius: 50%;
    background: white;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}

.dark-mode .circular-dial-inner {
    background: #0f172a;
}

.timeline-vertical {
    position: relative;
    padding-left: 2rem;
    border-left: 3px solid #e2e8f0;
}

.dark-mode .timeline-vertical {
    border-left-color: #334155;
}

.timeline-step {
    position: relative;
    margin-bottom: 2rem;
}

.timeline-step-node {
    position: absolute;
    left: -2.75rem;
    top: 0;
    width: 38px;
    height: 38px;
    border-radius: 50%;
    background: white;
    border: 3px solid #6366f1;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    box-shadow: 0 4px 10px rgba(99, 102, 241, 0.3);
}

.dark-mode .timeline-step-node {
    background: #1e293b;
}
</style>

<div class="content-header mb-4">
    <div>
        <h1 class="page-title d-flex align-items-center gap-2">
            <i class="fas fa-brain text-primary"></i> AI Skill Gap Analysis
        </h1>
        <p class="subtitle">Identify missing skills, skill match percentage, AI insights, and placement roadmap</p>
    </div>
</div>

<!-- Header Card: Circular Score Dial & AI Insights -->
<div class="card glass-header-card mb-4 border-0 shadow-sm">
    <div class="card-body p-4">
        <div class="row align-items-center g-4">
            <!-- Circular Readiness Score Dial -->
            <div class="col-md-3 text-center border-end-md">
                <div class="d-flex flex-column align-items-center">
                    <div class="circular-dial-box mb-2">
                        <div class="circular-dial-inner">
                            <span class="fw-bold fs-3 text-primary mb-0"><?= number_format($insights['readiness_score'], 1) ?>%</span>
                            <small class="text-muted font-medium" style="font-size:0.65rem;">READINESS</small>
                        </div>
                    </div>
                    <span class="badge bg-primary px-3 py-2 rounded-pill fw-bold">Skill Match Percentage</span>
                </div>
            </div>

            <!-- AI Callout Insights -->
            <div class="col-md-9">
                <h6 class="fw-bold text-uppercase text-muted mb-3" style="font-size:0.75rem; letter-spacing:1px;">
                    <i class="fas fa-sparkles text-warning me-1"></i> Personalized AI Placement Insights
                </h6>
                <div class="row g-3">
                    <?php foreach ($insights['insights'] as $ins): ?>
                    <div class="col-md-4">
                        <div class="p-3 bg-white rounded-3 border h-100 shadow-xs">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <i class="<?= $ins['icon'] ?> fs-5"></i>
                                <span class="fw-bold" style="font-size:0.9rem;"><?= $ins['title'] ?></span>
                            </div>
                            <p class="text-muted mb-0" style="font-size:0.82rem; line-height:1.4;">
                                <?= preg_replace('/\*\*(.*?)\*\*/', '<strong class="text-dark">$1</strong>', htmlspecialchars($ins['text'])) ?>
                            </p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Summary Quick Stats -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-4">
        <div class="p-3 bg-white rounded-3 border text-center shadow-xs">
            <div class="fw-bold fs-4 text-success"><?= count($insights['matched_skills']) ?></div>
            <small class="text-muted text-uppercase fw-semibold" style="font-size:0.75rem;">Matched Skills</small>
        </div>
    </div>
    <div class="col-6 col-md-4">
        <div class="p-3 bg-white rounded-3 border text-center shadow-xs">
            <div class="fw-bold fs-4 text-danger"><?= count($insights['missing_skills']) ?></div>
            <small class="text-muted text-uppercase fw-semibold" style="font-size:0.75rem;">Missing Skills</small>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="p-3 bg-white rounded-3 border text-center shadow-xs">
            <div class="fw-bold fs-4 text-primary"><?= number_format($insights['readiness_score'], 1) ?>%</div>
            <small class="text-muted text-uppercase fw-semibold" style="font-size:0.75rem;">Skill Readiness Score</small>
        </div>
    </div>
</div>

<!-- Interactive Navigation Tabs -->
<ul class="nav nav-pills nav-fill mb-4 p-1 bg-light rounded-3 border" id="skillGapTabs" role="tablist">
    <li class="nav-item">
        <button class="nav-link active fw-bold" id="missing-tab" data-bs-toggle="tab" data-bs-target="#missing-content">
            <i class="fas fa-exclamation-triangle me-1"></i> Missing Skills &amp; Gaps (<?= count($insights['missing_skills']) ?>)
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link fw-bold" id="matched-tab" data-bs-toggle="tab" data-bs-target="#matched-content">
            <i class="fas fa-check-circle me-1"></i> Matched Skills (<?= count($insights['matched_skills']) ?>)
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link fw-bold" id="roadmap-tab" data-bs-toggle="tab" data-bs-target="#roadmap-content">
            <i class="fas fa-map-signs me-1"></i> Personalized Roadmap
        </button>
    </li>
</ul>

<!-- Tab Contents -->
<div class="tab-content" id="skillGapTabContent">

    <!-- TAB 1: Missing Skills Breakdown -->
    <div class="tab-pane fade show active" id="missing-content">
        <div class="card border-0 shadow-sm p-4" style="border-radius:1rem;">
            <h5 class="fw-bold mb-3"><i class="fas fa-exclamation-triangle text-warning me-2"></i>Identified Skill Gaps</h5>
            <?php if (empty($insights['missing_skills'])): ?>
            <div class="text-center py-4 text-muted">
                <i class="fas fa-check-circle text-success fs-1 mb-2 d-block opacity-75"></i>
                <p class="fw-bold">No skill gaps identified! You match all essential job skills for your profile.</p>
            </div>
            <?php else: ?>
            <div class="row g-3">
                <?php foreach ($insights['missing_skills'] as $m): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="p-3 bg-white rounded-3 border h-100 shadow-xs">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h6 class="fw-bold text-danger mb-0"><i class="fas fa-times-circle me-1"></i><?= htmlspecialchars($m['skill_name']) ?></h6>
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle"><?= htmlspecialchars($m['demand_level']) ?> Demand</span>
                        </div>
                        <p class="text-muted small mb-2" style="font-size:0.82rem;"><?= htmlspecialchars($m['importance']) ?></p>
                        <div class="d-flex justify-content-between text-muted" style="font-size:0.75rem;">
                            <span><i class="fas fa-clock me-1"></i>Est. Time: <?= htmlspecialchars($m['learning_time']) ?></span>
                            <span><i class="fas fa-layer-group me-1"></i>Level: <?= htmlspecialchars($m['difficulty']) ?></span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- TAB 2: Matched Skills -->
    <div class="tab-pane fade" id="matched-content">
        <div class="card border-0 shadow-sm p-4" style="border-radius:1rem;">
            <h5 class="fw-bold mb-3"><i class="fas fa-check-circle text-success me-2"></i>Acquired &amp; Matched Skills</h5>
            <?php if (empty($insights['matched_skills'])): ?>
            <div class="text-center py-4 text-muted">
                <i class="fas fa-info-circle fs-1 mb-2 d-block opacity-50"></i>
                <p>No skills added to profile yet. Update your skills in Edit Profile!</p>
            </div>
            <?php else: ?>
            <div class="row g-3">
                <?php foreach ($insights['matched_skills'] as $ms): ?>
                <div class="col-md-4 col-sm-6">
                    <div class="p-3 bg-light rounded-3 border d-flex align-items-center gap-2">
                        <i class="fas fa-check-circle text-success fs-5"></i>
                        <span class="fw-bold"><?= htmlspecialchars(is_array($ms) ? ($ms['skill_name'] ?? $ms['name'] ?? 'Skill') : $ms) ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- TAB 3: Personalized Vertical Learning Roadmap -->
    <div class="tab-pane fade" id="roadmap-content">
        <div class="card border-0 shadow-sm p-4" style="border-radius:1rem;">
            <h5 class="fw-bold mb-4"><i class="fas fa-map-signs text-primary me-2"></i>Personalized Placement Roadmap Timeline</h5>

            <div class="timeline-vertical ms-3">
                <?php foreach ($roadmap as $step): ?>
                <div class="timeline-step">
                    <div class="timeline-step-node">
                        <?= $step['step_number'] ?>
                    </div>
                    <div class="bg-white p-3 rounded-3 border shadow-xs">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <h6 class="fw-bold mb-0"><i class="<?= htmlspecialchars($step['icon']) ?> me-2"></i><?= htmlspecialchars($step['title']) ?></h6>
                            <span class="badge bg-light text-dark border"><?= htmlspecialchars($step['category']) ?></span>
                        </div>
                        <p class="text-muted small mb-2"><?= htmlspecialchars($step['description']) ?></p>
                        
                        <?php if ($step['progress'] > 0): ?>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-primary" role="progressbar" style="width: <?= $step['progress'] ?>%;"></div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

</div>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>
