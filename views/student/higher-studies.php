<?php require_once ROOT_PATH . '/includes/header.php'; ?>
<div class="content-header">
    <div>
        <h1 class="page-title">Higher Studies &amp; Career Guidance</h1>
        <p class="subtitle">Apply for higher studies, competitive exams, and track your applications</p>
    </div>
</div>

<ul class="nav nav-tabs mb-4" id="higherStudiesTabs">
    <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#applyTab"><i class="fas fa-paper-plane me-1"></i>Apply for Higher Studies</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#myapps"><i class="fas fa-list-alt me-1"></i>My Applications (<?= count($myApplications) ?>)</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#exams"><i class="fas fa-file-signature me-1"></i>Entrance Exams</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#scholarships"><i class="fas fa-award me-1"></i>Scholarships</a></li>
</ul>

<div class="tab-content">
    <!-- Apply for Higher Studies -->
    <div class="tab-pane fade show active" id="applyTab">
        <div class="row">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0 fw-bold"><i class="fas fa-graduation-cap text-primary me-2"></i>Higher Studies &amp; Career Application Form</h6>
                    </div>
                    <div class="card-body p-4">
                        <form action="<?= url('/student/register-higher-study') ?>" method="POST">
                            <?= CsrfMiddleware::tokenField() ?>

                            <!-- Career Option Selection -->
                            <div class="mb-3">
                                <label class="form-label fw-bold" for="career_option">Select Career Option / Goal *</label>
                                <select class="form-select" id="career_option" name="career_option" required>
                                    <option value="">-- Choose Career Option --</option>
                                    <option value="M.Tech">M.Tech (Master of Technology)</option>
                                    <option value="MBA">MBA (Master of Business Administration)</option>
                                    <option value="MS Abroad">MS Abroad (Master of Science Overseas)</option>
                                    <option value="GATE">GATE (Graduate Aptitude Test in Engineering)</option>
                                    <option value="CAT">CAT (Common Admission Test)</option>
                                    <option value="GRE">GRE (Graduate Record Examinations)</option>
                                    <option value="TOEFL">TOEFL / IELTS (Language Proficiency)</option>
                                    <option value="UPSC">UPSC / Civil Services Exams</option>
                                    <option value="Government Exams">Government / Public Sector Exams</option>
                                    <option value="Research">Research / Ph.D.</option>
                                    <option value="Entrepreneurship">Entrepreneurship / Startup</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>

                            <!-- Suggested Programs Alert -->
                            <div id="suggestedProgramsBox" class="alert alert-info d-none mb-3 py-2 px-3" style="font-size:0.88rem;">
                                <i class="fas fa-lightbulb me-1 text-warning"></i> <strong>Recommended Courses/Focus:</strong>
                                <span id="suggestedProgramsList"></span>
                            </div>

                            <!-- Preferred Course / Program -->
                            <div class="mb-3">
                                <label class="form-label fw-bold" for="preferred_course">Preferred Course / Specialization *</label>
                                <input type="text" class="form-control" id="preferred_course" name="preferred_course" 
                                       placeholder="e.g. Computer Science, Data Science, Finance, Structural Engg" required>
                            </div>

                            <!-- Preferred University / Institute -->
                            <div class="mb-3">
                                <label class="form-label fw-bold" for="preferred_university">Preferred University / Institute / Target Body</label>
                                <input type="text" class="form-control" id="preferred_university" name="preferred_university" 
                                       placeholder="e.g. IIT Bombay, IIM Ahmedabad, Harvard, Stanford, Central Govt">
                            </div>

                            <!-- Exam Score / Target Score -->
                            <div class="mb-3">
                                <label class="form-label fw-semibold" for="exam_score">Exam Score / Status (if applicable)</label>
                                <input type="text" class="form-control" id="exam_score" name="exam_score" 
                                       placeholder="e.g. GATE Score: 720, GRE: 320, CAT: 98 percentile, Appearing in 2026">
                            </div>

                            <!-- Additional Notes -->
                            <div class="mb-4">
                                <label class="form-label fw-semibold" for="notes">Additional Information / Statement of Purpose</label>
                                <textarea class="form-control" id="notes" name="notes" rows="3" 
                                          placeholder="Share any background details, preparation status, or specific guidance required..."></textarea>
                            </div>

                            <button type="submit" class="btn btn-primary px-4 py-2 fw-semibold">
                                <i class="fas fa-paper-plane me-1"></i> Submit Higher Studies Application
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Helpful Info Sidebar -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm mb-4 bg-primary-subtle text-primary">
                    <div class="card-body p-4">
                        <h6 class="fw-bold"><i class="fas fa-info-circle me-1"></i> Why Submit?</h6>
                        <p class="small mb-0 opacity-90">
                            Submitting your Higher Studies plan alerts the Placement &amp; Training Cell. You will receive customized guidance, exam preparation resources, and recommendations for university applications.
                        </p>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3"><h6 class="mb-0 fw-bold">Popular Target Institutions</h6></div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            <?php foreach (array_slice($universities, 0, 5) as $uni): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-semibold small"><?= htmlspecialchars($uni['name']) ?></div>
                                    <small class="text-muted"><?= htmlspecialchars($uni['city'] . ', ' . $uni['country']) ?></small>
                                </div>
                                <?php if ($uni['ranking']): ?><span class="badge bg-primary">#<?= $uni['ranking'] ?></span><?php endif; ?>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- My Applications -->
    <div class="tab-pane fade" id="myapps">
        <?php if (empty($myApplications)): ?>
        <div class="card"><div class="card-body empty-state"><i class="fas fa-graduation-cap"></i><h5>No Applications Submitted</h5><p>Submit your higher studies application using the form above.</p></div></div>
        <?php else: ?>
        <div class="card"><div class="card-body p-0"><div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Career Option</th>
                        <th>Preferred Course</th>
                        <th>Preferred University</th>
                        <th>Exam Score / Details</th>
                        <th>Status</th>
                        <th>Submitted On</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($myApplications as $ma): ?>
                    <tr>
                        <td>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 fw-bold">
                                <?= htmlspecialchars($ma['career_option'] ?? 'N/A') ?>
                            </span>
                        </td>
                        <td class="fw-bold"><?= htmlspecialchars($ma['preferred_course'] ?: ($ma['legacy_course_name'] ?: 'N/A')) ?></td>
                        <td><?= htmlspecialchars($ma['preferred_university'] ?: ($ma['legacy_univ_name'] ?: 'N/A')) ?></td>
                        <td><small class="text-muted"><?= htmlspecialchars($ma['exam_score'] ?? 'N/A') ?></small></td>
                        <td>
                            <?php
                            $statusClass = 'bg-secondary';
                            if ($ma['status'] === 'accepted' || $ma['status'] === 'enrolled') $statusClass = 'bg-success';
                            elseif ($ma['status'] === 'applied') $statusClass = 'bg-info text-dark';
                            elseif ($ma['status'] === 'interested') $statusClass = 'bg-warning text-dark';
                            elseif ($ma['status'] === 'rejected') $statusClass = 'bg-danger';
                            ?>
                            <span class="badge <?= $statusClass ?>"><?= ucfirst($ma['status']) ?></span>
                        </td>
                        <td><small><?= formatDate($ma['created_at']) ?></small></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div></div></div>
        <?php endif; ?>
    </div>

    <!-- Entrance Exams -->
    <div class="tab-pane fade" id="exams">
        <div class="card"><div class="card-body p-0"><div class="table-responsive">
            <table class="table mb-0"><thead><tr><th>Exam</th><th>Conducting Body</th><th>Exam Date</th><th>Registration Deadline</th><th>Link</th></tr></thead>
            <tbody>
                <?php foreach ($exams as $exam): ?>
                <tr>
                    <td><div class="fw-bold"><?= htmlspecialchars($exam['name']) ?></div><small class="text-muted"><?= htmlspecialchars($exam['full_name'] ?? '') ?></small></td>
                    <td><?= htmlspecialchars($exam['conducting_body'] ?? 'N/A') ?></td>
                    <td><?= $exam['exam_date'] ? formatDate($exam['exam_date']) : 'TBA' ?></td>
                    <td><?= $exam['registration_deadline'] ? formatDate($exam['registration_deadline']) : 'TBA' ?></td>
                    <td><?php if ($exam['website']): ?><a href="<?= htmlspecialchars($exam['website']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">Visit</a><?php endif; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody></table>
        </div></div></div>
    </div>

    <!-- Scholarships -->
    <div class="tab-pane fade" id="scholarships">
        <div class="row g-4">
            <?php foreach ($scholarships as $sch): ?>
            <div class="col-lg-6">
                <div class="card hover-scale">
                    <div class="card-body">
                        <h5 class="fw-bold mb-1"><?= htmlspecialchars($sch['name']) ?></h5>
                        <small class="text-primary"><?= htmlspecialchars($sch['provider'] ?? '') ?></small>
                        <div class="mt-2 mb-2"><span class="badge bg-success"><?= formatCurrency($sch['amount'], $sch['currency']) ?></span> <span class="badge bg-light text-dark"><?= ucfirst($sch['type']) ?></span></div>
                        <?php if ($sch['eligibility']): ?><p style="font-size:0.82rem" class="text-muted"><?= htmlspecialchars(truncateText($sch['eligibility'], 100)) ?></p><?php endif; ?>
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">Deadline: <?= $sch['application_deadline'] ? formatDate($sch['application_deadline']) : 'TBA' ?></small>
                            <?php if ($sch['website']): ?><a href="<?= htmlspecialchars($sch['website']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">Apply</a><?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const careerProgramsMap = {
        'M.Tech': 'Computer Science & Engg, VLSI & Embedded Systems, Data Science, Thermal Engg, AI & ML',
        'MBA': 'Finance, Marketing, Human Resources, Operations, Business Analytics, International Business',
        'MS Abroad': 'MS in Computer Science, MS in Data Analytics, MS in Information Systems, MS in Robotics',
        'GATE': 'Computer Science (CS), Electrical (EE), Mechanical (ME), Electronics (EC), Civil (CE)',
        'CAT': 'Post Graduate Program in Management (PGP / MBA)',
        'GRE': 'STEM Master Programs, Doctoral / Ph.D. Programs',
        'TOEFL': 'IELTS Academic, TOEFL iBT (For US, UK, Canada, Australia Admissions)',
        'UPSC': 'Civil Services Examination (IAS, IPS, IFS, IRS), Indian Engineering Services (IES)',
        'Government Exams': 'SSC CGL, RRB NTPC, IBPS PO / Specialist Officer, State Public Service Commissions',
        'Research': 'Doctoral (Ph.D.) Fellowships, CSIR-NET, JRF, Research Assistantship',
        'Entrepreneurship': 'Incubation Programs, Venture Capital Pitching, Product Innovation',
        'Other': 'Post Graduate Diplomas, Executive Certifications'
    };

    const careerOptionSelect = document.getElementById('career_option');
    const suggestedBox = document.getElementById('suggestedProgramsBox');
    const suggestedList = document.getElementById('suggestedProgramsList');

    careerOptionSelect?.addEventListener('change', function() {
        const selected = this.value;
        if (selected && careerProgramsMap[selected]) {
            suggestedList.textContent = careerProgramsMap[selected];
            suggestedBox.classList.remove('d-none');
        } else {
            suggestedBox.classList.add('d-none');
        }
    });
});
</script>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>
