<?php require_once ROOT_PATH . '/includes/header.php'; ?>
<div class="content-header">
    <div>
        <h1 class="page-title">Higher Studies Management</h1>
        <p class="subtitle">Monitor student higher education applications, career choices, exams, and universities</p>
    </div>
</div>

<ul class="nav nav-tabs mb-4" id="higherStudiesAdminTabs">
    <li class="nav-item">
        <a class="nav-link active" data-bs-toggle="tab" href="#studentApps">
            <i class="fas fa-user-graduate me-1"></i> Student Applications (<?= count($applications) ?>)
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-bs-toggle="tab" href="#univ">
            <i class="fas fa-university me-1"></i> Universities (<?= count($universities) ?>)
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-bs-toggle="tab" href="#exams">
            <i class="fas fa-file-alt me-1"></i> Entrance Exams (<?= count($exams) ?>)
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-bs-toggle="tab" href="#schol">
            <i class="fas fa-award me-1"></i> Scholarships (<?= count($scholarships) ?>)
        </a>
    </li>
</ul>

<div class="tab-content">
    <!-- Student Higher Studies Applications -->
    <div class="tab-pane fade show active" id="studentApps">
        <div class="card mb-4 border-0 shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold"><i class="fas fa-list text-primary me-2"></i>Student Applications</h6>
                <button type="button" class="btn btn-outline-success btn-sm" id="btnExportCSV">
                    <i class="fas fa-file-csv me-1"></i> Export CSV
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0" id="appsTable">
                        <thead class="table-light">
                            <tr>
                                <th>Student Name</th>
                                <th>Branch</th>
                                <th>CGPA</th>
                                <th>Selected Career Option</th>
                                <th>Preferred Course</th>
                                <th>Preferred University</th>
                                <th>Application Date</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($applications)): ?>
                            <tr>
                                <td colspan="9" class="text-center py-4 text-muted">
                                    <i class="fas fa-folder-open fs-3 mb-2 d-block opacity-50"></i>
                                    No student applications found yet.
                                </td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($applications as $app): ?>
                            <tr>
                                <td class="fw-bold">
                                    <?= htmlspecialchars($app['first_name'] . ' ' . $app['last_name']) ?>
                                </td>
                                <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($app['branch'] ?? 'N/A') ?></span></td>
                                <td><span class="fw-semibold text-primary"><?= $app['cgpa'] ? number_format($app['cgpa'], 2) : 'N/A' ?></span></td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                                        <?= htmlspecialchars($app['career_option'] ?? 'N/A') ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($app['preferred_course'] ?: ($app['legacy_course_name'] ?: 'N/A')) ?></td>
                                <td><?= htmlspecialchars($app['preferred_university'] ?: ($app['legacy_univ_name'] ?: ($app['university_name'] ?: 'N/A'))) ?></td>
                                <td><small class="text-muted"><?= formatDate($app['created_at']) ?></small></td>
                                <td>
                                    <?php
                                    $statusClass = 'bg-secondary';
                                    if ($app['status'] === 'accepted' || $app['status'] === 'enrolled') $statusClass = 'bg-success';
                                    elseif ($app['status'] === 'applied') $statusClass = 'bg-info text-dark';
                                    elseif ($app['status'] === 'interested') $statusClass = 'bg-warning text-dark';
                                    elseif ($app['status'] === 'rejected') $statusClass = 'bg-danger';
                                    ?>
                                    <span class="badge <?= $statusClass ?>"><?= ucfirst($app['status']) ?></span>
                                </td>
                                <td class="text-end">
                                    <button type="button" class="btn btn-sm btn-outline-info me-1" 
                                            onclick='viewAppDetails(<?= json_encode($app, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'>
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <form action="<?= url('/admin/update-higher-study/' . $app['id']) ?>" method="POST" class="d-inline">
                                        <?= CsrfMiddleware::tokenField() ?>
                                        <?php if ($app['status'] !== 'accepted' && $app['status'] !== 'enrolled'): ?>
                                        <button type="submit" name="status" value="accepted" class="btn btn-sm btn-success" title="Approve">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <?php endif; ?>
                                        <?php if ($app['status'] !== 'rejected'): ?>
                                        <button type="submit" name="status" value="rejected" class="btn btn-sm btn-danger ms-1" title="Reject">
                                            <i class="fas fa-times"></i>
                                        </button>
                                        <?php endif; ?>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Universities Tab -->
    <div class="tab-pane fade" id="univ">
        <div class="d-flex justify-content-end mb-3">
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addUnivModal">
                <i class="fas fa-plus me-1"></i>Add University
            </button>
        </div>
        <div class="card"><div class="card-body p-0"><div class="table-responsive"><table class="table mb-0">
            <thead><tr><th>University</th><th>Location</th><th>Ranking</th><th>Deadline</th><th>Status</th></tr></thead>
            <tbody>
            <?php foreach ($universities as $u): ?>
            <tr>
                <td class="fw-bold"><?= htmlspecialchars($u['name']) ?></td>
                <td><small><?= htmlspecialchars($u['city'] . ', ' . $u['country']) ?></small></td>
                <td><?= $u['ranking'] ? '#' . $u['ranking'] : '-' ?></td>
                <td><small><?= $u['admission_deadline'] ? formatDate($u['admission_deadline']) : 'TBA' ?></small></td>
                <td><span class="badge bg-success"><?= ucfirst($u['status']) ?></span></td>
            </tr>
            <?php endforeach; ?>
        </tbody></table></div></div></div>
    </div>

    <!-- Entrance Exams Tab -->
    <div class="tab-pane fade" id="exams">
        <div class="card"><div class="card-body p-0"><div class="table-responsive"><table class="table mb-0">
            <thead><tr><th>Exam</th><th>Conducting Body</th><th>Date</th><th>Deadline</th></tr></thead>
            <tbody>
            <?php foreach ($exams as $e): ?>
            <tr>
                <td class="fw-bold"><?= htmlspecialchars($e['name']) ?></td>
                <td><small><?= htmlspecialchars($e['conducting_body'] ?? '') ?></small></td>
                <td><small><?= $e['exam_date'] ? formatDate($e['exam_date']) : 'TBA' ?></small></td>
                <td><small><?= $e['registration_deadline'] ? formatDate($e['registration_deadline']) : 'TBA' ?></small></td>
            </tr>
            <?php endforeach; ?>
        </tbody></table></div></div></div>
    </div>

    <!-- Scholarships Tab -->
    <div class="tab-pane fade" id="schol">
        <div class="card"><div class="card-body p-0"><div class="table-responsive"><table class="table mb-0">
            <thead><tr><th>Scholarship</th><th>Provider</th><th>Amount</th><th>Deadline</th></tr></thead>
            <tbody>
            <?php foreach ($scholarships as $s): ?>
            <tr>
                <td class="fw-bold"><?= htmlspecialchars($s['name']) ?></td>
                <td><small><?= htmlspecialchars($s['provider'] ?? '') ?></small></td>
                <td class="text-success fw-bold"><?= formatCurrency($s['amount'], $s['currency'] ?? 'INR') ?></td>
                <td><small><?= $s['application_deadline'] ? formatDate($s['application_deadline']) : 'TBA' ?></small></td>
            </tr>
            <?php endforeach; ?>
        </tbody></table></div></div></div>
    </div>
</div>

<!-- View Application Modal -->
<div class="modal fade" id="viewAppModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Application Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewAppModalBody">
                <!-- Dynamically filled -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Add University Modal -->
<div class="modal fade" id="addUnivModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title">Add University</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <form action="<?= url('/admin/create-university') ?>" method="POST"><?= CsrfMiddleware::tokenField() ?>
        <div class="modal-body"><div class="row g-3">
            <div class="col-12"><label class="form-label">Name *</label><input type="text" class="form-control" name="name" required></div>
            <div class="col-md-6"><label class="form-label">City</label><input type="text" class="form-control" name="city"></div>
            <div class="col-md-6"><label class="form-label">Country</label><input type="text" class="form-control" name="country" value="India"></div>
            <div class="col-md-6"><label class="form-label">Ranking</label><input type="number" class="form-control" name="ranking" min="1"></div>
            <div class="col-md-6"><label class="form-label">Deadline</label><input type="date" class="form-control" name="admission_deadline"></div>
            <div class="col-12"><label class="form-label">Website</label><input type="url" class="form-control" name="website"></div>
            <div class="col-12"><label class="form-label">Description</label><textarea class="form-control" name="description" rows="2"></textarea></div>
        </div></div>
        <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Add</button></div>
    </form>
</div></div></div>

<script>
function viewAppDetails(app) {
    const body = document.getElementById('viewAppModalBody');
    body.innerHTML = `
        <table class="table table-bordered">
            <tr><th>Student Name</th><td>${app.first_name || ''} ${app.last_name || ''}</td></tr>
            <tr><th>Branch</th><td>${app.branch || 'N/A'}</td></tr>
            <tr><th>CGPA</th><td>${app.cgpa || 'N/A'}</td></tr>
            <tr><th>Career Option</th><td><span class="badge bg-primary">${app.career_option || 'N/A'}</span></td></tr>
            <tr><th>Preferred Course</th><td>${app.preferred_course || app.legacy_course_name || 'N/A'}</td></tr>
            <tr><th>Preferred University</th><td>${app.preferred_university || app.legacy_univ_name || app.university_name || 'N/A'}</td></tr>
            <tr><th>Exam Score / Details</th><td>${app.exam_score || 'N/A'}</td></tr>
            <tr><th>Application Date</th><td>${app.created_at || ''}</td></tr>
            <tr><th>Current Status</th><td><span class="badge bg-info">${app.status || ''}</span></td></tr>
            <tr><th>Notes</th><td>${app.notes || 'None'}</td></tr>
        </table>
    `;
    const modal = new bootstrap.Modal(document.getElementById('viewAppModal'));
    modal.show();
}

document.getElementById('btnExportCSV')?.addEventListener('click', function() {
    const table = document.getElementById('appsTable');
    let csv = [];
    for (let row of table.rows) {
        let cols = Array.from(row.cells).slice(0, 8).map(c => '"' + c.innerText.replace(/"/g, '""').trim() + '"');
        csv.push(cols.join(','));
    }
    const blob = new Blob([csv.join('\n')], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'higher_studies_applications.csv';
    a.click();
});
</script>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>
