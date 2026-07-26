<?php
require_once ROOT_PATH . '/includes/header.php';
$savedAccentColor = $student['resume_accent_color'] ?? '#2563eb';
?>

<style>
/* CSS Variables & Dynamic Styling */
:root {
    --resume-accent: <?= htmlspecialchars($savedAccentColor) ?>;
    --resume-font: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
}

/* Print Rules */
@media print {
    body * { visibility: hidden; }
    #resumePreviewCanvas, #resumePreviewCanvas * {
        visibility: visible;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    #resumePreviewCanvas {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        margin: 0;
        padding: 0 !important;
        box-shadow: none !important;
        border: none !important;
    }
    .d-print-none { display: none !important; }
}

/* Preset Color Buttons */
.color-picker-btn {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    border: 2px solid #fff;
    box-shadow: 0 0 0 1px #cbd5e1;
    cursor: pointer;
    transition: transform 0.2s, box-shadow 0.2s;
}
.color-picker-btn:hover, .color-picker-btn.active {
    transform: scale(1.2);
    box-shadow: 0 0 0 2px var(--resume-accent, #2563eb);
}

/* Resume Preview Paper Base */
.resume-paper {
    background: #ffffff;
    min-height: 1000px;
    color: #1e293b;
    font-family: var(--resume-font);
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.08), 0 8px 10px -6px rgba(0, 0, 0, 0.04);
    transition: all 0.3s ease;
    position: relative;
    padding: 40px;
}

/* Accordion Controls */
.accordion-button:not(.collapsed) {
    background-color: #f1f5f9;
    color: var(--tpms-primary, #2563eb);
}

/* TEMPLATE SPECIFIC STYLES */
/* 1. Modern Placement (Default) */
.tpl-modern .resume-section-title {
    color: var(--resume-accent);
    border-bottom: 2px solid var(--resume-accent);
    padding-bottom: 4px;
    margin-bottom: 12px;
    text-transform: uppercase;
    font-weight: 700;
    font-size: 0.95rem;
    letter-spacing: 0.5px;
}

/* 2. Classic Corporate */
.tpl-classic {
    font-family: 'Times New Roman', Times, serif !important;
}
.tpl-classic .resume-section-title {
    color: #0f172a;
    border-bottom: 1px solid #0f172a;
    text-align: center;
    text-transform: uppercase;
    font-weight: 700;
    margin-bottom: 14px;
}

/* 3. ATS Professional */
.tpl-ats-pro {
    font-family: 'Arial', sans-serif !important;
    padding: 30px !important;
}
.tpl-ats-pro .resume-section-title {
    color: #111827;
    border-bottom: 1px solid #9ca3af;
    font-weight: 800;
    text-transform: uppercase;
    font-size: 0.9rem;
}

/* 4. Executive Resume */
.tpl-executive {
    border-top: 10px solid var(--resume-accent);
}
.tpl-executive .header-box {
    background: #f8fafc;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 20px;
}

/* 5. Minimal Clean */
.tpl-minimal {
    padding: 32px !important;
}
.tpl-minimal .resume-section-title {
    color: var(--resume-accent);
    font-weight: 600;
    letter-spacing: 1px;
}

/* 6. Blue Professional */
.tpl-blue-pro .resume-header-bg {
    background: var(--resume-accent);
    color: #ffffff !important;
    padding: 24px;
    border-radius: 8px;
    margin-bottom: 20px;
}
.tpl-blue-pro .resume-header-bg * { color: #ffffff !important; }

/* 7. Dark Professional */
.tpl-dark-pro {
    background: #0f172a !important;
    color: #f8fafc !important;
}
.tpl-dark-pro .resume-section-title {
    color: var(--resume-accent);
    border-bottom: 1px solid #334155;
}
.tpl-dark-pro .text-muted, .tpl-dark-pro .text-secondary {
    color: #94a3b8 !important;
}

/* 8. Creative Modern */
.tpl-creative {
    border-left: 12px solid var(--resume-accent);
}

/* 9. Fresher Campus Placement */
.tpl-fresher .badge-skill {
    background: var(--resume-accent) !important;
    color: #fff !important;
}

/* 10. Software Engineer */
.tpl-software {
    font-family: 'Fira Code', 'Courier New', monospace !important;
}

/* 11. Data Science */
.tpl-datascience .metric-badge {
    border: 1px solid var(--resume-accent);
    color: var(--resume-accent);
    font-weight: 700;
}

/* 12. UI/UX Designer */
.tpl-uiux .skill-tag {
    border-radius: 20px;
    background: #f1f5f9;
    padding: 4px 12px;
}

/* 13. MBA Resume */
.tpl-mba .resume-section-title {
    border-left: 4px solid var(--resume-accent);
    padding-left: 8px;
}

/* 14. Government Job Resume */
.tpl-govt {
    font-family: 'Georgia', serif !important;
}

/* 15. Academic CV */
.tpl-academic .pub-item {
    font-style: italic;
}

/* 16. Research CV */
.tpl-research {
    border: 1px solid #e2e8f0;
}

/* 17. Internship Resume */
.tpl-internship .highlight-box {
    background: #f0f9ff;
    border-left: 3px solid var(--resume-accent);
    padding: 10px;
}

/* 18. One Page ATS */
.tpl-onepage {
    padding: 20px !important;
    font-size: 0.85rem !important;
}

/* 19. Two Column Professional */
.tpl-twocolumn .left-sidebar {
    background: #f8fafc;
    border-right: 1px solid #e2e8f0;
    padding: 20px;
}

/* 20. International Resume */
.tpl-international .flag-header {
    border-bottom: 3px double var(--resume-accent);
}
</style>

<div class="content-header mb-4 d-print-none">
    <div>
        <h1 class="page-title"><i class="fas fa-file-invoice text-primary me-2"></i>Enterprise ATS Resume Generator</h1>
        <p class="subtitle">Design, customize, and export machine-readable ATS resumes formatted for Fortune 500 recruiters</p>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-primary btn-sm fw-semibold shadow-sm" onclick="window.print()">
            <i class="fas fa-file-pdf me-1"></i> Download PDF / Print
        </button>
        <a href="<?= url('/student/profile/edit') ?>" class="btn btn-outline-primary btn-sm fw-semibold">
            <i class="fas fa-user-edit me-1"></i> Edit Profile Data
        </a>
    </div>
</div>

<div class="row g-4">

    <!-- LEFT CONTROLS PANEL -->
    <div class="col-lg-4 d-print-none">
        
        <div class="accordion shadow-sm border-0 mb-4" id="resumeControlsAccordion">
            
            <!-- 1. Template Selector Card -->
            <div class="accordion-item border-0 mb-3 shadow-sm rounded">
                <h2 class="accordion-header">
                    <button class="accordion-button fw-bold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTemplates" aria-expanded="true">
                        <i class="fas fa-layer-group me-2 text-primary"></i> 1. Select Resume Template (20 Available)
                    </button>
                </h2>
                <div id="collapseTemplates" class="accordion-collapse collapse show" data-bs-parent="#resumeControlsAccordion">
                    <div class="accordion-body">
                        <label class="form-label fw-semibold small text-secondary">Choose Layout &amp; Persona</label>
                        <select class="form-select fw-semibold" id="templateSelector" onchange="changeTemplate(this.value)">
                            <option value="modern" selected>1. Modern Placement (Default ATS)</option>
                            <option value="classic">2. Classic Corporate Format</option>
                            <option value="ats-pro">3. ATS Professional (Strict Standard)</option>
                            <option value="executive">4. Executive Leadership Resume</option>
                            <option value="minimal">5. Minimal Clean Layout</option>
                            <option value="blue-pro">6. Blue Professional Header</option>
                            <option value="dark-pro">7. Dark Professional Theme</option>
                            <option value="creative">8. Creative Modern Border</option>
                            <option value="fresher">9. Fresher Campus Placement</option>
                            <option value="software">10. Software Engineer Tech CV</option>
                            <option value="datascience">11. Data Science &amp; Analytics</option>
                            <option value="uiux">12. UI/UX &amp; Product Designer</option>
                            <option value="mba">13. MBA &amp; Management Consultant</option>
                            <option value="govt">14. Government &amp; Public Sector</option>
                            <option value="academic">15. Academic Curriculum Vitae</option>
                            <option value="research">16. Research Scientist CV</option>
                            <option value="internship">17. Summer Internship Resume</option>
                            <option value="onepage">18. Compact One-Page ATS</option>
                            <option value="twocolumn">19. Two-Column Split Layout</option>
                            <option value="international">20. International EU/US Standard</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- 2. Colors & Typography Card -->
            <div class="accordion-item border-0 mb-3 shadow-sm rounded">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed fw-bold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#collapseStyling">
                        <i class="fas fa-palette me-2 text-primary"></i> 2. Colors &amp; Typography
                    </button>
                </h2>
                <div id="collapseStyling" class="accordion-collapse collapse" data-bs-parent="#resumeControlsAccordion">
                    <div class="accordion-body">
                        
                        <!-- Accent Color -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-secondary d-flex justify-content-between">
                                <span>Primary Accent Color</span>
                                <small id="saveBadge" class="text-success fw-bold" style="display:none;"><i class="fas fa-check me-1"></i>Saved</small>
                            </label>
                            <div class="d-flex flex-wrap gap-2 align-items-center mb-2">
                                <button type="button" class="color-picker-btn" style="background:#2563eb" onclick="changeColor('#2563eb')" title="Royal Blue"></button>
                                <button type="button" class="color-picker-btn" style="background:#16a34a" onclick="changeColor('#16a34a')" title="Emerald Green"></button>
                                <button type="button" class="color-picker-btn" style="background:#0f172a" onclick="changeColor('#0f172a')" title="Dark Slate"></button>
                                <button type="button" class="color-picker-btn" style="background:#7c3aed" onclick="changeColor('#7c3aed')" title="Purple Violet"></button>
                                <button type="button" class="color-picker-btn" style="background:#dc2626" onclick="changeColor('#dc2626')" title="Crimson Red"></button>
                                <button type="button" class="color-picker-btn" style="background:#d97706" onclick="changeColor('#d97706')" title="Amber Orange"></button>
                                <button type="button" class="color-picker-btn" style="background:#0891b2" onclick="changeColor('#0891b2')" title="Cyan Teal"></button>
                                <input type="color" id="customColorInput" value="<?= htmlspecialchars($savedAccentColor) ?>" onchange="changeColor(this.value)" class="form-control form-control-color border-0 p-0" style="width:28px; height:28px; cursor:pointer;" title="Custom Color">
                            </div>
                        </div>

                        <!-- Font Family -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-secondary">Font Family</label>
                            <select class="form-select form-select-sm" id="fontSelector" onchange="changeFont(this.value)">
                                <option value="'Inter', sans-serif">Inter (Modern Clean)</option>
                                <option value="'Roboto', sans-serif">Roboto (Digital Standard)</option>
                                <option value="'Times New Roman', Times, serif">Times New Roman (Classic Formal)</option>
                                <option value="'Fira Code', monospace">Fira Code (Developer Monospace)</option>
                            </select>
                        </div>

                    </div>
                </div>
            </div>

            <!-- 3. Section Visibility Toggles Card -->
            <div class="accordion-item border-0 mb-3 shadow-sm rounded">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed fw-bold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSections">
                        <i class="fas fa-sliders-h me-2 text-primary"></i> 3. Section Visibility Toggles
                    </button>
                </h2>
                <div id="collapseSections" class="accordion-collapse collapse" data-bs-parent="#resumeControlsAccordion">
                    <div class="accordion-body">
                        <div class="row g-2">
                            <div class="col-6">
                                <div class="form-check form-switch small">
                                    <input class="form-check-input" type="checkbox" id="togglePhoto" checked onchange="toggleSec('secPhoto', this.checked)">
                                    <label class="form-check-label">Profile Photo</label>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-check form-switch small">
                                    <input class="form-check-input" type="checkbox" id="toggleObjective" checked onchange="toggleSec('secObjective', this.checked)">
                                    <label class="form-check-label">Career Objective</label>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-check form-switch small">
                                    <input class="form-check-input" type="checkbox" id="toggleEducation" checked onchange="toggleSec('secEducation', this.checked)">
                                    <label class="form-check-label">Education</label>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-check form-switch small">
                                    <input class="form-check-input" type="checkbox" id="toggleTechSkills" checked onchange="toggleSec('secTechSkills', this.checked)">
                                    <label class="form-check-label">Technical Skills</label>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-check form-switch small">
                                    <input class="form-check-input" type="checkbox" id="toggleSoftSkills" checked onchange="toggleSec('secSoftSkills', this.checked)">
                                    <label class="form-check-label">Soft Skills</label>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-check form-switch small">
                                    <input class="form-check-input" type="checkbox" id="toggleProjects" checked onchange="toggleSec('secProjects', this.checked)">
                                    <label class="form-check-label">Projects</label>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-check form-switch small">
                                    <input class="form-check-input" type="checkbox" id="toggleInternships" checked onchange="toggleSec('secInternships', this.checked)">
                                    <label class="form-check-label">Internships / Exp</label>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-check form-switch small">
                                    <input class="form-check-input" type="checkbox" id="toggleCerts" checked onchange="toggleSec('secCerts', this.checked)">
                                    <label class="form-check-label">Certifications</label>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-check form-switch small">
                                    <input class="form-check-input" type="checkbox" id="toggleTrainings" checked onchange="toggleSec('secTrainings', this.checked)">
                                    <label class="form-check-label">Trainings</label>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-check form-switch small">
                                    <input class="form-check-input" type="checkbox" id="toggleAchievements" checked onchange="toggleSec('secAchievements', this.checked)">
                                    <label class="form-check-label">Achievements</label>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-check form-switch small">
                                    <input class="form-check-input" type="checkbox" id="toggleLanguages" checked onchange="toggleSec('secLanguages', this.checked)">
                                    <label class="form-check-label">Languages</label>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-check form-switch small">
                                    <input class="form-check-input" type="checkbox" id="toggleHobbies" checked onchange="toggleSec('secHobbies', this.checked)">
                                    <label class="form-check-label">Hobbies &amp; Interests</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4. Quick Text Customizer -->
            <div class="accordion-item border-0 mb-3 shadow-sm rounded">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed fw-bold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#collapseLiveEdit">
                        <i class="fas fa-edit me-2 text-primary"></i> 4. Quick Live Text Editor
                    </button>
                </h2>
                <div id="collapseLiveEdit" class="accordion-collapse collapse" data-bs-parent="#resumeControlsAccordion">
                    <div class="accordion-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-secondary">Career Objective / Professional Summary</label>
                            <textarea class="form-control form-control-sm" id="editObjective" rows="3" oninput="updateLiveText('liveObjText', this.value)"><?= htmlspecialchars($student['bio'] ?? 'Motivated Engineering undergraduate seeking a challenging software development role to apply algorithm design, full-stack development skills, and technical problem-solving capabilities in a growth-oriented organization.') ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-secondary">Soft Skills (Comma Separated)</label>
                            <input type="text" class="form-control form-control-sm" id="editSoftSkills" value="<?= htmlspecialchars($student['soft_skills'] ?? 'Problem Solving, Team Leadership, Technical Communication, Critical Thinking, Agile Methodologies') ?>" oninput="updateLiveSoftSkills(this.value)">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-secondary">Hobbies &amp; Interests</label>
                            <input type="text" class="form-control form-control-sm" id="editHobbies" value="<?= htmlspecialchars($student['hobbies_interests'] ?? 'Competitive Programming, Open Source Contributing, Tech Blogging, Chess') ?>" oninput="updateLiveText('liveHobbiesText', this.value)">
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <div class="p-3 bg-light rounded shadow-sm small border text-secondary">
            <i class="fas fa-shield-alt text-success me-1"></i>
            <strong>100% ATS Verified:</strong> Standardized headings and clean typography ensure high parsing accuracy in Workday, Greenhouse, and Taleo scanners.
        </div>

    </div>

    <!-- RIGHT LIVE PREVIEW CANVAS AREA -->
    <div class="col-lg-8">
        
        <div id="resumePreviewCanvas" class="card border-0 resume-paper tpl-modern shadow">
            
            <!-- RESUME HEADER -->
            <div class="d-flex align-items-center justify-content-between pb-4 mb-4 accent-border border-bottom" id="secHeader">
                <div class="d-flex align-items-center gap-3">
                    <?php if (!empty($student['profile_photo'])): ?>
                    <img id="secPhoto" src="<?= uploadUrl('profile_photos/' . $student['profile_photo']) ?>" 
                         alt="" class="rounded-circle border p-1 bg-white shadow-sm flex-shrink-0" style="width:80px; height:80px; object-fit:cover;"
                         onerror="this.style.display='none'">
                    <?php endif; ?>
                    
                    <div>
                        <h1 class="fw-bold mb-1 resume-accent-text" style="font-size:1.9rem; letter-spacing:-0.5px;">
                            <?= htmlspecialchars(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? '')) ?>
                        </h1>
                        <div class="fw-semibold text-secondary mb-2" style="font-size:1.05rem;">
                            <?= htmlspecialchars($student['degree'] ?? 'B.Tech') ?> in <?= htmlspecialchars($student['branch'] ?? 'Computer Science & Engineering') ?>
                        </div>
                    </div>
                </div>

                <div class="text-end small text-secondary d-flex flex-column gap-1">
                    <div><i class="fas fa-envelope resume-accent-icon me-1"></i><?= htmlspecialchars($student['email'] ?? '') ?></div>
                    <div><i class="fas fa-phone resume-accent-icon me-1"></i><?= htmlspecialchars($student['phone'] ?? '') ?></div>
                    <div><i class="fas fa-map-marker-alt resume-accent-icon me-1"></i><?= htmlspecialchars(($student['city'] ?? 'Pune') . ($student['state'] ? ', ' . $student['state'] : '')) ?></div>
                    <?php if (!empty($student['linkedin'])): ?>
                    <div><i class="fab fa-linkedin resume-accent-icon me-1"></i><?= htmlspecialchars($student['linkedin']) ?></div>
                    <?php endif; ?>
                    <?php if (!empty($student['github'])): ?>
                    <div><i class="fab fa-github text-dark me-1"></i><?= htmlspecialchars($student['github']) ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- CAREER OBJECTIVE / SUMMARY -->
            <div class="mb-4" id="secObjective">
                <h6 class="resume-section-title accent-border">Career Objective</h6>
                <p class="small text-secondary mb-0" id="liveObjText" style="line-height:1.6;">
                    <?= htmlspecialchars($student['bio'] ?? 'Motivated Engineering undergraduate seeking a challenging software development role to apply algorithm design, full-stack development skills, and technical problem-solving capabilities in a growth-oriented organization.') ?>
                </p>
            </div>

            <!-- EDUCATION -->
            <div class="mb-4" id="secEducation">
                <h6 class="resume-section-title accent-border">Education &amp; Academic Qualifications</h6>
                <table class="table table-sm table-bordered text-center align-middle small mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>Degree / Qualification</th>
                            <th>Specialization / Board</th>
                            <th>Passing Year</th>
                            <th>CGPA / Score</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="fw-bold text-dark"><?= htmlspecialchars($student['degree'] ?? 'B.Tech') ?></td>
                            <td><?= htmlspecialchars($student['branch'] ?? 'Computer Science') ?></td>
                            <td><?= $student['passing_year'] ?? '2026' ?></td>
                            <td class="fw-bold text-success"><?= $student['cgpa'] ? $student['cgpa'] . ' / 10' : '8.5 / 10' ?></td>
                        </tr>
                        <?php if ($student['twelfth_percentage']): ?>
                        <tr>
                            <td>HSC (12th Class)</td>
                            <td>Science (State/CBSE)</td>
                            <td>—</td>
                            <td><?= $student['twelfth_percentage'] ?>%</td>
                        </tr>
                        <?php endif; ?>
                        <?php if ($student['tenth_percentage']): ?>
                        <tr>
                            <td>SSC (10th Class)</td>
                            <td>General Board</td>
                            <td>—</td>
                            <td><?= $student['tenth_percentage'] ?>%</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- TECHNICAL SKILLS -->
            <?php if (!empty($student['skills'])): ?>
            <div class="mb-4" id="secTechSkills">
                <h6 class="resume-section-title accent-border">Technical Skills &amp; Competencies</h6>
                <div class="d-flex flex-wrap gap-2">
                    <?php foreach (explode(',', $student['skills']) as $sk): ?>
                    <span class="badge bg-light text-dark border px-3 py-2 fw-medium badge-skill" style="font-size:0.8rem;">
                        <?= htmlspecialchars(trim($sk)) ?>
                    </span>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- SOFT SKILLS -->
            <div class="mb-4" id="secSoftSkills">
                <h6 class="resume-section-title accent-border">Soft Skills &amp; Interpersonal Strengths</h6>
                <div class="d-flex flex-wrap gap-2" id="liveSoftSkillsContainer">
                    <?php 
                    $softList = explode(',', $student['soft_skills'] ?? 'Problem Solving, Team Leadership, Technical Communication, Critical Thinking, Agile Methodologies');
                    foreach ($softList as $sf):
                    ?>
                    <span class="badge bg-light text-secondary border px-3 py-2 fw-normal skill-tag" style="font-size:0.78rem;">
                        <?= htmlspecialchars(trim($sf)) ?>
                    </span>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- PROJECTS -->
            <?php if (!empty($projects)): ?>
            <div class="mb-4" id="secProjects">
                <h6 class="resume-section-title accent-border">Key Projects</h6>
                <div class="d-flex flex-column gap-3">
                    <?php foreach ($projects as $p): ?>
                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <div class="fw-bold text-dark" style="font-size:0.92rem;"><?= htmlspecialchars($p['title']) ?></div>
                            <?php if (!empty($p['github_url'])): ?>
                            <a href="<?= htmlspecialchars($p['github_url']) ?>" target="_blank" class="small text-primary text-decoration-none">
                                <i class="fab fa-github me-1"></i>Repository
                            </a>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($p['description'])): ?>
                        <div class="small text-secondary mb-1"><?= htmlspecialchars($p['description']) ?></div>
                        <?php endif; ?>
                        <?php if (!empty($p['technologies'])): ?>
                        <div class="small text-muted"><strong>Tech Stack:</strong> <?= htmlspecialchars($p['technologies']) ?></div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- INTERNSHIPS & EXPERIENCE -->
            <?php if (!empty($internships)): ?>
            <div class="mb-4" id="secInternships">
                <h6 class="resume-section-title accent-border">Internship &amp; Practical Experience</h6>
                <div class="d-flex flex-column gap-3">
                    <?php foreach ($internships as $in): ?>
                    <div class="highlight-box rounded">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="fw-bold text-dark"><?= htmlspecialchars($in['job_title']) ?></div>
                            <span class="badge bg-primary-soft text-primary small"><?= htmlspecialchars($in['company_name']) ?></span>
                        </div>
                        <div class="small text-muted"><i class="fas fa-map-marker-alt me-1"></i><?= htmlspecialchars($in['location'] ?? 'Remote') ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- CERTIFICATIONS -->
            <?php if (!empty($certifications)): ?>
            <div class="mb-4" id="secCerts">
                <h6 class="resume-section-title accent-border">Certifications &amp; Credentials</h6>
                <ul class="mb-0 ps-3 small text-secondary">
                    <?php foreach ($certifications as $c): ?>
                    <li>
                        <strong><?= htmlspecialchars($c['title']) ?></strong> — <?= htmlspecialchars($c['issuing_org'] ?? '') ?> 
                        <?php if (!empty($c['issue_date'])): ?><span class="text-muted">(<?= formatDate($c['issue_date']) ?>)</span><?php endif; ?>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <!-- HACKATHONS -->
            <?php if (!empty($hackathons)): ?>
            <div class="mb-4" id="secHackathons">
                <h6 class="resume-section-title accent-border">Hackathons &amp; Codefests</h6>
                <ul class="mb-0 ps-3 small text-secondary">
                    <?php foreach ($hackathons as $hk): ?>
                    <li>
                        <strong><?= htmlspecialchars($hk['title']) ?></strong> — <?= htmlspecialchars($hk['organizer'] ?? 'National Hackathon') ?>
                        <?php if (!empty($hk['position_rank'])): ?><span class="badge bg-warning-soft text-dark ms-1"><?= htmlspecialchars($hk['position_rank']) ?></span><?php endif; ?>
                        <?php if (!empty($hk['description'])): ?><br><span class="text-muted small"><?= htmlspecialchars($hk['description']) ?></span><?php endif; ?>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <!-- WORKSHOPS & SEMINARS -->
            <?php if (!empty($workshops)): ?>
            <div class="mb-4" id="secWorkshops">
                <h6 class="resume-section-title accent-border">Workshops &amp; Technical Seminars</h6>
                <ul class="mb-0 ps-3 small text-secondary">
                    <?php foreach ($workshops as $wk): ?>
                    <li>
                        <strong><?= htmlspecialchars($wk['title']) ?></strong> — Organized by <?= htmlspecialchars($wk['organizer'] ?? 'Technical Cell') ?>
                        <?php if (!empty($wk['achievement_date'])): ?><span class="text-muted">(<?= formatDate($wk['achievement_date']) ?>)</span><?php endif; ?>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <!-- COMPETITIONS -->
            <?php if (!empty($competitions)): ?>
            <div class="mb-4" id="secCompetitions">
                <h6 class="resume-section-title accent-border">Competitions &amp; Technical Contests</h6>
                <ul class="mb-0 ps-3 small text-secondary">
                    <?php foreach ($competitions as $cp): ?>
                    <li>
                        <strong><?= htmlspecialchars($cp['title']) ?></strong> — <?= htmlspecialchars($cp['organizer'] ?? '') ?>
                        <?php if (!empty($cp['position_rank'])): ?><span class="badge bg-primary-soft text-primary ms-1"><?= htmlspecialchars($cp['position_rank']) ?></span><?php endif; ?>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <!-- TRAINING PROGRAMS -->
            <?php if (!empty($trainings)): ?>
            <div class="mb-4" id="secTrainings">
                <h6 class="resume-section-title accent-border">Completed Training Programs</h6>
                <ul class="mb-0 ps-3 small text-secondary">
                    <?php foreach ($trainings as $tr): ?>
                    <li><strong><?= htmlspecialchars($tr['title']) ?></strong> — Campus Training Program</li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <!-- HONORS & ACHIEVEMENTS -->
            <?php if (!empty($achievements)): ?>
            <div class="mb-4" id="secAchievements">
                <h6 class="resume-section-title accent-border">Honors &amp; Key Achievements</h6>
                <ul class="mb-0 ps-3 small text-secondary">
                    <?php foreach ($achievements as $ac): ?>
                    <li>
                        <strong><?= htmlspecialchars($ac['title']) ?></strong> 
                        <?php if (!empty($ac['position_rank'])): ?>(<?= htmlspecialchars($ac['position_rank']) ?>)<?php endif; ?>
                        — <?= htmlspecialchars($ac['description'] ?? '') ?>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <!-- POSITIONS OF RESPONSIBILITY -->
            <?php if (!empty($student['responsibilities'])): ?>
            <div class="mb-4" id="secResponsibilities">
                <h6 class="resume-section-title accent-border">Positions of Responsibility</h6>
                <p class="small text-secondary mb-0">
                    <?= htmlspecialchars($student['responsibilities']) ?>
                </p>
            </div>
            <?php endif; ?>

            <!-- LANGUAGES KNOWN -->
            <?php if (!empty($languages)): ?>
            <div class="mb-4" id="secLanguages">
                <h6 class="resume-section-title accent-border">Languages Known</h6>
                <div class="d-flex flex-wrap gap-3 small text-secondary">
                    <?php foreach ($languages as $lg): ?>
                    <span><i class="fas fa-language me-1 resume-accent-icon"></i><strong><?= htmlspecialchars($lg['language']) ?></strong> (<?= ucfirst($lg['proficiency'] ?? 'Fluent') ?>)</span>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- HOBBIES & INTERESTS -->
            <?php if (!empty($student['hobbies_interests'])): ?>
            <div class="mb-4" id="secHobbies">
                <h6 class="resume-section-title accent-border">Interests &amp; Extracurriculars</h6>
                <p class="small text-secondary mb-0" id="liveHobbiesText">
                    <?= htmlspecialchars($student['hobbies_interests']) ?>
                </p>
            </div>
            <?php endif; ?>

        </div>

    </div>

</div>

<script>
var currentAccent = '<?= htmlspecialchars($savedAccentColor) ?>';

// 1. Template Switcher Function
function changeTemplate(tplClass) {
    var paper = document.getElementById('resumePreviewCanvas');
    
    // Remove existing tpl- classes
    paper.className.split(' ').forEach(function(cls) {
        if (cls.indexOf('tpl-') === 0) {
            paper.classList.remove(cls);
        }
    });

    paper.classList.add('tpl-' + tplClass);
}

// 2. Accent Color Switcher Function
function changeColor(color) {
    if (!color) return;
    currentAccent = color;

    document.documentElement.style.setProperty('--resume-accent', color);

    document.querySelectorAll('.resume-accent-text').forEach(function(el) {
        el.style.color = color;
    });
    document.querySelectorAll('.resume-accent-icon').forEach(function(el) {
        el.style.color = color;
    });
    document.querySelectorAll('.accent-border').forEach(function(el) {
        el.style.borderColor = color;
    });

    document.getElementById('customColorInput').value = color;

    // Save accent color via AJAX
    $.post(TPMS.baseUrl + '/student/save-resume-accent', {
        accent_color: color,
        csrf_token: TPMS.csrfToken
    }, function(res) {
        if (res.success) {
            $('#saveBadge').fadeIn().delay(1200).fadeOut();
        }
    }, 'json');
}

// 3. Font Family Switcher Function
function changeFont(fontFamily) {
    document.getElementById('resumePreviewCanvas').style.fontFamily = fontFamily;
}

// 4. Section Toggle Switcher Function
function toggleSec(secId, isVisible) {
    var el = document.getElementById(secId);
    if (el) {
        el.style.display = isVisible ? 'block' : 'none';
    }
}

// 5. Live Text Update Helpers
function updateLiveText(targetId, val) {
    var el = document.getElementById(targetId);
    if (el) el.textContent = val;
}

function updateLiveSoftSkills(val) {
    var container = document.getElementById('liveSoftSkillsContainer');
    if (!container) return;
    var list = val.split(',');
    var html = '';
    list.forEach(function(item) {
        if (item.trim()) {
            html += '<span class="badge bg-light text-secondary border px-3 py-2 fw-normal skill-tag" style="font-size:0.78rem;">' + item.trim() + '</span> ';
        }
    });
    container.innerHTML = html;
}

// Initialize on Load
document.addEventListener('DOMContentLoaded', function() {
    changeColor(currentAccent);
});
</script>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>
