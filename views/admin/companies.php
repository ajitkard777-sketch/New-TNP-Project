<?php require_once ROOT_PATH . '/includes/header.php'; ?>
<div class="content-header"><div><h1 class="page-title">Manage Companies</h1><p class="subtitle">Total: <?= $total ?> companies</p></div></div>

<div class="card mb-4"><div class="card-body"><form method="GET" class="row g-3 align-items-end">
    <div class="col-md-5"><label class="form-label">Search</label><input type="text" class="form-control" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Company name, email..."></div>
    <div class="col-md-4"><label class="form-label">Approval Status</label><select class="form-select" name="status"><option value="">All</option><option value="approved" <?= $status === 'approved' ? 'selected' : '' ?>>Approved</option><option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Pending</option></select></div>
    <div class="col-md-3"><button type="submit" class="btn btn-primary w-100"><i class="fas fa-search me-1"></i>Search</button></div>
</form></div></div>

<div class="card"><div class="card-body p-0"><div class="table-responsive">
    <table class="table mb-0">
        <thead><tr><th>Company</th><th>Industry</th><th>Contact</th><th>Type</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
            <?php foreach ($companies as $c): ?>
            <tr>
                <td><div class="user-cell"><img src="<?= $c['logo'] ? uploadUrl('company/' . $c['logo']) : asset('images/default-avatar.png') ?>" alt="" class="user-avatar" style="border-radius:var(--radius-sm)" onerror="this.src='<?= asset('images/default-avatar.png') ?>'"><div class="user-info"><div class="name"><?= htmlspecialchars($c['company_name']) ?></div><div class="email"><?= htmlspecialchars($c['email']) ?></div></div></div></td>
                <td><small><?= htmlspecialchars($c['industry'] ?? 'N/A') ?></small></td>
                <td><small><?= htmlspecialchars($c['contact_person'] ?? 'N/A') ?><br><?= htmlspecialchars($c['contact_phone'] ?? '') ?></small></td>
                <td><span class="badge bg-light text-dark"><?= ucfirst($c['company_type'] ?? 'other') ?></span></td>
                <td><?php if ($c['is_approved']): ?><span class="badge bg-success">Approved</span><?php else: ?><span class="badge bg-warning">Pending</span><?php endif; ?></td>
                <td>
                    <div class="d-flex gap-1">
                        <?php if (!$c['is_approved']): ?>
                        <a href="<?= url('/admin/approve-company/' . $c['id']) ?>" class="btn btn-sm btn-success" title="Approve"><i class="fas fa-check"></i></a>
                        <?php else: ?>
                        <a href="<?= url('/admin/reject-company/' . $c['id']) ?>" class="btn btn-sm btn-warning" title="Revoke" data-confirm="Revoke approval?"><i class="fas fa-ban"></i></a>
                        <?php endif; ?>
                        <a href="<?= url('/admin/delete-company/' . $c['id']) ?>" class="btn btn-sm btn-danger" data-confirm="Delete company and all its data?"><i class="fas fa-trash"></i></a>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div></div></div>
<div class="mt-4"><?= renderPagination($pagination, url('/admin/companies')) ?></div>
<?php require_once ROOT_PATH . '/includes/footer.php'; ?>
