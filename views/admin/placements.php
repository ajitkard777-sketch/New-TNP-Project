<?php require_once ROOT_PATH . '/includes/header.php'; ?>
<div class="content-header">
    <div>
        <h1 class="page-title">Placements</h1>
        <p class="subtitle"><?= $stats['total'] ?> total placements recorded</p>
    </div>
</div>


<!-- Search & Filter Form -->
<div class="card mb-4"><div class="card-body">
    <form method="GET" class="row g-3 align-items-end">
        <div class="col-md-4">
            <label class="form-label">Search Student</label>
            <input type="text" class="form-control" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Name or email...">
        </div>
        <div class="col-md-3">
            <label class="form-label">Branch</label>
            <select class="form-select" name="branch">
                <option value="">All Branches</option>
                <?php foreach (BRANCHES as $b): ?>
                <option value="<?= $b ?>" <?= $branch === $b ? 'selected' : '' ?>><?= $b ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Company</label>
            <select class="form-select" name="company">
                <option value="">All Companies</option>
                <?php foreach ($companyList as $c): ?>
                <option value="<?= htmlspecialchars($c['company_name']) ?>" <?= $company === $c['company_name'] ? 'selected' : '' ?>><?= htmlspecialchars($c['company_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search me-1"></i>Search</button>
        </div>
    </form>
    <?php if ($search || $branch || $company): ?>
    <div class="mt-2">
        <a href="<?= url('/admin/placements') ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-times me-1"></i>Clear Filters</a>
        <small class="text-muted ms-2">Showing filtered results</small>
    </div>
    <?php endif; ?>
</div></div>

<?php if (empty($placements)): ?>
<div class="card"><div class="card-body">
    <div class="empty-state py-5">
        <i class="fas fa-trophy" style="font-size:3rem;color:var(--border-color)"></i>
        <h5 class="mt-3">No placements found</h5>
        <p class="text-muted">Try adjusting your search or filters.</p>
    </div>
</div></div>
<?php else: ?>
<div class="card"><div class="card-body p-0"><div class="table-responsive">
    <table class="table mb-0" id="placementsTable">
        <thead><tr>
            <th>Student</th>
            <th>Email</th>
            <th>Branch</th>
            <th>Company</th>
            <th>Package (LPA)</th>
            <th>Date</th>
            <th>Status</th>
        </tr></thead>
        <tbody>
            <?php foreach ($placements as $p): ?>
            <tr>
                <td><div class="user-cell"><img src="<?= $p['profile_photo'] ? uploadUrl('profile_photos/' . $p['profile_photo']) : asset('images/default-avatar.png') ?>" alt="" class="user-avatar" onerror="this.src='<?= asset('images/default-avatar.png') ?>'"><span class="fw-medium"><?= htmlspecialchars($p['first_name'] . ' ' . $p['last_name']) ?></span></div></td>
                <td><small><?= htmlspecialchars($p['email'] ?? '') ?></small></td>
                <td><span class="badge bg-light text-dark"><?= htmlspecialchars($p['branch']) ?></span></td>
                <td class="fw-medium"><?= htmlspecialchars($p['company_name'] ?? 'N/A') ?></td>
                <td class="fw-bold text-success"><?= formatPackage($p['package']) ?></td>
                <td><small><?= formatDate($p['placement_date']) ?></small></td>
                <td><span class="badge bg-success"><?= ucfirst($p['status'] ?? 'confirmed') ?></span></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div></div></div>

<div class="mt-4">
    <?= renderPagination($pagination, url('/admin/placements'), array_filter(['search' => $search, 'branch' => $branch, 'company' => $company])) ?>
</div>
<?php endif; ?>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>
