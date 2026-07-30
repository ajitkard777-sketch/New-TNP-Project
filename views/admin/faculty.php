<?php require_once ROOT_PATH . '/includes/header.php'; ?>
<div class="content-header"><div><h1 class="page-title">Manage Faculty</h1><p class="subtitle"><?= count($faculty) ?> faculty members</p></div><button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addFacultyModal"><i class="fas fa-plus me-1"></i>Add Faculty</button></div>

<div class="card"><div class="card-body p-0"><div class="table-responsive">
    <table class="table mb-0"><thead><tr><th>Name</th><th>Email</th><th>Department</th><th>Designation</th><th>Specialization</th><th>Status</th><th>Action</th></tr></thead><tbody>
        <?php foreach ($faculty as $f): ?>
        <tr><td class="fw-bold"><?= htmlspecialchars($f['name']) ?></td><td><small><?= htmlspecialchars($f['email'] ?? '') ?></small></td><td><?= htmlspecialchars($f['department'] ?? '') ?></td><td><small><?= htmlspecialchars($f['designation'] ?? '') ?></small></td><td><small><?= htmlspecialchars($f['specialization'] ?? '') ?></small></td><td><span class="badge bg-<?= $f['status'] === 'active' ? 'success' : 'secondary' ?>"><?= ucfirst($f['status']) ?></span></td>
        <td><a href="<?= url('/admin/delete-faculty/' . $f['id']) ?>" class="btn btn-sm btn-danger" data-confirm="Delete faculty?"><i class="fas fa-trash"></i></a></td></tr>
        <?php endforeach; ?>
    </tbody></table>
</div></div></div>

<div class="modal fade" id="addFacultyModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title">Add Faculty</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <form action="<?= url('/admin/create-faculty') ?>" method="POST"><?= CsrfMiddleware::tokenField() ?>
        <div class="modal-body"><div class="row g-3">
            <div class="col-12"><label class="form-label">Name *</label><input type="text" class="form-control" name="name" required></div>
            <div class="col-md-6"><label class="form-label">Email</label><input type="email" class="form-control" name="email"></div>
            <div class="col-md-6"><label class="form-label">Phone</label><input type="text" class="form-control" name="phone"></div>
            <div class="col-md-6"><label class="form-label">Department</label><input type="text" class="form-control" name="department"></div>
            <div class="col-md-6"><label class="form-label">Designation</label><input type="text" class="form-control" name="designation"></div>
            <div class="col-12"><label class="form-label">Specialization</label><input type="text" class="form-control" name="specialization"></div>
        </div></div>
        <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Add Faculty</button></div>
    </form>
</div></div></div>
<?php require_once ROOT_PATH . '/includes/footer.php'; ?>
