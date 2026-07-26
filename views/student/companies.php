<?php require_once ROOT_PATH . '/includes/header.php'; ?>

<div class="content-header mb-4">
    <div>
        <h1 class="page-title"><i class="fas fa-building text-primary me-2"></i>Companies Directory</h1>
        <p class="subtitle">Explore recruiting companies, active placement drives, and direct HR contacts</p>
    </div>
</div>

<!-- Search & Filters -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body p-4">
        <form method="GET" action="<?= url('/student/companies') ?>" class="row g-3 align-items-center">
            <div class="col-md-9">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" class="form-control border-start-0 ps-0" name="search" value="<?= htmlspecialchars($search ?? '') ?>" placeholder="Search company name, industry, city...">
                </div>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100 fw-semibold">
                    <i class="fas fa-search me-1"></i> Search Companies
                </button>
            </div>
        </form>
    </div>
</div>

<?php if (empty($companies)): ?>
<div class="card shadow-sm border-0">
    <div class="card-body text-center p-5">
        <i class="fas fa-building text-muted mb-3" style="font-size:3rem; opacity:0.5;"></i>
        <h5 class="fw-bold">No Companies Found</h5>
        <p class="text-muted small">No verified companies match your current search criteria.</p>
        <a href="<?= url('/student/companies') ?>" class="btn btn-outline-primary btn-sm">View All Companies</a>
    </div>
</div>
<?php else: ?>

<div class="row g-4">
    <?php foreach ($companies as $comp): ?>
    <div class="col-lg-6 col-md-12">
        <div class="card shadow-sm border-0 h-100 animate-fade-in-up hover-lift">
            <div class="card-body p-4 d-flex flex-column">
                
                <!-- Company Header: Logo & Title -->
                <div class="d-flex align-items-start gap-3 mb-3">
                    <img src="<?= $comp['logo'] ? uploadUrl('company/' . $comp['logo']) : asset('images/default-avatar.png') ?>" 
                         alt="<?= htmlspecialchars($comp['company_name']) ?>" 
                         class="rounded border p-1 bg-white flex-shrink-0 shadow-sm" 
                         style="width:60px; height:60px; object-fit:cover;"
                         onerror="this.src='<?= asset('images/default-avatar.png') ?>'">
                    
                    <div class="flex-grow-1 min-w-0">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <h5 class="fw-bold text-dark mb-0 text-truncate"><?= htmlspecialchars($comp['company_name']) ?></h5>
                            <span class="badge bg-primary-soft text-primary font-mono" style="font-size:0.78rem;">
                                <i class="fas fa-briefcase me-1"></i><?= $comp['open_jobs_count'] ?? 0 ?> Open Jobs
                            </span>
                        </div>
                        <div class="text-muted small">
                            <i class="fas fa-industry me-1"></i><?= htmlspecialchars($comp['industry'] ?? 'Technology & Services') ?>
                            <?php if (!empty($comp['city'])): ?>
                            <span class="ms-2"><i class="fas fa-map-marker-alt text-danger me-1"></i><?= htmlspecialchars($comp['city']) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Description -->
                <div class="text-secondary small mb-3 flex-grow-1" style="line-height:1.5;">
                    <?= htmlspecialchars(mb_strimwidth($comp['description'] ?? 'Established recruiting partner participating in campus placement drives.', 0, 160, '...')) ?>
                </div>

                <!-- HR Contact & Website Information -->
                <div class="bg-light p-3 rounded mb-3 small">
                    <div class="row g-2">
                        <div class="col-sm-6 text-truncate">
                            <i class="fas fa-user-tie text-primary me-2"></i>
                            <strong>HR Contact:</strong> <?= htmlspecialchars($comp['contact_person'] ?? 'HR Manager') ?>
                        </div>
                        <div class="col-sm-6 text-truncate">
                            <i class="fas fa-envelope text-primary me-2"></i>
                            <strong>Email:</strong> <?= htmlspecialchars($comp['hr_email'] ?? ($comp['email'] ?? 'hr@company.com')) ?>
                        </div>
                        <?php if (!empty($comp['phone'])): ?>
                        <div class="col-sm-6 text-truncate">
                            <i class="fas fa-phone text-success me-2"></i>
                            <strong>Phone:</strong> <?= htmlspecialchars($comp['phone']) ?>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($comp['website'])): ?>
                        <div class="col-sm-6 text-truncate">
                            <i class="fas fa-globe text-info me-2"></i>
                            <strong>Website:</strong> 
                            <a href="<?= htmlspecialchars((str_starts_with($comp['website'], 'http') ? '' : 'https://') . $comp['website']) ?>" target="_blank" class="fw-semibold text-decoration-underline ms-1">
                                Visit Site <i class="fas fa-external-link-alt ms-1" style="font-size:0.7rem;"></i>
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Footer Actions: Open Jobs & Chat Button -->
                <div class="d-flex align-items-center justify-content-between border-top pt-3 mt-auto">
                    <a href="<?= url('/student/jobs?search=' . urlencode($comp['company_name'])) ?>" class="btn btn-outline-primary btn-sm fw-semibold">
                        <i class="fas fa-eye me-1"></i> View Open Jobs (<?= $comp['open_jobs_count'] ?? 0 ?>)
                    </a>

                    <!-- Direct Chat HR Button -->
                    <?php if (!empty($comp['user_id'])): ?>
                    <a href="<?= url('/student/messages?partner=' . $comp['user_id']) ?>" class="btn btn-primary btn-sm fw-semibold shadow-sm">
                        <i class="fas fa-comments me-1"></i> Direct Chat HR
                    </a>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php endif; ?>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>
