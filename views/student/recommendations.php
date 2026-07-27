<?php require_once ROOT_PATH . '/includes/header.php'; ?>

<style>
/* Glassmorphism AI Recommendation Dashboard Styles */
.ai-hero-banner {
    background: linear-gradient(135deg, rgba(99, 102, 241, 0.15) 0%, rgba(168, 85, 247, 0.15) 50%, rgba(236, 72, 153, 0.15) 100%);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 1rem;
    padding: 2rem;
    margin-bottom: 2rem;
    position: relative;
    overflow: hidden;
}

.dark-mode .ai-hero-banner {
    border-color: rgba(255, 255, 255, 0.08);
    background: linear-gradient(135deg, rgba(30, 27, 75, 0.6) 0%, rgba(88, 28, 135, 0.5) 50%, rgba(131, 24, 67, 0.5) 100%);
}

.ai-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.35rem 0.85rem;
    border-radius: 2rem;
    background: linear-gradient(135deg, #6366f1, #a855f7);
    color: white;
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
}

.rec-card {
    background: rgba(255, 255, 255, 0.85);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border: 1px solid rgba(226, 232, 240, 0.8);
    border-radius: 1rem;
    padding: 1.5rem;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    height: 100%;
    display: flex;
    flex-direction: column;
    position: relative;
}

.rec-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    border-color: rgba(99, 102, 241, 0.4);
}

.dark-mode .rec-card {
    background: rgba(30, 41, 59, 0.7);
    border-color: rgba(51, 65, 85, 0.6);
}

.dark-mode .rec-card:hover {
    border-color: rgba(168, 85, 247, 0.5);
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5);
}

/* Score Circle Indicator */
.score-circle {
    width: 72px;
    height: 72px;
    border-radius: 50%;
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-weight: 700;
    font-size: 1.15rem;
    background: conic-gradient(var(--score-color, #6366f1) calc(var(--score-deg) * 1deg), rgba(226, 232, 240, 0.5) 0deg);
}

.dark-mode .score-circle {
    background: conic-gradient(var(--score-color, #6366f1) calc(var(--score-deg) * 1deg), rgba(51, 65, 85, 0.5) 0deg);
}

.score-circle-inner {
    width: 58px;
    height: 58px;
    border-radius: 50%;
    background: var(--card-bg, #ffffff);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: var(--text-color, #1e293b);
    line-height: 1;
}

.dark-mode .score-circle-inner {
    background: #1e293b;
    color: #f8fafc;
}

.score-value {
    font-size: 1.1rem;
    font-weight: 800;
}

.score-percent {
    font-size: 0.65rem;
    opacity: 0.7;
}

/* Skill Chips */
.skill-chip {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.25rem 0.65rem;
    border-radius: 0.5rem;
    font-size: 0.75rem;
    font-weight: 500;
}

.skill-chip.matched {
    background: rgba(16, 185, 129, 0.15);
    color: #059669;
    border: 1px solid rgba(16, 185, 129, 0.3);
}

.dark-mode .skill-chip.matched {
    background: rgba(16, 185, 129, 0.2);
    color: #34d399;
}

.skill-chip.missing {
    background: rgba(245, 158, 11, 0.15);
    color: #d97706;
    border: 1px solid rgba(245, 158, 11, 0.3);
}

.dark-mode .skill-chip.missing {
    background: rgba(245, 158, 11, 0.2);
    color: #fbbf24;
}

.ai-explanation-box {
    background: rgba(248, 250, 252, 0.8);
    border: 1px solid rgba(226, 232, 240, 0.8);
    border-radius: 0.75rem;
    padding: 0.85rem 1rem;
    margin-top: 1rem;
    font-size: 0.82rem;
}

.dark-mode .ai-explanation-box {
    background: rgba(15, 23, 42, 0.6);
    border-color: rgba(51, 65, 85, 0.6);
}

.ai-explanation-list {
    list-style: none;
    padding: 0;
    margin: 0.5rem 0 0 0;
}

.ai-explanation-list li {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.3rem;
}

.ai-explanation-list li:last-child {
    margin-bottom: 0;
}

.nav-pills-glass .nav-link {
    border-radius: 0.75rem;
    padding: 0.6rem 1.2rem;
    font-weight: 600;
    color: #64748b;
    transition: all 0.2s;
}

.nav-pills-glass .nav-link.active {
    background: linear-gradient(135deg, #6366f1, #a855f7);
    color: white;
    box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
}

.dark-mode .nav-pills-glass .nav-link {
    color: #94a3b8;
}

.filter-glass-card {
    background: rgba(255, 255, 255, 0.8);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(226, 232, 240, 0.8);
    border-radius: 1rem;
    padding: 1.25rem;
    margin-bottom: 1.5rem;
}

.dark-mode .filter-glass-card {
    background: rgba(30, 41, 59, 0.7);
    border-color: rgba(51, 65, 85, 0.6);
}
</style>

<div class="content-header">
    <div>
        <div class="ai-badge mb-2"><i class="fas fa-sparkles"></i> AI Recommendation Engine</div>
        <h1 class="page-title">Personalized Job Recommendations</h1>
        <p class="subtitle">AI-matched job opportunities tailored to your skills, CGPA, branch, and location preferences</p>
    </div>
    <div>
        <button class="btn btn-outline-primary" onclick="triggerRegenerate()"><i class="fas fa-sync-alt me-1"></i> Refresh Matches</button>
    </div>
</div>

<!-- AI Hero / Profile Summary Banner -->
<div class="ai-hero-banner animate-fade-in-up">
    <div class="row align-items-center g-3">
        <div class="col-lg-8">
            <div class="d-flex align-items-center gap-3">
                <div class="avatar-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 54px; height: 54px; border-radius: 50%; font-size: 1.5rem;">
                    <i class="fas fa-user-astronaut"></i>
                </div>
                <div>
                    <h4 class="mb-1 fw-bold"><?= htmlspecialchars(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? '')) ?></h4>
                    <div class="d-flex flex-wrap gap-2 text-muted" style="font-size: 0.85rem;">
                        <span><i class="fas fa-graduation-cap text-primary me-1"></i><?= htmlspecialchars($student['branch'] ?? 'N/A') ?></span>
                        <span>•</span>
                        <span><i class="fas fa-chart-line text-success me-1"></i>CGPA: <strong><?= number_format((float)($student['cgpa'] ?? 0), 2) ?></strong></span>
                        <span>•</span>
                        <span><i class="fas fa-map-marker-alt text-danger me-1"></i>Pref Location: <strong><?= htmlspecialchars($student['preferred_location'] ?: ($student['city'] ?? 'Any')) ?></strong></span>
                        <span>•</span>
                        <span><i class="fas fa-history me-1 text-info"></i>Backlogs: <strong><?= (int)($student['active_backlogs'] ?? 0) ?></strong></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 text-lg-end">
            <div class="d-inline-flex align-items-center gap-3 bg-white bg-opacity-75 dark-mode-bg-dark p-2 px-3 rounded-3 border">
                <div class="text-center px-2">
                    <div class="fs-4 fw-bold text-primary"><?= count($topRecommendations) ?></div>
                    <div class="text-muted" style="font-size: 0.75rem;">Top Matches</div>
                </div>
                <div class="border-end" style="height: 30px;"></div>
                <div class="text-center px-2">
                    <div class="fs-4 fw-bold text-success"><?= !empty($topRecommendations) ? round($topRecommendations[0]['recommendation_score']) . '%' : 'N/A' ?></div>
                    <div class="text-muted" style="font-size: 0.75rem;">Best Match Score</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Smart Filters Section -->
<div class="filter-glass-card animate-fade-in-up">
    <form method="GET" action="<?= url('/student/recommendations') ?>" id="filterForm">
        <div class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label font-semibold text-muted" style="font-size:0.8rem;">Search Keywords</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" class="form-control" name="search" value="<?= htmlspecialchars($filters['search']) ?>" placeholder="Job title, company, skills...">
                </div>
            </div>
            <div class="col-md-2">
                <label class="form-label font-semibold text-muted" style="font-size:0.8rem;">Min Score</label>
                <select class="form-select form-select-sm" name="min_score">
                    <option value="">All Match Scores</option>
                    <option value="90" <?= $filters['min_score'] == '90' ? 'selected' : '' ?>>90%+ (Excellent)</option>
                    <option value="75" <?= $filters['min_score'] == '75' ? 'selected' : '' ?>>75%+ (Very Good)</option>
                    <option value="60" <?= $filters['min_score'] == '60' ? 'selected' : '' ?>>60%+ (Good)</option>
                    <option value="40" <?= $filters['min_score'] == '40' ? 'selected' : '' ?>>40%+ (Fair)</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label font-semibold text-muted" style="font-size:0.8rem;">Job Type</label>
                <select class="form-select form-select-sm" name="type">
                    <option value="">All Types</option>
                    <?php foreach (JOB_TYPES as $k => $v): ?>
                    <option value="<?= $k ?>" <?= $filters['job_type'] === $k ? 'selected' : '' ?>><?= $v ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label font-semibold text-muted" style="font-size:0.8rem;">Work Mode</label>
                <select class="form-select form-select-sm" name="work_mode">
                    <option value="">All Modes</option>
                    <option value="remote" <?= $filters['work_mode'] === 'remote' ? 'selected' : '' ?>>Remote</option>
                    <option value="hybrid" <?= $filters['work_mode'] === 'hybrid' ? 'selected' : '' ?>>Hybrid</option>
                    <option value="onsite" <?= $filters['work_mode'] === 'onsite' ? 'selected' : '' ?>>On-site</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label font-semibold text-muted" style="font-size:0.8rem;">Sort By</label>
                <select class="form-select form-select-sm" name="sort_by">
                    <option value="score" <?= $filters['sort_by'] === 'score' ? 'selected' : '' ?>>Recommendation Score</option>
                    <option value="salary" <?= $filters['sort_by'] === 'salary' ? 'selected' : '' ?>>Highest Salary</option>
                    <option value="recent" <?= $filters['sort_by'] === 'recent' ? 'selected' : '' ?>>Recently Posted</option>
                </select>
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn btn-primary btn-sm w-100"><i class="fas fa-filter"></i></button>
            </div>
        </div>
    </form>
</div>

<!-- Tabs for Recommendation Categories -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <ul class="nav nav-pills nav-pills-glass gap-2" id="recTabs" role="tablist">
        <li class="nav-item">
            <button class="nav-link active" id="top-tab" data-bs-toggle="tab" data-bs-target="#top-recs" type="button"><i class="fas fa-star text-warning me-1"></i> Top Recommended (<?= count($topRecommendations) ?>)</button>
        </li>
        <li class="nav-item">
            <button class="nav-link" id="recent-tab" data-bs-toggle="tab" data-bs-target="#recent-recs" type="button"><i class="fas fa-clock text-info me-1"></i> Recently Added (<?= count($recentJobs) ?>)</button>
        </li>
        <li class="nav-item">
            <button class="nav-link" id="salary-tab" data-bs-toggle="tab" data-bs-target="#salary-recs" type="button"><i class="fas fa-money-bill-wave text-success me-1"></i> Highest Salary (<?= count($highestSalaryJobs) ?>)</button>
        </li>
        <li class="nav-item">
            <button class="nav-link" id="saved-tab" data-bs-toggle="tab" data-bs-target="#saved-recs" type="button"><i class="fas fa-bookmark text-primary me-1"></i> Saved (<?= count($savedRecommendations) ?>)</button>
        </li>
    </ul>
</div>

<!-- Tab Contents -->
<div class="tab-content" id="recTabsContent">
    
    <!-- TOP RECOMMENDED JOBS TAB -->
    <div class="tab-pane fade show active" id="top-recs" role="tabpanel">
        <?php if (empty($topRecommendations)): ?>
            <div class="card p-5 text-center empty-state">
                <i class="fas fa-robot text-primary" style="font-size: 3rem; opacity: 0.5;"></i>
                <h5 class="mt-3 fw-bold">No Top Matching Jobs Found</h5>
                <p class="text-muted">Try updating your skills on your profile or relaxing search filters to see more recommendations.</p>
                <div>
                    <a href="<?= url('/student/profile/edit') ?>" class="btn btn-outline-primary btn-sm"><i class="fas fa-user-edit me-1"></i> Update Profile Skills</a>
                </div>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($topRecommendations as $rec): ?>
                    <?php renderRecommendationCard($rec, $student); ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- RECENTLY ADDED JOBS TAB -->
    <div class="tab-pane fade" id="recent-recs" role="tabpanel">
        <div class="row g-4">
            <?php foreach ($recentJobs as $rec): ?>
                <?php renderRecommendationCard($rec, $student); ?>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- HIGHEST SALARY JOBS TAB -->
    <div class="tab-pane fade" id="salary-recs" role="tabpanel">
        <div class="row g-4">
            <?php foreach ($highestSalaryJobs as $rec): ?>
                <?php renderRecommendationCard($rec, $student); ?>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- SAVED RECOMMENDATIONS TAB -->
    <div class="tab-pane fade" id="saved-recs" role="tabpanel">
        <?php if (empty($savedRecommendations)): ?>
            <div class="card p-5 text-center empty-state">
                <i class="fas fa-bookmark text-muted" style="font-size: 3rem;"></i>
                <h5 class="mt-3 fw-bold">No Saved Recommendations</h5>
                <p class="text-muted">Click the bookmark icon on any job card to save it for quick access later.</p>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($savedRecommendations as $rec): ?>
                    <?php renderRecommendationCard($rec, $student); ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

</div>

<!-- Job Details Modal -->
<div class="modal fade" id="jobDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 1rem;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="modalJobTitle">Job Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" id="modalJobBody">
                <div class="text-center py-4"><i class="fas fa-spinner fa-spin me-2"></i> Loading details...</div>
            </div>
        </div>
    </div>
</div>

<?php
/**
 * Helper template function to render recommendation card UI
 */
function renderRecommendationCard(array $rec, array $student) {
    $score = (float)$rec['recommendation_score'];
    $deg = round(($score / 100) * 360);
    
    // Pick color based on score
    if ($score >= 90) $scoreColor = '#10b981'; // Emerald
    elseif ($score >= 75) $scoreColor = '#6366f1'; // Indigo
    elseif ($score >= 60) $scoreColor = '#3b82f6'; // Blue
    elseif ($score >= 40) $scoreColor = '#f59e0b'; // Amber
    else $scoreColor = '#ef4444'; // Red

    $matchedSkills = $rec['matched_skills_array'] ?? [];
    $missingSkills = $rec['missing_skills_array'] ?? [];
    ?>
    <div class="col-lg-6 col-xl-6">
        <div class="rec-card">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div class="d-flex gap-3 align-items-center">
                    <img src="<?= $rec['logo'] ? uploadUrl('company/' . $rec['logo']) : asset('images/default-avatar.png') ?>" alt="" class="job-company-logo rounded-3" style="width: 52px; height: 52px; object-fit: contain; background: #f8fafc; padding: 4px; border: 1px solid #e2e8f0;" onerror="this.src='<?= asset('images/default-avatar.png') ?>'">
                    <div>
                        <h5 class="fw-bold mb-1" style="font-size: 1.05rem;"><?= htmlspecialchars($rec['title']) ?></h5>
                        <div class="text-muted font-medium" style="font-size: 0.85rem;"><i class="fas fa-building me-1"></i><?= htmlspecialchars($rec['company_name']) ?></div>
                    </div>
                </div>

                <!-- Circular Score Indicator -->
                <div class="score-circle" style="--score-color: <?= $scoreColor ?>; --score-deg: <?= $deg ?>;" title="Recommendation Score: <?= $score ?>%">
                    <div class="score-circle-inner">
                        <span class="score-value" style="color: <?= $scoreColor ?>;"><?= round($score) ?></span>
                        <span class="score-percent">MATCH</span>
                    </div>
                </div>
            </div>

            <!-- Meta details -->
            <div class="d-flex flex-wrap gap-2 mb-3 text-muted" style="font-size: 0.8rem;">
                <span class="badge bg-light text-dark border"><i class="fas fa-map-marker-alt text-danger me-1"></i><?= htmlspecialchars($rec['location'] ?: 'N/A') ?></span>
                <span class="badge bg-light text-dark border"><i class="fas fa-clock text-info me-1"></i><?= JOB_TYPES[$rec['job_type']] ?? ucfirst($rec['job_type']) ?></span>
                <span class="badge bg-light text-dark border"><i class="fas fa-laptop-house text-primary me-1"></i><?= ucfirst($rec['work_mode'] ?: 'onsite') ?></span>
                <span class="badge bg-light text-dark border"><i class="fas fa-users text-success me-1"></i><?= (int)$rec['openings'] ?> openings</span>
            </div>

            <!-- Recommendation Level Badge -->
            <div class="mb-3">
                <span class="badge" style="background: <?= $scoreColor ?>; font-weight: 600;">
                    <i class="fas fa-star me-1"></i><?= htmlspecialchars($rec['recommendation_level']) ?>
                </span>
                <span class="ms-2 text-muted" style="font-size: 0.8rem;">
                    Salary: <strong class="text-success"><?= formatSalaryRange($rec['salary_min'], $rec['salary_max']) ?></strong>
                </span>
            </div>

            <!-- Matched Skills Chips -->
            <?php if (!empty($matchedSkills)): ?>
            <div class="mb-2">
                <div class="text-muted mb-1" style="font-size: 0.75rem; font-weight: 600;">MATCHED SKILLS (<?= count($matchedSkills) ?>):</div>
                <div class="d-flex flex-wrap gap-1">
                    <?php foreach ($matchedSkills as $ms): ?>
                    <span class="skill-chip matched"><i class="fas fa-check-circle"></i><?= htmlspecialchars($ms) ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Missing Skills Suggestion Box -->
            <?php if (!empty($missingSkills)): ?>
            <div class="mb-2">
                <div class="text-muted mb-1" style="font-size: 0.75rem; font-weight: 600;">MISSING SKILLS (GAP SUGGESTION):</div>
                <div class="d-flex flex-wrap gap-1 mb-1">
                    <?php foreach ($missingSkills as $ms): ?>
                    <span class="skill-chip missing"><i class="fas fa-exclamation-triangle"></i><?= htmlspecialchars($ms) ?></span>
                    <?php endforeach; ?>
                </div>
                <div class="text-warning" style="font-size: 0.75rem; font-style: italic;">
                    <i class="fas fa-lightbulb me-1"></i>Improve your profile by learning these skills.
                </div>
            </div>
            <?php endif; ?>

            <!-- AI Explanation Accordion / Collapsible -->
            <div class="ai-explanation-box mb-3">
                <div class="fw-bold text-primary d-flex align-items-center gap-1" style="font-size: 0.8rem;">
                    <i class="fas fa-robot"></i> Recommended because:
                </div>
                <ul class="ai-explanation-list">
                    <?php foreach ($rec['reasons'] as $reason): ?>
                    <li>
                        <?php if ($reason['status'] === 'success'): ?>
                            <i class="fas fa-check-circle text-success"></i>
                        <?php elseif ($reason['status'] === 'warning'): ?>
                            <i class="fas fa-exclamation-circle text-warning"></i>
                        <?php else: ?>
                            <i class="fas fa-times-circle text-danger"></i>
                        <?php endif; ?>
                        <span><?= htmlspecialchars($reason['text']) ?></span>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- Footer Action Buttons -->
            <div class="mt-auto pt-3 border-top d-flex justify-content-between align-items-center">
                <div>
                    <button class="btn btn-icon btn-light btn-sm bookmark-btn" onclick="toggleBookmark(<?= $rec['job_id'] ?>)" title="Bookmark Recommendation">
                        <i class="<?= $rec['is_bookmarked'] ? 'fas text-primary' : 'far' ?> fa-bookmark"></i>
                    </button>
                    <button class="btn btn-outline-secondary btn-sm ms-1" onclick="showJobDetails(<?= htmlspecialchars(json_encode($rec)) ?>)"><i class="fas fa-eye me-1"></i> View Details</button>
                </div>
                <div>
                    <?php if ($rec['has_applied']): ?>
                        <span class="badge bg-success p-2 px-3"><i class="fas fa-check me-1"></i> Applied</span>
                    <?php else: ?>
                        <?php if ($rec['eligibility_cgpa'] > 0 && ($student['cgpa'] ?? 0) < $rec['eligibility_cgpa']): ?>
                            <span class="badge bg-danger p-2" title="CGPA below minimum requirement"><i class="fas fa-times me-1"></i> CGPA Ineligible</span>
                        <?php else: ?>
                            <a href="<?= url('/student/apply/' . $rec['job_id']) ?>" class="btn btn-primary btn-sm px-3" data-confirm="Apply for <?= htmlspecialchars($rec['title']) ?>?"><i class="fas fa-paper-plane me-1"></i> Apply Now</a>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php
}
?>

<script>
function triggerRegenerate() {
    const btn = event.currentTarget;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Recalculating...';
    
    fetch('<?= url('/api/recommendations/generate') ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'csrf_token=<?= $_SESSION['csrf_token'] ?? '' ?>'
    })
    .then(r => r.json())
    .then(data => {
        window.location.reload();
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-sync-alt me-1"></i> Refresh Matches';
        alert('Failed to regenerate recommendations.');
    });
}

function showJobDetails(rec) {
    const modalTitle = document.getElementById('modalJobTitle');
    const modalBody = document.getElementById('modalJobBody');
    modalTitle.innerText = rec.title + ' - ' + rec.company_name;

    let matchedSkillsHtml = rec.matched_skills_array.map(s => `<span class="badge bg-success-subtle text-success border border-success me-1 mb-1"><i class="fas fa-check me-1"></i>${s}</span>`).join('');
    let missingSkillsHtml = rec.missing_skills_array.map(s => `<span class="badge bg-warning-subtle text-warning border border-warning me-1 mb-1"><i class="fas fa-exclamation-triangle me-1"></i>${s}</span>`).join('');

    let html = `
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="p-3 bg-light rounded-3">
                    <div class="text-muted font-medium" style="font-size:0.8rem">RECOMMENDATION MATCH SCORE</div>
                    <div class="fs-2 fw-bold text-primary">${rec.recommendation_score}%</div>
                    <div class="badge bg-primary">${rec.recommendation_level}</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="p-3 bg-light rounded-3">
                    <div class="text-muted font-medium" style="font-size:0.8rem">SALARY PACKAGE</div>
                    <div class="fs-4 fw-bold text-success">${rec.salary_min} - ${rec.salary_max} LPA</div>
                    <div class="text-muted" style="font-size:0.8rem">Location: ${rec.location || 'N/A'} (${rec.work_mode})</div>
                </div>
            </div>
        </div>

        <h6 class="fw-bold"><i class="fas fa-align-left me-1"></i> Job Description</h6>
        <p class="text-muted" style="font-size: 0.9rem; line-height: 1.6;">${rec.description || 'No description provided.'}</p>

        <h6 class="fw-bold mt-4"><i class="fas fa-tasks me-1"></i> Required Skills Analysis</h6>
        <div class="mb-3">
            <div class="text-muted mb-1" style="font-size:0.8rem">Skills You Possess:</div>
            <div>${matchedSkillsHtml || '<span class="text-muted">None matched yet</span>'}</div>
        </div>
        <div class="mb-4">
            <div class="text-muted mb-1" style="font-size:0.8rem">Recommended Skills To Learn:</div>
            <div>${missingSkillsHtml || '<span class="text-success">You have all required skills!</span>'}</div>
        </div>

        <div class="d-flex justify-content-end gap-2 pt-3 border-top">
            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            ${!rec.has_applied ? `<a href="<?= url('/student/apply/') ?>${rec.job_id}" class="btn btn-primary btn-sm"><i class="fas fa-paper-plane me-1"></i> Apply Now</a>` : '<span class="badge bg-success p-2">Already Applied</span>'}
        </div>
    `;

    modalBody.innerHTML = html;
    const modal = new bootstrap.Modal(document.getElementById('jobDetailModal'));
    modal.show();
}
</script>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>
