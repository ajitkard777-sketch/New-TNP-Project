<?php require_once ROOT_PATH . '/includes/header.php'; ?>
<div class="content-header"><div><h1 class="page-title">Applications</h1><p class="subtitle"><?= htmlspecialchars($job['title']) ?> — <?= count($applications) ?> applications</p></div><a href="<?= url('/company/jobs') ?>" class="btn btn-light btn-sm"><i class="fas fa-arrow-left me-1"></i> Back to Jobs</a></div>

<!-- Status filter tabs -->
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
<div class="card"><div class="card-body empty-state"><i class="fas fa-inbox"></i><h5>No Applications</h5><p>No one has applied for this job yet.</p></div></div>
<?php else: ?>
<div class="card"><div class="card-body p-0"><div class="table-responsive">
    <table class="table mb-0" id="applicationsTable">
        <thead><tr><th>Student</th><th>Email</th><th>Branch</th><th>CGPA</th><th>Resume</th><th>Status</th><th>Applied</th><th>Actions</th></tr></thead>
        <tbody>
            <?php foreach ($applications as $a): ?>
            <tr data-status="<?= $a['status'] ?>">
                <td><div class="user-cell"><img src="<?= $a['profile_photo'] ? uploadUrl('profile_photos/' . $a['profile_photo']) : asset('images/default-avatar.png') ?>" alt="" class="user-avatar" onerror="this.src='<?= asset('images/default-avatar.png') ?>'"><div class="user-info"><div class="name"><?= htmlspecialchars($a['first_name'] . ' ' . $a['last_name']) ?></div><div class="email"><?= htmlspecialchars($a['phone'] ?? '') ?></div></div></div></td>
                <td><small><?= htmlspecialchars($a['email']) ?></small></td>
                <td><?= htmlspecialchars($a['branch']) ?></td>
                <td><span class="fw-bold text-primary"><?= $a['cgpa'] ?? 'N/A' ?></span></td>
                <td><?php if ($a['resume_path']): ?><a href="<?= uploadUrl('resume/' . $a['resume_path']) ?>" target="_blank" class="btn btn-sm btn-outline-primary"><i class="fas fa-file-pdf me-1"></i>View</a><?php else: ?><span class="text-muted">-</span><?php endif; ?></td>
                <td><span class="badge <?= getStatusBadgeClass($a['status']) ?>"><?= ucfirst($a['status']) ?></span></td>
                <td><small class="text-muted"><?= timeAgo($a['applied_at']) ?></small></td>
                <td>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-light dropdown-toggle" data-bs-toggle="dropdown">Action</button>
                        <ul class="dropdown-menu">
                            <?php foreach (['shortlisted'=>'Shortlist','interview'=>'Schedule Interview','selected'=>'Select','rejected'=>'Reject'] as $sk=>$sv): ?>
                            <?php if ($a['status'] !== $sk): ?>
                            <li><a class="dropdown-item" href="#" onclick="updateStatus(<?= $a['id'] ?>,'<?= $sk ?>')"><i class="fas fa-<?= $sk === 'selected' ? 'check-circle text-success' : ($sk === 'rejected' ? 'times-circle text-danger' : ($sk === 'shortlisted' ? 'star text-warning' : 'calendar text-info')) ?> me-2"></i><?= $sv ?></a></li>
                            <?php endif; ?>
                            <?php endforeach; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#scheduleModal" onclick="setInterviewApp(<?= $a['id'] ?>)"><i class="fas fa-calendar-plus text-primary me-2"></i>Schedule Interview</a></li>
                        </ul>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div></div></div>
<?php endif; ?>

<!-- Schedule Interview Modal -->
<div class="modal fade" id="scheduleModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title">Schedule Interview</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
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
        <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Schedule</button></div>
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

// Status filter tabs
document.querySelectorAll('#statusTabs .nav-link').forEach(tab => {
    tab.addEventListener('click', function(e) {
        e.preventDefault();
        document.querySelectorAll('#statusTabs .nav-link').forEach(t => t.classList.remove('active'));
        this.classList.add('active');
        const filter = this.dataset.filter;
        document.querySelectorAll('#applicationsTable tbody tr').forEach(row => {
            row.style.display = (filter === 'all' || row.dataset.status === filter) ? '' : 'none';
        });
    });
});
</script>
<?php require_once ROOT_PATH . '/includes/footer.php'; ?>
