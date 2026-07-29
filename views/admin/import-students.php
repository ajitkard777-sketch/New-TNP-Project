<?php require_once ROOT_PATH . '/includes/header.php'; ?>

<style>
/* =====================================================
   Import Students Wizard — Scoped Styles
   ===================================================== */

/* Hero Header */
.import-hero {
    background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 60%, #a855f7 100%);
    border-radius: 20px;
    padding: 36px 40px;
    color: #fff;
    margin-bottom: 28px;
    position: relative;
    overflow: hidden;
}
.import-hero::before {
    content: '';
    position: absolute; top: -40px; right: -40px;
    width: 200px; height: 200px;
    background: rgba(255,255,255,0.06);
    border-radius: 50%;
}
.import-hero::after {
    content: '';
    position: absolute; bottom: -60px; left: 40px;
    width: 280px; height: 280px;
    background: rgba(255,255,255,0.04);
    border-radius: 50%;
}
.import-hero-icon { font-size: 3rem; margin-bottom: 12px; }
.import-hero h1 { font-size: 1.8rem; font-weight: 800; margin: 0 0 6px; letter-spacing: -0.5px; }
.import-hero p  { margin: 0; opacity: 0.85; font-size: 0.95rem; }

/* Wizard Steps */
.wizard-steps {
    display: flex;
    gap: 0;
    margin-bottom: 28px;
    border-radius: 14px;
    overflow: hidden;
    background: var(--card-bg, #fff);
    border: 1px solid var(--border-color, #e5e7eb);
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}
.wizard-step {
    flex: 1;
    padding: 16px 20px;
    text-align: center;
    position: relative;
    cursor: default;
    transition: background 0.2s;
}
.wizard-step.active   { background: linear-gradient(135deg, #4f46e5, #7c3aed); color: #fff; }
.wizard-step.done     { background: #f0fdf4; color: #16a34a; }
.wizard-step.inactive { color: var(--text-muted, #9ca3af); }
.wizard-step .step-num {
    display: inline-flex; align-items: center; justify-content: center;
    width: 30px; height: 30px; border-radius: 50%;
    font-weight: 700; font-size: 0.85rem; margin: 0 auto 6px;
    background: rgba(255,255,255,0.2);
}
.wizard-step.inactive .step-num { background: var(--border-color, #e5e7eb); }
.wizard-step.done .step-num     { background: #16a34a; color: #fff; }
.wizard-step .step-label { font-size: 0.78rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }

/* Step connector */
.wizard-step + .wizard-step::before {
    content: '';
    position: absolute; left: 0; top: 50%; transform: translateY(-50%);
    width: 1px; height: 60%; background: var(--border-color, #e5e7eb);
}

/* Info / Feature Cards */
.feature-cards { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 28px; }
.feature-card {
    background: var(--card-bg, #fff);
    border: 1px solid var(--border-color, #e5e7eb);
    border-radius: 14px; padding: 20px 18px;
    display: flex; align-items: flex-start; gap: 14px;
    transition: box-shadow 0.2s, transform 0.2s;
}
.feature-card:hover { box-shadow: 0 6px 20px rgba(79,70,229,0.1); transform: translateY(-2px); }
.feature-card-icon {
    width: 44px; height: 44px; border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.2rem; flex-shrink: 0;
}
.feature-card-body h6 { font-size: 0.85rem; font-weight: 700; margin: 0 0 4px; }
.feature-card-body p  { font-size: 0.78rem; color: var(--text-muted, #9ca3af); margin: 0; line-height: 1.5; }

/* Upload Zone */
.upload-zone {
    border: 2.5px dashed #a5b4fc;
    border-radius: 16px;
    padding: 56px 40px;
    text-align: center;
    background: linear-gradient(135deg, #f8f7ff 0%, #faf5ff 100%);
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
}
.upload-zone:hover,
.upload-zone.dragover {
    border-color: #4f46e5;
    background: linear-gradient(135deg, #f0eeff 0%, #f5f3ff 100%);
    box-shadow: 0 0 0 4px rgba(79,70,229,0.08);
    transform: scale(1.005);
}
.upload-zone .upload-icon {
    font-size: 3.5rem; margin-bottom: 16px;
    animation: float 3s ease-in-out infinite;
}
@keyframes float {
    0%, 100% { transform: translateY(0); }
    50%       { transform: translateY(-6px); }
}
.upload-zone h4 { font-size: 1.1rem; font-weight: 700; margin-bottom: 8px; color: #4f46e5; }
.upload-zone p  { color: var(--text-muted, #9ca3af); font-size: 0.88rem; margin-bottom: 20px; }
.upload-zone .file-types { display: flex; justify-content: center; gap: 10px; flex-wrap: wrap; }
.file-type-badge {
    background: rgba(79,70,229,0.08); color: #4f46e5;
    border: 1px solid rgba(79,70,229,0.2);
    border-radius: 20px; padding: 4px 14px; font-size: 0.75rem; font-weight: 600;
}
#importFileInput { position: absolute; inset: 0; opacity: 0; cursor: pointer; }

/* Selected File Preview */
.file-preview {
    display: none;
    background: var(--card-bg, #fff);
    border: 1px solid #d1fae5;
    border-left: 4px solid #10b981;
    border-radius: 12px;
    padding: 16px 20px;
    margin-top: 16px;
    align-items: center; gap: 14px;
}
.file-preview.show { display: flex; }
.file-preview-icon { font-size: 2rem; }
.file-preview-info h6 { margin: 0 0 2px; font-size: 0.9rem; font-weight: 700; }
.file-preview-info small { color: var(--text-muted, #9ca3af); font-size: 0.8rem; }

/* Progress Bar */
.import-progress-wrap {
    display: none;
    background: var(--card-bg, #fff);
    border: 1px solid var(--border-color, #e5e7eb);
    border-radius: 14px; padding: 28px 28px;
    margin-top: 20px; text-align: center;
}
.import-progress-wrap.show { display: block; }
.progress-ring { font-size: 3rem; margin-bottom: 12px; animation: spin 2s linear infinite; display: inline-block; }
@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
.progress-bar-wrap { height: 10px; background: var(--border-color, #e5e7eb); border-radius: 50px; overflow: hidden; margin: 16px 0 8px; }
.progress-bar-fill { height: 100%; background: linear-gradient(90deg, #4f46e5, #7c3aed, #a855f7); border-radius: 50px; transition: width 0.6s ease; animation: shimmer 2s infinite; background-size: 200% 100%; }
@keyframes shimmer { 0% { background-position: -200% 0; } 100% { background-position: 200% 0; } }
.progress-label { font-size: 0.88rem; color: var(--text-muted, #9ca3af); }

/* Result Summary Cards */
.result-cards {
    display: none;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-top: 24px;
}
.result-cards.show { display: grid; }
.result-card {
    background: var(--card-bg, #fff);
    border: 1px solid var(--border-color, #e5e7eb);
    border-radius: 14px; padding: 20px;
    text-align: center;
    transition: transform 0.2s;
}
.result-card:hover { transform: translateY(-3px); }
.result-card .rc-icon { font-size: 2rem; margin-bottom: 8px; }
.result-card .rc-value {
    font-size: 2.2rem; font-weight: 800; line-height: 1;
    margin-bottom: 4px;
}
.result-card .rc-label { font-size: 0.78rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-muted, #9ca3af); }
.rc-total   .rc-value { color: #4f46e5; }
.rc-success .rc-value { color: #16a34a; }
.rc-skipped .rc-value { color: #d97706; }
.rc-failed  .rc-value { color: #dc2626; }

/* Error / Status Log Table */
.log-section {
    display: none;
    margin-top: 24px;
}
.log-section.show { display: block; }
.log-table { font-size: 0.84rem; }
.log-table .badge-success { background: #d1fae5; color: #065f46; border-radius: 20px; padding: 3px 10px; font-size: 0.75rem; font-weight: 600; }
.log-table .badge-skipped { background: #fef3c7; color: #92400e; border-radius: 20px; padding: 3px 10px; font-size: 0.75rem; font-weight: 600; }
.log-table .badge-failed  { background: #fee2e2; color: #991b1b; border-radius: 20px; padding: 3px 10px; font-size: 0.75rem; font-weight: 600; }
.error-list { list-style: none; padding: 0; margin: 0; }
.error-list li { margin-bottom: 2px; }
.error-list li::before { content: '• '; color: #dc2626; font-weight: 700; }

/* Report download buttons */
.report-actions {
    display: none;
    gap: 12px;
    flex-wrap: wrap;
    margin-top: 20px;
}
.report-actions.show { display: flex; }

/* Column reference table */
.col-ref-table td:first-child { font-weight: 600; font-family: 'Courier New', monospace; font-size: 0.82rem; color: #4f46e5; }
.col-ref-table .required-star { color: #dc2626; font-weight: 700; }

@media (max-width: 768px) {
    .feature-cards { grid-template-columns: 1fr; }
    .result-cards  { grid-template-columns: repeat(2, 1fr); }
    .wizard-steps  { flex-direction: column; }
}
</style>

<!-- ============================================================
     Page Header / Hero
     ============================================================ -->
<div class="import-hero">
    <div class="import-hero-icon">📊</div>
    <h1>Import Students from Excel</h1>
    <p>Bulk import hundreds of student records in one click — validate, deduplicate, create accounts &amp; send welcome emails automatically.</p>
</div>

<!-- ============================================================
     Wizard Progress Steps
     ============================================================ -->
<div class="wizard-steps mb-4" id="wizardSteps">
    <div class="wizard-step active" id="wStep1">
        <div class="step-num">1</div>
        <div class="step-label">Prepare</div>
    </div>
    <div class="wizard-step inactive" id="wStep2">
        <div class="step-num">2</div>
        <div class="step-label">Upload</div>
    </div>
    <div class="wizard-step inactive" id="wStep3">
        <div class="step-num">3</div>
        <div class="step-label">Importing</div>
    </div>
    <div class="wizard-step inactive" id="wStep4">
        <div class="step-num">4</div>
        <div class="step-label">Results</div>
    </div>
</div>

<!-- ============================================================
     Feature Info Cards
     ============================================================ -->
<div class="feature-cards">
    <div class="feature-card">
        <div class="feature-card-icon" style="background:#eff6ff;color:#2563eb;">🔍</div>
        <div class="feature-card-body">
            <h6>Smart Validation</h6>
            <p>Email format, phone digits, CGPA range, DOB format — all validated per row.</p>
        </div>
    </div>
    <div class="feature-card">
        <div class="feature-card-icon" style="background:#f0fdf4;color:#16a34a;">🚫</div>
        <div class="feature-card-body">
            <h6>Duplicate Detection</h6>
            <p>Skips any row with duplicate Email, PRN, or Registration Number.</p>
        </div>
    </div>
    <div class="feature-card">
        <div class="feature-card-icon" style="background:#fdf4ff;color:#9333ea;">📧</div>
        <div class="feature-card-body">
            <h6>Auto Welcome Email</h6>
            <p>Each imported student receives login credentials via email.</p>
        </div>
    </div>
</div>

<!-- ============================================================
     Main Import Card
     ============================================================ -->
<div class="row">
    <!-- Left: Upload + Actions -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">

                <!-- Step 1: Template Download -->
                <div class="d-flex align-items-center justify-content-between mb-4 pb-3" style="border-bottom: 1px solid var(--border-color, #e5e7eb);">
                    <div>
                        <h5 class="mb-1" style="font-weight:700;">Step 1 — Download Sample Template</h5>
                        <p class="text-muted mb-0" style="font-size:0.88rem;">Use our pre-formatted Excel template to avoid column mapping errors.</p>
                    </div>
                    <a href="<?= url('/admin/download-import-template') ?>" class="btn btn-outline-primary btn-sm" id="btnDownloadTemplate" style="white-space:nowrap;">
                        <i class="fas fa-download me-1"></i> Download Template
                    </a>
                </div>

                <!-- Step 2: Upload Zone -->
                <h5 class="mb-3" style="font-weight:700;">Step 2 — Upload Your Excel File</h5>

                <form id="importForm" enctype="multipart/form-data">
                    <?= CsrfMiddleware::tokenField() ?>
                    <input type="hidden" name="MAX_FILE_SIZE" value="10485760">

                    <div class="upload-zone" id="uploadZone">
                        <input type="file" name="import_file" id="importFileInput"
                               accept=".xlsx,.xls,.csv"
                               aria-label="Upload Excel or CSV file">
                        <div class="upload-icon">📂</div>
                        <h4>Drag &amp; Drop your file here</h4>
                        <p>or click anywhere to browse</p>
                        <div class="file-types">
                            <span class="file-type-badge">.XLSX</span>
                            <span class="file-type-badge">.XLS</span>
                            <span class="file-type-badge">.CSV</span>
                            <span class="file-type-badge ms-2" style="background:rgba(239,68,68,0.08);color:#dc2626;border-color:rgba(239,68,68,0.2);">Max 10 MB</span>
                        </div>
                    </div>

                    <!-- File Selected Preview -->
                    <div class="file-preview" id="filePreview">
                        <div class="file-preview-icon">📄</div>
                        <div class="file-preview-info flex-grow-1">
                            <h6 id="previewFileName">file.xlsx</h6>
                            <small id="previewFileSize">0 KB</small>
                        </div>
                        <button type="button" class="btn btn-sm btn-light" id="btnRemoveFile" title="Remove file">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex gap-3 mt-4">
                        <button type="submit" class="btn btn-primary" id="btnImport" disabled
                                style="min-width:160px;background:linear-gradient(135deg,#4f46e5,#7c3aed);border:none;font-weight:700;">
                            <i class="fas fa-upload me-2"></i>Import Students
                        </button>
                        <a href="<?= url('/admin/students') ?>" class="btn btn-light">
                            <i class="fas fa-arrow-left me-1"></i> Cancel
                        </a>
                    </div>
                </form>

                <!-- Import Progress -->
                <div class="import-progress-wrap" id="importProgress">
                    <div class="progress-ring">⚙️</div>
                    <h5 class="mt-3 mb-1" style="font-weight:700;" id="progressTitle">Processing your file…</h5>
                    <p class="text-muted" style="font-size:0.88rem;" id="progressSubtitle">Validating rows, checking duplicates, and creating accounts…</p>
                    <div class="progress-bar-wrap">
                        <div class="progress-bar-fill" id="progressBarFill" style="width:30%"></div>
                    </div>
                    <div class="progress-label" id="progressLabel">Please wait, do not close this page.</div>
                </div>

                <!-- Result Summary Cards -->
                <div class="result-cards" id="resultCards">
                    <div class="result-card rc-total">
                        <div class="rc-icon">📋</div>
                        <div class="rc-value" id="rcTotal">0</div>
                        <div class="rc-label">Total Rows</div>
                    </div>
                    <div class="result-card rc-success">
                        <div class="rc-icon">✅</div>
                        <div class="rc-value" id="rcImported">0</div>
                        <div class="rc-label">Imported</div>
                    </div>
                    <div class="result-card rc-skipped">
                        <div class="rc-icon">⏭️</div>
                        <div class="rc-value" id="rcSkipped">0</div>
                        <div class="rc-label">Skipped</div>
                    </div>
                    <div class="result-card rc-failed">
                        <div class="rc-icon">❌</div>
                        <div class="rc-value" id="rcFailed">0</div>
                        <div class="rc-label">Failed</div>
                    </div>
                </div>

                <!-- Report Download Actions -->
                <div class="report-actions" id="reportActions">
                    <a href="<?= url('/admin/download-import-report?type=success') ?>" class="btn btn-success" id="btnDlSuccess">
                        <i class="fas fa-file-csv me-2"></i>Download Success Report
                    </a>
                    <a href="<?= url('/admin/download-import-report?type=failed') ?>" class="btn btn-danger" id="btnDlFailed">
                        <i class="fas fa-file-csv me-2"></i>Download Failed Report
                    </a>
                    <a href="<?= url('/admin/students') ?>" class="btn btn-primary">
                        <i class="fas fa-users me-2"></i>View All Students
                    </a>
                </div>

                <!-- Detailed Row Log -->
                <div class="log-section" id="logSection">
                    <hr class="my-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h5 class="mb-0" style="font-weight:700;">📝 Detailed Import Log</h5>
                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-light active" id="logFilterAll" onclick="filterLog('all')">All</button>
                            <button class="btn btn-sm btn-light" id="logFilterSuccess" onclick="filterLog('success')"><span class="text-success">✓</span> Imported</button>
                            <button class="btn btn-sm btn-light" id="logFilterSkipped" onclick="filterLog('skipped')"><span class="text-warning">⏭</span> Skipped</button>
                            <button class="btn btn-sm btn-light" id="logFilterFailed" onclick="filterLog('failed')"><span class="text-danger">✗</span> Failed</button>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table log-table mb-0">
                            <thead>
                                <tr>
                                    <th style="width:70px;">Row</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th style="width:100px;">Status</th>
                                    <th>Details</th>
                                </tr>
                            </thead>
                            <tbody id="logTableBody"></tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Right: Column Reference -->
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-body">
                <h6 class="mb-3" style="font-weight:700;font-size:0.9rem;text-transform:uppercase;letter-spacing:0.5px;color:var(--text-muted,#6b7280);">
                    📋 Excel Column Reference
                </h6>
                <div class="table-responsive">
                    <table class="table col-ref-table table-sm mb-0" style="font-size:0.8rem;">
                        <thead>
                            <tr>
                                <th>Column Name</th>
                                <th>Required?</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td>Full Name</td><td><span class="required-star">★</span> Required</td></tr>
                            <tr><td>Email</td><td><span class="required-star">★</span> Required</td></tr>
                            <tr><td>PRN/Roll Number</td><td><span class="required-star">★</span> Required</td></tr>
                            <tr><td>Branch</td><td><span class="required-star">★</span> Required</td></tr>
                            <tr><td>Phone</td><td>Optional</td></tr>
                            <tr><td>Registration Number</td><td>Optional</td></tr>
                            <tr><td>Semester</td><td>Optional</td></tr>
                            <tr><td>Passing Year</td><td>Optional</td></tr>
                            <tr><td>CGPA</td><td>Optional</td></tr>
                            <tr><td>Gender</td><td>Optional</td></tr>
                            <tr><td>Date of Birth</td><td>Optional</td></tr>
                            <tr><td>Skills</td><td>Optional</td></tr>
                            <tr><td>Address</td><td>Optional</td></tr>
                            <tr><td>LinkedIn</td><td>Optional</td></tr>
                            <tr><td>GitHub</td><td>Optional</td></tr>
                            <tr><td>Portfolio</td><td>Optional</td></tr>
                            <tr><td>Parent Name</td><td>Optional</td></tr>
                            <tr><td>Parent Phone</td><td>Optional</td></tr>
                        </tbody>
                    </table>
                </div>
                <p class="mt-2 mb-0" style="font-size:0.75rem;color:var(--text-muted,#9ca3af);">
                    <span style="color:#dc2626;font-weight:700;">★</span> Required fields — rows missing these will be marked as Failed.
                </p>
            </div>
        </div>

        <!-- Validation Rules -->
        <div class="card mb-4">
            <div class="card-body">
                <h6 class="mb-3" style="font-weight:700;font-size:0.9rem;text-transform:uppercase;letter-spacing:0.5px;color:var(--text-muted,#6b7280);">
                    🔒 Validation Rules
                </h6>
                <ul class="list-unstyled mb-0" style="font-size:0.82rem;line-height:2;">
                    <li>✉️ Email must be valid format</li>
                    <li>📱 Phone must be 10 digits</li>
                    <li>📊 CGPA must be 0.00 – 10.00</li>
                    <li>📅 DOB: YYYY-MM-DD or DD/MM/YYYY</li>
                    <li>🗓️ Passing Year: 4-digit (2000–2050)</li>
                    <li>🚫 Duplicate Email → Skipped</li>
                    <li>🚫 Duplicate PRN → Skipped</li>
                    <li>🚫 Duplicate Reg No → Skipped</li>
                    <li>📁 Max file size: 10 MB</li>
                    <li>📎 Formats: .xlsx, .xls, .csv only</li>
                </ul>
            </div>
        </div>

        <!-- What Happens on Import -->
        <div class="card">
            <div class="card-body">
                <h6 class="mb-3" style="font-weight:700;font-size:0.9rem;text-transform:uppercase;letter-spacing:0.5px;color:var(--text-muted,#6b7280);">
                    ⚡ What Happens on Import
                </h6>
                <ol class="mb-0" style="font-size:0.82rem;line-height:2;padding-left:18px;">
                    <li>File is parsed and validated</li>
                    <li>Duplicate rows are skipped</li>
                    <li>Student account created (role: student)</li>
                    <li>Unique Student ID generated</li>
                    <li>Secure temp password generated</li>
                    <li>Account set to Active status</li>
                    <li>Welcome email sent with credentials</li>
                    <li>In-app notification created</li>
                    <li>Import logged in Activity Logs</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    'use strict';

    // ── Elements ────────────────────────────────────────
    const form            = document.getElementById('importForm');
    const fileInput       = document.getElementById('importFileInput');
    const uploadZone      = document.getElementById('uploadZone');
    const filePreview     = document.getElementById('filePreview');
    const previewName     = document.getElementById('previewFileName');
    const previewSize     = document.getElementById('previewFileSize');
    const btnRemove       = document.getElementById('btnRemoveFile');
    const btnImport       = document.getElementById('btnImport');
    const importProgress  = document.getElementById('importProgress');
    const progressBarFill = document.getElementById('progressBarFill');
    const progressTitle   = document.getElementById('progressTitle');
    const progressSubtitle = document.getElementById('progressSubtitle');
    const progressLabel   = document.getElementById('progressLabel');
    const resultCards     = document.getElementById('resultCards');
    const reportActions   = document.getElementById('reportActions');
    const logSection      = document.getElementById('logSection');
    const logTableBody    = document.getElementById('logTableBody');

    const rcTotal    = document.getElementById('rcTotal');
    const rcImported = document.getElementById('rcImported');
    const rcSkipped  = document.getElementById('rcSkipped');
    const rcFailed   = document.getElementById('rcFailed');

    // Wizard steps
    const steps = [
        document.getElementById('wStep1'),
        document.getElementById('wStep2'),
        document.getElementById('wStep3'),
        document.getElementById('wStep4'),
    ];

    let allResults = [];

    // ── Wizard helpers ──────────────────────────────────
    function setWizardStep(active) {
        steps.forEach((s, i) => {
            s.className = 'wizard-step ' + (i < active ? 'done' : (i === active ? 'active' : 'inactive'));
            const numEl = s.querySelector('.step-num');
            if (i < active) numEl.innerHTML = '✓';
            else numEl.textContent = i + 1;
        });
    }

    // ── File helpers ────────────────────────────────────
    function formatBytes(bytes) {
        if (bytes < 1024) return bytes + ' B';
        if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
        return (bytes / 1048576).toFixed(2) + ' MB';
    }

    function showFilePreview(file) {
        previewName.textContent = file.name;
        previewSize.textContent = formatBytes(file.size) + ' — ' + file.type || 'unknown type';
        filePreview.classList.add('show');
        btnImport.disabled = false;
        setWizardStep(1);
    }

    function clearFile() {
        fileInput.value = '';
        filePreview.classList.remove('show');
        btnImport.disabled = true;
        setWizardStep(0);
    }

    // ── File Input Events ────────────────────────────────
    fileInput.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            showFilePreview(this.files[0]);
        }
    });

    btnRemove.addEventListener('click', function(e) {
        e.preventDefault();
        clearFile();
    });

    // ── Drag & Drop ──────────────────────────────────────
    uploadZone.addEventListener('dragover', function(e) {
        e.preventDefault();
        this.classList.add('dragover');
    });
    uploadZone.addEventListener('dragleave', function() {
        this.classList.remove('dragover');
    });
    uploadZone.addEventListener('drop', function(e) {
        e.preventDefault();
        this.classList.remove('dragover');
        const files = e.dataTransfer.files;
        if (files && files[0]) {
            const dt = new DataTransfer();
            dt.items.add(files[0]);
            fileInput.files = dt.files;
            showFilePreview(files[0]);
        }
    });

    // ── Progress Simulation ──────────────────────────────
    function animateProgress(start, end, durationMs) {
        const startTime = Date.now();
        function tick() {
            const elapsed = Date.now() - startTime;
            const frac = Math.min(elapsed / durationMs, 1);
            const current = start + (end - start) * frac;
            progressBarFill.style.width = current + '%';
            if (frac < 1) requestAnimationFrame(tick);
        }
        requestAnimationFrame(tick);
    }

    // ── Animated Counter ─────────────────────────────────
    function animateCounter(el, target) {
        let start = 0;
        const step = Math.ceil(target / 40);
        const interval = setInterval(() => {
            start = Math.min(start + step, target);
            el.textContent = start;
            if (start >= target) clearInterval(interval);
        }, 30);
    }

    // ── Form Submit → AJAX Import ────────────────────────
    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        if (!fileInput.files || !fileInput.files[0]) {
            alert('Please select a file first.');
            return;
        }

        const file = fileInput.files[0];
        const ext  = file.name.split('.').pop().toLowerCase();
        if (!['xlsx', 'xls', 'csv'].includes(ext)) {
            alert('Only .xlsx, .xls, and .csv files are allowed.');
            return;
        }
        if (file.size > 10 * 1024 * 1024) {
            alert('File size exceeds 10 MB. Please use a smaller file.');
            return;
        }

        // ── Show progress ──
        setWizardStep(2);
        importProgress.classList.add('show');
        resultCards.classList.remove('show');
        reportActions.classList.remove('show');
        logSection.classList.remove('show');
        btnImport.disabled = true;
        btnImport.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Importing…';
        uploadZone.style.pointerEvents = 'none';
        animateProgress(5, 80, 3000);

        const progressMessages = [
            { title: 'Parsing file…', sub: 'Reading rows and column headers…' },
            { title: 'Validating data…', sub: 'Checking emails, phones, and required fields…' },
            { title: 'Checking duplicates…', sub: 'Comparing against existing records in database…' },
            { title: 'Creating accounts…', sub: 'Generating student IDs and temporary passwords…' },
            { title: 'Sending emails…', sub: 'Dispatching welcome emails to imported students…' },
        ];
        let msgIdx = 0;
        const msgInterval = setInterval(() => {
            if (msgIdx < progressMessages.length - 1) {
                const m = progressMessages[msgIdx++];
                progressTitle.textContent = m.title;
                progressSubtitle.textContent = m.sub;
            }
        }, 1200);

        // ── Build FormData ──
        const formData = new FormData(this);

        try {
            const response = await fetch('<?= url('/admin/import-students') ?>', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            });

            clearInterval(msgInterval);
            animateProgress(80, 100, 400);

            const data = await response.json();

            setTimeout(() => {
                importProgress.classList.remove('show');

                if (!data.success) {
                    setWizardStep(1);
                    uploadZone.style.pointerEvents = '';
                    btnImport.disabled = false;
                    btnImport.innerHTML = '<i class="fas fa-upload me-2"></i>Import Students';
                    showAlert('danger', '❌ ' + (data.message || 'Import failed. Please try again.'));
                    return;
                }

                // ── Success → Show results ──
                setWizardStep(3);
                resultCards.classList.add('show');
                reportActions.classList.add('show');

                animateCounter(rcTotal, data.totalRows || 0);
                animateCounter(rcImported, data.imported || 0);
                animateCounter(rcSkipped, data.skipped || 0);
                animateCounter(rcFailed, data.failed || 0);

                // Show log
                allResults = data.results || [];
                renderLog(allResults);

                if (allResults.length > 0) {
                    logSection.classList.add('show');
                }

                // Show success flash
                if (data.imported > 0) {
                    showAlert('success', '🎉 Import complete! ' + data.imported + ' student(s) imported successfully.');
                } else if (data.skipped > 0 && data.failed === 0) {
                    showAlert('warning', '⏭ All rows were skipped (duplicates). No new students were imported.');
                }

                btnImport.innerHTML = '<i class="fas fa-check me-2"></i>Import Complete';
                uploadZone.style.pointerEvents = '';

            }, 600);

        } catch (err) {
            clearInterval(msgInterval);
            importProgress.classList.remove('show');
            setWizardStep(1);
            uploadZone.style.pointerEvents = '';
            btnImport.disabled = false;
            btnImport.innerHTML = '<i class="fas fa-upload me-2"></i>Import Students';
            showAlert('danger', '❌ Network error. Please check your connection and try again.');
        }
    });

    // ── Render log table rows ────────────────────────────
    function renderLog(results) {
        logTableBody.innerHTML = '';

        if (results.length === 0) {
            logTableBody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4">No rows to display.</td></tr>';
            return;
        }

        results.forEach(r => {
            const tr = document.createElement('tr');
            tr.setAttribute('data-status', r.status);

            let statusBadge = '';
            if (r.status === 'success') {
                statusBadge = '<span class="badge-success">Imported</span>';
            } else if (r.status === 'skipped') {
                statusBadge = '<span class="badge-skipped">Skipped</span>';
            } else {
                statusBadge = '<span class="badge-failed">Failed</span>';
            }

            const errorsHtml = r.errors && r.errors.length > 0
                ? '<ul class="error-list">' + r.errors.map(e => '<li>' + escHtml(e) + '</li>').join('') + '</ul>'
                : (r.studentId ? '🆔 Student ID: <strong>' + escHtml(r.studentId) + '</strong>' : '<span class="text-muted">—</span>');

            tr.innerHTML = `
                <td class="text-center fw-bold">${r.row}</td>
                <td>${escHtml(r.name || '—')}</td>
                <td><small>${escHtml(r.email || '—')}</small></td>
                <td>${statusBadge}</td>
                <td>${errorsHtml}</td>
            `;
            logTableBody.appendChild(tr);
        });
    }

    // ── Log Filter ───────────────────────────────────────
    window.filterLog = function(status) {
        // Update button states
        document.querySelectorAll('[id^="logFilter"]').forEach(b => b.classList.remove('active'));
        const activeBtn = document.getElementById('logFilter' + status.charAt(0).toUpperCase() + status.slice(1));
        if (activeBtn) activeBtn.classList.add('active');

        const filtered = status === 'all' ? allResults : allResults.filter(r => r.status === status);
        renderLog(filtered);
    };

    // ── Alert flash helper ───────────────────────────────
    function showAlert(type, msg) {
        const existing = document.getElementById('importAlertBox');
        if (existing) existing.remove();
        const div = document.createElement('div');
        div.id = 'importAlertBox';
        div.className = 'alert alert-' + type + ' alert-dismissible fade show mt-3';
        div.innerHTML = msg + '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
        form.parentNode.insertBefore(div, form.nextSibling);
        div.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    // ── HTML escaping ────────────────────────────────────
    function escHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

})();
</script>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>
