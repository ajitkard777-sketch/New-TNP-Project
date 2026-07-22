<?php require_once ROOT_PATH . '/includes/header.php'; ?>
<div class="content-header"><div><h1 class="page-title">Manage Trainings</h1><p class="subtitle"><?= count($trainings) ?> trainings</p></div><button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addTrainingModal"><i class="fas fa-plus me-1"></i>Add Training</button></div>

<div class="card"><div class="card-body p-0"><div class="table-responsive">
    <table class="table mb-0">
        <thead><tr><th>Training</th><th>Type</th><th>Trainer</th><th>Dates</th><th>Mode</th><th>Capacity</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
            <?php foreach ($trainings as $t): ?>
            <tr>
                <td><div class="fw-bold"><?= htmlspecialchars($t['title']) ?></div><small class="text-muted"><?= htmlspecialchars($t['venue'] ?? '') ?></small></td>
                <td><span class="badge bg-<?= $t['training_type'] === 'technical' ? 'primary' : ($t['training_type'] === 'soft-skills' ? 'success' : 'warning') ?>"><?= ucfirst($t['training_type']) ?></span></td>
                <td><small><?= htmlspecialchars($t['trainer_name'] ?? $t['faculty_name'] ?? 'TBA') ?></small></td>
                <td><small><?= formatDate($t['start_date']) ?> - <?= formatDate($t['end_date']) ?></small></td>
                <td><span class="badge bg-<?= $t['mode'] === 'online' ? 'info' : 'secondary' ?>"><?= ucfirst($t['mode']) ?></span></td>
                <td><?= $t['registered_count'] ?>/<?= $t['capacity'] ?></td>
                <td><span class="badge <?= getStatusBadgeClass($t['status']) ?>"><?= ucfirst($t['status']) ?></span></td>
                <td><a href="<?= url('/admin/delete-training/' . $t['id']) ?>" class="btn btn-sm btn-danger" data-confirm="Delete?"><i class="fas fa-trash"></i></a></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div></div></div>

<!-- Add Training Modal -->
<div class="modal fade" id="addTrainingModal" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title">Add Training</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <form action="<?= url('/admin/create-training') ?>" method="POST"><?= CsrfMiddleware::tokenField() ?>
        <div class="modal-body"><div class="row g-3">
            <div class="col-md-8"><label class="form-label">Title *</label><input type="text" class="form-control" name="title" required></div>
            <div class="col-md-4"><label class="form-label">Type</label><select class="form-select" name="training_type"><option value="technical">Technical</option><option value="soft-skills">Soft Skills</option><option value="aptitude">Aptitude</option><option value="workshop">Workshop</option></select></div>
            <div class="col-12"><label class="form-label">Description</label><textarea class="form-control" name="description" rows="2"></textarea></div>
            <div class="col-md-4"><label class="form-label">Mode</label><select class="form-select" name="mode"><option value="offline">Offline</option><option value="online">Online</option><option value="hybrid">Hybrid</option></select></div>
            <div class="col-md-4"><label class="form-label">Venue</label><input type="text" class="form-control" name="venue"></div>
            <div class="col-md-4"><label class="form-label">Capacity</label><input type="number" class="form-control" name="capacity" value="50" min="1"></div>
            <div class="col-md-6"><label class="form-label">Trainer Name</label><input type="text" class="form-control" name="trainer_name"></div>
            <div class="col-md-6"><label class="form-label">Faculty</label><select class="form-select" name="faculty_id"><option value="">None</option><?php foreach ($faculty as $f): ?><option value="<?= $f['id'] ?>"><?= htmlspecialchars($f['name']) ?></option><?php endforeach; ?></select></div>
            <div class="col-md-3"><label class="form-label">Start Date *</label><input type="date" class="form-control" name="start_date" required></div>
            <div class="col-md-3"><label class="form-label">End Date *</label><input type="date" class="form-control" name="end_date" required></div>
            <div class="col-md-3"><label class="form-label">Start Time</label><input type="time" class="form-control" name="start_time"></div>
            <div class="col-md-3"><label class="form-label">Status</label><select class="form-select" name="status"><option value="upcoming">Upcoming</option><option value="ongoing">Ongoing</option></select></div>
        </div></div>
        <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Create</button></div>
    </form>
</div></div></div>
<?php require_once ROOT_PATH . '/includes/footer.php'; ?>
