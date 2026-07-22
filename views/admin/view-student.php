<?php require_once ROOT_PATH . '/includes/header.php'; ?>
<div class="content-header"><div><h1 class="page-title"><?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?></h1><p class="subtitle"><?= htmlspecialchars($student['branch'] ?? '') ?> | <?= htmlspecialchars($student['enrollment_no'] ?? '') ?></p></div><a href="<?= url('/admin/students') ?>" class="btn btn-light btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a></div>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card"><div class="card-body profile-card">
            <img src="<?= $student['profile_photo'] ? uploadUrl('profile_photos/' . $student['profile_photo']) : asset('images/default-avatar.png') ?>" alt="" class="profile-avatar" onerror="this.src='<?= asset('images/default-avatar.png') ?>'">
            <h5 class="profile-name"><?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?></h5>
            <p class="profile-role"><?= htmlspecialchars($student['email'] ?? '') ?></p>
            <div class="mb-3"><?php if ($student['is_placed']): ?><span class="badge bg-success fs-6">Placed at <?= htmlspecialchars($student['placed_company'] ?? '') ?></span><?php endif; ?></div>
            <div class="text-start">
                <div class="row g-2" style="font-size:0.85rem">
                    <div class="col-6"><strong>Phone:</strong><br><?= htmlspecialchars($student['phone'] ?? 'N/A') ?></div>
                    <div class="col-6"><strong>DOB:</strong><br><?= $student['dob'] ? formatDate($student['dob']) : 'N/A' ?></div>
                    <div class="col-6"><strong>Gender:</strong><br><?= ucfirst($student['gender'] ?? 'N/A') ?></div>
                    <div class="col-6"><strong>CGPA:</strong><br><span class="text-primary fw-bold"><?= $student['cgpa'] ?? 'N/A' ?></span></div>
                    <div class="col-6"><strong>10th:</strong><br><?= ($student['tenth_percentage'] ?? 'N/A') . '%' ?></div>
                    <div class="col-6"><strong>12th:</strong><br><?= ($student['twelfth_percentage'] ?? 'N/A') . '%' ?></div>
                    <div class="col-6"><strong>Backlogs:</strong><br><?= $student['backlogs'] ?? 0 ?> (Active: <?= $student['active_backlogs'] ?? 0 ?>)</div>
                    <div class="col-6"><strong>Year:</strong><br><?= $student['passing_year'] ?? 'N/A' ?></div>
                    <?php if ($student['placed_package']): ?><div class="col-12"><strong>Package:</strong> <?= formatCurrency($student['placed_package']) ?></div><?php endif; ?>
                </div>
                <?php if ($student['skills']): ?><div class="mt-3"><strong>Skills:</strong><div class="d-flex flex-wrap gap-1 mt-1"><?php foreach (explode(',', $student['skills']) as $sk): ?><span class="job-tag"><?= htmlspecialchars(trim($sk)) ?></span><?php endforeach; ?></div></div><?php endif; ?>
                <?php if ($student['resume_path']): ?><a href="<?= uploadUrl('resume/' . $student['resume_path']) ?>" target="_blank" class="btn btn-outline-primary btn-sm mt-3 w-100"><i class="fas fa-file-pdf me-1"></i>View Resume</a><?php endif; ?>
            </div>
        </div></div>
    </div>
    <div class="col-lg-8">
        <div class="card mb-4"><div class="card-header"><h6><i class="fas fa-paper-plane me-2 text-primary"></i>Applications (<?= count($applications) ?>)</h6></div>
        <div class="card-body p-0"><div class="table-responsive"><table class="table mb-0"><thead><tr><th>Job</th><th>Company</th><th>Status</th><th>Date</th></tr></thead><tbody>
            <?php foreach ($applications as $a): ?>
            <tr><td class="fw-medium"><?= htmlspecialchars($a['job_title']) ?></td><td><?= htmlspecialchars($a['company_name']) ?></td><td><span class="badge <?= getStatusBadgeClass($a['status']) ?>"><?= ucfirst($a['status']) ?></span></td><td><small><?= formatDate($a['applied_at']) ?></small></td></tr>
            <?php endforeach; ?>
            <?php if (empty($applications)): ?><tr><td colspan="4" class="text-center text-muted py-3">No applications</td></tr><?php endif; ?>
        </tbody></table></div></div></div>

        <?php if (!empty($projects)): ?>
        <div class="card mb-4"><div class="card-header"><h6><i class="fas fa-project-diagram me-2 text-primary"></i>Projects (<?= count($projects) ?>)</h6></div>
        <div class="card-body p-0"><?php foreach ($projects as $p): ?><div class="p-3 border-bottom"><div class="fw-bold"><?= htmlspecialchars($p['title']) ?></div><small class="text-muted"><?= htmlspecialchars($p['technologies'] ?? '') ?></small></div><?php endforeach; ?></div></div>
        <?php endif; ?>
    </div>
</div>
<?php require_once ROOT_PATH . '/includes/footer.php'; ?>
