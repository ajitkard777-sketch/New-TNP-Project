<?php require_once ROOT_PATH . '/includes/header.php'; ?>
<div class="content-header">
    <div>
        <h1 class="page-title">Interview Schedule</h1>
        <p class="subtitle">Manage and track candidate interview schedules</p>
    </div>
</div>

<!-- Summary Cards -->
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
            <div class="stat-card-label">Total Scheduled</div>
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
            <div class="stat-card-value"><?= count($passed) ?></div>
            <div class="stat-card-label">Passed</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card gradient-warning">
            <div class="stat-card-icon bg-warning-soft"><i class="fas fa-user-check"></i></div>
            <div class="stat-card-value"><?= count($completed) ?></div>
            <div class="stat-card-label">Completed</div>
        </div>
    </div>
</div>

<?php if (empty($interviews)): ?>
<div class="card">
    <div class="card-body empty-state py-5">
        <i class="fas fa-calendar-check text-muted" style="font-size:3rem;"></i>
        <h5 class="mt-3">No Interviews Scheduled</h5>
        <p class="text-muted mb-4">Schedule candidate interviews directly from your Job Applications page.</p>
        <a href="<?= url('/company/jobs') ?>" class="btn btn-primary">
            <i class="fas fa-briefcase me-1"></i> View Jobs &amp; Applications
        </a>
    </div>
</div>
<?php else: ?>
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table mb-0" id="companyInterviewsTable">
                <thead>
                    <tr>
                        <th>Candidate</th>
                        <th>Job Title</th>
                        <th>Round</th>
                        <th>Date &amp; Time</th>
                        <th>Mode / Details</th>
                        <th>Status</th>
                        <th>Result</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($interviews as $i): ?>
                    <tr>
                        <td>
                            <div class="fw-semibold"><?= htmlspecialchars($i['first_name'] . ' ' . $i['last_name']) ?></div>
                            <small class="text-muted"><?= htmlspecialchars($i['branch']) ?></small>
                        </td>
                        <td>
                            <span class="fw-medium" style="font-size:0.88rem;"><?= htmlspecialchars($i['job_title']) ?></span>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border"><?= htmlspecialchars($i['round'] ?? 'Round 1') ?></span>
                        </td>
                        <td>
                            <div class="fw-semibold" style="font-size:0.85rem;"><?= formatDate($i['interview_date']) ?></div>
                            <small class="text-muted"><i class="far fa-clock me-1"></i><?= date('h:i A', strtotime($i['interview_time'])) ?></small>
                        </td>
                        <td>
                            <?php if ($i['mode'] === 'online'): ?>
                            <span class="badge mb-1" style="background:#dbeafe;color:#1e40af;"><i class="fas fa-video me-1"></i>Online</span>
                            <?php if ($i['meeting_link']): ?>
                            <div><a href="<?= htmlspecialchars($i['meeting_link']) ?>" target="_blank" class="text-primary" style="font-size:0.75rem;"><i class="fas fa-link me-1"></i>Join Link</a></div>
                            <?php endif; ?>
                            <?php else: ?>
                            <span class="badge mb-1" style="background:#f3f4f6;color:#374151;"><i class="fas fa-building me-1"></i>Offline</span>
                            <?php if ($i['venue']): ?>
                            <div class="text-muted" style="font-size:0.75rem;"><i class="fas fa-map-marker-alt me-1 text-danger"></i><?= htmlspecialchars($i['venue']) ?></div>
                            <?php endif; ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge <?= getStatusBadgeClass($i['status']) ?>"><?= ucfirst($i['status']) ?></span>
                        </td>
                        <td>
                            <?php if ($i['result'] === 'passed'): ?>
                            <span class="badge" style="background:#dcfce7;color:#166534;"><i class="fas fa-check me-1"></i>Passed</span>
                            <?php elseif ($i['result'] === 'failed'): ?>
                            <span class="badge" style="background:#fee2e2;color:#991b1b;"><i class="fas fa-times me-1"></i>Failed</span>
                            <?php else: ?>
                            <span class="text-muted" style="font-size:0.8rem;">Pending</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-light dropdown-toggle" data-bs-toggle="dropdown">Manage</button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><h6 class="dropdown-header">Mark Result</h6></li>
                                    <li><a class="dropdown-item" href="#" onclick="updateResult(<?= $i['id'] ?>,'passed')"><i class="fas fa-check-circle text-success me-2"></i>Passed</a></li>
                                    <li><a class="dropdown-item" href="#" onclick="updateResult(<?= $i['id'] ?>,'failed')"><i class="fas fa-times-circle text-danger me-2"></i>Failed</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="#" onclick="openEditModal(<?= htmlspecialchars(json_encode($i)) ?>)"><i class="fas fa-edit text-primary me-2"></i>Reschedule / Edit</a></li>
                                    <?php if ($i['status'] !== 'cancelled'): ?>
                                    <li><a class="dropdown-item text-danger" href="#" onclick="cancelInterview(<?= $i['id'] ?>)"><i class="fas fa-ban me-2"></i>Cancel Interview</a></li>
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

<!-- Reschedule / Edit Modal -->
<div class="modal fade" id="editInterviewModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reschedule / Edit Interview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editInterviewForm" method="POST">
                <?= CsrfMiddleware::tokenField() ?>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Round *</label>
                            <input type="text" class="form-control" name="round" id="editRound" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Mode *</label>
                            <select class="form-select" name="mode" id="editMode">
                                <option value="offline">Offline</option>
                                <option value="online">Online</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Date *</label>
                            <input type="date" class="form-control" name="interview_date" id="editDate" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Time *</label>
                            <input type="time" class="form-control" name="interview_time" id="editTime" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Venue</label>
                            <input type="text" class="form-control" name="venue" id="editVenue" placeholder="Room/Building/Address">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Meeting Link (Online)</label>
                            <input type="url" class="form-control" name="meeting_link" id="editLink" placeholder="https://meet.google.com/...">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Instructions</label>
                            <textarea class="form-control" name="instructions" id="editInstructions" rows="2"></textarea>
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
function updateResult(id, result) {
    if (!confirm('Mark interview result as ' + result.toUpperCase() + '?')) return;
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = TPMS.baseUrl + '/company/interview-result/' + id;
    form.innerHTML = '<input name="csrf_token" value="' + TPMS.csrfToken + '"><input name="result" value="' + result + '">';
    document.body.appendChild(form);
    form.submit();
}

function cancelInterview(id) {
    if (!confirm('Are you sure you want to cancel this interview?')) return;
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = TPMS.baseUrl + '/company/cancel-interview/' + id;
    form.innerHTML = '<input name="csrf_token" value="' + TPMS.csrfToken + '">';
    document.body.appendChild(form);
    form.submit();
}

function openEditModal(interview) {
    document.getElementById('editInterviewForm').action = TPMS.baseUrl + '/company/edit-interview/' + interview.id;
    document.getElementById('editRound').value = interview.round || 'Round 1';
    document.getElementById('editMode').value = interview.mode || 'offline';
    document.getElementById('editDate').value = interview.interview_date || '';
    document.getElementById('editTime').value = interview.interview_time || '';
    document.getElementById('editVenue').value = interview.venue || '';
    document.getElementById('editLink').value = interview.meeting_link || '';
    document.getElementById('editInstructions').value = interview.instructions || '';
    new bootstrap.Modal(document.getElementById('editInterviewModal')).show();
}

$(function(){
    if($.fn.DataTable){
        $('#companyInterviewsTable').DataTable({
            order: [[3, 'desc']],
            pageLength: 20
        });
    }
});
</script>
<?php require_once ROOT_PATH . '/includes/footer.php'; ?>
