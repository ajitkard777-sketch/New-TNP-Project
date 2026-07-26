<?php require_once ROOT_PATH . '/includes/header.php'; ?>
<div class="content-header d-flex align-items-center justify-content-between">
    <div>
        <h1 class="page-title"><i class="fas fa-sms text-primary me-2"></i>SMS Module Settings</h1>
        <p class="subtitle">Configure SMS notification service, provider API keys, and event message templates</p>
    </div>
    <div>
        <a href="<?= url('/admin/sms-logs') ?>" class="btn btn-outline-primary">
            <i class="fas fa-history me-1"></i> View Notification History
        </a>
    </div>
</div>

<form action="<?= url('/admin/sms-settings') ?>" method="POST">
    <?= CsrfMiddleware::tokenField() ?>

    <div class="row g-4">
        <!-- Master Controls & Provider Selector -->
        <div class="col-lg-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-transparent fw-bold d-flex justify-content-between align-items-center py-3">
                    <span><i class="fas fa-toggle-on text-primary me-2"></i>General SMS Configuration</span>
                    <div class="form-check form-switch fs-5">
                        <input class="form-check-input" type="checkbox" id="sms_enabled" name="sms_enabled" value="1" <?= (!empty($settings['sms_enabled']) && $settings['sms_enabled'] == '1') ? 'checked' : '' ?>>
                        <label class="form-check-label fs-6 fw-semibold" for="sms_enabled">Enable SMS Module</label>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Default SMS Provider</label>
                            <select name="default_provider" class="form-select" id="providerSelect">
                                <option value="twilio" <?= ($settings['default_provider'] ?? '') === 'twilio' ? 'selected' : '' ?>>Twilio (Global / International)</option>
                                <option value="fast2sms" <?= ($settings['default_provider'] ?? '') === 'fast2sms' ? 'selected' : '' ?>>Fast2SMS (India DLT / Quick SMS)</option>
                                <option value="msg91" <?= ($settings['default_provider'] ?? '') === 'msg91' ? 'selected' : '' ?>>MSG91 (India DLT / Flow SMS)</option>
                            </select>
                            <small class="text-muted">The abstraction layer will use this provider for sending notifications.</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Maximum Automatic Retry Attempts</label>
                            <input type="number" name="max_retries" class="form-control" min="1" max="5" value="<?= htmlspecialchars($settings['max_retries'] ?? '3') ?>">
                            <small class="text-muted">Automatic retries attempted if provider endpoint fails.</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Provider API Credentials Tabs / Cards -->
        <div class="col-lg-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-transparent fw-bold py-3">
                    <i class="fas fa-key text-warning me-2"></i>SMS Provider API Credentials
                </div>
                <div class="card-body">
                    <ul class="nav nav-pills mb-3" id="providerTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="twilio-tab" data-bs-toggle="tab" data-bs-target="#twilio-panel" type="button" role="tab">
                                <i class="fas fa-broadcast-tower me-1"></i> Twilio
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="fast2sms-tab" data-bs-toggle="tab" data-bs-target="#fast2sms-panel" type="button" role="tab">
                                <i class="fas fa-paper-plane me-1"></i> Fast2SMS
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="msg91-tab" data-bs-toggle="tab" data-bs-target="#msg91-panel" type="button" role="tab">
                                <i class="fas fa-comments me-1"></i> MSG91
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content p-3 border rounded bg-light" id="providerTabContent">
                        <!-- Twilio Panel -->
                        <div class="tab-pane fade show active" id="twilio-panel" role="tabpanel">
                            <h6 class="fw-bold mb-3 text-primary"><i class="fab fa-twilio me-1"></i> Twilio API Settings</h6>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Account SID</label>
                                    <input type="text" name="twilio_account_sid" class="form-control" placeholder="ACxxxxxxxxxxxxxxxxxxxxxxxx" value="<?= htmlspecialchars($settings['twilio_account_sid'] ?? '') ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Auth Token</label>
                                    <input type="password" name="twilio_auth_token" class="form-control" placeholder="Your Twilio Auth Token" value="<?= htmlspecialchars($settings['twilio_auth_token'] ?? '') ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">From Phone Number</label>
                                    <input type="text" name="twilio_from_number" class="form-control" placeholder="+18005550199" value="<?= htmlspecialchars($settings['twilio_from_number'] ?? '') ?>">
                                </div>
                            </div>
                        </div>

                        <!-- Fast2SMS Panel -->
                        <div class="tab-pane fade" id="fast2sms-panel" role="tabpanel">
                            <h6 class="fw-bold mb-3 text-success"><i class="fas fa-bolt me-1"></i> Fast2SMS API Settings</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">API Key</label>
                                    <input type="password" name="fast2sms_api_key" class="form-control" placeholder="Fast2SMS Authorization Key" value="<?= htmlspecialchars($settings['fast2sms_api_key'] ?? '') ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Sender ID (DLT Header)</label>
                                    <input type="text" name="fast2sms_sender_id" class="form-control" placeholder="TXTIND" maxlength="6" value="<?= htmlspecialchars($settings['fast2sms_sender_id'] ?? 'TXTIND') ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Route</label>
                                    <select name="fast2sms_route" class="form-select">
                                        <option value="v3" <?= ($settings['fast2sms_route'] ?? '') === 'v3' ? 'selected' : '' ?>>v3 (DLT/Quick SMS)</option>
                                        <option value="otp" <?= ($settings['fast2sms_route'] ?? '') === 'otp' ? 'selected' : '' ?>>otp (OTP Route)</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- MSG91 Panel -->
                        <div class="tab-pane fade" id="msg91-panel" role="tabpanel">
                            <h6 class="fw-bold mb-3 text-info"><i class="fas fa-comment-dots me-1"></i> MSG91 API Settings</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Auth Key</label>
                                    <input type="password" name="msg91_auth_key" class="form-control" placeholder="MSG91 Auth Key" value="<?= htmlspecialchars($settings['msg91_auth_key'] ?? '') ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Sender ID Header</label>
                                    <input type="text" name="msg91_sender_id" class="form-control" placeholder="TPMSYS" maxlength="6" value="<?= htmlspecialchars($settings['msg91_sender_id'] ?? 'TPMSYS') ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Route</label>
                                    <input type="text" name="msg91_route" class="form-control" placeholder="4" value="<?= htmlspecialchars($settings['msg91_route'] ?? '4') ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Event Message Templates -->
        <div class="col-lg-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-transparent fw-bold py-3">
                    <i class="fas fa-sliders-h text-success me-2"></i>Event Notification Templates
                </div>
                <div class="card-body">
                    <div class="alert alert-info py-2" style="font-size:0.88rem;">
                        <i class="fas fa-info-circle me-1"></i> You can use shortcode place-holders in templates:
                        <span class="badge bg-secondary">{company_name}</span>
                        <span class="badge bg-secondary">{student_name}</span>
                        <span class="badge bg-secondary">{job_title}</span>
                        <span class="badge bg-secondary">{package}</span>
                        <span class="badge bg-secondary">{date}</span>
                        <span class="badge bg-secondary">{time}</span>
                        <span class="badge bg-secondary">{mode}</span>
                        <span class="badge bg-secondary">{otp}</span>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold"><i class="fas fa-building text-primary me-1"></i> Company Verified Template</label>
                            <textarea name="template_company_verified" class="form-control" rows="2"><?= htmlspecialchars($settings['template_company_verified'] ?? "Hello {company_name}, your company account on TPMS has been verified. You can now log in and post job opportunities.") ?></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold"><i class="fas fa-briefcase text-success me-1"></i> Job Posted Template</label>
                            <textarea name="template_job_posted" class="form-control" rows="2"><?= htmlspecialchars($settings['template_job_posted'] ?? "New Job Opening: {job_title} at {company_name}. Package: {package}. Apply now on TPMS portal!") ?></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold"><i class="fas fa-user-check text-info me-1"></i> Student Shortlisted Template</label>
                            <textarea name="template_student_shortlisted" class="form-control" rows="2"><?= htmlspecialchars($settings['template_student_shortlisted'] ?? "Congratulations {student_name}! You have been shortlisted for {job_title} at {company_name}.") ?></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold"><i class="fas fa-calendar-alt text-warning me-1"></i> Interview Scheduled Template</label>
                            <textarea name="template_interview_scheduled" class="form-control" rows="2"><?= htmlspecialchars($settings['template_interview_scheduled'] ?? "Interview Update: {student_name}, your interview for {job_title} at {company_name} is scheduled on {date} at {time}. Mode: {mode}.") ?></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold"><i class="fas fa-award text-purple me-1"></i> Offer Letter Released Template</label>
                            <textarea name="template_offer_released" class="form-control" rows="2"><?= htmlspecialchars($settings['template_offer_released'] ?? "Congratulations {student_name}! An offer letter has been released for {job_title} at {company_name}. Check TPMS portal for details.") ?></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold"><i class="fas fa-key text-danger me-1"></i> Password Reset Template</label>
                            <textarea name="template_password_reset" class="form-control" rows="2"><?= htmlspecialchars($settings['template_password_reset'] ?? "Your TPMS password reset verification code is: {otp}. This code is valid for 10 minutes. Do not share it with anyone.") ?></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 text-end mb-4">
            <button type="submit" class="btn btn-primary btn-lg px-4">
                <i class="fas fa-save me-2"></i> Save SMS Module Settings
            </button>
        </div>
    </div>
</form>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>
