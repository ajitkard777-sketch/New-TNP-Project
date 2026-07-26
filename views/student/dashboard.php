<?php require_once ROOT_PATH . '/includes/header.php'; ?>

<div class="content-header">
    <div>
        <h1 class="page-title"><?= getGreeting() ?>, <?= htmlspecialchars($student['first_name'] ?? 'Student') ?>!</h1>
        <p class="subtitle">Welcome to your Training & Placement Portal</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('/student/jobs') ?>" class="btn btn-primary btn-sm"><i class="fas fa-briefcase me-1"></i> Browse Jobs</a>
        <a href="<?= url('/student/profile/edit') ?>" class="btn btn-outline-primary btn-sm"><i class="fas fa-user-edit me-1"></i> Edit Profile</a>
    </div>
</div>

<!-- Profile Completion Alert -->
<?php if (($student['profile_completion'] ?? 0) < 80): ?>
<div class="alert alert-warning animate-fade-in-up d-flex align-items-center justify-content-between mb-4" style="border-left:4px solid #f59e0b">
    <div class="d-flex align-items-center gap-2">
        <i class="fas fa-exclamation-triangle text-warning fs-5"></i>
        <div>
            <strong>Complete your profile!</strong> Your profile is currently <?= $student['profile_completion'] ?? 0 ?>% complete. 
            <a href="<?= url('/student/profile/edit') ?>" class="fw-bold text-dark text-decoration-underline ms-1">Complete it now →</a> to boost placement matching.
        </div>
    </div>
    <span class="badge bg-warning text-dark"><?= $student['profile_completion'] ?? 0 ?>%</span>
</div>
<?php endif; ?>

<!-- ════════════════════ TOP DASHBOARD INTEGRATION CARDS ════════════════════ -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="stat-card gradient-primary">
            <div class="stat-card-icon bg-primary-soft"><i class="fas fa-calendar-check"></i></div>
            <div class="stat-card-value"><?= $upcomingInterviewsCount ?? 0 ?></div>
            <div class="stat-card-label">Upcoming Interviews</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card gradient-success">
            <div class="stat-card-icon bg-success-soft"><i class="fas fa-briefcase"></i></div>
            <div class="stat-card-value"><?= $upcomingDrivesCount ?? 0 ?></div>
            <div class="stat-card-label">Upcoming Drives</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card gradient-warning">
            <div class="stat-card-icon bg-warning-soft"><i class="fas fa-trophy"></i></div>
            <div class="stat-card-value"><?= $totalAchievementsCount ?? 0 ?></div>
            <div class="stat-card-label">Total Achievements</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card gradient-violet">
            <div class="stat-card-icon bg-violet-soft"><i class="fas fa-calendar-alt"></i></div>
            <div class="stat-card-value"><?= $eventsThisMonthCount ?? 0 ?></div>
            <div class="stat-card-label">Calendar Events (Month)</div>
        </div>
    </div>
</div>

<!-- ════════════════════ MAIN CONTENT GRID ════════════════════ -->
<div class="row g-4">

    <!-- ── LEFT COLUMN: STUDENT PROFILE CARD & INTERVIEWS ── -->
    <div class="col-lg-4">
        <!-- Student Profile Card -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body profile-card text-center p-4">
                <div class="position-relative d-inline-block mb-3">
                    <img src="<?= $student['profile_photo'] ? uploadUrl('profile_photos/' . $student['profile_photo']) : asset('images/default-avatar.png') ?>" 
                         alt="Profile" class="profile-avatar shadow-sm" onerror="this.src='<?= asset('images/default-avatar.png') ?>'">
                    <?php if ($student['is_placed']): ?>
                    <span class="position-absolute bottom-0 end-0 badge rounded-pill bg-success p-2" title="Placed">
                        <i class="fas fa-check"></i>
                    </span>
                    <?php endif; ?>
                </div>

                <h5 class="profile-name fw-bold mb-1"><?= htmlspecialchars(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? '')) ?></h5>
                <div class="badge bg-primary-soft text-primary px-3 py-1 mb-3" style="font-size:0.82rem;">
                    <?= htmlspecialchars($student['branch'] ?? 'Student') ?>
                </div>

                <div class="text-start bg-light p-3 rounded mb-3" style="font-size:0.85rem;">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="fas fa-id-card text-primary" style="width:18px"></i>
                        <span class="text-muted">Roll No:</span>
                        <strong class="text-dark ms-auto"><?= htmlspecialchars($student['enrollment_no'] ?? 'N/A') ?></strong>
                    </div>
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="fas fa-envelope text-primary" style="width:18px"></i>
                        <span class="text-muted">Email:</span>
                        <strong class="text-dark ms-auto text-truncate" style="max-width:170px;"><?= htmlspecialchars($student['email'] ?? 'N/A') ?></strong>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <i class="fas fa-phone text-primary" style="width:18px"></i>
                        <span class="text-muted">Phone:</span>
                        <strong class="text-dark ms-auto"><?= htmlspecialchars($student['phone'] ?? 'N/A') ?></strong>
                    </div>
                </div>

                <div class="profile-completion text-start mb-3">
                    <div class="d-flex justify-content-between small mb-1">
                        <span class="text-muted">Profile Progress</span>
                        <span class="text-primary fw-bold"><?= $student['profile_completion'] ?? 0 ?>%</span>
                    </div>
                    <div class="progress" style="height:8px;">
                        <div class="progress-bar bg-primary" style="width: <?= $student['profile_completion'] ?? 0 ?>%"></div>
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <a href="<?= url('/student/profile/edit') ?>" class="btn btn-primary btn-sm fw-semibold">
                        <i class="fas fa-user-edit me-1"></i> Edit Profile
                    </a>
                    <a href="<?= url('/student/profile') ?>" class="btn btn-outline-primary btn-sm fw-semibold">
                        <i class="fas fa-user me-1"></i> View Detailed Profile
                    </a>
                </div>
            </div>
        </div>

        <!-- Upcoming Interviews Card -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-bold d-flex align-items-center gap-2">
                    <i class="fas fa-calendar-check text-warning"></i>
                    <span>Upcoming Interviews</span>
                </h6>
            </div>
            <div class="card-body p-0">
                <?php if (empty($upcomingInterviews)): ?>
                <div class="p-4 text-center text-muted small">
                    <i class="fas fa-calendar-times fs-3 mb-2 text-muted d-block opacity-50"></i>
                    No upcoming interviews scheduled.
                </div>
                <?php else: ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($upcomingInterviews as $interview): ?>
                    <div class="list-group-item p-3 border-bottom">
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <h6 class="fw-bold mb-0 text-primary" style="font-size:0.9rem;"><?= htmlspecialchars($interview['job_title']) ?></h6>
                            <span class="badge <?= getStatusBadgeClass($interview['status']) ?>"><?= ucfirst($interview['status']) ?></span>
                        </div>
                        <div class="text-dark fw-semibold small mb-2"><?= htmlspecialchars($interview['company_name']) ?></div>
                        <div class="small text-muted bg-light p-2 rounded">
                            <i class="far fa-calendar text-primary me-1"></i><?= formatDate($interview['interview_date']) ?>
                            <i class="far fa-clock text-primary ms-2 me-1"></i><?= date('h:i A', strtotime($interview['interview_time'])) ?>
                            <?php if ($interview['venue']): ?>
                            <div class="mt-1"><i class="fas fa-map-marker-alt text-danger me-1"></i><?= htmlspecialchars($interview['venue']) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- ── RIGHT COLUMN: ACADEMIC DETAILS & PROFILE SECTIONS ── -->
    <div class="col-lg-8">
        
        <!-- Academic Details Card -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-bold d-flex align-items-center gap-2">
                    <i class="fas fa-graduation-cap text-primary fs-5"></i>
                    <span>Academic Details</span>
                </h6>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-md-3 col-sm-6">
                        <div class="p-3 bg-light rounded text-center h-100">
                            <small class="text-muted d-block text-uppercase fw-semibold mb-1" style="font-size:0.7rem;">Degree</small>
                            <span class="fw-bold text-dark fs-6"><?= htmlspecialchars($student['degree'] ?? 'B.Tech') ?></span>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="p-3 bg-light rounded text-center h-100">
                            <small class="text-muted d-block text-uppercase fw-semibold mb-1" style="font-size:0.7rem;">Department</small>
                            <span class="fw-bold text-primary fs-6"><?= htmlspecialchars($student['branch'] ?? 'N/A') ?></span>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="p-3 bg-light rounded text-center h-100">
                            <small class="text-muted d-block text-uppercase fw-semibold mb-1" style="font-size:0.7rem;">CGPA</small>
                            <span class="fw-bold text-success fs-5"><?= $student['cgpa'] ? $student['cgpa'] . ' / 10' : 'N/A' ?></span>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="p-3 bg-light rounded text-center h-100">
                            <small class="text-muted d-block text-uppercase fw-semibold mb-1" style="font-size:0.7rem;">Current Semester</small>
                            <span class="fw-bold text-dark fs-6"><?= $student['current_semester'] ?? 8 ?>th Sem</span>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="p-3 bg-light rounded text-center h-100">
                            <small class="text-muted d-block text-uppercase fw-semibold mb-1" style="font-size:0.7rem;">10th Percentage</small>
                            <span class="fw-bold text-dark fs-6"><?= $student['tenth_percentage'] ? $student['tenth_percentage'] . '%' : 'N/A' ?></span>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="p-3 bg-light rounded text-center h-100">
                            <small class="text-muted d-block text-uppercase fw-semibold mb-1" style="font-size:0.7rem;">12th Percentage</small>
                            <span class="fw-bold text-dark fs-6"><?= $student['twelfth_percentage'] ? $student['twelfth_percentage'] . '%' : 'N/A' ?></span>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="p-3 bg-light rounded text-center h-100">
                            <small class="text-muted d-block text-uppercase fw-semibold mb-1" style="font-size:0.7rem;">Backlogs</small>
                            <span class="fw-bold <?= ($student['active_backlogs'] ?? 0) > 0 ? 'text-danger' : 'text-success' ?> fs-6">
                                <?= $student['active_backlogs'] ?? 0 ?> Active (Total: <?= $student['backlogs'] ?? 0 ?>)
                            </span>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="p-3 bg-light rounded text-center h-100">
                            <small class="text-muted d-block text-uppercase fw-semibold mb-1" style="font-size:0.7rem;">Passing Year</small>
                            <span class="fw-bold text-dark fs-6"><?= $student['passing_year'] ?? 'N/A' ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 6 Separate Cards Below Academic Details -->
        <div class="row g-3 mb-4">
            
            <!-- 1. Skills Card -->
            <div class="col-md-6">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                        <h6 class="mb-0 fw-bold d-flex align-items-center gap-2">
                            <i class="fas fa-tools text-primary"></i> Skills
                        </h6>
                        <a href="<?= url('/student/profile/edit') ?>" class="text-primary small fw-semibold">Edit</a>
                    </div>
                    <div class="card-body p-3">
                        <?php if (!empty($student['skills'])): ?>
                            <div class="d-flex flex-wrap gap-2">
                                <?php foreach (explode(',', $student['skills']) as $skill): ?>
                                <span class="badge bg-primary-soft text-primary px-3 py-2 fw-medium" style="font-size:0.8rem;">
                                    <i class="fas fa-check-circle me-1 text-primary"></i><?= htmlspecialchars(trim($skill)) ?>
                                </span>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted small mb-0">No skills added yet. <a href="<?= url('/student/profile/edit') ?>">Add skills</a></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- 2. Programming Languages Card -->
            <div class="col-md-6">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                        <h6 class="mb-0 fw-bold d-flex align-items-center gap-2">
                            <i class="fas fa-code text-info"></i> Programming Languages
                        </h6>
                        <a href="<?= url('/student/profile/edit') ?>" class="text-primary small fw-semibold">Edit</a>
                    </div>
                    <div class="card-body p-3">
                        <?php if (!empty($languages)): ?>
                            <div class="d-flex flex-wrap gap-2">
                                <?php foreach ($languages as $lang): ?>
                                <span class="badge bg-info-soft text-info px-3 py-2 fw-medium" style="font-size:0.8rem;">
                                    <i class="fas fa-terminal me-1"></i><?= htmlspecialchars($lang['language']) ?> (<?= ucfirst($lang['proficiency'] ?? 'intermediate') ?>)
                                </span>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted small mb-0">No programming languages added yet. <a href="<?= url('/student/profile/edit') ?>">Add languages</a></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- 3. Certifications Card -->
            <div class="col-md-6">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                        <h6 class="mb-0 fw-bold d-flex align-items-center gap-2">
                            <i class="fas fa-certificate text-warning"></i> Certifications
                        </h6>
                        <a href="<?= url('/student/profile/edit') ?>" class="text-primary small fw-semibold">Edit</a>
                    </div>
                    <div class="card-body p-3">
                        <?php if (!empty($certifications)): ?>
                            <div class="list-group list-group-flush">
                                <?php foreach (array_slice($certifications, 0, 3) as $cert): ?>
                                <div class="py-2 border-bottom last-border-0">
                                    <div class="fw-semibold small text-dark"><?= htmlspecialchars($cert['title']) ?></div>
                                    <small class="text-muted"><?= htmlspecialchars($cert['issuing_org'] ?? '') ?></small>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted small mb-0">No certifications added. <a href="<?= url('/student/profile/edit') ?>">Add certifications</a></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- 4. Resume Status Card -->
            <div class="col-md-6">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                        <h6 class="mb-0 fw-bold d-flex align-items-center gap-2">
                            <i class="fas fa-file-pdf text-danger"></i> Resume Status
                        </h6>
                        <a href="<?= url('/student/profile/edit#resume-section') ?>" class="text-primary small fw-semibold">Manage</a>
                    </div>
                    <div class="card-body p-3">
                        <?php if (!empty($student['resume_path'])): ?>
                            <div class="d-flex align-items-center justify-content-between bg-light p-2 rounded mb-2">
                                <div class="d-flex align-items-center gap-2 overflow-hidden me-2">
                                    <i class="fas fa-file-pdf text-danger fs-4"></i>
                                    <div class="text-truncate">
                                        <div class="fw-semibold small text-dark text-truncate"><?= htmlspecialchars($student['resume_original_name'] ?: 'My_Resume.pdf') ?></div>
                                        <small class="text-success"><i class="fas fa-check-circle me-1"></i>Active &amp; Ready</small>
                                    </div>
                                </div>
                                <div class="d-flex gap-1">
                                    <a href="<?= url('/student/preview-resume') ?>" target="_blank" class="btn btn-xs btn-outline-primary" title="Preview Resume"><i class="fas fa-eye"></i></a>
                                    <a href="<?= url('/student/download-resume') ?>" class="btn btn-xs btn-primary" title="Download Resume"><i class="fas fa-download"></i></a>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="p-2 text-center border rounded border-dashed text-muted small">
                                <i class="fas fa-cloud-upload-alt fs-4 mb-1 text-danger d-block"></i>
                                No resume uploaded. <a href="<?= url('/student/profile/edit#resume-section') ?>" class="fw-bold">Upload Resume PDF</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- 5. Projects Card -->
            <div class="col-md-6">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                        <h6 class="mb-0 fw-bold d-flex align-items-center gap-2">
                            <i class="fas fa-project-diagram text-violet" style="color:#8b5cf6"></i> Projects
                        </h6>
                        <a href="<?= url('/student/profile/edit') ?>" class="text-primary small fw-semibold">Edit</a>
                    </div>
                    <div class="card-body p-3">
                        <?php if (!empty($projects)): ?>
                            <div class="list-group list-group-flush">
                                <?php foreach (array_slice($projects, 0, 3) as $proj): ?>
                                <div class="py-2 border-bottom last-border-0">
                                    <div class="fw-semibold small text-dark"><?= htmlspecialchars($proj['title']) ?></div>
                                    <small class="text-muted d-block text-truncate"><?= htmlspecialchars($proj['technologies'] ?? '') ?></small>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted small mb-0">No projects added yet. <a href="<?= url('/student/profile/edit') ?>">Add project</a></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- 6. Achievements Card -->
            <div class="col-md-6">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                        <h6 class="mb-0 fw-bold d-flex align-items-center gap-2">
                            <i class="fas fa-award text-success"></i> Achievements
                        </h6>
                        <a href="<?= url('/student/profile/edit') ?>" class="text-primary small fw-semibold">Edit</a>
                    </div>
                    <div class="card-body p-3">
                        <?php if (!empty($achievements)): ?>
                            <div class="list-group list-group-flush">
                                <?php foreach (array_slice($achievements, 0, 3) as $ach): ?>
                                <div class="py-2 border-bottom last-border-0">
                                    <div class="fw-semibold small text-dark"><i class="fas fa-medal me-1 text-warning"></i><?= htmlspecialchars($ach['title']) ?></div>
                                    <small class="text-muted"><?= htmlspecialchars($ach['description'] ?? '') ?></small>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted small mb-0">No achievements recorded yet. <a href="<?= url('/student/profile/edit') ?>">Add achievement</a></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        </div>

        <!-- AI Recommended Jobs Section -->
        <div class="card shadow-sm border-0 border-top border-4 border-primary">
            <div class="card-header bg-white border-bottom d-flex align-items-center justify-content-between py-3">
                <h6 class="mb-0 fw-bold d-flex align-items-center gap-2">
                    <i class="fas fa-robot text-primary fs-5"></i>
                    <span>AI Recommended Jobs For You</span>
                </h6>
                <a href="<?= url('/student/recommendations') ?>" class="btn btn-sm btn-outline-primary fw-semibold">
                    View All Matched Jobs <i class="fas fa-arrow-right ms-1"></i>
                </a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($recommendedJobs)): ?>
                <div class="p-4 text-center text-muted">
                    <i class="fas fa-sparkles fs-2 mb-2 text-warning d-block"></i>
                    <p class="mb-1 fw-semibold">No job recommendations found yet.</p>
                    <small>Update your <a href="<?= url('/student/profile/edit') ?>" class="fw-bold">skills and branch details</a> to get personalized recommendations!</small>
                </div>
                <?php else: ?>
                <div class="list-group list-group-flush">
                    <?php foreach (array_slice($recommendedJobs, 0, 4) as $rJob): ?>
                    <div class="list-group-item p-3 border-bottom hover-scale">
                        <div class="d-flex align-items-start gap-3">
                            <img src="<?= $rJob['logo'] ? uploadUrl('company/' . $rJob['logo']) : asset('images/default-avatar.png') ?>" 
                                 alt="" class="rounded shadow-sm" style="width:48px;height:48px;object-fit:cover;border:1px solid #e2e8f0;" 
                                 onerror="this.src='<?= asset('images/default-avatar.png') ?>'">
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-1">
                                    <h6 class="mb-0 fw-bold" style="font-size:0.95rem;"><?= htmlspecialchars($rJob['title']) ?></h6>
                                    <span class="badge <?= $rJob['match_badge_class'] ?> px-2 py-1" style="font-size:0.78rem;">
                                        <i class="fas fa-fire me-1"></i> <?= $rJob['match_score'] ?>% Match — <?= $rJob['match_label'] ?>
                                    </span>
                                </div>
                                <div class="text-muted small mb-2">
                                    <span class="fw-medium text-primary me-2"><?= htmlspecialchars($rJob['company_name']) ?></span>
                                    <span class="me-2"><i class="fas fa-map-marker-alt me-1 text-danger"></i><?= htmlspecialchars($rJob['location'] ?? 'N/A') ?></span>
                                    <span class="text-success fw-bold"><i class="fas fa-money-bill-wave me-1"></i><?= formatSalaryRange($rJob['salary_min'], $rJob['salary_max']) ?></span>
                                </div>
                                <div class="p-2 rounded bg-light border-start border-primary border-3 small text-secondary">
                                    <i class="fas fa-lightbulb text-warning me-1"></i>
                                    <strong>Match Reason:</strong> <?= htmlspecialchars($rJob['match_explanation']) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

    </div>

</div>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>
