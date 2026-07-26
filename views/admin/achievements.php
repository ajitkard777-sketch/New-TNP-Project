<?php require_once ROOT_PATH . '/includes/header.php'; ?>

<div class="content-header d-flex flex-wrap justify-content-between align-items-center gap-3">
    <div>
        <h1 class="page-title"><i class="fas fa-award text-warning me-2"></i>Student Achievements Verification</h1>
        <p class="subtitle">Review and verify student hackathons, coding competitions, sports, and extracurricular credentials.</p>
    </div>
</div>

<!-- Status Filter Tabs -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-2">
        <div class="d-flex gap-2">
            <a href="<?= url('/admin/achievements') ?>" class="btn btn-sm <?= empty($status) ? 'btn-primary' : 'btn-light' ?>">All Achievements</a>
            <a href="<?= url('/admin/achievements?status=pending') ?>" class="btn btn-sm <?= $status === 'pending' ? 'btn-warning text-dark' : 'btn-light' ?>">Pending Review</a>
            <a href="<?= url('/admin/achievements?status=verified') ?>" class="btn btn-sm <?= $status === 'verified' ? 'btn-success' : 'btn-light' ?>">Verified</a>
            <a href="<?= url('/admin/achievements?status=rejected') ?>" class="btn btn-sm <?= $status === 'rejected' ? 'btn-danger' : 'btn-light' ?>">Rejected</a>
        </div>
    </div>
</div>

<?php if (empty($achievements)): ?>
<div class="card border-0 shadow-sm py-5 text-center">
    <div class="card-body">
        <i class="fas fa-check-double text-success mb-3" style="font-size: 3.5rem;"></i>
        <h5>No Achievements Found</h5>
        <p class="text-muted small">No achievement records matching the selected status filter.</p>
    </div>
</div>
<?php else: ?>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th>Student</th>
                        <th>Achievement Title</th>
                        <th>Category</th>
                        <th>Organizer / Rank</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th class="text-end">Verification Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($achievements as $ach): 
                        $badgeBg = 'bg-warning text-dark';
                        if ($ach['status'] === 'verified') $badgeBg = 'bg-success';
                        if ($ach['status'] === 'rejected') $badgeBg = 'bg-danger';
                    ?>
                    <tr>
                        <td>
                            <div class="fw-bold text-dark"><?= htmlspecialchars($ach['first_name'] . ' ' . $ach['last_name']) ?></div>
                            <span class="text-muted small"><?= htmlspecialchars($ach['enrollment_no'] ?? '') ?> • <?= htmlspecialchars($ach['branch'] ?? '') ?></span>
                        </td>
                        <td>
                            <div class="fw-semibold text-dark"><?= htmlspecialchars($ach['title']) ?></div>
                            <?php if (!empty($ach['description'])): ?>
                                <div class="text-muted small text-truncate" style="max-width: 250px;"><?= htmlspecialchars($ach['description']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td><span class="badge bg-primary-soft text-primary"><?= htmlspecialchars($ach['category']) ?></span></td>
                        <td>
                            <div class="small fw-medium"><?= htmlspecialchars($ach['organizer'] ?: 'N/A') ?></div>
                            <?php if (!empty($ach['position_rank'])): ?>
                                <span class="badge bg-warning-soft text-warning fw-bold"><?= htmlspecialchars($ach['position_rank']) ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="small"><?= $ach['achievement_date'] ? formatDate($ach['achievement_date']) : 'N/A' ?></td>
                        <td><span class="badge <?= $badgeBg ?> text-capitalize"><?= htmlspecialchars($ach['status']) ?></span></td>
                        <td class="text-end">
                            <div class="d-flex gap-1 justify-content-end">
                                <?php if (!empty($ach['certificate_file'])): ?>
                                <a href="<?= uploadUrl('achievements/' . $ach['certificate_file']) ?>" target="_blank" class="btn btn-sm btn-outline-info" title="View Document">
                                    <i class="fas fa-file-pdf"></i>
                                </a>
                                <?php endif; ?>
                                <button class="btn btn-sm btn-success" onclick='openVerifyModal(<?= json_encode($ach) ?>, "verified")'>
                                    <i class="fas fa-check me-1"></i> Approve
                                </button>
                                <button class="btn btn-sm btn-outline-danger" onclick='openVerifyModal(<?= json_encode($ach) ?>, "rejected")'>
                                    <i class="fas fa-times me-1"></i> Reject
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

<?php endif; ?>

<!-- VERIFY ACHIEVEMENT MODAL -->
<div class="modal fade" id="verifyAchModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <form action="" id="verifyAchForm" method="POST">
                <input type="hidden" name="csrf_token" value="<?= CsrfMiddleware::getToken() ?>">
                <input type="hidden" name="status" id="verifyAchStatus">
                
                <div class="modal-header text-white" id="verifyAchHeader">
                    <h5 class="modal-title fw-bold" id="verifyAchTitle">Verify Achievement</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <strong class="d-block text-dark fs-6" id="verifyAchItemTitle"></strong>
                        <span class="small text-muted" id="verifyAchStudent"></span>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Admin Remarks / Feedback</label>
                        <textarea name="admin_remarks" class="form-control" rows="3" placeholder="Enter remarks or verification details..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm" id="verifyAchSubmitBtn">Submit Decision</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openVerifyModal(ach, targetStatus) {
    $('#verifyAchForm').attr('action', TPMS.baseUrl + '/admin/verify-achievement/' + ach.id);
    $('#verifyAchStatus').val(targetStatus);
    $('#verifyAchItemTitle').text(ach.title + ' (' + ach.category + ')');
    $('#verifyAchStudent').text('Submitted by: ' + ach.first_name + ' ' + ach.last_name + ' (' + (ach.enrollment_no || 'N/A') + ')');

    if (targetStatus === 'verified') {
        $('#verifyAchHeader').removeClass('bg-danger').addClass('bg-success');
        $('#verifyAchTitle').text('Approve Achievement');
        $('#verifyAchSubmitBtn').removeClass('btn-danger').addClass('btn-success').text('Approve & Verify');
    } else {
        $('#verifyAchHeader').removeClass('bg-success').addClass('bg-danger');
        $('#verifyAchTitle').text('Reject Achievement');
        $('#verifyAchSubmitBtn').removeClass('btn-success').addClass('btn-danger').text('Confirm Rejection');
    }

    new bootstrap.Modal(document.getElementById('verifyAchModal')).show();
}
</script>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>
