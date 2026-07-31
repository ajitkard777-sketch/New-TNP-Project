<?php require_once ROOT_PATH . '/includes/header.php'; ?>

<div class="content-header d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
    <div>
        <h1 class="page-title mb-1"><i class="fas fa-calendar-check text-primary me-2"></i>Interview Schedule</h1>
        <p class="subtitle mb-0">Track your interview rounds, join live online meetings, and review past performance.</p>
    </div>
</div>

<?php if (empty($interviews)): ?>
<div class="card shadow-sm border-0">
    <div class="card-body empty-state text-center py-5">
        <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
        <h5>No Interviews Scheduled Yet</h5>
        <p class="text-muted">Interviews will appear here automatically when companies shortlist your applications.</p>
        <a href="<?= url('/student/jobs') ?>" class="btn btn-primary btn-sm mt-2"><i class="fas fa-search me-1"></i> Browse Jobs & Apply</a>
    </div>
</div>
<?php else: ?>

<?php
$today = date('Y-m-d');
$upcoming = array_values(array_filter($interviews, fn($i) => strtolower($i['status']) !== 'rejected' && strtolower($i['status']) !== 'cancelled' && $i['interview_date'] >= $today));
$past     = array_values(array_filter($interviews, fn($i) => !in_array($i, $upcoming)));
$nextInterview = !empty($upcoming) ? $upcoming[0] : null;

$totalCount = count($interviews);
$upcomingCount = count($upcoming);
$passedCount = count(array_filter($interviews, fn($i) => strtolower($i['result'] ?? '') === 'passed' || strtolower($i['status']) === 'selected'));
$pastCount = count($past);
?>

<!-- Next Immediate Interview Hero Highlight Card (Broad View) -->
<?php if ($nextInterview): ?>
<div class="card shadow-sm border-0 mb-4 overflow-hidden" style="background: var(--bg-card); border-left: 5px solid var(--primary) !important;">
    <div class="card-body p-4">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
            <span class="badge bg-primary px-3 py-2 text-uppercase fw-semibold" style="letter-spacing: 0.5px; font-size: 0.75rem;">
                <i class="fas fa-star me-1"></i> Next Upcoming Interview
            </span>
            <span class="interview-countdown badge bg-dark px-3 py-2 font-monospace" data-date="<?= $nextInterview['interview_date'] ?>T<?= $nextInterview['interview_time'] ?>" style="font-size: 0.8rem;">
                <i class="fas fa-hourglass-half me-1 text-warning"></i> <span class="countdown-text">Calculating...</span>
            </span>
        </div>

        <div class="row align-items-center g-4">
            <div class="col-lg-7">
                <div class="d-flex align-items-start gap-3">
                    <?php if ($nextInterview['logo']): ?>
                    <img src="<?= uploadUrl('logos/' . $nextInterview['logo']) ?>" alt="" class="rounded border p-1 flex-shrink-0" style="width: 60px; height: 60px; object-fit: contain; background: #fff;" onerror="this.src='<?= asset('images/default-company.png') ?>'">
                    <?php else: ?>
                    <div class="rounded border p-2 flex-shrink-0 bg-primary-soft text-primary d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                        <i class="fas fa-building fs-3"></i>
                    </div>
                    <?php endif; ?>
                    <div>
                        <h4 class="fw-bold mb-1" style="color: var(--text-primary);"><?= htmlspecialchars($nextInterview['job_title']) ?></h4>
                        <div class="text-primary fw-semibold fs-6 mb-2">
                            <i class="fas fa-building me-1 opacity-75"></i><?= htmlspecialchars($nextInterview['company_name']) ?>
                            <?php if ($nextInterview['job_location']): ?>
                            <span class="text-muted small fw-normal ms-2"><i class="fas fa-map-marker-alt me-1"></i><?= htmlspecialchars($nextInterview['job_location']) ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="d-flex align-items-center gap-2 flex-wrap text-muted small">
                            <span class="badge bg-light text-dark border"><i class="fas fa-layer-group me-1 text-primary"></i><?= htmlspecialchars($nextInterview['round'] ?? 'Round 1') ?></span>
                            <span class="badge bg-light text-dark border"><i class="fas fa-video me-1 text-info"></i><?= ucfirst($nextInterview['mode'] ?? 'online') ?></span>
                            <?php if ($nextInterview['salary_min'] || $nextInterview['salary_max']): ?>
                            <span class="badge bg-light text-success border"><i class="fas fa-money-bill-wave me-1"></i><?= formatSalaryRange($nextInterview['salary_min'], $nextInterview['salary_max']) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="p-3 bg-light rounded border">
                    <div class="row g-2 mb-3 small">
                        <div class="col-6">
                            <div class="text-muted"><i class="far fa-calendar text-primary me-1"></i>Date</div>
                            <div class="fw-bold text-dark fs-6"><?= formatDate($nextInterview['interview_date']) ?></div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted"><i class="far fa-clock text-primary me-1"></i>Time</div>
                            <div class="fw-bold text-dark fs-6"><?= date('h:i A', strtotime($nextInterview['interview_time'])) ?></div>
                        </div>
                    </div>

                    <?php if (!empty($nextInterview['instructions'])): ?>
                    <div class="p-2 mb-3 rounded bg-warning bg-opacity-10 border border-warning text-dark small" style="font-size: 0.8rem;">
                        <i class="fas fa-info-circle text-warning me-1"></i> <strong>Note:</strong> <?= htmlspecialchars($nextInterview['instructions']) ?>
                    </div>
                    <?php endif; ?>

                    <div class="d-grid gap-2">
                        <?php if (!empty($nextInterview['meeting_link'])): ?>
                        <a href="<?= htmlspecialchars($nextInterview['meeting_link']) ?>" target="_blank" class="btn btn-primary fw-bold shadow-sm d-flex align-items-center justify-content-center gap-2">
                            <i class="fas fa-video fs-5"></i> Join Live Online Meeting
                        </a>
                        <?php else: ?>
                        <div class="alert alert-secondary text-center py-2 mb-0 small">
                            <i class="fas fa-info-circle me-1"></i> Mode: <?= htmlspecialchars($nextInterview['venue'] ?? 'Online link pending') ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Tabs Switcher for Broad Table View vs Upcoming Cards -->
<ul class="nav nav-tabs mb-4" id="interviewTabs">
    <li class="nav-item">
        <a class="nav-link active fw-semibold" data-bs-toggle="tab" href="#tabUpcoming">
            <i class="fas fa-clock me-2"></i>Upcoming Rounds (<?= count($upcoming) ?>)
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link fw-semibold" data-bs-toggle="tab" href="#tabAllHistory">
            <i class="fas fa-list-alt me-2"></i>All Interviews &amp; History (<?= count($interviews) ?>)
        </a>
    </li>
</ul>

<div class="tab-content">
    
    <!-- 1. UPCOMING INTERVIEWS TAB -->
    <div class="tab-pane fade show active" id="tabUpcoming">
        <?php if (empty($upcoming)): ?>
        <div class="card shadow-sm border"><div class="card-body text-center py-4 text-muted"><i class="fas fa-check-circle fa-2x mb-2"></i><h6>No upcoming interview rounds right now.</h6></div></div>
        <?php else: ?>
        <div class="row g-4">
            <?php foreach ($upcoming as $idx => $i): ?>
            <div class="col-lg-6">
                <div class="card shadow-sm border h-100 hover-scale">
                    <div class="card-body p-4 d-flex flex-column justify-content-between">
                        <div>
                            <div class="d-flex align-items-start justify-content-between gap-2 mb-3">
                                <div>
                                    <h5 class="fw-bold mb-1" style="color: var(--text-primary);"><?= htmlspecialchars($i['job_title']) ?></h5>
                                    <div class="text-primary fw-semibold small"><i class="fas fa-building me-1"></i><?= htmlspecialchars($i['company_name']) ?></div>
                                </div>
                                <span class="interview-countdown badge bg-primary bg-opacity-10 text-primary border border-primary px-2.5 py-1.5" data-date="<?= $i['interview_date'] ?>T<?= $i['interview_time'] ?>" style="font-size: 0.75rem;">
                                    <i class="fas fa-hourglass-half me-1"></i> <span class="countdown-text">Calculating...</span>
                                </span>
                            </div>

                            <div class="row g-2 mb-3 bg-light p-3 rounded border text-muted small">
                                <div class="col-6">
                                    <div class="fw-semibold text-dark"><i class="fas fa-layer-group text-primary me-1"></i>Round:</div>
                                    <div><?= htmlspecialchars($i['round'] ?? 'Round 1') ?></div>
                                </div>
                                <div class="col-6">
                                    <div class="fw-semibold text-dark"><i class="fas fa-video text-info me-1"></i>Mode:</div>
                                    <div><?= ucfirst($i['mode'] ?? 'online') ?></div>
                                </div>
                                <div class="col-6 mt-2">
                                    <div class="fw-semibold text-dark"><i class="far fa-calendar text-primary me-1"></i>Date:</div>
                                    <div><?= formatDate($i['interview_date']) ?></div>
                                </div>
                                <div class="col-6 mt-2">
                                    <div class="fw-semibold text-dark"><i class="far fa-clock text-primary me-1"></i>Time:</div>
                                    <div><?= date('h:i A', strtotime($i['interview_time'])) ?></div>
                                </div>
                            </div>

                            <?php if (!empty($i['instructions'])): ?>
                            <div class="p-2 mb-3 rounded bg-info bg-opacity-10 text-info border border-info small" style="font-size: 0.78rem;">
                                <i class="fas fa-info-circle me-1"></i> <?= htmlspecialchars($i['instructions']) ?>
                            </div>
                            <?php endif; ?>
                        </div>

                        <div class="mt-3 pt-3 border-top d-flex align-items-center justify-content-between gap-2 flex-wrap">
                            <button type="button" class="btn btn-xs btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#interviewDetailModal<?= $i['id'] ?>">
                                <i class="fas fa-eye me-1"></i> Full Info
                            </button>

                            <?php if (!empty($i['meeting_link'])): ?>
                            <a href="<?= htmlspecialchars($i['meeting_link']) ?>" target="_blank" class="btn btn-sm btn-primary fw-semibold px-3">
                                <i class="fas fa-video me-1"></i> Join Meeting
                            </a>
                            <?php else: ?>
                            <span class="text-muted small"><i class="fas fa-info-circle me-1"></i>Link pending</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- 2. ALL INTERVIEWS BROAD TABLE TAB -->
    <div class="tab-pane fade" id="tabAllHistory">
        <div class="card shadow-sm border">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0 align-middle table-hover">
                        <thead class="table-light">
                            <tr>
                                <th style="padding-left: 20px;">Company &amp; Job Role</th>
                                <th>Round</th>
                                <th>Date &amp; Time</th>
                                <th>Mode / Link</th>
                                <th>Countdown / Status</th>
                                <th>Result</th>
                                <th style="text-align: right; padding-right: 20px;">Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($interviews as $i): ?>
                            <tr>
                                <td style="padding-left: 20px;">
                                    <div class="d-flex align-items-center gap-3">
                                        <?php if ($i['logo']): ?>
                                        <img src="<?= uploadUrl('logos/' . $i['logo']) ?>" alt="" class="rounded border p-1 flex-shrink-0" style="width: 40px; height: 40px; object-fit: contain;" onerror="this.src='<?= asset('images/default-company.png') ?>'">
                                        <?php else: ?>
                                        <div class="rounded border p-2 flex-shrink-0 bg-light text-primary d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                            <i class="fas fa-building"></i>
                                        </div>
                                        <?php endif; ?>
                                        <div>
                                            <div class="fw-bold text-dark"><?= htmlspecialchars($i['job_title']) ?></div>
                                            <div class="text-primary small fw-semibold"><?= htmlspecialchars($i['company_name']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border font-monospace"><?= htmlspecialchars($i['round'] ?? 'Round 1') ?></span>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark small"><?= formatDate($i['interview_date']) ?></div>
                                    <div class="text-muted small"><i class="far fa-clock me-1"></i><?= date('h:i A', strtotime($i['interview_time'])) ?></div>
                                </td>
                                <td>
                                    <?php if (!empty($i['meeting_link'])): ?>
                                    <a href="<?= htmlspecialchars($i['meeting_link']) ?>" target="_blank" class="btn btn-xs btn-outline-primary fw-semibold d-inline-flex align-items-center gap-1">
                                        <i class="fas fa-video text-info"></i> Join Link
                                    </a>
                                    <?php else: ?>
                                    <span class="text-muted small"><i class="fas fa-map-marker-alt me-1"></i><?= htmlspecialchars($i['venue'] ?? 'Offline') ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($i['interview_date'] >= $today && strtolower($i['status']) !== 'rejected'): ?>
                                    <span class="interview-countdown badge bg-primary bg-opacity-10 text-primary border border-primary px-2 py-1" data-date="<?= $i['interview_date'] ?>T<?= $i['interview_time'] ?>" style="font-size: 0.72rem;">
                                        <i class="fas fa-hourglass-half me-1"></i> <span class="countdown-text">...</span>
                                    </span>
                                    <?php else: ?>
                                    <span class="badge <?= getStatusBadgeClass($i['status']) ?>"><?= ucfirst($i['status']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($i['result']) && $i['result'] !== 'pending'): ?>
                                    <span class="badge <?= $i['result'] === 'passed' ? 'bg-success' : 'bg-danger' ?>" style="font-size: 0.75rem;">
                                        <i class="fas fa-<?= $i['result'] === 'passed' ? 'check-circle' : 'times-circle' ?> me-1"></i> <?= ucfirst($i['result']) ?>
                                    </span>
                                    <?php else: ?>
                                    <span class="text-muted small">Pending</span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: right; padding-right: 20px;">
                                    <button type="button" class="btn btn-xs btn-light border" data-bs-toggle="modal" data-bs-target="#interviewDetailModal<?= $i['id'] ?>">
                                        <i class="fas fa-info-circle text-primary me-1"></i> Full Info
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Full Info Modals for Every Interview -->
<?php foreach ($interviews as $i): ?>
<div class="modal fade" id="interviewDetailModal<?= $i['id'] ?>" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-gradient-primary text-white" style="background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%);">
                <h5 class="modal-title text-white">
                    <i class="fas fa-calendar-check me-2"></i>Interview Full Details — <?= htmlspecialchars($i['job_title']) ?>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="d-flex align-items-center gap-3 mb-4 p-3 bg-light rounded border">
                    <?php if ($i['logo']): ?>
                    <img src="<?= uploadUrl('logos/' . $i['logo']) ?>" alt="" class="rounded border p-1 bg-white" style="width: 54px; height: 54px; object-fit: contain;" onerror="this.src='<?= asset('images/default-company.png') ?>'">
                    <?php else: ?>
                    <div class="rounded border p-2 bg-white text-primary d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
                        <i class="fas fa-building fs-3"></i>
                    </div>
                    <?php endif; ?>
                    <div>
                        <h5 class="fw-bold mb-0 text-dark"><?= htmlspecialchars($i['company_name']) ?></h5>
                        <div class="text-primary fw-semibold small"><?= htmlspecialchars($i['job_title']) ?></div>
                        <?php if ($i['company_website']): ?>
                        <a href="<?= htmlspecialchars($i['company_website']) ?>" target="_blank" class="text-muted small"><i class="fas fa-external-link-alt me-1"></i><?= htmlspecialchars($i['company_website']) ?></a>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="p-3 border rounded bg-white">
                            <div class="text-muted small fw-semibold text-uppercase mb-1"><i class="fas fa-layer-group text-primary me-1"></i> Round Details</div>
                            <div class="fw-bold fs-6 text-dark"><?= htmlspecialchars($i['round'] ?? 'Round 1') ?></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 border rounded bg-white">
                            <div class="text-muted small fw-semibold text-uppercase mb-1"><i class="fas fa-globe text-info me-1"></i> Mode of Interview</div>
                            <div class="fw-bold fs-6 text-dark"><?= ucfirst($i['mode'] ?? 'online') ?></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 border rounded bg-white">
                            <div class="text-muted small fw-semibold text-uppercase mb-1"><i class="far fa-calendar text-primary me-1"></i> Interview Date</div>
                            <div class="fw-bold fs-6 text-dark"><?= formatDate($i['interview_date']) ?></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 border rounded bg-white">
                            <div class="text-muted small fw-semibold text-uppercase mb-1"><i class="far fa-clock text-primary me-1"></i> Interview Time</div>
                            <div class="fw-bold fs-6 text-dark"><?= date('h:i A', strtotime($i['interview_time'])) ?></div>
                        </div>
                    </div>
                </div>

                <?php if (!empty($i['instructions'])): ?>
                <div class="mb-4">
                    <label class="form-label fw-bold"><i class="fas fa-info-circle text-primary me-1"></i> Company Instructions &amp; Guidelines</label>
                    <div class="p-3 bg-light rounded border text-dark">
                        <?= nl2br(htmlspecialchars($i['instructions'])) ?>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (!empty($i['meeting_link'])): ?>
                <div class="p-3 mb-3 rounded bg-primary bg-opacity-10 border border-primary d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div>
                        <div class="fw-bold text-primary"><i class="fas fa-video me-1"></i> Live Online Meeting Link</div>
                        <small class="text-muted font-monospace"><?= htmlspecialchars($i['meeting_link']) ?></small>
                    </div>
                    <a href="<?= htmlspecialchars($i['meeting_link']) ?>" target="_blank" class="btn btn-primary font-semibold">
                        <i class="fas fa-video me-1"></i> Join Meeting
                    </a>
                </div>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<?php endforeach; ?>

<?php endif; ?>

<script>
// Countdown timer for upcoming interviews
document.querySelectorAll('.interview-countdown').forEach(el => {
    const dateStr = el.dataset.date;
    const target  = new Date(dateStr).getTime();
    const textEl  = el.querySelector('.countdown-text');

    function update() {
        const now  = Date.now();
        const diff = target - now;
        if (diff <= 0) {
            textEl.textContent = 'Live / Started';
            el.className = 'interview-countdown badge bg-success text-white px-2 py-1';
            return;
        }
        const d  = Math.floor(diff / 86400000);
        const h  = Math.floor((diff % 86400000) / 3600000);
        const m  = Math.floor((diff % 3600000) / 60000);
        if (d > 0) textEl.textContent = `Starts in ${d}d ${h}h`;
        else if (h > 0) textEl.textContent = `Starts in ${h}h ${m}m`;
        else textEl.textContent = `Starts in ${m}m`;
    }

    update();
    setInterval(update, 30000);
});
</script>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>
