<?php
/**
 * TPMS - Flash Alert Messages
 */
$flash = getFlash();
?>

<?php if ($flash): ?>
<div class="content-wrapper" style="padding-bottom:0">
    <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show animate-fade-in-up" role="alert">
        <i class="fas fa-<?= $flash['type'] === 'success' ? 'check-circle' : ($flash['type'] === 'danger' ? 'times-circle' : ($flash['type'] === 'warning' ? 'exclamation-triangle' : 'info-circle')) ?>"></i>
        <div><?= $flash['message'] ?></div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
</div>
<?php endif; ?>
