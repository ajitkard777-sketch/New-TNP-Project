<?php require_once ROOT_PATH . '/includes/header.php'; ?>

<div class="content-header d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
    <div>
        <h1 class="page-title mb-1">Applied Students</h1>
        <p class="subtitle mb-0"><?= htmlspecialchars($job['title']) ?> — <span class="fw-semibold text-primary"><?= count($applications) ?> Applicants</span></p>
    </div>
    <div>
        <a href="<?= url('/company/jobs') ?>" class="btn btn-light btn-sm border fw-semibold px-3">
            <i class="fas fa-arrow-left me-1"></i> Back to Jobs
        </a>
    </div>
</div>

<!-- Status Filter Tabs -->
<?php
$statusCounts = ['all' => count($applications)];
foreach ($applications as $a) { $statusCounts[$a['status']] = ($statusCounts[$a['status']] ?? 0) + 1; }
?>
<ul class="nav nav-tabs mb-4" id="statusTabs">
    <li class="nav-item"><a class="nav-link active" href="#" data-filter="all">All (<?= $statusCounts['all'] ?>)</a></li>
    <?php foreach (['applied','shortlisted','interview','selected','rejected'] as $s): ?>
    <?php if (($statusCounts[$s] ?? 0) > 0): ?>
    <li class="nav-item"><a class="nav-link" href="#" data-filter="<?= $s ?>"><?= ucfirst($s) ?> (<?= $statusCounts[$s] ?>)</a></li>
    <?php endif; ?>
    <?php endforeach; ?>
</ul>

<?php if (empty($applications)): ?>
<div class="card shadow-sm border-0"><div class="card-body empty-state text-center py-5"><i class="fas fa-inbox fa-3x text-muted mb-3"></i><h5>No Applications</h5><p class="text-muted">No students have applied for this job yet.</p></div></div>
<?php else: ?>

<!-- APPLIED STUDENTS TABLE VIEW -->
<div class="card shadow-sm border" id="applicationsTableView">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table mb-0 align-middle" id="applicationsTable">
                <thead>
                    <tr class="table-light">
                        <th style="padding-left: 20px;">Student</th>
                        <th>Email</th>
                        <th>Branch</th>
                        <th>CGPA</th>
                        <th>Resume</th>
                        <th>Status</th>
                        <th>Applied</th>
                        <th style="text-align: right; padding-right: 20px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($applications as $a): ?>
                    <tr data-status="<?= $a['status'] ?>">
                        <td style="padding-left: 20px;">
                            <div class="d-flex align-items-center gap-3">
                                <img src="<?= $a['profile_photo'] ? uploadUrl('profile_photos/' . $a['profile_photo']) : asset('images/default-avatar.png') ?>"
                                     alt="" class="rounded-circle border flex-shrink-0" style="width: 42px; height: 42px; object-fit: cover;"
                                     onerror="this.src='<?= asset('images/default-avatar.png') ?>'">
                                <div>
                                    <div class="fw-bold text-dark" style="font-size: 0.9rem;"><?= htmlspecialchars($a['first_name'] . ' ' . $a['last_name']) ?></div>
                                    <?php if (!empty($a['phone'])): ?>
                                    <div class="text-muted small"><i class="fas fa-phone me-1 opacity-75"></i><?= htmlspecialchars($a['phone']) ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td>
                            <small class="text-dark fw-medium"><i class="fas fa-envelope me-1 opacity-75 text-muted"></i><?= htmlspecialchars($a['email']) ?></small>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border font-monospace"><?= htmlspecialchars($a['branch'] ?? 'N/A') ?></span>
                        </td>
                        <td>
                            <span class="fw-bold text-primary"><?= $a['cgpa'] ? number_format($a['cgpa'], 2) : 'N/A' ?></span>
                        </td>
                        <td>
                            <?php if ($a['resume_path']): ?>
                            <a href="<?= url('/company/serve-resume/' . $a['student_id']) ?>" target="_blank" class="btn btn-xs btn-outline-danger d-inline-flex align-items-center gap-1" style="padding: 4px 8px; font-size: 0.76rem;">
                                <i class="fas fa-file-pdf"></i> PDF
                            </a>
                            <?php else: ?>
                            <span class="text-muted small">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge <?= getStatusBadgeClass($a['status']) ?>" style="font-size: 0.72rem; padding: 5px 9px;">
                                <?= ucfirst($a['status']) ?>
                            </span>
                        </td>
                        <td>
                            <small class="text-muted"><i class="far fa-clock me-1"></i><?= timeAgo($a['applied_at']) ?></small>
                        </td>
                        <td style="text-align: right; padding-right: 20px;">
                            <div class="d-flex align-items-center justify-content-end gap-1">
                                <!-- Profile Button -->
                                <a href="<?= url('/company/view-applicant/' . $a['student_id']) ?>" class="btn btn-xs btn-primary d-inline-flex align-items-center gap-1" style="padding: 5px 10px; font-size: 0.78rem; font-weight: 600;" title="View Full Profile">
                                    <i class="fas fa-user"></i> Profile
                                </a>

                                <!-- Message Applicant Button -->
                                <?php $applicantUserId = (int)($a['user_id'] ?? 0); ?>
                                <?php if ($applicantUserId > 0): ?>
                                <a href="<?= url('/chat?user_id=' . $applicantUserId) ?>" class="btn btn-xs btn-outline-primary" style="padding: 5px 8px; font-size: 0.78rem;" title="Message Applicant">
                                    <i class="fas fa-comment-dots"></i>
                                </a>
                                <?php else: ?>
                                <button class="btn btn-xs btn-outline-secondary disabled" style="padding: 5px 8px; font-size: 0.78rem;" title="Chat Unavailable" disabled>
                                    <i class="fas fa-comment-dots"></i>
                                </button>
                                <?php endif; ?>

                                <!-- Action Dropdown -->
                                <div class="dropdown">
                                    <button class="btn btn-xs btn-light border dropdown-toggle fw-semibold" style="padding: 5px 10px; font-size: 0.78rem;" data-bs-toggle="dropdown" data-bs-popper-config='{"strategy":"fixed"}'>
                                        Action
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-lg" style="border-radius: 10px; font-size: 0.82rem; min-width: 200px;">
                                        <li><a class="dropdown-item py-2" href="<?= url('/company/view-applicant/' . $a['student_id']) ?>"><i class="fas fa-user text-primary me-2"></i>View Profile</a></li>
                                        <?php if (!empty($a['resume_path'])): ?>
                                        <li><a class="dropdown-item py-2" href="<?= url('/company/serve-resume/' . $a['student_id']) ?>" target="_blank"><i class="fas fa-file-pdf text-danger me-2"></i>View Resume</a></li>
                                        <li><a class="dropdown-item py-2" href="<?= url('/company/serve-resume/' . $a['student_id'] . '?download=1') ?>"><i class="fas fa-download text-success me-2"></i>Download Resume</a></li>
                                        <?php endif; ?>
                                        <?php if ($applicantUserId > 0): ?>
                                        <li><a class="dropdown-item py-2" href="<?= url('/chat?user_id=' . $applicantUserId) ?>"><i class="fas fa-comment-dots text-info me-2"></i>Send Message</a></li>
                                        <?php endif; ?>
                                        <li><hr class="dropdown-divider"></li>
                                        <li class="dropdown-header text-uppercase fw-bold text-muted" style="font-size:0.68rem;">Application Actions</li>
                                        <?php if ($a['status'] !== 'shortlisted'): ?>
                                        <li><a class="dropdown-item py-2" href="#" onclick="updateStatus(<?= $a['id'] ?>,'shortlisted');return false;"><i class="fas fa-star text-warning me-2"></i>Shortlist</a></li>
                                        <?php endif; ?>
                                        <?php if ($a['status'] !== 'selected'): ?>
                                        <li><a class="dropdown-item py-2" href="#" onclick="updateStatus(<?= $a['id'] ?>,'selected');return false;"><i class="fas fa-check-circle text-success me-2"></i>Select Candidate</a></li>
                                        <?php endif; ?>
                                        <?php if ($a['status'] !== 'rejected'): ?>
                                        <li><a class="dropdown-item py-2" href="#" onclick="updateStatus(<?= $a['id'] ?>,'rejected');return false;"><i class="fas fa-times-circle text-danger me-2"></i>Reject Application</a></li>
                                        <?php endif; ?>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item py-2" href="#" data-bs-toggle="modal" data-bs-target="#scheduleModal" onclick="setInterviewApp(<?= $a['id'] ?>)"><i class="fas fa-calendar-plus text-primary me-2"></i>Schedule Interview</a></li>
                                    </ul>
                                </div>
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

<!-- Schedule Interview Modal -->
<div class="modal fade" id="scheduleModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title"><i class="fas fa-calendar-plus text-primary me-2"></i>Schedule Interview</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <form id="scheduleForm" method="POST" data-tpms-validate>
        <?= CsrfMiddleware::tokenField() ?>
        <div class="modal-body">
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label fw-semibold">Round *</label><input type="text" class="form-control" name="round" value="Round 1" required></div>
                <div class="col-md-6"><label class="form-label fw-semibold">Date *</label><input type="date" class="form-control" name="interview_date" min="<?= date('Y-m-d') ?>" required></div>
                <div class="col-md-6"><label class="form-label fw-semibold">Time *</label><input type="time" class="form-control" name="interview_time" required></div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Meeting Link *</label>
                    <input type="url" class="form-control" name="meeting_link" placeholder="https://meet.google.com/..." required data-validate-rule="meetingLink" data-validate-label="Meeting Link">
                    <div class="invalid-feedback"></div>
                </div>
                <div class="col-12"><label class="form-label">Instructions / Notes</label><textarea class="form-control" name="instructions" rows="2" placeholder="Optional notes for candidate"></textarea></div>
            </div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary"><i class="fas fa-calendar-check me-1"></i> Schedule Interview</button></div>
    </form>
</div></div></div>

<script>
function updateStatus(appId, status) {
    if (!confirm('Update status to ' + status + '?')) return;
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = TPMS.baseUrl + '/company/update-application/' + appId;
    form.innerHTML = '<input name="csrf_token" value="' + TPMS.csrfToken + '"><input name="status" value="' + status + '">';
    document.body.appendChild(form);
    form.submit();
}

function setInterviewApp(appId) {
    document.getElementById('scheduleForm').action = TPMS.baseUrl + '/company/schedule-interview/' + appId;
}

// Status filter tabs for Table view
document.querySelectorAll('#statusTabs .nav-link').forEach(tab => {
    tab.addEventListener('click', function(e) {
        e.preventDefault();
        document.querySelectorAll('#statusTabs .nav-link').forEach(t => t.classList.remove('active'));
        this.classList.add('active');
        const filter = this.dataset.filter;

        // Filter table view rows
        document.querySelectorAll('#applicationsTable tbody tr').forEach(row => {
            row.style.display = (filter === 'all' || row.dataset.status === filter) ? '' : 'none';
        });
    });
});

// Auto-select tab if status query parameter is present in URL (e.g. ?status=shortlisted)
const urlParams = new URLSearchParams(window.location.search);
const initialStatus = urlParams.get('status');
if (initialStatus) {
    const targetTab = document.querySelector(`#statusTabs .nav-link[data-filter="${initialStatus}"]`);
    if (targetTab) {
        targetTab.click();
    }
}
</script>
<?php require_once ROOT_PATH . '/includes/footer.php'; ?>
