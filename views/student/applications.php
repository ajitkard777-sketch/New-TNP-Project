<?php require_once ROOT_PATH . '/includes/header.php'; ?>

<div class="content-header">
    <div>
        <h1 class="page-title">My Applications</h1>
        <p class="subtitle">Track all your submitted job applications and download application receipts</p>
    </div>
</div>

<?php if (empty($applications)): ?>
<div class="card p-5 text-center empty-state border-0 shadow-sm" style="border-radius: 1rem;">
    <i class="fas fa-paper-plane text-muted" style="font-size: 3rem; opacity: 0.5;"></i>
    <h5 class="mt-3 fw-bold">No Applications Yet</h5>
    <p class="text-muted">You haven't applied to any job postings yet. Explore recommended jobs and submit your applications!</p>
    <div>
        <a href="<?= url('/student/jobs') ?>" class="btn btn-primary btn-sm px-4"><i class="fas fa-search me-1"></i> Browse Jobs</a>
    </div>
</div>
<?php else: ?>

<!-- Application Summary Stats -->
<div class="row g-3 mb-4">
    <?php
    $statusCounts = [];
    foreach ($applications as $a) { $statusCounts[$a['status']] = ($statusCounts[$a['status']] ?? 0) + 1; }
    $statColors = [
        'applied' => 'primary',
        'shortlisted' => 'warning',
        'interview' => 'info',
        'selected' => 'success',
        'rejected' => 'danger',
        'withdrawn' => 'secondary'
    ];
    foreach ($statColors as $stKey => $color):
    ?>
    <div class="col-6 col-sm-4 col-md-2">
        <div class="p-3 text-center bg-white rounded-3 border shadow-sm">
            <div class="fw-bold fs-4 text-<?= $color ?>"><?= $statusCounts[$stKey] ?? 0 ?></div>
            <small class="text-muted font-medium" style="font-size:0.75rem; text-transform: uppercase;"><?= ucfirst($stKey) ?></small>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Applications Cards List -->
<div class="row g-4">
    <?php foreach ($applications as $app): ?>
    <div class="col-lg-6">
        <div class="card h-100 border-0 shadow-sm hover-shadow animate-fade-in-up" style="border-radius: 1rem; transition: transform 0.2s;">
            <div class="card-body p-4 d-flex flex-column justify-content-between">
                <div>
                    <!-- Header with Company Logo & Status Badge -->
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="d-flex gap-3 align-items-center">
                            <img src="<?= $app['logo'] ? uploadUrl('company/' . $app['logo']) : asset('images/default-avatar.png') ?>" alt="" class="job-company-logo rounded-3 border" style="width: 50px; height: 50px; object-fit: contain; background: #fff; padding: 3px;" onerror="this.src='<?= asset('images/default-avatar.png') ?>'">
                            <div>
                                <h5 class="fw-bold mb-1" style="font-size: 1.05rem;"><?= htmlspecialchars($app['job_title']) ?></h5>
                                <div class="text-muted font-medium" style="font-size: 0.85rem;"><i class="fas fa-building me-1"></i><?= htmlspecialchars($app['company_name']) ?></div>
                            </div>
                        </div>

                        <!-- Status Badge -->
                        <span class="badge <?= getStatusBadgeClass($app['status']) ?> p-2 px-3 fw-bold" style="font-size:0.8rem;">
                            <?= ucfirst($app['status']) ?>
                        </span>
                    </div>

                    <!-- Meta info -->
                    <div class="d-flex flex-wrap gap-2 mb-3 text-muted" style="font-size: 0.8rem;">
                        <span class="badge bg-light text-dark border"><i class="fas fa-map-marker-alt text-danger me-1"></i><?= htmlspecialchars($app['location'] ?: 'N/A') ?></span>
                        <span class="badge bg-light text-dark border"><i class="fas fa-clock text-info me-1"></i><?= JOB_TYPES[$app['job_type']] ?? ucfirst($app['job_type']) ?></span>
                        <span class="badge bg-light text-dark border"><i class="fas fa-calendar-alt text-primary me-1"></i>Applied: <?= formatDate($app['applied_at']) ?></span>
                    </div>

                    <div class="fw-bold text-success mb-3" style="font-size: 0.95rem;">
                        Salary Package: <?= formatSalaryRange($app['salary_min'], $app['salary_max']) ?>
                    </div>
                </div>

                <!-- Action Buttons: View Details, Withdraw -->
                <div class="pt-3 border-top d-flex flex-wrap align-items-center justify-content-between gap-2 mt-2">
                    <div>
                        <!-- View Details Button -->
                        <button onclick='viewApplicationDetails(<?= json_encode($app) ?>)' class="btn btn-outline-primary btn-sm px-3" title="View Application Details">
                            <i class="fas fa-eye me-1"></i> Details
                        </button>
                    </div>

                    <div>
                        <?php if ($app['status'] === 'applied'): ?>
                        <a href="<?= url('/student/withdraw/' . $app['id']) ?>" class="btn btn-sm btn-outline-danger" data-confirm="Are you sure you want to withdraw this application?">
                            <i class="fas fa-undo me-1"></i> Withdraw
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

<!-- Application Details Modal -->
<div class="modal fade" id="appDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 1rem;">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold" id="appModalTitle">Application Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" id="appModalBody">
            </div>
        </div>
    </div>
</div>

<script>
function viewAppDetails(app) {
    const modalTitle = document.getElementById('appModalTitle');
    const modalBody = document.getElementById('appModalBody');
    modalTitle.innerText = app.job_title + ' - ' + app.company_name;

    const appIdFormatted = 'APP-' + String(app.id).padStart(6, '0');

    let html = `
        <div class="p-3 bg-light rounded-3 mb-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted font-semibold" style="font-size:0.8rem">APPLICATION ID</span>
                <span class="badge bg-primary fs-6">${appIdFormatted}</span>
            </div>
            <div class="d-flex justify-content-between align-items-center">
                <span class="text-muted font-semibold" style="font-size:0.8rem">CURRENT STATUS</span>
                <span class="fw-bold text-success" style="text-transform:uppercase;">${app.status}</span>
            </div>
        </div>

        <div class="mb-3">
            <div class="text-muted font-semibold" style="font-size:0.8rem">COMPANY</div>
            <div class="fw-bold">${app.company_name}</div>
        </div>

        <div class="mb-3">
            <div class="text-muted font-semibold" style="font-size:0.8rem">LOCATION</div>
            <div>${app.location || 'N/A'} (${app.work_mode || 'onsite'})</div>
        </div>

        <div class="mb-3">
            <div class="text-muted font-semibold" style="font-size:0.8rem">APPLIED DATE</div>
            <div>${app.applied_at || 'Recently'}</div>
        </div>

        <div class="d-flex justify-content-end gap-2 pt-3 border-top">
            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
        </div>
    `;

    modalBody.innerHTML = html;
    const modal = new bootstrap.Modal(document.getElementById('appDetailsModal'));
    modal.show();
}

</script>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>
