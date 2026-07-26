<?php require_once ROOT_PATH . '/includes/header.php'; ?>

<div class="content-header d-flex flex-wrap justify-content-between align-items-center gap-3">
    <div>
        <h1 class="page-title"><i class="fas fa-calendar-check text-primary me-2"></i>My Interview Schedule</h1>
        <p class="subtitle">View upcoming interviews, live countdown timers, call letters, and guidelines.</p>
    </div>
    <div class="d-flex gap-2">
        <div class="btn-group" role="group">
            <button type="button" class="btn btn-outline-primary active btn-sm" id="btnCardView" onclick="switchView('card')">
                <i class="fas fa-th-large me-1"></i> Cards
            </button>
            <button type="button" class="btn btn-outline-primary btn-sm" id="btnTableView" onclick="switchView('table')">
                <i class="fas fa-list me-1"></i> Table
            </button>
        </div>
    </div>
</div>

<?php
$upcoming = array_filter($interviews, fn($i) => ($i['status'] === 'scheduled' || $i['status'] === 'rescheduled') && strtotime($i['interview_date']) >= strtotime(date('Y-m-d')));
$completed = array_filter($interviews, fn($i) => $i['status'] === 'completed' || strtotime($i['interview_date']) < strtotime(date('Y-m-d')));
?>

<!-- Filter & Search Bar -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-3">
        <div class="row g-2 align-items-center">
            <div class="col-md-5">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" id="interviewSearchInput" class="form-control border-start-0" placeholder="Search company, job role, or round..." onkeyup="filterInterviews()">
                </div>
            </div>
            <div class="col-md-4">
                <select class="form-select form-select-sm" id="interviewStatusFilter" onchange="filterInterviews()">
                    <option value="">All Statuses</option>
                    <option value="upcoming">Upcoming</option>
                    <option value="completed">Completed</option>
                    <option value="passed">Selected / Passed</option>
                    <option value="failed">Rejected / Failed</option>
                </select>
            </div>
            <div class="col-md-3 text-end">
                <span class="badge bg-primary-soft text-primary px-3 py-2 fw-medium">Total Scheduled: <?= count($interviews) ?></span>
            </div>
        </div>
    </div>
</div>

<?php if (empty($interviews)): ?>
<div class="card border-0 shadow-sm py-5 text-center">
    <div class="card-body">
        <i class="fas fa-calendar-times text-muted mb-3" style="font-size: 3.5rem;"></i>
        <h5>No Interviews Scheduled Yet</h5>
        <p class="text-muted small max-w-500 mx-auto">When companies shortlist your profile or when the Placement Cell schedules your interview rounds, your interview dates and call letters will appear here.</p>
        <a href="<?= url('/student/jobs') ?>" class="btn btn-primary btn-sm"><i class="fas fa-briefcase me-1"></i> Browse Open Jobs</a>
    </div>
</div>
<?php else: ?>

<!-- CARDS VIEW CONTAINER -->
<div id="interviewCardContainer" class="row g-4">
    <?php foreach ($interviews as $iv): 
        $isUpcoming = ($iv['status'] === 'scheduled' || $iv['status'] === 'rescheduled') && strtotime($iv['interview_date'] . ' ' . $iv['interview_time']) >= time();
        
        $displayStatus = 'Upcoming';
        $badgeClass = 'bg-primary';
        if ($iv['result'] === 'passed') {
            $displayStatus = 'Selected';
            $badgeClass = 'bg-success';
        } elseif ($iv['result'] === 'failed') {
            $displayStatus = 'Rejected';
            $badgeClass = 'bg-danger';
        } elseif ($iv['status'] === 'completed') {
            $displayStatus = 'Completed';
            $badgeClass = 'bg-info text-white';
        } elseif ($iv['status'] === 'cancelled') {
            $displayStatus = 'Cancelled';
            $badgeClass = 'bg-secondary';
        }

        $datetimeStr = $iv['interview_date'] . ' ' . $iv['interview_time'];
    ?>
    <div class="col-lg-6 interview-item-card" 
         data-search="<?= strtolower(htmlspecialchars($iv['company_name'] . ' ' . $iv['job_title'] . ' ' . $iv['round'])) ?>"
         data-status="<?= strtolower($displayStatus) ?>"
         data-result="<?= strtolower($iv['result'] ?? 'pending') ?>">
        <div class="card border-0 shadow-sm h-100 position-relative overflow-hidden">
            <div class="card-body p-4">
                
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-light p-2 rounded border flex-shrink-0" style="width: 54px; height: 54px; display: flex; align-items: center; justify-content: center;">
                            <?php if (!empty($iv['logo'])): ?>
                                <img src="<?= uploadUrl('logos/' . $iv['logo']) ?>" alt="Logo" class="img-fluid" style="max-height: 40px;" onerror="this.src='<?= asset('images/default-company.png') ?>'">
                            <?php else: ?>
                                <i class="fas fa-building text-secondary fs-3"></i>
                            <?php endif; ?>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-0 text-dark"><?= htmlspecialchars($iv['company_name']) ?></h5>
                            <span class="text-secondary small fw-medium"><?= htmlspecialchars($iv['job_title']) ?></span>
                        </div>
                    </div>
                    <span class="badge <?= $badgeClass ?> px-3 py-2 rounded-pill fw-semibold"><?= $displayStatus ?></span>
                </div>

                <div class="p-3 bg-light rounded-3 mb-3">
                    <div class="row g-2 small">
                        <div class="col-6">
                            <span class="text-muted d-block" style="font-size:0.75rem;">ROUND</span>
                            <strong class="text-dark"><i class="fas fa-layer-group text-primary me-1"></i><?= htmlspecialchars($iv['round']) ?></strong>
                        </div>
                        <div class="col-6">
                            <span class="text-muted d-block" style="font-size:0.75rem;">INTERVIEW TYPE</span>
                            <strong class="text-dark">
                                <i class="fas fa-<?= $iv['mode'] === 'online' ? 'laptop-code text-info' : 'building text-primary' ?> me-1"></i>
                                <?= ucfirst($iv['mode']) ?>
                            </strong>
                        </div>
                        <div class="col-6 mt-2">
                            <span class="text-muted d-block" style="font-size:0.75rem;">DATE & TIME</span>
                            <strong class="text-dark"><i class="far fa-calendar-alt text-danger me-1"></i><?= formatDate($iv['interview_date']) ?> @ <?= date('h:i A', strtotime($iv['interview_time'])) ?></strong>
                        </div>
                        <div class="col-6 mt-2">
                            <span class="text-muted d-block" style="font-size:0.75rem;"><?= $iv['mode'] === 'online' ? 'MEETING LINK' : 'VENUE' ?></span>
                            <strong class="text-dark text-truncate d-block">
                                <?php if ($iv['mode'] === 'online' && !empty($iv['meeting_link'])): ?>
                                    <a href="<?= htmlspecialchars($iv['meeting_link']) ?>" target="_blank" class="text-primary"><i class="fas fa-external-link-alt me-1"></i>Join Link</a>
                                <?php else: ?>
                                    <i class="fas fa-map-marker-alt text-danger me-1"></i><?= htmlspecialchars($iv['venue'] ?: 'Campus Venue') ?>
                                <?php endif; ?>
                            </strong>
                        </div>
                    </div>
                </div>

                <!-- Live Countdown Timer for Upcoming Interviews -->
                <?php if ($isUpcoming): ?>
                <div class="alert alert-primary bg-primary-soft border-0 p-2 text-center rounded-3 mb-3">
                    <small class="fw-bold text-uppercase d-block mb-1 text-primary" style="font-size:0.7rem; letter-spacing:0.5px;">Interview Starts In</small>
                    <div class="countdown-timer fw-bold text-dark fs-6" data-target="<?= $datetimeStr ?>">
                        <span class="days">00</span>d : <span class="hours">00</span>h : <span class="minutes">00</span>m : <span class="seconds">00</span>s
                    </div>
                </div>
                <?php endif; ?>

                <div class="d-flex justify-content-between align-items-center pt-2">
                    <button class="btn btn-outline-secondary btn-sm" onclick='openInterviewModal(<?= json_encode($iv) ?>)'>
                        <i class="fas fa-eye me-1"></i> Details &amp; Guidelines
                    </button>
                    <a href="<?= url('/student/download-call-letter/' . $iv['id']) ?>" target="_blank" class="btn btn-primary btn-sm">
                        <i class="fas fa-file-download me-1"></i> Call Letter
                    </a>
                </div>

            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- TABLE VIEW CONTAINER (Hidden by default) -->
<div id="interviewTableContainer" class="card border-0 shadow-sm d-none">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th>Company</th>
                        <th>Job Role</th>
                        <th>Round</th>
                        <th>Type</th>
                        <th>Date &amp; Time</th>
                        <th>Venue / Link</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($interviews as $iv): 
                        $displayStatus = 'Upcoming';
                        $badgeClass = 'bg-primary';
                        if ($iv['result'] === 'passed') { $displayStatus = 'Selected'; $badgeClass = 'bg-success'; }
                        elseif ($iv['result'] === 'failed') { $displayStatus = 'Rejected'; $badgeClass = 'bg-danger'; }
                        elseif ($iv['status'] === 'completed') { $displayStatus = 'Completed'; $badgeClass = 'bg-info text-white'; }
                        elseif ($iv['status'] === 'cancelled') { $displayStatus = 'Cancelled'; $badgeClass = 'bg-secondary'; }
                    ?>
                    <tr class="interview-item-row"
                        data-search="<?= strtolower(htmlspecialchars($iv['company_name'] . ' ' . $iv['job_title'] . ' ' . $iv['round'])) ?>"
                        data-status="<?= strtolower($displayStatus) ?>">
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <?php if (!empty($iv['logo'])): ?>
                                    <img src="<?= uploadUrl('logos/' . $iv['logo']) ?>" style="width:28px; height:28px; object-fit:contain;" onerror="this.style.display='none'">
                                <?php endif; ?>
                                <span class="fw-bold text-dark"><?= htmlspecialchars($iv['company_name']) ?></span>
                            </div>
                        </td>
                        <td><?= htmlspecialchars($iv['job_title']) ?></td>
                        <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($iv['round']) ?></span></td>
                        <td><span class="badge bg-<?= $iv['mode'] === 'online' ? 'info' : 'primary' ?>-soft text-<?= $iv['mode'] === 'online' ? 'info' : 'primary' ?>"><?= ucfirst($iv['mode']) ?></span></td>
                        <td class="small fw-semibold"><?= formatDate($iv['interview_date']) ?><br><span class="text-muted"><?= date('h:i A', strtotime($iv['interview_time'])) ?></span></td>
                        <td class="small text-truncate" style="max-width: 150px;">
                            <?php if ($iv['mode'] === 'online' && !empty($iv['meeting_link'])): ?>
                                <a href="<?= htmlspecialchars($iv['meeting_link']) ?>" target="_blank" class="text-primary">Meeting Link</a>
                            <?php else: ?>
                                <?= htmlspecialchars($iv['venue'] ?: 'Campus') ?>
                            <?php endif; ?>
                        </td>
                        <td><span class="badge <?= $badgeClass ?>"><?= $displayStatus ?></span></td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-light me-1" onclick='openInterviewModal(<?= json_encode($iv) ?>)' title="View Details">
                                <i class="fas fa-eye text-primary"></i>
                            </button>
                            <a href="<?= url('/student/download-call-letter/' . $iv['id']) ?>" target="_blank" class="btn btn-sm btn-outline-primary" title="Download Call Letter">
                                <i class="fas fa-download"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php endif; ?>

<!-- INTERVIEW DETAILS MODAL -->
<div class="modal fade" id="interviewDetailModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-header-title mb-0" id="modalCompanyName">Interview Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <small class="text-muted text-uppercase fw-semibold d-block" style="font-size:0.75rem;">JOB ROLE</small>
                        <span class="fw-bold text-dark fs-6" id="modalJobTitle"></span>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted text-uppercase fw-semibold d-block" style="font-size:0.75rem;">ROUND</small>
                        <span class="fw-bold text-primary fs-6" id="modalRound"></span>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted text-uppercase fw-semibold d-block" style="font-size:0.75rem;">DATE & TIME</small>
                        <span class="fw-semibold text-dark" id="modalDateTime"></span>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted text-uppercase fw-semibold d-block" style="font-size:0.75rem;">LOCATION / LINK</small>
                        <span class="fw-semibold text-dark" id="modalVenue"></span>
                    </div>
                </div>

                <div class="card bg-light border-0 rounded-3 mb-3">
                    <div class="card-body">
                        <h6 class="fw-bold text-dark mb-2"><i class="fas fa-list-ul text-warning me-2"></i>Interview Instructions</h6>
                        <p class="small text-secondary mb-0" id="modalInstructions">No specific instructions provided.</p>
                    </div>
                </div>

                <div class="card bg-light border-0 rounded-3 mb-3">
                    <div class="card-body">
                        <h6 class="fw-bold text-dark mb-2"><i class="fas fa-file-alt text-info me-2"></i>Required Documents</h6>
                        <p class="small text-secondary mb-0" id="modalDocuments">Original Resume (2 Copies), College ID Card, All Marksheets, Passport Size Photos.</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light border-0">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                <a href="#" id="modalCallLetterBtn" target="_blank" class="btn btn-primary btn-sm"><i class="fas fa-download me-1"></i> Download Call Letter</a>
            </div>
        </div>
    </div>
</div>

<script>
// Switch Views
function switchView(view) {
    if (view === 'card') {
        $('#interviewCardContainer').removeClass('d-none');
        $('#interviewTableContainer').addClass('d-none');
        $('#btnCardView').addClass('active');
        $('#btnTableView').removeClass('active');
    } else {
        $('#interviewCardContainer').addClass('d-none');
        $('#interviewTableContainer').removeClass('d-none');
        $('#btnCardView').removeClass('active');
        $('#btnTableView').addClass('active');
    }
}

// Filter Interviews
function filterInterviews() {
    var search = $('#interviewSearchInput').val().toLowerCase();
    var status = $('#interviewStatusFilter').val().toLowerCase();

    $('.interview-item-card, .interview-item-row').each(function() {
        var cardSearch = $(this).data('search') || '';
        var cardStatus = $(this).data('status') || '';

        var matchSearch = !search || cardSearch.includes(search);
        var matchStatus = !status || cardStatus === status || (status === 'upcoming' && cardStatus === 'upcoming');

        if (matchSearch && matchStatus) {
            $(this).show();
        } else {
            $(this).hide();
        }
    });
}

// Modal Detail Filler
function openInterviewModal(iv) {
    $('#modalCompanyName').text(iv.company_name);
    $('#modalJobTitle').text(iv.job_title);
    $('#modalRound').text(iv.round);
    $('#modalDateTime').text(iv.interview_date + ' at ' + iv.interview_time);
    $('#modalVenue').html(iv.mode === 'online' ? '<a href="' + iv.meeting_link + '" target="_blank">' + iv.meeting_link + '</a>' : (iv.venue || 'Campus Venue'));
    $('#modalInstructions').text(iv.instructions || 'Arrive 15 minutes before scheduled time in formal attire.');
    $('#modalDocuments').text(iv.required_documents || '1. College ID Card\n2. Printed Copy of Call Letter\n3. 2 Copies of Resume\n4. Original Academic Marksheets');
    $('#modalCallLetterBtn').attr('href', TPMS.baseUrl + '/student/download-call-letter/' + iv.id);

    new bootstrap.Modal(document.getElementById('interviewDetailModal')).show();
}

// Live Countdown Timers
document.addEventListener('DOMContentLoaded', function() {
    function updateCountdowns() {
        $('.countdown-timer').each(function() {
            var targetStr = $(this).data('target');
            if (!targetStr) return;
            var targetDate = new Date(targetStr.replace(/-/g, '/')).getTime();
            var now = new Date().getTime();
            var diff = targetDate - now;

            if (diff > 0) {
                var days = Math.floor(diff / (1000 * 60 * 60 * 24));
                var hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                var minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                var seconds = Math.floor((diff % (1000 * 60)) / 1000);

                $(this).find('.days').text(days < 10 ? '0' + days : days);
                $(this).find('.hours').text(hours < 10 ? '0' + hours : hours);
                $(this).find('.minutes').text(minutes < 10 ? '0' + minutes : minutes);
                $(this).find('.seconds').text(seconds < 10 ? '0' + seconds : seconds);
            } else {
                $(this).html('<span class="text-danger fw-bold">Interview Started / Time Elapsed</span>');
            }
        });
    }

    updateCountdowns();
    setInterval(updateCountdowns, 1000);
});
</script>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>
