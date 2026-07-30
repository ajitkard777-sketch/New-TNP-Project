<?php require_once ROOT_PATH . '/includes/header.php'; ?>

<div class="content-header">
    <div>
        <h1 class="page-title">Higher Studies</h1>
        <p class="subtitle">Explore universities, entrance exams, scholarships and manage your applications</p>
    </div>
</div>

<?php if (!empty($myApplications)): ?>
<div class="row g-3 mb-4">
    <?php
    $pending  = count(array_filter($myApplications, fn($a) => $a['status'] === 'pending'));
    $approved = count(array_filter($myApplications, fn($a) => $a['status'] === 'approved'));
    $rejected = count(array_filter($myApplications, fn($a) => $a['status'] === 'rejected'));
    ?>
    <div class="col-auto"><span class="badge bg-warning text-dark px-3 py-2" style="font-size:.85rem"><i class="fas fa-clock me-1"></i><?= $pending ?> Pending</span></div>
    <div class="col-auto"><span class="badge bg-success px-3 py-2" style="font-size:.85rem"><i class="fas fa-check-circle me-1"></i><?= $approved ?> Approved</span></div>
    <div class="col-auto"><span class="badge bg-danger px-3 py-2" style="font-size:.85rem"><i class="fas fa-times-circle me-1"></i><?= $rejected ?> Rejected</span></div>
</div>
<?php endif; ?>

<?php
$isMyAppsTab = ($_GET['tab'] ?? '') === 'myapps';
?>
<ul class="nav nav-tabs mb-4" id="hsTab">
    <li class="nav-item"><a class="nav-link <?= !$isMyAppsTab ? 'active' : '' ?>" data-bs-toggle="tab" href="#universities"><i class="fas fa-university me-1"></i>Universities</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#exams"><i class="fas fa-file-alt me-1"></i>Entrance Exams</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#scholarships"><i class="fas fa-award me-1"></i>Scholarships</a></li>
    <li class="nav-item">
        <a class="nav-link <?= $isMyAppsTab ? 'active' : '' ?>" data-bs-toggle="tab" href="#myapps">
            <i class="fas fa-paper-plane me-1"></i>My Applications
            <?php if (!empty($myApplications)): ?><span class="badge bg-primary ms-1"><?= count($myApplications) ?></span><?php endif; ?>
        </a>
    </li>
</ul>

<div class="tab-content">
    <!-- Universities Tab -->
    <div class="tab-pane fade <?= !$isMyAppsTab ? 'show active' : '' ?>" id="universities">
        <?php if (empty($universities)): ?>
        <div class="card"><div class="card-body empty-state"><i class="fas fa-university"></i><h5>No Universities Available</h5><p>Check back later for university listings.</p></div></div>
        <?php else: ?>
        <div class="row mb-3 align-items-center">
            <div class="col-md-6 ms-auto">
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" id="univSearchInput" class="form-control" placeholder="Search university by name, location, country...">
                </div>
            </div>
        </div>
        <div class="row g-4" id="universitiesContainer">
            <?php foreach ($universities as $uni):
                $alreadyApplied = in_array($uni['id'], $appliedUnivIds); ?>
            <div class="col-lg-6">
                <div class="card hover-scale h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <h5 class="fw-bold mb-1"><?= htmlspecialchars($uni['name']) ?></h5>
                                <small class="text-muted"><i class="fas fa-map-marker-alt me-1 text-danger"></i><?= htmlspecialchars(trim(($uni['city'] ? $uni['city'].', ' : '') . ($uni['country'] ?? ''), ', ')) ?></small>
                            </div>
                            <?php if ($uni['ranking']): ?><span class="badge bg-primary">#<?= $uni['ranking'] ?> Ranked</span><?php endif; ?>
                        </div>
                        <?php if ($uni['description']): ?><p class="text-muted mb-3" style="font-size:.84rem"><?= htmlspecialchars(mb_substr($uni['description'],0,120)) ?>...</p><?php endif; ?>
                        <div class="d-flex gap-2 flex-wrap mb-3" style="font-size:.8rem">
                            <span class="badge bg-light text-dark"><i class="fas fa-book me-1"></i><?= $uni['course_count'] ?? 0 ?> Courses</span>
                            <?php if ($uni['admission_deadline']): ?><span class="badge bg-light text-dark"><i class="fas fa-calendar me-1"></i>Deadline: <?= formatDate($uni['admission_deadline']) ?></span><?php endif; ?>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <?php if ($uni['website']): ?><a href="<?= htmlspecialchars($uni['website']) ?>" target="_blank" class="btn btn-sm btn-outline-primary"><i class="fas fa-external-link-alt me-1"></i>Website</a><?php else: ?><span></span><?php endif; ?>
                            <?php if ($alreadyApplied): ?>
                            <span class="badge bg-success px-3 py-2"><i class="fas fa-check me-1"></i>Applied</span>
                            <?php else: ?>
                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#applyModal<?= $uni['id'] ?>"><i class="fas fa-paper-plane me-1"></i>Apply Now</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <?php if (!$alreadyApplied): ?>
            <div class="modal fade" id="applyModal<?= $uni['id'] ?>" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title"><i class="fas fa-graduation-cap me-2"></i>Apply — <?= htmlspecialchars($uni['name']) ?></h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <form action="<?= url('/student/apply-higher-study') ?>" method="POST">
                            <?= CsrfMiddleware::tokenField() ?>
                            <input type="hidden" name="university_id" value="<?= $uni['id'] ?>">
                            <div class="modal-body">
                                <div class="alert alert-info py-2 mb-3" style="font-size:.85rem"><i class="fas fa-info-circle me-1"></i>Applying to <strong><?= htmlspecialchars($uni['name']) ?></strong> — <?= htmlspecialchars(($uni['city'] ?? '') . ', ' . ($uni['country'] ?? '')) ?></div>
                                <div class="row g-3">
                                    <?php if (!empty($coursesByUniversity[$uni['id']])): ?>
                                    <div class="col-md-6">
                                        <label class="form-label">Program / Course</label>
                                        <select class="form-select" name="course_id" id="courseSelect<?= $uni['id'] ?>" onchange="updateCourseName(this,<?= $uni['id'] ?>)">
                                            <option value="">— Select a Course —</option>
                                            <?php foreach ($coursesByUniversity[$uni['id']] as $c): ?>
                                            <option value="<?= $c['id'] ?>" data-name="<?= htmlspecialchars($c['name']) ?>"><?= htmlspecialchars($c['name']) ?> <?= $c['degree_type'] ? '('.$c['degree_type'].')' : '' ?> <?= $c['duration'] ? '· '.$c['duration'] : '' ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <input type="hidden" name="course_name" id="courseNameHidden<?= $uni['id'] ?>">
                                    </div>
                                    <?php else: ?>
                                    <div class="col-md-6">
                                        <label class="form-label">Course / Program Name</label>
                                        <input type="text" class="form-control" name="course_name" placeholder="e.g. M.Tech Computer Science">
                                        <input type="hidden" name="course_id" value="">
                                    </div>
                                    <?php endif; ?>
                                    <div class="col-md-6">
                                        <label class="form-label">Entrance Exam</label>
                                        <input type="text" class="form-control" name="entrance_exam" placeholder="e.g. GATE, GRE, CAT">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Exam Score / Rank</label>
                                        <input type="text" class="form-control" name="exam_score" placeholder="e.g. 720, AIR 450">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Application Date</label>
                                        <input type="date" class="form-control" name="application_date" value="<?= date('Y-m-d') ?>" max="<?= date('Y-m-d') ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Expected Joining Date</label>
                                        <input type="date" class="form-control" name="expected_joining_date" min="<?= date('Y-m-d') ?>">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Remarks / Notes</label>
                                        <textarea class="form-control" name="notes" rows="3" placeholder="Any additional information, scholarship interest..."></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane me-1"></i>Submit Application</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Entrance Exams Tab -->
    <div class="tab-pane fade" id="exams">
        <?php if (empty($exams)): ?>
        <div class="card"><div class="card-body empty-state"><i class="fas fa-file-alt"></i><h5>No Exams Listed</h5></div></div>
        <?php else: ?>
        <div class="card"><div class="card-body p-0"><div class="table-responsive">
            <table class="table mb-0"><thead><tr><th>Exam</th><th>Conducting Body</th><th>Exam Date</th><th>Reg. Deadline</th><th>Link</th></tr></thead><tbody>
                <?php foreach ($exams as $exam): ?>
                <tr>
                    <td><div class="fw-bold"><?= htmlspecialchars($exam['name']) ?></div><small class="text-muted"><?= htmlspecialchars($exam['full_name'] ?? '') ?></small></td>
                    <td><?= htmlspecialchars($exam['conducting_body'] ?? 'N/A') ?></td>
                    <td><?= $exam['exam_date'] ? formatDate($exam['exam_date']) : '<span class="text-muted">TBA</span>' ?></td>
                    <td><?= $exam['registration_deadline'] ? formatDate($exam['registration_deadline']) : '<span class="text-muted">TBA</span>' ?></td>
                    <td><?php if ($exam['website']): ?><a href="<?= htmlspecialchars($exam['website']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">Visit</a><?php endif; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody></table>
        </div></div></div>
        <?php endif; ?>
    </div>

    <!-- Scholarships Tab -->
    <div class="tab-pane fade" id="scholarships">
        <?php if (empty($scholarships)): ?>
        <div class="card"><div class="card-body empty-state"><i class="fas fa-award"></i><h5>No Scholarships Listed</h5></div></div>
        <?php else: ?>
        <div class="row g-4">
            <?php foreach ($scholarships as $sch): ?>
            <div class="col-lg-6">
                <div class="card hover-scale h-100"><div class="card-body">
                    <h5 class="fw-bold mb-1"><?= htmlspecialchars($sch['name']) ?></h5>
                    <small class="text-primary"><?= htmlspecialchars($sch['provider'] ?? '') ?></small>
                    <div class="mt-2 mb-2"><span class="badge bg-success"><?= formatCurrency($sch['amount'], $sch['currency'] ?? 'INR') ?></span> <span class="badge bg-light text-dark"><?= ucfirst($sch['type'] ?? '') ?></span></div>
                    <?php if ($sch['eligibility']): ?><p style="font-size:.82rem" class="text-muted"><?= htmlspecialchars(mb_substr($sch['eligibility'],0,120)) ?></p><?php endif; ?>
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <small class="text-muted"><i class="fas fa-calendar me-1"></i>Deadline: <?= $sch['application_deadline'] ? formatDate($sch['application_deadline']) : 'TBA' ?></small>
                        <?php if ($sch['website']): ?><a href="<?= htmlspecialchars($sch['website']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">Apply</a><?php endif; ?>
                    </div>
                </div></div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- My Applications Tab -->
    <div class="tab-pane fade <?= $isMyAppsTab ? 'show active' : '' ?>" id="myapps">
        <?php if (empty($myApplications)): ?>
        <div class="card"><div class="card-body empty-state"><i class="fas fa-graduation-cap"></i><h5>No Applications Yet</h5><p>Browse universities above and click <strong>Apply Now</strong> to start.</p></div></div>
        <?php else: ?>
        <div class="card"><div class="card-body p-0"><div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead><tr><th>University</th><th>Course</th><th>Entrance Exam</th><th>App. Date</th><th>Status</th><th>Admin Remarks</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php foreach ($myApplications as $ma): ?>
                    <tr>
                        <td>
                            <div class="fw-bold"><?= htmlspecialchars($ma['university_name']) ?></div>
                            <small class="text-muted"><?= htmlspecialchars(($ma['city'] ?? '') . (($ma['city'] ?? '') ? ', ' : '') . ($ma['country'] ?? '')) ?></small>
                        </td>
                        <td><?= htmlspecialchars($ma['course_name'] ?? '—') ?></td>
                        <td>
                            <?php if ($ma['entrance_exam']): ?>
                            <div><?= htmlspecialchars($ma['entrance_exam']) ?></div>
                            <?php if ($ma['exam_score']): ?><small class="text-muted">Score: <?= htmlspecialchars($ma['exam_score']) ?></small><?php endif; ?>
                            <?php else: ?><span class="text-muted">—</span><?php endif; ?>
                        </td>
                        <td>
                            <?= $ma['application_date'] ? formatDate($ma['application_date']) : '<span class="text-muted">—</span>' ?>
                            <?php if ($ma['expected_joining_date']): ?><br><small class="text-muted">Join: <?= formatDate($ma['expected_joining_date']) ?></small><?php endif; ?>
                        </td>
                        <td>
                            <?php $badge = ['pending'=>'bg-warning text-dark','approved'=>'bg-success','rejected'=>'bg-danger','withdrawn'=>'bg-secondary'][$ma['status']] ?? 'bg-secondary'; ?>
                            <span class="badge <?= $badge ?>"><?= ucfirst($ma['status']) ?></span>
                        </td>
                        <td><small class="text-muted"><?= htmlspecialchars($ma['admin_remarks'] ?? '—') ?></small></td>
                        <td>
                            <?php if ($ma['status'] === 'pending'): ?>
                            <div class="d-flex gap-1 flex-nowrap">
                                <button class="btn btn-xs btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editModal<?= $ma['id'] ?>" title="Edit"><i class="fas fa-edit"></i></button>
                                <a href="<?= url('/student/withdraw-higher-study/' . $ma['id']) ?>" class="btn btn-xs btn-outline-danger" data-confirm="Withdraw this application?" title="Withdraw"><i class="fas fa-times"></i></a>
                            </div>
                            <?php else: ?><span class="text-muted small">—</span><?php endif; ?>
                        </td>
                    </tr>

                    <?php if ($ma['status'] === 'pending'): ?>
                    <div class="modal fade" id="editModal<?= $ma['id'] ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header"><h5 class="modal-title"><i class="fas fa-edit me-2"></i>Edit — <?= htmlspecialchars($ma['university_name']) ?></h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                <form action="<?= url('/student/edit-higher-study/' . $ma['id']) ?>" method="POST">
                                    <?= CsrfMiddleware::tokenField() ?>
                                    <div class="modal-body"><div class="row g-3">
                                        <div class="col-12"><label class="form-label">Course Name</label><input type="text" class="form-control" name="course_name" value="<?= htmlspecialchars($ma['course_name'] ?? '') ?>"><input type="hidden" name="course_id" value="<?= $ma['course_id'] ?? '' ?>"></div>
                                        <div class="col-md-6"><label class="form-label">Entrance Exam</label><input type="text" class="form-control" name="entrance_exam" value="<?= htmlspecialchars($ma['entrance_exam'] ?? '') ?>"></div>
                                        <div class="col-md-6"><label class="form-label">Exam Score / Rank</label><input type="text" class="form-control" name="exam_score" value="<?= htmlspecialchars($ma['exam_score'] ?? '') ?>"></div>
                                        <div class="col-md-6"><label class="form-label">Application Date</label><input type="date" class="form-control" name="application_date" value="<?= $ma['application_date'] ?? '' ?>" max="<?= date('Y-m-d') ?>"></div>
                                        <div class="col-md-6"><label class="form-label">Expected Joining Date</label><input type="date" class="form-control" name="expected_joining_date" value="<?= $ma['expected_joining_date'] ?? '' ?>" min="<?= date('Y-m-d') ?>"></div>
                                        <div class="col-12"><label class="form-label">Remarks</label><textarea class="form-control" name="notes" rows="3"><?= htmlspecialchars($ma['notes'] ?? '') ?></textarea></div>
                                    </div></div>
                                    <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Save Changes</button></div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div></div></div>
        <?php endif; ?>
    </div>
</div>

<script>
function updateCourseName(select, univId) {
    const sel = select.options[select.selectedIndex];
    const hidden = document.getElementById('courseNameHidden' + univId);
    if (hidden) hidden.value = sel ? (sel.dataset.name || '') : '';
}

document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('univSearchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const term = this.value.toLowerCase().trim();
            const cards = document.querySelectorAll('#universitiesContainer .col-lg-6');
            cards.forEach(card => {
                const text = card.textContent.toLowerCase();
                card.style.display = text.includes(term) ? '' : 'none';
            });
        });
    }

    const urlParams = new URLSearchParams(window.location.search);
    const tabParam = urlParams.get('tab');
    if (tabParam === 'myapps' || window.location.hash === '#myapps') {
        const myAppsTabBtn = document.querySelector('a[href="#myapps"]');
        if (myAppsTabBtn) {
            const tab = new bootstrap.Tab(myAppsTabBtn);
            tab.show();
        }
    }
});
</script>
<?php require_once ROOT_PATH . '/includes/footer.php'; ?>
