<?php require_once ROOT_PATH . '/includes/header.php'; ?>
<div class="content-header">
    <div>
        <h1 class="page-title">Training Programs</h1>
        <p class="subtitle">Register for training programs and track your enrollment status</p>
    </div>
</div>

<ul class="nav nav-tabs mb-4">
    <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#available"><i class="fas fa-chalkboard-teacher me-1"></i>Available Trainings</a></li>
    <li class="nav-item">
        <a class="nav-link" data-bs-toggle="tab" href="#registered">
            <i class="fas fa-clipboard-list me-1"></i>My Registrations
            <?php if (!empty($myTrainings)): ?><span class="badge bg-primary ms-1"><?= count($myTrainings) ?></span><?php endif; ?>
        </a>
    </li>
</ul>

<div class="tab-content">
    <!-- Available Trainings -->
    <div class="tab-pane fade show active" id="available">
        <?php if (empty($trainings)): ?>
        <div class="card"><div class="card-body empty-state"><i class="fas fa-chalkboard-teacher"></i><h5>No Trainings Available</h5><p>Check back later for upcoming training programs.</p></div></div>
        <?php else: ?>
        <div class="row g-4">
            <?php foreach ($trainings as $t): ?>
            <div class="col-lg-6">
                <div class="card hover-scale h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h5 class="fw-bold mb-1"><?= htmlspecialchars($t['title']) ?></h5>
                                <span class="badge bg-<?= $t['training_type'] === 'technical' ? 'primary' : ($t['training_type'] === 'soft-skills' ? 'success' : 'warning') ?>"><?= ucfirst($t['training_type']) ?></span>
                                <span class="badge <?= getStatusBadgeClass($t['status']) ?>"><?= ucfirst($t['status']) ?></span>
                            </div>
                            <span class="badge bg-<?= $t['mode'] === 'online' ? 'info' : 'secondary' ?>"><?= ucfirst($t['mode']) ?></span>
                        </div>
                        <?php if ($t['description']): ?><p class="text-muted mb-3" style="font-size:.85rem"><?= htmlspecialchars(mb_substr($t['description'],0,120)) ?>...</p><?php endif; ?>
                        <div class="row g-2 mb-3" style="font-size:.82rem">
                            <div class="col-6"><i class="fas fa-user-tie text-primary me-1"></i><?= htmlspecialchars($t['trainer_name'] ?? 'TBA') ?></div>
                            <div class="col-6"><i class="fas fa-calendar text-primary me-1"></i><?= formatDate($t['start_date']) ?> – <?= formatDate($t['end_date']) ?></div>
                            <div class="col-6"><i class="fas fa-map-marker-alt text-primary me-1"></i><?= htmlspecialchars($t['venue'] ?? 'TBA') ?></div>
                            <div class="col-6"><i class="fas fa-users text-primary me-1"></i><?= $t['registered_count'] ?>/<?= $t['capacity'] ?> seats</div>
                        </div>
                        <div class="progress mb-3" style="height:6px">
                            <div class="progress-bar bg-primary" style="width:<?= min(100, ($t['registered_count'] / max(1, $t['capacity'])) * 100) ?>%"></div>
                        </div>
                        <?php if ($t['is_registered']): ?>
                        <span class="badge bg-success px-3 py-2"><i class="fas fa-check me-1"></i>Registered</span>
                        <?php elseif ($t['registered_count'] >= $t['capacity']): ?>
                        <span class="badge bg-danger px-3 py-2">Full</span>
                        <?php else: ?>
                        <a href="<?= url('/student/register-training/' . $t['id']) ?>" class="btn btn-primary btn-sm" data-confirm="Register for this training?"><i class="fas fa-plus me-1"></i>Register</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- My Registrations -->
    <div class="tab-pane fade" id="registered">
        <?php if (empty($myTrainings)): ?>
        <div class="card"><div class="card-body empty-state"><i class="fas fa-clipboard-list"></i><h5>No Registrations Yet</h5><p>Register for available trainings above.</p></div></div>
        <?php else: ?>
        <div class="card"><div class="card-body p-0"><div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead>
                    <tr>
                        <th>Training</th>
                        <th>Trainer</th>
                        <th>Dates</th>
                        <th>Enrollment Status</th>
                        <th>Attendance</th>
                        <th>Certificate</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($myTrainings as $mt):
                        $statusBadge = [
                            'registered' => 'bg-warning text-dark',
                            'attended'   => 'bg-info',
                            'completed'  => 'bg-success',
                            'dropped'    => 'bg-secondary',
                        ][$mt['status']] ?? 'bg-secondary';
                    ?>
                    <tr>
                        <td>
                            <div class="fw-bold"><?= htmlspecialchars($mt['title']) ?></div>
                            <small class="text-muted"><?= ucfirst($mt['training_status'] ?? '') ?></small>
                        </td>
                        <td><?= htmlspecialchars($mt['trainer_name'] ?? 'N/A') ?></td>
                        <td><small><?= formatDate($mt['start_date']) ?> –<br><?= formatDate($mt['end_date']) ?></small></td>
                        <td><span class="badge <?= $statusBadge ?>"><?= ucfirst($mt['status']) ?></span></td>
                        <td>
                            <?php if ($mt['attendance_count'] > 0): ?>
                            <span class="badge bg-light text-dark"><i class="fas fa-calendar-check me-1 text-success"></i><?= $mt['attendance_count'] ?> days</span>
                            <?php elseif (!empty($mt['attendance_percentage'])): ?>
                            <span class="badge bg-light text-dark"><?= number_format($mt['attendance_percentage'], 1) ?>%</span>
                            <?php else: ?><span class="text-muted">—</span><?php endif; ?>
                        </td>
                        <td>
                            <?php if ($mt['certificate_issued']): ?>
                            <span class="badge bg-success"><i class="fas fa-certificate me-1"></i>Issued</span>
                            <?php else: ?><span class="text-muted small">Pending</span><?php endif; ?>
                        </td>
                        <td>
                            <?php if ($mt['status'] === 'registered'): ?>
                            <a href="<?= url('/student/withdraw-training/' . $mt['id']) ?>" class="btn btn-xs btn-outline-danger" data-confirm="Withdraw from this training?"><i class="fas fa-times me-1"></i>Withdraw</a>
                            <?php else: ?><span class="text-muted small">—</span><?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div></div></div>
        <?php endif; ?>
    </div>
</div>
<?php require_once ROOT_PATH . '/includes/footer.php'; ?>
