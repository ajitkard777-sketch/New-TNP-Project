<?php require_once ROOT_PATH . '/includes/header.php'; ?>

<div class="content-header">
    <div>
        <h1 class="page-title">Manage Companies</h1>
        <p class="subtitle">Total: <?= $totalCount ?> companies &nbsp;|&nbsp;
            <span class="text-warning"><?= $pendingCount ?> pending</span> &nbsp;|&nbsp;
            <span class="text-success"><?= $verifiedCount ?> verified</span> &nbsp;|&nbsp;
            <span class="text-danger"><?= $rejectedCount ?> rejected</span>
        </p>
    </div>
</div>

<!-- Search Bar -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-8">
                <label class="form-label">Search</label>
                <input type="text" class="form-control" name="search"
                       value="<?= htmlspecialchars($search) ?>"
                       placeholder="Company name, email, HR name...">
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search me-1"></i> Search
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Tabs -->
<ul class="nav nav-tabs mb-0" id="companyTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link <?= ($activeTab === 'pending') ? 'active' : '' ?>"
                id="tab-pending" data-bs-toggle="tab" data-bs-target="#pending"
                type="button" role="tab">
            <i class="fas fa-clock me-1 text-warning"></i> Pending
            <span class="badge bg-warning text-dark ms-1"><?= $pendingCount ?></span>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link <?= ($activeTab === 'verified') ? 'active' : '' ?>"
                id="tab-verified" data-bs-toggle="tab" data-bs-target="#verified"
                type="button" role="tab">
            <i class="fas fa-check-circle me-1 text-success"></i> Verified
            <span class="badge bg-success ms-1"><?= $verifiedCount ?></span>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link <?= ($activeTab === 'rejected') ? 'active' : '' ?>"
                id="tab-rejected" data-bs-toggle="tab" data-bs-target="#rejected"
                type="button" role="tab">
            <i class="fas fa-ban me-1 text-danger"></i> Rejected / Suspended
            <span class="badge bg-danger ms-1"><?= $rejectedCount + $suspendedCount ?></span>
        </button>
    </li>
</ul>

<div class="tab-content card" style="border-top: none; border-radius: 0 0 var(--radius-md) var(--radius-md);">

    <!-- ═══════════════════════════ PENDING TAB ═══════════════════════════ -->
    <div class="tab-pane fade <?= ($activeTab === 'pending') ? 'show active' : '' ?>"
         id="pending" role="tabpanel">
        <div class="card-body p-0">
            <?php if (empty($pendingCompanies)): ?>
            <div class="empty-state py-5">
                <i class="fas fa-inbox" style="font-size:2.5rem; opacity:0.3"></i>
                <p class="mt-3 text-muted">No pending company registrations.</p>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Company</th>
                            <th>Industry</th>
                            <th>HR Name</th>
                            <th>Mobile</th>
                            <th>Address</th>
                            <th>Registered</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pendingCompanies as $c): ?>
                        <tr>
                            <td>
                                <div class="user-cell">
                                    <img src="<?= $c['logo'] ? uploadUrl('company/' . $c['logo']) : asset('images/default-avatar.png') ?>"
                                         alt="" class="user-avatar" style="border-radius:var(--radius-sm)"
                                         onerror="this.src='<?= asset('images/default-avatar.png') ?>'">
                                    <div class="user-info">
                                        <div class="name"><?= htmlspecialchars($c['company_name']) ?></div>
                                        <div class="email"><?= htmlspecialchars($c['email']) ?></div>
                                        <?php if ($c['website']): ?>
                                        <a href="<?= htmlspecialchars($c['website']) ?>" target="_blank"
                                           class="text-muted" style="font-size:0.75rem">
                                            <i class="fas fa-external-link-alt me-1"></i><?= htmlspecialchars($c['website']) ?>
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td><small><?= htmlspecialchars($c['industry'] ?? 'N/A') ?></small><br>
                                <span class="badge bg-light text-dark" style="font-size:0.7rem"><?= ucfirst($c['company_type'] ?? 'other') ?></span>
                            </td>
                            <td><small class="fw-medium"><?= htmlspecialchars($c['contact_person'] ?? 'N/A') ?></small></td>
                            <td><small><?= htmlspecialchars($c['contact_phone'] ?? 'N/A') ?></small></td>
                            <td><small class="text-muted"><?= htmlspecialchars(($c['address'] ?? '') . ($c['city'] ? ', ' . $c['city'] : '') . ($c['state'] ? ', ' . $c['state'] : '')) ?: 'N/A' ?></small></td>
                            <td><small class="text-muted"><?= timeAgo($c['created_at']) ?></small></td>
                            <td>
                                <div class="d-flex gap-1 flex-wrap">
                                    <!-- Approve -->
                                    <a href="<?= url('/admin/approve-company/' . $c['id']) ?>"
                                       class="btn btn-sm btn-success" title="Approve Company"
                                       data-confirm="Approve <?= htmlspecialchars(addslashes($c['company_name'])) ?>?">
                                        <i class="fas fa-check me-1"></i>Approve
                                    </a>
                                    <!-- Reject with reason -->
                                    <button type="button" class="btn btn-sm btn-danger"
                                            data-bs-toggle="modal" data-bs-target="#rejectModal"
                                            data-company-id="<?= $c['id'] ?>"
                                            data-company-name="<?= htmlspecialchars($c['company_name']) ?>"
                                            title="Reject">
                                        <i class="fas fa-times me-1"></i>Reject
                                    </button>
                                    <!-- Delete -->
                                    <a href="<?= url('/admin/delete-company/' . $c['id']) ?>"
                                       class="btn btn-sm btn-outline-danger" title="Delete"
                                       data-confirm="Permanently delete <?= htmlspecialchars(addslashes($c['company_name'])) ?> and all its data?">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ═══════════════════════════ VERIFIED TAB ══════════════════════════ -->
    <div class="tab-pane fade <?= ($activeTab === 'verified') ? 'show active' : '' ?>"
         id="verified" role="tabpanel">
        <div class="card-body p-0">
            <?php if (empty($verifiedCompanies)): ?>
            <div class="empty-state py-5">
                <i class="fas fa-building" style="font-size:2.5rem; opacity:0.3"></i>
                <p class="mt-3 text-muted">No verified companies yet.</p>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Company</th>
                            <th>Industry / Type</th>
                            <th>HR Contact</th>
                            <th>Jobs Posted</th>
                            <th>Applications</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($verifiedCompanies as $c): ?>
                        <tr>
                            <td>
                                <div class="user-cell">
                                    <img src="<?= $c['logo'] ? uploadUrl('company/' . $c['logo']) : asset('images/default-avatar.png') ?>"
                                         alt="" class="user-avatar" style="border-radius:var(--radius-sm)"
                                         onerror="this.src='<?= asset('images/default-avatar.png') ?>'">
                                    <div class="user-info">
                                        <div class="name"><?= htmlspecialchars($c['company_name']) ?></div>
                                        <div class="email"><?= htmlspecialchars($c['email']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <small><?= htmlspecialchars($c['industry'] ?? 'N/A') ?></small><br>
                                <span class="badge bg-light text-dark" style="font-size:0.7rem"><?= ucfirst($c['company_type'] ?? 'other') ?></span>
                            </td>
                            <td>
                                <small class="fw-medium"><?= htmlspecialchars($c['contact_person'] ?? 'N/A') ?></small><br>
                                <small class="text-muted"><?= htmlspecialchars($c['contact_phone'] ?? '') ?></small>
                            </td>
                            <td><span class="badge bg-primary"><?= $c['job_count'] ?? 0 ?></span></td>
                            <td><span class="badge bg-info text-dark"><?= $c['application_count'] ?? 0 ?></span></td>
                            <td>
                                <?php if ($c['user_status'] === 'blocked'): ?>
                                    <span class="badge bg-warning text-dark">Suspended</span>
                                <?php else: ?>
                                    <span class="badge bg-success">Active</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="d-flex gap-1 flex-wrap">
                                    <?php if ($c['user_status'] !== 'blocked'): ?>
                                    <!-- Suspend -->
                                    <a href="<?= url('/admin/suspend-company/' . $c['id']) ?>"
                                       class="btn btn-sm btn-warning text-dark" title="Suspend Company"
                                       data-confirm="Suspend <?= htmlspecialchars(addslashes($c['company_name'])) ?>? They won't be able to log in.">
                                        <i class="fas fa-pause-circle me-1"></i>Suspend
                                    </a>
                                    <?php else: ?>
                                    <!-- Unsuspend -->
                                    <a href="<?= url('/admin/unsuspend-company/' . $c['id']) ?>"
                                       class="btn btn-sm btn-success" title="Re-activate Company"
                                       data-confirm="Re-activate <?= htmlspecialchars(addslashes($c['company_name'])) ?>?">
                                        <i class="fas fa-play-circle me-1"></i>Activate
                                    </a>
                                    <?php endif; ?>
                                    <!-- Reject -->
                                    <button type="button" class="btn btn-sm btn-outline-danger"
                                            data-bs-toggle="modal" data-bs-target="#rejectModal"
                                            data-company-id="<?= $c['id'] ?>"
                                            data-company-name="<?= htmlspecialchars($c['company_name']) ?>"
                                            title="Reject & Block">
                                        <i class="fas fa-ban"></i>
                                    </button>
                                    <!-- Delete -->
                                    <a href="<?= url('/admin/delete-company/' . $c['id']) ?>"
                                       class="btn btn-sm btn-outline-danger" title="Delete"
                                       data-confirm="Permanently delete <?= htmlspecialchars(addslashes($c['company_name'])) ?> and all its data?">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ══════════════════════ REJECTED / SUSPENDED TAB ═══════════════════ -->
    <div class="tab-pane fade <?= ($activeTab === 'rejected') ? 'show active' : '' ?>"
         id="rejected" role="tabpanel">
        <div class="card-body p-0">
            <?php $allRejected = array_merge($rejectedCompanies, $suspendedCompanies); ?>
            <?php if (empty($allRejected)): ?>
            <div class="empty-state py-5">
                <i class="fas fa-ban" style="font-size:2.5rem; opacity:0.3"></i>
                <p class="mt-3 text-muted">No rejected or suspended companies.</p>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Company</th>
                            <th>HR Contact</th>
                            <th>State</th>
                            <th>Reason</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($allRejected as $c): ?>
                        <tr>
                            <td>
                                <div class="user-cell">
                                    <img src="<?= $c['logo'] ? uploadUrl('company/' . $c['logo']) : asset('images/default-avatar.png') ?>"
                                         alt="" class="user-avatar" style="border-radius:var(--radius-sm)"
                                         onerror="this.src='<?= asset('images/default-avatar.png') ?>'">
                                    <div class="user-info">
                                        <div class="name"><?= htmlspecialchars($c['company_name']) ?></div>
                                        <div class="email"><?= htmlspecialchars($c['email']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <small class="fw-medium"><?= htmlspecialchars($c['contact_person'] ?? 'N/A') ?></small><br>
                                <small class="text-muted"><?= htmlspecialchars($c['contact_phone'] ?? '') ?></small>
                            </td>
                            <td>
                                <?php if (!empty($c['is_rejected'])): ?>
                                    <span class="badge bg-danger">Rejected</span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark">Suspended</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <small class="text-muted">
                                    <?= !empty($c['rejection_reason']) ? htmlspecialchars($c['rejection_reason']) : '<em>No reason provided</em>' ?>
                                </small>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <!-- Re-approve -->
                                    <a href="<?= url('/admin/approve-company/' . $c['id']) ?>"
                                       class="btn btn-sm btn-success" title="Re-approve"
                                       data-confirm="Re-approve <?= htmlspecialchars(addslashes($c['company_name'])) ?>?">
                                        <i class="fas fa-undo me-1"></i>Approve
                                    </a>
                                    <!-- Delete -->
                                    <a href="<?= url('/admin/delete-company/' . $c['id']) ?>"
                                       class="btn btn-sm btn-outline-danger" title="Delete"
                                       data-confirm="Permanently delete <?= htmlspecialchars(addslashes($c['company_name'])) ?>?">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>

</div><!-- /.tab-content -->

<?= renderPagination($pagination, url('/admin/companies')) ?>

<!-- ════════════════════════ Reject Modal ════════════════════════ -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" id="rejectForm" action="">
                <?= CsrfMiddleware::tokenField() ?>
                <div class="modal-header">
                    <h5 class="modal-title" id="rejectModalLabel">
                        <i class="fas fa-ban text-danger me-2"></i>Reject Company
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>You are about to reject <strong id="rejectCompanyName"></strong>.</p>
                    <div class="mb-3">
                        <label class="form-label">Rejection Reason <small class="text-muted">(optional but recommended)</small></label>
                        <textarea class="form-control" name="rejection_reason" rows="3"
                                  placeholder="Explain why the registration is being rejected. The company will see this reason."></textarea>
                    </div>
                    <div class="alert alert-warning mb-0" style="font-size:0.82rem">
                        <i class="fas fa-exclamation-triangle me-1"></i>
                        The company account will be blocked and they will not be able to log in.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-ban me-1"></i>Reject Company
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Populate reject modal with company name and set form action
document.getElementById('rejectModal').addEventListener('show.bs.modal', function(event) {
    const btn = event.relatedTarget;
    document.getElementById('rejectCompanyName').textContent = btn.dataset.companyName;
    document.getElementById('rejectForm').action =
        '<?= rtrim(BASE_URL, '/') ?>/admin/reject-company/' + btn.dataset.companyId;
});

// Activate the correct tab based on URL hash or server-side flag
document.addEventListener('DOMContentLoaded', function () {
    const hash = window.location.hash;
    if (hash) {
        const tab = document.querySelector('[data-bs-target="' + hash + '"]');
        if (tab) bootstrap.Tab.getOrCreateInstance(tab).show();
    }
});
</script>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>
