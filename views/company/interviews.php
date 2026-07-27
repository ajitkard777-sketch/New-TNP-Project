<?php require_once ROOT_PATH . '/includes/header.php'; ?>

<div class="content-header mb-4">
    <div>
        <h1 class="page-title d-flex align-items-center gap-2">
            <i class="fas fa-calendar-alt text-primary"></i> Company Interview Scheduler
        </h1>
        <p class="subtitle">Schedule, manage, update, and track candidate interviews for your posted jobs</p>
    </div>
    <div>
        <button class="btn btn-primary px-4 shadow-sm" onclick="openScheduleModal()">
            <i class="fas fa-plus-circle me-1"></i> Schedule Interview
        </button>
    </div>
</div>

<!-- Stats Row -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-md">
        <div class="stat-card p-3 bg-white rounded-3 border shadow-xs text-center">
            <div class="stat-value text-primary fs-3 fw-bold"><?= $totalInterviews ?></div>
            <small class="stat-label text-muted text-uppercase font-medium" style="font-size:0.72rem;">Total Interviews</small>
        </div>
    </div>
    <div class="col-sm-6 col-md">
        <div class="stat-card p-3 bg-white rounded-3 border shadow-xs text-center">
            <div class="stat-value text-info fs-3 fw-bold"><?= $todayInterviews ?></div>
            <small class="stat-label text-muted text-uppercase font-medium" style="font-size:0.72rem;">Today's Interviews</small>
        </div>
    </div>
    <div class="col-sm-6 col-md">
        <div class="stat-card p-3 bg-white rounded-3 border shadow-xs text-center">
            <div class="stat-value text-warning fs-3 fw-bold"><?= $upcomingInterviews ?></div>
            <small class="stat-label text-muted text-uppercase font-medium" style="font-size:0.72rem;">Upcoming</small>
        </div>
    </div>
    <div class="col-sm-6 col-md">
        <div class="stat-card p-3 bg-white rounded-3 border shadow-xs text-center">
            <div class="stat-value text-success fs-3 fw-bold"><?= $completedInterviews ?></div>
            <small class="stat-label text-muted text-uppercase font-medium" style="font-size:0.72rem;">Completed</small>
        </div>
    </div>
    <div class="col-sm-6 col-md">
        <div class="stat-card p-3 bg-white rounded-3 border shadow-xs text-center">
            <div class="stat-value text-danger fs-3 fw-bold"><?= $cancelledInterviews ?></div>
            <small class="stat-label text-muted text-uppercase font-medium" style="font-size:0.72rem;">Cancelled</small>
        </div>
    </div>
</div>

<!-- Navigation Tabs: List View vs Calendar View -->
<ul class="nav nav-pills nav-fill mb-4 p-1 bg-light rounded-3 border" id="interviewTabs" role="tablist">
    <li class="nav-item">
        <button class="nav-link active fw-bold" id="list-tab" data-bs-toggle="tab" data-bs-target="#list-content">
            <i class="fas fa-list me-1"></i> Interview Schedule List (<?= count($interviews) ?>)
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link fw-bold" id="calendar-tab" data-bs-toggle="tab" data-bs-target="#calendar-content">
            <i class="fas fa-calendar-week me-1"></i> Interactive Calendar
        </button>
    </li>
</ul>

<!-- Tab Contents -->
<div class="tab-content" id="interviewTabContent">

    <!-- TAB 1: Interview List -->
    <div class="tab-pane fade show active" id="list-content">
        <div class="card border-0 shadow-sm" style="border-radius:1rem;">
            <div class="card-body p-0">
                <?php if (empty($interviews)): ?>
                <div class="text-center p-5 text-muted">
                    <i class="fas fa-calendar-times fs-1 opacity-50 mb-2"></i>
                    <h5 class="fw-bold">No Scheduled Interviews</h5>
                    <p>Click "Schedule Interview" to set up interview drives for shortlisted applicants.</p>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Candidate</th>
                                <th>Job Role</th>
                                <th>Round &amp; Mode</th>
                                <th>Date &amp; Time Slot</th>
                                <th>Venue / Meeting Link</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($interviews as $inv): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <img src="<?= $inv['profile_photo'] ? uploadUrl('profile_photos/' . $inv['profile_photo']) : asset('images/default-avatar.png') ?>" alt="" class="rounded-circle border" style="width: 40px; height: 40px; object-fit: cover;" onerror="this.src='<?= asset('images/default-avatar.png') ?>'">
                                        <div>
                                            <div class="fw-bold"><?= htmlspecialchars($inv['first_name'] . ' ' . $inv['last_name']) ?></div>
                                            <small class="text-muted"><?= htmlspecialchars($inv['branch']) ?> (CGPA: <?= $inv['cgpa'] ?>)</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-bold"><?= htmlspecialchars($inv['job_title']) ?></div>
                                </td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle me-1"><?= strtoupper($inv['round'] ?? 'ROUND 1') ?></span>
                                    <span class="badge bg-light text-dark border"><?= ucfirst($inv['mode'] ?? 'offline') ?></span>
                                </td>
                                <td>
                                    <div class="fw-semibold"><i class="fas fa-calendar-alt text-muted me-1"></i><?= formatDate($inv['interview_date']) ?></div>
                                    <small class="text-muted"><i class="fas fa-clock me-1"></i><?= date('h:i A', strtotime($inv['interview_time'] ?? '09:00:00')) ?></small>
                                </td>
                                <td>
                                    <?php if (!empty($inv['meeting_link'])): ?>
                                    <a href="<?= htmlspecialchars($inv['meeting_link']) ?>" target="_blank" class="btn btn-sm btn-outline-info rounded-pill px-2 py-0"><i class="fas fa-video me-1"></i> Virtual Link</a>
                                    <?php else: ?>
                                    <small class="text-muted"><i class="fas fa-map-marker-alt text-danger me-1"></i><?= htmlspecialchars($inv['venue'] ?: 'Onsite Office') ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge <?= getStatusBadgeClass($inv['status']) ?> px-3 py-2">
                                        <?= ucfirst($inv['status']) ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="dropdown">
                                        <button class="btn btn-light btn-sm rounded-circle" type="button" data-bs-toggle="dropdown"><i class="fas fa-ellipsis-v"></i></button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li><a class="dropdown-item" href="<?= url('/api/interview/pdf/' . $inv['id']) ?>" target="_blank"><i class="fas fa-file-pdf text-danger me-2"></i> Download Call Letter</a></li>
                                            <li><a class="dropdown-item" href="#" onclick="openFeedbackModal(<?= htmlspecialchars(json_encode($inv)) ?>)"><i class="fas fa-star text-warning me-2"></i> Record Feedback</a></li>
                                            <li><a class="dropdown-item" href="#" onclick="openRescheduleModal(<?= htmlspecialchars(json_encode($inv)) ?>)"><i class="fas fa-history text-info me-2"></i> Reschedule</a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item text-danger" href="#" onclick="openCancelModal(<?= $inv['id'] ?>)"><i class="fas fa-times me-2"></i> Cancel Interview</a></li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- TAB 2: Interactive Calendar -->
    <div class="tab-pane fade" id="calendar-content">
        <div class="card border-0 shadow-sm p-4" style="border-radius:1rem;">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0"><i class="fas fa-calendar text-primary me-2"></i>Scheduled Interview Events</h5>
                <div class="btn-group btn-group-sm" role="group">
                    <button class="btn btn-outline-secondary active" onclick="switchCalendarView('month')">Month</button>
                    <button class="btn btn-outline-secondary" onclick="switchCalendarView('week')">Week</button>
                    <button class="btn btn-outline-secondary" onclick="switchCalendarView('day')">Day</button>
                </div>
            </div>

            <!-- Simple Interactive Grid Calendar -->
            <div class="row g-3" id="calendarEventsGrid">
                <?php foreach ($interviews as $inv): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="p-3 bg-white rounded-3 border shadow-xs">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="badge bg-primary px-2 py-1"><?= formatDate($inv['interview_date']) ?></span>
                            <span class="badge <?= getStatusBadgeClass($inv['status']) ?>"><?= ucfirst($inv['status']) ?></span>
                        </div>
                        <h6 class="fw-bold mb-1"><?= htmlspecialchars($inv['first_name'] . ' ' . $inv['last_name']) ?></h6>
                        <div class="small text-muted mb-2"><i class="fas fa-briefcase me-1"></i><?= htmlspecialchars($inv['job_title']) ?></div>
                        <div class="small text-dark font-medium"><i class="fas fa-clock text-info me-1"></i><?= date('h:i A', strtotime($inv['interview_time'] ?? '09:00:00')) ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

</div>

<!-- Schedule Interview Modal -->
<div class="modal fade" id="scheduleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius:1.25rem;">
            <div class="modal-header bg-primary text-white border-0">
                <h5 class="modal-title fw-bold"><i class="fas fa-calendar-plus me-2"></i>Schedule Interview Drive</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form id="scheduleForm" onsubmit="handleScheduleSubmit(event)">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label font-semibold">Select Job Posting *</label>
                            <select class="form-select" id="schedJobSelect" required onchange="loadJobApplicants()">
                                <option value="">-- Choose Job --</option>
                                <?php foreach ($jobs as $j): ?>
                                <option value="<?= $j['id'] ?>"><?= htmlspecialchars($j['title']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label font-semibold">Interview Title *</label>
                            <input type="text" class="form-control" name="interview_title" value="Technical Round Drive" required>
                        </div>
                    </div>

                    <!-- Applicants Selection Table -->
                    <div class="mb-3">
                        <label class="form-label font-semibold">Select Shortlisted Candidate(s) *</label>
                        <div id="applicantsTableBox" class="border rounded-3 p-3 bg-light" style="max-height: 200px; overflow-y: auto;">
                            <div class="text-center text-muted py-3">Select a job posting above to load applicants</div>
                        </div>
                    </div>

                    <!-- Schedule Details -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-3">
                            <label class="form-label font-semibold">Interview Round</label>
                            <select class="form-select" name="interview_round">
                                <option value="Round 1">Round 1</option>
                                <option value="Round 2">Round 2</option>
                                <option value="Aptitude Round">Aptitude Round</option>
                                <option value="Technical Round">Technical Round</option>
                                <option value="HR Round">HR Round</option>
                                <option value="Managerial Round">Managerial Round</option>
                                <option value="Final Round">Final Round</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label font-semibold">Interview Mode</label>
                            <select class="form-select" name="interview_type" id="schedType" onchange="toggleVenueInput()">
                                <option value="online">Online (Virtual)</option>
                                <option value="offline">Offline (In-Person)</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label font-semibold">Interview Date *</label>
                            <input type="date" class="form-control" name="interview_date" min="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label font-semibold">Interview Time *</label>
                            <input type="time" class="form-control" name="interview_time" value="10:00" required>
                        </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6" id="venueBox" style="display:none;">
                            <label class="form-label font-semibold">Venue Location</label>
                            <input type="text" class="form-control" name="venue" placeholder="e.g. Conference Room 3, Building B">
                        </div>
                        <div class="col-md-6" id="meetingBox">
                            <label class="form-label font-semibold">Virtual Meeting Link</label>
                            <input type="url" class="form-control" name="meeting_link" placeholder="e.g. https://meet.google.com/abc-defg-hij">
                        </div>
                    </div>

                    <!-- Interviewer Info -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label font-semibold">Interviewer Name</label>
                            <input type="text" class="form-control" name="interviewer_name" placeholder="e.g. Rahul Sharma (Lead Engineer)">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label font-semibold">Interviewer Email</label>
                            <input type="email" class="form-control" name="interviewer_email" placeholder="e.g. interviewer@company.com">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label font-semibold">Dress Code</label>
                            <input type="text" class="form-control" name="dress_code" value="Formal Business Attire">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label font-semibold">Instructions for Candidates</label>
                        <textarea class="form-control" name="instructions" rows="2" placeholder="Please join 10 minutes prior and have your updated resume ready."></textarea>
                    </div>

                    <div class="modal-footer border-0 px-0 pb-0">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary px-4" id="submitSchedBtn">
                            <i class="fas fa-check-circle me-1"></i> Schedule Interview &amp; Notify Candidates
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Reschedule Modal -->
<div class="modal fade" id="rescheduleModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius:1rem;">
            <div class="modal-header bg-info text-white border-0">
                <h5 class="modal-title fw-bold">Reschedule Interview</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form id="rescheduleForm" onsubmit="handleRescheduleSubmit(event)">
                    <input type="hidden" name="interview_id" id="reschInvId">
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label font-semibold">New Interview Date *</label>
                            <input type="date" class="form-control" name="interview_date" min="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label font-semibold">New Time *</label>
                            <input type="time" class="form-control" name="interview_time" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label font-semibold">Reschedule Reason</label>
                        <textarea class="form-control" name="reason" rows="2" placeholder="Unavoidable panel scheduling conflict"></textarea>
                    </div>
                    <div class="text-end">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-info btn-sm text-white px-3">Confirm Reschedule</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Feedback Modal -->
<div class="modal fade" id="feedbackModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius:1rem;">
            <div class="modal-header bg-warning text-dark border-0">
                <h5 class="modal-title fw-bold"><i class="fas fa-star me-1"></i> Record Candidate Feedback</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form id="feedbackForm" onsubmit="handleFeedbackSubmit(event)">
                    <input type="hidden" name="interview_id" id="fbInvId">
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label small font-semibold">Technical Rating (1-10)</label>
                            <input type="number" min="1" max="10" class="form-control" name="technical_rating" value="8">
                        </div>
                        <div class="col-6">
                            <label class="form-label small font-semibold">Communication (1-10)</label>
                            <input type="number" min="1" max="10" class="form-control" name="communication_rating" value="8">
                        </div>
                        <div class="col-6">
                            <label class="form-label small font-semibold">Problem Solving (1-10)</label>
                            <input type="number" min="1" max="10" class="form-control" name="problem_solving_rating" value="8">
                        </div>
                        <div class="col-6">
                            <label class="form-label small font-semibold">Overall Rating (1-10)</label>
                            <input type="number" min="1" max="10" class="form-control" name="overall_rating" value="8">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label font-semibold">Recommendation Result</label>
                        <select class="form-select" name="result">
                            <option value="selected">Selected for Job Offer</option>
                            <option value="next_round">Proceed to Next Round</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label font-semibold">Interviewer Comments</label>
                        <textarea class="form-control" name="comments" rows="2" placeholder="Strong technical background and excellent problem solving skills."></textarea>
                    </div>
                    <div class="text-end">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning btn-sm px-3">Save Feedback</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function openScheduleModal() {
    const modal = new bootstrap.Modal(document.getElementById('scheduleModal'));
    modal.show();
}

function toggleVenueInput() {
    const type = document.getElementById('schedType').value;
    document.getElementById('venueBox').style.display = (type === 'offline' || type === 'hybrid') ? 'block' : 'none';
    document.getElementById('meetingBox').style.display = (type === 'online' || type === 'hybrid') ? 'block' : 'none';
}

function loadJobApplicants() {
    const jobId = document.getElementById('schedJobSelect').value;
    const box = document.getElementById('applicantsTableBox');

    if (!jobId) {
        box.innerHTML = '<div class="text-center text-muted py-3">Select a job posting above</div>';
        return;
    }

    box.innerHTML = '<div class="text-center text-muted py-3"><i class="fas fa-spinner fa-spin me-1"></i> Loading applicants...</div>';

    fetch('<?= url('/api/admin/eligible-students/') ?>' + jobId)
    .then(r => r.json())
    .then(data => {
        if (data.success && data.students && data.students.length > 0) {
            let html = `
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th width="30"><input type="checkbox" onchange="toggleSelectAllSched(this)"></th>
                            <th>Candidate</th>
                            <th>Branch</th>
                            <th>CGPA</th>
                            <th>Match Score</th>
                        </tr>
                    </thead>
                    <tbody>
            `;
            data.students.forEach(s => {
                html += `
                    <tr>
                        <td><input type="checkbox" class="sched-cand-cb" name="student_ids[]" value="${s.student_id}"></td>
                        <td class="fw-bold">${s.name}</td>
                        <td>${s.branch}</td>
                        <td>${s.cgpa}</td>
                        <td><span class="badge bg-success">${s.eligibility_score}%</span></td>
                    </tr>
                `;
            });
            html += `</tbody></table>`;
            box.innerHTML = html;
        } else {
            box.innerHTML = '<div class="alert alert-warning mb-0">No applicants found for this job posting.</div>';
        }
    })
    .catch(err => {
        box.innerHTML = '<div class="alert alert-danger mb-0">Failed to load applicants.</div>';
    });
}

function toggleSelectAllSched(master) {
    document.querySelectorAll('.sched-cand-cb').forEach(cb => cb.checked = master.checked);
}

function handleScheduleSubmit(e) {
    e.preventDefault();
    const form = document.getElementById('scheduleForm');
    const formData = new FormData(form);
    formData.append('csrf_token', '<?= $_SESSION['csrf_token'] ?? '' ?>');

    const btn = document.getElementById('submitSchedBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Scheduling...';

    fetch('<?= url('/api/company/interviews') ?>', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-check-circle me-1"></i> Schedule Interview & Notify Candidates';
        if (data.success) {
            TPMS.toast('success', data.message);
            setTimeout(() => window.location.reload(), 600);
        } else {
            TPMS.toast('danger', data.error || 'Scheduling failed');
        }
    })
    .catch(err => {
        btn.disabled = false;
        TPMS.toast('danger', 'Network error.');
    });
}

function openRescheduleModal(inv) {
    document.getElementById('reschInvId').value = inv.id;
    const modal = new bootstrap.Modal(document.getElementById('rescheduleModal'));
    modal.show();
}

function handleRescheduleSubmit(e) {
    e.preventDefault();
    const formData = new FormData(document.getElementById('rescheduleForm'));
    formData.append('csrf_token', '<?= $_SESSION['csrf_token'] ?? '' ?>');

    fetch('<?= url('/api/company/interviews/reschedule') ?>', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            TPMS.toast('success', data.message);
            setTimeout(() => window.location.reload(), 600);
        } else {
            TPMS.toast('danger', data.error || 'Reschedule failed');
        }
    });
}

function openFeedbackModal(inv) {
    document.getElementById('fbInvId').value = inv.id;
    const modal = new bootstrap.Modal(document.getElementById('feedbackModal'));
    modal.show();
}

function handleFeedbackSubmit(e) {
    e.preventDefault();
    const formData = new FormData(document.getElementById('feedbackForm'));
    formData.append('csrf_token', '<?= $_SESSION['csrf_token'] ?? '' ?>');

    fetch('<?= url('/api/company/interviews/feedback') ?>', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            TPMS.toast('success', data.message);
            setTimeout(() => window.location.reload(), 600);
        } else {
            TPMS.toast('danger', data.error || 'Feedback save failed');
        }
    });
}

function openCancelModal(invId) {
    const reason = prompt('Please enter cancellation reason:');
    if (reason === null) return;

    fetch('<?= url('/api/company/interviews/cancel') ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `interview_id=${invId}&reason=${encodeURIComponent(reason)}&csrf_token=<?= $_SESSION['csrf_token'] ?? '' ?>`
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            TPMS.toast('success', data.message);
            setTimeout(() => window.location.reload(), 600);
        } else {
            TPMS.toast('danger', data.error || 'Cancel failed');
        }
    });
}
</script>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>
