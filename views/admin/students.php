<?php require_once ROOT_PATH . '/includes/header.php'; ?>
<div class="content-header"><div><h1 class="page-title">Manage Students</h1><p class="subtitle">Total: <?= $total ?> students</p></div></div>

<div class="card mb-4"><div class="card-body">
    <form method="GET" class="row g-3 align-items-end">
        <div class="col-md-4"><label class="form-label">Search</label><input type="text" class="form-control" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Name, email, enrollment..."></div>
        <div class="col-md-3"><label class="form-label">Branch</label><select class="form-select" name="branch"><option value="">All Branches</option><?php foreach (BRANCHES as $b): ?><option value="<?= $b ?>" <?= $branch === $b ? 'selected' : '' ?>><?= $b ?></option><?php endforeach; ?></select></div>
        <div class="col-md-3"><label class="form-label">Status</label><select class="form-select" name="status"><option value="">All</option><option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Active</option><option value="inactive" <?= $status === 'inactive' ? 'selected' : '' ?>>Inactive</option></select></div>
        <div class="col-md-2"><button type="submit" class="btn btn-primary w-100"><i class="fas fa-search me-1"></i>Search</button></div>
    </form>
</div></div>

<div class="card"><div class="card-body p-0"><div class="table-responsive">
    <table class="table mb-0" id="studentsTable">
        <thead><tr><th>Student</th><th>Email</th><th>Branch</th><th>CGPA</th><th>Status</th><th>Placed</th><th>Actions</th></tr></thead>
        <tbody>
            <?php foreach ($students as $s): ?>
            <tr>
                <td><div class="user-cell"><img src="<?= $s['profile_photo'] ? uploadUrl('profile_photos/' . $s['profile_photo']) : asset('images/default-avatar.png') ?>" alt="" class="user-avatar" onerror="this.src='<?= asset('images/default-avatar.png') ?>'"><div class="user-info"><div class="name"><?= htmlspecialchars($s['first_name'] . ' ' . $s['last_name']) ?></div><div class="email"><?= htmlspecialchars($s['enrollment_no'] ?? '') ?></div></div></div></td>
                <td><small><?= htmlspecialchars($s['email']) ?></small></td>
                <td><span class="badge bg-light text-dark"><?= htmlspecialchars($s['branch'] ?? '') ?></span></td>
                <td><span class="fw-bold text-primary"><?= $s['cgpa'] ?? '-' ?></span></td>
                <td><span class="badge <?= getStatusBadgeClass($s['user_status']) ?>"><?= ucfirst($s['user_status']) ?></span></td>
                <td><?= $s['is_placed'] ? '<span class="badge bg-success"><i class="fas fa-check me-1"></i>' . htmlspecialchars($s['placed_company'] ?? 'Yes') . '</span>' : '<span class="text-muted">No</span>' ?></td>
                <td>
                    <div class="d-flex gap-1">
                        <a href="<?= url('/admin/view-student/' . $s['id']) ?>" class="btn btn-sm btn-icon btn-light" title="View"><i class="fas fa-eye"></i></a>
                        <?php if (!$s['is_placed']): ?>
                        <button class="btn btn-sm btn-icon btn-success" title="Mark Placed" data-bs-toggle="modal" data-bs-target="#placeModal" onclick="setPlaceStudent(<?= $s['id'] ?>,'<?= htmlspecialchars($s['first_name'] . ' ' . $s['last_name']) ?>')"><i class="fas fa-trophy"></i></button>
                        <?php endif; ?>
                        <a href="<?= url('/admin/delete-student/' . $s['id']) ?>" class="btn btn-sm btn-icon btn-danger" data-confirm="Delete this student permanently?"><i class="fas fa-trash"></i></a>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div></div></div>

<div class="mt-4"><?= renderPagination($pagination, url('/admin/students')) ?></div>

<!-- Mark Placed Modal -->
<div class="modal fade" id="placeModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title">Mark as Placed</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <form id="placeForm" method="POST"><?= CsrfMiddleware::tokenField() ?>
        <div class="modal-body">
            <div class="alert alert-info py-2" style="font-size:0.85rem"><i class="fas fa-user me-1"></i><span id="placeStudentName"></span></div>
            <div class="mb-3"><label class="form-label">Company *</label><input type="text" class="form-control" name="placed_company" required></div>
            <div class="mb-3"><label class="form-label">Package (LPA) *</label><input type="number" class="form-control" name="placed_package" step="0.01" min="0" required></div>
            <div class="mb-3"><label class="form-label">Placement Date</label><input type="date" class="form-control" name="placed_date" value="<?= date('Y-m-d') ?>"></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-success">Mark Placed</button></div>
    </form>
</div></div></div>

<script>
function setPlaceStudent(id, name) {
    document.getElementById('placeForm').action = TPMS.baseUrl + '/admin/mark-placed/' + id;
    document.getElementById('placeStudentName').textContent = name;
}
$(function() { if ($.fn.DataTable) { $('#studentsTable').DataTable({ paging: false, searching: false, info: false, order: [[2, 'asc']] }); } });
</script>
<?php require_once ROOT_PATH . '/includes/footer.php'; ?>
