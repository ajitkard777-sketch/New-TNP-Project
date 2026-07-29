<?php require_once ROOT_PATH . '/includes/header.php'; ?>
<?php
// ── Convenience helpers ──────────────────────────────────────────────────
$fullName     = htmlspecialchars($student['first_name'] . ' ' . $student['last_name']);
$avatarUrl    = $student['profile_photo']
    ? uploadUrl('profile_photos/' . $student['profile_photo'])
    : asset('images/default-avatar.png');
$statusColor  = $student['is_placed'] ? 'success' : 'primary';
$statusLabel  = $student['is_placed'] ? 'Placed' : 'Available';
$matchColor   = $aiMatchScore >= 75 ? 'success' : ($aiMatchScore >= 50 ? 'warning' : 'danger');
$matchLabel   = $aiMatchScore >= 75 ? 'Excellent' : ($aiMatchScore >= 50 ? 'Good' : 'Low');

$resumePreviewUrl  = $student['resume_path'] ? url('/company/serve-resume/' . $student['id']) : null;
$resumeDownloadUrl = $student['resume_path'] ? url('/company/serve-resume/' . $student['id'] . '?download=1') : null;
$latestAppId       = $latestApp['id'] ?? null;
?>

<!-- ── Back breadcrumb ───────────────────────────────────────────────── -->
<div class="content-header d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= url('/company/jobs') ?>">Jobs</a></li>
                <?php if ($latestApp): ?>
                <li class="breadcrumb-item"><a href="<?= url('/company/applications/' . $latestApp['job_id']) ?>">Applicants</a></li>
                <?php endif; ?>
                <li class="breadcrumb-item active"><?= $fullName ?></li>
            </ol>
        </nav>
        <h1 class="page-title">Applicant Profile</h1>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <?php if ($latestApp): ?>
        <a href="<?= url('/company/applications/' . $latestApp['job_id']) ?>" class="btn btn-light border btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Back to Applicants
        </a>
        <?php endif; ?>
        <?php if ($resumeDownloadUrl): ?>
        <a href="<?= $resumeDownloadUrl ?>" class="btn btn-outline-primary btn-sm">
            <i class="fas fa-download me-1"></i> Download Resume
        </a>
        <?php endif; ?>
        <?php $applicantUserId = (int)($student['user_id'] ?? 0); ?>
        <?php if ($applicantUserId > 0): ?>
        <a href="<?= url('/chat?user_id=' . $applicantUserId) ?>" class="btn btn-primary btn-sm">
            <i class="fas fa-comment-dots me-1"></i> Message
        </a>
        <?php else: ?>
        <button class="btn btn-secondary btn-sm disabled" title="Chat Unavailable" disabled>
            <i class="fas fa-comment-dots me-1"></i> Message
        </button>
        <?php endif; ?>
    </div>
</div>

<div class="row g-4">

    <!-- ══════════════════════════════════════════════════════════════════
         LEFT COLUMN — Hero card + quick stats + applications
         ══════════════════════════════════════════════════════════════════ -->
    <div class="col-xl-4 col-lg-5">

        <!-- ── Hero / Identity Card ──────────────────────────────────── -->
        <div class="card shadow-sm mb-4 overflow-hidden">
            <!-- Gradient banner -->
            <div style="height:90px; background: linear-gradient(135deg, var(--primary) 0%, #1d4ed8 100%); position:relative;">
                <div style="position:absolute;inset:0;background:url('data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22400%22 height=%2290%22%3E%3Crect width=%22400%22 height=%2290%22 fill=%22none%22/%3E%3Ccircle cx=%22350%22 cy=%2245%22 r=%2280%22 fill=%22rgba(255,255,255,0.05)%22/%3E%3Ccircle cx=%2220%22 cy=%2270%22 r=%2260%22 fill=%22rgba(255,255,255,0.05)%22/%3E%3C/svg%3E');"></div>
            </div>
            <div class="card-body text-center pt-0">
                <!-- Avatar overlapping banner -->
                <div style="position:relative; display:inline-block; margin-top:-44px; margin-bottom:10px;">
                    <img src="<?= $avatarUrl ?>"
                         alt="<?= $fullName ?>"
                         onerror="this.src='<?= asset('images/default-avatar.png') ?>'"
                         style="width:88px;height:88px;border-radius:50%;border:4px solid #fff;object-fit:cover;box-shadow:0 4px 16px rgba(37,99,235,0.2);">
                    <span style="position:absolute;bottom:4px;right:4px;width:18px;height:18px;border-radius:50%;background:<?= $student['is_placed'] ? '#16a34a' : '#2563eb' ?>;border:3px solid #fff;display:block;"></span>
                </div>

                <h4 class="fw-bold mb-1" style="font-size:1.1rem;"><?= $fullName ?></h4>
                <p class="text-muted small mb-2"><?= htmlspecialchars($student['degree'] ?? 'B.Tech') ?> · <?= htmlspecialchars($student['branch'] ?? '') ?> · <?= $student['passing_year'] ?? '' ?></p>

                <div class="d-flex align-items-center justify-content-center gap-2 mb-3">
                    <span class="badge bg-<?= $statusColor ?>-subtle text-<?= $statusColor ?> border border-<?= $statusColor ?>-subtle px-3 py-1">
                        <i class="fas fa-circle me-1" style="font-size:.5rem;vertical-align:middle;"></i><?= $statusLabel ?>
                    </span>
                    <?php if ($student['is_placed'] && $student['placed_company']): ?>
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1" style="font-size:0.72rem;">
                        <?= htmlspecialchars($student['placed_company']) ?>
                    </span>
                    <?php endif; ?>
                </div>

                <!-- Social Links -->
                <div class="d-flex justify-content-center gap-3 mb-3">
                    <?php if ($student['linkedin']): ?>
                    <a href="<?= htmlspecialchars($student['linkedin']) ?>" target="_blank" class="btn btn-sm btn-outline-primary" title="LinkedIn" style="width:34px;height:34px;padding:0;display:flex;align-items:center;justify-content:center;border-radius:50%;">
                        <i class="fab fa-linkedin-in"></i>
                    </a>
                    <?php endif; ?>
                    <?php if ($student['github']): ?>
                    <a href="<?= htmlspecialchars($student['github']) ?>" target="_blank" class="btn btn-sm btn-outline-dark" title="GitHub" style="width:34px;height:34px;padding:0;display:flex;align-items:center;justify-content:center;border-radius:50%;">
                        <i class="fab fa-github"></i>
                    </a>
                    <?php endif; ?>
                    <?php if ($student['portfolio']): ?>
                    <a href="<?= htmlspecialchars($student['portfolio']) ?>" target="_blank" class="btn btn-sm btn-outline-secondary" title="Portfolio" style="width:34px;height:34px;padding:0;display:flex;align-items:center;justify-content:center;border-radius:50%;">
                        <i class="fas fa-globe"></i>
                    </a>
                    <?php endif; ?>
                    <?php if ($applicantUserId > 0): ?>
                    <a href="<?= url('/chat?user_id=' . $applicantUserId) ?>" class="btn btn-sm btn-outline-info" title="Message" style="width:34px;height:34px;padding:0;display:flex;align-items:center;justify-content:center;border-radius:50%;">
                        <i class="fas fa-comment-dots"></i>
                    </a>
                    <?php else: ?>
                    <button class="btn btn-sm btn-outline-secondary disabled" title="Chat Unavailable" style="width:34px;height:34px;padding:0;display:flex;align-items:center;justify-content:center;border-radius:50%;" disabled>
                        <i class="fas fa-comment-dots"></i>
                    </button>
                    <?php endif; ?>
                </div>

                <div class="border-top pt-3">
                    <!-- Profile Completion -->
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <small class="text-muted fw-semibold">Profile Completion</small>
                        <small class="fw-bold text-primary"><?= $profileCompletion ?>%</small>
                    </div>
                    <div class="progress mb-3" style="height:6px;border-radius:4px;">
                        <div class="progress-bar bg-primary" style="width:<?= $profileCompletion ?>%;border-radius:4px;"></div>
                    </div>

                    <!-- AI Match Score -->
                    <div class="d-flex align-items-center justify-content-between p-2 rounded-3 mb-0"
                         style="background:var(--<?= $matchColor == 'warning' ? 'warning' : $matchColor ?>-subtle, rgba(37,99,235,0.06));">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fas fa-robot text-<?= $matchColor ?>"></i>
                            <span class="fw-semibold small">AI Match Score</span>
                        </div>
                        <div class="d-flex align-items-center gap-1">
                            <span class="fw-bold text-<?= $matchColor ?>" style="font-size:1.1rem;"><?= $aiMatchScore ?>%</span>
                            <span class="badge bg-<?= $matchColor ?> ms-1" style="font-size:0.65rem;"><?= $matchLabel ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── Contact Info Card ─────────────────────────────────────── -->
        <div class="card shadow-sm mb-4">
            <div class="card-header py-2 px-4 d-flex align-items-center gap-2 border-0 bg-transparent">
                <i class="fas fa-address-card text-primary fa-sm"></i>
                <span class="fw-bold small" style="font-size:0.83rem;text-transform:uppercase;letter-spacing:.5px;">Contact Information</span>
            </div>
            <div class="card-body pt-0 pb-3">
                <ul class="list-unstyled mb-0" style="font-size:0.875rem;">
                    <?php if ($student['email']): ?>
                    <li class="d-flex align-items-center gap-2 py-2 border-bottom">
                        <i class="fas fa-envelope text-muted" style="width:16px;"></i>
                        <a href="mailto:<?= htmlspecialchars($student['email']) ?>" class="text-dark text-truncate"><?= htmlspecialchars($student['email']) ?></a>
                    </li>
                    <?php endif; ?>
                    <?php if ($student['phone']): ?>
                    <li class="d-flex align-items-center gap-2 py-2 border-bottom">
                        <i class="fas fa-phone text-muted" style="width:16px;"></i>
                        <span><?= htmlspecialchars($student['phone']) ?></span>
                    </li>
                    <?php endif; ?>
                    <?php if ($student['city'] || $student['state']): ?>
                    <li class="d-flex align-items-center gap-2 py-2 border-bottom">
                        <i class="fas fa-map-marker-alt text-muted" style="width:16px;"></i>
                        <span><?= htmlspecialchars(implode(', ', array_filter([$student['city'], $student['state']]))) ?></span>
                    </li>
                    <?php endif; ?>
                    <?php if ($student['enrollment_no']): ?>
                    <li class="d-flex align-items-center gap-2 py-2 border-bottom">
                        <i class="fas fa-id-badge text-muted" style="width:16px;"></i>
                        <span class="font-monospace"><?= htmlspecialchars($student['enrollment_no']) ?></span>
                    </li>
                    <?php endif; ?>
                    <?php if ($student['gender']): ?>
                    <li class="d-flex align-items-center gap-2 py-2">
                        <i class="fas fa-user-circle text-muted" style="width:16px;"></i>
                        <span><?= ucfirst($student['gender']) ?></span>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>

        <!-- ── Application History for this Company ──────────────────── -->
        <div class="card shadow-sm mb-4">
            <div class="card-header py-2 px-4 d-flex align-items-center gap-2 border-0 bg-transparent">
                <i class="fas fa-briefcase text-primary fa-sm"></i>
                <span class="fw-bold small" style="font-size:0.83rem;text-transform:uppercase;letter-spacing:.5px;">Applications at <?= htmlspecialchars($company['company_name']) ?></span>
            </div>
            <div class="card-body pt-0 pb-2">
                <?php if (empty($applications)): ?>
                <p class="text-muted small text-center py-2">No applications found.</p>
                <?php else: ?>
                <?php foreach ($applications as $app): ?>
                <div class="d-flex align-items-center justify-content-between gap-2 py-2 border-bottom">
                    <div class="min-w-0">
                        <div class="fw-semibold small text-truncate"><?= htmlspecialchars($app['job_title']) ?></div>
                        <div class="text-muted" style="font-size:0.72rem;"><?= timeAgo($app['applied_at']) ?></div>
                    </div>
                    <div class="d-flex align-items-center gap-2 flex-shrink-0">
                        <span class="badge <?= getStatusBadgeClass($app['status']) ?>"><?= ucfirst($app['status']) ?></span>
                        <div class="dropdown">
                            <button class="btn btn-xs btn-light border dropdown-toggle" style="font-size:.75rem;padding:3px 8px;" data-bs-toggle="dropdown">
                                <i class="fas fa-ellipsis-h"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                <?php foreach (['shortlisted'=>'Shortlist','selected'=>'Select','rejected'=>'Reject'] as $sk=>$sv): ?>
                                <?php if ($app['status'] !== $sk): ?>
                                <li><a class="dropdown-item small" href="#" onclick="updateStatus(<?= $app['id'] ?>,'<?= $sk ?>')">
                                    <i class="fas fa-<?= $sk==='selected'?'check-circle text-success':($sk==='rejected'?'times-circle text-danger':'star text-warning') ?> me-2"></i><?= $sv ?>
                                </a></li>
                                <?php endif; ?>
                                <?php endforeach; ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item small" href="#" data-bs-toggle="modal" data-bs-target="#scheduleModal" onclick="setInterviewApp(<?= $app['id'] ?>)">
                                    <i class="fas fa-calendar-plus text-primary me-2"></i>Schedule Interview
                                </a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

    </div><!-- /col-left -->

    <!-- ══════════════════════════════════════════════════════════════════
         RIGHT COLUMN — Tabbed full profile
         ══════════════════════════════════════════════════════════════════ -->
    <div class="col-xl-8 col-lg-7">

        <!-- ── Tab Navigation ──────────────────────────────────────── -->
        <ul class="nav nav-tabs mb-0 border-bottom-0" id="profileTabs" role="tablist"
            style="gap:2px; border-bottom:2px solid var(--border-color);">
            <?php
            $tabs = [
                'overview'        => ['fas fa-user',           'Overview'],
                'academic'        => ['fas fa-graduation-cap', 'Academic'],
                'skills'          => ['fas fa-tools',          'Skills & Certs'],
                'projects'        => ['fas fa-code-branch',    'Projects'],
                'experience'      => ['fas fa-briefcase',      'Experience'],
                'resume'          => ['fas fa-file-pdf',       'Resume'],
                'documents'       => ['fas fa-folder-open',    'Documents'],
            ];
            $first = true;
            foreach ($tabs as $tid => [$icon, $label]):
                $active = $first ? 'active' : '';
                $first = false;
            ?>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?= $active ?> px-3 py-2 fw-semibold"
                        id="tab-<?= $tid ?>"
                        data-bs-toggle="tab"
                        data-bs-target="#pane-<?= $tid ?>"
                        type="button"
                        role="tab"
                        style="font-size:0.82rem;">
                    <i class="<?= $icon ?> me-1"></i><?= $label ?>
                </button>
            </li>
            <?php endforeach; ?>
        </ul>

        <!-- ── Tab Panes ───────────────────────────────────────────── -->
        <div class="tab-content" id="profileTabsContent">

            <!-- ════════ OVERVIEW ════════ -->
            <div class="tab-pane fade show active pt-4" id="pane-overview" role="tabpanel">

                <!-- Career Objective -->
                <?php if ($student['bio']): ?>
                <div class="card shadow-sm mb-4">
                    <div class="card-header border-0 bg-transparent py-2 px-4">
                        <span class="fw-bold small text-uppercase letter-space-sm"><i class="fas fa-bullseye text-primary me-2"></i>Career Objective</span>
                    </div>
                    <div class="card-body pt-0 pb-3">
                        <p class="text-secondary mb-0" style="font-size:0.9rem;line-height:1.7;">
                            <?= nl2br(htmlspecialchars($student['bio'])) ?>
                        </p>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Quick Stats Row -->
                <div class="row g-3 mb-4">
                    <?php
                    $stats = [
                        ['CGPA',            $student['cgpa'] ? number_format($student['cgpa'],2) : '—',  'fas fa-star',          'warning'],
                        ['Passing Year',    $student['passing_year'] ?? '—',                              'fas fa-calendar',      'primary'],
                        ['Experience',      ($student['experience_years'] ?? 0) . ' yrs',                'fas fa-clock',         'info'],
                        ['Backlogs',        ($student['backlogs'] ?? 0),                                  'fas fa-exclamation-circle', ($student['backlogs'] ?? 0) == 0 ? 'success' : 'danger'],
                    ];
                    foreach ($stats as [$label, $value, $icon, $color]):
                    ?>
                    <div class="col-6 col-md-3">
                        <div class="card shadow-sm h-100 text-center p-3">
                            <div class="mb-1">
                                <i class="<?= $icon ?> text-<?= $color ?> fa-lg"></i>
                            </div>
                            <div class="fw-bold" style="font-size:1.1rem;"><?= htmlspecialchars((string)$value) ?></div>
                            <div class="text-muted" style="font-size:0.72rem;text-transform:uppercase;letter-spacing:.5px;"><?= $label ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Languages -->
                <?php if (!empty($languages)): ?>
                <div class="card shadow-sm mb-4">
                    <div class="card-header border-0 bg-transparent py-2 px-4">
                        <span class="fw-bold small text-uppercase"><i class="fas fa-language text-primary me-2"></i>Languages</span>
                    </div>
                    <div class="card-body pt-0 pb-3">
                        <div class="d-flex flex-wrap gap-2">
                            <?php foreach ($languages as $lang): ?>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2" style="font-size:0.82rem;">
                                <?= htmlspecialchars($lang['language']) ?>
                                <span class="ms-1 text-muted fw-normal">(<?= ucfirst($lang['proficiency']) ?>)</span>
                            </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Achievements -->
                <?php if (!empty($achievements)): ?>
                <div class="card shadow-sm mb-4">
                    <div class="card-header border-0 bg-transparent py-2 px-4">
                        <span class="fw-bold small text-uppercase"><i class="fas fa-trophy text-warning me-2"></i>Achievements</span>
                    </div>
                    <div class="card-body pt-0 pb-3">
                        <div class="d-flex flex-column gap-2">
                            <?php foreach ($achievements as $ach): ?>
                            <div class="d-flex align-items-start gap-3 p-2 rounded-3 border">
                                <div class="flex-shrink-0 mt-1" style="width:28px;height:28px;border-radius:50%;background:var(--warning-subtle);display:flex;align-items:center;justify-content:center;">
                                    <i class="fas fa-medal text-warning fa-xs"></i>
                                </div>
                                <div class="min-w-0">
                                    <div class="fw-semibold small"><?= htmlspecialchars($ach['title']) ?></div>
                                    <?php if ($ach['description']): ?>
                                    <div class="text-muted" style="font-size:0.8rem;"><?= htmlspecialchars($ach['description']) ?></div>
                                    <?php endif; ?>
                                    <?php if ($ach['date']): ?>
                                    <div class="text-muted" style="font-size:0.72rem;"><?= formatDate($ach['date']) ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- ════════ ACADEMIC ════════ -->
            <div class="tab-pane fade pt-4" id="pane-academic" role="tabpanel">
                <div class="card shadow-sm">
                    <div class="card-header border-0 bg-transparent py-2 px-4">
                        <span class="fw-bold small text-uppercase"><i class="fas fa-graduation-cap text-primary me-2"></i>Academic Information</span>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <tbody>
                                    <?php
                                    $academics = [
                                        ['Degree',             $student['degree'] ?? 'B.Tech'],
                                        ['Branch / Stream',    $student['branch'] ?? '—'],
                                        ['Enrollment No.',     $student['enrollment_no'] ?? '—'],
                                        ['Admission Year',     $student['admission_year'] ?? '—'],
                                        ['Passing Year',       $student['passing_year'] ?? '—'],
                                        ['CGPA / GPA',         $student['cgpa'] ? number_format($student['cgpa'],2) . ' / 10.00' : '—'],
                                        ['10th Percentage',    $student['tenth_percentage'] ? $student['tenth_percentage'] . '%' : '—'],
                                        ['12th / Diploma %',   ($student['twelfth_percentage'] ?? $student['diploma_percentage']) ? (($student['twelfth_percentage'] ?: $student['diploma_percentage'])) . '%' : '—'],
                                        ['Active Backlogs',    $student['active_backlogs'] ?? '0'],
                                        ['Total Backlogs',     $student['backlogs'] ?? '0'],
                                        ['Preferred Location', $student['preferred_location'] ?? '—'],
                                    ];
                                    foreach ($academics as [$label, $value]):
                                    ?>
                                    <tr>
                                        <td class="text-muted fw-semibold" style="font-size:0.82rem;width:40%;"><?= $label ?></td>
                                        <td class="fw-medium" style="font-size:0.88rem;"><?= htmlspecialchars((string)$value) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ════════ SKILLS & CERTIFICATIONS ════════ -->
            <div class="tab-pane fade pt-4" id="pane-skills" role="tabpanel">

                <!-- Skills -->
                <?php $skillsList = array_filter(array_map('trim', explode(',', $student['skills'] ?? ''))); ?>
                <div class="card shadow-sm mb-4">
                    <div class="card-header border-0 bg-transparent py-2 px-4">
                        <span class="fw-bold small text-uppercase"><i class="fas fa-tools text-primary me-2"></i>Technical Skills</span>
                    </div>
                    <div class="card-body pt-0 pb-3">
                        <?php if (!empty($skillsList)): ?>
                        <div class="d-flex flex-wrap gap-2">
                            <?php foreach ($skillsList as $sk): ?>
                            <span class="badge bg-primary text-white px-3 py-2" style="font-size:0.82rem;font-weight:500;border-radius:20px;">
                                <?= htmlspecialchars($sk) ?>
                            </span>
                            <?php endforeach; ?>
                        </div>
                        <?php else: ?>
                        <p class="text-muted mb-0 small">No skills listed.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Certifications -->
                <div class="card shadow-sm">
                    <div class="card-header border-0 bg-transparent py-2 px-4">
                        <span class="fw-bold small text-uppercase"><i class="fas fa-certificate text-warning me-2"></i>Certifications</span>
                    </div>
                    <div class="card-body pt-0 pb-3">
                        <?php if (!empty($certifications)): ?>
                        <div class="d-flex flex-column gap-3">
                            <?php foreach ($certifications as $cert): ?>
                            <div class="d-flex align-items-start gap-3 p-3 rounded-3 border">
                                <div class="flex-shrink-0" style="width:36px;height:36px;border-radius:50%;background:var(--warning-subtle);display:flex;align-items:center;justify-content:center;">
                                    <i class="fas fa-certificate text-warning"></i>
                                </div>
                                <div class="flex-grow-1 min-w-0">
                                    <div class="fw-bold" style="font-size:0.88rem;"><?= htmlspecialchars($cert['title']) ?></div>
                                    <?php if ($cert['issuing_org']): ?>
                                    <div class="text-muted small"><?= htmlspecialchars($cert['issuing_org']) ?></div>
                                    <?php endif; ?>
                                    <div class="d-flex flex-wrap gap-2 mt-1">
                                        <?php if ($cert['issue_date']): ?>
                                        <span class="badge bg-light text-dark border" style="font-size:0.72rem;">Issued: <?= formatDate($cert['issue_date']) ?></span>
                                        <?php endif; ?>
                                        <?php if ($cert['expiry_date']): ?>
                                        <span class="badge bg-light text-dark border" style="font-size:0.72rem;">Expires: <?= formatDate($cert['expiry_date']) ?></span>
                                        <?php endif; ?>
                                        <?php if ($cert['credential_url']): ?>
                                        <a href="<?= htmlspecialchars($cert['credential_url']) ?>" target="_blank" class="badge bg-primary text-white" style="font-size:0.72rem;">View Credential</a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php else: ?>
                        <p class="text-muted mb-0 small">No certifications listed.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- ════════ PROJECTS ════════ -->
            <div class="tab-pane fade pt-4" id="pane-projects" role="tabpanel">
                <div class="card shadow-sm">
                    <div class="card-header border-0 bg-transparent py-2 px-4">
                        <span class="fw-bold small text-uppercase"><i class="fas fa-code-branch text-primary me-2"></i>Projects</span>
                    </div>
                    <div class="card-body pt-0 pb-3">
                        <?php if (!empty($projects)): ?>
                        <div class="d-flex flex-column gap-4">
                            <?php foreach ($projects as $proj): ?>
                            <div class="p-3 rounded-3 border" style="border-left:4px solid var(--primary) !important;">
                                <div class="d-flex align-items-start justify-content-between gap-2 flex-wrap mb-2">
                                    <div>
                                        <h6 class="fw-bold mb-0" style="font-size:0.92rem;"><?= htmlspecialchars($proj['title']) ?></h6>
                                        <?php if ($proj['start_date'] || $proj['end_date']): ?>
                                        <div class="text-muted" style="font-size:0.75rem;">
                                            <?= $proj['start_date'] ? formatDate($proj['start_date']) : '' ?>
                                            <?= ($proj['start_date'] && $proj['end_date']) ? ' – ' : '' ?>
                                            <?= $proj['end_date'] ? formatDate($proj['end_date']) : ($proj['start_date'] ? ' – Present' : '') ?>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="d-flex gap-1">
                                        <?php if ($proj['project_url']): ?>
                                        <a href="<?= htmlspecialchars($proj['project_url']) ?>" target="_blank" class="btn btn-sm btn-outline-primary" style="font-size:0.72rem;padding:2px 8px;">
                                            <i class="fas fa-external-link-alt me-1"></i>Live
                                        </a>
                                        <?php endif; ?>
                                        <?php if ($proj['github_url']): ?>
                                        <a href="<?= htmlspecialchars($proj['github_url']) ?>" target="_blank" class="btn btn-sm btn-outline-dark" style="font-size:0.72rem;padding:2px 8px;">
                                            <i class="fab fa-github me-1"></i>Code
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php if ($proj['description']): ?>
                                <p class="text-muted mb-2" style="font-size:0.83rem;line-height:1.6;"><?= nl2br(htmlspecialchars($proj['description'])) ?></p>
                                <?php endif; ?>
                                <?php if ($proj['technologies']): ?>
                                <div class="d-flex flex-wrap gap-1">
                                    <?php foreach (array_filter(array_map('trim', explode(',', $proj['technologies']))) as $tech): ?>
                                    <span class="badge bg-light text-dark border" style="font-size:0.72rem;"><?= htmlspecialchars($tech) ?></span>
                                    <?php endforeach; ?>
                                </div>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-code fa-2x text-muted mb-2"></i>
                            <p class="text-muted mb-0">No projects added yet.</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- ════════ EXPERIENCE ════════ -->
            <div class="tab-pane fade pt-4" id="pane-experience" role="tabpanel">
                <div class="card shadow-sm mb-4">
                    <div class="card-header border-0 bg-transparent py-2 px-4">
                        <span class="fw-bold small text-uppercase"><i class="fas fa-briefcase text-primary me-2"></i>Work Experience</span>
                    </div>
                    <div class="card-body py-3">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="text-center p-3 rounded-3 border">
                                    <div class="fw-bold" style="font-size:1.5rem;color:var(--primary);"><?= number_format((float)($student['experience_years'] ?? 0), 1) ?></div>
                                    <div class="text-muted small">Years of Experience</div>
                                </div>
                            </div>
                            <div class="col-md-8 d-flex align-items-center">
                                <p class="text-muted mb-0" style="font-size:0.88rem;">
                                    <?php if (($student['experience_years'] ?? 0) > 0): ?>
                                    This candidate has <strong><?= number_format((float)$student['experience_years'], 1) ?> years</strong> of work experience.
                                    <?php else: ?>
                                    This is a <strong>fresher</strong> candidate with no prior industry experience.
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if ($student['is_placed'] && $student['placed_company']): ?>
                <div class="card shadow-sm border-success">
                    <div class="card-header border-0 bg-success-subtle py-2 px-4">
                        <span class="fw-bold small text-success text-uppercase"><i class="fas fa-check-circle me-2"></i>Placement Status</span>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="text-muted small">Company</div>
                                <div class="fw-bold"><?= htmlspecialchars($student['placed_company']) ?></div>
                            </div>
                            <?php if ($student['placed_package']): ?>
                            <div class="col-md-4">
                                <div class="text-muted small">Package</div>
                                <div class="fw-bold text-success"><?= number_format((float)$student['placed_package'], 2) ?> LPA</div>
                            </div>
                            <?php endif; ?>
                            <?php if ($student['placed_date']): ?>
                            <div class="col-md-4">
                                <div class="text-muted small">Placement Date</div>
                                <div class="fw-bold"><?= formatDate($student['placed_date']) ?></div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- ════════ RESUME ════════ -->
            <div class="tab-pane fade pt-4" id="pane-resume" role="tabpanel">
                <?php if ($student['resume_path']): ?>
                <div class="card shadow-sm">
                    <div class="card-header py-2 px-4 border-0 bg-transparent d-flex align-items-center justify-content-between">
                        <span class="fw-bold small text-uppercase"><i class="fas fa-file-pdf text-danger me-2"></i>Resume</span>
                        <div class="d-flex gap-2">
                            <a href="<?= $resumePreviewUrl ?>" target="_blank" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-expand me-1"></i>Full Screen
                            </a>
                            <a href="<?= $resumeDownloadUrl ?>" class="btn btn-sm btn-primary">
                                <i class="fas fa-download me-1"></i>Download
                            </a>
                        </div>
                    </div>
                    <div class="card-body p-0" style="border-top:1px solid var(--border-color);">
                        <!-- Resume meta info -->
                        <div class="px-4 py-2 bg-light border-bottom d-flex flex-wrap align-items-center gap-3">
                            <div class="d-flex align-items-center gap-2">
                                <i class="fas fa-file-pdf text-danger"></i>
                                <span class="fw-medium small"><?= htmlspecialchars($student['resume_original_name'] ?: $student['resume_path']) ?></span>
                            </div>
                            <span class="text-muted small">PDF Document</span>
                        </div>
                        <!-- Embedded PDF Preview -->
                        <iframe
                            src="<?= $resumePreviewUrl ?>"
                            style="width:100%;height:700px;border:none;display:block;"
                            title="Resume Preview">
                            <div class="text-center py-5">
                                <p>Your browser cannot preview PDFs inline.</p>
                                <a href="<?= $resumePreviewUrl ?>" target="_blank" class="btn btn-primary">Open Resume</a>
                            </div>
                        </iframe>
                    </div>
                </div>
                <?php else: ?>
                <div class="card shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-file-pdf fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No Resume Uploaded</h5>
                        <p class="text-muted small">This student has not uploaded a resume yet.</p>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- ════════ DOCUMENTS ════════ -->
            <div class="tab-pane fade pt-4" id="pane-documents" role="tabpanel">
                <?php if (!empty($documents)): ?>
                <div class="d-flex flex-column gap-3" id="docsContainer">
                    <?php foreach ($documents as $doc): ?>
                    <?php
                    $docType    = ucfirst(str_replace('_', ' ', $doc['document_type'] ?? 'Other'));
                    $docSize    = $doc['file_size'] ? round($doc['file_size'] / 1024, 1) . ' KB' : '—';
                    $docMime    = $doc['mime_type'] ?? '';
                    $isPdf      = str_contains($docMime, 'pdf') || str_ends_with(strtolower($doc['original_name']), '.pdf');
                    $previewUrl = url('/company/serve-document/' . $doc['id']);
                    $downloadUrl = $previewUrl . '?download=1';
                    $docIcon    = $isPdf ? 'fa-file-pdf text-danger' : 'fa-file text-secondary';
                    ?>
                    <div class="card shadow-sm">
                        <div class="card-header py-2 px-4 border-0 bg-transparent d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <div class="d-flex align-items-center gap-3">
                                <i class="fas <?= $docIcon ?> fa-lg"></i>
                                <div>
                                    <div class="fw-semibold small"><?= htmlspecialchars($doc['original_name']) ?></div>
                                    <div class="text-muted d-flex flex-wrap gap-2" style="font-size:0.72rem;">
                                        <span class="badge bg-light text-dark border"><?= $docType ?></span>
                                        <span><?= $docSize ?></span>
                                        <span><?= date('d M Y', strtotime($doc['created_at'])) ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex gap-2">
                                <?php if ($isPdf): ?>
                                <button class="btn btn-sm btn-outline-secondary" onclick="toggleDocPreview(<?= $doc['id'] ?>)">
                                    <i class="fas fa-eye me-1"></i>Preview
                                </button>
                                <?php endif; ?>
                                <a href="<?= $downloadUrl ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-download me-1"></i>Download
                                </a>
                            </div>
                        </div>
                        <?php if ($isPdf): ?>
                        <div id="docPreview-<?= $doc['id'] ?>" style="display:none;border-top:1px solid var(--border-color);">
                            <iframe
                                src="about:blank"
                                data-src="<?= $previewUrl ?>"
                                id="docFrame-<?= $doc['id'] ?>"
                                style="width:100%;height:600px;border:none;display:block;"
                                title="<?= htmlspecialchars($doc['original_name']) ?>">
                            </iframe>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="card shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No Documents Uploaded</h5>
                        <p class="text-muted small">This student has not uploaded any supporting documents.</p>
                    </div>
                </div>
                <?php endif; ?>
            </div>

        </div><!-- /tab-content -->
    </div><!-- /col-right -->
</div><!-- /row -->

<!-- ── Schedule Interview Modal (shared with applications page) ────── -->
<div class="modal fade" id="scheduleModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title"><i class="fas fa-calendar-plus text-primary me-2"></i>Schedule Interview</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <form id="scheduleForm" method="POST">
        <?= CsrfMiddleware::tokenField() ?>
        <div class="modal-body">
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label">Round *</label><input type="text" class="form-control" name="round" value="Round 1" required></div>
                <div class="col-md-6"><label class="form-label">Mode</label><select class="form-select" name="mode"><option value="offline">Offline</option><option value="online">Online</option></select></div>
                <div class="col-md-6"><label class="form-label">Date *</label><input type="date" class="form-control" name="interview_date" min="<?= date('Y-m-d') ?>" required></div>
                <div class="col-md-6"><label class="form-label">Time *</label><input type="time" class="form-control" name="interview_time" required></div>
                <div class="col-12"><label class="form-label">Venue</label><input type="text" class="form-control" name="venue" placeholder="Room/Building"></div>
                <div class="col-12"><label class="form-label">Meeting Link</label><input type="url" class="form-control" name="meeting_link" placeholder="https://..."></div>
                <div class="col-12"><label class="form-label">Instructions</label><textarea class="form-control" name="instructions" rows="2"></textarea></div>
            </div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Schedule</button></div>
    </form>
</div></div></div>

<script>
function updateStatus(appId, status) {
    if (!confirm('Update status to ' + status + '?')) return;
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = TPMS.baseUrl + '/company/update-application/' + appId;
    form.innerHTML = '<input name="csrf_token" value="' + TPMS.csrfToken + '"><input name="status" value="' + status + '">';
    document.body.appendChild(form);
    form.submit();
}

function setInterviewApp(appId) {
    document.getElementById('scheduleForm').action = TPMS.baseUrl + '/company/schedule-interview/' + appId;
}

// Lazy-load document PDF preview frames
function toggleDocPreview(docId) {
    const pane  = document.getElementById('docPreview-' + docId);
    const frame = document.getElementById('docFrame-' + docId);
    if (!pane) return;
    if (pane.style.display === 'none') {
        pane.style.display = 'block';
        // Lazy-load: only set src when first opened
        if (frame && frame.src === 'about:blank' && frame.dataset.src) {
            frame.src = frame.dataset.src;
        }
    } else {
        pane.style.display = 'none';
    }
}

// Open Resume tab if URL hash says so
if (window.location.hash === '#resume') {
    const resumeTab = document.getElementById('tab-resume');
    if (resumeTab) resumeTab.click();
}
if (window.location.hash === '#documents') {
    const docTab = document.getElementById('tab-documents');
    if (docTab) docTab.click();
}
</script>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>
