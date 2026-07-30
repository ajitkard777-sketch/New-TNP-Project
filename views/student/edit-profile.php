<?php require_once ROOT_PATH . '/includes/header.php'; ?>

<div class="content-header">
    <div>
        <h1 class="page-title">Edit Profile</h1>
        <p class="subtitle">Keep your profile updated for better placement opportunities</p>
    </div>
    <a href="<?= url('/student/profile') ?>" class="btn btn-light btn-sm"><i class="fas fa-arrow-left me-1"></i> Back to Profile</a>
</div>

<div class="row g-4">
    <!-- Left: Photo & Resume -->
    <div class="col-lg-4">
        <!-- Profile Photo -->
        <div class="card mb-4">
            <div class="card-header"><h6><i class="fas fa-camera me-2 text-primary"></i>Profile Photo</h6></div>
            <div class="card-body text-center">
                <img src="<?= $student['profile_photo'] ? uploadUrl('profile_photos/' . $student['profile_photo']) : asset('images/default-avatar.png') ?>"
                     alt="Profile" class="profile-avatar mb-3" id="photoPreview" onerror="this.src='<?= asset('images/default-avatar.png') ?>'">
                <form action="<?= url('/student/upload-photo') ?>" method="POST" enctype="multipart/form-data">
                    <?= CsrfMiddleware::tokenField() ?>
                    <input type="file" name="profile_photo" id="profilePhotoInput" accept="image/*" class="form-control form-control-sm mb-2" onchange="previewImage(this, 'photoPreview')">
                    <button type="submit" class="btn btn-primary btn-sm w-100"><i class="fas fa-upload me-1"></i> Upload Photo</button>
                </form>
                <small class="text-muted d-block mt-2">Max 5MB. JPG, PNG, GIF, WEBP</small>
            </div>
        </div>

        <!-- Resume -->
        <div class="card mb-4">
            <div class="card-header"><h6><i class="fas fa-file-pdf me-2 text-danger"></i>Resume</h6></div>
            <div class="card-body">
                <?php if ($student['resume_path']): ?>
                <div class="alert alert-success py-2 px-3" style="font-size:0.82rem">
                    <i class="fas fa-check-circle me-1"></i> 
                    <?= htmlspecialchars($student['resume_original_name'] ?? 'Resume uploaded') ?>
                </div>
                <div class="d-flex gap-2 mb-3">
                    <a href="<?= url('/student/preview-resume') ?>" target="_blank" class="btn btn-outline-primary btn-sm flex-fill"><i class="fas fa-eye me-1"></i> Preview</a>
                    <a href="<?= url('/student/download-resume') ?>" class="btn btn-success btn-sm flex-fill"><i class="fas fa-download me-1"></i> Download</a>
                    <a href="<?= url('/student/delete-resume') ?>" class="btn btn-danger btn-sm" data-confirm="Delete your resume?"><i class="fas fa-trash"></i></a>
                </div>
                <?php endif; ?>
                <form action="<?= url('/student/upload-resume') ?>" method="POST" enctype="multipart/form-data">
                    <?= CsrfMiddleware::tokenField() ?>
                    <input type="file" name="resume" accept=".pdf" class="form-control form-control-sm mb-2">
                    <button type="submit" class="btn btn-primary btn-sm w-100"><i class="fas fa-upload me-1"></i> <?= $student['resume_path'] ? 'Replace Resume' : 'Upload Resume' ?></button>
                </form>
                <small class="text-muted d-block mt-2">Only PDF files. Max 5MB.</small>
            </div>
        </div>

        <!-- Documents -->
        <div class="card mb-4">
            <div class="card-header"><h6><i class="fas fa-folder me-2 text-warning"></i>Documents</h6></div>
            <div class="card-body">
                <?php
                $documents = $this->db->fetchAll("SELECT * FROM documents WHERE user_id = ? ORDER BY created_at DESC", [$_SESSION['user_id']]);
                foreach ($documents as $doc): ?>
                <div class="d-flex justify-content-between align-items-center mb-2 p-2 rounded" style="background:var(--gray-50)">
                    <div>
                        <div class="fw-medium" style="font-size:0.82rem"><?= htmlspecialchars($doc['original_name']) ?></div>
                        <small class="text-muted"><?= ucfirst($doc['document_type']) ?></small>
                    </div>
                    <a href="<?= url('/student/delete-document/' . $doc['id']) ?>" class="btn btn-sm btn-danger btn-icon" data-confirm="Delete this document?"><i class="fas fa-trash" style="font-size:0.7rem"></i></a>
                </div>
                <?php endforeach; ?>
                
                <form action="<?= url('/student/upload-document') ?>" method="POST" enctype="multipart/form-data" class="mt-3" id="documentUploadForm">
                    <?= CsrfMiddleware::tokenField() ?>
                    <select name="document_type" class="form-select form-select-sm mb-2">
                        <option value="certificate">Certificate</option>
                        <option value="marksheet">Marksheet</option>
                        <option value="id_proof">ID Proof</option>
                        <option value="other">Other</option>
                    </select>
                    <input type="file" name="document" id="documentFileInput" accept=".pdf,.doc,.docx" class="form-control form-control-sm mb-2" required>
                    <div id="doc-file-error" class="text-danger small mt-1" style="display:none;"><i class="fas fa-exclamation-circle me-1"></i>Only PDF, DOC, and DOCX files are allowed.</div>
                    <button type="submit" id="documentUploadBtn" class="btn btn-warning btn-sm w-100"><i class="fas fa-upload me-1"></i> Upload Document</button>
                    <small class="text-muted d-block mt-2">Allowed: PDF, DOC, DOCX. Max 5MB.</small>
                </form>
                <script>
                (function() {
                    var input  = document.getElementById('documentFileInput');
                    var error  = document.getElementById('doc-file-error');
                    var btn    = document.getElementById('documentUploadBtn');
                    var form   = document.getElementById('documentUploadForm');
                    var allowed = ['pdf','doc','docx'];
                    function validate() {
                        if (!input.files || !input.files[0]) { error.style.display='none'; btn.disabled=false; return true; }
                        var ext = input.files[0].name.split('.').pop().toLowerCase();
                        if (allowed.indexOf(ext) === -1) {
                            error.style.display = 'block';
                            input.classList.add('is-invalid');
                            btn.disabled = true;
                            return false;
                        }
                        error.style.display = 'none';
                        input.classList.remove('is-invalid');
                        btn.disabled = false;
                        return true;
                    }
                    input.addEventListener('change', validate);
                    form.addEventListener('submit', function(e) {
                        if (!validate()) { e.preventDefault(); return false; }
                    });
                })();
                </script>
            </div>
        </div>
    </div>

    <!-- Right: Edit Form -->
    <div class="col-lg-8">
        <form action="<?= url('/student/profile/edit') ?>" method="POST" data-validate data-tpms-validate>
            <?= CsrfMiddleware::tokenField() ?>
            
            <!-- Nav Tabs -->
            <ul class="nav nav-tabs mb-4" role="tablist">
                <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#personal">Personal</a></li>
                <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#academic">Academic</a></li>
                <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#skills-tab">Skills & Bio</a></li>
            </ul>

            <div class="tab-content">
                <!-- Personal Tab -->
                <div class="tab-pane fade show active" id="personal">
                    <div class="card">
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6"><label class="form-label">First Name *</label><input type="text" class="form-control" name="first_name" value="<?= htmlspecialchars($student['first_name'] ?? '') ?>" required></div>
                                <div class="col-md-6"><label class="form-label">Last Name *</label><input type="text" class="form-control" name="last_name" value="<?= htmlspecialchars($student['last_name'] ?? '') ?>" required></div>
                                <div class="col-md-6"><label class="form-label">Phone</label><input type="text" class="form-control" name="phone" id="edit_phone" value="<?= htmlspecialchars($student['phone'] ?? '') ?>" maxlength="10" inputmode="numeric" placeholder="10-digit number" data-validate-rule="phone" data-validate-label="Phone number"><div class="invalid-feedback"></div></div>
                                <div class="col-md-6"><label class="form-label">Date of Birth</label><input type="date" class="form-control" name="dob" value="<?= $student['dob'] ?? '' ?>" max="<?= date('Y-m-d') ?>"></div>
                                <div class="col-md-6"><label class="form-label">Gender</label>
                                    <select class="form-select" name="gender">
                                        <option value="">Select</option>
                                        <option value="male" <?= ($student['gender'] ?? '') === 'male' ? 'selected' : '' ?>>Male</option>
                                        <option value="female" <?= ($student['gender'] ?? '') === 'female' ? 'selected' : '' ?>>Female</option>
                                        <option value="other" <?= ($student['gender'] ?? '') === 'other' ? 'selected' : '' ?>>Other</option>
                                    </select>
                                </div>
                                <div class="col-md-6"><label class="form-label">Pincode</label><input type="text" class="form-control" name="pincode" id="edit_pincode" value="<?= htmlspecialchars($student['pincode'] ?? '') ?>" maxlength="6" inputmode="numeric" placeholder="6-digit PIN" data-validate-rule="pincode" data-validate-label="PIN code"><div class="invalid-feedback"></div></div>
                                <div class="col-12">
                                    <label class="form-label">Address <small class="text-muted">(10–250 characters)</small></label>
                                    <textarea class="form-control" name="address" id="edit_address" rows="2"
                                              data-validate-rule="address" data-validate-label="Address"
                                              data-maxlength="250" data-maxlength-target="address_counter"><?= htmlspecialchars($student['address'] ?? '') ?></textarea>
                                    <div class="d-flex justify-content-between">
                                        <div class="invalid-feedback"></div>
                                        <small class="text-muted ms-auto" id="address_counter"><?= mb_strlen($student['address'] ?? '') ?>/250</small>
                                    </div>
                                </div>
                                <div class="col-md-6"><label class="form-label">City</label><input type="text" class="form-control" name="city" id="edit_city" value="<?= htmlspecialchars($student['city'] ?? '') ?>" data-validate-rule="city" data-validate-label="City name"><div class="invalid-feedback"></div></div>
                                <div class="col-md-6"><label class="form-label">State</label><input type="text" class="form-control" name="state" id="edit_state" value="<?= htmlspecialchars($student['state'] ?? '') ?>" data-validate-rule="state" data-validate-label="State name"><div class="invalid-feedback"></div></div>
                                <div class="col-md-4"><label class="form-label">LinkedIn</label><input type="url" class="form-control" name="linkedin" id="edit_linkedin" value="<?= htmlspecialchars($student['linkedin'] ?? '') ?>" placeholder="https://linkedin.com/in/..." data-validate-rule="optionalUrl" data-validate-label2="LinkedIn URL"><div class="invalid-feedback"></div></div>
                                <div class="col-md-4"><label class="form-label">GitHub</label><input type="url" class="form-control" name="github" id="edit_github" value="<?= htmlspecialchars($student['github'] ?? '') ?>" placeholder="https://github.com/..." data-validate-rule="optionalUrl" data-validate-label2="GitHub URL"><div class="invalid-feedback"></div></div>
                                <div class="col-md-4"><label class="form-label">Portfolio</label><input type="url" class="form-control" name="portfolio" id="edit_portfolio" value="<?= htmlspecialchars($student['portfolio'] ?? '') ?>" data-validate-rule="optionalUrl" data-validate-label2="Portfolio URL"><div class="invalid-feedback"></div></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Academic Tab -->
                <div class="tab-pane fade" id="academic">
                    <div class="card">
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6"><label class="form-label">Branch *</label>
                                    <select class="form-select" name="branch" required>
                                        <?php foreach (BRANCHES as $b): ?>
                                        <option value="<?= $b ?>" <?= ($student['branch'] ?? '') === $b ? 'selected' : '' ?>><?= $b ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6"><label class="form-label">Enrollment No</label><input type="text" class="form-control" name="enrollment_no" value="<?= htmlspecialchars($student['enrollment_no'] ?? '') ?>"></div>
                                <div class="col-md-4"><label class="form-label">Degree</label><input type="text" class="form-control" name="degree" value="<?= htmlspecialchars($student['degree'] ?? 'B.Tech') ?>"></div>
                                <div class="col-md-4"><label class="form-label">Admission Year</label><input type="number" class="form-control" name="admission_year" value="<?= $student['admission_year'] ?? '' ?>" min="2000" max="2099"></div>
                                <div class="col-md-4"><label class="form-label">Passing Year</label><input type="number" class="form-control" name="passing_year" value="<?= $student['passing_year'] ?? '' ?>" min="2000" max="2099"></div>
                                <div class="col-md-3"><label class="form-label">CGPA (0-10)</label><input type="number" class="form-control" name="cgpa" value="<?= $student['cgpa'] ?? '' ?>" step="0.01" min="0" max="10"></div>
                                <div class="col-md-3"><label class="form-label">10th %</label><input type="number" class="form-control" name="tenth_percentage" value="<?= $student['tenth_percentage'] ?? '' ?>" step="0.01" min="0" max="100"></div>
                                <div class="col-md-3"><label class="form-label">12th %</label><input type="number" class="form-control" name="twelfth_percentage" value="<?= $student['twelfth_percentage'] ?? '' ?>" step="0.01" min="0" max="100"></div>
                                <div class="col-md-3"><label class="form-label">Diploma %</label><input type="number" class="form-control" name="diploma_percentage" value="<?= $student['diploma_percentage'] ?? '' ?>" step="0.01" min="0" max="100"></div>
                                <div class="col-md-6"><label class="form-label">Total Backlogs</label><input type="number" class="form-control" name="backlogs" value="<?= $student['backlogs'] ?? 0 ?>" min="0"></div>
                                <div class="col-md-6"><label class="form-label">Active Backlogs</label><input type="number" class="form-control" name="active_backlogs" value="<?= $student['active_backlogs'] ?? 0 ?>" min="0"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Skills & Bio Tab -->
                <div class="tab-pane fade" id="skills-tab">
                    <div class="card">
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Skills (comma separated) <small class="text-muted">max 300 chars</small></label>
                                <input type="text" class="form-control" name="skills" id="edit_skills"
                                       value="<?= htmlspecialchars($student['skills'] ?? '') ?>"
                                       placeholder="e.g. Java, Python, React, MySQL"
                                       data-validate-rule="skills"
                                       data-maxlength="300" data-maxlength-target="skills_counter">
                                <div class="d-flex justify-content-between">
                                    <small class="form-text">Separate skills with commas. Duplicates will be removed automatically.</small>
                                    <small class="text-muted" id="skills_counter"><?= mb_strlen($student['skills'] ?? '') ?>/300</small>
                                </div>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">About / Bio <small class="text-muted">(20–500 characters)</small></label>
                                <textarea class="form-control" name="bio" id="edit_bio" rows="4"
                                          placeholder="Tell us about yourself..."
                                          data-validate-rule="bio"
                                          data-maxlength="500" data-maxlength-target="bio_counter"><?= htmlspecialchars($student['bio'] ?? '') ?></textarea>
                                <div class="d-flex justify-content-between">
                                    <div class="invalid-feedback"></div>
                                    <small class="text-muted ms-auto" id="bio_counter"><?= mb_strlen($student['bio'] ?? '') ?>/500</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i> Save Profile</button>
                <a href="<?= url('/student/profile') ?>" class="btn btn-light ms-2">Cancel</a>
            </div>
        </form>

        <!-- Projects Section -->
        <div class="card mt-4">
            <div class="card-header">
                <h6><i class="fas fa-project-diagram me-2 text-primary"></i>Projects</h6>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addProjectModal"><i class="fas fa-plus me-1"></i> Add</button>
            </div>
            <div class="card-body p-0">
                <?php if (empty($projects)): ?>
                <div class="p-4 text-center text-muted"><small>No projects added yet.</small></div>
                <?php else: ?>
                <?php foreach ($projects as $p): ?>
                <div class="p-3 border-bottom d-flex justify-content-between align-items-start">
                    <div>
                        <div class="fw-bold"><?= htmlspecialchars($p['title']) ?></div>
                        <small class="text-muted"><?= htmlspecialchars($p['technologies'] ?? '') ?></small>
                    </div>
                    <a href="<?= url('/student/delete-project/' . $p['id']) ?>" class="btn btn-sm btn-danger btn-icon" data-confirm="Delete this project?"><i class="fas fa-trash" style="font-size:0.7rem"></i></a>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Certifications Section -->
        <div class="card mt-4">
            <div class="card-header">
                <h6><i class="fas fa-certificate me-2 text-warning"></i>Certifications</h6>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addCertModal"><i class="fas fa-plus me-1"></i> Add</button>
            </div>
            <div class="card-body p-0">
                <?php if (empty($certifications)): ?>
                <div class="p-4 text-center text-muted"><small>No certifications added yet.</small></div>
                <?php else: ?>
                <?php foreach ($certifications as $c): ?>
                <div class="p-3 border-bottom d-flex justify-content-between">
                    <div><div class="fw-bold"><?= htmlspecialchars($c['title']) ?></div><small class="text-muted"><?= htmlspecialchars($c['issuing_org'] ?? '') ?></small></div>
                    <a href="<?= url('/student/delete-certification/' . $c['id']) ?>" class="btn btn-sm btn-danger btn-icon" data-confirm="Delete?"><i class="fas fa-trash" style="font-size:0.7rem"></i></a>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Languages -->
        <div class="card mt-4">
            <div class="card-header">
                <h6><i class="fas fa-language me-2 text-info"></i>Languages</h6>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addLangModal"><i class="fas fa-plus me-1"></i> Add</button>
            </div>
            <div class="card-body p-0">
                <?php if (empty($languages)): ?>
                <div class="p-4 text-center text-muted"><small>No languages added yet.</small></div>
                <?php else: ?>
                <?php foreach ($languages as $l): ?>
                <div class="p-3 border-bottom d-flex justify-content-between">
                    <span><?= htmlspecialchars($l['language']) ?> - <span class="badge <?= $l['proficiency'] === 'native' ? 'bg-success' : 'bg-secondary' ?>"><?= ucfirst($l['proficiency']) ?></span></span>
                    <a href="<?= url('/student/delete-language/' . $l['id']) ?>" class="btn btn-sm btn-danger btn-icon"><i class="fas fa-trash" style="font-size:0.7rem"></i></a>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Achievements -->
        <div class="card mt-4">
            <div class="card-header">
                <h6><i class="fas fa-trophy me-2 text-warning"></i>Achievements</h6>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addAchModal"><i class="fas fa-plus me-1"></i> Add</button>
            </div>
            <div class="card-body p-0">
                <?php if (empty($achievements)): ?>
                <div class="p-4 text-center text-muted"><small>No achievements added yet.</small></div>
                <?php else: ?>
                <?php foreach ($achievements as $a): ?>
                <div class="p-3 border-bottom d-flex justify-content-between">
                    <div><div class="fw-bold"><?= htmlspecialchars($a['title']) ?></div><small class="text-muted"><?= $a['date'] ? formatDate($a['date'], 'M Y') : '' ?></small></div>
                    <a href="<?= url('/student/delete-achievement/' . $a['id']) ?>" class="btn btn-sm btn-danger btn-icon" data-confirm="Delete?"><i class="fas fa-trash" style="font-size:0.7rem"></i></a>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Add Project Modal -->
<div class="modal fade" id="addProjectModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title">Add Project</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <form action="<?= url('/student/add-project') ?>" method="POST">
        <?= CsrfMiddleware::tokenField() ?>
        <div class="modal-body">
            <div class="mb-3">
                <label class="form-label">Title *</label>
                <input type="text" class="form-control" name="title" id="project_title" required
                       data-validate-rule="text" data-validate-label="Project title" data-validate-min="2" data-validate-max="150">
                <div class="invalid-feedback"></div>
            </div>
            <div class="mb-3">
                <label class="form-label">Description <small class="text-muted">(max 1000)</small></label>
                <textarea class="form-control" name="description" rows="2" data-validate-rule="text" data-validate-label="Description" data-validate-min="1" data-validate-max="1000" data-validate-required="false"></textarea>
                <div class="invalid-feedback"></div>
            </div>
            <div class="mb-3">
                <label class="form-label">Technologies <small class="text-muted">(max 300)</small></label>
                <input type="text" class="form-control" name="technologies" id="project_technologies" placeholder="React, Node.js, MongoDB"
                       data-validate-rule="text" data-validate-label="Technologies" data-validate-min="1" data-validate-max="300" data-validate-required="false">
                <div class="invalid-feedback"></div>
            </div>
            <div class="mb-3">
                <label class="form-label">Project URL *</label>
                <input type="url" class="form-control" name="project_url" id="project_url" placeholder="https://github.com/user/project"
                       data-validate-rule="projectUrl" data-validate-label="Project URL">
                <div class="invalid-feedback"></div>
            </div>
            <div class="row"><div class="col-6"><label class="form-label">Start Date</label><input type="date" class="form-control" name="start_date" max="<?= date('Y-m-d') ?>"></div><div class="col-6"><label class="form-label">End Date</label><input type="date" class="form-control" name="end_date" max="<?= date('Y-m-d') ?>"></div></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Add Project</button></div>
    </form>
</div></div></div>

<!-- Add Certification Modal -->
<div class="modal fade" id="addCertModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title">Add Certification</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <form action="<?= url('/student/add-certification') ?>" method="POST">
        <?= CsrfMiddleware::tokenField() ?>
        <div class="modal-body">
            <div class="mb-3">
                <label class="form-label">Title *</label>
                <input type="text" class="form-control" name="title" id="cert_title" required
                       data-validate-rule="text" data-validate-label="Certificate title" data-validate-min="2" data-validate-max="150">
                <div class="invalid-feedback"></div>
            </div>
            <div class="mb-3"><label class="form-label">Issuing Organization</label><input type="text" class="form-control" name="issuing_org"></div>
            <div class="row"><div class="col-6"><label class="form-label">Issue Date</label><input type="date" class="form-control" name="issue_date" max="<?= date('Y-m-d') ?>"></div><div class="col-6"><label class="form-label">Credential ID</label><input type="text" class="form-control" name="credential_id"></div></div>
            <div class="mb-3 mt-3">
                <label class="form-label">Credential URL</label>
                <input type="url" class="form-control" name="credential_url" id="cert_url" placeholder="https://..."
                       data-validate-rule="optionalUrl" data-validate-label2="Credential URL">
                <div class="invalid-feedback"></div>
            </div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Add Certification</button></div>
    </form>
</div></div></div>

<!-- Add Language Modal -->
<div class="modal fade" id="addLangModal" tabindex="-1"><div class="modal-dialog modal-sm"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title">Add Language</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <form action="<?= url('/student/add-language') ?>" method="POST">
        <?= CsrfMiddleware::tokenField() ?>
        <div class="modal-body">
            <div class="mb-3">
                <label class="form-label">Language *</label>
                <input type="text" class="form-control" name="language" id="language_name" required
                       data-validate-rule="language" data-validate-label="Language name">
                <div class="invalid-feedback"></div>
            </div>
            <div class="mb-3"><label class="form-label">Proficiency</label>
                <select class="form-select" name="proficiency"><option value="beginner">Beginner</option><option value="intermediate" selected>Intermediate</option><option value="advanced">Advanced</option><option value="native">Native</option></select>
            </div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Add</button></div>
    </form>
</div></div></div>

<!-- Add Achievement Modal -->
<div class="modal fade" id="addAchModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title">Add Achievement</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <form action="<?= url('/student/add-achievement') ?>" method="POST">
        <?= CsrfMiddleware::tokenField() ?>
        <div class="modal-body">
            <div class="mb-3">
                <label class="form-label">Title *</label>
                <input type="text" class="form-control" name="title" id="ach_title" required
                       data-validate-rule="text" data-validate-label="Achievement title" data-validate-min="2" data-validate-max="150">
                <div class="invalid-feedback"></div>
            </div>
            <div class="mb-3">
                <label class="form-label">Description <small class="text-muted">(max 500)</small></label>
                <textarea class="form-control" name="description" rows="2"
                          data-validate-rule="achievement" data-validate-label="Description"></textarea>
                <div class="invalid-feedback"></div>
            </div>
            <div class="mb-3"><label class="form-label">Date</label><input type="date" class="form-control" name="date" max="<?= date('Y-m-d') ?>"></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Add</button></div>
    </form>
</div></div></div>

<script>
function previewImage(input, previewId) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => document.getElementById(previewId).src = e.target.result;
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<script src="<?= asset('js/validation.js') ?>"></script>
<script>
// Initialize validation on all modal forms as well
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.modal form').forEach(function(f) {
        TPMSValidation.initForm(f);
    });
});
</script>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>
