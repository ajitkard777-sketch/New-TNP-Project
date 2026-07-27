<?php require_once ROOT_PATH . '/includes/header.php'; ?>

<div class="content-header">
    <div>
        <div class="badge bg-primary-subtle text-primary border border-primary px-3 py-1 mb-2 rounded-pill font-medium"><i class="fas fa-robot me-1"></i> AI Admin Assistant &amp; Analytics</div>
        <h1 class="page-title">AI Candidate Matching &amp; Placement Intelligence</h1>
        <p class="subtitle">System-wide analytics on student job matching, candidate shortlisting, and report exports</p>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-primary btn-sm" onclick="openFindStudentsModal()"><i class="fas fa-user-check me-1"></i> Find Eligible Students</button>
        <div class="dropdown">
            <button class="btn btn-success btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown"><i class="fas fa-file-export me-1"></i> Export Reports</button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="<?= url('/api/admin/reports?type=placements&format=csv') ?>"><i class="fas fa-file-excel text-success me-2"></i> Placements Report (CSV)</a></li>
                <li><a class="dropdown-item" href="<?= url('/api/admin/reports?type=eligibility&format=csv') ?>"><i class="fas fa-file-excel text-success me-2"></i> Student Eligibility Report (CSV)</a></li>
            </ul>
        </div>
        <button class="btn btn-primary btn-sm" onclick="recalculateAll()"><i class="fas fa-sync-alt me-1"></i> Recalculate Matches</button>
    </div>
</div>

<!-- Stats Row -->
<div class="row g-4 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card animate-fade-in-up">
            <div class="stat-icon bg-primary text-white"><i class="fas fa-chart-line"></i></div>
            <div class="stat-info">
                <div class="stat-value"><?= number_format($analytics['average_score'], 1) ?>%</div>
                <div class="stat-label">Avg Recommendation Score</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card animate-fade-in-up" style="animation-delay: 0.1s;">
            <div class="stat-icon bg-success text-white"><i class="fas fa-magic"></i></div>
            <div class="stat-info">
                <div class="stat-value"><?= number_format($analytics['total_recommendations']) ?></div>
                <div class="stat-label">Total Matches Calculated</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card animate-fade-in-up" style="animation-delay: 0.2s;">
            <div class="stat-icon bg-warning text-white"><i class="fas fa-user-graduate"></i></div>
            <div class="stat-info">
                <div class="stat-value"><?= count($analytics['unmatched_students']) ?></div>
                <div class="stat-label">Students Needing Upskilling</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card animate-fade-in-up" style="animation-delay: 0.3s;">
            <div class="stat-icon bg-info text-white"><i class="fas fa-building"></i></div>
            <div class="stat-info">
                <div class="stat-value"><?= count($analytics['top_companies']) ?></div>
                <div class="stat-label">Top Hiring Companies</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Most Recommended Jobs -->
    <div class="col-lg-7">
        <div class="card h-100 animate-fade-in-up">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title m-0"><i class="fas fa-trophy text-warning me-2"></i>Most Recommended Jobs</h5>
                <span class="badge bg-light text-dark border">Top Matches</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle table-hover m-0">
                        <thead class="table-light">
                            <tr>
                                <th>Job Title</th>
                                <th>Company</th>
                                <th>Avg Match %</th>
                                <th>Rec Count</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($analytics['top_jobs'])): ?>
                            <tr><td colspan="4" class="text-center text-muted py-4">No recommendation data available</td></tr>
                            <?php else: ?>
                            <?php foreach ($analytics['top_jobs'] as $job): ?>
                            <tr>
                                <td class="fw-bold"><?= htmlspecialchars($job['title']) ?></td>
                                <td><?= htmlspecialchars($job['company_name']) ?></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="progress flex-grow-1" style="height: 6px; width: 80px;">
                                            <div class="progress-bar bg-success" style="width: <?= min(100, $job['avg_score']) ?>%;"></div>
                                        </div>
                                        <span class="fw-bold text-success" style="font-size: 0.85rem;"><?= number_format($job['avg_score'], 1) ?>%</span>
                                    </div>
                                </td>
                                <td><span class="badge bg-secondary"><?= $job['recommendation_count'] ?> students</span></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Most Demanded Skills -->
    <div class="col-lg-5">
        <div class="card h-100 animate-fade-in-up">
            <div class="card-header">
                <h5 class="card-title m-0"><i class="fas fa-fire text-danger me-2"></i>Most Demanded Industry Skills</h5>
            </div>
            <div class="card-body">
                <p class="text-muted" style="font-size: 0.85rem;">Skills required across active placement drive job postings:</p>
                <div class="d-flex flex-wrap gap-2">
                    <?php if (empty($analytics['top_skills'])): ?>
                    <span class="text-muted">No skills recorded yet.</span>
                    <?php else: ?>
                    <?php foreach ($analytics['top_skills'] as $skill => $freq): ?>
                    <div class="p-2 px-3 border rounded-3 bg-light d-flex align-items-center gap-2">
                        <i class="fas fa-code text-primary"></i>
                        <span class="fw-bold" style="font-size: 0.85rem;"><?= htmlspecialchars($skill) ?></span>
                        <span class="badge bg-primary rounded-pill"><?= $freq ?> jobs</span>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Students Without High Matching Jobs -->
    <div class="col-lg-7">
        <div class="card animate-fade-in-up">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title m-0"><i class="fas fa-exclamation-circle text-danger me-2"></i>Students Without Matching Jobs (Upskilling Needed)</h5>
                <span class="badge bg-danger-subtle text-danger border border-danger">Score &lt; 50%</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle table-hover m-0">
                        <thead class="table-light">
                            <tr>
                                <th>Student</th>
                                <th>Branch</th>
                                <th>CGPA</th>
                                <th>Top Match %</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($analytics['unmatched_students'])): ?>
                            <tr><td colspan="5" class="text-center text-success py-4"><i class="fas fa-check-circle me-1"></i> All students have high-matching job opportunities!</td></tr>
                            <?php else: ?>
                            <?php foreach ($analytics['unmatched_students'] as $st): ?>
                            <tr>
                                <td class="fw-bold"><?= htmlspecialchars($st['first_name'] . ' ' . $st['last_name']) ?></td>
                                <td><?= htmlspecialchars($st['branch'] ?: 'N/A') ?></td>
                                <td><?= number_format((float)($st['cgpa'] ?? 0), 2) ?></td>
                                <td>
                                    <span class="badge bg-danger-subtle text-danger border border-danger">
                                        <?= $st['top_score'] ? number_format($st['top_score'], 1) . '%' : 'No Match' ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="<?= url('/admin/view-student/' . $st['id']) ?>" class="btn btn-outline-primary btn-sm"><i class="fas fa-eye me-1"></i> View Profile</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Hiring Companies -->
    <div class="col-lg-5">
        <div class="card animate-fade-in-up">
            <div class="card-header">
                <h5 class="card-title m-0"><i class="fas fa-building text-info me-2"></i>Top Hiring Companies</h5>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    <?php if (empty($analytics['top_companies'])): ?>
                    <div class="p-4 text-center text-muted">No active hiring companies found.</div>
                    <?php else: ?>
                    <?php foreach ($analytics['top_companies'] as $comp): ?>
                    <div class="list-group-item d-flex align-items-center justify-content-between p-3">
                        <div class="d-flex align-items-center gap-3">
                            <img src="<?= $comp['logo'] ? uploadUrl('company/' . $comp['logo']) : asset('images/default-avatar.png') ?>" alt="" class="rounded-3 border" style="width: 42px; height: 42px; object-fit: contain;" onerror="this.src='<?= asset('images/default-avatar.png') ?>'">
                            <div>
                                <h6 class="fw-bold mb-0"><?= htmlspecialchars($comp['company_name']) ?></h6>
                                <span class="text-muted" style="font-size: 0.75rem;"><?= $comp['active_jobs'] ?> active job posting(s)</span>
                            </div>
                        </div>
                        <div class="text-end">
                            <div class="fw-bold text-success" style="font-size: 0.9rem;"><?= number_format((float)($comp['avg_recommendation'] ?? 0), 1) ?>%</div>
                            <span class="text-muted" style="font-size: 0.7rem;">Avg Candidate Match</span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Find Eligible Students Modal -->
<div class="modal fade" id="findStudentsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 1.25rem;">
            <div class="modal-header bg-dark text-white border-0">
                <h5 class="modal-title fw-bold" id="findStudentsModalTitle"><i class="fas fa-robot text-primary me-2"></i>Find Eligible Candidates for Job</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label font-semibold">Select Job Posting</label>
                        <select class="form-select" id="modalJobSelect" onchange="fetchEligibleStudents()">
                            <option value="">-- Choose Active Job Posting --</option>
                            <?php foreach ($analytics['top_jobs'] as $j): ?>
                            <option value="<?= $j['job_id'] ?>"><?= htmlspecialchars($j['title']) ?> (<?= htmlspecialchars($j['company_name']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label font-semibold">Filter Branch</label>
                        <select class="form-select" id="modalBranchFilter" onchange="fetchEligibleStudents()">
                            <option value="">All Branches</option>
                            <option value="Computer Science">Computer Science</option>
                            <option value="Information Technology">Information Technology</option>
                            <option value="Mechanical">Mechanical</option>
                            <option value="Civil">Civil</option>
                            <option value="Electrical">Electrical</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label font-semibold">Min CGPA</label>
                        <input type="number" step="0.1" min="0" max="10" class="form-control" placeholder="e.g. 7.5" id="modalMinCgpa" onchange="fetchEligibleStudents()">
                    </div>
                </div>

                <!-- Candidates Table Container -->
                <div id="eligibleCandidatesContainer">
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-user-search fs-1 opacity-50 mb-2"></i>
                        <p>Select a job posting above to view AI ranked eligible candidates</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary btn-sm" id="sendInviteBtn" style="display:none;" onclick="sendBatchInvitations()">
                    <i class="fas fa-paper-plane me-1"></i> Send Interview Invitations to Selected
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let currentJobCandidates = [];
let selectedJobId = 0;

function openFindStudentsModal() {
    const modal = new bootstrap.Modal(document.getElementById('findStudentsModal'));
    modal.show();
    const select = document.getElementById('modalJobSelect');
    if (select.options.length > 1 && !select.value) {
        select.selectedIndex = 1;
        fetchEligibleStudents();
    }
}

function fetchEligibleStudents() {
    const jobId = document.getElementById('modalJobSelect').value;
    const branch = document.getElementById('modalBranchFilter').value;
    const minCgpa = document.getElementById('modalMinCgpa').value;
    const container = document.getElementById('eligibleCandidatesContainer');

    if (!jobId) {
        container.innerHTML = '<div class="text-center py-5 text-muted"><p>Select a job posting above</p></div>';
        return;
    }

    selectedJobId = jobId;
    container.innerHTML = '<div class="text-center py-5"><i class="fas fa-spinner fa-spin fs-2 text-primary mb-2"></i><p>Calculating Candidate Eligibility Scores...</p></div>';

    let url = '<?= url('/api/admin/eligible-students/') ?>' + jobId + '?branch=' + encodeURIComponent(branch) + '&min_cgpa=' + encodeURIComponent(minCgpa);
    
    fetch(url)
    .then(r => r.json())
    .then(data => {
        if (data.success && data.students) {
            currentJobCandidates = data.students;
            renderEligibleStudentsTable(data.students);
            document.getElementById('sendInviteBtn').style.display = 'inline-block';
        } else {
            container.innerHTML = '<div class="alert alert-warning">No matching candidates found for this criteria.</div>';
        }
    })
    .catch(err => {
        container.innerHTML = '<div class="alert alert-danger">Failed to fetch candidates.</div>';
    });
}

function renderEligibleStudentsTable(students) {
    const container = document.getElementById('eligibleCandidatesContainer');
    if (!students || students.length === 0) {
        container.innerHTML = '<div class="alert alert-warning">No candidates meet the selected filter criteria.</div>';
        return;
    }

    let html = `
        <div class="d-flex justify-content-between align-items-center mb-3">
            <span class="fw-bold">Found <span class="text-primary">${students.length}</span> Eligible Candidates</span>
            <small class="text-muted">Formula: Skills (50%) + CGPA (20%) + Branch (15%) + Certifications (10%) + Location (5%)</small>
        </div>
        <div class="table-responsive" style="max-height: 350px; overflow-y: auto;">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light sticky-top">
                    <tr>
                        <th width="40"><input type="checkbox" onchange="toggleSelectAllCandidates(this)"></th>
                        <th>Student Candidate</th>
                        <th>Branch</th>
                        <th>CGPA</th>
                        <th>Eligibility Score</th>
                        <th>Matched Skills</th>
                        <th>Reasoning</th>
                    </tr>
                </thead>
                <tbody>
    `;

    students.forEach((s, idx) => {
        const scoreColor = s.eligibility_score >= 80 ? 'success' : (s.eligibility_score >= 60 ? 'primary' : 'warning');
        const matched = s.matched_skills.map(sk => `<span class="badge bg-success-subtle text-success border border-success-subtle me-1">${sk}</span>`).join('');
        
        html += `
            <tr>
                <td><input type="checkbox" class="cand-select-cb" value="${s.student_id}"></td>
                <td>
                    <div class="fw-bold">${s.name}</div>
                    <small class="text-muted">${s.enrollment_no}</small>
                </td>
                <td><span class="badge bg-light text-dark">${s.branch}</span></td>
                <td class="fw-bold">${s.cgpa}</td>
                <td>
                    <div class="d-flex align-items-center gap-2">
                        <div class="progress flex-grow-1" style="height:6px; width:60px;">
                            <div class="progress-bar bg-${scoreColor}" style="width: ${s.eligibility_score}%;"></div>
                        </div>
                        <span class="fw-bold text-${scoreColor}">${s.eligibility_score}%</span>
                    </div>
                </td>
                <td>${matched || '<small class="text-muted">General</small>'}</td>
                <td><small class="text-muted">${s.reason}</small></td>
            </tr>
        `;
    });

    html += `</tbody></table></div>`;
    container.innerHTML = html;
}

function toggleSelectAllCandidates(masterCb) {
    document.querySelectorAll('.cand-select-cb').forEach(cb => cb.checked = masterCb.checked);
}

function sendBatchInvitations() {
    const selected = Array.from(document.querySelectorAll('.cand-select-cb:checked')).map(cb => cb.value);
    if (selected.length === 0) {
        alert('Please select at least one candidate using the checkboxes.');
        return;
    }

    const btn = document.getElementById('sendInviteBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Sending Invitations...';

    fetch('<?= url('/api/admin/notify-shortlist') ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `job_id=${selectedJobId}&${selected.map(id => 'student_ids[]=' + id).join('&')}&csrf_token=<?= $_SESSION['csrf_token'] ?? '' ?>`
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-paper-plane me-1"></i> Send Interview Invitations to Selected';
        if (data.success) {
            alert(data.message);
        } else {
            alert(data.error || 'Failed to send invitations.');
        }
    })
    .catch(err => {
        btn.disabled = false;
        alert('Network error.');
    });
}

function recalculateAll() {
    const btn = event.currentTarget;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Calculating...';

    fetch('<?= url('/api/recommendations/generate') ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'csrf_token=<?= $_SESSION['csrf_token'] ?? '' ?>'
    })
    .then(r => r.json())
    .then(data => {
        window.location.reload();
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-sync-alt me-1"></i> Recalculate System Matches';
        alert('Calculation failed.');
    });
}
</script>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>

