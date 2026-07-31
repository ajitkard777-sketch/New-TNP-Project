<?php require_once ROOT_PATH . '/includes/header.php'; ?>

<?php
// ─── Sorting helpers ────────────────────────────────────────────────
$sortBy  = $sortBy  ?? 'date';
$sortDir = $sortDir ?? 'DESC';
$nextDir = ($sortDir === 'DESC') ? 'asc' : 'desc';
$curSort = $sortBy;

/**
 * Build a sort URL preserving all current GET params except page.
 */
function sortUrl(string $col, string $curSort, string $sortDir): string {
    $params = $_GET;
    $params['sort']  = $col;
    $params['order'] = ($curSort === $col && $sortDir === 'DESC') ? 'asc' : 'desc';
    unset($params['page']);
    return url('/admin/training-enrollments') . '?' . http_build_query($params);
}

function sortIcon(string $col, string $curSort, string $sortDir): string {
    if ($curSort !== $col) return '<i class="fas fa-sort text-muted opacity-50 ms-1" style="font-size:0.7rem;"></i>';
    $icon = ($sortDir === 'ASC') ? 'fa-sort-up' : 'fa-sort-down';
    return '<i class="fas ' . $icon . ' text-primary ms-1" style="font-size:0.7rem;"></i>';
}

// Base URL for filter links (preserves sort)
$baseFilterUrl = url('/admin/training-enrollments') . '?sort=' . urlencode($sortBy) . '&order=' . strtolower($sortDir);
?>

<!-- ─── Page Header ─────────────────────────────────────────────── -->
<div class="content-header d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
    <div>
        <h1 class="page-title mb-1"><i class="fas fa-user-check text-primary me-2"></i>Training Enrollments</h1>
        <p class="subtitle mb-0">Manage student training applications — approve, reject, mark completed, and download records.</p>
    </div>
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <a href="<?= url('/admin/training-enrollments') ?>?<?= http_build_query(array_merge($_GET, ['export' => 'csv'])) ?>"
           class="btn btn-outline-success btn-sm fw-semibold" id="btnExportCSV">
            <i class="fas fa-file-csv me-1"></i> Export CSV
        </a>
        <a href="<?= url('/admin/training-enrollments') ?>?<?= http_build_query(array_merge($_GET, ['export' => 'csv'])) ?>"
           class="btn btn-outline-warning btn-sm fw-semibold" id="btnExportExcel"
           download="training_enrollments_<?= date('Y-m-d') ?>.csv">
            <i class="fas fa-file-excel me-1"></i> Export Excel
        </a>
        <button type="button" class="btn btn-outline-danger btn-sm fw-semibold" id="btnPrint" onclick="printEnrollments()">
            <i class="fas fa-file-pdf me-1"></i> Export PDF / Print
        </button>
        <a href="<?= url('/admin/trainings') ?>" class="btn btn-light btn-sm border">
            <i class="fas fa-chalkboard-teacher me-1"></i> Manage Trainings
        </a>
    </div>
</div>

<!-- ─── Metrics Summary Cards ───────────────────────────────────── -->
<div class="row g-3 mb-4" id="metricsRow">
    <!-- Total -->
    <div class="col-lg col-md-4 col-6">
        <a href="<?= url('/admin/training-enrollments') ?>" class="text-decoration-none">
            <div class="card border-0 shadow-sm h-100" style="border-left:4px solid #6366f1!important;">
                <div class="card-body py-3 px-3 d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:44px;height:44px;background:rgba(99,102,241,0.12);color:#6366f1;">
                        <i class="fas fa-list-alt"></i>
                    </div>
                    <div>
                        <div class="fw-bolder fs-4 lh-1" style="color:#6366f1;"><?= $totalApplications ?></div>
                        <div class="text-muted small fw-medium mt-1">Total</div>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <!-- Pending -->
    <div class="col-lg col-md-4 col-6">
        <a href="<?= $baseFilterUrl ?>&status=pending" class="text-decoration-none">
            <div class="card border-0 shadow-sm h-100" style="border-left:4px solid #f59e0b!important;">
                <div class="card-body py-3 px-3 d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:44px;height:44px;background:rgba(245,158,11,0.12);color:#f59e0b;">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div>
                        <div class="fw-bolder fs-4 lh-1" style="color:#f59e0b;"><?= $pendingCount ?></div>
                        <div class="text-muted small fw-medium mt-1">Pending</div>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <!-- Approved -->
    <div class="col-lg col-md-4 col-6">
        <a href="<?= $baseFilterUrl ?>&status=approved" class="text-decoration-none">
            <div class="card border-0 shadow-sm h-100" style="border-left:4px solid #10b981!important;">
                <div class="card-body py-3 px-3 d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:44px;height:44px;background:rgba(16,185,129,0.12);color:#10b981;">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div>
                        <div class="fw-bolder fs-4 lh-1" style="color:#10b981;"><?= $approvedCount ?></div>
                        <div class="text-muted small fw-medium mt-1">Approved</div>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <!-- Rejected -->
    <div class="col-lg col-md-4 col-6">
        <a href="<?= $baseFilterUrl ?>&status=rejected" class="text-decoration-none">
            <div class="card border-0 shadow-sm h-100" style="border-left:4px solid #ef4444!important;">
                <div class="card-body py-3 px-3 d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:44px;height:44px;background:rgba(239,68,68,0.12);color:#ef4444;">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <div>
                        <div class="fw-bolder fs-4 lh-1" style="color:#ef4444;"><?= $rejectedCount ?></div>
                        <div class="text-muted small fw-medium mt-1">Rejected</div>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <!-- Completed -->
    <div class="col-lg col-md-4 col-6">
        <a href="<?= $baseFilterUrl ?>&status=completed" class="text-decoration-none">
            <div class="card border-0 shadow-sm h-100" style="border-left:4px solid #3b82f6!important;">
                <div class="card-body py-3 px-3 d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:44px;height:44px;background:rgba(59,130,246,0.12);color:#3b82f6;">
                        <i class="fas fa-award"></i>
                    </div>
                    <div>
                        <div class="fw-bolder fs-4 lh-1" style="color:#3b82f6;"><?= $completedCount ?></div>
                        <div class="text-muted small fw-medium mt-1">Completed</div>
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>

<!-- ─── Filters & Search ─────────────────────────────────────────── -->
<div class="card shadow-sm border mb-4" id="filterCard">
    <div class="card-body p-3">
        <form method="GET" action="<?= url('/admin/training-enrollments') ?>" id="filterForm" class="row g-2 align-items-center">
            <!-- Preserve sort params -->
            <input type="hidden" name="sort"  value="<?= htmlspecialchars($sortBy) ?>">
            <input type="hidden" name="order" value="<?= htmlspecialchars(strtolower($sortDir)) ?>">

            <div class="col-lg-3 col-md-6">
                <label class="form-label small fw-semibold text-muted mb-1">Search</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" class="form-control" name="search" id="searchInput"
                           value="<?= htmlspecialchars($search) ?>"
                           placeholder="Name, Roll No, Reg No, Email, Program…">
                </div>
            </div>

            <div class="col-lg-2 col-md-3">
                <label class="form-label small fw-semibold text-muted mb-1">Status</label>
                <select class="form-select form-select-sm" name="status" id="statusFilter">
                    <option value="">All Statuses</option>
                    <option value="pending"   <?= in_array($status, ['pending','registered']) ? 'selected' : '' ?>>Pending / Registered</option>
                    <option value="approved"  <?= in_array($status, ['approved','attended'])  ? 'selected' : '' ?>>Approved</option>
                    <option value="rejected"  <?= in_array($status, ['rejected','dropped'])   ? 'selected' : '' ?>>Rejected</option>
                    <option value="completed" <?= $status === 'completed'                     ? 'selected' : '' ?>>Completed</option>
                </select>
            </div>

            <div class="col-lg-2 col-md-3">
                <label class="form-label small fw-semibold text-muted mb-1">Branch</label>
                <select class="form-select form-select-sm" name="branch" id="branchFilter">
                    <option value="">All Branches</option>
                    <?php foreach (BRANCHES as $b): ?>
                    <option value="<?= $b ?>" <?= $branch === $b ? 'selected' : '' ?>><?= $b ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-lg-3 col-md-6">
                <label class="form-label small fw-semibold text-muted mb-1">Training Program</label>
                <select class="form-select form-select-sm" name="training_id" id="trainingFilter">
                    <option value="0">All Programs</option>
                    <?php foreach ($allTrainings as $t): ?>
                    <option value="<?= $t['id'] ?>" <?= $trainingId === (int)$t['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($t['title']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-lg-2 col-md-6 d-flex gap-2 align-self-end">
                <button type="submit" class="btn btn-primary btn-sm fw-semibold w-100" id="btnSearch">
                    <i class="fas fa-search me-1"></i> Filter
                </button>
                <a href="<?= url('/admin/training-enrollments') ?>" class="btn btn-light btn-sm border px-3" title="Reset Filters">
                    <i class="fas fa-undo"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- ─── Results Info Bar ────────────────────────────────────────── -->
<?php if ($search || $status || $branch || $trainingId > 0): ?>
<div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
    <span class="badge bg-light text-dark border fw-normal">
        <i class="fas fa-filter me-1 text-primary"></i>
        <?= $total ?> result<?= $total !== 1 ? 's' : '' ?> found
    </span>
    <?php if ($search): ?>
        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25">
            Search: "<?= htmlspecialchars($search) ?>"
        </span>
    <?php endif; ?>
    <?php if ($status): ?>
        <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25">
            Status: <?= htmlspecialchars(ucfirst($status)) ?>
        </span>
    <?php endif; ?>
    <?php if ($branch): ?>
        <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25">
            Branch: <?= htmlspecialchars($branch) ?>
        </span>
    <?php endif; ?>
    <a href="<?= url('/admin/training-enrollments') ?>" class="small text-danger text-decoration-none ms-1">
        <i class="fas fa-times me-1"></i>Clear all filters
    </a>
</div>
<?php endif; ?>

<!-- ─── Main Data Table ─────────────────────────────────────────── -->
<div class="card shadow-sm border" id="enrollmentsCard">
    <div class="card-body p-0">
        <?php if (empty($enrollments)): ?>
        <div class="text-center py-5">
            <div class="mb-3" style="font-size:3rem;opacity:0.25;">
                <i class="fas fa-user-slash text-muted"></i>
            </div>
            <h5 class="fw-bold text-dark">No Training Applications Found</h5>
            <p class="text-muted mb-3">
                <?php if ($search || $status || $branch || $trainingId > 0): ?>
                    No applications match your current filter criteria.
                    <a href="<?= url('/admin/training-enrollments') ?>">Clear filters</a>
                <?php else: ?>
                    No students have registered for any training program yet.
                <?php endif; ?>
            </p>
        </div>
        <?php else: ?>
        <div class="table-responsive" id="tableWrapper">
            <table class="table mb-0 align-middle table-hover" id="enrollmentsTable">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3" style="width:110px;">
                            <a href="<?= sortUrl('date', $curSort, $sortDir) ?>" class="text-dark text-decoration-none fw-semibold small">
                                App ID <?= sortIcon('date', $curSort, $sortDir) ?>
                            </a>
                        </th>
                        <th>
                            <a href="<?= sortUrl('name', $curSort, $sortDir) ?>" class="text-dark text-decoration-none fw-semibold small">
                                Student Details <?= sortIcon('name', $curSort, $sortDir) ?>
                            </a>
                        </th>
                        <th style="width:130px;">
                            <a href="<?= sortUrl('branch', $curSort, $sortDir) ?>" class="text-dark text-decoration-none fw-semibold small">
                                Branch <?= sortIcon('branch', $curSort, $sortDir) ?>
                            </a>
                        </th>
                        <th>
                            <a href="<?= sortUrl('training', $curSort, $sortDir) ?>" class="text-dark text-decoration-none fw-semibold small">
                                Training Program <?= sortIcon('training', $curSort, $sortDir) ?>
                            </a>
                        </th>
                        <th style="width:145px;">
                            <a href="<?= sortUrl('status', $curSort, $sortDir) ?>" class="text-dark text-decoration-none fw-semibold small">
                                Status &amp; Resume <?= sortIcon('status', $curSort, $sortDir) ?>
                            </a>
                        </th>
                        <th class="text-end pe-3" style="width:230px;"><span class="small fw-semibold">Actions</span></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($enrollments as $e):
                        $statusRaw   = strtolower($e['status'] ?? 'pending');
                        $statusLabel = 'Pending';
                        $statusBadge = 'bg-warning text-dark';

                        if ($statusRaw === 'approved' || $statusRaw === 'attended') {
                            $statusBadge = 'bg-success';       $statusLabel = 'Approved';
                        } elseif ($statusRaw === 'rejected' || $statusRaw === 'dropped') {
                            $statusBadge = 'bg-danger';        $statusLabel = 'Rejected';
                        } elseif ($statusRaw === 'completed') {
                            $statusBadge = 'bg-primary';       $statusLabel = 'Completed';
                        } elseif ($statusRaw === 'pending' || $statusRaw === 'registered') {
                            $statusBadge = 'bg-warning text-dark'; $statusLabel = 'Pending';
                        }

                        $resumeFile  = $e['resume_path'] ?? '';
                        $resumeExt   = $resumeFile ? strtolower(pathinfo($resumeFile, PATHINFO_EXTENSION)) : '';
                        $resumeUrl   = $resumeFile ? uploadUrl('resume/' . $resumeFile) : '';
                        $appliedDate = !empty($e['applied_at']) ? formatDate($e['applied_at']) : formatDate($e['created_at'] ?? date('Y-m-d'));
                    ?>
                    <tr class="border-bottom border-light" data-enrollment-id="<?= $e['id'] ?>">

                        <!-- App ID & Date -->
                        <td class="ps-3">
                            <span class="badge bg-light text-dark border font-monospace d-block mb-1 text-start" style="font-size:0.75rem;">
                                #TR-<?= $e['id'] ?>
                            </span>
                            <div class="text-muted" style="font-size:0.73rem;">
                                <i class="far fa-calendar-alt me-1"></i><?= $appliedDate ?>
                            </div>
                        </td>

                        <!-- Student Details -->
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <img src="<?= !empty($e['profile_photo']) ? uploadUrl('profile_photos/' . $e['profile_photo']) : asset('images/default-avatar.png') ?>"
                                     alt="<?= htmlspecialchars($e['first_name']) ?>"
                                     class="rounded-circle border flex-shrink-0"
                                     style="width:36px;height:36px;object-fit:cover;"
                                     onerror="this.src='<?= asset('images/default-avatar.png') ?>'">
                                <div class="min-w-0">
                                    <a href="<?= url('/admin/view-student/' . $e['student_id']) ?>"
                                       class="fw-bold text-dark text-decoration-none d-block"
                                       style="font-size:0.88rem;" title="View Student Profile">
                                        <?= htmlspecialchars($e['first_name'] . ' ' . $e['last_name']) ?>
                                    </a>
                                    <div style="font-size:0.74rem;color:#6b7280;line-height:1.6;">
                                        <?php if (!empty($e['enrollment_no'])): ?>
                                        <span title="Roll / Enrollment No">
                                            <i class="fas fa-id-badge text-primary me-1 opacity-75"></i><?= htmlspecialchars($e['enrollment_no']) ?>
                                        </span>
                                        <?php endif; ?>
                                        <?php if (!empty($e['registration_no'])): ?>
                                        <span class="ms-1" title="Registration No">
                                            &bull; Reg: <span class="fw-medium text-dark"><?= htmlspecialchars($e['registration_no']) ?></span>
                                        </span>
                                        <?php endif; ?>
                                    </div>
                                    <div style="font-size:0.73rem;color:#6b7280;line-height:1.6;">
                                        <i class="fas fa-envelope me-1 opacity-75"></i><?= htmlspecialchars($e['email']) ?>
                                        <?php if (!empty($e['phone'])): ?>
                                        &nbsp;&bull;&nbsp;<i class="fas fa-phone-alt me-1 opacity-75"></i><?= htmlspecialchars($e['phone']) ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </td>

                        <!-- Branch & Academic -->
                        <td>
                            <span class="badge bg-light text-dark border d-block mb-1 text-truncate" style="max-width:115px;font-size:0.74rem;" title="<?= htmlspecialchars($e['branch'] ?? '') ?>">
                                <?= htmlspecialchars($e['branch'] ?? 'N/A') ?>
                            </span>
                            <div class="text-muted" style="font-size:0.72rem;">
                                <?= !empty($e['degree']) ? htmlspecialchars($e['degree']) : '' ?>
                                <?= !empty($e['passing_year']) ? ' · ' . $e['passing_year'] : '' ?>
                            </div>
                        </td>

                        <!-- Training Program -->
                        <td>
                            <div class="fw-semibold text-primary" style="font-size:0.86rem;line-height:1.4;">
                                <?= htmlspecialchars($e['training_title'] ?? 'N/A') ?>
                            </div>
                            <div class="text-muted" style="font-size:0.73rem;line-height:1.6;">
                                <?= ucfirst($e['training_type'] ?? 'technical') ?> &bull; <?= ucfirst($e['mode'] ?? 'offline') ?>
                                <?php if (!empty($e['trainer_name'])): ?>
                                &bull; <span class="fw-medium text-dark"><?= htmlspecialchars($e['trainer_name']) ?></span>
                                <?php endif; ?>
                            </div>
                        </td>

                        <!-- Status & Resume -->
                        <td>
                            <span class="badge <?= $statusBadge ?> mb-2 d-block" style="font-size:0.74rem;width:fit-content;">
                                <?= $statusLabel ?>
                            </span>

                            <?php if ($statusRaw === 'completed' && !empty($e['certificate_issued'])): ?>
                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 d-block mb-1" style="font-size:0.7rem;width:fit-content;">
                                <i class="fas fa-award me-1"></i>Certified
                            </span>
                            <?php endif; ?>

                            <?php if (!empty($resumeFile)): ?>
                                <?php if ($resumeExt === 'pdf'): ?>
                                <a href="<?= $resumeUrl ?>" target="_blank" rel="noopener"
                                   class="btn btn-xs btn-outline-primary d-inline-flex align-items-center gap-1"
                                   title="View PDF Resume in new tab" id="resumePdf<?= $e['id'] ?>">
                                    <i class="fas fa-file-pdf text-danger" style="font-size:0.75rem;"></i>
                                    <span style="font-size:0.75rem;">View PDF</span>
                                </a>
                                <?php elseif (in_array($resumeExt, ['doc','docx'])): ?>
                                <a href="<?= $resumeUrl ?>" download
                                   class="btn btn-xs btn-outline-secondary d-inline-flex align-items-center gap-1"
                                   title="Download Word Resume" id="resumeDoc<?= $e['id'] ?>">
                                    <i class="fas fa-file-word text-primary" style="font-size:0.75rem;"></i>
                                    <span style="font-size:0.75rem;">Download</span>
                                </a>
                                <?php else: ?>
                                <a href="<?= $resumeUrl ?>" target="_blank"
                                   class="btn btn-xs btn-outline-secondary d-inline-flex align-items-center gap-1"
                                   title="View Resume" id="resumeOther<?= $e['id'] ?>">
                                    <i class="fas fa-file me-1" style="font-size:0.75rem;"></i>
                                    <span style="font-size:0.75rem;">Resume</span>
                                </a>
                                <?php endif; ?>
                            <?php else: ?>
                            <span class="text-muted d-block" style="font-size:0.73rem;font-style:italic;">
                                <i class="fas fa-ban me-1 opacity-50"></i>Not Available
                            </span>
                            <?php endif; ?>
                        </td>

                        <!-- Actions -->
                        <td class="text-end pe-3">
                            <div class="d-flex align-items-center justify-content-end gap-1 flex-wrap">

                                <!-- View Profile -->
                                <a href="<?= url('/admin/view-student/' . $e['student_id']) ?>"
                                   class="btn btn-xs btn-light border" title="View Full Profile"
                                   id="viewProfile<?= $e['id'] ?>">
                                    <i class="fas fa-user-circle text-primary me-1"></i>Profile
                                </a>

                                <!-- Approve -->
                                <?php if (!in_array($statusRaw, ['approved','attended','completed'])): ?>
                                <form action="<?= url('/admin/approve-training-enrollment/' . $e['id']) ?>"
                                      method="POST" class="d-inline"
                                      data-confirm="Approve this training application for <?= htmlspecialchars($e['first_name']) ?>?"
                                      id="formApprove<?= $e['id'] ?>">
                                    <?= CsrfMiddleware::tokenField() ?>
                                    <button type="submit" class="btn btn-xs btn-success fw-semibold" title="Approve Application"
                                            id="btnApprove<?= $e['id'] ?>">
                                        <i class="fas fa-check me-1"></i>Approve
                                    </button>
                                </form>
                                <?php endif; ?>

                                <!-- Reject -->
                                <?php if (!in_array($statusRaw, ['rejected','dropped','completed'])): ?>
                                <button type="button"
                                        class="btn btn-xs btn-outline-danger"
                                        data-bs-toggle="modal"
                                        data-bs-target="#rejectModal<?= $e['id'] ?>"
                                        title="Reject Application"
                                        id="btnReject<?= $e['id'] ?>">
                                    <i class="fas fa-times me-1"></i>Reject
                                </button>
                                <?php endif; ?>

                                <!-- Mark Completed -->
                                <?php if ($statusRaw !== 'completed'): ?>
                                <form action="<?= url('/admin/complete-training-enrollment/' . $e['id']) ?>"
                                      method="POST" class="d-inline"
                                      data-confirm="Mark training completed and issue certificate for <?= htmlspecialchars($e['first_name']) ?>?"
                                      id="formComplete<?= $e['id'] ?>">
                                    <?= CsrfMiddleware::tokenField() ?>
                                    <button type="submit" class="btn btn-xs btn-primary fw-semibold" title="Mark Completed &amp; Issue Certificate"
                                            id="btnComplete<?= $e['id'] ?>">
                                        <i class="fas fa-award me-1"></i>Complete
                                    </button>
                                </form>
                                <?php endif; ?>

                            </div><!-- /action buttons -->

                            <?php if (!empty($e['admin_remarks'])): ?>
                            <div class="mt-1 text-end">
                                <span class="text-muted" style="font-size:0.71rem;" title="<?= htmlspecialchars($e['admin_remarks']) ?>">
                                    <i class="fas fa-comment-alt me-1 opacity-60"></i><?= htmlspecialchars(mb_strimwidth($e['admin_remarks'], 0, 40, '…')) ?>
                                </span>
                            </div>
                            <?php endif; ?>
                        </td>

                    </tr><!-- /row -->

                    <!-- ─── Reject Reason Modal ─────────────────── -->
                    <div class="modal fade text-start" id="rejectModal<?= $e['id'] ?>" tabindex="-1" aria-labelledby="rejectModalLabel<?= $e['id'] ?>" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border-0 shadow-lg">
                                <div class="modal-header text-white" style="background:linear-gradient(135deg,#ef4444,#dc2626);">
                                    <h5 class="modal-title" id="rejectModalLabel<?= $e['id'] ?>">
                                        <i class="fas fa-times-circle me-2"></i>Reject Application
                                    </h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form action="<?= url('/admin/reject-training-enrollment/' . $e['id']) ?>" method="POST">
                                    <?= CsrfMiddleware::tokenField() ?>
                                    <div class="modal-body">
                                        <div class="d-flex align-items-center gap-3 mb-4 p-3 rounded" style="background:#fef2f2;">
                                            <img src="<?= !empty($e['profile_photo']) ? uploadUrl('profile_photos/' . $e['profile_photo']) : asset('images/default-avatar.png') ?>"
                                                 class="rounded-circle border" style="width:44px;height:44px;object-fit:cover;"
                                                 onerror="this.src='<?= asset('images/default-avatar.png') ?>'">
                                            <div>
                                                <div class="fw-bold text-dark"><?= htmlspecialchars($e['first_name'] . ' ' . $e['last_name']) ?></div>
                                                <div class="text-muted small"><?= htmlspecialchars($e['training_title'] ?? '') ?></div>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Reason for Rejection <span class="text-muted fw-normal">(Optional)</span></label>
                                            <textarea class="form-control" name="admin_remarks" rows="3"
                                                      placeholder="Provide a reason to notify the student (e.g. Eligibility criteria not met, capacity full…)"
                                                      id="rejectRemarks<?= $e['id'] ?>"></textarea>
                                            <div class="form-text">The student will be notified with this reason.</div>
                                        </div>
                                    </div>
                                    <div class="modal-footer bg-light">
                                        <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-danger fw-semibold" id="confirmReject<?= $e['id'] ?>">
                                            <i class="fas fa-ban me-1"></i>Confirm Rejection
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div><!-- /rejectModal -->

                    <?php endforeach; ?>
                </tbody>
            </table>
        </div><!-- /table-responsive -->
        <?php endif; ?>
    </div><!-- /card-body -->

    <!-- ─── Pagination Footer ─────────────────────────────────────── -->
    <?php if (($pagination['total_pages'] ?? 1) > 1): ?>
    <div class="card-footer bg-white d-flex align-items-center justify-content-between flex-wrap gap-2 py-3">
        <div class="text-muted small">
            Showing
            <strong><?= (($pagination['current_page'] - 1) * $pagination['per_page']) + 1 ?></strong>
            –
            <strong><?= min($total, $pagination['current_page'] * $pagination['per_page']) ?></strong>
            of <strong><?= $total ?></strong> records
        </div>
        <div>
            <?= renderPagination($pagination, '/admin/training-enrollments', [
                'search'      => $search,
                'status'      => $status,
                'branch'      => $branch,
                'training_id' => $trainingId,
                'sort'        => $sortBy,
                'order'       => strtolower($sortDir),
            ]) ?>
        </div>
    </div>
    <?php endif; ?>

</div><!-- /card -->

<!-- ─── Print Styles ─────────────────────────────────────────────── -->
<style>
@media print {
    #metricsRow, #filterCard, .content-header .d-flex, .sidebar, .topbar, nav, footer,
    .btn, form[data-confirm], #btnPrint, #btnExportCSV, #btnExportExcel { display: none !important; }
    #enrollmentsCard { box-shadow: none !important; border: 1px solid #ccc !important; }
    #enrollmentsTable th, #enrollmentsTable td { font-size: 0.72rem !important; padding: 4px 6px !important; }
    .modal { display: none !important; }
    body { font-size: 12px; }
    .page-title { font-size: 1.1rem !important; }
}

/* Sortable header hover */
#enrollmentsTable thead a:hover { color: var(--bs-primary) !important; }

/* Compact action buttons */
.btn-xs {
    padding: 0.2rem 0.5rem;
    font-size: 0.75rem;
    border-radius: 0.25rem;
    line-height: 1.5;
}
</style>

<script>
/**
 * Open print dialog (for PDF export).
 * Temporarily hides non-essential elements.
 */
function printEnrollments() {
    document.title = 'Training Enrollments — <?= date('d M Y') ?>';
    window.print();
}

/**
 * Auto-submit filter form when dropdowns change.
 */
document.addEventListener('DOMContentLoaded', function () {
    ['statusFilter', 'branchFilter', 'trainingFilter'].forEach(function (id) {
        var el = document.getElementById(id);
        if (el) {
            el.addEventListener('change', function () {
                document.getElementById('filterForm').submit();
            });
        }
    });

    /**
     * Confirm dialogs on status-change forms.
     */
    document.querySelectorAll('form[data-confirm]').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            var msg = form.getAttribute('data-confirm');
            if (msg && !confirm(msg)) {
                e.preventDefault();
            }
        });
    });

    /**
     * Highlight active sorted column header.
     */
    var curSort  = <?= json_encode($sortBy) ?>;
    var curOrder = <?= json_encode(strtolower($sortDir)) ?>;
    var map = { date:'App ID', name:'Student Details', branch:'Branch', training:'Training Program', status:'Status & Resume' };
    // already highlighted via PHP-rendered icons, no extra JS needed.
});
</script>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>
