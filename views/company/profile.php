<?php require_once ROOT_PATH . '/includes/header.php'; ?>
<div class="content-header"><div><h1 class="page-title">Company Profile</h1><p class="subtitle">Manage your company information</p></div></div>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card"><div class="card-body profile-card">
            <img src="<?= $company['logo'] ? uploadUrl('company/' . $company['logo']) : asset('images/default-avatar.png') ?>" alt="Logo" class="profile-avatar" style="border-radius:var(--radius-lg)" onerror="this.src='<?= asset('images/default-avatar.png') ?>'">
            <h5 class="profile-name"><?= htmlspecialchars($company['company_name']) ?></h5>
            <p class="profile-role"><?= htmlspecialchars($company['industry'] ?? 'Company') ?></p>
            <span class="badge bg-<?= $company['is_approved'] ? 'success' : 'warning' ?>"><?= $company['is_approved'] ? 'Approved' : 'Pending Approval' ?></span>
            <div class="text-start mt-4">
                <div class="mb-2"><i class="fas fa-envelope text-primary me-2" style="width:18px"></i><small><?= htmlspecialchars($company['email'] ?? '') ?></small></div>
                <div class="mb-2"><i class="fas fa-phone text-primary me-2" style="width:18px"></i><small><?= htmlspecialchars($company['contact_phone'] ?? 'N/A') ?></small></div>
                <div class="mb-2"><i class="fas fa-user text-primary me-2" style="width:18px"></i><small><?= htmlspecialchars($company['contact_person'] ?? 'N/A') ?></small></div>
                <div class="mb-2"><i class="fas fa-map-marker-alt text-primary me-2" style="width:18px"></i><small><?= htmlspecialchars(($company['city'] ?? '') . ($company['state'] ? ', ' . $company['state'] : '')) ?: 'N/A' ?></small></div>
                <?php if ($company['website']): ?><div class="mb-2"><i class="fas fa-globe text-primary me-2" style="width:18px"></i><a href="<?= htmlspecialchars($company['website']) ?>" target="_blank"><small>Website</small></a></div><?php endif; ?>
            </div>
        </div></div>
    </div>
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header"><h6><i class="fas fa-edit me-2 text-primary"></i>Edit Profile</h6></div>
            <div class="card-body">
                <form action="<?= url('/company/profile') ?>" method="POST" enctype="multipart/form-data" data-validate>
                    <?= CsrfMiddleware::tokenField() ?>
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label">Company Name *</label><input type="text" class="form-control" name="company_name" value="<?= htmlspecialchars($company['company_name'] ?? '') ?>" required></div>
                        <div class="col-md-6"><label class="form-label">Industry</label><select class="form-select" name="industry"><option value="">Select</option><?php foreach (['Information Technology','Finance','Healthcare','Manufacturing','Consulting','E-Commerce','Education','Automotive','Technology','Other'] as $ind): ?><option value="<?= $ind ?>" <?= ($company['industry'] ?? '') === $ind ? 'selected' : '' ?>><?= $ind ?></option><?php endforeach; ?></select></div>
                        <div class="col-md-6"><label class="form-label">Website</label><input type="url" class="form-control" name="website" value="<?= htmlspecialchars($company['website'] ?? '') ?>"></div>
                        <div class="col-md-6"><label class="form-label">Company Type</label><select class="form-select" name="company_type"><option value="">Select</option><?php foreach (['product'=>'Product Based','service'=>'Service Based','startup'=>'Startup','mnc'=>'MNC','government'=>'Government'] as $k=>$v): ?><option value="<?= $k ?>" <?= ($company['company_type'] ?? '') === $k ? 'selected' : '' ?>><?= $v ?></option><?php endforeach; ?></select></div>
                        <div class="col-md-4"><label class="form-label">Contact Person</label><input type="text" class="form-control" name="contact_person" value="<?= htmlspecialchars($company['contact_person'] ?? '') ?>"></div>
                        <div class="col-md-4"><label class="form-label">Contact Phone</label><input type="text" class="form-control" name="contact_phone" value="<?= htmlspecialchars($company['contact_phone'] ?? '') ?>" maxlength="10"></div>
                        <div class="col-md-4"><label class="form-label">Contact Email</label><input type="email" class="form-control" name="contact_email" value="<?= htmlspecialchars($company['contact_email'] ?? '') ?>"></div>
                        <div class="col-md-4"><label class="form-label">Employee Count</label><select class="form-select" name="employee_count"><option value="">Select</option><?php foreach (['1-50','50-200','200-1000','1000-5000','5000+','50000+'] as $ec): ?><option value="<?= $ec ?>" <?= ($company['employee_count'] ?? '') === $ec ? 'selected' : '' ?>><?= $ec ?></option><?php endforeach; ?></select></div>
                        <div class="col-md-4"><label class="form-label">Established Year</label><input type="number" class="form-control" name="established_year" value="<?= $company['established_year'] ?? '' ?>" min="1900" max="<?= date('Y') ?>"></div>
                        <div class="col-md-4"><label class="form-label">Company Logo</label><input type="file" class="form-control" name="logo" accept="image/*"></div>
                        <div class="col-12"><label class="form-label">Address</label><input type="text" class="form-control" name="address" value="<?= htmlspecialchars($company['address'] ?? '') ?>"></div>
                        <div class="col-md-4"><label class="form-label">City</label><input type="text" class="form-control" name="city" value="<?= htmlspecialchars($company['city'] ?? '') ?>"></div>
                        <div class="col-md-4"><label class="form-label">State</label><input type="text" class="form-control" name="state" value="<?= htmlspecialchars($company['state'] ?? '') ?>"></div>
                        <div class="col-md-4"><label class="form-label">Country</label><input type="text" class="form-control" name="country" value="<?= htmlspecialchars($company['country'] ?? 'India') ?>"></div>
                        <div class="col-12"><label class="form-label">Description</label><textarea class="form-control" name="description" rows="3"><?= htmlspecialchars($company['description'] ?? '') ?></textarea></div>
                    </div>
                    <div class="mt-4"><button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Save Changes</button></div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php require_once ROOT_PATH . '/includes/footer.php'; ?>
