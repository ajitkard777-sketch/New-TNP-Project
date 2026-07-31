<?php require_once ROOT_PATH . '/includes/header.php'; ?>
<div class="content-header">
    <div><h1 class="page-title">Training Programs</h1><p class="subtitle">Create training programs and manage student enrollments</p></div>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addTrainingModal"><i class="fas fa-plus me-1"></i>Add Training</button>
</div>

<ul class="nav nav-tabs mb-4" id="trainingsMainTab" role="tablist">
    <li class="nav-item" role="presentation">
        <a class="nav-link active" id="trainings-tab" data-bs-toggle="tab" href="#trainingsTab" role="tab" aria-controls="trainingsTab" aria-selected="true">
            <i class="fas fa-chalkboard-teacher me-1"></i>Training Programs (<?= count($trainings) ?>)
        </a>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link" id="enrollments-tab" data-bs-toggle="tab" href="#enrollmentsTab" role="tab" aria-controls="enrollmentsTab" aria-selected="false">
            <i class="fas fa-clipboard-list me-1"></i>Enrollments
            <?php $pendingEnroll = count(array_filter($enrollments, fn($e) => $e['status'] === 'registered')); ?>
            <?php if ($pendingEnroll): ?><span class="badge bg-warning text-dark ms-1"><?php echo $pendingEnroll; ?> pending</span><?php else: ?><span class="badge bg-secondary ms-1"><?php echo count($enrollments); ?></span><?php endif; ?>
        </a>
    </li>
</ul>

<div class="tab-content" id="trainingsMainTabContent">

    <!-- ===== TRAINING PROGRAMS TAB ===== -->
    <div class="tab-pane fade show active" id="trainingsTab" role="tabpanel" aria-labelledby="trainings-tab">
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0" id="trainingsTable">
                        <thead>
                            <tr>
                                <th>Training</th>
                                <th>Type</th>
                                <th>Trainer</th>
                                <th>Dates</th>
                                <th>Mode</th>
                                <th>Enrolled/Cap</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($trainings as $t): ?>
                            <tr>
                                <td>
                                    <div class="fw-bold"><?php echo htmlspecialchars($t['title']); ?></div>
                                    <small class="text-muted"><?php echo htmlspecialchars($t['venue'] ?? ''); ?></small>
                                </td>
                                <td>
                                    <span class="badge bg-<?php echo $t['training_type'] === 'technical' ? 'primary' : ($t['training_type'] === 'soft-skills' ? 'success' : 'warning'); ?>">
                                        <?php echo ucfirst($t['training_type']); ?>
                                    </span>
                                </td>
                                <td><small><?php echo htmlspecialchars($t['trainer_name'] ?? $t['faculty_name'] ?? 'TBA'); ?></small></td>
                                <td><small><?php echo formatDate($t['start_date']); ?> &ndash; <?php echo formatDate($t['end_date']); ?></small></td>
                                <td>
                                    <span class="badge bg-<?php echo $t['mode'] === 'online' ? 'info' : 'secondary'; ?>">
                                        <?php echo ucfirst($t['mode']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="fw-bold"><?php echo $t['enrolled_count'] ?? $t['registered_count']; ?></span>/<span class="text-muted"><?php echo $t['capacity']; ?></span>
                                </td>
                                <td><span class="badge <?php echo getStatusBadgeClass($t['status']); ?>"><?php echo ucfirst($t['status']); ?></span></td>
                                <td>
                                    <a href="<?php echo url('/admin/delete-training/' . $t['id']); ?>" class="btn btn-sm btn-danger" data-confirm="Delete this training?">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div><!-- end #trainingsTab -->

    <!-- ===== ENROLLMENTS TAB ===== -->
    <div class="tab-pane fade" id="enrollmentsTab" role="tabpanel" aria-labelledby="enrollments-tab">
        <?php if (empty($enrollments)): ?>
        <div class="card">
            <div class="card-body empty-state">
                <i class="fas fa-clipboard-list"></i>
                <h5>No Enrollments Yet</h5>
                <p>Student training registrations will appear here.</p>
            </div>
        </div>
        <?php else: ?>
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0 align-middle" id="enrollmentsTable">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Training</th>
                                <th>Enrolled On</th>
                                <th>Status</th>
                                <th>Attendance</th>
                                <th>Certificate</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($enrollments as $e):
                                $statusBadge = [
                                    'registered' => 'bg-warning text-dark',
                                    'attended'   => 'bg-info',
                                    'completed'  => 'bg-success',
                                    'dropped'    => 'bg-secondary',
                                ][$e['status']] ?? 'bg-secondary';
                            ?>
                            <tr>
                                <td>
                                    <div class="fw-bold"><?php echo htmlspecialchars($e['first_name'] . ' ' . $e['last_name']); ?></div>
                                    <small class="text-muted"><?php echo htmlspecialchars($e['branch'] ?? ''); ?> | <?php echo htmlspecialchars($e['enrollment_no'] ?? ''); ?></small>
                                </td>
                                <td>
                                    <div class="fw-bold"><?php echo htmlspecialchars($e['training_title']); ?></div>
                                    <small class="text-muted"><?php echo formatDate($e['start_date']); ?> &ndash; <?php echo formatDate($e['end_date']); ?></small>
                                </td>
                                <td><small><?php echo formatDate($e['created_at']); ?></small></td>
                                <td><span class="badge <?php echo $statusBadge; ?>"><?php echo ucfirst($e['status']); ?></span></td>
                                <td>
                                    <?php if ($e['attendance_count'] > 0): ?>
                                    <span class="badge bg-light text-dark"><?php echo $e['attendance_count']; ?> days</span>
                                    <?php else: ?>
                                    <span class="text-muted">&mdash;</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($e['certificate_issued']): ?>
                                    <span class="badge bg-success"><i class="fas fa-certificate me-1"></i>Issued</span>
                                    <?php else: ?>
                                    <span class="text-muted small">&mdash;</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="d-flex gap-1 flex-nowrap">
                                        <?php if ($e['status'] === 'registered'): ?>
                                        <button class="btn btn-xs btn-success"
                                                data-bs-toggle="modal"
                                                data-bs-target="#approveEnroll<?php echo $e['id']; ?>"
                                                title="Approve">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button class="btn btn-xs btn-danger"
                                                data-bs-toggle="modal"
                                                data-bs-target="#rejectEnroll<?php echo $e['id']; ?>"
                                                title="Reject">
                                            <i class="fas fa-times"></i>
                                        </button>
                                        <?php endif; ?>
                                        <?php if (in_array($e['status'], ['attended', 'registered']) && !$e['certificate_issued']): ?>
                                        <a href="<?php echo url('/admin/issue-certificate/' . $e['id']); ?>"
                                           class="btn btn-xs btn-warning"
                                           data-confirm="Issue certificate for this student?"
                                           title="Issue Certificate">
                                            <i class="fas fa-certificate"></i>
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>

                            <?php if ($e['status'] === 'registered'): ?>
                            <!-- Approve Enrollment Modal -->
                            <div class="modal fade" id="approveEnroll<?php echo $e['id']; ?>" tabindex="-1" aria-labelledby="approveEnrollLabel<?php echo $e['id']; ?>" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header bg-success text-white">
                                            <h5 class="modal-title" id="approveEnrollLabel<?php echo $e['id']; ?>">
                                                <i class="fas fa-check-circle me-2"></i>Approve Enrollment
                                            </h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <form action="<?php echo url('/admin/approve-training-enrollment/' . $e['id']); ?>" method="POST">
                                            <?php echo CsrfMiddleware::tokenField(); ?>
                                            <div class="modal-body">
                                                <p>Approve <strong><?php echo htmlspecialchars($e['first_name'] . ' ' . $e['last_name']); ?></strong>'s enrollment in <strong><?php echo htmlspecialchars($e['training_title']); ?></strong>?</p>
                                                <label class="form-label">Remarks (optional)</label>
                                                <textarea class="form-control" name="admin_remarks" rows="2" placeholder="Any notes..."></textarea>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-success"><i class="fas fa-check me-1"></i>Approve</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Reject Enrollment Modal -->
                            <div class="modal fade" id="rejectEnroll<?php echo $e['id']; ?>" tabindex="-1" aria-labelledby="rejectEnrollLabel<?php echo $e['id']; ?>" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header bg-danger text-white">
                                            <h5 class="modal-title" id="rejectEnrollLabel<?php echo $e['id']; ?>">
                                                <i class="fas fa-times-circle me-2"></i>Reject Enrollment
                                            </h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <form action="<?php echo url('/admin/reject-training-enrollment/' . $e['id']); ?>" method="POST">
                                            <?php echo CsrfMiddleware::tokenField(); ?>
                                            <div class="modal-body">
                                                <p>Reject <strong><?php echo htmlspecialchars($e['first_name'] . ' ' . $e['last_name']); ?></strong>'s enrollment in <strong><?php echo htmlspecialchars($e['training_title']); ?></strong>?</p>
                                                <label class="form-label">Reason for Rejection</label>
                                                <textarea class="form-control" name="admin_remarks" rows="2" placeholder="Explain reason..."></textarea>
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
    </div><!-- end #enrollmentsTab -->

</div><!-- end .tab-content -->

<!-- ===== ADD TRAINING MODAL ===== -->
<div class="modal fade" id="addTrainingModal" tabindex="-1" aria-labelledby="addTrainingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addTrainingModalLabel">Add Training Program</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?php echo url('/admin/create-training'); ?>" method="POST">
                <?php echo CsrfMiddleware::tokenField(); ?>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="title" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Type</label>
                            <select class="form-select" name="training_type">
                                <option value="technical">Technical</option>
                                <option value="soft-skills">Soft Skills</option>
                                <option value="aptitude">Aptitude</option>
                                <option value="workshop">Workshop</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="2"></textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Mode</label>
                            <select class="form-select" name="mode">
                                <option value="offline">Offline</option>
                                <option value="online">Online</option>
                                <option value="hybrid">Hybrid</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Venue</label>
                            <input type="text" class="form-control" name="venue">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Capacity</label>
                            <input type="number" class="form-control" name="capacity" value="50" min="1">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Trainer Name</label>
                            <input type="text" class="form-control" name="trainer_name">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Faculty</label>
                            <select class="form-select" name="faculty_id">
                                <option value="">None</option>
                                <?php foreach ($faculty as $f): ?>
                                <option value="<?php echo $f['id']; ?>"><?php echo htmlspecialchars($f['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Start Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="start_date" min="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">End Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="end_date" min="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Start Time</label>
                            <input type="time" class="form-control" name="start_time">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status">
                                <option value="upcoming">Upcoming</option>
                                <option value="ongoing">Ongoing</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-plus me-1"></i>Create Training</button>
                </div>
            </form>
        </div>
    </div>
</div><!-- end #addTrainingModal -->

<script>
// Auto-activate the tab that matches the URL hash (e.g. #enrollmentsTab)
(function () {
    var hash = window.location.hash;
    if (hash) {
        var tabTrigger = document.querySelector('a[href="' + hash + '"][data-bs-toggle="tab"]');
        if (tabTrigger) {
            var bsTab = new bootstrap.Tab(tabTrigger);
            bsTab.show();
            // Smooth scroll to top so the tab is visible
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    }
})();
</script>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>
