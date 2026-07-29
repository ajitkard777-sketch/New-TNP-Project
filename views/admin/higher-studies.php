<?php require_once ROOT_PATH . '/includes/header.php'; ?>
<div class="content-header">
    <div>
        <h1 class="page-title">Higher Studies Management</h1>
        <p class="subtitle">Manage universities, entrance exams, scholarships and student applications</p>
    </div>
</div>

<ul class="nav nav-tabs mb-4" id="hsMainTab" role="tablist">
    <li class="nav-item" role="presentation">
        <a class="nav-link active" id="univ-tab" data-bs-toggle="tab" href="#univ" role="tab" aria-controls="univ" aria-selected="true">
            <i class="fas fa-university me-1"></i>Universities (<?= count($universities) ?>)
        </a>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link" id="exams-tab" data-bs-toggle="tab" href="#exams" role="tab" aria-controls="exams" aria-selected="false">
            <i class="fas fa-file-alt me-1"></i>Entrance Exams (<?= count($exams) ?>)
        </a>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link" id="schol-tab" data-bs-toggle="tab" href="#schol" role="tab" aria-controls="schol" aria-selected="false">
            <i class="fas fa-award me-1"></i>Scholarships (<?= count($scholarships) ?>)
        </a>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link" id="applications-tab" data-bs-toggle="tab" href="#applications" role="tab" aria-controls="applications" aria-selected="false">
            <i class="fas fa-paper-plane me-1"></i>Student Applications
            <?php $pendingApps = count(array_filter($studentApplications, fn($a) => $a['status'] === 'pending')); ?>
            <?php if ($pendingApps): ?>
            <span class="badge bg-warning text-dark ms-1"><?= $pendingApps ?> pending</span>
            <?php else: ?>
            <span class="badge bg-secondary ms-1"><?= count($studentApplications) ?></span>
            <?php endif; ?>
        </a>
    </li>
</ul>

<div class="tab-content" id="hsMainTabContent">

    <!-- ===== UNIVERSITIES TAB ===== -->
    <div class="tab-pane fade show active" id="univ" role="tabpanel" aria-labelledby="univ-tab">
        <div class="d-flex justify-content-end mb-3">
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addUnivModal">
                <i class="fas fa-plus me-1"></i>Add University
            </button>
        </div>
        <?php if (empty($universities)): ?>
        <div class="card"><div class="card-body empty-state"><i class="fas fa-university"></i><h5>No Universities Added</h5><p>Click "Add University" to get started.</p></div></div>
        <?php else: ?>
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0" id="universitiesTable">
                        <thead>
                            <tr>
                                <th>University</th>
                                <th>Location</th>
                                <th>Ranking</th>
                                <th>Admission Deadline</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($universities as $u): ?>
                            <tr>
                                <td>
                                    <div class="fw-bold"><?= htmlspecialchars($u['name']) ?></div>
                                    <?php if ($u['website']): ?><small><a href="<?= htmlspecialchars($u['website']) ?>" target="_blank" class="text-primary"><?= htmlspecialchars($u['website']) ?></a></small><?php endif; ?>
                                </td>
                                <td><small><?= htmlspecialchars(($u['city'] ? $u['city'] . ', ' : '') . ($u['country'] ?? '')) ?></small></td>
                                <td><?= $u['ranking'] ? '<span class="badge bg-primary">#' . $u['ranking'] . '</span>' : '<span class="text-muted">—</span>' ?></td>
                                <td><small><?= $u['admission_deadline'] ? formatDate($u['admission_deadline']) : '<span class="text-muted">TBA</span>' ?></small></td>
                                <td><span class="badge bg-<?= $u['status'] === 'active' ? 'success' : 'secondary' ?>"><?= ucfirst($u['status']) ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div><!-- end #univ -->

    <!-- ===== ENTRANCE EXAMS TAB ===== -->
    <div class="tab-pane fade" id="exams" role="tabpanel" aria-labelledby="exams-tab">
        <?php if (empty($exams)): ?>
        <div class="card"><div class="card-body empty-state"><i class="fas fa-file-alt"></i><h5>No Entrance Exams Listed</h5><p>Exam information will appear here once added.</p></div></div>
        <?php else: ?>
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0" id="examsTable">
                        <thead>
                            <tr>
                                <th>Exam Name</th>
                                <th>Conducting Body</th>
                                <th>Exam Date</th>
                                <th>Reg. Deadline</th>
                                <th>Website</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($exams as $e): ?>
                            <tr>
                                <td>
                                    <div class="fw-bold"><?= htmlspecialchars($e['name']) ?></div>
                                    <?php if (!empty($e['full_name'])): ?><small class="text-muted"><?= htmlspecialchars($e['full_name']) ?></small><?php endif; ?>
                                </td>
                                <td><small><?= htmlspecialchars($e['conducting_body'] ?? '—') ?></small></td>
                                <td><small><?= $e['exam_date'] ? formatDate($e['exam_date']) : '<span class="text-muted">TBA</span>' ?></small></td>
                                <td><small><?= $e['registration_deadline'] ? formatDate($e['registration_deadline']) : '<span class="text-muted">TBA</span>' ?></small></td>
                                <td>
                                    <?php if (!empty($e['website'])): ?>
                                    <a href="<?= htmlspecialchars($e['website']) ?>" target="_blank" class="btn btn-sm btn-outline-primary btn-xs">Visit</a>
                                    <?php else: ?><span class="text-muted">—</span><?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div><!-- end #exams -->

    <!-- ===== SCHOLARSHIPS TAB ===== -->
    <div class="tab-pane fade" id="schol" role="tabpanel" aria-labelledby="schol-tab">
        <?php if (empty($scholarships)): ?>
        <div class="card"><div class="card-body empty-state"><i class="fas fa-award"></i><h5>No Scholarships Listed</h5><p>Scholarship opportunities will appear here once added.</p></div></div>
        <?php else: ?>
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0" id="scholarshipsTable">
                        <thead>
                            <tr>
                                <th>Scholarship</th>
                                <th>Provider</th>
                                <th>Amount</th>
                                <th>Eligibility</th>
                                <th>Application Deadline</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($scholarships as $s): ?>
                            <tr>
                                <td>
                                    <div class="fw-bold"><?= htmlspecialchars($s['name']) ?></div>
                                    <?php if (!empty($s['type'])): ?><span class="badge bg-light text-dark"><?= ucfirst($s['type']) ?></span><?php endif; ?>
                                </td>
                                <td><small><?= htmlspecialchars($s['provider'] ?? '—') ?></small></td>
                                <td class="text-success fw-bold"><?= formatCurrency($s['amount'], $s['currency'] ?? 'INR') ?></td>
                                <td><small><?= htmlspecialchars(mb_substr($s['eligibility'] ?? '—', 0, 80)) ?><?= strlen($s['eligibility'] ?? '') > 80 ? '…' : '' ?></small></td>
                                <td><small><?= $s['application_deadline'] ? formatDate($s['application_deadline']) : '<span class="text-muted">TBA</span>' ?></small></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div><!-- end #schol -->

    <!-- ===== STUDENT APPLICATIONS TAB ===== -->
    <div class="tab-pane fade" id="applications" role="tabpanel" aria-labelledby="applications-tab">
        <?php if (empty($studentApplications)): ?>
        <div class="card">
            <div class="card-body empty-state">
                <i class="fas fa-graduation-cap"></i>
                <h5>No Student Applications Yet</h5>
                <p>Student higher studies applications will appear here once submitted.</p>
            </div>
        </div>
        <?php else: ?>
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0 align-middle" id="applicationsTable">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>University</th>
                                <th>Course</th>
                                <th>Entrance Exam</th>
                                <th>Application Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($studentApplications as $app):
                                $badge = [
                                    'pending'  => 'bg-warning text-dark',
                                    'approved' => 'bg-success',
                                    'rejected' => 'bg-danger',
                                ][$app['status']] ?? 'bg-secondary';
                            ?>
                            <tr>
                                <td>
                                    <div class="fw-bold"><?= htmlspecialchars($app['first_name'] . ' ' . $app['last_name']) ?></div>
                                    <small class="text-muted"><?= htmlspecialchars($app['branch'] ?? '') ?> | <?= htmlspecialchars($app['enrollment_no'] ?? '') ?></small>
                                </td>
                                <td>
                                    <div class="fw-bold"><?= htmlspecialchars($app['university_name']) ?></div>
                                    <small class="text-muted"><?= htmlspecialchars($app['country'] ?? '') ?></small>
                                </td>
                                <td><?= htmlspecialchars($app['course_name'] ?? '—') ?></td>
                                <td>
                                    <?php if (!empty($app['entrance_exam'])): ?>
                                    <div><?= htmlspecialchars($app['entrance_exam']) ?></div>
                                    <?php if (!empty($app['exam_score'])): ?><small class="text-muted">Score: <?= htmlspecialchars($app['exam_score']) ?></small><?php endif; ?>
                                    <?php else: ?><span class="text-muted">—</span><?php endif; ?>
                                </td>
                                <td><small><?= $app['application_date'] ? formatDate($app['application_date']) : formatDate($app['created_at']) ?></small></td>
                                <td><span class="badge <?= $badge ?>"><?= ucfirst($app['status']) ?></span></td>
                                <td>
                                    <?php if ($app['status'] === 'pending'): ?>
                                    <div class="d-flex gap-1">
                                        <button class="btn btn-xs btn-success"
                                                data-bs-toggle="modal"
                                                data-bs-target="#approveModal<?= $app['id'] ?>"
                                                title="Approve Application">
                                            <i class="fas fa-check me-1"></i>Approve
                                        </button>
                                        <button class="btn btn-xs btn-danger"
                                                data-bs-toggle="modal"
                                                data-bs-target="#rejectModal<?= $app['id'] ?>"
                                                title="Reject Application">
                                            <i class="fas fa-times me-1"></i>Reject
                                        </button>
                                    </div>
                                    <?php else: ?>
                                    <span class="text-muted small"><?= htmlspecialchars($app['admin_remarks'] ?? '—') ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>

                            <?php if ($app['status'] === 'pending'): ?>
                            <!-- Approve Modal for Application #<?= $app['id'] ?> -->
                            <div class="modal fade" id="approveModal<?= $app['id'] ?>" tabindex="-1" aria-labelledby="approveModalLabel<?= $app['id'] ?>" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header bg-success text-white">
                                            <h5 class="modal-title" id="approveModalLabel<?= $app['id'] ?>">
                                                <i class="fas fa-check-circle me-2"></i>Approve Application
                                            </h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <form action="<?= url('/admin/approve-higher-study/' . $app['id']) ?>" method="POST">
                                            <?= CsrfMiddleware::tokenField() ?>
                                            <div class="modal-body">
                                                <p>Approve <strong><?= htmlspecialchars($app['first_name'] . ' ' . $app['last_name']) ?></strong>'s application to <strong><?= htmlspecialchars($app['university_name']) ?></strong>?</p>
                                                <label class="form-label">Admin Remarks <span class="text-muted">(optional)</span></label>
                                                <textarea class="form-control" name="admin_remarks" rows="2" placeholder="Congratulations message or any notes..."></textarea>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-success"><i class="fas fa-check me-1"></i>Approve</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Reject Modal for Application #<?= $app['id'] ?> -->
                            <div class="modal fade" id="rejectModal<?= $app['id'] ?>" tabindex="-1" aria-labelledby="rejectModalLabel<?= $app['id'] ?>" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header bg-danger text-white">
                                            <h5 class="modal-title" id="rejectModalLabel<?= $app['id'] ?>">
                                                <i class="fas fa-times-circle me-2"></i>Reject Application
                                            </h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <form action="<?= url('/admin/reject-higher-study/' . $app['id']) ?>" method="POST">
                                            <?= CsrfMiddleware::tokenField() ?>
                                            <div class="modal-body">
                                                <p>Reject <strong><?= htmlspecialchars($app['first_name'] . ' ' . $app['last_name']) ?></strong>'s application to <strong><?= htmlspecialchars($app['university_name']) ?></strong>?</p>
                                                <label class="form-label">Reason for Rejection</label>
                                                <textarea class="form-control" name="admin_remarks" rows="2" placeholder="Explain the reason..."></textarea>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-danger"><i class="fas fa-times me-1"></i>Reject</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>

                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div><!-- end #applications -->

</div><!-- end .tab-content -->

<!-- ===== ADD UNIVERSITY MODAL ===== -->
<div class="modal fade" id="addUnivModal" tabindex="-1" aria-labelledby="addUnivModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addUnivModalLabel">Add University</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= url('/admin/create-university') ?>" method="POST">
                <?= CsrfMiddleware::tokenField() ?>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">University Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" required placeholder="e.g. IIT Bombay">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">City</label>
                            <input type="text" class="form-control" name="city" placeholder="e.g. Mumbai">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Country</label>
                            <input type="text" class="form-control" name="country" value="India">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">NIRF Ranking</label>
                            <input type="number" class="form-control" name="ranking" min="1" placeholder="e.g. 1">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Admission Deadline</label>
                            <input type="date" class="form-control" name="admission_deadline">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Website</label>
                            <input type="url" class="form-control" name="website" placeholder="https://...">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="2" placeholder="Brief description..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-plus me-1"></i>Add University</button>
                </div>
            </form>
        </div>
    </div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof $ !== 'undefined' && $.fn && $.fn.DataTable) {
        if ($('#universitiesTable').length) {
            $('#universitiesTable').DataTable({
                pageLength: 10,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
                order: [[2, 'asc']]
            });
        }
        if ($('#examsTable').length) {
            $('#examsTable').DataTable({ pageLength: 10 });
        }
        if ($('#scholarshipsTable').length) {
            $('#scholarshipsTable').DataTable({ pageLength: 10 });
        }
        if ($('#applicationsTable').length) {
            $('#applicationsTable').DataTable({ pageLength: 10, order: [[4, 'desc']] });
        }
    }
});
</script>
<?php require_once ROOT_PATH . '/includes/footer.php'; ?>
