<?php require_once ROOT_PATH . '/includes/header.php'; ?>

<div class="content-header mb-4">
    <div>
        <h1 class="page-title"><i class="fas fa-sparkles text-primary me-2"></i>AI Job Recommendations</h1>
        <p class="subtitle">Personalized job matching powered by intelligent rule engine (Skills, Branch, CGPA &amp; Location)</p>
    </div>
    <div>
        <a href="<?= url('/student/profile/edit#skills-section') ?>" class="btn btn-outline-primary btn-sm fw-semibold">
            <i class="fas fa-sliders-h me-1"></i> Update Skills &amp; Preferences
        </a>
    </div>
</div>

<?php if (empty($recommendedJobs)): ?>
<div class="card shadow-sm border-0">
    <div class="card-body text-center p-5">
        <i class="fas fa-robot text-warning mb-3" style="font-size:3.5rem; opacity:0.6;"></i>
        <h5 class="fw-bold">No AI Recommendations Yet</h5>
        <p class="text-muted small max-w-md mx-auto mb-3" style="max-width:480px;">
            To generate high-precision AI job recommendations, please complete your student profile details including skills, branch, CGPA, and preferred job locations.
        </p>
        <a href="<?= url('/student/profile/edit') ?>" class="btn btn-primary btn-sm fw-semibold">Update Profile Now</a>
    </div>
</div>
<?php else: ?>

<div class="row g-4 mb-4">
    <?php foreach ($recommendedJobs as $rJob): ?>
    <?php
        $eligibility = checkStudentJobEligibility($rJob, $student);
        $isEligible = $eligibility['is_eligible'];
    ?>
    <div class="col-xl-6 col-lg-12">
        <div class="card shadow-sm border-0 h-100 animate-fade-in-up hover-lift border-start border-4 <?= $isEligible ? 'border-primary' : 'border-warning' ?>">
            <div class="card-body p-4 d-flex flex-column">
                
                <!-- Match Score Badge Bar -->
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="badge <?= $rJob['match_badge_class'] ?> px-3 py-2 fw-bold" style="font-size:0.85rem;">
                        <i class="fas fa-fire me-1"></i> <?= $rJob['match_score'] ?>% Match — <?= $rJob['match_label'] ?>
                    </span>
                    
                    <?php if ($isEligible): ?>
                        <span class="badge bg-success-soft text-success fw-bold px-2 py-1" style="font-size:0.75rem;"><i class="fas fa-check-circle me-1"></i>Eligible</span>
                    <?php else: ?>
                        <span class="badge bg-danger-soft text-danger fw-bold px-2 py-1" style="font-size:0.75rem;"><i class="fas fa-times-circle me-1"></i>Not Eligible</span>
                    <?php endif; ?>
                </div>

                <!-- Job Header: Logo, Title, Company Name -->
                <div class="d-flex align-items-start gap-3 mb-3">
                    <img src="<?= $rJob['logo'] ? uploadUrl('company/' . $rJob['logo']) : asset('images/default-avatar.png') ?>" 
                         alt="" class="rounded border p-1 bg-white shadow-sm flex-shrink-0" 
                         style="width:52px; height:52px; object-fit:cover;"
                         onerror="this.src='<?= asset('images/default-avatar.png') ?>'">
                    
                    <div class="flex-grow-1 min-w-0">
                        <h5 class="fw-bold text-dark mb-0 text-truncate"><?= htmlspecialchars($rJob['title']) ?></h5>
                        <div class="fw-semibold text-primary small mb-1"><?= htmlspecialchars($rJob['company_name']) ?></div>
                        <div class="text-muted small">
                            <span class="me-2"><i class="fas fa-map-marker-alt text-danger me-1"></i><?= htmlspecialchars($rJob['location'] ?: 'N/A') ?></span>
                            <span class="text-success fw-bold"><i class="fas fa-money-bill-wave me-1"></i><?= formatSalaryRange($rJob['salary_min'], $rJob['salary_max']) ?></span>
                        </div>
                    </div>
                </div>

                <!-- AI Recommendation Reason Callout -->
                <div class="p-3 rounded bg-light border-start border-primary border-3 mb-3 small text-secondary">
                    <i class="fas fa-lightbulb text-warning me-1"></i>
                    <strong>Match Reason:</strong> <?= htmlspecialchars($rJob['match_explanation']) ?>
                </div>

                <!-- Footer Actions: Chat Recruiter, Apply Button -->
                <div class="d-flex align-items-center justify-content-between border-top pt-3 mt-auto">
                    <?php if (!empty($rJob['company_user_id'])): ?>
                    <a href="<?= url('/student/messages?partner=' . $rJob['company_user_id']) ?>" class="btn btn-sm btn-outline-primary fw-semibold">
                        <i class="fas fa-comments me-1"></i>Chat HR
                    </a>
                    <?php else: ?>
                    <div></div>
                    <?php endif; ?>

                    <div>
                        <?php if (!empty($rJob['has_applied'])): ?>
                        <span class="badge bg-success px-3 py-2 fw-semibold" style="font-size:0.85rem;"><i class="fas fa-check-circle me-1"></i>Applied</span>
                        <?php elseif ($isEligible): ?>
                        <a href="<?= url('/student/apply/' . $rJob['id']) ?>" class="btn btn-primary btn-sm px-4 fw-semibold shadow-sm" data-confirm="Submit application for <?= htmlspecialchars($rJob['title']) ?>?">
                            <i class="fas fa-paper-plane me-1"></i> Apply Now
                        </a>
                        <?php else: ?>
                        <button class="btn btn-light btn-sm text-muted border" disabled><i class="fas fa-ban me-1 text-danger"></i> Ineligible</button>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php endif; ?>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>
