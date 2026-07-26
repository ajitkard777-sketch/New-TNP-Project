<?php require_once ROOT_PATH . '/includes/header.php'; ?>
<div class="content-header"><div><h1 class="page-title">Post New Job</h1><p class="subtitle">Create a new job posting for campus recruitment</p></div><a href="<?= url('/company/jobs') ?>" class="btn btn-light btn-sm"><i class="fas fa-arrow-left me-1"></i> Back</a></div>

<div class="card"><div class="card-body">
<form action="<?= url('/company/post-job') ?>" method="POST" data-validate>
    <?= CsrfMiddleware::tokenField() ?>
    <div class="row g-3">
        <div class="col-md-8"><label class="form-label">Job Title *</label><input type="text" class="form-control" name="title" placeholder="e.g. Software Engineer" required></div>
        <div class="col-md-4"><label class="form-label">Job Type</label><select class="form-select" name="job_type"><?php foreach (JOB_TYPES as $k => $v): ?><option value="<?= $k ?>"><?= $v ?></option><?php endforeach; ?></select></div>
        <div class="col-12"><label class="form-label">Description *</label><textarea class="form-control" name="description" rows="5" placeholder="Job description, responsibilities, requirements..." required></textarea></div>
        <div class="col-md-4"><label class="form-label">Work Mode</label><select class="form-select" name="work_mode"><option value="onsite">On-site</option><option value="remote">Remote</option><option value="hybrid">Hybrid</option></select></div>
        <div class="col-md-4"><label class="form-label">Location</label><input type="text" class="form-control" name="location" placeholder="City"></div>
        <div class="col-md-4"><label class="form-label">Openings</label><input type="number" class="form-control" name="openings" value="1" min="1"></div>
        <div class="col-md-8">
            <label class="form-label fw-semibold">Salary Range (LPA)</label>
            <div class="input-group">
                <input type="number" class="form-control" name="salary_min" step="0.5" min="0" placeholder="Min e.g. 3">
                <span class="input-group-text fw-bold">–</span>
                <input type="number" class="form-control" name="salary_max" step="0.5" min="0" placeholder="Max e.g. 8">
                <span class="input-group-text text-muted">LPA</span>
            </div>
            <small class="text-muted" style="font-size:0.78rem">Example: 3 – 8 means 3 to 8 Lakhs Per Annum. Leave blank if not disclosed.</small>
        </div>
        <div class="col-md-4"><label class="form-label">Application Deadline</label><input type="date" class="form-control" name="application_deadline" min="<?= date('Y-m-d') ?>"></div>
        <div class="col-12"><label class="form-label">Skills Required</label><input type="text" class="form-control" name="skills_required" placeholder="e.g. Java, Python, React, SQL (comma separated)"></div>
        <div class="col-md-4"><label class="form-label">Min CGPA</label><input type="number" class="form-control" name="eligibility_cgpa" step="0.01" min="0" max="10" value="0"></div>
        <div class="col-md-4"><label class="form-label">Eligible Branches</label><input type="text" class="form-control" name="eligibility_branches" placeholder="CSE, IT, ECE (comma separated, or leave empty for all)"></div>
        <div class="col-md-4"><label class="form-label">Max Active Backlogs</label><input type="number" class="form-control" name="eligibility_backlogs" value="0" min="0"></div>
        <div class="col-12"><label class="form-label">Experience Required</label><input type="text" class="form-control" name="experience_required" placeholder="e.g. Freshers / 0-2 years"></div>
    </div>
    <div class="mt-4"><button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-paper-plane me-2"></i> Post Job</button></div>
</form>
</div></div>
<?php require_once ROOT_PATH . '/includes/footer.php'; ?>
