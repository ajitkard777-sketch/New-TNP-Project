<?php require_once ROOT_PATH . '/includes/header.php'; ?>

<div class="content-header d-flex align-items-center justify-content-between">
    <div>
        <h1 class="page-title"><i class="fas fa-history text-primary me-2"></i>SMS Notification History</h1>
        <p class="subtitle">Real-time audit logs of outbound SMS notifications and delivery status</p>
    </div>
    <div>
        <a href="<?= url('/admin/sms-settings') ?>" class="btn btn-outline-secondary">
            <i class="fas fa-cog me-1"></i> SMS Settings
        </a>
    </div>
</div>

<!-- Filters & Search -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <form method="GET" action="<?= url('/admin/sms-logs') ?>" class="row g-3">
            <div class="col-md-3">
                <label class="form-label small text-muted">Search Phone / Message</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="+919876543210 or text..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Statuses</option>
                    <option value="sent" <?= ($_GET['status'] ?? '') === 'sent' ? 'selected' : '' ?>>Sent</option>
                    <option value="failed" <?= ($_GET['status'] ?? '') === 'failed' ? 'selected' : '' ?>>Failed</option>
                    <option value="pending" <?= ($_GET['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted">Event Type</label>
                <select name="event" class="form-select form-select-sm">
                    <option value="">All Events</option>
                    <option value="company_verified" <?= ($_GET['event'] ?? '') === 'company_verified' ? 'selected' : '' ?>>Company Verified</option>
                    <option value="job_posted" <?= ($_GET['event'] ?? '') === 'job_posted' ? 'selected' : '' ?>>Job Posted</option>
                    <option value="student_shortlisted" <?= ($_GET['event'] ?? '') === 'student_shortlisted' ? 'selected' : '' ?>>Student Shortlisted</option>
                    <option value="interview_scheduled" <?= ($_GET['event'] ?? '') === 'interview_scheduled' ? 'selected' : '' ?>>Interview Scheduled</option>
                    <option value="offer_released" <?= ($_GET['event'] ?? '') === 'offer_released' ? 'selected' : '' ?>>Offer Letter Released</option>
                    <option value="password_reset" <?= ($_GET['event'] ?? '') === 'password_reset' ? 'selected' : '' ?>>Password Reset</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">Provider</label>
                <select name="provider" class="form-select form-select-sm">
                    <option value="">All Providers</option>
                    <option value="twilio" <?= ($_GET['provider'] ?? '') === 'twilio' ? 'selected' : '' ?>>Twilio</option>
                    <option value="fast2sms" <?= ($_GET['provider'] ?? '') === 'fast2sms' ? 'selected' : '' ?>>Fast2SMS</option>
                    <option value="msg91" <?= ($_GET['provider'] ?? '') === 'msg91' ? 'selected' : '' ?>>MSG91</option>
                </select>
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button type="submit" class="btn btn-sm btn-primary w-100"><i class="fas fa-filter me-1"></i> Filter</button>
            </div>
        </form>
    </div>
</div>

<!-- Log Table -->
<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">ID</th>
                        <th>Recipient</th>
                        <th>Event</th>
                        <th>Provider</th>
                        <th>Message Snippet</th>
                        <th>Status</th>
                        <th>Retries</th>
                        <th>Time</th>
                        <th class="pe-3 text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($logs)): ?>
                    <tr>
                        <td colspan="9" class="text-center py-4 text-muted">
                            <i class="fas fa-inbox fs-3 mb-2 d-block"></i> No SMS notification logs found.
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($logs as $l): ?>
                        <tr>
                            <td class="ps-3 fw-bold">#<?= $l['id'] ?></td>
                            <td>
                                <div><strong style="font-size:0.9rem;"><?= htmlspecialchars($l['recipient_phone']) ?></strong></div>
                                <?php if (!empty($l['user_email'])): ?>
                                    <small class="text-muted"><?= htmlspecialchars($l['user_email']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                $eventBadges = [
                                    'company_verified'    => 'bg-info',
                                    'job_posted'          => 'bg-success',
                                    'student_shortlisted' => 'bg-warning text-dark',
                                    'interview_scheduled' => 'bg-primary',
                                    'offer_released'      => 'bg-purple',
                                    'password_reset'      => 'bg-danger',
                                ];
                                $badgeClass = $eventBadges[$l['event_type']] ?? 'bg-secondary';
                                ?>
                                <span class="badge <?= $badgeClass ?>">
                                    <?= htmlspecialchars(ucwords(str_replace('_', ' ', $l['event_type']))) ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    <?= htmlspecialchars(strtoupper($l['provider'])) ?>
                                </span>
                            </td>
                            <td style="max-width:260px;">
                                <div class="text-truncate" title="<?= htmlspecialchars($l['message']) ?>">
                                    <?= htmlspecialchars($l['message']) ?>
                                </div>
                            </td>
                            <td>
                                <?php if ($l['status'] === 'sent'): ?>
                                    <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i> Sent</span>
                                <?php elseif ($l['status'] === 'failed'): ?>
                                    <span class="badge bg-danger" title="<?= htmlspecialchars($l['error_message'] ?? '') ?>">
                                        <i class="fas fa-times-circle me-1"></i> Failed
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark"><i class="fas fa-clock me-1"></i> Pending</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-outline-secondary"><?= (int)$l['retry_count'] ?></span>
                            </td>
                            <td>
                                <small class="text-muted" title="<?= $l['created_at'] ?>">
                                    <?= timeAgo($l['created_at']) ?>
                                </small>
                            </td>
                            <td class="pe-3 text-end">
                                <button type="button" class="btn btn-sm btn-outline-info me-1" data-bs-toggle="modal" data-bs-target="#logModal<?= $l['id'] ?>">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <?php if ($l['status'] === 'failed'): ?>
                                    <a href="<?= url('/admin/sms-retry/' . $l['id']) ?>" class="btn btn-sm btn-outline-danger" title="Retry sending SMS">
                                        <i class="fas fa-redo"></i> Retry
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>

                        <!-- Modal for Details -->
                        <div class="modal fade" id="logModal<?= $l['id'] ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">SMS Log Details #<?= $l['id'] ?></h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-content-body p-4">
                                        <div class="mb-3">
                                            <strong>Recipient Phone:</strong> <?= htmlspecialchars($l['recipient_phone']) ?>
                                        </div>
                                        <div class="mb-3">
                                            <strong>Event Type:</strong> <?= htmlspecialchars($l['event_type']) ?>
                                        </div>
                                        <div class="mb-3">
                                            <strong>Provider:</strong> <?= htmlspecialchars(strtoupper($l['provider'])) ?>
                                        </div>
                                        <div class="mb-3">
                                            <strong>Status:</strong>
                                            <span class="badge bg-<?= $l['status'] === 'sent' ? 'success' : ($l['status'] === 'failed' ? 'danger' : 'warning') ?>">
                                                <?= ucfirst($l['status']) ?>
                                            </span>
                                        </div>
                                        <?php if (!empty($l['error_message'])): ?>
                                        <div class="mb-3">
                                            <strong class="text-danger">Error Details:</strong>
                                            <div class="p-2 bg-light text-danger rounded mt-1 font-monospace" style="font-size:0.85rem;">
                                                <?= htmlspecialchars($l['error_message']) ?>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                        <div class="mb-3">
                                            <strong>Message Content:</strong>
                                            <div class="p-3 bg-light rounded mt-1" style="white-space:pre-wrap; font-size:0.9rem;">
                                                <?= htmlspecialchars($l['message']) ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        <?php if ($l['status'] === 'failed'): ?>
                                            <a href="<?= url('/admin/sms-retry/' . $l['id']) ?>" class="btn btn-danger">
                                                <i class="fas fa-redo me-1"></i> Retry Sending Now
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-4">
    <?= renderPagination($pagination, url('/admin/sms-logs')) ?>
</div>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>
