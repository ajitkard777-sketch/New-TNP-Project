<?php require_once ROOT_PATH . '/includes/header.php'; ?>

<div class="content-header">
    <div>
        <h1 class="page-title">My Profile</h1>
        <p class="subtitle">View and manage your profile information</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('/student/profile/edit') ?>" class="btn btn-primary btn-sm"><i class="fas fa-edit me-1"></i> Edit Profile</a>
        <?php if ($student['resume_path']): ?>
        <a href="<?= url('/student/preview-resume') ?>" class="btn btn-outline-primary btn-sm" target="_blank"><i class="fas fa-file-pdf me-1"></i> View Resume</a>
        <a href="<?= url('/student/download-resume') ?>" class="btn btn-success btn-sm"><i class="fas fa-download me-1"></i> Download</a>
        <?php endif; ?>
    </div>
</div>

<div class="row g-4">
    <!-- Left - Profile Card -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-body profile-card">
                <img src="<?= $student['profile_photo'] ? uploadUrl('profile_photos/' . $student['profile_photo']) : asset('images/default-avatar.png') ?>"
                     alt="Profile" class="profile-avatar" onerror="this.src='<?= asset('images/default-avatar.png') ?>'">
                <h5 class="profile-name"><?= htmlspecialchars(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? '')) ?></h5>
                <p class="profile-role mb-1"><?= htmlspecialchars($student['branch'] ?? '') ?></p>
                <p class="text-muted mb-2" style="font-size:0.82rem"><?= htmlspecialchars($student['email'] ?? '') ?></p>

                <?php if ($student['is_placed']): ?>
                <div class="alert alert-success py-2 px-3 text-start" style="font-size:0.82rem">
                    <i class="fas fa-trophy me-1"></i> <strong>Placed</strong> at <?= htmlspecialchars($student['placed_company'] ?? 'N/A') ?>
                    <?php if ($student['placed_package']): ?><br>Package: <?= formatCurrency($student['placed_package']) ?><?php endif; ?>
                </div>
                <?php endif; ?>

                <div class="profile-completion mt-3">
                    <div class="profile-completion-label">
                        <span>Profile Completion</span>
                        <span class="text-primary fw-bold"><?= $student['profile_completion'] ?? 0 ?>%</span>
                    </div>
                    <div class="progress"><div class="progress-bar" style="width:<?= $student['profile_completion'] ?? 0 ?>%"></div></div>
                </div>

                <div class="text-start mt-4">
                    <h6 class="fw-bold mb-3">Quick Info</h6>
                    <div class="mb-2"><i class="fas fa-phone text-primary me-2" style="width:18px"></i><small><?= htmlspecialchars($student['phone'] ?? 'N/A') ?></small></div>
                    <div class="mb-2"><i class="fas fa-birthday-cake text-primary me-2" style="width:18px"></i><small><?= $student['dob'] ? formatDate($student['dob']) : 'N/A' ?></small></div>
                    <div class="mb-2"><i class="fas fa-venus-mars text-primary me-2" style="width:18px"></i><small><?= ucfirst($student['gender'] ?? 'N/A') ?></small></div>
                    <div class="mb-2"><i class="fas fa-map-marker-alt text-primary me-2" style="width:18px"></i><small><?= htmlspecialchars(($student['city'] ?? '') . ($student['state'] ? ', ' . $student['state'] : '')) ?: 'N/A' ?></small></div>
                    <div class="mb-2"><i class="fas fa-id-card text-primary me-2" style="width:18px"></i><small><?= htmlspecialchars($student['enrollment_no'] ?? 'N/A') ?></small></div>
                    <?php if ($student['linkedin']): ?>
                    <div class="mb-2"><i class="fab fa-linkedin text-primary me-2" style="width:18px"></i><a href="<?= htmlspecialchars($student['linkedin']) ?>" target="_blank"><small>LinkedIn</small></a></div>
                    <?php endif; ?>
                    <?php if ($student['github']): ?>
                    <div class="mb-2"><i class="fab fa-github text-primary me-2" style="width:18px"></i><a href="<?= htmlspecialchars($student['github']) ?>" target="_blank"><small>GitHub</small></a></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Right - Details -->
    <div class="col-lg-8">
        <!-- Academic Details -->
        <div class="card mb-4">
            <div class="card-header"><h6><i class="fas fa-graduation-cap me-2 text-primary"></i>Academic Details</h6></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4"><label class="form-text">Degree</label><div class="fw-bold"><?= htmlspecialchars($student['degree'] ?? 'N/A') ?></div></div>
                    <div class="col-md-4"><label class="form-text">CGPA</label><div class="fw-bold text-primary"><?= $student['cgpa'] ?? 'N/A' ?> / 10</div></div>
                    <div class="col-md-4"><label class="form-text">Backlogs</label><div class="fw-bold"><?= $student['backlogs'] ?? 0 ?> (Active: <?= $student['active_backlogs'] ?? 0 ?>)</div></div>
                    <div class="col-md-4"><label class="form-text">10th Percentage</label><div class="fw-bold"><?= $student['tenth_percentage'] ? $student['tenth_percentage'] . '%' : 'N/A' ?></div></div>
                    <div class="col-md-4"><label class="form-text">12th Percentage</label><div class="fw-bold"><?= $student['twelfth_percentage'] ? $student['twelfth_percentage'] . '%' : 'N/A' ?></div></div>
                    <div class="col-md-4"><label class="form-text">Passing Year</label><div class="fw-bold"><?= $student['passing_year'] ?? 'N/A' ?></div></div>
                </div>
            </div>
        </div>

        <!-- Skills -->
        <?php if ($student['skills']): ?>
        <div class="card mb-4">
            <div class="card-header"><h6><i class="fas fa-code me-2 text-primary"></i>Skills</h6></div>
            <div class="card-body">
                <div class="d-flex flex-wrap gap-2">
                    <?php foreach (explode(',', $student['skills']) as $skill): ?>
                    <span class="job-tag"><?= htmlspecialchars(trim($skill)) ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Bio -->
        <?php if ($student['bio']): ?>
        <div class="card mb-4">
            <div class="card-header"><h6><i class="fas fa-user me-2 text-primary"></i>About</h6></div>
            <div class="card-body"><p style="font-size:0.9rem"><?= nl2br(htmlspecialchars($student['bio'])) ?></p></div>
        </div>
        <?php endif; ?>

        <!-- Projects -->
        <?php if (!empty($projects)): ?>
        <div class="card mb-4">
            <div class="card-header"><h6><i class="fas fa-project-diagram me-2 text-primary"></i>Projects (<?= count($projects) ?>)</h6></div>
            <div class="card-body p-0">
                <?php foreach ($projects as $project): ?>
                <div class="p-3 border-bottom">
                    <h6 class="fw-bold mb-1"><?= htmlspecialchars($project['title']) ?></h6>
                    <?php if ($project['description']): ?><p class="text-muted mb-2" style="font-size:0.85rem"><?= htmlspecialchars($project['description']) ?></p><?php endif; ?>
                    <?php if ($project['technologies']): ?>
                    <div class="d-flex flex-wrap gap-1 mb-2">
                        <?php foreach (explode(',', $project['technologies']) as $tech): ?>
                        <span class="badge bg-light text-dark" style="font-size:0.7rem"><?= htmlspecialchars(trim($tech)) ?></span>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                    <div style="font-size:0.75rem" class="text-muted">
                        <?php if ($project['start_date']): ?><?= formatDate($project['start_date'], 'M Y') ?><?php endif; ?>
                        <?php if ($project['end_date']): ?> - <?= formatDate($project['end_date'], 'M Y') ?><?php endif; ?>
                        <?php if ($project['project_url']): ?><a href="<?= htmlspecialchars($project['project_url']) ?>" target="_blank" class="ms-2"><i class="fas fa-external-link-alt"></i> Demo</a><?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Certifications -->
        <?php if (!empty($certifications)): ?>
        <div class="card mb-4">
            <div class="card-header"><h6><i class="fas fa-certificate me-2 text-warning"></i>Certifications (<?= count($certifications) ?>)</h6></div>
            <div class="card-body p-0">
                <?php foreach ($certifications as $cert): ?>
                <div class="p-3 border-bottom">
                    <h6 class="fw-bold mb-1"><?= htmlspecialchars($cert['title']) ?></h6>
                    <div class="text-muted" style="font-size:0.82rem"><?= htmlspecialchars($cert['issuing_org'] ?? '') ?></div>
                    <div style="font-size:0.75rem" class="text-muted mt-1">
                        <?php if ($cert['issue_date']): ?>Issued: <?= formatDate($cert['issue_date'], 'M Y') ?><?php endif; ?>
                        <?php if ($cert['credential_url']): ?><a href="<?= htmlspecialchars($cert['credential_url']) ?>" target="_blank" class="ms-2"><i class="fas fa-external-link-alt"></i> Verify</a><?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Languages & Achievements -->
        <div class="row g-4">
            <?php if (!empty($languages)): ?>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header"><h6><i class="fas fa-language me-2 text-info"></i>Languages</h6></div>
                    <div class="card-body p-0">
                        <?php foreach ($languages as $lang): ?>
                        <div class="p-3 border-bottom d-flex justify-content-between">
                            <span class="fw-medium"><?= htmlspecialchars($lang['language']) ?></span>
                            <span class="badge <?= $lang['proficiency'] === 'native' ? 'bg-success' : ($lang['proficiency'] === 'advanced' ? 'bg-primary' : 'bg-secondary') ?>"><?= ucfirst($lang['proficiency']) ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($achievements)): ?>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header"><h6><i class="fas fa-trophy me-2 text-warning"></i>Achievements</h6></div>
                    <div class="card-body p-0">
                        <?php foreach ($achievements as $ach): ?>
                        <div class="p-3 border-bottom">
                            <div class="fw-bold" style="font-size:0.85rem"><?= htmlspecialchars($ach['title']) ?></div>
                            <?php if ($ach['date']): ?><small class="text-muted"><?= formatDate($ach['date'], 'M Y') ?></small><?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>
