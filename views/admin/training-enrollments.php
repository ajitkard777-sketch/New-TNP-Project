<?php require_once ROOT_PATH . '/includes/header.php'; ?>

<div class="content-header d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
    <div>
        <h1 class="page-title mb-1"><i class="fas fa-user-check text-primary me-2"></i>Training Enrollments</h1>
        <p class="subtitle mb-0">Manage student training applications, evaluate candidates, approve/reject registrations, and issue certificates.</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <button type="button" class="btn btn-outline-success btn-sm fw-semibold" onclick="exportEnrollmentsCSV()">
            <i class="fas fa-file-excel me-1"></i> Export CSV / Excel
        </button>
        <button type="button" class="btn btn-outline-danger btn-sm fw-semibold" onclick="window.print()">
            <i class="fas fa-file-pdf me-1"></i> Export PDF / Print
        </button>
    </div>
</div>

<!-- Filters & Search Form -->
<div class="card shadow-sm border mb-4">
    <div class="card-body p-3">
        <form method="GET" action="<?= url('/admin/training-enrollments') ?>" class="row g-2 align-items-center">
            <div class="col-lg-3 col-md-6">
                <label class="form-label small fw-semibold text-muted mb-1">Search Candidate / Program</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" class="form-control" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Name, Roll No, Reg No, Title...">
                </div>
            </div>
            <div class="col-lg-2 col-md-6">
                <label class="form-label small fw-semibold text-muted mb-1">Status</label>
                <select class="form-select form-select-sm" name="status">
                    <option value="">All Statuses</option>
                    <option value="pending" <?= ($status === 'pending' || $status === 'registered') ? 'selected' : '' ?>>Pending / Registered</option>
                    <option value="approved" <?= ($status === 'approved' || $status === 'attended') ? 'selected' : '' ?>>Approved</option>
                    <option value="rejected" <?= ($status === 'rejected' || $status === 'dropped') ? 'selected' : '' ?>>Rejected</option>
                    <option value="completed" <?= $status === 'completed' ? 'selected' : '' ?>>Completed</option>
                </select>
            </div>
            <div class="col-lg-2 col-md-6">
                <label class="form-label small fw-semibold text-muted mb-1">Branch</label>
                <select class="form-select form-select-sm" name="branch">
                    <option value="">All Branches</option>
                    <?php foreach (BRANCHES as $b): ?>
                    <option value="<?= $b ?>" <?= $branch === $b ? 'selected' : '' ?>><?= $b ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-lg-3 col-md-6">
                <label class="form-label small fw-semibold text-muted mb-1">Training Program</label>
                <select class="form-select form-select-sm" name="training_id">
                    <option value="0">All Training Programs</option>
                    <?php foreach ($allTrainings as $t): ?>
                    <option value="<?= $t['id'] ?>" <?= $trainingId === (int)$t['id'] ? 'selected' : '' ?>><?= htmlspecialchars($t['title']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-lg-2 col-md-12 d-flex gap-2 align-self-end">
                <button type="submit" class="btn btn-primary btn-sm fw-semibold w-100"><i class="fas fa-search me-1"></i> Search</button>
                <a href="<?= url('/admin/training-enrollments') ?>" class="btn btn-light btn-sm border" title="Reset Filters"><i class="fas fa-undo"></i></a>
            </div>
        </form>
    </div>
</div>

<!-- Main Enrollments Data Table -->
<div class="card shadow-sm border">
    <div class="card-body p-0">
        <?php if (empty($enrollments)): ?>
        <div class="text-center py-5">
            <i class="fas fa-user-slash fa-3x text-muted mb-3 opacity-50"></i>
            <h5 class="fw-bold">No Training Applications Found</h5>
            <p class="text-muted mb-0">No student training applications match your selected filter criteria.</p>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table mb-0 align-middle table-hover" id="enrollmentsDataTable">
                <thead class="table-light">
                    <tr>
                        <th style="padding-left: 15px; width: 120px;">App ID &amp; Date</th>
                        <th>Student &amp; Contact Details</th>
                        <th style="width: 140px;">Branch &amp; Academic</th>
                        <th>Training Program</th>
                        <th style="width: 140px;">Status &amp; Resume</th>
                        <th style="text-align: right; padding-right: 15px; width: 220px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($enrollments as $e): 
                        $statusRaw = strtolower($e['status']);
                        $statusBadgeClass = 'bg-secondary';
                        $statusLabel = ucfirst($e['status']);

                        if ($statusRaw === 'approved' || $statusRaw === 'attended') {
                            $statusBadgeClass = 'bg-success';
                            $statusLabel = 'Approved';
                        } elseif ($statusRaw === 'rejected' || $statusRaw === 'dropped') {
                            $statusBadgeClass = 'bg-danger';
                            $statusLabel = 'Rejected';
                        } elseif ($statusRaw === 'completed') {
                            $statusBadgeClass = 'bg-primary';
                            $statusLabel = 'Completed';
                        } elseif ($statusRaw === 'pending' || $statusRaw === 'registered') {
                            $statusBadgeClass = 'bg-warning text-dark';
                            $statusLabel = 'Pending';
                        }

                        $resumeFile = $e['resume_path'] ?? $e['resume'] ?? '';
                    ?>
                    <tr>
                        <td style="padding-left: 15px;">
                            <span class="badge bg-light text-dark border font-monospace mb-1">#TR-<?= $e['id'] ?></span>
                            <div class="text-muted" style="font-size:0.75rem;"><i class="far fa-calendar-alt me-1"></i><?= formatDate($e['created_at']) ?></div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <img src="<?= !empty($e['profile_photo']) ? uploadUrl('profile_photos/' . $e['profile_photo']) : asset('images/default-avatar.png') ?>" alt="" class="rounded-circle border flex-shrink-0" style="width:36px;height:36px;object-fit:cover;" onerror="this.src='<?= asset('images/default-avatar.png') ?>'">
                                <div class="min-w-0">
                                    <a href="<?= url('/admin/view-student/' . $e['student_id']) ?>" class="fw-bold text-dark text-decoration-none hover-primary d-block text-truncate">
                                        <?= htmlspecialchars($e['first_name'] . ' ' . $e['last_name']) ?>
                                    </a>
                                    <div class="text-muted" style="font-size:0.76rem;">
                                        <span class="fw-semibold text-dark"><?= htmlspecialchars($e['enrollment_no'] ?? 'N/A') ?></span> • <?= htmlspecialchars($e['email']) ?>
                                        <?php if (!empty($e['phone'])): ?> • <i class="fas fa-phone-alt me-1 opacity-75"></i><?= htmlspecialchars($e['phone']) ?><?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border mb-1"><?= htmlspecialchars($e['branch'] ?? 'N/A') ?></span>
                            <div class="text-muted" style="font-size:0.75rem;">
                                <?= !empty($e['degree']) ? htmlspecialchars($e['degree']) : '' ?>
                                <?= !empty($e['passing_year']) ? ' (' . $e['passing_year'] . ')' : '' ?>
                            </div>
                        </td>
                        <td>
                            <div class="fw-semibold text-primary"><?= htmlspecialchars($e['training_title']) ?></div>
                            <div class="text-muted" style="font-size:0.76rem;">
                                <?= ucfirst($e['training_type'] ?? 'technical') ?> • <?= ucfirst($e['mode'] ?? 'offline') ?>
                                <?php if (!empty($e['trainer_name'])): ?> • Provider: <strong><?= htmlspecialchars($e['trainer_name']) ?></strong><?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <div class="mb-1"><span class="badge <?= $statusBadgeClass ?>" style="font-size:0.76rem;"><?= $statusLabel ?></span></div>
                            <?php if (!empty($resumeFile)): ?>
                            <a href="<?= uploadUrl('resume/' . $resumeFile) ?>" target="_blank" class="btn btn-xs btn-outline-primary d-inline-flex align-items-center gap-1" title="View Resume">
                                <i class="fas fa-file-pdf text-danger"></i> Resume
                            </a>
                            <?php else: ?>
                            <span class="text-muted" style="font-size:0.75rem;">No Resume</span>
                            <?php endif; ?>
                        </td>
                        <td style="text-align: right; padding-right: 15px;">
                            <div class="d-flex align-items-center justify-content-end gap-1 flex-wrap">
                                <!-- View Profile Button -->
                                <a href="<?= url('/admin/view-student/' . $e['student_id']) ?>" class="btn btn-xs btn-light border" title="View Full Profile">
                                    <i class="fas fa-user-circle text-primary"></i> Profile
                                </a>

                                <!-- Approve Button -->
                                <?php if ($statusRaw !== 'approved' && $statusRaw !== 'attended' && $statusRaw !== 'completed'): ?>
                                <form action="<?= url('/admin/approve-training-enrollment/' . $e['id']) ?>" method="POST" class="d-inline" data-confirm="Approve this student application?">
                                    <?= CsrfMiddleware::tokenField() ?>
                                    <button type="submit" class="btn btn-xs btn-success fw-semibold" title="Approve Application">
                                        <i class="fas fa-check me-1"></i> Approve
                                    </button>
                                </form>
                                <?php endif; ?>

                                <!-- Reject Button -->
                                <?php if ($statusRaw !== 'rejected' && $statusRaw !== 'dropped' && $statusRaw !== 'completed'): ?>
                                <button type="button" class="btn btn-xs btn-outline-danger" data-bs-toggle="modal" data-bs-target="#rejectModal<?= $e['id'] ?>" title="Reject Application">
                                    <i class="fas fa-times me-1"></i> Reject
                                </button>
                                <?php endif; ?>

                                <!-- Mark Completed Button -->
                                <?php if ($statusRaw !== 'completed'): ?>
                                <form action="<?= url('/admin/complete-training-enrollment/' . $e['id']) ?>" method="POST" class="d-inline" data-confirm="Mark training completed & issue certificate for this student?">
                                    <?= CsrfMiddleware::tokenField() ?>
                                    <button type="submit" class="btn btn-xs btn-primary fw-semibold" title="Mark Completed & Issue Certificate">
                                        <i class="fas fa-award me-1"></i> Complete
                                    </button>
                                </form>
                                <?php endif; ?>
                            </div>

                            <!-- Reject Reason Modal -->
                            <div class="modal fade text-start" id="rejectModal<?= $e['id'] ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header bg-danger text-white">
                                            <h5 class="modal-title"><i class="fas fa-times-circle me-2"></i>Reject Application — <?= htmlspecialchars($e['first_name'] . ' ' . $e['last_name']) ?></h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form action="<?= url('/admin/reject-training-enrollment/' . $e['id']) ?>" method="POST">
                                            <?= CsrfMiddleware::tokenField() ?>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold">Reason for Rejection (Optional)</label>
                                                    <textarea class="form-control" name="admin_remarks" rows="3" placeholder="Provide remarks or reason to notify student..."></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-danger"><i class="fas fa-ban me-1"></i> Confirm Rejection</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>

    <!-- Pagination Footer -->
    <?php if (($pagination['total_pages'] ?? 1) > 1): ?>
    <div class="card-footer bg-white d-flex align-items-center justify-content-between py-3">
        <div class="text-muted small">
            Showing <?= ((($pagination['current_page'] ?? 1) - 1) * $pagination['per_page']) + 1 ?> to <?= min($total, ($pagination['current_page'] ?? 1) * $pagination['per_page']) ?> of <?= $total ?> records
        </div>
        <div>
            <?= renderPagination($pagination, '/admin/training-enrollments', ['search' => $search, 'status' => $status, 'branch' => $branch, 'training_id' => $trainingId]) ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
function exportEnrollmentsCSV() {
    const table = document.getElementById('enrollmentsDataTable');
    if (!table) return;
    let csv = [];
    const rows = table.querySelectorAll('tr');
    
    rows.forEach(row => {
        const cols = row.querySelectorAll('th, td');
        let rowData = [];
        cols.forEach((col, idx) => {
            if (idx < cols.length - 1) { // Skip Actions column
                let text = col.innerText.replace(/(\r\n|\n|\r)/gm, ' ').replace(/"/g, '""').trim();
                rowData.push('"' + text + '"');
            }
        });
        if (rowData.length > 0) csv.push(rowData.join(','));
    });

    const csvContent = 'data:text/csv;charset=utf-8,' + csv.join('\n');
    const encodedUri = encodeURI(csvContent);
    const link = document.createElement('a');
    link.setAttribute('href', encodedUri);
    link.setAttribute('download', 'training_enrollments_' + new Date().toISOString().slice(0, 10) + '.csv');
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}
</script>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>
