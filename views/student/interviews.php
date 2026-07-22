<?php require_once ROOT_PATH . '/includes/header.php'; ?>
<div class="content-header">
    <div>
        <h1 class="page-title">Interview Schedule</h1>
        <p class="subtitle">Your upcoming and past interviews</p>
    </div>
</div>

<?php if (empty($interviews)): ?>
<div class="card">
    <div class="card-body empty-state">
        <i class="fas fa-calendar-check"></i>
        <h5>No Interviews Scheduled</h5>
        <p>Interviews will appear here when you are shortlisted for a position by a company.</p>
    </div>
</div>
<?php else: ?>

<?php
$upcoming = array_filter($interviews, fn($i) => $i['status'] === 'scheduled' && $i['interview_date'] >= date('Y-m-d'));
$past     = array_filter($interviews, fn($i) => !($i['status'] === 'scheduled' && $i['interview_date'] >= date('Y-m-d')));
?>

<?php if (!empty($upcoming)): ?>
<h6 class="fw-bold text-primary mb-3" style="font-size:0.82rem;text-transform:uppercase;letter-spacing:1px;">
    <i class="fas fa-clock me-2"></i>Upcoming Interviews (<?= count($upcoming) ?>)
</h6>
<div class="row g-4 mb-4">
    <?php foreach ($upcoming as $i): ?>
    <div class="col-lg-6">
        <div class="card border-start border-primary border-3 hover-scale" style="border-left-width:4px!important;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h5 class="fw-bold mb-1"><?= htmlspecialchars($i['job_title']) ?></h5>
                        <div class="d-flex align-items-center gap-2">
                            <i class="fas fa-building text-primary" style="font-size:0.8rem;"></i>
                            <span style="font-size:0.9rem;font-weight:600;color:var(--primary);"><?= htmlspecialchars($i['company_name']) ?></span>
                        </div>
                    </div>
                    <span class="badge bg-primary" style="font-size:0.72rem;">Upcoming</span>
                </div>
                <div class="row g-2 mb-3" style="font-size:0.84rem;">
                    <div class="col-6">
                        <div class="d-flex align-items-center gap-2 text-muted">
                            <i class="fas fa-tag" style="width:14px;"></i>
                            <span><?= htmlspecialchars($i['round'] ?? 'Interview') ?></span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="d-flex align-items-center gap-2 text-muted">
                            <?php if ($i['mode'] === 'online'): ?>
                            <i class="fas fa-video" style="width:14px;color:#1e40af;"></i>
                            <?php else: ?>
                            <i class="fas fa-building" style="width:14px;"></i>
                            <?php endif; ?>
                            <span><?= ucfirst($i['mode'] ?? 'offline') ?></span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="d-flex align-items-center gap-2 text-muted">
                            <i class="far fa-calendar" style="width:14px;"></i>
                            <span><?= formatDate($i['interview_date']) ?></span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="d-flex align-items-center gap-2 text-muted">
                            <i class="far fa-clock" style="width:14px;"></i>
                            <span><?= date('h:i A', strtotime($i['interview_time'])) ?></span>
                        </div>
                    </div>
                    <?php if ($i['venue']): ?>
                    <div class="col-12">
                        <div class="d-flex align-items-center gap-2 text-muted">
                            <i class="fas fa-map-marker-alt text-danger" style="width:14px;"></i>
                            <span><?= htmlspecialchars($i['venue']) ?></span>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                <?php if ($i['instructions']): ?>
                <div class="p-2 rounded" style="background:#f0f7ff;border:1px solid #bfdbfe;font-size:0.78rem;color:#1e40af;margin-bottom:12px;">
                    <i class="fas fa-info-circle me-1"></i> <?= htmlspecialchars($i['instructions']) ?>
                </div>
                <?php endif; ?>
                <div class="d-flex align-items-center justify-content-between">
                    <span class="interview-countdown" data-date="<?= $i['interview_date'] ?>T<?= $i['interview_time'] ?>">
                        <i class="fas fa-hourglass-half"></i>
                        <span class="countdown-text">Loading...</span>
                    </span>
                    <?php if ($i['meeting_link']): ?>
                    <a href="<?= htmlspecialchars($i['meeting_link']) ?>" target="_blank" class="btn btn-sm btn-primary">
                        <i class="fas fa-video me-1"></i>Join Meeting
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php if (!empty($past)): ?>
<h6 class="fw-bold text-muted mb-3" style="font-size:0.82rem;text-transform:uppercase;letter-spacing:1px;">
    <i class="fas fa-history me-2"></i>Past Interviews (<?= count($past) ?>)
</h6>
<div class="row g-4">
    <?php foreach ($past as $i): ?>
    <div class="col-lg-6">
        <div class="card hover-scale" style="opacity:0.88;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h6 class="fw-bold mb-1"><?= htmlspecialchars($i['job_title']) ?></h6>
                        <small class="text-muted"><?= htmlspecialchars($i['company_name']) ?></small>
                    </div>
                    <span class="badge <?= getStatusBadgeClass($i['status']) ?>"><?= ucfirst($i['status']) ?></span>
                </div>
                <div class="row g-1 mb-2" style="font-size:0.8rem;color:var(--text-muted);">
                    <div class="col-6"><i class="fas fa-tag me-1"></i><?= htmlspecialchars($i['round'] ?? 'Interview') ?></div>
                    <div class="col-6"><i class="far fa-calendar me-1"></i><?= formatDate($i['interview_date']) ?></div>
                    <div class="col-6"><i class="far fa-clock me-1"></i><?= date('h:i A', strtotime($i['interview_time'])) ?></div>
                    <div class="col-6"><i class="fas fa-<?= $i['mode'] === 'online' ? 'video' : 'building' ?> me-1"></i><?= ucfirst($i['mode'] ?? 'offline') ?></div>
                </div>
                <?php if ($i['result'] !== 'pending'): ?>
                <div class="mt-2">
                    <span class="badge" style="font-size:0.78rem;background:<?= $i['result'] === 'passed' ? '#dcfce7' : '#fee2e2' ?>;color:<?= $i['result'] === 'passed' ? '#166534' : '#991b1b' ?>;">
                        <i class="fas fa-<?= $i['result'] === 'passed' ? 'check' : 'times' ?> me-1"></i>
                        <?= ucfirst($i['result']) ?>
                    </span>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>
<?php endif; ?>

<script>
// Countdown timer for upcoming interviews
document.querySelectorAll('.interview-countdown').forEach(el => {
    const dateStr = el.dataset.date;
    const target  = new Date(dateStr).getTime();
    const textEl  = el.querySelector('.countdown-text');

    function update() {
        const now  = Date.now();
        const diff = target - now;
        if (diff <= 0) {
            textEl.textContent = 'Started';
            el.style.background = '#dcfce7';
            el.style.color = '#166534';
            return;
        }
        const d  = Math.floor(diff / 86400000);
        const h  = Math.floor((diff % 86400000) / 3600000);
        const m  = Math.floor((diff % 3600000) / 60000);
        if (d > 0) textEl.textContent = `in ${d}d ${h}h`;
        else if (h > 0) textEl.textContent = `in ${h}h ${m}m`;
        else textEl.textContent = `in ${m}m`;
    }

    update();
    setInterval(update, 60000);
});
</script>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>
