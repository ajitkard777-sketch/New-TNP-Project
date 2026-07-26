<?php require_once ROOT_PATH . '/includes/header.php'; ?>

<div class="content-header mb-4">
    <div>
        <h1 class="page-title"><i class="fas fa-user-circle text-primary me-2"></i>My Profile</h1>
        <p class="subtitle">Comprehensive student placement record and academic portfolio</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('/student/profile/edit') ?>" class="btn btn-primary btn-sm fw-semibold">
            <i class="fas fa-edit me-1"></i> Edit All Profile Sections
        </a>
        <?php if (!empty($student['resume_path'])): ?>
        <a href="<?= url('/student/preview-resume') ?>" class="btn btn-outline-primary btn-sm fw-semibold" target="_blank">
            <i class="fas fa-file-pdf me-1"></i> View Resume PDF
        </a>
        <?php endif; ?>
    </div>
</div>

<div class="row g-4">

    <!-- Left Header Card: Quick Snapshot -->
    <div class="col-lg-4">
        <div class="card shadow-sm border-0 mb-4 sticky-lg-top" style="top:80px; z-index:10;">
            <div class="card-body profile-card text-center p-4">
                <img src="<?= $student['profile_photo'] ? uploadUrl('profile_photos/' . $student['profile_photo']) : asset('images/default-avatar.png') ?>" 
                     alt="Profile Photo" class="profile-avatar shadow-sm mb-3" onerror="this.src='<?= asset('images/default-avatar.png') ?>'">
                
                <h5 class="fw-bold mb-1"><?= htmlspecialchars(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? '')) ?></h5>
                <div class="badge bg-primary-soft text-primary px-3 py-1 mb-3" style="font-size:0.82rem;">
                    <?= htmlspecialchars($student['branch'] ?? 'Student') ?>
                </div>

                <?php if (!empty($student['is_placed'])): ?>
                <div class="alert alert-success py-2 px-3 text-start small mb-3">
                    <i class="fas fa-trophy text-warning me-1"></i> <strong>Placed!</strong> at <?= htmlspecialchars($student['placed_company'] ?? 'N/A') ?>
                    <?php if ($student['placed_package']): ?><br>Package: <strong><?= formatCurrency($student['placed_package']) ?></strong><?php endif; ?>
                </div>
                <?php endif; ?>

                <div class="profile-completion text-start mb-3">
                    <div class="d-flex justify-content-between small mb-1">
                        <span class="text-muted">Profile Score</span>
                        <span class="text-primary fw-bold"><?= $student['profile_completion'] ?? 0 ?>%</span>
                    </div>
                    <div class="progress" style="height:8px;">
                        <div class="progress-bar bg-primary" style="width: <?= $student['profile_completion'] ?? 0 ?>%"></div>
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <a href="<?= url('/student/profile/edit') ?>" class="btn btn-primary btn-sm fw-semibold">
                        <i class="fas fa-edit me-1"></i> Edit Profile
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Detailed Sections -->
    <div class="col-lg-8">
        
        <!-- 1. Personal Information Section -->
        <div class="card shadow-sm border-0 mb-4" id="section-personal">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-bold d-flex align-items-center gap-2">
                    <i class="fas fa-id-card text-primary"></i> 1. Personal Information
                </h6>
                <a href="<?= url('/student/profile/edit#personal-section') ?>" class="btn btn-sm btn-outline-primary fw-semibold">
                    <i class="fas fa-edit me-1"></i> Edit
                </a>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <small class="text-muted d-block">Full Name</small>
                        <span class="fw-bold text-dark"><?= htmlspecialchars(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? '')) ?></span>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block">Email Address</small>
                        <span class="fw-bold text-dark"><?= htmlspecialchars($student['email'] ?? 'N/A') ?></span>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block">Phone Number</small>
                        <span class="fw-bold text-dark"><?= htmlspecialchars($student['phone'] ?? 'N/A') ?></span>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block">Date of Birth</small>
                        <span class="fw-bold text-dark"><?= $student['dob'] ? formatDate($student['dob']) : 'N/A' ?></span>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block">Gender</small>
                        <span class="fw-bold text-dark"><?= ucfirst($student['gender'] ?? 'N/A') ?></span>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block">City &amp; State</small>
                        <span class="fw-bold text-dark"><?= htmlspecialchars(($student['city'] ?? '') . ($student['state'] ? ', ' . $student['state'] : '')) ?: 'N/A' ?></span>
                    </div>
                    <div class="col-12">
                        <small class="text-muted d-block">Full Address</small>
                        <span class="fw-medium text-dark"><?= htmlspecialchars($student['address'] ?? 'N/A') ?> <?= $student['pincode'] ? '(' . $student['pincode'] . ')' : '' ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Academic Information Section -->
        <div class="card shadow-sm border-0 mb-4" id="section-academic">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-bold d-flex align-items-center gap-2">
                    <i class="fas fa-graduation-cap text-primary"></i> 2. Academic Information
                </h6>
                <a href="<?= url('/student/profile/edit#academic-section') ?>" class="btn btn-sm btn-outline-primary fw-semibold">
                    <i class="fas fa-edit me-1"></i> Edit
                </a>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-md-4">
                        <small class="text-muted d-block">Degree &amp; Program</small>
                        <span class="fw-bold text-dark"><?= htmlspecialchars($student['degree'] ?? 'B.Tech') ?></span>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted d-block">Department / Branch</small>
                        <span class="fw-bold text-primary"><?= htmlspecialchars($student['branch'] ?? 'N/A') ?></span>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted d-block">Enrollment / Roll No</small>
                        <span class="fw-bold text-dark"><?= htmlspecialchars($student['enrollment_no'] ?? 'N/A') ?></span>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted d-block">Current CGPA</small>
                        <span class="fw-bold text-success fs-6"><?= $student['cgpa'] ? $student['cgpa'] . ' / 10' : 'N/A' ?></span>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted d-block">10th Percentage</small>
                        <span class="fw-bold text-dark"><?= $student['tenth_percentage'] ? $student['tenth_percentage'] . '%' : 'N/A' ?></span>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted d-block">12th Percentage</small>
                        <span class="fw-bold text-dark"><?= $student['twelfth_percentage'] ? $student['twelfth_percentage'] . '%' : 'N/A' ?></span>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted d-block">Diploma Percentage</small>
                        <span class="fw-bold text-dark"><?= $student['diploma_percentage'] ? $student['diploma_percentage'] . '%' : 'N/A' ?></span>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted d-block">Backlogs</small>
                        <span class="fw-bold <?= ($student['active_backlogs'] ?? 0) > 0 ? 'text-danger' : 'text-success' ?>">
                            <?= $student['active_backlogs'] ?? 0 ?> Active (Total: <?= $student['backlogs'] ?? 0 ?>)
                        </span>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted d-block">Passing Year</small>
                        <span class="fw-bold text-dark"><?= $student['passing_year'] ?? 'N/A' ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Skills Section -->
        <div class="card shadow-sm border-0 mb-4" id="section-skills">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-bold d-flex align-items-center gap-2">
                    <i class="fas fa-tools text-primary"></i> 3. Skills &amp; Competencies
                </h6>
                <a href="<?= url('/student/profile/edit#skills-section') ?>" class="btn btn-sm btn-outline-primary fw-semibold">
                    <i class="fas fa-edit me-1"></i> Edit
                </a>
            </div>
            <div class="card-body p-4">
                <?php if (!empty($student['skills'])): ?>
                <div class="d-flex flex-wrap gap-2">
                    <?php foreach (explode(',', $student['skills']) as $skill): ?>
                    <span class="badge bg-primary-soft text-primary px-3 py-2 fw-medium" style="font-size:0.85rem;">
                        <i class="fas fa-check-circle me-1"></i><?= htmlspecialchars(trim($skill)) ?>
                    </span>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <p class="text-muted small mb-0">No technical skills added yet. <a href="<?= url('/student/profile/edit#skills-section') ?>">Add skills now</a></p>
                <?php endif; ?>
            </div>
        </div>

        <!-- 4. Resume Section -->
        <div class="card shadow-sm border-0 mb-4" id="section-resume">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-bold d-flex align-items-center gap-2">
                    <i class="fas fa-file-pdf text-danger"></i> 4. Resume Document
                </h6>
                <button type="button" class="btn btn-sm btn-outline-primary fw-semibold" data-bs-toggle="modal" data-bs-target="#uploadResumeModal">
                    <i class="fas fa-upload me-1"></i> Upload / Edit
                </button>
            </div>
            <div class="card-body p-4">
                <?php if (!empty($student['resume_path'])): ?>
                <div class="d-flex flex-wrap align-items-center justify-content-between bg-light p-3 rounded gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <i class="fas fa-file-pdf text-danger fs-2"></i>
                        <div>
                            <div class="fw-bold text-dark"><?= htmlspecialchars($student['resume_original_name'] ?: 'Student_Resume.pdf') ?></div>
                            <small class="text-success"><i class="fas fa-check-circle me-1"></i>Active Resume for Job Applications</small>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="<?= url('/student/preview-resume') ?>" target="_blank" class="btn btn-sm btn-outline-primary fw-semibold">
                            <i class="fas fa-eye me-1"></i> Preview
                        </a>
                        <a href="<?= url('/student/download-resume') ?>" class="btn btn-sm btn-primary fw-semibold">
                            <i class="fas fa-download me-1"></i> Download
                        </a>
                        <a href="<?= url('/student/delete-resume') ?>" class="btn btn-sm btn-outline-danger fw-semibold" data-confirm="Are you sure you want to delete your resume?">
                            <i class="fas fa-trash me-1"></i> Delete
                        </a>
                    </div>
                </div>
                <?php else: ?>
                <div class="p-4 text-center border rounded border-dashed text-muted">
                    <i class="fas fa-file-upload fs-2 mb-2 text-danger d-block"></i>
                    <p class="mb-1 fw-semibold text-dark">No resume uploaded</p>
                    <small class="d-block mb-3">Upload your latest PDF resume to apply to companies.</small>
                    <form action="<?= url('/student/upload-resume') ?>" method="POST" enctype="multipart/form-data" class="mx-auto" style="max-width:450px;">
                        <?= CsrfMiddleware::tokenField() ?>
                        <div class="input-group">
                            <input type="file" name="resume" class="form-control" accept=".pdf" required>
                            <button type="submit" class="btn btn-primary fw-semibold"><i class="fas fa-upload me-1"></i> Upload Resume PDF</button>
                        </div>
                    </form>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- 5. Social Links Section -->
        <div class="card shadow-sm border-0 mb-4" id="section-social">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-bold d-flex align-items-center gap-2">
                    <i class="fas fa-share-alt text-info"></i> 5. Social &amp; Professional Links
                </h6>
                <a href="<?= url('/student/profile/edit#personal-section') ?>" class="btn btn-sm btn-outline-primary fw-semibold">
                    <i class="fas fa-edit me-1"></i> Edit
                </a>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-md-4">
                        <small class="text-muted d-block mb-1"><i class="fab fa-linkedin text-primary me-1"></i>LinkedIn</small>
                        <?php if (!empty($student['linkedin'])): ?>
                        <a href="<?= htmlspecialchars($student['linkedin']) ?>" target="_blank" class="fw-semibold text-truncate d-block">
                            <?= htmlspecialchars($student['linkedin']) ?>
                        </a>
                        <?php else: ?>
                        <span class="text-muted small">Not provided</span>
                        <?php endif; ?>
                    </div>

                    <div class="col-md-4">
                        <small class="text-muted d-block mb-1"><i class="fab fa-github text-dark me-1"></i>GitHub</small>
                        <?php if (!empty($student['github'])): ?>
                        <a href="<?= htmlspecialchars($student['github']) ?>" target="_blank" class="fw-semibold text-truncate d-block">
                            <?= htmlspecialchars($student['github']) ?>
                        </a>
                        <?php else: ?>
                        <span class="text-muted small">Not provided</span>
                        <?php endif; ?>
                    </div>

                    <div class="col-md-4">
                        <small class="text-muted d-block mb-1"><i class="fas fa-globe text-success me-1"></i>Portfolio Website</small>
                        <?php if (!empty($student['portfolio'])): ?>
                        <a href="<?= htmlspecialchars($student['portfolio']) ?>" target="_blank" class="fw-semibold text-truncate d-block">
                            <?= htmlspecialchars($student['portfolio']) ?>
                        </a>
                        <?php else: ?>
                        <span class="text-muted small">Not provided</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- 6. Documents Section -->
        <div class="card shadow-sm border-0 mb-4" id="section-documents">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-bold d-flex align-items-center gap-2">
                    <i class="fas fa-folder-open text-primary"></i> 6. Academic Documents &amp; Marksheets (<?= count($documents ?? []) ?>)
                </h6>
                <button type="button" class="btn btn-sm btn-outline-primary fw-semibold" data-bs-toggle="modal" data-bs-target="#uploadDocModal">
                    <i class="fas fa-upload me-1"></i> Upload Document
                </button>
            </div>
            <div class="card-body p-0">
                <?php if (empty($documents)): ?>
                <div class="p-4 text-center text-muted small">No extra documents uploaded yet. <button type="button" class="btn btn-link btn-sm p-0" data-bs-toggle="modal" data-bs-target="#uploadDocModal">Upload marksheets</button></div>
                <?php else: ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($documents as $doc): ?>
                    <div class="p-3 border-bottom d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-3">
                            <i class="fas fa-file-alt text-primary fs-4"></i>
                            <div>
                                <div class="fw-bold text-dark small"><?= htmlspecialchars($doc['original_name']) ?></div>
                                <small class="text-muted"><?= ucfirst($doc['document_type'] ?? 'document') ?> • <?= formatFileSize($doc['file_size'] ?? 0) ?></small>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="<?= uploadUrl($doc['file_path']) ?>" target="_blank" class="btn btn-xs btn-outline-primary">View</a>
                            <a href="<?= url('/student/delete-document/' . $doc['id']) ?>" class="btn btn-xs btn-outline-danger" data-confirm="Delete document?"><i class="fas fa-trash"></i></a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

    </div>

</div>

<!-- Upload Resume Modal -->
<div class="modal fade" id="uploadResumeModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title"><i class="fas fa-file-pdf me-2 text-danger"></i>Upload Resume PDF</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <form action="<?= url('/student/upload-resume') ?>" method="POST" enctype="multipart/form-data">
        <?= CsrfMiddleware::tokenField() ?>
        <div class="modal-body">
            <div class="mb-3">
                <label class="form-label fw-semibold">Select PDF File *</label>
                <input type="file" name="resume" accept=".pdf" class="form-control" required>
                <small class="text-muted d-block mt-1">Maximum file size: 5MB. PDF format only.</small>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary"><i class="fas fa-upload me-1"></i> Upload Resume</button>
        </div>
    </form>
</div></div></div>

<!-- Upload Document Modal -->
<div class="modal fade" id="uploadDocModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title"><i class="fas fa-folder me-2 text-primary"></i>Upload Academic Document</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <form action="<?= url('/student/upload-document') ?>" method="POST" enctype="multipart/form-data">
        <?= CsrfMiddleware::tokenField() ?>
        <div class="modal-body">
            <div class="mb-3">
                <label class="form-label">Document Type *</label>
                <select name="document_type" class="form-select">
                    <option value="marksheet">Marksheet / Transcript</option>
                    <option value="certificate">Course Certificate</option>
                    <option value="id_proof">ID Proof / Aadhaar</option>
                    <option value="other">Other Academic Record</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Select File *</label>
                <input type="file" name="document" class="form-control" required>
                <small class="text-muted d-block mt-1">PDF, JPG, PNG up to 5MB.</small>
            </div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary"><i class="fas fa-upload me-1"></i> Upload Document</button></div>
    </form>
</div></div></div>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>
