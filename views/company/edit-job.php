<?php require_once ROOT_PATH . '/includes/header.php'; ?>

<div class="content-header">
    <div>
        <h1 class="page-title">Edit Job</h1>
        <p class="subtitle"><?= htmlspecialchars($job['title']) ?></p>
    </div>
    <a href="<?= url('/company/jobs') ?>" class="btn btn-light btn-sm"><i class="fas fa-arrow-left me-1"></i> Back to Jobs</a>
</div>

<form action="<?= url('/company/edit-job/' . $job['id']) ?>" method="POST" data-tpms-validate id="editJobForm">
    <?= CsrfMiddleware::tokenField() ?>

    <!-- Section 1: Basic Information -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-transparent py-3">
            <h5 class="fw-bold mb-0 text-primary"><i class="fas fa-briefcase me-2"></i>Basic Job Details</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label fw-semibold">Job Title *</label>
                    <input type="text" class="form-control" name="title" id="job_title"
                           placeholder="e.g. Software Development Engineer"
                           value="<?= htmlspecialchars($oldInput['title'] ?? $job['title'] ?? '') ?>" required
                           data-validate-rule="text" data-validate-label="Job title" data-validate-min="3" data-validate-max="150">
                    <div class="invalid-feedback"></div>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">Job Type / Employment Type *</label>
                    <select class="form-select" name="job_type" id="job_type" required data-validate-rule="text" data-validate-label="Job type">
                        <option value="">Select Job Type</option>
                        <?php foreach (JOB_TYPES as $k => $v): ?>
                        <option value="<?= $k ?>" <?= ($oldInput['job_type'] ?? $job['job_type'] ?? '') === $k ? 'selected' : '' ?>><?= $v ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="invalid-feedback"></div>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">Work Mode *</label>
                    <select class="form-select" name="work_mode" id="work_mode" required data-validate-rule="text" data-validate-label="Work mode">
                        <option value="onsite" <?= ($oldInput['work_mode'] ?? $job['work_mode'] ?? 'onsite') === 'onsite' ? 'selected' : '' ?>>On-site</option>
                        <option value="remote" <?= ($oldInput['work_mode'] ?? $job['work_mode'] ?? '') === 'remote' ? 'selected' : '' ?>>Remote</option>
                        <option value="hybrid" <?= ($oldInput['work_mode'] ?? $job['work_mode'] ?? '') === 'hybrid' ? 'selected' : '' ?>>Hybrid</option>
                    </select>
                    <div class="invalid-feedback"></div>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">Location *</label>
                    <input type="text" class="form-control" name="location" id="job_location"
                           placeholder="e.g. Mumbai, Pune, Bangalore"
                           value="<?= htmlspecialchars($oldInput['location'] ?? $job['location'] ?? '') ?>" required
                           data-validate-rule="text" data-validate-label="Location" data-validate-min="2" data-validate-max="150">
                    <div class="invalid-feedback"></div>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">Number of Vacancies / Openings *</label>
                    <input type="number" class="form-control" name="openings" id="job_openings"
                           placeholder="e.g. 5" min="1"
                           value="<?= htmlspecialchars($oldInput['openings'] ?? $job['openings'] ?? '1') ?>" required
                           data-validate-rule="integer" data-validate-label="Number of vacancies" data-validate-min="1">
                    <div class="invalid-feedback"></div>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Min Package (LPA) *</label>
                    <input type="number" class="form-control" name="salary_min" id="salary_min"
                           step="0.1" min="0" placeholder="e.g. 4.5"
                           value="<?= htmlspecialchars($oldInput['salary_min'] ?? $job['salary_min'] ?? '') ?>" required
                           data-validate-rule="numeric" data-validate-label="Minimum salary" data-validate-min="0">
                    <div class="invalid-feedback"></div>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Max Package (LPA) *</label>
                    <input type="number" class="form-control" name="salary_max" id="salary_max"
                           step="0.1" min="0" placeholder="e.g. 12.0"
                           value="<?= htmlspecialchars($oldInput['salary_max'] ?? $job['salary_max'] ?? '') ?>" required
                           data-validate-rule="salaryMax" data-validate-label="Maximum salary">
                    <div class="invalid-feedback"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Section 2: Timeline & Dates -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-transparent py-3">
            <h5 class="fw-bold mb-0 text-primary"><i class="fas fa-calendar-alt me-2"></i>Key Dates &amp; Timeline</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Application Deadline *</label>
                    <input type="date" class="form-control" name="application_deadline" id="application_deadline"
                           min="<?= date('Y-m-d') ?>"
                           value="<?= htmlspecialchars($oldInput['application_deadline'] ?? $job['application_deadline'] ?? '') ?>" required
                           data-validate-rule="date" data-validate-label="Application deadline" data-validate-future="false">
                    <div class="invalid-feedback"></div>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Expected Joining Date *</label>
                    <input type="date" class="form-control" name="joining_date" id="joining_date"
                           min="<?= date('Y-m-d') ?>"
                           value="<?= htmlspecialchars($oldInput['joining_date'] ?? $job['joining_date'] ?? '') ?>" required
                           data-validate-rule="date" data-validate-label="Expected joining date" data-validate-future="false">
                    <div class="invalid-feedback"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Section 3: Eligibility & Requirements -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-transparent py-3">
            <h5 class="fw-bold mb-0 text-primary"><i class="fas fa-user-check me-2"></i>Eligibility Criteria &amp; Requirements</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Required Qualification / Degree *</label>
                    <input type="text" class="form-control" name="qualification" id="job_qualification"
                           placeholder="e.g. B.Tech, B.E., M.Tech, MCA"
                           value="<?= htmlspecialchars($oldInput['qualification'] ?? $job['qualification'] ?? '') ?>" required
                           data-validate-rule="text" data-validate-label="Qualification" data-validate-min="2" data-validate-max="100">
                    <div class="invalid-feedback"></div>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Eligible Branches / Departments <small class="text-muted">(Optional)</small></label>
                    <input type="text" class="form-control" name="eligibility_branches" id="eligibility_branches"
                           placeholder="e.g. CSE, IT, ECE (leave blank for all branches)"
                           value="<?= htmlspecialchars($oldInput['eligibility_branches'] ?? $job['eligibility_branches'] ?? '') ?>"
                           data-validate-rule="text" data-validate-label="Eligible branches" data-validate-min="2" data-validate-max="200" data-validate-required="false">
                    <div class="invalid-feedback"></div>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">Eligible Passing Year *</label>
                    <input type="text" class="form-control" name="passing_year" id="passing_year"
                           placeholder="e.g. 2025, 2026, or All"
                           value="<?= htmlspecialchars($oldInput['passing_year'] ?? $job['passing_year'] ?? date('Y')) ?>" required
                           data-validate-rule="text" data-validate-label="Passing year" data-validate-min="2" data-validate-max="50">
                    <div class="invalid-feedback"></div>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">Minimum CGPA (0.0 – 10.0) *</label>
                    <input type="number" class="form-control" name="eligibility_cgpa" id="eligibility_cgpa"
                           step="0.01" min="0" max="10" placeholder="e.g. 6.50"
                           value="<?= htmlspecialchars($oldInput['eligibility_cgpa'] ?? $job['eligibility_cgpa'] ?? '0.00') ?>" required
                           data-validate-rule="numeric" data-validate-label="Minimum CGPA" data-validate-min="0" data-validate-max="10">
                    <div class="invalid-feedback"></div>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">Max Active Backlogs Allowed *</label>
                    <input type="number" class="form-control" name="eligibility_backlogs" id="eligibility_backlogs"
                           min="0" placeholder="e.g. 0"
                           value="<?= htmlspecialchars($oldInput['eligibility_backlogs'] ?? $job['eligibility_backlogs'] ?? '0') ?>" required
                           data-validate-rule="integer" data-validate-label="Max active backlogs" data-validate-min="0">
                    <div class="invalid-feedback"></div>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Experience Required *</label>
                    <input type="text" class="form-control" name="experience_required" id="experience_required"
                           placeholder="e.g. Freshers / 0-2 years"
                           value="<?= htmlspecialchars($oldInput['experience_required'] ?? $job['experience_required'] ?? 'Freshers') ?>" required
                           data-validate-rule="text" data-validate-label="Experience required" data-validate-min="2" data-validate-max="100">
                    <div class="invalid-feedback"></div>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Required Skills (comma separated) *</label>
                    <input type="text" class="form-control" name="skills_required" id="skills_required"
                           placeholder="e.g. Java, Python, React, MySQL"
                           value="<?= htmlspecialchars($oldInput['skills_required'] ?? $job['skills_required'] ?? '') ?>" required
                           data-validate-rule="skills" data-maxlength="300">
                    <div class="invalid-feedback"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Section 4: Selection Process & Description -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-transparent py-3">
            <h5 class="fw-bold mb-0 text-primary"><i class="fas fa-tasks me-2"></i>Selection Process &amp; Description</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label fw-semibold">Selection Process *</label>
                    <textarea class="form-control" name="selection_process" id="selection_process" rows="3"
                              placeholder="e.g. Round 1: Online Aptitude Test, Round 2: Technical Interview, Round 3: HR Round"
                              required data-validate-rule="text" data-validate-label="Selection process" data-validate-min="10" data-validate-max="1000"><?= htmlspecialchars($oldInput['selection_process'] ?? $job['selection_process'] ?? '') ?></textarea>
                    <div class="invalid-feedback"></div>
                </div>

                <div class="col-12">
                    <label class="form-label fw-semibold">Detailed Job Description * <small class="text-muted">(20–2000 chars)</small></label>
                    <textarea class="form-control" name="description" id="job_description" rows="6"
                              placeholder="Describe responsibilities, role expectations, perks, and working culture..."
                              required data-validate-rule="text" data-validate-label="Job description" data-validate-min="20" data-validate-max="2000"
                              data-maxlength="2000" data-maxlength-target="desc_counter"><?= htmlspecialchars($oldInput['description'] ?? $job['description'] ?? '') ?></textarea>
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="invalid-feedback"></div>
                        <small class="text-muted ms-auto" id="desc_counter"><?= mb_strlen($oldInput['description'] ?? $job['description'] ?? '') ?>/2000</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Section 5: Contact & Company Link -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-transparent py-3">
            <h5 class="fw-bold mb-0 text-primary"><i class="fas fa-address-card me-2"></i>Contact Information &amp; Company Link</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Contact Person *</label>
                    <input type="text" class="form-control" name="contact_person" id="contact_person"
                           placeholder="HR Manager name"
                           value="<?= htmlspecialchars($oldInput['contact_person'] ?? $job['contact_person'] ?? $company['contact_person'] ?? '') ?>" required
                           data-validate-rule="text" data-validate-label="Contact person" data-validate-min="2" data-validate-max="100">
                    <div class="invalid-feedback"></div>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Contact Email *</label>
                    <input type="email" class="form-control" name="contact_email" id="contact_email"
                           placeholder="hr@company.com"
                           value="<?= htmlspecialchars($oldInput['contact_email'] ?? $job['contact_email'] ?? $company['contact_email'] ?? '') ?>" required
                           data-validate-rule="email" data-validate-label="Contact email">
                    <div class="invalid-feedback"></div>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Contact Phone (10 digits) *</label>
                    <input type="text" class="form-control" name="contact_phone" id="contact_phone"
                           placeholder="10-digit phone number" maxlength="10" inputmode="numeric"
                           value="<?= htmlspecialchars($oldInput['contact_phone'] ?? $job['contact_phone'] ?? $company['contact_phone'] ?? '') ?>" required
                           data-validate-rule="phone" data-validate-label="Contact phone">
                    <div class="invalid-feedback"></div>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Company Website / Application URL *</label>
                    <input type="url" class="form-control" name="website" id="company_website"
                           placeholder="https://company.com"
                           value="<?= htmlspecialchars($oldInput['website'] ?? $job['website'] ?? $company['website'] ?? '') ?>" required
                           data-validate-rule="projectUrl" data-validate-label="Company website / URL">
                    <div class="invalid-feedback"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Submit Button -->
    <div class="mb-5 text-end">
        <a href="<?= url('/company/jobs') ?>" class="btn btn-light btn-lg me-2">Cancel</a>
        <button type="submit" class="btn btn-primary btn-lg px-5">
            <i class="fas fa-save me-2"></i> Update Job Posting
        </button>
    </div>
</form>

<script src="<?= asset('js/validation.js') ?>"></script>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>

