<?php require_once ROOT_PATH . '/includes/header.php'; ?>
<div class="content-header"><div><h1 class="page-title">Edit Job</h1><p class="subtitle"><?= htmlspecialchars($job['title']) ?></p></div><a href="<?= url('/company/jobs') ?>" class="btn btn-light btn-sm"><i class="fas fa-arrow-left me-1"></i> Back</a></div>

<div class="card"><div class="card-body">
<form action="<?= url('/company/edit-job/' . $job['id']) ?>" method="POST" data-validate>
    <?= CsrfMiddleware::tokenField() ?>
    <div class="row g-3">
        <div class="col-md-8"><label class="form-label">Job Title *</label><input type="text" class="form-control" name="title" value="<?= htmlspecialchars($job['title']) ?>" required></div>
        <div class="col-md-4"><label class="form-label">Job Type</label><select class="form-select" name="job_type"><?php foreach (JOB_TYPES as $k => $v): ?><option value="<?= $k ?>" <?= $job['job_type'] === $k ? 'selected' : '' ?>><?= $v ?></option><?php endforeach; ?></select></div>
        <div class="col-12"><label class="form-label">Description</label><textarea class="form-control" name="description" rows="5"><?= htmlspecialchars($job['description'] ?? '') ?></textarea></div>
        <div class="col-md-4"><label class="form-label">Work Mode</label><select class="form-select" name="work_mode"><option value="onsite" <?= ($job['work_mode'] ?? '') === 'onsite' ? 'selected' : '' ?>>On-site</option><option value="remote" <?= ($job['work_mode'] ?? '') === 'remote' ? 'selected' : '' ?>>Remote</option><option value="hybrid" <?= ($job['work_mode'] ?? '') === 'hybrid' ? 'selected' : '' ?>>Hybrid</option></select></div>
        <div class="col-md-4"><label class="form-label">Location</label><input type="text" class="form-control" name="location" value="<?= htmlspecialchars($job['location'] ?? '') ?>"></div>
        <div class="col-md-4"><label class="form-label">Openings</label><input type="number" class="form-control" name="openings" value="<?= $job['openings'] ?? 1 ?>" min="1"></div>
        <div class="col-12">
            <label class="form-label fw-semibold">Salary Range (LPA)</label>
            <div class="input-group">
                <input type="number" class="form-control" name="salary_min" step="0.5" min="0" value="<?= $job['salary_min'] ?? '' ?>" placeholder="Min e.g. 3">
                <span class="input-group-text fw-bold">–</span>
                <input type="number" class="form-control" name="salary_max" step="0.5" min="0" value="<?= $job['salary_max'] ?? '' ?>" placeholder="Max e.g. 8">
                <span class="input-group-text text-muted">LPA</span>
            </div>
            <small class="text-muted" style="font-size:0.78rem">Example: 3 – 8 means 3 to 8 Lakhs Per Annum.</small>
        </div>
        <div class="col-md-4"><label class="form-label">Application Deadline</label><input type="date" class="form-control" name="application_deadline" value="<?= $job['application_deadline'] ?? '' ?>"></div>
        <div class="col-12"><label class="form-label">Skills Required</label><input type="text" class="form-control" name="skills_required" value="<?= htmlspecialchars($job['skills_required'] ?? '') ?>"></div>
        <div class="col-md-4"><label class="form-label">Min CGPA</label><input type="number" class="form-control" name="eligibility_cgpa" step="0.01" min="0" max="10" value="<?= $job['eligibility_cgpa'] ?? 0 ?>"></div>
        <div class="col-md-4"><label class="form-label">Eligible Branches</label><input type="text" class="form-control" name="eligibility_branches" value="<?= htmlspecialchars($job['eligibility_branches'] ?? '') ?>"></div>
        <div class="col-md-4"><label class="form-label">Max Active Backlogs</label><input type="number" class="form-control" name="eligibility_backlogs" value="<?= $job['eligibility_backlogs'] ?? 0 ?>" min="0"></div>
    </div>
    <div class="mt-4"><button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Update Job</button><a href="<?= url('/company/jobs') ?>" class="btn btn-light ms-2">Cancel</a></div>
</form>
</div></div>
<?php require_once ROOT_PATH . '/includes/footer.php'; ?>
