<?php require_once ROOT_PATH . '/includes/header.php'; ?>
<div class="content-header"><div><h1 class="page-title">Higher Studies Management</h1><p class="subtitle">Manage universities, exams, and scholarships</p></div></div>

<ul class="nav nav-tabs mb-4">
    <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#univ">Universities (<?= count($universities) ?>)</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#exams">Exams (<?= count($exams) ?>)</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#schol">Scholarships (<?= count($scholarships) ?>)</a></li>
</ul>

<div class="tab-content">
    <div class="tab-pane fade show active" id="univ">
        <div class="d-flex justify-content-end mb-3"><button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addUnivModal"><i class="fas fa-plus me-1"></i>Add University</button></div>
        <div class="card"><div class="card-body p-0"><div class="table-responsive"><table class="table mb-0">
            <thead><tr><th>University</th><th>Location</th><th>Ranking</th><th>Deadline</th><th>Status</th></tr></thead><tbody>
            <?php foreach ($universities as $u): ?>
            <tr><td class="fw-bold"><?= htmlspecialchars($u['name']) ?></td><td><small><?= htmlspecialchars($u['city'] . ', ' . $u['country']) ?></small></td><td><?= $u['ranking'] ? '#' . $u['ranking'] : '-' ?></td><td><small><?= $u['admission_deadline'] ? formatDate($u['admission_deadline']) : 'TBA' ?></small></td><td><span class="badge bg-success"><?= ucfirst($u['status']) ?></span></td></tr>
            <?php endforeach; ?>
        </tbody></table></div></div></div>
    </div>
    <div class="tab-pane fade" id="exams">
        <div class="card"><div class="card-body p-0"><div class="table-responsive"><table class="table mb-0">
            <thead><tr><th>Exam</th><th>Conducting Body</th><th>Date</th><th>Deadline</th></tr></thead><tbody>
            <?php foreach ($exams as $e): ?>
            <tr><td class="fw-bold"><?= htmlspecialchars($e['name']) ?></td><td><small><?= htmlspecialchars($e['conducting_body'] ?? '') ?></small></td><td><small><?= $e['exam_date'] ? formatDate($e['exam_date']) : 'TBA' ?></small></td><td><small><?= $e['registration_deadline'] ? formatDate($e['registration_deadline']) : 'TBA' ?></small></td></tr>
            <?php endforeach; ?>
        </tbody></table></div></div></div>
    </div>
    <div class="tab-pane fade" id="schol">
        <div class="card"><div class="card-body p-0"><div class="table-responsive"><table class="table mb-0">
            <thead><tr><th>Scholarship</th><th>Provider</th><th>Amount</th><th>Deadline</th></tr></thead><tbody>
            <?php foreach ($scholarships as $s): ?>
            <tr><td class="fw-bold"><?= htmlspecialchars($s['name']) ?></td><td><small><?= htmlspecialchars($s['provider'] ?? '') ?></small></td><td class="text-success fw-bold"><?= formatCurrency($s['amount'], $s['currency'] ?? 'INR') ?></td><td><small><?= $s['application_deadline'] ? formatDate($s['application_deadline']) : 'TBA' ?></small></td></tr>
            <?php endforeach; ?>
        </tbody></table></div></div></div>
    </div>
</div>

<!-- Add University Modal -->
<div class="modal fade" id="addUnivModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title">Add University</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <form action="<?= url('/admin/create-university') ?>" method="POST"><?= CsrfMiddleware::tokenField() ?>
        <div class="modal-body"><div class="row g-3">
            <div class="col-12"><label class="form-label">Name *</label><input type="text" class="form-control" name="name" required></div>
            <div class="col-md-6"><label class="form-label">City</label><input type="text" class="form-control" name="city"></div>
            <div class="col-md-6"><label class="form-label">Country</label><input type="text" class="form-control" name="country" value="India"></div>
            <div class="col-md-6"><label class="form-label">Ranking</label><input type="number" class="form-control" name="ranking" min="1"></div>
            <div class="col-md-6"><label class="form-label">Deadline</label><input type="date" class="form-control" name="admission_deadline"></div>
            <div class="col-12"><label class="form-label">Website</label><input type="url" class="form-control" name="website"></div>
            <div class="col-12"><label class="form-label">Description</label><textarea class="form-control" name="description" rows="2"></textarea></div>
        </div></div>
        <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Add</button></div>
    </form>
</div></div></div>
<?php require_once ROOT_PATH . '/includes/footer.php'; ?>
