<?php require_once ROOT_PATH . '/includes/header.php'; ?>

<div class="content-header d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
        <h1 class="page-title">Applied Students</h1>
        <p class="subtitle"><?= htmlspecialchars($job['title']) ?> — <span class="fw-semibold text-primary"><?= count($applications) ?> Applicants</span></p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <!-- View Toggle (Grid / Table) -->
        <div class="btn-group btn-group-sm" role="group" id="viewToggleGroup">
            <button type="button" class="btn btn-outline-secondary active" id="btnGridView" title="Grid Card View">
                <i class="fas fa-th-large me-1"></i> Grid
            </button>
            <button type="button" class="btn btn-outline-secondary" id="btnTableView" title="Table View">
                <i class="fas fa-list me-1"></i> Table
            </button>
        </div>
        <a href="<?= url('/company/jobs') ?>" class="btn btn-light btn-sm border"><i class="fas fa-arrow-left me-1"></i> Back to Jobs</a>
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
<div class="card"><div class="card-body empty-state text-center py-5"><i class="fas fa-inbox fa-3x text-muted mb-3"></i><h5>No Applications</h5><p class="text-muted">No students have applied for this job yet.</p></div></div>
<?php else: ?>

<!-- 1. GRID CARDS VIEW (Equal Height & Equal Width Layout) -->
<div class="row g-4 mb-4" id="applicationsGridView">
    <?php foreach ($applications as $a): ?>
    <div class="col-xl-4 col-lg-6 col-md-6 col-12 applicant-card-col" data-status="<?= $a['status'] ?>">
        <div class="applicant-card">
            <div class="card-body">
                <!-- Top Section: Avatar, Info & Status -->
                <div class="d-flex align-items-start justify-content-between gap-2 mb-3">
                    <div class="d-flex align-items-center gap-3 min-w-0 flex-grow-1">
                        <img src="<?= $a['profile_photo'] ? uploadUrl('profile_photos/' . $a['profile_photo']) : asset('images/default-avatar.png') ?>"
                             alt="" class="rounded-circle border flex-shrink-0" style="width: 50px; height: 50px; object-fit: cover;"
                             onerror="this.src='<?= asset('images/default-avatar.png') ?>'">
                        <div class="min-w-0 flex-grow-1">
                            <h6 class="fw-bold mb-1 text-truncate" style="font-size: 0.95rem;" title="<?= htmlspecialchars($a['first_name'] . ' ' . $a['last_name']) ?>">
                                <?= htmlspecialchars($a['first_name'] . ' ' . $a['last_name']) ?>
                            </h6>
                            <div class="text-muted small text-truncate" title="<?= htmlspecialchars($a['email']) ?>">
                                <i class="fas fa-envelope me-1 opacity-75"></i><?= htmlspecialchars($a['email']) ?>
                            </div>
                            <?php if (!empty($a['phone'])): ?>
                            <div class="text-muted small text-truncate">
                                <i class="fas fa-phone me-1 opacity-75"></i><?= htmlspecialchars($a['phone']) ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <span class="badge <?= getStatusBadgeClass($a['status']) ?> flex-shrink-0 ms-2" style="font-size: 0.725rem; padding: 5px 10px;">
                        <?= ucfirst($a['status']) ?>
                    </span>
                </div>

                <!-- Academic Info Grid -->
                <div class="applicant-meta-grid mb-3">
                    <div class="row g-2 text-center align-items-center">
                        <div class="col-6 border-end">
                            <span class="text-muted d-block" style="font-size: 0.68rem; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 700;">Branch</span>
                            <span class="fw-bold text-dark text-truncate d-block small" title="<?= htmlspecialchars($a['branch'] ?? 'N/A') ?>">
                                <?= htmlspecialchars($a['branch'] ?? 'N/A') ?>
                            </span>
                        </div>
                        <div class="col-6">
                            <span class="text-muted d-block" style="font-size: 0.68rem; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 700;">CGPA</span>
                            <span class="fw-bold text-primary small">
                                <?= $a['cgpa'] ? number_format($a['cgpa'], 2) : 'N/A' ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Applied Date -->
                <div class="small text-muted mb-3 d-flex align-items-center justify-content-between">
                    <span><i class="far fa-clock me-1 opacity-75"></i>Applied:</span>
                    <span class="fw-medium text-dark"><?= timeAgo($a['applied_at']) ?></span>
                </div>

                <!-- Bottom Action Row (Always Aligned at Card Bottom) -->
                <div class="mt-auto pt-3 border-top d-flex align-items-center justify-content-between gap-2">
                    <div class="d-flex align-items-center gap-1">
                        <!-- View Full Profile -->
                        <a href="<?= url('/company/view-applicant/' . $a['student_id']) ?>" class="btn btn-sm btn-primary" title="View Full Profile">
                            <i class="fas fa-user me-1"></i> Profile
                        </a>

                        <!-- Message Applicant Button -->
                        <?php $applicantUserId = (int)($a['user_id'] ?? 0); ?>
                        <?php if ($applicantUserId > 0): ?>
                        <a href="<?= url('/chat?user_id=' . $applicantUserId) ?>" class="btn btn-sm btn-outline-primary" title="Message Applicant">
                            <i class="fas fa-comment-dots"></i>
                        </a>
                        <?php else: ?>
                        <button class="btn btn-sm btn-outline-secondary disabled" title="Chat Unavailable" disabled>
                            <i class="fas fa-comment-dots"></i>
                        </button>
                        <?php endif; ?>

                        <!-- Resume View Link -->
                        <?php if ($a['resume_path']): ?>
                        <a href="<?= url('/company/serve-resume/' . $a['student_id']) ?>" target="_blank" class="btn btn-sm btn-outline-secondary" title="Preview Resume">
                            <i class="fas fa-file-pdf"></i>
                        </a>
                        <?php endif; ?>
                    </div>

                    <!-- Action Dropdown -->
                    <div class="dropdown">
                        <button class="btn btn-sm btn-light border dropdown-toggle fw-medium" data-bs-toggle="dropdown">
                            Action
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                            <?php foreach (['shortlisted'=>'Shortlist','selected'=>'Select','rejected'=>'Reject'] as $sk=>$sv): ?>
                            <?php if ($a['status'] !== $sk): ?>
                            <li><a class="dropdown-item" href="#" onclick="updateStatus(<?= $a['id'] ?>,'<?= $sk ?>')"><i class="fas fa-<?= $sk === 'selected' ? 'check-circle text-success' : ($sk === 'rejected' ? 'times-circle text-danger' : ($sk === 'shortlisted' ? 'star text-warning' : 'calendar text-info')) ?> me-2"></i><?= $sv ?></a></li>
                            <?php endif; ?>
                            <?php endforeach; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#scheduleModal" onclick="setInterviewApp(<?= $a['id'] ?>)"><i class="fas fa-calendar-plus text-primary me-2"></i>Schedule Interview</a></li>
                        </ul>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- 2. TABLE VIEW (Optional view switcher) -->
<div class="card d-none" id="applicationsTableView">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table mb-0 align-middle" id="applicationsTable">
                <thead><tr><th>Student</th><th>Email</th><th>Branch</th><th>CGPA</th><th>Resume</th><th>Status</th><th>Applied</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php foreach ($applications as $a): ?>
                    <tr data-status="<?= $a['status'] ?>">
                        <td><div class="user-cell"><img src="<?= $a['profile_photo'] ? uploadUrl('profile_photos/' . $a['profile_photo']) : asset('images/default-avatar.png') ?>" alt="" class="user-avatar" onerror="this.src='<?= asset('images/default-avatar.png') ?>'"><div class="user-info"><div class="name"><?= htmlspecialchars($a['first_name'] . ' ' . $a['last_name']) ?></div><div class="email"><?= htmlspecialchars($a['phone'] ?? '') ?></div></div></div></td>
                        <td><small><?= htmlspecialchars($a['email']) ?></small></td>
                        <td><?= htmlspecialchars($a['branch']) ?></td>
                        <td><span class="fw-bold text-primary"><?= $a['cgpa'] ?? 'N/A' ?></span></td>
                        <td><?php if ($a['resume_path']): ?><a href="<?= url('/company/serve-resume/' . $a['student_id']) ?>" target="_blank" class="btn btn-sm btn-outline-primary"><i class="fas fa-file-pdf me-1"></i>View</a><?php else: ?><span class="text-muted">-</span><?php endif; ?></td>
                        <td><span class="badge <?= getStatusBadgeClass($a['status']) ?>"><?= ucfirst($a['status']) ?></span></td>
                        <td><small class="text-muted"><?= timeAgo($a['applied_at']) ?></small></td>
                        <td>
                            <div class="d-flex align-items-center gap-1">
                                <a href="<?= url('/company/view-applicant/' . $a['student_id']) ?>" class="btn btn-sm btn-primary" title="View Profile">
                                    <i class="fas fa-user"></i>
                                </a>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-light border dropdown-toggle" data-bs-toggle="dropdown">Action</button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                    <?php foreach (['shortlisted'=>'Shortlist','selected'=>'Select','rejected'=>'Reject'] as $sk=>$sv): ?>
                                    <?php if ($a['status'] !== $sk): ?>
                                    <li><a class="dropdown-item" href="#" onclick="updateStatus(<?= $a['id'] ?>,'<?= $sk ?>')"><i class="fas fa-<?= $sk === 'selected' ? 'check-circle text-success' : ($sk === 'rejected' ? 'times-circle text-danger' : ($sk === 'shortlisted' ? 'star text-warning' : 'calendar text-info')) ?> me-2"></i><?= $sv ?></a></li>
                                    <?php endif; ?>
                                    <?php endforeach; ?>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#scheduleModal" onclick="setInterviewApp(<?= $a['id'] ?>)"><i class="fas fa-calendar-plus text-primary me-2"></i>Schedule Interview</a></li>
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
    <form id="scheduleForm" method="POST">
        <?= CsrfMiddleware::tokenField() ?>
        <div class="modal-body">
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label">Round *</label><input type="text" class="form-control" name="round" value="Round 1" required></div>
                <div class="col-md-6"><label class="form-label">Mode</label><select class="form-select" name="mode"><option value="offline">Offline</option><option value="online">Online</option></select></div>
                <div class="col-md-6"><label class="form-label">Date *</label><input type="date" class="form-control" name="interview_date" min="<?= date('Y-m-d') ?>" required></div>
                <div class="col-md-6"><label class="form-label">Time *</label><input type="time" class="form-control" name="interview_time" required></div>
                <div class="col-12"><label class="form-label">Venue</label><input type="text" class="form-control" name="venue" placeholder="Room/Building"></div>
                <div class="col-12"><label class="form-label">Meeting Link</label><input type="url" class="form-control" name="meeting_link" placeholder="https://..."></div>
                <div class="col-12"><label class="form-label">Instructions</label><textarea class="form-control" name="instructions" rows="2"></textarea></div>
            </div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Schedule</button></div>
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

// Status filter tabs for both Grid & Table view
document.querySelectorAll('#statusTabs .nav-link').forEach(tab => {
    tab.addEventListener('click', function(e) {
        e.preventDefault();
        document.querySelectorAll('#statusTabs .nav-link').forEach(t => t.classList.remove('active'));
        this.classList.add('active');
        const filter = this.dataset.filter;

        // Filter grid view cards
        document.querySelectorAll('.applicant-card-col').forEach(cardCol => {
            cardCol.style.display = (filter === 'all' || cardCol.dataset.status === filter) ? '' : 'none';
        });

        // Filter table view rows
        document.querySelectorAll('#applicationsTable tbody tr').forEach(row => {
            row.style.display = (filter === 'all' || row.dataset.status === filter) ? '' : 'none';
        });
    });
});

// View Toggle Switcher (Grid vs Table)
const btnGrid = document.getElementById('btnGridView');
const btnTable = document.getElementById('btnTableView');
const gridView = document.getElementById('applicationsGridView');
const tableView = document.getElementById('applicationsTableView');

if (btnGrid && btnTable && gridView && tableView) {
    btnGrid.addEventListener('click', function() {
        btnGrid.classList.add('active');
        btnTable.classList.remove('active');
        gridView.classList.remove('d-none');
        tableView.classList.add('d-none');
        localStorage.setItem('tpms_company_app_view', 'grid');
    });

    btnTable.addEventListener('click', function() {
        btnTable.classList.add('active');
        btnGrid.classList.remove('active');
        tableView.classList.remove('d-none');
        gridView.classList.add('d-none');
        localStorage.setItem('tpms_company_app_view', 'table');
    });

    // Restore saved view preference
    const savedView = localStorage.getItem('tpms_company_app_view');
    if (savedView === 'table') {
        btnTable.click();
    }
}
</script>
<?php require_once ROOT_PATH . '/includes/footer.php'; ?>
