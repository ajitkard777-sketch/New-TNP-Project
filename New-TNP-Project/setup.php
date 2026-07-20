<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>TPMS — Database Setup</title>
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: 'Segoe UI', system-ui, sans-serif; background: #0f172a; color: #f1f5f9; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 2rem; }
  .card { background: #1e293b; border: 1px solid #334155; border-radius: 16px; padding: 2.5rem; max-width: 700px; width: 100%; }
  h1 { font-size: 1.5rem; font-weight: 800; color: #818cf8; margin-bottom: 0.5rem; }
  p.sub { color: #94a3b8; font-size: 0.875rem; margin-bottom: 1.5rem; }
  .step { padding: 0.6rem 1rem; margin: 0.4rem 0; border-radius: 8px; font-size: 0.85rem; border-left: 3px solid; }
  .step.ok  { background: rgba(16,185,129,0.1); border-color: #10b981; color: #6ee7b7; }
  .step.err { background: rgba(239,68,68,0.1);  border-color: #ef4444; color: #fca5a5; }
  .step.info{ background: rgba(79,70,229,0.1);  border-color: #4f46e5; color: #a5b4fc; }
  .done { margin-top: 1.5rem; padding: 1rem; background: rgba(16,185,129,0.15); border: 1px solid #10b981; border-radius: 10px; text-align: center; }
  .done a { color: #818cf8; font-weight: 700; text-decoration: none; }
  .done a:hover { text-decoration: underline; }
  .warn { margin-top: 1rem; padding: 0.75rem 1rem; background: rgba(245,158,11,0.1); border: 1px solid #f59e0b; border-radius: 8px; font-size: 0.8rem; color: #fcd34d; }
</style>
</head>
<body>
<div class="card">
<h1>🎓 TPMS Database Setup</h1>
<p class="sub">This script creates the <code>tnp_db</code> database, all tables, and seeds default data. Run it once.</p>

<?php
// ── Security: prevent re-run if lockfile exists ───────────────
$lockFile = __DIR__ . '/.setup_complete';
if (file_exists($lockFile) && !isset($_GET['force'])) {
    echo '<div class="step err">⚠️  Setup already completed. Delete <code>.setup_complete</code> or add <code>?force=1</code> to re-run.</div>';
    echo '<div class="done"><a href="index.html">← Go to TPMS Application</a></div>';
    echo '</div></body></html>';
    exit;
}

$steps = [];
$hasError = false;

// ── Step 1: Connect to MySQL without selecting a DB ──────────
try {
    $pdo = new PDO(
        'mysql:host=localhost;port=3306;charset=utf8mb4',
        'root', '',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    $steps[] = ['ok', 'Connected to MySQL server successfully.'];
} catch (PDOException $e) {
    $steps[] = ['err', 'Cannot connect to MySQL: ' . $e->getMessage()];
    $hasError = true;
}

if (!$hasError) {
    // ── Step 2: Create Database ──────────────────────────────
    try {
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `tnp_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE `tnp_db`");
        $steps[] = ['ok', 'Database <strong>tnp_db</strong> created / verified.'];
    } catch (PDOException $e) {
        $steps[] = ['err', 'Failed to create database: ' . $e->getMessage()];
        $hasError = true;
    }
}

if (!$hasError) {
    // ── Step 3: Run Schema ───────────────────────────────────
    $schemaFile = __DIR__ . '/sql/schema.sql';
    if (!file_exists($schemaFile)) {
        $steps[] = ['err', 'Schema file not found at sql/schema.sql'];
        $hasError = true;
    } else {
        try {
            $sql = file_get_contents($schemaFile);
            // Split by semicolons and execute each statement
            $statements = array_filter(
                array_map('trim', explode(';', $sql)),
                fn($s) => strlen($s) > 10
            );
            foreach ($statements as $stmt) {
                $pdo->exec($stmt);
            }
            $steps[] = ['ok', 'All database tables created successfully.'];
        } catch (PDOException $e) {
            $steps[] = ['err', 'Schema error: ' . $e->getMessage()];
            $hasError = true;
        }
    }
}

if (!$hasError) {
    // ── Step 4: Seed Data ────────────────────────────────────
    try {
        // Default passwords
        $adminPass   = password_hash('Admin@1234',   PASSWORD_DEFAULT);
        $studentPass = password_hash('Student@1234', PASSWORD_DEFAULT);
        $companyPass = password_hash('Company@1234', PASSWORD_DEFAULT);

        // ── Users ────────────────────────────────────────────
        $pdo->exec("DELETE FROM `activities`");
        $pdo->exec("DELETE FROM `university_apps`");
        $pdo->exec("DELETE FROM `bookmarked_jobs`");
        $pdo->exec("DELETE FROM `student_training`");
        $pdo->exec("DELETE FROM `app_timeline`");
        $pdo->exec("DELETE FROM `applications`");
        $pdo->exec("DELETE FROM `job_skills`");
        $pdo->exec("DELETE FROM `jobs`");
        $pdo->exec("DELETE FROM `companies`");
        $pdo->exec("DELETE FROM `universities`");
        $pdo->exec("DELETE FROM `training`");
        $pdo->exec("DELETE FROM `student_skills`");
        $pdo->exec("DELETE FROM `students`");
        $pdo->exec("DELETE FROM `users`");

        $userStmt = $pdo->prepare(
            "INSERT INTO `users` (`uid`,`name`,`email`,`password_hash`,`role`,`avatar`) VALUES (?,?,?,?,?,?)"
        );

        // Admin
        $userStmt->execute(['ADMIN001', 'TPO Administrator', 'admin@tpms.edu', $adminPass, 'admin',
            'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?auto=format&fit=facearea&facepad=2&w=80&h=80&q=80']);

        // Students
        $userStmt->execute(['STU2026001', 'Aarav Sharma',  'aarav.sharma@college.edu',  $studentPass, 'student',
            'https://images.unsplash.com/photo-1539571696357-5a69c17a67c6?auto=format&fit=crop&q=80&w=120']);
        $userStmt->execute(['STU2026002', 'Sneha Reddy',   'sneha.reddy@college.edu',   $studentPass, 'student',
            'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&q=80&w=120']);
        $userStmt->execute(['STU2026003', 'Rahul Verma',   'rahul.verma@college.edu',   $studentPass, 'student',
            'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&q=80&w=120']);
        $userStmt->execute(['STU2026004', 'Rohan Das',     'rohan.das@college.edu',     $studentPass, 'student',
            'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?auto=format&fit=crop&q=80&w=120']);
        $userStmt->execute(['STU2026005', 'Kriti Sen',     'kriti.sen@college.edu',     $studentPass, 'student',
            'https://images.unsplash.com/photo-1438761681033-6461ffad8d80?auto=format&fit=crop&q=80&w=120']);

        // Company user (Google recruiter)
        $userStmt->execute(['CUSER001', 'Google Recruiter', 'recruiter@google.com', $companyPass, 'company',
            'https://images.unsplash.com/photo-1549737481-c3b85d9c2a3d?auto=format&fit=crop&q=80&w=80']);

        $steps[] = ['ok', 'User accounts seeded (7 users: 1 admin, 5 students, 1 company).'];

        // ── Companies ────────────────────────────────────────
        $companyStmt = $pdo->prepare(
            "INSERT INTO `companies` (`comp_uid`,`user_id`,`name`,`website`,`industry`,`contact`,`registered_date`,`job_count`,`logo_url`) VALUES (?,?,?,?,?,?,?,?,?)"
        );
        $googleUserId = $pdo->lastInsertId();
        // Get actual user IDs
        $uids = $pdo->query("SELECT id, uid FROM users WHERE role IN ('company') ORDER BY id")->fetchAll(PDO::FETCH_KEY_PAIR);
        // company user uid → id map
        $cuserRow = $pdo->query("SELECT id FROM users WHERE uid='CUSER001'")->fetch();
        $cuId = $cuserRow['id'];

        $companyStmt->execute(['COMP001','','Google',   'https://careers.google.com',    'Technology',            'hr-in@google.com',         '2022-03-12', 3, 'https://upload.wikimedia.org/wikipedia/commons/2/2f/Google_2015_logo.svg']);
        $pdo->exec("UPDATE companies SET user_id=$cuId WHERE comp_uid='COMP001'");
        $companyStmt->execute(['COMP002','','Microsoft','https://careers.microsoft.com',  'Technology/Cloud',      'indiajobs@microsoft.com',  '2022-01-15', 2, 'https://upload.wikimedia.org/wikipedia/commons/9/96/Microsoft_logo_%282012%29.svg']);
        $companyStmt->execute(['COMP003','','Amazon',   'https://amazon.jobs',            'E-Commerce/Web Services','in-recruitment@amazon.com','2023-05-19', 4, 'https://upload.wikimedia.org/wikipedia/commons/a/a9/Amazon_logo.svg']);
        $companyStmt->execute(['COMP004','','TCS',      'https://tcs.com/careers',        'IT Services',           'campus.hiring@tcs.com',    '2021-08-01', 8, 'https://upload.wikimedia.org/wikipedia/commons/b/b1/Tata_Consultancy_Services_Logo.svg']);
        $companyStmt->execute(['COMP005','','Adobe',    'https://adobe.com/careers',      'Creative Software',     'talent@adobe.com',         '2023-11-10', 1, 'https://upload.wikimedia.org/wikipedia/commons/8/8d/Adobe_Systems_logo_and_wordmark.svg']);

        $steps[] = ['ok', 'Companies seeded (5 companies).'];

        // ── Students ─────────────────────────────────────────
        $stuMap = $pdo->query("SELECT uid, id FROM users WHERE role='student' ORDER BY id")->fetchAll(PDO::FETCH_KEY_PAIR);
        $stuStmt = $pdo->prepare(
            "INSERT INTO `students` (`user_id`,`student_uid`,`branch`,`cgpa`,`backlogs`,`placement_status`,`profile_completion`,`resume_name`) VALUES (?,?,?,?,?,?,?,?)"
        );
        $stuStmt->execute([$stuMap['STU2026001'],'STU2026001','Computer Science & Engineering', 9.24, 0,'In Progress',85,'Aarav_Sharma_Resume.pdf']);
        $stuStmt->execute([$stuMap['STU2026002'],'STU2026002','Electronics & Communication',    8.85, 0,'In Progress',78,'Sneha_Reddy_Resume.pdf']);
        $stuStmt->execute([$stuMap['STU2026003'],'STU2026003','Electronics & Communication',    8.92, 0,'Placed',      90,'Rahul_Verma_Resume.pdf']);
        $stuStmt->execute([$stuMap['STU2026004'],'STU2026004','Mechanical Engineering',         7.20, 0,'In Progress',65,'Rohan_Das_CV.pdf']);
        $stuStmt->execute([$stuMap['STU2026005'],'STU2026005','Computer Science',               9.51, 0,'Placed',      92,'Kriti_Sen_Resume.pdf']);

        // Skills for STU2026001
        $sdMap = $pdo->query("SELECT student_uid, id FROM students ORDER BY id")->fetchAll(PDO::FETCH_KEY_PAIR);
        $skillStmt = $pdo->prepare("INSERT INTO student_skills (student_id, skill) VALUES (?,?)");
        foreach (['React.js','Node.js','Python','MongoDB','Data Structures','Tailwind CSS'] as $sk)
            $skillStmt->execute([$sdMap['STU2026001'], $sk]);
        foreach (['Embedded C','VLSI','Networking','Python'] as $sk)
            $skillStmt->execute([$sdMap['STU2026002'], $sk]);

        $steps[] = ['ok', 'Students seeded (5 students with profiles).'];

        // ── Jobs ─────────────────────────────────────────────
        $compMap = $pdo->query("SELECT comp_uid, id FROM companies")->fetchAll(PDO::FETCH_KEY_PAIR);
        $jobStmt = $pdo->prepare(
            "INSERT INTO `jobs` (`job_uid`,`company_id`,`title`,`package`,`location`,`eligibility`,`deadline`,`status`,`description`,`company_logo`) VALUES (?,?,?,?,?,?,?,?,?,?)"
        );
        $jobs = [
            ['JOB001',$compMap['COMP001'],'Software Engineer - L3',       '32.5 LPA','Bangalore, India',    'B.Tech CSE/IT, CGPA >= 8.0, 0 Backlogs',       '2026-07-25','Active','We are looking for Software Engineers to join our core infrastructure and product teams.','https://upload.wikimedia.org/wikipedia/commons/2/2f/Google_2015_logo.svg'],
            ['JOB002',$compMap['COMP002'],'Azure Cloud Consultant',        '26.0 LPA','Hyderabad (Hybrid)',  'B.Tech CSE/IT/ECE, CGPA >= 7.5',               '2026-07-28','Active','Join the Microsoft Customer Success Unit to design enterprise cloud systems.','https://upload.wikimedia.org/wikipedia/commons/9/96/Microsoft_logo_%282012%29.svg'],
            ['JOB003',$compMap['COMP003'],'Systems Analyst Intern',        '18.0 LPA','Pune, India',         'B.Tech / M.Tech / MCA, CGPA >= 7.0',           '2026-07-22','Active','Optimize warehouse delivery tracking and backend analytics databases.','https://upload.wikimedia.org/wikipedia/commons/a/a9/Amazon_logo.svg'],
            ['JOB004',$compMap['COMP004'],'Digital Software Developer',    '7.5 LPA', 'Chennai, India',      'All Branches, CGPA >= 6.0',                     '2026-08-05','Active','TCS Digital hiring entry level consultants for a 3-month training program.','https://upload.wikimedia.org/wikipedia/commons/b/b1/Tata_Consultancy_Services_Logo.svg'],
            ['JOB005',$compMap['COMP005'],'UI/UX Developer',               '22.0 LPA','Noida, India',        'B.Tech / B.Des, CGPA >= 7.0',                  '2026-07-19','Active','Adobe Creative Cloud is looking for a front-end UI/UX Developer with high aesthetic taste.','https://upload.wikimedia.org/wikipedia/commons/8/8d/Adobe_Systems_logo_and_wordmark.svg'],
        ];
        foreach ($jobs as $j) $jobStmt->execute($j);

        $jobMap = $pdo->query("SELECT job_uid, id FROM jobs")->fetchAll(PDO::FETCH_KEY_PAIR);
        $jsStmt = $pdo->prepare("INSERT INTO job_skills (job_id, skill) VALUES (?,?)");
        $jobSkills = [
            'JOB001' => ['Java','C++','System Design','Algorithms'],
            'JOB002' => ['C#','Cloud Computing','Networking','Azure'],
            'JOB003' => ['SQL','Python','Data Pipelines','AWS'],
            'JOB004' => ['JavaScript','HTML/CSS','DBMS','Java'],
            'JOB005' => ['Figma','CSS/Tailwind','JavaScript','React.js'],
        ];
        foreach ($jobSkills as $uid => $skills)
            foreach ($skills as $sk)
                $jsStmt->execute([$jobMap[$uid], $sk]);

        $steps[] = ['ok', 'Jobs seeded (5 job listings with skills).'];

        // ── Applications ─────────────────────────────────────
        $appStmt = $pdo->prepare(
            "INSERT INTO `applications` (`app_uid`,`student_id`,`job_id`,`applied_date`,`status`) VALUES (?,?,?,?,?)"
        );
        $apps = [
            ['APP001',$sdMap['STU2026001'],$jobMap['JOB001'],'2026-07-12','Interview'],
            ['APP002',$sdMap['STU2026001'],$jobMap['JOB003'],'2026-07-14','Under Review'],
            ['APP003',$sdMap['STU2026002'],$jobMap['JOB002'],'2026-07-11','Shortlisted'],
            ['APP004',$sdMap['STU2026003'],$jobMap['JOB002'],'2026-07-10','Selected'],
            ['APP005',$sdMap['STU2026004'],$jobMap['JOB004'],'2026-07-15','Applied'],
        ];
        foreach ($apps as $a) $appStmt->execute($a);

        $appMap = $pdo->query("SELECT app_uid, id FROM applications")->fetchAll(PDO::FETCH_KEY_PAIR);
        $tlStmt = $pdo->prepare("INSERT INTO app_timeline (application_id,stage,stage_date,done,sort_order) VALUES (?,?,?,?,?)");
        $timelines = [
            'APP001' => [
                ['Applied','July 12, 2026',1,1],['Under Review','July 13, 2026',1,2],
                ['Shortlisted','July 14, 2026',1,3],['Interview','Scheduled: July 20, 2026',0,4],
                ['Selected','TBD',0,5]
            ],
            'APP002' => [
                ['Applied','July 14, 2026',1,1],['Under Review','July 15, 2026',1,2],
                ['Shortlisted','Pending',0,3],['Interview','TBD',0,4],['Selected','TBD',0,5]
            ],
            'APP003' => [
                ['Applied','July 11, 2026',1,1],['Under Review','July 12, 2026',1,2],
                ['Shortlisted','July 15, 2026',1,3],['Interview','Pending',0,4],['Selected','TBD',0,5]
            ],
            'APP004' => [
                ['Applied','July 10, 2026',1,1],['Under Review','July 11, 2026',1,2],
                ['Shortlisted','July 12, 2026',1,3],['Interview','July 14, 2026',1,4],
                ['Selected','July 16, 2026',1,5]
            ],
            'APP005' => [
                ['Applied','July 15, 2026',1,1],['Under Review','Pending',0,2],
                ['Shortlisted','Pending',0,3],['Interview','TBD',0,4],['Selected','TBD',0,5]
            ],
        ];
        foreach ($timelines as $appUid => $stages)
            foreach ($stages as $s)
                $tlStmt->execute([$appMap[$appUid], $s[0], $s[1], $s[2], $s[3]]);

        // Bookmarks
        $pdo->exec("INSERT INTO bookmarked_jobs (student_id,job_id) VALUES ({$sdMap['STU2026001']},{$jobMap['JOB002']})");

        $steps[] = ['ok', 'Applications and timelines seeded.'];

        // ── Training ─────────────────────────────────────────
        $trnStmt = $pdo->prepare(
            "INSERT INTO `training` (`trn_uid`,`title`,`trainer`,`trn_date`,`duration`,`status`,`description`) VALUES (?,?,?,?,?,?,?)"
        );
        $training = [
            ['TRN001','Advanced Data Structures & Algorithms','Dr. Rajesh K. (Ex-Amazon Architect)','Every Sat-Sun (July 10 - Aug 15)','30 Hours','Ongoing','Rigorous training covers Graphs, Dynamic Programming, String Algorithms, and Competitive Programming.'],
            ['TRN002','Full Stack Development with MERN Stack','Vikram Malhotra (Senior Engineer, TechVeda)','Mon-Wed-Fri (July 15 - Sept 15)','60 Hours','Upcoming','Build production-ready applications with MongoDB, Express.js, React, Node.js, Redux, and Tailwind.'],
            ['TRN003','System Design (LLD & HLD) Foundations','Suresh Pillai (Technical Director, Intel)','July 24, 25 & 26','12 Hours','Upcoming','Crash course on Microservices, Caching, Load Balancers, Database Sharding, Kafka, and OOP Design Patterns.'],
            ['TRN004','Aptitude, Soft Skills & Resume Building','Prof. Anjali Mehta (TPC Advisor)','Completed (June 01 - June 20)','20 Hours','Completed','Covers quantitative aptitude, logical reasoning, verbal ability, resume formatting, and mock HR interviews.'],
        ];
        foreach ($training as $t) $trnStmt->execute($t);

        $trnMap = $pdo->query("SELECT trn_uid, id FROM training")->fetchAll(PDO::FETCH_KEY_PAIR);
        $enrStmt = $pdo->prepare("INSERT INTO student_training (student_id, training_id) VALUES (?,?)");
        $enrStmt->execute([$sdMap['STU2026001'], $trnMap['TRN001']]);
        $enrStmt->execute([$sdMap['STU2026001'], $trnMap['TRN003']]);

        $steps[] = ['ok', 'Training programs seeded (4 programs).'];

        // ── Universities ──────────────────────────────────────
        $uniStmt = $pdo->prepare(
            "INSERT INTO `universities` (`uni_uid`,`name`,`country`,`courses`,`deadline`,`scholarship`,`fees`,`ranking`,`min_cgpa`,`website`,`logo`) VALUES (?,?,?,?,?,?,?,?,?,?,?)"
        );
        $unis = [
            ['UNI001','Harvard University','USA','M.S. in Computer Science, Ph.D. in AI','2026-12-15','Partial Scholarship Available (upto 50%)','$58,000 / year','QS Rank 4',8.5,'https://www.harvard.edu/admissions','https://upload.wikimedia.org/wikipedia/commons/0/07/Harvard_university_shield.svg'],
            ['UNI002','Stanford University','USA','M.S. in Software Engineering, M.S. in Data Science','2026-11-30','Fully Funded Fellowship Available','$62,000 / year','QS Rank 3',8.0,'https://gradadmissions.stanford.edu','https://upload.wikimedia.org/wikipedia/commons/a/a4/Seal_of_Stanford_University.svg'],
            ['UNI003','Imperial College London','UK','M.Sc. in Advanced Computing, M.Sc. in Machine Learning','2026-10-15','Commonwealth Scholarships Eligible','£36,500 / year','QS Rank 6',7.5,'https://www.imperial.ac.uk/study/pg','https://upload.wikimedia.org/wikipedia/en/b/b5/Imperial_College_London_Crest.svg'],
            ['UNI004','National University of Singapore (NUS)','Singapore','Master of Computing, M.Sc. in Quantitative Finance','2026-09-30','Tuition Fee Grant Available','S$48,000 / year','QS Rank 8',7.0,'https://nus.edu.sg/admissions','https://upload.wikimedia.org/wikipedia/commons/e/e0/National_University_of_Singapore_logo_and_seal.svg'],
            ['UNI005','University of Melbourne','Australia','Master of Information Technology','2026-11-15','Melbourne Graduate Scholarship Available','A$52,000 / year','QS Rank 14',6.5,'https://study.unimelb.edu.au','https://upload.wikimedia.org/wikipedia/commons/c/c3/Seal_of_the_University_of_Melbourne.svg'],
        ];
        foreach ($unis as $u) $uniStmt->execute($u);

        $uniMap = $pdo->query("SELECT uni_uid, id FROM universities")->fetchAll(PDO::FETCH_KEY_PAIR);
        $pdo->exec("INSERT INTO university_apps (student_id, university_id) VALUES ({$sdMap['STU2026001']},{$uniMap['UNI002']})");

        $steps[] = ['ok', 'Universities seeded (5 universities).'];

        // ── Activities ────────────────────────────────────────
        $actStmt = $pdo->prepare("INSERT INTO activities (type,text,icon) VALUES (?,?,?)");
        $activities = [
            ['placement','Rahul Verma (ECE) selected at Microsoft - CTC 45 LPA','award'],
            ['job',      'Google posted new role: Associate Software Engineer',   'briefcase'],
            ['interview','Interview scheduled for Sneha Reddy with Amazon',       'calendar'],
            ['registration','Intel Corp registered as a new recruiter partner',   'user-plus'],
            ['training', 'New training created: Full Stack Web Dev bootcamp',     'book-open'],
            ['placement','Priyan Patel (CSE) selected at Adobe - CTC 28 LPA',    'award'],
        ];
        foreach ($activities as $a) $actStmt->execute($a);

        $steps[] = ['ok', 'Activity feed seeded (6 entries).'];

        // ── Lock File ─────────────────────────────────────────
        file_put_contents($lockFile, date('Y-m-d H:i:s'));
        $steps[] = ['info', 'Lock file created. Delete <code>.setup_complete</code> to re-run setup.'];

    } catch (Exception $e) {
        $steps[] = ['err', 'Seed error: ' . $e->getMessage()];
        $hasError = true;
    }
}

// ── Output Results ────────────────────────────────────────────
foreach ($steps as [$type, $msg]) {
    echo "<div class=\"step $type\">$msg</div>\n";
}

if (!$hasError):
?>
<div class="done">
    <p style="font-size:1.25rem;font-weight:800;color:#10b981;margin-bottom:0.5rem">✅ Setup Complete!</p>
    <p style="color:#94a3b8;font-size:0.875rem;margin-bottom:1rem">
        Default credentials:<br>
        Admin: <code>admin@tpms.edu</code> / <code>Admin@1234</code><br>
        Student: <code>aarav.sharma@college.edu</code> / <code>Student@1234</code><br>
        Company: <code>recruiter@google.com</code> / <code>Company@1234</code>
    </p>
    <a href="index.html">→ Launch TPMS Application</a>
</div>
<div class="warn">
    ⚠️ <strong>Security:</strong> Delete or restrict access to <code>setup.php</code> after first run in production.
</div>
<?php else: ?>
<div class="step err" style="margin-top:1rem;">
    ❌ Setup failed. Check MySQL credentials in <code>config/database.php</code> and try again.
</div>
<?php endif; ?>

</div>
</body>
</html>
