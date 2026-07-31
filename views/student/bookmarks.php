<?php require_once ROOT_PATH . '/includes/header.php'; ?>

<div class="content-header d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
    <div>
        <h1 class="page-title mb-1"><i class="fas fa-bookmark text-primary me-2"></i>Bookmarked Jobs &amp; Saved Opportunities</h1>
        <p class="subtitle mb-0">Review your saved positions, evaluate eligibility criteria, and apply directly.</p>
    </div>
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <!-- View Toggle (Grid / Table Pill Switcher) -->
        <div class="view-toggle-pill" role="group" id="bookmarkViewToggleGroup">
            <button type="button" class="view-toggle-btn active" id="btnBmGridView" title="Grid Card View">
                <i class="fas fa-th-large me-1"></i> Grid
            </button>
            <button type="button" class="view-toggle-btn" id="btnBmTableView" title="Broad Table View">
                <i class="fas fa-list me-1"></i> Table
            </button>
        </div>
        <a href="<?= url('/student/jobs') ?>" class="btn btn-light btn-sm border fw-semibold px-3">
            <i class="fas fa-search me-1"></i> Browse More Jobs
        </a>
    </div>
</div>

<?php if (empty($bookmarks)): ?>
<div class="card shadow-sm border-0">
    <div class="card-body empty-state text-center py-5">
        <i class="fas fa-bookmark fa-3x text-muted mb-3 opacity-50"></i>
        <h5>No Bookmarked Jobs Saved</h5>
        <p class="text-muted">Save interesting job postings by clicking the bookmark icon on any job card while browsing.</p>
        <a href="<?= url('/student/jobs') ?>" class="btn btn-primary btn-sm px-4 py-2 mt-2">
            <i class="fas fa-briefcase me-1"></i> Browse Jobs &amp; Opportunities
        </a>
    </div>
</div>
<?php else: ?>

<?php
$totalSaved = count($bookmarks);
$appliedCount = count(array_filter($bookmarks, fn($j) => $j['has_applied']));
$eligibleCount = count(array_filter($bookmarks, fn($j) => !$j['has_applied'] && ($j['eligibility_cgpa'] == 0 || ($student['cgpa'] ?? 0) >= $j['eligibility_cgpa'])));
$openCount = count(array_filter($bookmarks, fn($j) => strtolower($j['job_status'] ?? 'active') === 'active'));
?>

<!-- Search & Filter Bar -->
<div class="card shadow-sm border mb-4">
    <div class="card-body p-3">
        <div class="row g-2 align-items-center">
            <div class="col-md-8">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" id="bookmarkSearchInput" class="form-control border-start-0 ps-0" placeholder="Search saved jobs by title, company name, skills, or location...">
                </div>
            </div>
            <div class="col-md-4 text-md-end text-muted small">
                Showing <strong id="visibleBookmarkCount" class="text-primary"><?= count($bookmarks) ?></strong> saved opportunities
            </div>
        </div>
    </div>
</div>

<!-- 1. GRID CARDS VIEW (Equal Height & Full Info) -->
<div class="row g-4 mb-4" id="bookmarksGridView">
    <?php foreach ($bookmarks as $job): 
        $eligible = true;
        $reason = '';
        if ($job['eligibility_cgpa'] > 0 && ($student['cgpa'] ?? 0) < $job['eligibility_cgpa']) {
            $eligible = false;
            $reason = 'Min CGPA ' . number_format($job['eligibility_cgpa'], 2) . ' required (Your CGPA: ' . number_format($student['cgpa'] ?? 0, 2) . ')';
        }
    ?>
    <div class="col-xl-6 col-12 bookmark-card-item">
        <div class="card shadow-sm border rounded-3 bg-card h-100 hover-scale position-relative overflow-hidden">
            <div class="card-body p-4 d-flex flex-column justify-content-between">
                
                <div>
                    <!-- Top Row: Logo, Title, Company & Un-bookmark Button -->
                    <div class="d-flex align-items-start justify-content-between gap-3 mb-3">
                        <div class="d-flex align-items-center gap-3 min-w-0 flex-grow-1">
                            <?php if (!empty($job['logo'])): ?>
                            <img src="<?= uploadUrl('company/' . $job['logo']) ?>" alt="" class="rounded border p-1 flex-shrink-0" style="width: 52px; height: 52px; object-fit: contain; background: #fff;" onerror="this.src='<?= asset('images/default-avatar.png') ?>'">
                            <?php else: ?>
                            <div class="rounded border p-2 flex-shrink-0 bg-primary-soft text-primary d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                                <i class="fas fa-building fs-4"></i>
                            </div>
                            <?php endif; ?>
                            <div class="min-w-0 flex-grow-1">
                                <h5 class="fw-bold mb-1 text-truncate" style="color: var(--text-primary); font-size: 1.05rem;" title="<?= htmlspecialchars($job['title']) ?>">
                                    <?= htmlspecialchars($job['title']) ?>
                                </h5>
                                <div class="text-primary fw-semibold small text-truncate">
                                    <i class="fas fa-building me-1 opacity-75"></i><?= htmlspecialchars($job['company_name']) ?>
                                </div>
                            </div>
                        </div>
                        
                        <button class="btn btn-icon btn-light btn-sm text-danger border flex-shrink-0 shadow-sm" onclick="removeBookmark(<?= $job['id'] ?>)" title="Remove Bookmark">
                            <i class="fas fa-bookmark"></i>
                        </button>
                    </div>

                    <!-- Meta Pill Badges -->
                    <div class="d-flex align-items-center gap-2 flex-wrap mb-3" style="font-size: 0.78rem;">
                        <span class="badge bg-light text-dark border"><i class="fas fa-map-marker-alt text-danger me-1"></i><?= htmlspecialchars($job['location'] ?? 'Onsite') ?></span>
                        <span class="badge bg-light text-dark border"><i class="fas fa-clock text-primary me-1"></i><?= JOB_TYPES[$job['job_type']] ?? ucfirst($job['job_type']) ?></span>
                        <span class="badge bg-light text-dark border"><i class="fas fa-laptop-house text-info me-1"></i><?= ucfirst($job['work_mode'] ?? 'onsite') ?></span>
                        <span class="badge bg-light text-dark border"><i class="fas fa-users text-secondary me-1"></i><?= $job['openings'] ?? 1 ?> Openings</span>
                    </div>

                    <!-- Skills Required Tags -->
                    <?php if (!empty($job['skills_required'])): ?>
                    <div class="d-flex align-items-center gap-1 flex-wrap mb-3">
                        <span class="text-muted small me-1"><i class="fas fa-code text-primary me-1"></i>Skills:</span>
                        <?php foreach (array_slice(explode(',', $job['skills_required']), 0, 5) as $skill): ?>
                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-20 px-2 py-1" style="font-size: 0.73rem; font-weight: 500;"><?= htmlspecialchars(trim($skill)) ?></span>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <!-- Brief Description Snippet -->
                    <?php if (!empty($job['description'])): ?>
                    <p class="text-muted small mb-3" style="font-size: 0.83rem; line-height: 1.5;">
                        <?= htmlspecialchars(mb_substr(strip_tags($job['description']), 0, 140)) ?>...
                    </p>
                    <?php endif; ?>
                </div>

                <!-- Footer Action Row -->
                <div class="mt-3 pt-3 border-top">
                    <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap mb-2">
                        <div>
                            <div class="text-success fw-bold fs-6"><i class="fas fa-money-bill-wave me-1"></i><?= formatSalaryRange($job['salary_min'], $job['salary_max']) ?></div>
                            <div class="text-muted small" style="font-size: 0.76rem;">
                                <i class="far fa-calendar-alt me-1"></i><?= $job['application_deadline'] ? 'Deadline: ' . formatDate($job['application_deadline']) : 'Open' ?>
                            </div>
                        </div>

                        <div class="d-flex align-items-center gap-2">
                            <!-- Full Info Modal Trigger -->
                            <button type="button" class="btn btn-xs btn-outline-secondary fw-semibold" data-bs-toggle="modal" data-bs-target="#jobDetailModal<?= $job['id'] ?>">
                                <i class="fas fa-info-circle me-1"></i> Details
                            </button>

                            <?php if ($job['has_applied']): ?>
                            <span class="badge bg-success px-3 py-2" style="font-size: 0.8rem;"><i class="fas fa-check-circle me-1"></i>Applied</span>
                            <?php else: ?>
                                <?php if ($eligible): ?>
                                <a href="<?= url('/student/apply/' . $job['id']) ?>" class="btn btn-sm btn-primary fw-semibold" data-confirm="Apply for <?= htmlspecialchars($job['title']) ?>?">
                                    <i class="fas fa-paper-plane me-1"></i> Apply Now
                                </a>
                                <?php else: ?>
                                <span class="badge bg-danger px-3 py-2" data-bs-toggle="tooltip" title="<?= htmlspecialchars($reason) ?>" style="font-size: 0.78rem;">
                                    <i class="fas fa-exclamation-circle me-1"></i>Not Eligible
                                </span>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="d-flex align-items-center justify-content-between text-muted small pt-2 border-top border-dashed" style="font-size: 0.74rem;">
                        <span><i class="fas fa-graduation-cap me-1 text-primary"></i>Min CGPA: <strong><?= $job['eligibility_cgpa'] > 0 ? number_format($job['eligibility_cgpa'], 2) : 'No min limit' ?></strong></span>
                        <span><i class="fas fa-clock me-1 opacity-75"></i>Saved <?= timeAgo($job['bookmarked_at']) ?></span>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- 2. BROAD TABLE VIEW (Enterprise Responsive Data Table) -->
<div class="card shadow-sm border d-none" id="bookmarksTableView">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table mb-0 align-middle table-hover" id="bookmarksTable">
                <thead class="table-light">
                    <tr>
                        <th style="padding-left: 20px;">Company &amp; Job Position</th>
                        <th>Location &amp; Mode</th>
                        <th>Salary Package</th>
                        <th>Min CGPA</th>
                        <th>Deadline</th>
                        <th>Status</th>
                        <th style="text-align: right; padding-right: 20px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bookmarks as $job): 
                        $eligible = true;
                        $reason = '';
                        if ($job['eligibility_cgpa'] > 0 && ($student['cgpa'] ?? 0) < $job['eligibility_cgpa']) {
                            $eligible = false;
                            $reason = 'Min CGPA ' . number_format($job['eligibility_cgpa'], 2) . ' required';
                        }
                    ?>
                    <tr class="bookmark-row-item">
                        <td style="padding-left: 20px;">
                            <div class="d-flex align-items-center gap-3">
                                <?php if (!empty($job['logo'])): ?>
                                <img src="<?= uploadUrl('company/' . $job['logo']) ?>" alt="" class="rounded border p-1 flex-shrink-0" style="width: 40px; height: 40px; object-fit: contain;" onerror="this.src='<?= asset('images/default-avatar.png') ?>'">
                                <?php else: ?>
                                <div class="rounded border p-2 flex-shrink-0 bg-light text-primary d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                    <i class="fas fa-building"></i>
                                </div>
                                <?php endif; ?>
                                <div>
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($job['title']) ?></div>
                                    <div class="text-primary small fw-semibold"><?= htmlspecialchars($job['company_name']) ?></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="small fw-semibold text-dark"><i class="fas fa-map-marker-alt text-danger me-1"></i><?= htmlspecialchars($job['location'] ?? 'Onsite') ?></div>
                            <small class="text-muted"><?= JOB_TYPES[$job['job_type']] ?? ucfirst($job['job_type']) ?> • <?= ucfirst($job['work_mode'] ?? 'onsite') ?></small>
                        </td>
                        <td>
                            <span class="fw-bold text-success small"><?= formatSalaryRange($job['salary_min'], $job['salary_max']) ?></span>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border font-monospace"><?= $job['eligibility_cgpa'] > 0 ? number_format($job['eligibility_cgpa'], 2) : 'None' ?></span>
                        </td>
                        <td>
                            <small class="text-muted"><i class="far fa-calendar-alt me-1"></i><?= $job['application_deadline'] ? formatDate($job['application_deadline']) : 'Open' ?></small>
                        </td>
                        <td>
                            <?php if ($job['has_applied']): ?>
                            <span class="badge bg-success" style="font-size: 0.74rem;"><i class="fas fa-check-circle me-1"></i>Applied</span>
                            <?php else: ?>
                                <?php if ($eligible): ?>
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary px-2 py-1" style="font-size: 0.74rem;">Eligible</span>
                                <?php else: ?>
                                <span class="badge bg-danger" title="<?= htmlspecialchars($reason) ?>" style="font-size: 0.74rem;">Not Eligible</span>
                                <?php endif; ?>
                            <?php endif; ?>
                        </td>
                        <td style="text-align: right; padding-right: 20px;">
                            <div class="d-flex align-items-center justify-content-end gap-1">
                                <button type="button" class="btn btn-xs btn-light border" data-bs-toggle="modal" data-bs-target="#jobDetailModal<?= $job['id'] ?>" title="View Details">
                                    <i class="fas fa-info-circle text-primary"></i>
                                </button>
                                <?php if (!$job['has_applied'] && $eligible): ?>
                                <a href="<?= url('/student/apply/' . $job['id']) ?>" class="btn btn-xs btn-primary fw-semibold" data-confirm="Apply for <?= htmlspecialchars($job['title']) ?>?">
                                    Apply
                                </a>
                                <?php endif; ?>
                                <button type="button" class="btn btn-xs btn-outline-danger" onclick="removeBookmark(<?= $job['id'] ?>)" title="Remove Bookmark">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Full Job Info Modals for Every Bookmarked Job -->
<?php foreach ($bookmarks as $job): 
    $eligible = true;
    $reason = '';
    if ($job['eligibility_cgpa'] > 0 && ($student['cgpa'] ?? 0) < $job['eligibility_cgpa']) {
        $eligible = false;
        $reason = 'Min CGPA ' . number_format($job['eligibility_cgpa'], 2) . ' required';
    }
?>
<div class="modal fade" id="jobDetailModal<?= $job['id'] ?>" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-gradient-primary text-white" style="background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%);">
                <h5 class="modal-title text-white">
                    <i class="fas fa-briefcase me-2"></i>Job Details — <?= htmlspecialchars($job['title']) ?>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                
                <div class="d-flex align-items-center gap-3 mb-4 p-3 bg-light rounded border">
                    <?php if (!empty($job['logo'])): ?>
                    <img src="<?= uploadUrl('company/' . $job['logo']) ?>" alt="" class="rounded border p-1 bg-white flex-shrink-0" style="width: 56px; height: 56px; object-fit: contain;" onerror="this.src='<?= asset('images/default-avatar.png') ?>'">
                    <?php else: ?>
                    <div class="rounded border p-2 bg-white text-primary d-flex align-items-center justify-content-center flex-shrink-0" style="width: 56px; height: 56px;">
                        <i class="fas fa-building fs-3"></i>
                    </div>
                    <?php endif; ?>
                    <div>
                        <h4 class="fw-bold mb-1 text-dark"><?= htmlspecialchars($job['title']) ?></h4>
                        <div class="text-primary fw-semibold fs-6">
                            <i class="fas fa-building me-1 opacity-75"></i><?= htmlspecialchars($job['company_name']) ?>
                            <?php if ($job['location']): ?>
                            <span class="text-muted small fw-normal ms-2"><i class="fas fa-map-marker-alt text-danger me-1"></i><?= htmlspecialchars($job['location']) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="p-3 border rounded bg-white text-center">
                            <div class="text-muted small fw-semibold text-uppercase mb-1"><i class="fas fa-money-bill-wave text-success me-1"></i> Salary Package</div>
                            <div class="fw-bold fs-6 text-success"><?= formatSalaryRange($job['salary_min'], $job['salary_max']) ?></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 border rounded bg-white text-center">
                            <div class="text-muted small fw-semibold text-uppercase mb-1"><i class="fas fa-graduation-cap text-primary me-1"></i> Min Eligibility CGPA</div>
                            <div class="fw-bold fs-6 text-dark"><?= $job['eligibility_cgpa'] > 0 ? number_format($job['eligibility_cgpa'], 2) : 'No Min CGPA' ?></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 border rounded bg-white text-center">
                            <div class="text-muted small fw-semibold text-uppercase mb-1"><i class="far fa-calendar-alt text-danger me-1"></i> Application Deadline</div>
                            <div class="fw-bold fs-6 text-dark"><?= $job['application_deadline'] ? formatDate($job['application_deadline']) : 'Open' ?></div>
                        </div>
                    </div>
                </div>

                <?php if (!empty($job['skills_required'])): ?>
                <div class="mb-4">
                    <label class="form-label fw-bold"><i class="fas fa-code text-primary me-1"></i> Required Skills &amp; Technologies</label>
                    <div class="d-flex align-items-center gap-1 flex-wrap">
                        <?php foreach (explode(',', $job['skills_required']) as $skill): ?>
                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary px-3 py-1.5" style="font-size: 0.82rem;"><?= htmlspecialchars(trim($skill)) ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (!empty($job['description'])): ?>
                <div class="mb-4">
                    <label class="form-label fw-bold"><i class="fas fa-align-left text-primary me-1"></i> Job Description &amp; Responsibilities</label>
                    <div class="p-3 bg-light rounded border text-dark" style="font-size: 0.9rem; line-height: 1.6;">
                        <?= nl2br(htmlspecialchars($job['description'])) ?>
                    </div>
                </div>
                <?php endif; ?>

            </div>
            <div class="modal-footer d-flex align-items-center justify-content-between">
                <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeBookmark(<?= $job['id'] ?>)">
                    <i class="fas fa-bookmark me-1"></i> Remove Bookmark
                </button>

                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                    <?php if ($job['has_applied']): ?>
                    <span class="badge bg-success px-3 py-2"><i class="fas fa-check-circle me-1"></i>Applied</span>
                    <?php else: ?>
                        <?php if ($eligible): ?>
                        <a href="<?= url('/student/apply/' . $job['id']) ?>" class="btn btn-primary btn-sm fw-bold" data-confirm="Apply for <?= htmlspecialchars($job['title']) ?>?">
                            <i class="fas fa-paper-plane me-1"></i> Apply Now
                        </a>
                        <?php else: ?>
                        <span class="badge bg-danger px-3 py-2" title="<?= htmlspecialchars($reason) ?>"><i class="fas fa-times-circle me-1"></i>Not Eligible</span>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endforeach; ?>

<?php endif; ?>

<script>
function removeBookmark(jobId) {
    if (!confirm('Remove this job from your bookmarks?')) return;
    $.post(TPMS.baseUrl + '/student/bookmark/' + jobId, {csrf_token: TPMS.csrfToken}, function(r) {
        if (r.success) {
            TPMS.showToast(r.message, 'success');
            setTimeout(() => location.reload(), 400);
        }
    }, 'json');
}

// View Toggle Switcher (Grid vs Table)
const btnBmGrid = document.getElementById('btnBmGridView');
const btnBmTable = document.getElementById('btnBmTableView');
const bmGridView = document.getElementById('bookmarksGridView');
const bmTableView = document.getElementById('bookmarksTableView');

if (btnBmGrid && btnBmTable && bmGridView && bmTableView) {
    btnBmGrid.addEventListener('click', function() {
        btnBmGrid.classList.add('active');
        btnBmTable.classList.remove('active');
        bmGridView.classList.remove('d-none');
        bmTableView.classList.add('d-none');
        localStorage.setItem('tpms_student_bm_view', 'grid');
    });

    btnBmTable.addEventListener('click', function() {
        btnBmTable.classList.add('active');
        btnBmGrid.classList.remove('active');
        bmTableView.classList.remove('d-none');
        bmGridView.classList.add('d-none');
        localStorage.setItem('tpms_student_bm_view', 'table');
    });

    // Restore saved view preference
    const savedBmView = localStorage.getItem('tpms_student_bm_view');
    if (savedBmView === 'table') {
        btnBmTable.click();
    }
}

// Real-time Bookmark Search Filter
const bmSearchInput = document.getElementById('bookmarkSearchInput');
if (bmSearchInput) {
    bmSearchInput.addEventListener('input', function() {
        const term = this.value.toLowerCase().trim();
        let visibleCount = 0;

        // Filter Grid Cards
        document.querySelectorAll('.bookmark-card-item').forEach(card => {
            const text = card.textContent.toLowerCase();
            const show = text.includes(term);
            card.style.display = show ? '' : 'none';
            if (show) visibleCount++;
        });

        // Filter Table Rows
        document.querySelectorAll('.bookmark-row-item').forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(term) ? '' : 'none';
        });

        const countEl = document.getElementById('visibleBookmarkCount');
        if (countEl) countEl.textContent = visibleCount;
    });
}
</script>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>
