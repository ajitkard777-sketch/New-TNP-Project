<?php require_once ROOT_PATH . '/includes/header.php'; ?>

<div class="content-header mb-4">
    <div>
        <h1 class="page-title d-flex align-items-center gap-2">
            <i class="fas fa-calendar-check text-primary"></i> My Scheduled Interviews
        </h1>
        <p class="subtitle">Track your upcoming recruitment drives, download official call letters, and join virtual interview links</p>
    </div>
</div>

<?php if (empty($interviews)): ?>
<div class="card p-5 text-center empty-state border-0 shadow-sm" style="border-radius: 1rem;">
    <i class="fas fa-calendar-minus text-muted" style="font-size: 3.5rem; opacity: 0.5;"></i>
    <h5 class="mt-3 fw-bold">No Scheduled Interviews Yet</h5>
    <p class="text-muted">When a company shortlists you and schedules an interview, your call letters and schedules will appear here.</p>
    <div>
        <a href="<?= url('/student/jobs') ?>" class="btn btn-primary btn-sm px-4"><i class="fas fa-search me-1"></i> Browse Jobs</a>
    </div>
</div>
<?php else: ?>

<!-- Interview Cards List -->
<div class="row g-4">
    <?php foreach ($interviews as $inv): ?>
    <div class="col-lg-6">
        <div class="card h-100 border-0 shadow-sm hover-shadow animate-fade-in-up" style="border-radius: 1rem; transition: transform 0.2s;">
            <div class="card-body p-4 d-flex flex-column justify-content-between">
                <div>
                    <!-- Header: Company Logo & Status Badge -->
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="d-flex gap-3 align-items-center">
                            <img src="<?= $inv['logo'] ? uploadUrl('company/' . $inv['logo']) : asset('images/default-avatar.png') ?>" alt="" class="job-company-logo rounded-3 border" style="width: 52px; height: 52px; object-fit: contain; background: #fff; padding: 3px;" onerror="this.src='<?= asset('images/default-avatar.png') ?>'">
                            <div>
                                <h5 class="fw-bold mb-1" style="font-size: 1.05rem;"><?= htmlspecialchars($inv['job_title'] ?? $inv['interview_title'] ?? 'Interview Drive') ?></h5>
                                <div class="text-muted font-medium" style="font-size: 0.85rem;"><i class="fas fa-building me-1"></i><?= htmlspecialchars($inv['company_name']) ?></div>
                            </div>
                        </div>

                        <!-- Status Badge -->
                        <span class="badge <?= getStatusBadgeClass($inv['status']) ?> px-3 py-2 fw-bold" style="font-size:0.8rem;">
                            <?= ucfirst($inv['status']) ?>
                        </span>
                    </div>

                    <!-- Meta info badges -->
                    <div class="d-flex flex-wrap gap-2 mb-3" style="font-size: 0.82rem;">
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle fw-bold">
                            <i class="fas fa-layer-group me-1"></i><?= strtoupper($inv['round'] ?? $inv['interview_round'] ?? 'ROUND 1') ?> ROUND
                        </span>
                        <span class="badge bg-light text-dark border">
                            <i class="fas fa-calendar-alt text-danger me-1"></i><?= formatDate($inv['interview_date']) ?>
                        </span>
                        <span class="badge bg-light text-dark border">
                            <i class="fas fa-clock text-info me-1"></i><?= date('h:i A', strtotime($inv['interview_time'] ?? $inv['start_time'] ?? '09:00:00')) ?>
                        </span>
                        <span class="badge bg-light text-dark border">
                            <i class="fas fa-globe text-secondary me-1"></i><?= htmlspecialchars($inv['timezone'] ?? 'IST') ?>
                        </span>
                    </div>

                    <!-- Location / Virtual Meeting Link -->
                    <div class="p-3 bg-light rounded-3 mb-3" style="font-size: 0.85rem;">
                        <?php if (!empty($inv['meeting_link'])): ?>
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-muted font-semibold">Virtual Meeting Link:</span><br>
                                <a href="<?= htmlspecialchars($inv['meeting_link']) ?>" target="_blank" class="text-primary fw-bold text-break"><?= htmlspecialchars($inv['meeting_link']) ?></a>
                            </div>
                            <a href="<?= htmlspecialchars($inv['meeting_link']) ?>" target="_blank" class="btn btn-sm btn-primary px-3 text-nowrap"><i class="fas fa-video me-1"></i> Join Meeting</a>
                        </div>
                        <?php else: ?>
                        <div>
                            <span class="text-muted font-semibold">In-Person Venue:</span><br>
                            <span class="fw-bold text-dark"><i class="fas fa-map-marker-alt text-danger me-1"></i><?= htmlspecialchars($inv['venue'] ?: 'Company Headquarters Office') ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Action Buttons: Download Call Letter PDF, Add to Calendar, View Details -->
                <div class="pt-3 border-top d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <div class="d-flex gap-2">
                        <!-- Download Call Letter PDF Button -->
                        <a href="<?= url('/api/interview/pdf/' . $inv['id']) ?>" target="_blank" class="btn btn-outline-danger btn-sm px-3" title="Download Interview Call Letter PDF">
                            <i class="fas fa-file-pdf me-1"></i> Download PDF
                        </a>

                        <!-- Add to Google Calendar -->
                        <a href="<?= buildGoogleCalendarUrl($inv) ?>" target="_blank" class="btn btn-outline-secondary btn-sm" title="Add to Google Calendar">
                            <i class="fab fa-google me-1"></i> Add to Calendar
                        </a>
                    </div>

                    <button class="btn btn-outline-primary btn-sm" onclick="viewInterviewDetails(<?= htmlspecialchars(json_encode($inv)) ?>)">
                        <i class="fas fa-eye me-1"></i> Details
                    </button>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Interview Details Modal -->
<div class="modal fade" id="interviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 1rem;">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold" id="invModalTitle">Interview Call Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" id="invModalBody">
            </div>
        </div>
    </div>
</div>

<?php
function buildGoogleCalendarUrl(array $inv): string {
    $title   = urlencode('Interview: ' . ($inv['job_title'] ?? 'Job Drive') . ' at ' . $inv['company_name']);
    $dateStr = date('Ymd', strtotime($inv['interview_date']));
    // Use actual DB column interview_time; accept start_time as forward-compat fallback
    $rawTime  = $inv['interview_time'] ?? $inv['start_time'] ?? '09:00:00';
    $startStr = date('His', strtotime($rawTime));
    // No end_time in DB — default to 1 hour after start
    $endStr   = !empty($inv['end_time']) ? date('His', strtotime($inv['end_time'])) : date('His', strtotime($rawTime) + 3600);

    $dates    = $dateStr . 'T' . $startStr . '/' . $dateStr . 'T' . $endStr;
    $details  = urlencode('Official Interview Drive scheduled via TPMS. Round: ' . strtoupper($inv['round'] ?? $inv['interview_round'] ?? 'Technical'));
    $location = urlencode(!empty($inv['meeting_link']) ? $inv['meeting_link'] : ($inv['venue'] ?? 'Campus Placement Office'));

    return "https://calendar.google.com/calendar/render?action=TEMPLATE&text={$title}&dates={$dates}&details={$details}&location={$location}";
}
?>

<script>
function viewInterviewDetails(inv) {
    const modalTitle = document.getElementById('invModalTitle');
    const modalBody = document.getElementById('invModalBody');
    modalTitle.innerText = inv.company_name + ' - Interview Call';

    const invIdFormatted = 'INV-' + String(inv.id).padStart(6, '0');

    let html = `
        <div class="p-3 bg-light rounded-3 mb-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted font-semibold" style="font-size:0.8rem">INTERVIEW CALL ID</span>
                <span class="badge bg-primary fs-6">${invIdFormatted}</span>
            </div>
            <div class="d-flex justify-content-between align-items-center">
                <span class="text-muted font-semibold" style="font-size:0.8rem">STATUS</span>
                <span class="fw-bold text-success" style="text-transform:uppercase;">${inv.status}</span>
            </div>
        </div>

        <div class="mb-3">
            <div class="text-muted font-semibold" style="font-size:0.8rem">JOB ROLE &amp; ROUND</div>
            <div class="fw-bold">${inv.job_title || inv.interview_title} (${(inv.interview_round || 'technical').toUpperCase()} ROUND)</div>
        </div>

        <div class="mb-3">
            <div class="text-muted font-semibold" style="font-size:0.8rem">SCHEDULED TIME</div>
            <div>${inv.interview_date} at ${inv.start_time || '09:00 AM'} (${inv.timezone || 'IST'})</div>
        </div>

        <div class="mb-3">
            <div class="text-muted font-semibold" style="font-size:0.8rem">INTERVIEWER CONTACT</div>
            <div>${inv.interviewer_name || 'HR Recruitment Panel'} (${inv.interviewer_email || 'N/A'})</div>
        </div>

        ${inv.instructions ? `
        <div class="mb-3 p-3 bg-warning-subtle text-warning-emphasis rounded-3">
            <div class="font-semibold" style="font-size:0.8rem">CANDIDATE INSTRUCTIONS</div>
            <small>${inv.instructions}</small>
        </div>` : ''}

        <div class="d-flex justify-content-end gap-2 pt-3 border-top">
            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            <a href="<?= url('/api/interview/pdf/') ?>${inv.id}" target="_blank" class="btn btn-danger btn-sm"><i class="fas fa-file-pdf me-1"></i> Download PDF Call Letter</a>
        </div>
    `;

    modalBody.innerHTML = html;
    const modal = new bootstrap.Modal(document.getElementById('interviewModal'));
    modal.show();
}
</script>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>
