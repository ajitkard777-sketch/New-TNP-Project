<?php require_once ROOT_PATH . '/includes/header.php'; ?>
<div class="content-header"><div><h1 class="page-title">Higher Studies</h1><p class="subtitle">Explore universities, exams, and scholarships</p></div></div>

<ul class="nav nav-tabs mb-4">
    <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#universities">Universities</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#exams">Entrance Exams</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#scholarships">Scholarships</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#myapps">My Applications</a></li>
</ul>

<div class="tab-content">
    <!-- Universities -->
    <div class="tab-pane fade show active" id="universities">
        <div class="row g-4">
            <?php foreach ($universities as $uni): ?>
            <div class="col-lg-6">
                <div class="card hover-scale">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div><h5 class="fw-bold mb-1"><?= htmlspecialchars($uni['name']) ?></h5><small class="text-muted"><i class="fas fa-map-marker-alt me-1"></i><?= htmlspecialchars($uni['city'] . ', ' . $uni['country']) ?></small></div>
                            <?php if ($uni['ranking']): ?><span class="badge bg-primary">#<?= $uni['ranking'] ?></span><?php endif; ?>
                        </div>
                        <?php if ($uni['description']): ?><p class="text-muted mt-2 mb-2" style="font-size:0.85rem"><?= htmlspecialchars(truncateText($uni['description'], 100)) ?></p><?php endif; ?>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <small class="text-muted"><?= $uni['course_count'] ?> courses | Deadline: <?= $uni['admission_deadline'] ? formatDate($uni['admission_deadline']) : 'TBA' ?></small>
                            <?php if ($uni['website']): ?><a href="<?= htmlspecialchars($uni['website']) ?>" target="_blank" class="btn btn-sm btn-outline-primary"><i class="fas fa-external-link-alt me-1"></i>Visit</a><?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Entrance Exams -->
    <div class="tab-pane fade" id="exams">
        <div class="card"><div class="card-body p-0"><div class="table-responsive">
            <table class="table mb-0"><thead><tr><th>Exam</th><th>Conducting Body</th><th>Exam Date</th><th>Registration Deadline</th><th>Link</th></tr></thead>
            <tbody>
                <?php foreach ($exams as $exam): ?>
                <tr>
                    <td><div class="fw-bold"><?= htmlspecialchars($exam['name']) ?></div><small class="text-muted"><?= htmlspecialchars($exam['full_name'] ?? '') ?></small></td>
                    <td><?= htmlspecialchars($exam['conducting_body'] ?? 'N/A') ?></td>
                    <td><?= $exam['exam_date'] ? formatDate($exam['exam_date']) : 'TBA' ?></td>
                    <td><?= $exam['registration_deadline'] ? formatDate($exam['registration_deadline']) : 'TBA' ?></td>
                    <td><?php if ($exam['website']): ?><a href="<?= htmlspecialchars($exam['website']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">Visit</a><?php endif; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody></table>
        </div></div></div>
    </div>

    <!-- Scholarships -->
    <div class="tab-pane fade" id="scholarships">
        <div class="row g-4">
            <?php foreach ($scholarships as $sch): ?>
            <div class="col-lg-6">
                <div class="card hover-scale">
                    <div class="card-body">
                        <h5 class="fw-bold mb-1"><?= htmlspecialchars($sch['name']) ?></h5>
                        <small class="text-primary"><?= htmlspecialchars($sch['provider'] ?? '') ?></small>
                        <div class="mt-2 mb-2"><span class="badge bg-success"><?= formatCurrency($sch['amount'], $sch['currency']) ?></span> <span class="badge bg-light text-dark"><?= ucfirst($sch['type']) ?></span></div>
                        <?php if ($sch['eligibility']): ?><p style="font-size:0.82rem" class="text-muted"><?= htmlspecialchars(truncateText($sch['eligibility'], 100)) ?></p><?php endif; ?>
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">Deadline: <?= $sch['application_deadline'] ? formatDate($sch['application_deadline']) : 'TBA' ?></small>
                            <?php if ($sch['website']): ?><a href="<?= htmlspecialchars($sch['website']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">Apply</a><?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- My Applications -->
    <div class="tab-pane fade" id="myapps">
        <?php if (empty($myApplications)): ?>
        <div class="card"><div class="card-body empty-state"><i class="fas fa-graduation-cap"></i><h5>No Applications</h5><p>Register your interest in universities above.</p></div></div>
        <?php else: ?>
        <div class="card"><div class="card-body p-0"><div class="table-responsive">
            <table class="table mb-0"><thead><tr><th>University</th><th>Country</th><th>Course</th><th>Status</th><th>Date</th></tr></thead>
            <tbody>
                <?php foreach ($myApplications as $ma): ?>
                <tr><td class="fw-bold"><?= htmlspecialchars($ma['university_name']) ?></td><td><?= htmlspecialchars($ma['country']) ?></td><td><?= htmlspecialchars($ma['course_name'] ?? 'N/A') ?></td><td><span class="badge <?= getStatusBadgeClass($ma['status']) ?>"><?= ucfirst($ma['status']) ?></span></td><td><small><?= formatDate($ma['created_at']) ?></small></td></tr>
                <?php endforeach; ?>
            </tbody></table>
        </div></div></div>
        <?php endif; ?>
    </div>
</div>
<?php require_once ROOT_PATH . '/includes/footer.php'; ?>
