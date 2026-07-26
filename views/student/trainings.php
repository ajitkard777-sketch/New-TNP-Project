<?php require_once ROOT_PATH . '/includes/header.php'; ?>
<div class="content-header mb-4">
    <div>
        <h1 class="page-title mb-1"><i class="fas fa-chalkboard-teacher text-primary me-2"></i>Training Programs</h1>
        <p class="subtitle mb-0">Register for skill-building workshops, track progress, and manage training registrations</p>
    </div>
</div>

<ul class="nav nav-tabs mb-4" id="trainingTabs">
    <li class="nav-item">
        <a class="nav-link active fw-semibold" data-bs-toggle="tab" href="#available">
            <i class="fas fa-list-ul me-1"></i> Available Trainings
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link fw-semibold" data-bs-toggle="tab" href="#registered">
            <i class="fas fa-clipboard-check me-1"></i> My Registrations
            <?php if (!empty($myTrainings)): ?>
                <span class="badge bg-primary rounded-pill ms-1"><?= count($myTrainings) ?></span>
            <?php endif; ?>
        </a>
    </li>
</ul>

<div class="tab-content">
    <!-- Available Trainings Tab -->
    <div class="tab-pane fade show active" id="available">
        <?php if (empty($trainings)): ?>
        <div class="card shadow-sm border-0">
            <div class="card-body text-center p-5">
                <i class="fas fa-chalkboard-teacher text-muted mb-3" style="font-size:3.5rem; opacity:0.4;"></i>
                <h5 class="fw-bold text-dark mb-1">No Trainings Available</h5>
                <p class="text-muted small mb-0">Check back later for new skill-building programs and workshops.</p>
            </div>
        </div>
        <?php else: ?>
        <div class="row g-4">
            <?php foreach ($trainings as $t): ?>
            <div class="col-lg-6">
                <div class="card shadow-sm border-0 h-100 hover-scale">
                    <div class="card-body p-4 d-flex flex-column justify-content-between">
                        <div>
                            <div class="d-flex justify-content-between align-items-start gap-2 mb-3">
                                <div>
                                    <h5 class="fw-bold text-dark mb-1" style="font-size:1.1rem;"><?= htmlspecialchars($t['title']) ?></h5>
                                    <div class="d-flex align-items-center gap-1 flex-wrap">
                                        <span class="badge bg-<?= $t['training_type'] === 'technical' ? 'primary' : ($t['training_type'] === 'soft-skills' ? 'success' : 'warning') ?> text-capitalize" style="font-size:0.7rem;">
                                            <?= ucfirst($t['training_type']) ?>
                                        </span>
                                        <span class="badge <?= getStatusBadgeClass($t['status']) ?> text-capitalize" style="font-size:0.7rem;">
                                            <?= ucfirst($t['status']) ?>
                                        </span>
                                    </div>
                                </div>
                                <span class="badge bg-<?= $t['mode'] === 'online' ? 'info-soft text-info' : 'secondary-soft text-secondary' ?> border text-capitalize" style="font-size:0.75rem;">
                                    <i class="fas fa-<?= $t['mode'] === 'online' ? 'video' : 'building' ?> me-1"></i><?= ucfirst($t['mode']) ?>
                                </span>
                            </div>

                            <?php if ($t['description']): ?>
                            <p class="text-secondary small mb-3" style="line-height:1.5; font-size:0.85rem;">
                                <?= htmlspecialchars(truncateText($t['description'], 140)) ?>
                            </p>
                            <?php endif; ?>

                            <div class="row g-2 mb-3 text-secondary" style="font-size:0.82rem;">
                                <div class="col-6"><i class="fas fa-user-tie text-primary me-1"></i><?= htmlspecialchars($t['trainer_name'] ?? 'TBA') ?></div>
                                <div class="col-6"><i class="fas fa-calendar text-primary me-1"></i><?= formatDate($t['start_date']) ?> - <?= formatDate($t['end_date']) ?></div>
                                <div class="col-6"><i class="fas fa-map-marker-alt text-primary me-1"></i><?= htmlspecialchars($t['venue'] ?? 'TBA') ?></div>
                                <div class="col-6"><i class="fas fa-users text-primary me-1"></i><?= $t['registered_count'] ?>/<?= $t['capacity'] ?> seats</div>
                            </div>

                            <?php $percent = round(($t['registered_count'] / max(1, $t['capacity'])) * 100); ?>
                            <div class="progress mb-3" style="height:6px;">
                                <div class="progress-bar bg-primary" style="width:<?= $percent ?>%"></div>
                            </div>
                        </div>

                        <div>
                            <?php if ($t['is_registered']): ?>
                            <span class="badge bg-success-soft text-success px-3 py-2 border border-success" style="font-size:0.8rem;">
                                <i class="fas fa-check-circle me-1"></i>Registered
                            </span>
                            <?php elseif ($t['registered_count'] >= $t['capacity']): ?>
                            <span class="badge bg-danger-soft text-danger px-3 py-2 border border-danger" style="font-size:0.8rem;">
                                <i class="fas fa-ban me-1"></i>Seats Full
                            </span>
                            <?php else: ?>
                            <button type="button" 
                                    onclick="registerForTraining(<?= $t['id'] ?>, this)" 
                                    class="btn btn-primary btn-sm fw-semibold">
                                <i class="fas fa-plus me-1"></i> Register Now
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- My Registrations Tab -->
    <div class="tab-pane fade" id="registered">
        <?php if (empty($myTrainings)): ?>
        <div class="card shadow-sm border-0">
            <div class="card-body text-center p-5">
                <i class="fas fa-clipboard-list text-muted mb-3" style="font-size:3.5rem; opacity:0.4;"></i>
                <h5 class="fw-bold text-dark mb-1">No Active Registrations</h5>
                <p class="text-muted small mb-0">You have not registered for any training programs yet.</p>
            </div>
        </div>
        <?php else: ?>
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-history me-2 text-primary"></i>My Registered Training Programs</h6>
                <span class="badge bg-primary-soft text-primary font-mono fw-bold"><?= count($myTrainings) ?> Total</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0" id="myRegistrationsTable">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Training Program</th>
                                <th>Trainer</th>
                                <th>Schedule Dates</th>
                                <th>Status</th>
                                <th>Certificate</th>
                                <th class="text-end pe-3">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($myTrainings as $mt): ?>
                            <?php
                                $today = date('Y-m-d');
                                $hasStarted = ($today >= $mt['start_date']);
                                $isCancelled = ($mt['status'] === 'cancelled');
                            ?>
                            <tr id="reg-row-<?= $mt['training_id'] ?>">
                                <td class="ps-3 fw-bold text-dark">
                                    <?= htmlspecialchars($mt['title']) ?>
                                </td>
                                <td>
                                    <span class="text-secondary small fw-medium">
                                        <i class="fas fa-user-tie me-1 text-muted"></i><?= htmlspecialchars($mt['trainer_name'] ?? 'N/A') ?>
                                    </span>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <i class="far fa-calendar-alt me-1"></i><?= formatDate($mt['start_date']) ?> - <?= formatDate($mt['end_date']) ?>
                                    </small>
                                </td>
                                <td>
                                    <?php if ($isCancelled): ?>
                                    <span class="badge bg-secondary">Cancelled</span>
                                    <?php else: ?>
                                    <span class="badge <?= getStatusBadgeClass($mt['status']) ?>"><?= ucfirst($mt['status']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= $mt['certificate_issued'] ? '<span class="badge bg-success"><i class="fas fa-certificate me-1"></i>Issued</span>' : '<span class="text-muted small">-</span>' ?>
                                </td>
                                <td class="text-end pe-3">
                                    <?php if ($isCancelled): ?>
                                    <span class="text-muted small"><i class="fas fa-times me-1"></i>Cancelled</span>
                                    <?php elseif ($hasStarted): ?>
                                    <div class="d-inline-block text-end">
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-danger disabled opacity-50 py-1 px-2" 
                                                disabled 
                                                title="Training already started. Cancellation not allowed." 
                                                style="font-size:0.75rem;">
                                            <i class="fas fa-ban me-1"></i> Cancel Registration
                                        </button>
                                        <small class="d-block text-danger mt-1 fw-semibold" style="font-size:0.68rem;">
                                            Training already started. Cancellation not allowed.
                                        </small>
                                    </div>
                                    <?php else: ?>
                                    <button type="button" 
                                            onclick="cancelTrainingRegistration(<?= $mt['training_id'] ?>, this)" 
                                            class="btn btn-sm btn-outline-danger py-1 px-2 fw-semibold btn-cancel-reg" 
                                            style="font-size:0.75rem;">
                                        <i class="fas fa-times-circle me-1"></i> Cancel Registration
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

<script>
function registerForTraining(trainingId, btnElem) {
    if (!confirm('Are you sure you want to register for this training program?')) return;
    
    let originalHtml = btnElem.innerHTML;
    btnElem.disabled = true;
    btnElem.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Registering...';
    
    $.ajax({
        url: TPMS.baseUrl + '/student/register-training/' + trainingId,
        type: 'POST',
        data: { csrf_token: TPMS.csrfToken },
        dataType: 'json',
        success: function(res) {
            if (res && res.success) {
                if (typeof TPMS.showToast === 'function') {
                    TPMS.showToast(res.message || 'Registered successfully!', 'success');
                }
                if (typeof TPMS.fetchNotifications === 'function') {
                    TPMS.fetchNotifications();
                }
                setTimeout(function() { location.reload(); }, 1000);
            } else {
                btnElem.disabled = false;
                btnElem.innerHTML = originalHtml;
                if (typeof TPMS.showToast === 'function') {
                    TPMS.showToast(res.message || 'Registration failed.', 'error');
                }
            }
        },
        error: function(xhr) {
            btnElem.disabled = false;
            btnElem.innerHTML = originalHtml;
            let msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Error processing request.';
            if (typeof TPMS.showToast === 'function') {
                TPMS.showToast(msg, 'error');
            }
        }
    });
}

function cancelTrainingRegistration(trainingId, btnElem) {
    if (!confirm('Are you sure you want to cancel this training registration?')) return;

    let originalHtml = btnElem.innerHTML;
    btnElem.disabled = true;
    btnElem.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Cancelling...';

    $.ajax({
        url: TPMS.baseUrl + '/student/cancel-training/' + trainingId,
        type: 'POST',
        data: { csrf_token: TPMS.csrfToken },
        dataType: 'json',
        success: function(res) {
            if (res && res.success) {
                if (typeof TPMS.showToast === 'function') {
                    TPMS.showToast(res.message || 'Training registration cancelled successfully.', 'success');
                }
                if (typeof TPMS.fetchNotifications === 'function') {
                    TPMS.fetchNotifications();
                }
                setTimeout(function() { location.reload(); }, 1000);
            } else {
                btnElem.disabled = false;
                btnElem.innerHTML = originalHtml;
                if (typeof TPMS.showToast === 'function') {
                    TPMS.showToast(res.message || 'Failed to cancel registration.', 'error');
                }
            }
        },
        error: function(xhr) {
            btnElem.disabled = false;
            btnElem.innerHTML = originalHtml;
            let msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Training already started. Cancellation not allowed.';
            if (typeof TPMS.showToast === 'function') {
                TPMS.showToast(msg, 'error');
            }
        }
    });
}
</script>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>
