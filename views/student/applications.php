<?php require_once ROOT_PATH . '/includes/header.php'; ?>

<div class="content-header mb-4">
    <div>
        <h1 class="page-title"><i class="fas fa-paper-plane text-primary me-2"></i>My Applications</h1>
        <p class="subtitle">Track and manage all your submitted job applications</p>
    </div>
    <div>
        <a href="<?= url('/student/jobs') ?>" class="btn btn-primary btn-sm fw-semibold">
            <i class="fas fa-search me-1"></i> Browse More Jobs
        </a>
    </div>
</div>

<?php if (empty($applications)): ?>
<div class="card shadow-sm border-0">
    <div class="card-body text-center p-5">
        <i class="fas fa-paper-plane text-muted mb-3" style="font-size:3rem; opacity:0.5;"></i>
        <h5 class="fw-bold">No Applications Submitted Yet</h5>
        <p class="text-muted small">Explore open placement drives and apply to top companies.</p>
        <a href="<?= url('/student/jobs') ?>" class="btn btn-primary btn-sm">Browse Active Jobs</a>
    </div>
</div>
<?php else: ?>

<!-- Applications Table Card -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
        <h6 class="mb-0 fw-bold"><i class="fas fa-list text-primary me-2"></i>Application Track Record</h6>
        <span class="badge bg-primary-soft text-primary fw-bold"><?= count($applications) ?> Total</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">Company</th>
                        <th>Job Role</th>
                        <th>Date Applied</th>
                        <th>Status</th>
                        <th>Resume Used</th>
                        <th>Details &amp; Schedule</th>
                        <th>Direct Chat</th>
                        <th class="pe-4 text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($applications as $app): ?>
                    <tr>
                        <!-- Company -->
                        <td class="ps-4">
                            <div class="d-flex align-items-center gap-3">
                                <img src="<?= $app['logo'] ? uploadUrl('company/' . $app['logo']) : asset('images/default-avatar.png') ?>" 
                                     alt="" class="rounded border p-1 bg-white" style="width:40px; height:40px; object-fit:cover;"
                                     onerror="this.src='<?= asset('images/default-avatar.png') ?>'">
                                <div>
                                    <div class="fw-bold text-dark" style="font-size:0.9rem;"><?= htmlspecialchars($app['company_name']) ?></div>
                                    <small class="text-muted"><i class="fas fa-map-marker-alt me-1"></i><?= htmlspecialchars($app['location'] ?? 'N/A') ?></small>
                                </div>
                            </div>
                        </td>

                        <!-- Job Role -->
                        <td>
                            <div class="fw-bold text-primary" style="font-size:0.92rem;"><?= htmlspecialchars($app['job_title']) ?></div>
                            <small class="text-muted"><?= JOB_TYPES[$app['job_type'] ?? 'full-time'] ?? ucfirst($app['job_type'] ?? 'full-time') ?></small>
                        </td>

                        <!-- Date Applied -->
                        <td>
                            <span class="fw-medium text-secondary" style="font-size:0.85rem;">
                                <i class="far fa-calendar-alt me-1 text-muted"></i><?= formatDate($app['applied_at']) ?>
                            </span>
                        </td>

                        <!-- Status -->
                        <td>
                            <span class="badge <?= getStatusBadgeClass($app['status']) ?> px-3 py-2" style="font-size:0.8rem;">
                                <?= ucfirst($app['status']) ?>
                            </span>
                        </td>

                        <!-- Resume Used -->
                        <td>
                            <?php if (!empty($app['resume_snapshot'])): ?>
                            <a href="<?= url('/student/preview-resume') ?>" target="_blank" class="btn btn-xs btn-outline-primary fw-semibold" style="font-size:0.75rem;">
                                <i class="fas fa-file-pdf text-danger me-1"></i>Submitted PDF
                            </a>
                            <?php else: ?>
                            <span class="text-muted small"><i class="fas fa-user-circle me-1"></i>Default Profile</span>
                            <?php endif; ?>
                        </td>

                        <!-- View Details & Interview Schedule -->
                        <td>
                            <?php if (!empty($app['interview_date'])): ?>
                            <div class="small fw-semibold text-primary">
                                <i class="far fa-calendar-check me-1"></i><?= formatDate($app['interview_date']) ?>
                            </div>
                            <div class="small text-muted"><?= date('h:i A', strtotime($app['interview_time'])) ?> (<?= htmlspecialchars($app['interview_round'] ?? 'Round 1') ?>)</div>
                            <?php elseif ($app['status'] === 'selected'): ?>
                            <span class="badge bg-success-soft text-success"><i class="fas fa-award me-1"></i>Offer Released</span>
                            <?php elseif ($app['status'] === 'rejected'): ?>
                            <span class="badge bg-danger-soft text-danger"><i class="fas fa-times-circle me-1"></i>Not Selected</span>
                            <?php else: ?>
                            <span class="text-muted small"><i class="fas fa-hourglass-half me-1"></i>Under Review</span>
                            <?php endif; ?>
                        </td>

                        <!-- Direct Chat HR -->
                        <td>
                            <?php if (!empty($app['company_user_id'])): ?>
                            <a href="<?= url('/student/messages?partner=' . $app['company_user_id']) ?>" class="btn btn-sm btn-outline-primary fw-semibold" title="Chat with HR">
                                <i class="fas fa-comments me-1"></i>Chat HR
                            </a>
                            <?php else: ?>
                            <span class="text-muted small">—</span>
                            <?php endif; ?>
                        </td>

                        <!-- Action Column -->
                        <td class="pe-4 text-end">
                            <div class="d-inline-flex gap-1 align-items-center justify-content-end">
                                <button type="button" 
                                        class="btn btn-sm btn-light border text-primary fw-semibold" 
                                        onclick='openAppDetailsModal(<?= htmlspecialchars(json_encode([
                                            "id" => $app["id"],
                                            "company_name" => $app["company_name"],
                                            "logo" => $app["logo"] ? uploadUrl("company/" . $app["logo"]) : asset("images/default-avatar.png"),
                                            "job_title" => $app["job_title"],
                                            "job_type" => JOB_TYPES[$app["job_type"] ?? "full-time"] ?? ucfirst($app["job_type"] ?? "full-time"),
                                            "location" => $app["location"] ?? "N/A",
                                            "salary" => formatSalaryRange($app["salary_min"], $app["salary_max"]),
                                            "applied_at" => formatDate($app["applied_at"]),
                                            "status" => ucfirst($app["status"]),
                                            "status_raw" => $app["status"],
                                            "status_badge" => getStatusBadgeClass($app["status"]),
                                            "resume_snapshot" => $app["resume_snapshot"] ?? null,
                                            "interview_date" => !empty($app["interview_date"]) ? formatDate($app["interview_date"]) : null,
                                            "interview_time" => !empty($app["interview_time"]) ? date("h:i A", strtotime($app["interview_time"])) : null,
                                            "interview_round" => $app["interview_round"] ?? "Round 1",
                                            "company_user_id" => $app["company_user_id"] ?? null,
                                            "job_id" => $app["job_id"],
                                            "job_status" => $app["job_status"] ?? "active",
                                            "work_mode" => ucfirst($app["work_mode"] ?? "onsite"),
                                            "description" => $app["job_description"] ?? "No description provided.",
                                            "skills" => $app["skills_required"] ?? ""
                                        ]), ENT_QUOTES, "UTF-8") ?>)' 
                                        title="View Application Details">
                                    <i class="fas fa-eye me-1"></i>Details
                                </button>

                                <?php if ($app['status'] === 'applied'): ?>
                                <a href="<?= url('/student/withdraw/' . $app['id']) ?>" 
                                   class="btn btn-sm btn-outline-danger fw-semibold" 
                                   data-confirm="Are you sure you want to withdraw your application for <?= htmlspecialchars($app['job_title']) ?>?" 
                                   title="Withdraw Application">
                                    <i class="fas fa-times me-1"></i>Withdraw
                                </a>
                                <?php elseif ($app['status'] === 'withdrawn'): ?>
                                    <?php if (($app['job_status'] ?? 'active') === 'active'): ?>
                                    <a href="<?= url('/student/apply/' . $app['job_id']) ?>" 
                                       class="btn btn-sm btn-outline-success fw-semibold" 
                                       data-confirm="Are you sure you want to re-apply for <?= htmlspecialchars($app['job_title']) ?>?" 
                                       title="Re-apply for Job">
                                        <i class="fas fa-redo me-1"></i>Re-apply
                                    </a>
                                    <?php else: ?>
                                    <span class="badge bg-secondary-soft text-muted px-2 py-1" style="font-size:0.75rem;">Withdrawn</span>
                                    <?php endif; ?>
                                <?php elseif ($app['status'] === 'interview'): ?>
                                <span class="badge bg-info-soft text-info fw-bold px-2 py-1" style="font-size:0.75rem;"><i class="fas fa-calendar-check me-1"></i>Interview</span>
                                <?php elseif ($app['status'] === 'shortlisted'): ?>
                                <span class="badge bg-warning-soft text-dark fw-bold px-2 py-1" style="font-size:0.75rem;"><i class="fas fa-star me-1"></i>Shortlisted</span>
                                <?php elseif ($app['status'] === 'selected'): ?>
                                <span class="badge bg-success-soft text-success fw-bold px-2 py-1" style="font-size:0.75rem;"><i class="fas fa-award me-1"></i>Selected</span>
                                <?php elseif ($app['status'] === 'rejected'): ?>
                                <span class="badge bg-danger-soft text-danger fw-bold px-2 py-1" style="font-size:0.75rem;"><i class="fas fa-times-circle me-1"></i>Rejected</span>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Application Summary Cards -->
<div class="row g-3">
    <?php
    $statusCounts = [];
    foreach ($applications as $a) { $statusCounts[$a['status']] = ($statusCounts[$a['status']] ?? 0) + 1; }
    $statColors = ['applied' => 'primary', 'shortlisted' => 'warning', 'interview' => 'info', 'selected' => 'success', 'rejected' => 'danger', 'withdrawn' => 'secondary'];
    foreach ($statColors as $status => $color): ?>
    <div class="col-md-2 col-sm-4">
        <div class="card shadow-sm border-0 text-center p-3">
            <div class="fw-bold fs-4 text-<?= $color ?>"><?= $statusCounts[$status] ?? 0 ?></div>
            <small class="text-muted text-uppercase fw-semibold" style="font-size:0.72rem;"><?= ucfirst($status) ?></small>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Application Details Modal -->
<div class="modal fade" id="appDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light py-3">
                <div class="d-flex align-items-center gap-3">
                    <img id="mAppLogo" src="" alt="" class="rounded border p-1 bg-white" style="width:48px; height:48px; object-fit:cover;">
                    <div>
                        <h5 class="modal-title fw-bold text-dark mb-0" id="mJobTitle"></h5>
                        <div class="small text-primary fw-semibold" id="mCompanyName"></div>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <!-- Status Banner -->
                <div class="d-flex align-items-center justify-content-between p-3 rounded mb-4 bg-light border">
                    <div>
                        <div class="text-muted small fw-semibold">Application Status</div>
                        <span id="mStatusBadge" class="badge fs-6 px-3 py-2 mt-1"></span>
                    </div>
                    <div class="text-end">
                        <div class="text-muted small fw-semibold">Applied Date</div>
                        <div class="fw-bold text-dark" id="mAppliedAt"></div>
                    </div>
                </div>

                <!-- Job Details Grid -->
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="p-3 bg-light rounded text-center">
                            <i class="fas fa-map-marker-alt text-danger mb-1 fs-5"></i>
                            <div class="small text-muted">Location</div>
                            <div class="fw-bold text-dark small" id="mLocation"></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 bg-light rounded text-center">
                            <i class="fas fa-money-bill-wave text-success mb-1 fs-5"></i>
                            <div class="small text-muted">Package</div>
                            <div class="fw-bold text-dark small" id="mSalary"></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 bg-light rounded text-center">
                            <i class="fas fa-briefcase text-primary mb-1 fs-5"></i>
                            <div class="small text-muted">Job Type</div>
                            <div class="fw-bold text-dark small" id="mJobType"></div>
                        </div>
                    </div>
                </div>

                <!-- Interview Schedule (If Present) -->
                <div id="mInterviewSec" class="card border-info mb-4 d-none">
                    <div class="card-header bg-info-soft py-2 fw-bold text-info small">
                        <i class="far fa-calendar-alt me-1"></i> Interview Schedule
                    </div>
                    <div class="card-body py-2 px-3 small">
                        <div class="row">
                            <div class="col-6"><strong>Round:</strong> <span id="mIntRound"></span></div>
                            <div class="col-6"><strong>Date &amp; Time:</strong> <span id="mIntDateTime"></span></div>
                        </div>
                    </div>
                </div>

                <!-- Job Description -->
                <div class="mb-3">
                    <h6 class="fw-bold text-dark"><i class="fas fa-align-left text-muted me-2"></i>Job Description</h6>
                    <div class="p-3 bg-light rounded text-secondary small" id="mDescription" style="white-space: pre-line;"></div>
                </div>
            </div>
            <div class="modal-footer bg-light py-2">
                <a id="mChatBtn" href="#" class="btn btn-outline-primary btn-sm me-auto d-none">
                    <i class="fas fa-comments me-1"></i>Chat with HR
                </a>
                <a id="mActionBtn" href="#" class="btn btn-sm"></a>
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
function openAppDetailsModal(app) {
    document.getElementById('mJobTitle').textContent = app.job_title;
    document.getElementById('mCompanyName').textContent = app.company_name;
    document.getElementById('mAppLogo').src = app.logo;
    document.getElementById('mAppliedAt').textContent = app.applied_at;
    
    var badge = document.getElementById('mStatusBadge');
    badge.textContent = app.status;
    badge.className = 'badge ' + app.status_badge + ' px-3 py-2';
    
    document.getElementById('mLocation').textContent = app.location;
    document.getElementById('mSalary').textContent = app.salary;
    document.getElementById('mJobType').textContent = app.job_type + ' (' + app.work_mode + ')';
    document.getElementById('mDescription').textContent = app.description;

    var intSec = document.getElementById('mInterviewSec');
    if (app.interview_date) {
        intSec.classList.remove('d-none');
        document.getElementById('mIntRound').textContent = app.interview_round;
        document.getElementById('mIntDateTime').textContent = app.interview_date + ' at ' + (app.interview_time || '');
    } else {
        intSec.classList.add('d-none');
    }

    var chatBtn = document.getElementById('mChatBtn');
    if (app.company_user_id) {
        chatBtn.classList.remove('d-none');
        chatBtn.href = TPMS.baseUrl + '/student/messages?partner=' + app.company_user_id;
    } else {
        chatBtn.classList.add('d-none');
    }

    var actBtn = document.getElementById('mActionBtn');
    if (app.status_raw === 'applied') {
        actBtn.classList.remove('d-none');
        actBtn.className = 'btn btn-outline-danger btn-sm';
        actBtn.innerHTML = '<i class="fas fa-times me-1"></i>Withdraw Application';
        actBtn.href = TPMS.baseUrl + '/student/withdraw/' + app.id;
        actBtn.setAttribute('data-confirm', 'Are you sure you want to withdraw your application?');
    } else if (app.status_raw === 'withdrawn' && app.job_status === 'active') {
        actBtn.classList.remove('d-none');
        actBtn.className = 'btn btn-outline-success btn-sm';
        actBtn.innerHTML = '<i class="fas fa-redo me-1"></i>Re-apply Now';
        actBtn.href = TPMS.baseUrl + '/student/apply/' + app.job_id;
        actBtn.setAttribute('data-confirm', 'Are you sure you want to re-apply for this job?');
    } else {
        actBtn.classList.add('d-none');
    }

    new bootstrap.Modal(document.getElementById('appDetailsModal')).show();
}
</script>

<?php endif; ?>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>
