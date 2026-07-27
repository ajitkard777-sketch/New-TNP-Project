<?php require_once ROOT_PATH . '/includes/header.php'; ?>
<div class="content-header">
    <div>
        <h1 class="page-title">Trainings &amp; Workshops</h1>
        <p class="subtitle">Register and track online and offline training programs</p>
    </div>
</div>

<ul class="nav nav-tabs mb-4">
    <li class="nav-item">
        <a class="nav-link active" data-bs-toggle="tab" href="#available">
            <i class="fas fa-chalkboard-teacher me-1"></i> Available Trainings
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-bs-toggle="tab" href="#registered">
            <i class="fas fa-clipboard-list me-1"></i> My Registrations (<?= count($myTrainings) ?>)
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
                <div class="card hover-scale border-0 shadow-sm h-100">
                    <div class="card-body d-flex flex-column justify-content-between p-4">
                        <div>
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h5 class="fw-bold mb-1"><?= htmlspecialchars($t['title']) ?></h5>
                                    <span class="badge bg-<?= $t['training_type'] === 'technical' ? 'primary' : ($t['training_type'] === 'soft-skills' ? 'success' : 'warning') ?>"><?= ucfirst($t['training_type']) ?></span>
                                    <span class="badge <?= getStatusBadgeClass($t['status']) ?>"><?= ucfirst($t['status']) ?></span>
                                </div>
                                <span class="badge bg-<?= $t['mode'] === 'online' ? 'info text-dark' : 'secondary' ?>"><?= ucfirst($t['mode']) ?></span>
                            </div>

                            <?php if ($t['description']): ?>
                            <p class="text-muted mb-3" style="font-size:0.85rem"><?= htmlspecialchars(truncateText($t['description'], 120)) ?></p>
                            <?php endif; ?>

                            <div class="row g-2 mb-3" style="font-size:0.82rem">
                                <div class="col-6"><i class="fas fa-user-tie text-primary me-1"></i>Trainer: <strong><?= htmlspecialchars($t['trainer_name'] ?? 'TBA') ?></strong></div>
                                <div class="col-6"><i class="fas fa-calendar text-primary me-1"></i><?= formatDate($t['start_date']) ?> - <?= formatDate($t['end_date']) ?></div>
                                <?php if ($t['mode'] === 'online'): ?>
                                <div class="col-6"><i class="fas fa-laptop text-info me-1"></i>Platform: <strong><?= htmlspecialchars($t['platform_name'] ?: 'Online') ?></strong></div>
                                <?php else: ?>
                                <div class="col-6"><i class="fas fa-map-marker-alt text-primary me-1"></i>Venue: <?= htmlspecialchars($t['venue'] ?? 'TBA') ?></div>
                                <?php endif; ?>
                                <div class="col-6"><i class="fas fa-users text-primary me-1"></i><?= $t['registered_count'] ?>/<?= $t['capacity'] ?> seats</div>
                            </div>
                            <div class="progress mb-3" style="height:6px"><div class="progress-bar bg-primary" style="width:<?= ($t['registered_count'] / max(1, $t['capacity'])) * 100 ?>%"></div></div>
                        </div>

                        <div>
                            <?php if ($t['is_registered']): ?>
                            <div class="d-flex align-items-center justify-content-between pt-2 border-top">
                                <span class="badge bg-success px-3 py-2"><i class="fas fa-check-circle me-1"></i>Registered</span>
                                <?php if ($t['mode'] === 'online' || !empty($t['training_link'])): ?>
                                    <?php if (!empty($t['training_link'])): ?>
                                    <a href="<?= htmlspecialchars($t['training_link']) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-success btn-sm fw-bold">
                                        <i class="fas fa-video me-1"></i> Join Training
                                    </a>
                                    <?php else: ?>
                                    <small class="text-muted fst-italic"><i class="fas fa-clock me-1"></i>Training link will be provided soon.</small>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                            <?php elseif ($t['registered_count'] >= $t['capacity']): ?>
                            <span class="badge bg-danger">Full</span>
                            <?php else: ?>
                            <a href="<?= url('/student/register-training/' . $t['id']) ?>" class="btn btn-primary btn-sm" data-confirm="Register for this training?"><i class="fas fa-plus me-1"></i> Register Now</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- My Registered Trainings -->
    <div class="tab-pane fade" id="registered">
        <?php if (empty($myTrainings)): ?>
        <div class="card"><div class="card-body empty-state"><i class="fas fa-clipboard-list"></i><h5>No Registrations</h5><p>Register for available trainings above.</p></div></div>
        <?php else: ?>
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Training Name</th>
                                <th>Platform / Mode</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Registration Status</th>
                                <th>Training Link</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($myTrainings as $mt): ?>
                            <tr>
                                <td class="fw-bold">
                                    <?= htmlspecialchars($mt['title']) ?>
                                    <?php if (!empty($mt['trainer_name'])): ?>
                                    <br><small class="text-muted font-normal">Trainer: <?= htmlspecialchars($mt['trainer_name']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($mt['mode'] === 'online'): ?>
                                    <span class="badge bg-info text-dark">
                                        <i class="fas fa-video me-1"></i><?= htmlspecialchars($mt['platform_name'] ?: 'Online') ?>
                                    </span>
                                    <?php else: ?>
                                    <span class="badge bg-secondary">
                                        <i class="fas fa-building me-1"></i><?= ucfirst($mt['mode']) ?>
                                    </span>
                                    <?php endif; ?>
                                </td>
                                <td><small class="fw-semibold"><?= formatDate($mt['start_date']) ?></small></td>
                                <td><small class="fw-semibold"><?= formatDate($mt['end_date']) ?></small></td>
                                <td>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                        <i class="fas fa-check-circle me-1"></i><?= ucfirst($mt['reg_status'] ?? 'registered') ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (!empty($mt['training_link'])): ?>
                                    <a href="<?= htmlspecialchars($mt['training_link']) ?>" target="_blank" rel="noopener noreferrer" class="text-primary text-decoration-none small font-monospace">
                                        <i class="fas fa-external-link-alt me-1"></i><?= htmlspecialchars(truncateText($mt['training_link'], 35)) ?>
                                    </a>
                                    <?php else: ?>
                                    <small class="text-muted fst-italic"><i class="fas fa-clock me-1"></i>Training link will be provided soon.</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($mt['training_link'])): ?>
                                    <a href="<?= htmlspecialchars($mt['training_link']) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-success btn-sm fw-bold">
                                        <i class="fas fa-video me-1"></i> Join Training
                                    </a>
                                    <?php else: ?>
                                    <button class="btn btn-sm btn-outline-secondary disabled">
                                        <i class="fas fa-lock me-1"></i> Pending
                                    </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>
