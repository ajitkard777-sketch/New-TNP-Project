<?php require_once ROOT_PATH . '/includes/header.php'; ?>
<div class="content-header"><div><h1 class="page-title">Placements</h1><p class="subtitle"><?= $stats['total'] ?> total placements</p></div></div>

<div class="row g-4 mb-4">
    <div class="col-md-4"><div class="stat-card gradient-success"><div class="stat-card-icon bg-success-soft"><i class="fas fa-trophy"></i></div><div class="stat-card-value"><?= $stats['total'] ?></div><div class="stat-card-label">Total Placements</div></div></div>
    <div class="col-md-4"><div class="stat-card gradient-primary"><div class="stat-card-icon bg-primary-soft"><i class="fas fa-arrow-up"></i></div><div class="stat-card-value"><?= $stats['highest'] ? number_format($stats['highest'], 2) : '0' ?></div><div class="stat-card-label">Highest Package (LPA)</div></div></div>
    <div class="col-md-4"><div class="stat-card gradient-info"><div class="stat-card-icon bg-info-soft"><i class="fas fa-chart-line"></i></div><div class="stat-card-value"><?= $stats['average'] ? number_format($stats['average'], 2) : '0' ?></div><div class="stat-card-label">Average Package (LPA)</div></div></div>
</div>

<div class="card"><div class="card-body p-0"><div class="table-responsive">
    <table class="table mb-0" id="placementsTable">
        <thead><tr><th>Student</th><th>Branch</th><th>Company</th><th>Package (LPA)</th><th>Date</th><th>Status</th></tr></thead>
        <tbody>
            <?php foreach ($placements as $p): ?>
            <tr>
                <td><div class="user-cell"><img src="<?= $p['profile_photo'] ? uploadUrl('profile_photos/' . $p['profile_photo']) : asset('images/default-avatar.png') ?>" alt="" class="user-avatar" onerror="this.src='<?= asset('images/default-avatar.png') ?>'"><span class="fw-medium"><?= htmlspecialchars($p['first_name'] . ' ' . $p['last_name']) ?></span></div></td>
                <td><span class="badge bg-light text-dark"><?= htmlspecialchars($p['branch']) ?></span></td>
                <td class="fw-medium"><?= htmlspecialchars($p['company_name'] ?? 'N/A') ?></td>
                <td class="fw-bold text-success"><?= $p['package'] ? number_format($p['package'], 2) : 'N/A' ?></td>
                <td><small><?= formatDate($p['placement_date']) ?></small></td>
                <td><span class="badge bg-success"><?= ucfirst($p['status'] ?? 'confirmed') ?></span></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div></div></div>

<script>$(function(){ if($.fn.DataTable){ $('#placementsTable').DataTable({order:[[4,'desc']]}); } });</script>
<?php require_once ROOT_PATH . '/includes/footer.php'; ?>
