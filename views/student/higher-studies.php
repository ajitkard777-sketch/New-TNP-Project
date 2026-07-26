<?php require_once ROOT_PATH . '/includes/header.php'; ?>

<style>
.portal-card {
    transition: all 0.25s ease;
    border-radius: 12px;
}
.portal-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.08) !important;
}
.nav-pills .nav-link {
    font-weight: 600;
    color: #475569;
    border-radius: 8px;
    padding: 10px 18px;
}
.nav-pills .nav-link.active {
    background-color: var(--tpms-primary, #2563eb);
    color: #ffffff;
    box-shadow: 0 4px 6px -1px rgba(37,99,235,0.2);
}
.flag-icon {
    width: 24px;
    height: 18px;
    object-fit: cover;
    border-radius: 3px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.2);
}
</style>

<div class="content-header mb-4">
    <div>
        <h1 class="page-title"><i class="fas fa-graduation-cap text-primary me-2"></i>Higher Studies &amp; Global Education Portal</h1>
        <p class="subtitle">Comprehensive guide to entrance exams, top Indian &amp; international universities, scholarships, SOPs, and visa guidance</p>
    </div>
</div>

<!-- Category Navigation Tabs -->
<ul class="nav nav-pills gap-2 mb-4 bg-white p-2 rounded shadow-sm border" id="higherStudiesTabs" role="tablist">
    <li class="nav-item">
        <a class="nav-link active" id="tab-exams" data-bs-toggle="tab" href="#sec-exams" role="tab">
            <i class="fas fa-file-alt me-1"></i> Entrance Exams
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="tab-india" data-bs-toggle="tab" href="#sec-india" role="tab">
            <i class="fas fa-landmark me-1"></i> Study in India
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="tab-abroad" data-bs-toggle="tab" href="#sec-abroad" role="tab">
            <i class="fas fa-globe-americas me-1"></i> Study Abroad
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="tab-scholarships" data-bs-toggle="tab" href="#sec-scholarships" role="tab">
            <i class="fas fa-award me-1"></i> Scholarships
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="tab-guidance" data-bs-toggle="tab" href="#sec-guidance" role="tab">
            <i class="fas fa-compass me-1"></i> Career Guidance
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="tab-resources" data-bs-toggle="tab" href="#sec-resources" role="tab">
            <i class="fas fa-book-open me-1"></i> Prep Resources
        </a>
    </li>
</ul>

<div class="tab-content" id="higherStudiesContent">

    <!-- 1. ENTRANCE EXAMS -->
    <div class="tab-pane fade show active" id="sec-exams" role="tabpanel">
        <div class="row g-4">
            <?php
            $examList = [
                ['name' => 'GATE', 'full' => 'Graduate Aptitude Test in Engineering', 'body' => 'IITs & IISc', 'target' => 'M.Tech, MS, PSU Jobs', 'website' => 'https://gate2026.iitg.ac.in', 'icon' => 'fa-laptop-code', 'color' => 'primary'],
                ['name' => 'GRE', 'full' => 'Graduate Record Examination', 'body' => 'ETS (Educational Testing Service)', 'target' => 'MS / PhD in USA, Germany, Global', 'website' => 'https://www.ets.org/gre', 'icon' => 'fa-globe', 'color' => 'info'],
                ['name' => 'GMAT', 'full' => 'Graduate Management Admission Test', 'body' => 'GMAC', 'target' => 'Global MBA / Executive Management', 'website' => 'https://www.mba.com', 'icon' => 'fa-briefcase', 'color' => 'warning'],
                ['name' => 'CAT', 'full' => 'Common Admission Test', 'body' => 'IIMs', 'target' => 'MBA / PGDM in IIMs & Top B-Schools', 'website' => 'https://iimcat.ac.in', 'icon' => 'fa-chart-line', 'color' => 'success'],
                ['name' => 'TOEFL', 'full' => 'Test of English as a Foreign Language', 'body' => 'ETS', 'target' => 'English Proficiency for US / Global Unis', 'website' => 'https://www.ets.org/toefl', 'icon' => 'fa-language', 'color' => 'danger'],
                ['name' => 'IELTS', 'full' => 'International English Language Testing System', 'body' => 'IDP / British Council', 'target' => 'UK, Canada, Australia, Europe Admissions', 'website' => 'https://www.ielts.org', 'icon' => 'fa-comments', 'color' => 'purple'],
                ['name' => 'CEED', 'full' => 'Common Entrance Examination for Design', 'body' => 'IIT Bombay', 'target' => 'M.Des & Industrial Design in IITs & IISc', 'website' => 'http://www.ceed.iitb.ac.in', 'icon' => 'fa-palette', 'color' => 'secondary'],
                ['name' => 'UGC NET', 'full' => 'National Eligibility Test', 'body' => 'NTA', 'target' => 'JRF & Assistant Professorship in India', 'website' => 'https://ugcnet.nta.ac.in', 'icon' => 'fa-chalkboard-teacher', 'color' => 'dark']
            ];
            foreach ($examList as $ex):
            ?>
            <div class="col-lg-4 col-md-6">
                <div class="card shadow-sm border-0 h-100 portal-card">
                    <div class="card-body p-4 d-flex flex-column">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="rounded-3 bg-<?= $ex['color'] ?>-soft text-<?= $ex['color'] ?> p-3 fs-4 d-flex align-items-center justify-content-center" style="width:50px; height:50px;">
                                <i class="fas <?= $ex['icon'] ?>"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-0 text-dark"><?= $ex['name'] ?></h5>
                                <small class="text-muted"><?= $ex['body'] ?></small>
                            </div>
                        </div>
                        <div class="small fw-semibold text-secondary mb-2"><?= $ex['full'] ?></div>
                        <div class="bg-light p-2 rounded small text-secondary mb-3 mt-auto">
                            <i class="fas fa-bullseye text-primary me-1"></i><strong>Target Goal:</strong> <?= $ex['target'] ?>
                        </div>
                        <a href="<?= $ex['website'] ?>" target="_blank" class="btn btn-outline-primary btn-sm w-100 fw-semibold mt-auto">
                            <i class="fas fa-external-link-alt me-1"></i> Official Portal
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- 2. STUDY IN INDIA -->
    <div class="tab-pane fade" id="sec-india" role="tabpanel">
        <div class="row g-4 mb-4">
            <?php
            $indiaPrograms = [
                ['title' => 'M.Tech / M.E.', 'icon' => 'fa-cogs', 'eligibility' => 'B.Tech / B.E. with valid GATE score', 'exams' => 'GATE', 'top' => 'IIT Bombay, IIT Delhi, IIT Madras, IISc, NIT Trichy', 'fee' => '₹50,000 - ₹2.5 Lakhs / yr', 'desc' => 'Specializations in AI, Data Science, VLSI, Robotics, and Structural Engineering.'],
                ['title' => 'MBA / PGDM', 'icon' => 'fa-chart-pie', 'eligibility' => 'Graduation (min 50% marks)', 'exams' => 'CAT, XAT, NMAT, SNAP, CMAT', 'top' => 'IIM Ahmedabad, IIM Bangalore, IIM Calcutta, XLRI, FMS', 'fee' => '₹12 - ₹28 Lakhs total', 'desc' => 'High-impact management degree with lucrative corporate campus recruitment.'],
                ['title' => 'MCA (Master of Computer Applications)', 'icon' => 'fa-code-branch', 'eligibility' => 'BCA / B.Sc / B.Tech with Maths', 'exams' => 'NIMCET, MAH-MCA-CET', 'top' => 'NIT Trichy, NIT Surathkal, JNU Delhi, BHU, Pune University', 'fee' => '₹40,000 - ₹1.5 Lakhs / yr', 'desc' => 'Advanced software engineering, cloud computing, and full-stack architecture.'],
                ['title' => 'MS by Research', 'icon' => 'fa-microscope', 'eligibility' => 'B.Tech / B.E. + Interview / GATE', 'exams' => 'GATE / Institute Test', 'top' => 'IISc Bangalore, IIT Hyderabad, IIIT Hyderabad, IIT Madras', 'fee' => 'Stipend provided (₹12,400/mo)', 'desc' => 'Research-heavy degree ideal for R&D careers, publications, and PhD tracks.'],
                ['title' => 'PhD in Engineering & Tech', 'icon' => 'fa-user-graduate', 'eligibility' => 'Master\'s or Direct B.Tech (High CGPA)', 'exams' => 'GATE, UGC-NET, CSIR-NET', 'top' => 'All IITs, IISc, TIFR, CSIR Labs, IIITs', 'fee' => 'Fully Funded + ₹37,000/mo Fellowship', 'desc' => 'Deep technology research, patents, academia, and global lab research leadership.']
            ];
            foreach ($indiaPrograms as $prog):
            ?>
            <div class="col-lg-4 col-md-6">
                <div class="card shadow-sm border-0 h-100 portal-card">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="rounded-circle bg-primary-soft text-primary p-3 fs-4">
                                <i class="fas <?= $prog['icon'] ?>"></i>
                            </div>
                            <h5 class="fw-bold text-dark mb-0"><?= $prog['title'] ?></h5>
                        </div>
                        <p class="small text-muted mb-3"><?= $prog['desc'] ?></p>
                        
                        <div class="small bg-light p-3 rounded mb-2">
                            <div><strong>Eligibility:</strong> <?= $prog['eligibility'] ?></div>
                            <div class="mt-1"><strong>Required Exams:</strong> <span class="badge bg-primary-soft text-primary"><?= $prog['exams'] ?></span></div>
                            <div class="mt-1"><strong>Approx Fees:</strong> <?= $prog['fee'] ?></div>
                        </div>

                        <div class="small text-secondary">
                            <strong>Top Institutes:</strong> <?= $prog['top'] ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- 3. STUDY ABROAD (8 COUNTRIES) -->
    <div class="tab-pane fade" id="sec-abroad" role="tabpanel">
        <div class="row g-4">
            <?php
            $countries = [
                [
                    'country' => 'USA', 'flag' => '🇺🇸', 'unis' => 'MIT, Stanford, CMU, UC Berkeley, Harvard',
                    'eligibility' => 'B.Tech (4 yrs) + Min 3.0/4.0 GPA', 'exams' => 'GRE + TOEFL / IELTS',
                    'fees' => '$30,000 - $60,000 / yr', 'scholarships' => 'Fulbright, TA/RA Assistantships',
                    'timeline' => 'Fall: Sept - Dec deadlines | Spring: May - July', 'visa' => 'F-1 Student Visa (OPT up to 3 yrs for STEM)'
                ],
                [
                    'country' => 'Canada', 'flag' => '🇨🇦', 'unis' => 'Univ of Toronto, UBC, McGill, Waterloo',
                    'eligibility' => '4 yrs Bachelor\'s + Min 75% aggregate', 'exams' => 'IELTS (Min 6.5 - 7.0) + GRE (Optional)',
                    'fees' => 'CAD 20,000 - 45,000 / yr', 'scholarships' => 'Vanier Canada Graduate Scholarships',
                    'timeline' => 'Fall: Oct - Jan deadlines | Winter: June - Aug', 'visa' => 'Study Permit (Post-Graduation Work Permit PGWP up to 3 yrs)'
                ],
                [
                    'country' => 'Germany', 'flag' => '🇩🇪', 'unis' => 'TU Munich, RWTH Aachen, TU Berlin, LMU Munich',
                    'eligibility' => 'Matching B.Tech course credits + Good CGPA', 'exams' => 'IELTS / TOEFL + GRE (for top TUs)',
                    'fees' => 'No Tuition Fees in Public Unis (€150-350 semester fee)', 'scholarships' => 'DAAD Scholarships',
                    'timeline' => 'Winter Semester: July 15 | Summer: Jan 15', 'visa' => 'German Student Visa + Blocked Account (€11,208/yr)'
                ],
                [
                    'country' => 'United Kingdom', 'flag' => '🇬🇧', 'unis' => 'Oxford, Cambridge, Imperial, UCL, Univ of Edinburgh',
                    'eligibility' => 'Bachelor\'s (1-Yr Master\'s degree available)', 'exams' => 'IELTS (Min 6.5+) / PTE',
                    'fees' => '£18,000 - £35,000 total', 'scholarships' => 'Chevening, Commonwealth Scholarships',
                    'timeline' => 'Sept Intake: Dec - May deadlines', 'visa' => 'Student Visa (Graduate Route 2-yr work visa)'
                ],
                [
                    'country' => 'Australia', 'flag' => '🇦🇺', 'unis' => 'Univ of Melbourne, USYD, UNSW, ANU, Monash',
                    'eligibility' => 'Bachelor\'s degree with min 65%+', 'exams' => 'IELTS / PTE Academic',
                    'fees' => 'AUD 35,000 - 50,000 / yr', 'scholarships' => 'Australia Awards, Destination Australia',
                    'timeline' => 'Feb Intake: Nov deadline | July Intake: April deadline', 'visa' => 'Subclass 500 Visa (Post-study work rights 2-4 yrs)'
                ],
                [
                    'country' => 'Japan', 'flag' => '🇯🇵', 'unis' => 'Univ of Tokyo, Kyoto Univ, Tokyo Tech, Osaka Univ',
                    'eligibility' => 'Bachelor\'s in Engineering/Science', 'exams' => 'TOEFL / IELTS + JLPT (Optional for English programs)',
                    'fees' => '¥535,800 / yr (Public Unis)', 'scholarships' => 'MEXT Japanese Government Scholarship',
                    'timeline' => 'Spring: Oct deadline | Autumn: April deadline', 'visa' => 'Student Visa (High Tech job placement opportunities)'
                ],
                [
                    'country' => 'Ireland', 'flag' => '🇮🇪', 'unis' => 'Trinity College Dublin, UCD, NUI Galway, UCC',
                    'eligibility' => 'Bachelor\'s with min 60%+', 'exams' => 'IELTS (6.5+) / TOEFL',
                    'fees' => '€12,000 - €22,000 / yr', 'scholarships' => 'Government of Ireland International Education Scholarship',
                    'timeline' => 'Autumn Intake: Feb - May deadlines', 'visa' => 'Stamp 1G (2-year stay back option)'
                ],
                [
                    'country' => 'Singapore', 'flag' => '🇸🇬', 'unis' => 'NUS (National Univ of Singapore), NTU',
                    'eligibility' => 'High CGPA Bachelor\'s in relevant field', 'exams' => 'GRE + TOEFL / IELTS',
                    'fees' => 'SGD 20,000 - 40,000 / yr', 'scholarships' => 'SINGA (Singapore International Graduate Award)',
                    'timeline' => 'August Intake: Nov - Jan deadlines', 'visa' => 'Student Pass + Tuition Grant Work Obligation'
                ]
            ];
            foreach ($countries as $c):
            ?>
            <div class="col-lg-6">
                <div class="card shadow-sm border-0 h-100 portal-card">
                    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                        <h5 class="fw-bold mb-0 text-dark">
                            <span class="me-2" style="font-size:1.4rem;"><?= $c['flag'] ?></span>Study in <?= $c['country'] ?>
                        </h5>
                        <span class="badge bg-primary-soft text-primary fw-bold">Global Hub</span>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <strong class="text-primary small">Top Universities:</strong>
                            <div class="fw-semibold text-dark small"><?= $c['unis'] ?></div>
                        </div>

                        <div class="row g-2 mb-3 small">
                            <div class="col-6 bg-light p-2 rounded">
                                <span class="text-muted d-block">Eligibility:</span>
                                <span class="fw-medium text-dark"><?= $c['eligibility'] ?></span>
                            </div>
                            <div class="col-6 bg-light p-2 rounded">
                                <span class="text-muted d-block">Required Exams:</span>
                                <span class="fw-medium text-dark"><?= $c['exams'] ?></span>
                            </div>
                            <div class="col-6 bg-light p-2 rounded">
                                <span class="text-muted d-block">Approx Fees:</span>
                                <span class="fw-medium text-success"><?= $c['fees'] ?></span>
                            </div>
                            <div class="col-6 bg-light p-2 rounded">
                                <span class="text-muted d-block">Scholarships:</span>
                                <span class="fw-medium text-dark"><?= $c['scholarships'] ?></span>
                            </div>
                        </div>

                        <div class="p-2 rounded bg-light border small mb-2">
                            <i class="far fa-calendar-alt text-info me-1"></i><strong>Timeline:</strong> <?= $c['timeline'] ?>
                        </div>
                        <div class="p-2 rounded bg-light border small text-secondary">
                            <i class="fas fa-passport text-warning me-1"></i><strong>Visa Info:</strong> <?= $c['visa'] ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- 4. SCHOLARSHIPS -->
    <div class="tab-pane fade" id="sec-scholarships" role="tabpanel">
        <div class="row g-4">
            <?php
            $scholarshipList = [
                ['name' => 'DAAD Scholarships (Germany)', 'type' => 'Government', 'amount' => 'Full Tuition + €934/mo Stipend', 'eligibility' => 'Bachelor\'s with min 2 yrs work experience / high CGPA', 'provider' => 'German Academic Exchange Service'],
                ['name' => 'Fulbright-Nehru Master\'s Fellowships', 'type' => 'International', 'amount' => 'Tuition + Living + Airfare', 'eligibility' => 'Indian graduates with min 3 yrs professional experience', 'provider' => 'USIEF'],
                ['name' => 'PMRF (Prime Minister\'s Research Fellowship)', 'type' => 'Merit', 'amount' => '₹70,000 - ₹80,000/mo + ₹2 Lakh grant', 'eligibility' => 'Top B.Tech graduates from IITs, NITs, IISc entering PhD', 'provider' => 'Government of India'],
                ['name' => 'Chevening Scholarships (UK)', 'type' => 'Government', 'amount' => 'Fully Funded 1-yr Master\'s', 'eligibility' => 'Future leaders with 2+ yrs work experience', 'provider' => 'UK Foreign Office'],
                ['name' => 'MEXT Japanese Government Scholarship', 'type' => 'Government', 'amount' => '100% Tuition + ¥143,000/mo', 'eligibility' => 'Under 35 yrs old, engineering/science background', 'provider' => 'Government of Japan'],
                ['name' => 'Inlaks Shivdasani Foundation Scholarship', 'type' => 'Private', 'amount' => 'Up to $100,000 cover', 'eligibility' => 'Exceptional Indian students under 30 admitted to US/UK/Europe', 'provider' => 'Inlaks Foundation']
            ];
            foreach ($scholarshipList as $sch):
            ?>
            <div class="col-lg-6">
                <div class="card shadow-sm border-0 h-100 portal-card">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="badge bg-success-soft text-success fw-bold px-3 py-1"><?= $sch['type'] ?> Scholarship</span>
                            <span class="fw-bold text-success fs-6"><?= $sch['amount'] ?></span>
                        </div>
                        <h5 class="fw-bold text-dark mb-1"><?= $sch['name'] ?></h5>
                        <small class="text-muted d-block mb-3"><i class="fas fa-building me-1"></i><?= $sch['provider'] ?></small>
                        
                        <div class="p-3 bg-light rounded small text-secondary">
                            <i class="fas fa-check-circle text-primary me-1"></i><strong>Eligibility:</strong> <?= $sch['eligibility'] ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- 5. CAREER GUIDANCE -->
    <div class="tab-pane fade" id="sec-guidance" role="tabpanel">
        <div class="row g-4">
            <?php
            $guidanceTopics = [
                ['title' => 'Statement of Purpose (SOP) Writing', 'icon' => 'fa-pen-nib', 'color' => 'primary', 'tips' => ['Hook the admissions committee with your specific academic interest', 'Explain relevant undergraduate projects and research experience', 'Articulate clearly: Why this University and Why this specific Program?', 'Maintain a professional 800-1000 word structure']],
                ['title' => 'Letters of Recommendation (LOR) Guide', 'icon' => 'fa-envelope-open-text', 'color' => 'success', 'tips' => ['Request LORs from professors who mentored your major projects/thesis', 'Provide recommender with your resume, draft SOP, and marksheets early', 'Ensure at least 2 Academic LORs + 1 Professional/Work LOR', 'Highlight soft skills, problem-solving ability, and academic integrity']],
                ['title' => 'Academic Resume & CV Tips', 'icon' => 'fa-file-invoice', 'color' => 'info', 'tips' => ['Use standard ATS single-page or 2-page academic layout', 'List Publications, Major Projects, Technical Stack, and CGPA upfront', 'Include GitHub repository links and live project URLs', 'Quantify achievements (e.g., Improved algorithm runtime by 35%)']],
                ['title' => 'Interview Preparation', 'icon' => 'fa-user-tie', 'color' => 'warning', 'tips' => ['Prepare concise technical summaries of your final year project', 'Be ready to explain why you want to transition to higher studies', 'Review fundamental core concepts in Data Structures & OS', 'Practice mock visa & university interview sessions']],
                ['title' => 'University Selection Framework', 'icon' => 'fa-university', 'color' => 'purple', 'tips' => ['Shortlist in 3 Tiers: 2 Dream / Ambitious, 3 Target / Realistic, 2 Safe', 'Check Faculty research alignment with your interest domain', 'Evaluate post-study work visa rights and placement track records', 'Consider cost of living vs stipend / funding availability']],
                ['title' => 'Application Checklist & Deadlines', 'icon' => 'fa-tasks', 'color' => 'danger', 'tips' => ['Official Transcripts & Degree Certificates', 'Valid Passport with min 2 yrs validity', 'GRE / GMAT / IELTS / TOEFL Official Score Reports', 'SOP + 3 LORs + Financial Proofs / Bank Balance Certificates']]
            ];
            foreach ($guidanceTopics as $g):
            ?>
            <div class="col-lg-4 col-md-6">
                <div class="card shadow-sm border-0 h-100 portal-card">
                    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-<?= $g['color'] ?>-soft text-<?= $g['color'] ?> p-2 fs-5">
                            <i class="fas <?= $g['icon'] ?>"></i>
                        </div>
                        <h6 class="fw-bold mb-0 text-dark"><?= $g['title'] ?></h6>
                    </div>
                    <div class="card-body p-4">
                        <ul class="mb-0 ps-3 small text-secondary d-flex flex-column gap-2">
                            <?php foreach ($g['tips'] as $tip): ?>
                            <li><?= htmlspecialchars($tip) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- 6. PREP RESOURCES -->
    <div class="tab-pane fade" id="sec-resources" role="tabpanel">
        <div class="row g-4">
            <div class="col-lg-6">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h6 class="mb-0 fw-bold"><i class="fas fa-book me-2 text-primary"></i>Recommended Books &amp; Study Guides</h6>
                    </div>
                    <div class="card-body p-4">
                        <ul class="list-group list-group-flush small">
                            <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                                <div>
                                    <div class="fw-bold text-dark">Made Easy GATE Engineering Mathematics &amp; Aptitude</div>
                                    <span class="text-muted">GATE Core Preparation Standard</span>
                                </div>
                                <span class="badge bg-light text-dark border">Book</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                                <div>
                                    <div class="fw-bold text-dark">Official GRE Super Power Pack by ETS</div>
                                    <span class="text-muted">Official ETS Verbal &amp; Quantitative Practice</span>
                                </div>
                                <span class="badge bg-light text-dark border">Book</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                                <div>
                                    <div class="fw-bold text-dark">Manhattan Prep 5 lb. Book of GRE Practice Problems</div>
                                    <span class="text-muted">3,000+ Practice Questions</span>
                                </div>
                                <span class="badge bg-light text-dark border">Book</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                                <div>
                                    <div class="fw-bold text-dark">The Official Cambridge Guide to IELTS</div>
                                    <span class="text-muted">Academic &amp; General Training</span>
                                </div>
                                <span class="badge bg-light text-dark border">Book</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h6 class="mb-0 fw-bold"><i class="fas fa-video me-2 text-danger"></i>Video Resources &amp; Free Portals</h6>
                    </div>
                    <div class="card-body p-4">
                        <ul class="list-group list-group-flush small">
                            <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                                <div>
                                    <div class="fw-bold text-dark">NPTEL Online Courses (IIT &amp; IISc)</div>
                                    <span class="text-muted">Free video lectures for GATE &amp; Core Engineering</span>
                                </div>
                                <a href="https://nptel.ac.in" target="_blank" class="btn btn-sm btn-outline-danger">Watch</a>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                                <div>
                                    <div class="fw-bold text-dark">GregMat &amp; Magoosh GRE Prep</div>
                                    <span class="text-muted">Strategies for GRE Verbal &amp; Quant 320+ Score</span>
                                </div>
                                <a href="https://www.gregmat.com" target="_blank" class="btn btn-sm btn-outline-danger">Visit</a>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                                <div>
                                    <div class="fw-bold text-dark">IELTS Liz Preparation Portal</div>
                                    <span class="text-muted">Free lessons for IELTS Speaking, Writing &amp; Reading</span>
                                </div>
                                <a href="https://ieltsliz.com" target="_blank" class="btn btn-sm btn-outline-danger">Visit</a>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                                <div>
                                    <div class="fw-bold text-dark">DAAD Official International Programs Database</div>
                                    <span class="text-muted">Search 2,000+ tuition-free English Master\'s in Germany</span>
                                </div>
                                <a href="https://www me-2.daad.de" target="_blank" class="btn btn-sm btn-outline-danger">Search</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>
