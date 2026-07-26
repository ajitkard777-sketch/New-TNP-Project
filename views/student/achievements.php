<?php require_once ROOT_PATH . '/includes/header.php'; ?>

<div class="content-header d-flex flex-wrap justify-content-between align-items-center gap-3">
    <div>
        <h1 class="page-title"><i class="fas fa-trophy text-warning me-2"></i>My Achievements &amp; Awards</h1>
        <p class="subtitle">Showcase your hackathons, coding competitions, sports, workshops, and extracurricular accolades.</p>
    </div>
    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addAchievementModal">
        <i class="fas fa-plus-circle me-1"></i> Add New Achievement
    </button>
</div>

<!-- Stats Bar -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-4">
        <div class="card border-0 shadow-sm gradient-primary text-white">
            <div class="card-body p-3 d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-white-50 small text-uppercase font-weight-bold">Total Added</div>
                    <h3 class="fw-bold mb-0"><?= $totalAchievements ?></h3>
                </div>
                <div class="bg-white-20 p-3 rounded-circle"><i class="fas fa-award fs-3"></i></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4">
        <div class="card border-0 shadow-sm gradient-success text-white">
            <div class="card-body p-3 d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-white-50 small text-uppercase font-weight-bold">Verified by T&amp;P</div>
                    <h3 class="fw-bold mb-0"><?= $verifiedCount ?></h3>
                </div>
                <div class="bg-white-20 p-3 rounded-circle"><i class="fas fa-check-circle fs-3"></i></div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="card border-0 shadow-sm gradient-warning text-white">
            <div class="card-body p-3 d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-white-50 small text-uppercase font-weight-bold">Pending Review</div>
                    <h3 class="fw-bold mb-0"><?= $pendingCount ?></h3>
                </div>
                <div class="bg-white-20 p-3 rounded-circle"><i class="fas fa-hourglass-half fs-3"></i></div>
            </div>
        </div>
    </div>
</div>

<!-- Filter & Search Controls -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-3">
        <form method="GET" action="<?= url('/student/achievements') ?>" class="row g-2 align-items-center">
            <div class="col-md-5">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-start-0" placeholder="Search by title, organizer..." value="<?= htmlspecialchars($search) ?>">
                </div>
            </div>
            <div class="col-md-4">
                <select name="category" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Categories</option>
                    <?php 
                    $categories = ['Hackathon', 'Coding Competition', 'Sports', 'Technical Event', 'Workshop', 'Seminar', 'Paper Presentation', 'Project Competition', 'Innovation', 'Others'];
                    foreach ($categories as $cat):
                    ?>
                    <option value="<?= $cat ?>" <?= $category === $cat ? 'selected' : '' ?>><?= $cat ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm w-100">Filter</button>
                <?php if ($search || $category): ?>
                <a href="<?= url('/student/achievements') ?>" class="btn btn-outline-secondary btn-sm">Clear</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<?php if (empty($achievements)): ?>
<div class="card border-0 shadow-sm py-5 text-center">
    <div class="card-body py-4">
        <i class="fas fa-trophy text-muted mb-3" style="font-size: 3.5rem;"></i>
        <h5>No Achievements Found</h5>
        <p class="text-muted small max-w-500 mx-auto">Upload your certificates and achievements to highlight them on your ATS resume and present them to recruiters.</p>
        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addAchievementModal">
            <i class="fas fa-plus-circle me-1"></i> Add Achievement
        </button>
    </div>
</div>
<?php else: ?>

<!-- ACHIEVEMENTS CARDS GRID -->
<div class="row g-4">
    <?php foreach ($achievements as $ach): 
        $badgeBg = 'bg-warning text-dark';
        if ($ach['status'] === 'verified') $badgeBg = 'bg-success';
        if ($ach['status'] === 'rejected') $badgeBg = 'bg-danger';
    ?>
    <div class="col-md-6 col-lg-4">
        <div class="card border-0 shadow-sm h-100 position-relative overflow-hidden card-hover">
            <?php if (!empty($ach['achievement_image'])): ?>
                <img src="<?= uploadUrl('achievements/' . $ach['achievement_image']) ?>" class="card-img-top" style="height:160px; object-fit:cover;" alt="Achievement Photo">
            <?php endif; ?>
            
            <div class="card-body p-4 d-flex flex-column justify-content-between">
                <div>
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="badge bg-primary-soft text-primary fw-semibold"><?= htmlspecialchars($ach['category']) ?></span>
                        <span class="badge <?= $badgeBg ?> text-capitalize"><?= htmlspecialchars($ach['status']) ?></span>
                    </div>

                    <h5 class="fw-bold text-dark mb-1"><?= htmlspecialchars($ach['title']) ?></h5>
                    
                    <?php if (!empty($ach['organizer'])): ?>
                    <div class="small text-muted mb-2"><i class="fas fa-building me-1 text-secondary"></i><?= htmlspecialchars($ach['organizer']) ?></div>
                    <?php endif; ?>

                    <?php if (!empty($ach['position_rank'])): ?>
                    <div class="badge bg-warning-soft text-warning fw-bold mb-2"><i class="fas fa-medal me-1"></i><?= htmlspecialchars($ach['position_rank']) ?></div>
                    <?php endif; ?>

                    <?php if (!empty($ach['description'])): ?>
                    <p class="small text-secondary mb-3" style="line-height:1.5; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;"><?= htmlspecialchars($ach['description']) ?></p>
                    <?php endif; ?>
                </div>

                <div>
                    <div class="d-flex justify-content-between align-items-center pt-3 border-top small text-muted">
                        <span><i class="far fa-calendar-alt me-1"></i><?= $ach['achievement_date'] ? formatDate($ach['achievement_date']) : 'N/A' ?></span>
                        <div class="d-flex gap-2">
                            <?php if (!empty($ach['certificate_file'])): ?>
                            <a href="<?= uploadUrl('achievements/' . $ach['certificate_file']) ?>" target="_blank" class="btn btn-sm btn-outline-info" title="View Certificate">
                                <i class="fas fa-file-pdf"></i>
                            </a>
                            <?php endif; ?>
                            <button class="btn btn-sm btn-outline-primary" onclick='editAchievement(<?= json_encode($ach) ?>)' title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <a href="<?= url('/student/delete-achievement/' . $ach['id']) ?>" class="btn btn-sm btn-outline-danger" data-confirm="Are you sure you want to delete this achievement?" title="Delete">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                    </div>

                    <?php if ($ach['status'] === 'rejected' && !empty($ach['admin_remarks'])): ?>
                    <div class="alert alert-danger p-2 small mt-3 mb-0">
                        <strong>Reason:</strong> <?= htmlspecialchars($ach['admin_remarks']) ?>
                    </div>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php endif; ?>

<!-- ADD ACHIEVEMENT MODAL -->
<div class="modal fade" id="addAchievementModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <form action="<?= url('/student/add-achievement') ?>" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= CsrfMiddleware::getToken() ?>">
                
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold"><i class="fas fa-trophy me-2"></i>Add New Achievement</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-semibold small">Achievement Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control" placeholder="e.g. Winner - Smart India Hackathon 2025" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Category <span class="text-danger">*</span></label>
                            <select name="category" class="form-select" required>
                                <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat ?>"><?= $cat ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Organizer / Institution</label>
                            <input type="text" name="organizer" class="form-control" placeholder="e.g. AICTE / IIT Bombay">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold small">Position / Rank</label>
                            <input type="text" name="position_rank" class="form-control" placeholder="e.g. 1st Place, Finalist">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold small">Event Date</label>
                            <input type="date" name="achievement_date" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold small">Description / Highlights</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Describe key accomplishments, project topic, or role..."></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Upload Certificate Document (PDF/JPG/PNG)</label>
                            <input type="file" name="certificate_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Achievement Image / Photo (Optional)</label>
                            <input type="file" name="achievement_image" class="form-control" accept=".jpg,.jpeg,.png,.webp">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-save me-1"></i> Save Achievement</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- EDIT ACHIEVEMENT MODAL -->
<div class="modal fade" id="editAchievementModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <form action="<?= url('/student/edit-achievement') ?>" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= CsrfMiddleware::getToken() ?>">
                <input type="hidden" name="achievement_id" id="editAchId">
                
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold"><i class="fas fa-edit me-2"></i>Edit Achievement</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-semibold small">Achievement Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" id="editAchTitle" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Category <span class="text-danger">*</span></label>
                            <select name="category" id="editAchCategory" class="form-select" required>
                                <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat ?>"><?= $cat ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Organizer / Institution</label>
                            <input type="text" name="organizer" id="editAchOrganizer" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold small">Position / Rank</label>
                            <input type="text" name="position_rank" id="editAchPosition" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold small">Event Date</label>
                            <input type="date" name="achievement_date" id="editAchDate" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold small">Description</label>
                            <textarea name="description" id="editAchDescription" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Replace Certificate File</label>
                            <input type="file" name="certificate_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Replace Image Photo</label>
                            <input type="file" name="achievement_image" class="form-control" accept=".jpg,.jpeg,.png,.webp">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-save me-1"></i> Update &amp; Resubmit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editAchievement(ach) {
    $('#editAchId').val(ach.id);
    $('#editAchTitle').val(ach.title);
    $('#editAchCategory').val(ach.category);
    $('#editAchOrganizer').val(ach.organizer);
    $('#editAchPosition').val(ach.position_rank);
    $('#editAchDate').val(ach.achievement_date);
    $('#editAchDescription').val(ach.description);

    new bootstrap.Modal(document.getElementById('editAchievementModal')).show();
}
</script>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>
