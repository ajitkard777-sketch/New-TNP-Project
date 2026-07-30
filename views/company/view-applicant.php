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
$applicantUserId   = (int)($student['user_id'] ?? 0);
?>

<!-- ── Content Header ───────────────────────────────────────────────── -->
<div class="content-header d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
    <div>
        <h1 class="page-title"><?= $fullName ?></h1>
        <p class="subtitle">
            <?= htmlspecialchars($student['degree'] ?? 'B.Tech') ?>
            <?= !empty($student['branch']) ? ' · ' . htmlspecialchars($student['branch']) : '' ?>
            <?= !empty($student['enrollment_no']) ? ' | ' . htmlspecialchars($student['enrollment_no']) : '' ?>
        </p>
    </div>
    <div class="d-flex gap-2 flex-wrap align-items-center">
        <?php if ($latestApp): ?>
        <a href="<?= url('/company/applications/' . $latestApp['job_id']) ?>" class="btn btn-light border btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Back to Applicants
        </a>
        <?php else: ?>
        <a href="<?= url('/company/jobs') ?>" class="btn btn-light border btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Back to Jobs
        </a>
        <?php endif; ?>

        <?php if ($resumeDownloadUrl): ?>
        <a href="<?= $resumeDownloadUrl ?>" class="btn btn-outline-primary btn-sm">
            <i class="fas fa-download me-1"></i> Download Resume
        </a>
        <?php endif; ?>

        <?php if ($applicantUserId > 0): ?>
        <a href="<?= url('/chat?user_id=' . $applicantUserId) ?>" class="btn btn-primary btn-sm">
            <i class="fas fa-comment-dots me-1"></i> Message
        </a>
        <?php endif; ?>
    </div>
</div>

<div class="row g-4">

    <!-- ══════════════════════════════════════════════════════════════════
         LEFT COLUMN — Profile Summary Card & Applications
         ══════════════════════════════════════════════════════════════════ -->
    <div class="col-lg-4">

        <!-- Profile Card -->
        <div class="card mb-4">
            <div class="card-body profile-card text-center p-4">
                <img src="<?= $avatarUrl ?>" alt="<?= $fullName ?>" class="profile-avatar mb-3"
                     style="width:96px;height:96px;border-radius:50%;object-fit:cover;border:3px solid var(--primary-soft, #e0e7ff);"
                     onerror="this.src='<?= asset('images/default-avatar.png') ?>'">
                <h5 class="profile-name mb-1 fw-bold"><?= $fullName ?></h5>
                <p class="profile-role text-muted small mb-2"><?= htmlspecialchars($student['email'] ?? '') ?></p>

                <!-- Status & AI Match Badges -->
                <div class="d-flex align-items-center justify-content-center gap-2 mb-3 flex-wrap">
                    <span class="badge bg-<?= $statusColor ?>-subtle text-<?= $statusColor ?> border border-<?= $statusColor ?>-subtle px-2 py-1" style="font-size:0.78rem;">
                        <i class="fas fa-circle me-1" style="font-size:.45rem;vertical-align:middle;"></i><?= $statusLabel ?>
                    </span>
                    <span class="badge bg-<?= $matchColor ?>-subtle text-<?= $matchColor ?> border border-<?= $matchColor ?>-subtle px-2 py-1" style="font-size:0.78rem;">
                        <i class="fas fa-robot me-1"></i>Match: <?= $aiMatchScore ?>% (<?= $matchLabel ?>)
                    </span>
                </div>

                <div class="text-start border-top pt-3">
                    <div class="row g-2" style="font-size:0.85rem">
                        <div class="col-6"><strong>Phone:</strong><br><?= htmlspecialchars($student['phone'] ?? 'N/A') ?></div>
                        <div class="col-6"><strong>DOB:</strong><br><?= $student['dob'] ? formatDate($student['dob']) : 'N/A' ?></div>
                        <div class="col-6"><strong>Gender:</strong><br><?= ucfirst($student['gender'] ?? 'N/A') ?></div>
                        <div class="col-6"><strong>CGPA:</strong><br><span class="text-primary fw-bold"><?= $student['cgpa'] ? number_format($student['cgpa'], 2) : 'N/A' ?></span></div>
                        <div class="col-6"><strong>10th %:</strong><br><?= $student['tenth_percentage'] ? $student['tenth_percentage'] . '%' : 'N/A' ?></div>
                        <div class="col-6"><strong>12th %:</strong><br><?= $student['twelfth_percentage'] ? $student['twelfth_percentage'] . '%' : 'N/A' ?></div>
                        <div class="col-6"><strong>Backlogs:</strong><br><?= $student['backlogs'] ?? 0 ?> (Active: <?= $student['active_backlogs'] ?? 0 ?>)</div>
                        <div class="col-6"><strong>Passing Year:</strong><br><?= $student['passing_year'] ?? 'N/A' ?></div>
                        <?php if (!empty($student['city']) || !empty($student['state'])): ?>
                        <div class="col-12"><strong>Location:</strong><br><?= htmlspecialchars(implode(', ', array_filter([$student['city'] ?? '', $student['state'] ?? '']))) ?></div>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($student['skills'])): ?>
                    <div class="mt-3">
                        <strong>Skills:</strong>
                        <div class="d-flex flex-wrap gap-1 mt-1">
                            <?php foreach (array_filter(array_map('trim', explode(',', $student['skills']))) as $sk): ?>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1" style="font-size:0.75rem;"><?= htmlspecialchars($sk) ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if ($resumePreviewUrl): ?>
                    <a href="<?= $resumePreviewUrl ?>" target="_blank" class="btn btn-outline-primary btn-sm mt-3 w-100">
                        <i class="fas fa-file-pdf me-1"></i> View Resume
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Applications at this company -->
        <div class="card mb-4">
            <div class="card-header py-3">
                <h6 class="mb-0 fw-bold"><i class="fas fa-paper-plane me-2 text-primary"></i>Applications (<?= count($applications) ?>)</h6>
            </div>
            <div class="card-body p-0">
                <?php if (empty($applications)): ?>
                <div class="p-3 text-center text-muted small">No applications found for your jobs.</div>
                <?php else: ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($applications as $app): ?>
                    <div class="list-group-item p-3">
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <div class="fw-bold text-truncate me-2" style="font-size:0.88rem;"><?= htmlspecialchars($app['job_title']) ?></div>
                            <span class="badge <?= getStatusBadgeClass($app['status']) ?>"><?= ucfirst($app['status']) ?></span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-2" style="font-size:0.78rem;">
                            <span class="text-muted"><i class="far fa-clock me-1"></i><?= timeAgo($app['applied_at']) ?></span>
                            <div class="dropdown">
                                <button class="btn btn-xs btn-light border dropdown-toggle fw-semibold" style="font-size:0.75rem;padding:2px 8px;" data-bs-toggle="dropdown" data-bs-popper-config='{"strategy":"fixed"}'>
                                    Action
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-lg" style="border-radius: 10px; font-size: 0.82rem; min-width: 190px;">
                                    <?php if ($app['status'] !== 'shortlisted'): ?>
                                    <li><a class="dropdown-item py-2" href="#" onclick="updateStatus(<?= $app['id'] ?>,'shortlisted');return false;"><i class="fas fa-star text-warning me-2"></i>Shortlist</a></li>
                                    <?php endif; ?>
                                    <?php if ($app['status'] !== 'selected'): ?>
                                    <li><a class="dropdown-item py-2" href="#" onclick="updateStatus(<?= $app['id'] ?>,'selected');return false;"><i class="fas fa-check-circle text-success me-2"></i>Select Candidate</a></li>
                                    <?php endif; ?>
                                    <?php if ($app['status'] !== 'rejected'): ?>
                                    <li><a class="dropdown-item py-2" href="#" onclick="updateStatus(<?= $app['id'] ?>,'rejected');return false;"><i class="fas fa-times-circle text-danger me-2"></i>Reject Application</a></li>
                                    <?php endif; ?>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item py-2" href="#" data-bs-toggle="modal" data-bs-target="#scheduleModal" onclick="setInterviewApp(<?= $app['id'] ?>)"><i class="fas fa-calendar-plus text-primary me-2"></i>Schedule Interview</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

    </div><!-- /col-lg-4 -->

    <!-- ══════════════════════════════════════════════════════════════════
         RIGHT COLUMN — Details & Tabs
         ══════════════════════════════════════════════════════════════════ -->
    <div class="col-lg-8">

        <!-- Tab Navigation -->
        <ul class="nav nav-tabs mb-4" id="profileTabs" role="tablist">
            <li class="nav-item"><a class="nav-link active fw-semibold" id="tab-overview" data-bs-toggle="tab" href="#pane-overview"><i class="fas fa-user me-1"></i> Overview</a></li>
            <li class="nav-item"><a class="nav-link fw-semibold" id="tab-academic" data-bs-toggle="tab" href="#pane-academic"><i class="fas fa-graduation-cap me-1"></i> Academic</a></li>
            <li class="nav-item"><a class="nav-link fw-semibold" id="tab-projects" data-bs-toggle="tab" href="#pane-projects"><i class="fas fa-code-branch me-1"></i> Projects (<?= count($projects) ?>)</a></li>
            <li class="nav-item"><a class="nav-link fw-semibold" id="tab-certs" data-bs-toggle="tab" href="#pane-certs"><i class="fas fa-certificate me-1"></i> Certifications (<?= count($certifications) ?>)</a></li>
            <li class="nav-item"><a class="nav-link fw-semibold" id="tab-docs" data-bs-toggle="tab" href="#pane-docs"><i class="fas fa-folder-open me-1"></i> Documents (<?= count($documents) ?>)</a></li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content" id="profileTabsContent">

            <!-- ── OVERVIEW TAB ────────────────────────────────────────── -->
            <div class="tab-pane fade show active" id="pane-overview" role="tabpanel">

                <?php if (!empty($student['bio'])): ?>
                <div class="card mb-4">
                    <div class="card-header py-3">
                        <h6 class="mb-0 fw-bold"><i class="fas fa-bullseye me-2 text-primary"></i>Career Objective & Bio</h6>
                    </div>
                    <div class="card-body">
                        <p class="text-secondary mb-0" style="line-height:1.6;font-size:0.9rem;">
                            <?= nl2br(htmlspecialchars($student['bio'])) ?>
                        </p>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Languages -->
                <?php if (!empty($languages)): ?>
                <div class="card mb-4">
                    <div class="card-header py-3">
                        <h6 class="mb-0 fw-bold"><i class="fas fa-language me-2 text-primary"></i>Languages Spoken</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-wrap gap-2">
                            <?php foreach ($languages as $lang): ?>
                            <span class="badge bg-light text-dark border px-3 py-2" style="font-size:0.82rem;">
                                <strong><?= htmlspecialchars($lang['language']) ?></strong>
                                <span class="text-muted fw-normal"> (<?= ucfirst($lang['proficiency']) ?>)</span>
                            </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Achievements -->
                <?php if (!empty($achievements)): ?>
                <div class="card mb-4">
                    <div class="card-header py-3">
                        <h6 class="mb-0 fw-bold"><i class="fas fa-trophy me-2 text-warning"></i>Achievements</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-column gap-2">
                            <?php foreach ($achievements as $ach): ?>
                            <div class="p-3 rounded border bg-light">
                                <div class="fw-bold text-dark mb-1"><?= htmlspecialchars($ach['title']) ?></div>
                                <?php if (!empty($ach['description'])): ?>
                                <div class="text-muted small mb-1"><?= htmlspecialchars($ach['description']) ?></div>
                                <?php endif; ?>
                                <?php if (!empty($ach['date'])): ?>
                                <div class="text-muted" style="font-size:0.75rem;"><i class="far fa-calendar-alt me-1"></i><?= formatDate($ach['date']) ?></div>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

            </div><!-- /pane-overview -->

            <!-- ── ACADEMIC TAB ───────────────────────────────────────── -->
            <div class="tab-pane fade" id="pane-academic" role="tabpanel">
                <div class="card mb-4">
                    <div class="card-header py-3">
                        <h6 class="mb-0 fw-bold"><i class="fas fa-graduation-cap me-2 text-primary"></i>Academic Details</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table mb-0">
                                <tbody>
                                    <tr><td class="text-muted fw-semibold" style="width:35%;">Degree</td><td><?= htmlspecialchars($student['degree'] ?? 'B.Tech') ?></td></tr>
                                    <tr><td class="text-muted fw-semibold">Branch / Stream</td><td><?= htmlspecialchars($student['branch'] ?? 'N/A') ?></td></tr>
                                    <tr><td class="text-muted fw-semibold">Enrollment Number</td><td><span class="font-monospace fw-bold"><?= htmlspecialchars($student['enrollment_no'] ?? 'N/A') ?></span></td></tr>
                                    <tr><td class="text-muted fw-semibold">CGPA</td><td><span class="badge bg-primary fs-6"><?= $student['cgpa'] ? number_format($student['cgpa'], 2) . ' / 10.00' : 'N/A' ?></span></td></tr>
                                    <tr><td class="text-muted fw-semibold">10th Percentage</td><td><?= $student['tenth_percentage'] ? $student['tenth_percentage'] . '%' : 'N/A' ?></td></tr>
                                    <tr><td class="text-muted fw-semibold">12th / Diploma %</td><td><?= ($student['twelfth_percentage'] ?? $student['diploma_percentage']) ? (($student['twelfth_percentage'] ?: $student['diploma_percentage'])) . '%' : 'N/A' ?></td></tr>
                                    <tr><td class="text-muted fw-semibold">Active Backlogs</td><td><span class="badge <?= ($student['active_backlogs'] ?? 0) == 0 ? 'bg-success' : 'bg-danger' ?>"><?= $student['active_backlogs'] ?? 0 ?></span></td></tr>
                                    <tr><td class="text-muted fw-semibold">Total Backlogs</td><td><?= $student['backlogs'] ?? 0 ?></td></tr>
                                    <tr><td class="text-muted fw-semibold">Passing Year</td><td><?= $student['passing_year'] ?? 'N/A' ?></td></tr>
                                    <tr><td class="text-muted fw-semibold">Preferred Location</td><td><?= htmlspecialchars($student['preferred_location'] ?? 'N/A') ?></td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div><!-- /pane-academic -->

            <!-- ── PROJECTS TAB ───────────────────────────────────────── -->
            <div class="tab-pane fade" id="pane-projects" role="tabpanel">
                <div class="card mb-4">
                    <div class="card-header py-3">
                        <h6 class="mb-0 fw-bold"><i class="fas fa-code-branch me-2 text-primary"></i>Projects (<?= count($projects) ?>)</h6>
                    </div>
                    <div class="card-body">
                        <?php if (empty($projects)): ?>
                        <div class="text-center text-muted py-4">No projects listed.</div>
                        <?php else: ?>
                        <div class="d-flex flex-column gap-3">
                            <?php foreach ($projects as $proj): ?>
                            <div class="p-3 rounded border">
                                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-2">
                                    <div>
                                        <h6 class="fw-bold mb-1" style="font-size:0.95rem;"><?= htmlspecialchars($proj['title']) ?></h6>
                                        <?php if ($proj['start_date'] || $proj['end_date']): ?>
                                        <small class="text-muted"><i class="far fa-calendar me-1"></i><?= formatDate($proj['start_date']) ?> – <?= $proj['end_date'] ? formatDate($proj['end_date']) : 'Present' ?></small>
                                        <?php endif; ?>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <?php if (!empty($proj['project_url'])): ?>
                                        <a href="<?= htmlspecialchars($proj['project_url']) ?>" target="_blank" class="btn btn-xs btn-outline-primary" style="font-size:0.75rem;"><i class="fas fa-external-link-alt me-1"></i>Live</a>
                                        <?php endif; ?>
                                        <?php if (!empty($proj['github_url'])): ?>
                                        <a href="<?= htmlspecialchars($proj['github_url']) ?>" target="_blank" class="btn btn-xs btn-outline-dark" style="font-size:0.75rem;"><i class="fab fa-github me-1"></i>Code</a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php if (!empty($proj['description'])): ?>
                                <p class="text-muted mb-2 small" style="line-height:1.5;"><?= nl2br(htmlspecialchars($proj['description'])) ?></p>
                                <?php endif; ?>
                                <?php if (!empty($proj['technologies'])): ?>
                                <div class="d-flex flex-wrap gap-1">
                                    <?php foreach (array_filter(array_map('trim', explode(',', $proj['technologies']))) as $tech): ?>
                                    <span class="badge bg-light text-dark border" style="font-size:0.72rem;"><?= htmlspecialchars($tech) ?></span>
                                    <?php endforeach; ?>
                                </div>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div><!-- /pane-projects -->

            <!-- ── CERTIFICATIONS TAB ─────────────────────────────────── -->
            <div class="tab-pane fade" id="pane-certs" role="tabpanel">
                <div class="card mb-4">
                    <div class="card-header py-3">
                        <h6 class="mb-0 fw-bold"><i class="fas fa-certificate me-2 text-warning"></i>Certifications (<?= count($certifications) ?>)</h6>
                    </div>
                    <div class="card-body">
                        <?php if (empty($certifications)): ?>
                        <div class="text-center text-muted py-4">No certifications listed.</div>
                        <?php else: ?>
                        <div class="d-flex flex-column gap-3">
                            <?php foreach ($certifications as $cert): ?>
                            <div class="p-3 rounded border">
                                <div class="fw-bold mb-1" style="font-size:0.92rem;"><?= htmlspecialchars($cert['title']) ?></div>
                                <?php if (!empty($cert['issuing_org'])): ?>
                                <div class="text-muted small mb-2"><i class="fas fa-building me-1"></i><?= htmlspecialchars($cert['issuing_org']) ?></div>
                                <?php endif; ?>
                                <div class="d-flex flex-wrap gap-2 align-items-center" style="font-size:0.78rem;">
                                    <?php if (!empty($cert['issue_date'])): ?><span class="text-muted">Issued: <?= formatDate($cert['issue_date']) ?></span><?php endif; ?>
                                    <?php if (!empty($cert['credential_url'])): ?>
                                    <a href="<?= htmlspecialchars($cert['credential_url']) ?>" target="_blank" class="text-primary fw-semibold"><i class="fas fa-link me-1"></i>View Credential</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div><!-- /pane-certs -->

            <!-- ── DOCUMENTS TAB ──────────────────────────────────────── -->
            <div class="tab-pane fade" id="pane-docs" role="tabpanel">

                <!-- Resume Header Card -->
                <?php if ($student['resume_path']): ?>
                <div class="card mb-4">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold"><i class="fas fa-file-pdf me-2 text-danger"></i>Resume Document</h6>
                        <div class="d-flex gap-2">
                            <a href="<?= $resumePreviewUrl ?>" target="_blank" class="btn btn-sm btn-outline-secondary"><i class="fas fa-eye me-1"></i>Open</a>
                            <a href="<?= $resumeDownloadUrl ?>" class="btn btn-sm btn-primary"><i class="fas fa-download me-1"></i>Download</a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-2 text-muted small">
                            <i class="fas fa-file-pdf text-danger fa-lg"></i>
                            <span><?= htmlspecialchars($student['resume_original_name'] ?: $student['resume_path']) ?></span>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Supporting Documents Card -->
                <div class="card mb-4">
                    <div class="card-header py-3">
                        <h6 class="mb-0 fw-bold"><i class="fas fa-folder-open me-2 text-primary"></i>Supporting Documents (<?= count($documents) ?>)</h6>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($documents)): ?>
                        <div class="p-4 text-center text-muted">No supporting documents uploaded.</div>
                        <?php else: ?>
                        <div class="table-responsive">
                            <table class="table mb-0">
                                <thead>
                                    <tr>
                                        <th>Document Name</th>
                                        <th>Type</th>
                                        <th>Uploaded Date</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($documents as $doc): ?>
                                    <?php
                                    $docType = ucfirst(str_replace('_', ' ', $doc['document_type'] ?? 'Other'));
                                    $previewUrl = url('/company/serve-document/' . $doc['id']);
                                    $downloadUrl = $previewUrl . '?download=1';
                                    ?>
                                    <tr>
                                        <td class="fw-medium"><i class="fas fa-file-alt me-2 text-secondary"></i><?= htmlspecialchars($doc['original_name']) ?></td>
                                        <td><span class="badge bg-light text-dark border"><?= $docType ?></span></td>
                                        <td><small class="text-muted"><?= formatDate($doc['created_at']) ?></small></td>
                                        <td class="text-end">
                                            <a href="<?= $downloadUrl ?>" class="btn btn-xs btn-outline-primary" style="font-size:0.75rem;"><i class="fas fa-download me-1"></i>Download</a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

            </div><!-- /pane-docs -->

        </div><!-- /tab-content -->

    </div><!-- /col-lg-8 -->

</div><!-- /row -->

<!-- ── Schedule Interview Modal ──────────────────────────────────────── -->
<div class="modal fade" id="scheduleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-calendar-plus text-primary me-2"></i>Schedule Interview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="scheduleForm" method="POST" data-tpms-validate>
                <?= CsrfMiddleware::tokenField() ?>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Round *</label>
                            <input type="text" class="form-control" name="round" value="Round 1" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Mode *</label>
                            <select class="form-select" name="mode">
                                <option value="offline">Offline</option>
                                <option value="online">Online</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Date *</label>
                            <input type="date" class="form-control" name="interview_date" min="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Time *</label>
                            <input type="time" class="form-control" name="interview_time" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Venue</label>
                            <input type="text" class="form-control" name="venue" placeholder="Room/Building/Address">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Meeting Link *</label>
                            <input type="url" class="form-control" name="meeting_link" placeholder="https://meet.google.com/..." required data-validate-rule="meetingLink" data-validate-label="Meeting Link">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Instructions / Notes</label>
                            <textarea class="form-control" name="instructions" rows="2" placeholder="Optional notes for candidate"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-calendar-check me-1"></i> Schedule Interview</button>
                </div>
            </form>
        </div>
    </div>
</div>

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
</script>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>
