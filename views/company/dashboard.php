<?php require_once ROOT_PATH . '/includes/header.php'; ?>

<div class="content-header">
    <div>
        <h1 class="page-title">Welcome, <?= htmlspecialchars($company['company_name']) ?>!</h1>
        <p class="subtitle">Company recruitment dashboard</p>
    </div>
    <a href="<?= url('/company/post-job') ?>" class="btn btn-primary btn-sm"><i class="fas fa-plus-circle me-1"></i> Post New Job</a>
</div>

<?php if (!$company['is_approved']): ?>
<div class="alert alert-warning animate-fade-in-up"><i class="fas fa-clock me-2"></i><strong>Pending Approval</strong> — Your company registration is under review. You'll be able to post jobs once approved.</div>
<?php endif; ?>

<!-- Stats -->
<div class="row g-4 mb-4">
    <div class="col-xl-2 col-lg-4 col-sm-6"><div class="stat-card gradient-primary"><div class="stat-card-icon bg-primary-soft"><i class="fas fa-briefcase"></i></div><div class="stat-card-value"><?= $totalJobs ?></div><div class="stat-card-label">Total Jobs</div></div></div>
    <div class="col-xl-2 col-lg-4 col-sm-6"><div class="stat-card gradient-success"><div class="stat-card-icon bg-success-soft"><i class="fas fa-check-circle"></i></div><div class="stat-card-value"><?= $activeJobs ?></div><div class="stat-card-label">Active Jobs</div></div></div>
    <div class="col-xl-2 col-lg-4 col-sm-6"><div class="stat-card gradient-info"><div class="stat-card-icon bg-info-soft"><i class="fas fa-paper-plane"></i></div><div class="stat-card-value"><?= $totalApplications ?></div><div class="stat-card-label">Applications</div></div></div>
    <div class="col-xl-2 col-lg-4 col-sm-6"><div class="stat-card gradient-warning"><div class="stat-card-icon bg-warning-soft"><i class="fas fa-star"></i></div><div class="stat-card-value"><?= $shortlisted ?></div><div class="stat-card-label">Shortlisted</div></div></div>
    <div class="col-xl-2 col-lg-4 col-sm-6"><div class="stat-card gradient-violet"><div class="stat-card-icon bg-violet-soft"><i class="fas fa-trophy"></i></div><div class="stat-card-value"><?= $selected ?></div><div class="stat-card-label">Selected</div></div></div>
    <div class="col-xl-2 col-lg-4 col-sm-6"><div class="stat-card gradient-danger"><div class="stat-card-icon bg-danger-soft"><i class="fas fa-calendar-check"></i></div><div class="stat-card-value"><?= $interviewCount ?></div><div class="stat-card-label">Interviews</div></div></div>
</div>

<div class="row g-4">
    <!-- Jobs List -->
    <div class="col-lg-5">
        <div class="card">
            <div class="card-header"><h6><i class="fas fa-briefcase me-2 text-primary"></i>Your Jobs</h6><a href="<?= url('/company/jobs') ?>" class="btn btn-sm btn-outline-primary">View All</a></div>
            <div class="card-body p-0">
                <?php if (empty($jobs)): ?>
                <div class="empty-state py-4"><i class="fas fa-briefcase" style="font-size:2rem"></i><p class="mt-2"><small>No jobs posted yet.</small></p></div>
                <?php else: ?>
                <?php foreach (array_slice($jobs, 0, 5) as $job): ?>
                <div class="p-3 border-bottom">
                    <div class="d-flex justify-content-between align-items-start">
                        <div><div class="fw-bold" style="font-size:0.9rem"><?= htmlspecialchars($job['title']) ?></div>
                            <small class="text-muted"><?= $job['application_count'] ?> applications • <?= JOB_TYPES[$job['job_type']] ?? ucfirst($job['job_type']) ?></small></div>
                        <span class="badge <?= getStatusBadgeClass($job['status']) ?>"><?= ucfirst($job['status']) ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Recent Applications -->
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header"><h6><i class="fas fa-users me-2 text-primary"></i>Recent Applications</h6></div>
            <div class="card-body p-0">
                <?php if (empty($recentApps)): ?>
                <div class="empty-state py-4"><i class="fas fa-inbox" style="font-size:2rem"></i><p class="mt-2"><small>No applications yet.</small></p></div>
                <?php else: ?>
                <div class="table-responsive"><table class="table mb-0">
                    <thead><tr><th>Student</th><th>Job</th><th>Branch</th><th>CGPA</th><th>Status</th><th>Date</th></tr></thead>
                    <tbody>
                        <?php foreach ($recentApps as $a): ?>
                        <tr>
                            <td><div class="user-cell"><img src="<?= $a['profile_photo'] ? uploadUrl('profile_photos/' . $a['profile_photo']) : asset('images/default-avatar.png') ?>" alt="" class="user-avatar" onerror="this.src='<?= asset('images/default-avatar.png') ?>'"><span class="fw-medium"><?= htmlspecialchars($a['first_name'] . ' ' . $a['last_name']) ?></span></div></td>
                            <td><small><?= htmlspecialchars($a['job_title']) ?></small></td>
                            <td><small><?= htmlspecialchars($a['branch']) ?></small></td>
                            <td><span class="fw-bold text-primary"><?= $a['cgpa'] ?? 'N/A' ?></span></td>
                            <td><span class="badge <?= getStatusBadgeClass($a['status']) ?>"><?= ucfirst($a['status']) ?></span></td>
                            <td><small class="text-muted"><?= timeAgo($a['applied_at']) ?></small></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php require_once ROOT_PATH . '/includes/footer.php'; ?>
