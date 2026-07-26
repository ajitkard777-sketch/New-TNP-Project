<?php require_once ROOT_PATH . '/includes/header.php'; ?>

<div class="content-header">
    <div>
        <h1 class="page-title">Interview Schedule</h1>
        <p class="subtitle">Monitor and manage all candidate interviews across companies</p>
    </div>
    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#adminScheduleModal">
        <i class="fas fa-calendar-plus me-1"></i> Schedule Interview
    </button>
</div>

<!-- Summary Stats -->
<?php
$scheduled = array_filter($interviews, fn($i) => $i['status'] === 'scheduled' || $i['status'] === 'rescheduled');
$completed = array_filter($interviews, fn($i) => $i['status'] === 'completed');
$passed    = array_filter($interviews, fn($i) => $i['result'] === 'passed');
?>
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="stat-card gradient-primary">
            <div class="stat-card-icon bg-primary-soft"><i class="fas fa-calendar-alt"></i></div>
            <div class="stat-card-value"><?= count($interviews) ?></div>
            <div class="stat-card-label">Total Interviews</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card gradient-info">
            <div class="stat-card-icon bg-info-soft"><i class="fas fa-clock"></i></div>
            <div class="stat-card-value"><?= count($scheduled) ?></div>
            <div class="stat-card-label">Upcoming / Active</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card gradient-success">
            <div class="stat-card-icon bg-success-soft"><i class="fas fa-check-circle"></i></div>
            <div class="stat-card-value"><?= count($completed) ?></div>
            <div class="stat-card-label">Completed</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card gradient-warning">
            <div class="stat-card-icon bg-warning-soft"><i class="fas fa-trophy"></i></div>
            <div class="stat-card-value"><?= count($passed) ?></div>
            <div class="stat-card-label">Passed</div>
        </div>
    </div>
</div>

<?php if (empty($interviews)): ?>
<div class="card">
    <div class="card-body empty-state py-5">
        <i class="fas fa-calendar-check text-muted" style="font-size:3rem;"></i>
        <h5 class="mt-3">No Interviews Scheduled</h5>
        <p class="text-muted mb-4">Interviews scheduled by companies or admin will appear here.</p>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#adminScheduleModal">
            <i class="fas fa-calendar-plus me-1"></i> Schedule First Interview
        </button>
    </div>
</div>
<?php else: ?>

<!-- Filter Bar -->
<div class="card mb-4">
    <div class="card-body py-3">
        <div class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label mb-1" style="font-size:0.78rem;font-weight:600;text-transform:uppercase;color:var(--text-muted)">Filter by Status</label>
                <select class="form-select form-select-sm" id="filterStatus">
                    <option value="">All Statuses</option>
                    <option value="scheduled">Scheduled</option>
                    <option value="rescheduled">Rescheduled</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label mb-1" style="font-size:0.78rem;font-weight:600;text-transform:uppercase;color:var(--text-muted)">Filter by Result</label>
                <select class="form-select form-select-sm" id="filterResult">
                    <option value="">All Results</option>
                    <option value="pending">Pending</option>
                    <option value="passed">Passed</option>
                    <option value="failed">Failed</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label mb-1" style="font-size:0.78rem;font-weight:600;text-transform:uppercase;color:var(--text-muted)">Filter by Mode</label>
                <select class="form-select form-select-sm" id="filterMode">
                    <option value="">All Modes</option>
                    <option value="online">Online</option>
                    <option value="offline">Offline</option>
                </select>
            </div>
            <div class="col-md-3">
                <button class="btn btn-light btn-sm w-100" onclick="resetFilters()">
                    <i class="fas fa-times me-1"></i> Clear Filters
                </button>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table mb-0" id="interviewsTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Student</th>
                        <th>Company</th>
                        <th>Job Role</th>
                        <th>Round</th>
                        <th>Date &amp; Time</th>
                        <th>Mode</th>
                        <th>Status</th>
                        <th>Result</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($interviews as $idx => $i): ?>
                    <tr data-status="<?= $i['status'] ?>" data-result="<?= $i['result'] ?>" data-mode="<?= $i['mode'] ?>">
                        <td class="text-muted" style="font-size:0.75rem;"><?= $idx + 1 ?></td>
                        <td>
                            <div class="fw-semibold" style="font-size:0.88rem;"><?= htmlspecialchars($i['first_name'] . ' ' . $i['last_name']) ?></div>
                            <small class="text-muted"><?= htmlspecialchars($i['branch']) ?></small>
                        </td>
                        <td>
                            <div style="font-size:0.88rem;font-weight:600;"><?= htmlspecialchars($i['company_name']) ?></div>
                        </td>
                        <td>
                            <small class="text-muted"><?= htmlspecialchars($i['job_title']) ?></small>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border" style="font-size:0.72rem;"><?= htmlspecialchars($i['round'] ?? 'Round 1') ?></span>
                        </td>
                        <td>
                            <div style="font-size:0.82rem;font-weight:600;"><?= formatDate($i['interview_date']) ?></div>
                            <small class="text-muted"><i class="far fa-clock me-1"></i><?= date('h:i A', strtotime($i['interview_time'])) ?></small>
                        </td>
                        <td>
                            <?php if ($i['mode'] === 'online'): ?>
                            <span class="badge" style="background:#dbeafe;color:#1e40af;font-size:0.72rem;">
                                <i class="fas fa-video me-1"></i>Online
                            </span>
                            <?php else: ?>
                            <span class="badge" style="background:#f3f4f6;color:#374151;font-size:0.72rem;">
                                <i class="fas fa-building me-1"></i>Offline
                            </span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge <?= getStatusBadgeClass($i['status']) ?>">
                                <?= ucfirst($i['status']) ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($i['result'] === 'passed'): ?>
                            <span class="badge" style="background:#dcfce7;color:#166534;font-size:0.72rem;">
                                <i class="fas fa-check me-1"></i>Passed
                            </span>
                            <?php elseif ($i['result'] === 'failed'): ?>
                            <span class="badge" style="background:#fee2e2;color:#991b1b;font-size:0.72rem;">
                                <i class="fas fa-times me-1"></i>Failed
                            </span>
                            <?php else: ?>
                            <span class="text-muted" style="font-size:0.78rem;">Pending</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-light dropdown-toggle" data-bs-toggle="dropdown">Manage</button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><h6 class="dropdown-header">Mark Result</h6></li>
                                    <li><a class="dropdown-item" href="#" onclick="adminUpdateResult(<?= $i['id'] ?>,'passed')"><i class="fas fa-check-circle text-success me-2"></i>Passed</a></li>
                                    <li><a class="dropdown-item" href="#" onclick="adminUpdateResult(<?= $i['id'] ?>,'failed')"><i class="fas fa-times-circle text-danger me-2"></i>Failed</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="#" onclick="openAdminEditModal(<?= htmlspecialchars(json_encode($i)) ?>)"><i class="fas fa-edit text-primary me-2"></i>Edit / Reschedule</a></li>
                                    <?php if ($i['status'] !== 'cancelled'): ?>
                                    <li><a class="dropdown-item text-danger" href="#" onclick="adminCancelInterview(<?= $i['id'] ?>)"><i class="fas fa-ban me-2"></i>Cancel Interview</a></li>
                                    <?php endif; ?>
                                </ul>
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

<!-- Admin Schedule Interview Modal -->
<div class="modal fade" id="adminScheduleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-calendar-plus me-2 text-primary"></i>Schedule Interview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= url('/admin/schedule-interview') ?>" method="POST">
                <?= CsrfMiddleware::tokenField() ?>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Select Application (Candidate &amp; Job) *</label>
                            <select class="form-select" name="application_id" required>
                                <option value="">Select Candidate Application</option>
                                <?php if (!empty($applications)): ?>
                                <?php foreach ($applications as $app): ?>
                                <option value="<?= $app['application_id'] ?>">
                                    <?= htmlspecialchars($app['first_name'] . ' ' . $app['last_name']) ?> (<?= htmlspecialchars($app['branch']) ?>) — <?= htmlspecialchars($app['job_title']) ?> at <?= htmlspecialchars($app['company_name']) ?>
                                </option>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Round *</label>
                            <input type="text" class="form-control" name="round" value="Round 1" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Mode *</label>
                            <select class="form-select" name="mode">
                                <option value="offline">Offline</option>
                                <option value="online">Online</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Date *</label>
                            <input type="date" class="form-control" name="interview_date" min="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Time *</label>
                            <input type="time" class="form-control" name="interview_time" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Venue</label>
                            <input type="text" class="form-control" name="venue" placeholder="Room/Building/Address">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Meeting Link (Online)</label>
                            <input type="url" class="form-control" name="meeting_link" placeholder="https://meet.google.com/...">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Instructions</label>
                            <textarea class="form-control" name="instructions" rows="2" placeholder="Interview guidelines or notes..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane me-1"></i> Schedule Interview</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Admin Edit / Reschedule Modal -->
<div class="modal fade" id="adminEditInterviewModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-edit me-2 text-primary"></i>Reschedule / Edit Interview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="adminEditInterviewForm" method="POST">
                <?= CsrfMiddleware::tokenField() ?>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Round *</label>
                            <input type="text" class="form-control" name="round" id="adminEditRound" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Mode *</label>
                            <select class="form-select" name="mode" id="adminEditMode">
                                <option value="offline">Offline</option>
                                <option value="online">Online</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Date *</label>
                            <input type="date" class="form-control" name="interview_date" id="adminEditDate" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Time *</label>
                            <input type="time" class="form-control" name="interview_time" id="adminEditTime" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Venue</label>
                            <input type="text" class="form-control" name="venue" id="adminEditVenue" placeholder="Room/Building/Address">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Meeting Link (Online)</label>
                            <input type="url" class="form-control" name="meeting_link" id="adminEditLink" placeholder="https://meet.google.com/...">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Instructions</label>
                            <textarea class="form-control" name="instructions" id="adminEditInstructions" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(function() {
    if ($.fn.DataTable) {
        $('#interviewsTable').DataTable({
            order: [[5, 'desc']],
            pageLength: 25
        });
    }
});

function adminUpdateResult(id, result) {
    if (!confirm('Mark interview result as ' + result.toUpperCase() + '?')) return;
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = TPMS.baseUrl + '/admin/interview-result/' + id;
    form.innerHTML = '<input name="csrf_token" value="' + TPMS.csrfToken + '"><input name="result" value="' + result + '">';
    document.body.appendChild(form);
    form.submit();
}

function adminCancelInterview(id) {
    if (!confirm('Are you sure you want to cancel this interview?')) return;
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = TPMS.baseUrl + '/admin/cancel-interview/' + id;
    form.innerHTML = '<input name="csrf_token" value="' + TPMS.csrfToken + '">';
    document.body.appendChild(form);
    form.submit();
}

function openAdminEditModal(interview) {
    document.getElementById('adminEditInterviewForm').action = TPMS.baseUrl + '/admin/edit-interview/' + interview.id;
    document.getElementById('adminEditRound').value = interview.round || 'Round 1';
    document.getElementById('adminEditMode').value = interview.mode || 'offline';
    document.getElementById('adminEditDate').value = interview.interview_date || '';
    document.getElementById('adminEditTime').value = interview.interview_time || '';
    document.getElementById('adminEditVenue').value = interview.venue || '';
    document.getElementById('adminEditLink').value = interview.meeting_link || '';
    document.getElementById('adminEditInstructions').value = interview.instructions || '';
    new bootstrap.Modal(document.getElementById('adminEditInterviewModal')).show();
}

function resetFilters() {
    document.getElementById('filterStatus').value = '';
    document.getElementById('filterResult').value = '';
    document.getElementById('filterMode').value = '';
    applyFilters();
}

['filterStatus', 'filterResult', 'filterMode'].forEach(id => {
    document.getElementById(id)?.addEventListener('change', applyFilters);
});

function applyFilters() {
    const status = document.getElementById('filterStatus').value;
    const result = document.getElementById('filterResult').value;
    const mode   = document.getElementById('filterMode').value;
    document.querySelectorAll('#interviewsTable tbody tr').forEach(row => {
        const matchStatus = !status || row.dataset.status === status;
        const matchResult = !result || row.dataset.result === result;
        const matchMode   = !mode   || row.dataset.mode   === mode;
        row.style.display = (matchStatus && matchResult && matchMode) ? '' : 'none';
    });
}
</script>
<?php require_once ROOT_PATH . '/includes/footer.php'; ?>
