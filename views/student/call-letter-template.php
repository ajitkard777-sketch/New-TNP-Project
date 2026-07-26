<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Interview Call Letter — <?= htmlspecialchars($interview['company_name']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f8fafc; font-family: 'Inter', -apple-system, sans-serif; color: #1e293b; }
        .call-letter-card { background: #fff; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); max-width: 800px; margin: 40px auto; padding: 40px; position: relative; }
        .header-brand { border-bottom: 2px solid #e2e8f0; padding-bottom: 24px; margin-bottom: 30px; }
        .company-logo { max-height: 60px; max-width: 180px; object-fit: contain; }
        .watermark { position: absolute; top: 40%; left: 50%; transform: translate(-50%, -50%) rotate(-30deg); font-size: 5rem; color: rgba(37, 99, 235, 0.04); font-weight: 800; text-transform: uppercase; pointer-events: none; white-space: nowrap; }
        .info-table td { padding: 8px 12px; }
        @media print {
            .no-print { display: none !important; }
            body { background: #fff; }
            .call-letter-card { box-shadow: none; margin: 0; padding: 20px; max-width: 100%; }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="no-print d-flex justify-content-between align-items-center my-4 max-w-800 mx-auto" style="max-width: 800px;">
        <a href="<?= url('/student/interviews') ?>" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i> Back to Interviews</a>
        <button onclick="window.print()" class="btn btn-primary btn-sm"><i class="fas fa-print me-1"></i> Print / Save as PDF</button>
    </div>

    <div class="call-letter-card">
        <div class="watermark">OFFICIAL CALL LETTER</div>

        <div class="header-brand d-flex justify-content-between align-items-center">
            <div>
                <h4 class="fw-bold text-primary mb-1">TRAINING & PLACEMENT CELL</h4>
                <div class="text-secondary small fw-medium">Official Interview Call Letter</div>
            </div>
            <div>
                <?php if (!empty($interview['company_logo'])): ?>
                    <img src="<?= uploadUrl('logos/' . $interview['company_logo']) ?>" alt="Logo" class="company-logo" onerror="this.style.display='none'">
                <?php else: ?>
                    <h3 class="fw-bold text-dark mb-0"><?= htmlspecialchars($interview['company_name']) ?></h3>
                <?php endif; ?>
            </div>
        </div>

        <div class="mb-4">
            <div class="row">
                <div class="col-6">
                    <small class="text-muted text-uppercase fw-semibold" style="font-size:0.75rem;">Candidate Details</small>
                    <div class="fw-bold text-dark fs-5 mt-1"><?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?></div>
                    <div class="text-secondary small">Enrollment: <strong><?= htmlspecialchars($student['enrollment_no'] ?? 'N/A') ?></strong></div>
                    <div class="text-secondary small">Branch: <strong><?= htmlspecialchars($student['branch'] ?? 'Engineering') ?></strong></div>
                </div>
                <div class="col-6 text-end">
                    <small class="text-muted text-uppercase fw-semibold" style="font-size:0.75rem;">Date of Issue</small>
                    <div class="fw-bold text-dark mt-1"><?= date('F d, Y') ?></div>
                    <div class="text-secondary small">Ref: <strong>TNP-CALL-<?= str_pad($interview['id'], 5, '0', STR_PAD_LEFT) ?></strong></div>
                </div>
            </div>
        </div>

        <div class="alert alert-primary bg-primary-subtle border-primary-subtle rounded-3 mb-4">
            <div class="d-flex align-items-center">
                <i class="fas fa-bullhorn text-primary fs-4 me-3"></i>
                <div>
                    <strong class="d-block text-primary">Interview Invitation</strong>
                    <span class="small text-secondary">You have been shortlisted for the <strong><?= htmlspecialchars($interview['round'] ?? 'Technical Round') ?></strong> interview at <strong><?= htmlspecialchars($interview['company_name']) ?></strong> for the post of <strong><?= htmlspecialchars($interview['job_title']) ?></strong>.</span>
                </div>
            </div>
        </div>

        <div class="card border-0 bg-light rounded-3 mb-4">
            <div class="card-body">
                <h6 class="fw-bold text-dark mb-3"><i class="fas fa-calendar-check text-primary me-2"></i>Schedule & Venue Details</h6>
                <div class="row g-3">
                    <div class="col-sm-6">
                        <div class="small text-muted">Interview Round</div>
                        <div class="fw-semibold text-dark"><?= htmlspecialchars($interview['round'] ?? 'Round 1') ?></div>
                    </div>
                    <div class="col-sm-6">
                        <div class="small text-muted">Interview Type / Mode</div>
                        <div class="fw-semibold text-dark"><span class="badge bg-<?= $interview['mode'] === 'online' ? 'info' : 'primary' ?>"><?= ucfirst($interview['mode'] ?? 'offline') ?></span></div>
                    </div>
                    <div class="col-sm-6">
                        <div class="small text-muted">Date & Time</div>
                        <div class="fw-semibold text-dark"><i class="far fa-clock me-1 text-primary"></i><?= formatDate($interview['interview_date']) ?> at <?= date('h:i A', strtotime($interview['interview_time'])) ?></div>
                    </div>
                    <div class="col-sm-6">
                        <div class="small text-muted"><?= $interview['mode'] === 'online' ? 'Meeting Link' : 'Venue' ?></div>
                        <div class="fw-semibold text-dark">
                            <?php if ($interview['mode'] === 'online' && !empty($interview['meeting_link'])): ?>
                                <a href="<?= htmlspecialchars($interview['meeting_link']) ?>" target="_blank" class="text-primary text-break"><?= htmlspecialchars($interview['meeting_link']) ?></a>
                            <?php else: ?>
                                <?= htmlspecialchars($interview['venue'] ?? 'Campus Placement Cell / Seminar Hall') ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if (!empty($interview['instructions']) || !empty($interview['required_documents'])): ?>
        <div class="row g-3 mb-4">
            <?php if (!empty($interview['instructions'])): ?>
            <div class="col-md-6">
                <div class="p-3 border rounded-3 h-100 bg-white">
                    <h6 class="fw-bold text-dark mb-2"><i class="fas fa-list-check text-warning me-2"></i>Instructions</h6>
                    <p class="small text-secondary mb-0"><?= nl2br(htmlspecialchars($interview['instructions'])) ?></p>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($interview['required_documents'])): ?>
            <div class="col-md-6">
                <div class="p-3 border rounded-3 h-100 bg-white">
                    <h6 class="fw-bold text-dark mb-2"><i class="fas fa-folder-open text-info me-2"></i>Required Documents</h6>
                    <p class="small text-secondary mb-0"><?= nl2br(htmlspecialchars($interview['required_documents'])) ?></p>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <div class="pt-4 border-top d-flex justify-content-between align-items-end mt-5">
            <div>
                <small class="text-muted d-block mb-1">Generated Systematically</small>
                <div class="small fw-semibold text-secondary">T&P Officer Signature</div>
            </div>
            <div class="text-end">
                <div class="fw-bold text-primary mb-1">Placement Cell Office</div>
                <div class="small text-muted">Training & Placement Department</div>
            </div>
        </div>

    </div>
</div>

</body>
</html>
