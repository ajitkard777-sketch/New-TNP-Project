<?php
/**
 * TPMS — api/jobs.php
 * CRUD for job listings (admin create; public list).
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/helpers.php';

setApiHeaders();
tpms_session_start();

$action = get_param('action') ?: post('action') ?: 'list';

switch ($action) {
    case 'list':   handleList();   break;
    case 'get':    handleGet();    break;
    case 'create': handleCreate(); break;
    case 'delete': handleDelete(); break;
    default: respond(['success' => false, 'message' => 'Unknown action'], 400);
}

function handleList(): void
{
    requireAuth();
    $pdo  = getPDO();
    $user = $_SESSION['user'];

    $where  = 'j.status = "Active"';
    $params = [];

    // Company sees only their own jobs
    if ($user['role'] === 'company') {
        $where .= " AND j.company_id = (SELECT id FROM companies WHERE comp_uid=?)";
        $params[] = $user['comp_uid'];
    }

    $jobs = $pdo->prepare(
        "SELECT j.job_uid AS id, c.comp_uid AS companyId, c.name AS companyName,
                j.title, j.package, j.location, j.eligibility,
                DATE_FORMAT(j.deadline,'%Y-%m-%d') AS deadline,
                j.status, j.description, j.company_logo AS companyLogo
         FROM jobs j JOIN companies c ON c.id=j.company_id
         WHERE $where ORDER BY j.created_at DESC"
    );
    $jobs->execute($params);
    $jobList = $jobs->fetchAll();

    // Attach skills
    $skillMap = [];
    $sRows = $pdo->query("SELECT j.job_uid, js.skill FROM job_skills js JOIN jobs j ON j.id=js.job_id")->fetchAll();
    foreach ($sRows as $r) $skillMap[$r['job_uid']][] = $r['skill'];
    foreach ($jobList as &$j) $j['skills'] = $skillMap[$j['id']] ?? [];

    respond(['success' => true, 'jobs' => $jobList]);
}

function handleGet(): void
{
    requireAuth();
    $uid = get_param('id');
    $pdo = getPDO();

    $stmt = $pdo->prepare(
        "SELECT j.job_uid AS id, c.comp_uid AS companyId, c.name AS companyName,
                j.title, j.package, j.location, j.eligibility,
                DATE_FORMAT(j.deadline,'%Y-%m-%d') AS deadline,
                j.status, j.description, j.company_logo AS companyLogo
         FROM jobs j JOIN companies c ON c.id=j.company_id WHERE j.job_uid=?"
    );
    $stmt->execute([$uid]);
    $job = $stmt->fetch();
    if (!$job) respond(['success' => false, 'message' => 'Job not found.'], 404);

    $sk = $pdo->prepare("SELECT skill FROM job_skills WHERE job_id=(SELECT id FROM jobs WHERE job_uid=?)");
    $sk->execute([$uid]);
    $job['skills'] = array_column($sk->fetchAll(), 'skill');

    respond(['success' => true, 'job' => $job]);
}

function handleCreate(): void
{
    requireRole('admin');
    validateCSRF();

    $compName = post('company');
    $title    = post('title');
    $ctc      = post('package');
    $location = post('location');
    $skills   = post('skills');
    $cgpa     = (float) post('cgpa');
    $deadline = post('deadline');

    if (!$title || !$compName) respond(['success' => false, 'message' => 'Title and company required.'], 422);

    $pdo = getPDO();

    // Find or create company
    $comp = $pdo->prepare("SELECT id, comp_uid, logo_url FROM companies WHERE name LIKE ? LIMIT 1");
    $comp->execute(["%$compName%"]);
    $compRow = $comp->fetch();

    if (!$compRow) {
        $compUid = 'COMP' . substr((string)(microtime(true)*10000), -6);
        $pdo->prepare("INSERT INTO companies (comp_uid,name,registered_date) VALUES (?,?,CURDATE())")->execute([$compUid, $compName]);
        $compId  = (int)$pdo->lastInsertId();
        $logoUrl = '';
    } else {
        $compId  = (int)$compRow['id'];
        $compUid = $compRow['comp_uid'];
        $logoUrl = $compRow['logo_url'];
    }

    $uid = 'JOB' . substr((string)(microtime(true)*10000), -6);
    $eligibility = "B.Tech, CGPA >= $ctc";
    if ($cgpa > 0) $eligibility = "B.Tech, CGPA >= " . number_format($cgpa, 1);

    $pdo->prepare(
        "INSERT INTO jobs (job_uid,company_id,title,package,location,eligibility,deadline,status,description,company_logo)
         VALUES (?,?,?,?,?,?,?,?,?,?)"
    )->execute([$uid, $compId, $title, $ctc, $location, $eligibility, $deadline, 'Active', 'Opening published via administrator panel.', $logoUrl]);

    $jobId = (int)$pdo->lastInsertId();
    if ($skills) {
        $skArr = array_map('trim', explode(',', $skills));
        $sk = $pdo->prepare("INSERT INTO job_skills (job_id,skill) VALUES (?,?)");
        foreach ($skArr as $s) if ($s) $sk->execute([$jobId, $s]);
    }

    // Update company job count
    $pdo->exec("UPDATE companies SET job_count=job_count+1 WHERE id=$compId");

    // Activity
    $pdo->prepare("INSERT INTO activities (type,text,icon) VALUES (?,?,?)")
        ->execute(['job', "$compName published: $title — CTC $ctc", 'briefcase']);

    respond(['success' => true, 'message' => "Job listing published for $compName!", 'id' => $uid]);
}

function handleDelete(): void
{
    requireRole('admin');
    validateCSRF();

    $uid = post('id') ?: get_param('id');
    $pdo = getPDO();
    $pdo->prepare("DELETE FROM jobs WHERE job_uid=?")->execute([$uid]);
    respond(['success' => true, 'message' => "Job $uid removed."]);
}
