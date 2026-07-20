<?php
/**
 * TPMS — api/applications.php
 * Student: apply for job, bookmark. Company/Admin: update status.
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/helpers.php';

setApiHeaders();
tpms_session_start();

$action = get_param('action') ?: post('action') ?: 'list';

switch ($action) {
    case 'list':          handleList();         break;
    case 'apply':         handleApply();        break;
    case 'update_status': handleUpdateStatus(); break;
    case 'bookmark':      handleBookmark();     break;
    case 'unbookmark':    handleUnbookmark();   break;
    default: respond(['success' => false, 'message' => 'Unknown action'], 400);
}

function handleList(): void
{
    $user = requireAuth();
    $pdo  = getPDO();

    if ($user['role'] === 'student') {
        $stuId = $user['student_db_id'];
        $apps  = $pdo->prepare(
            "SELECT a.app_uid AS id, j.job_uid AS jobId, j.title AS jobTitle,
                    c.name AS companyName, DATE_FORMAT(a.applied_date,'%Y-%m-%d') AS appliedDate, a.status
             FROM applications a
             JOIN jobs j ON j.id=a.job_id JOIN companies c ON c.id=j.company_id
             WHERE a.student_id=? ORDER BY a.created_at DESC"
        );
        $apps->execute([$stuId]);
        respond(['success' => true, 'applications' => $apps->fetchAll()]);
    }
    respond(['success' => false, 'message' => 'Not authorized.'], 403);
}

function handleApply(): void
{
    $user = requireRole('student');
    validateCSRF();

    $jobUid = post('job_uid');
    $pdo    = getPDO();
    $stuId  = $user['student_db_id'];

    // Get job DB id
    $job = $pdo->prepare("SELECT id FROM jobs WHERE job_uid=?");
    $job->execute([$jobUid]);
    $jobRow = $job->fetch();
    if (!$jobRow) respond(['success' => false, 'message' => 'Job not found.'], 404);

    // Check duplicate
    $dup = $pdo->prepare("SELECT id FROM applications WHERE student_id=? AND job_id=?");
    $dup->execute([$stuId, $jobRow['id']]);
    if ($dup->fetch()) respond(['success' => false, 'message' => 'You have already applied for this job.'], 409);

    $appUid = 'APP' . substr((string)(microtime(true)*10000), -6);

    $pdo->prepare(
        "INSERT INTO applications (app_uid,student_id,job_id,applied_date,status) VALUES (?,?,?,CURDATE(),'Applied')"
    )->execute([$appUid, $stuId, $jobRow['id']]);

    $appDbId = (int)$pdo->lastInsertId();

    // Insert standard timeline
    $stages = ['Applied','Under Review','Shortlisted','Interview','Selected'];
    $tl = $pdo->prepare("INSERT INTO app_timeline (application_id,stage,stage_date,done,sort_order) VALUES (?,?,?,?,?)");
    foreach ($stages as $i => $stage) {
        $date = ($i === 0) ? date('F j, Y') : 'Pending';
        $done = ($i === 0) ? 1 : 0;
        $tl->execute([$appDbId, $stage, $date, $done, $i + 1]);
    }

    // Log activity
    $jobInfo = $pdo->prepare("SELECT j.title, c.name FROM jobs j JOIN companies c ON c.id=j.company_id WHERE j.id=?");
    $jobInfo->execute([$jobRow['id']]);
    $jInfo = $jobInfo->fetch();
    $pdo->prepare("INSERT INTO activities (type,text,icon) VALUES (?,?,?)")
        ->execute(['job', "{$user['name']} applied for {$jInfo['title']} at {$jInfo['name']}", 'briefcase']);

    respond(['success' => true, 'message' => 'Application submitted successfully!', 'app_uid' => $appUid]);
}

function handleUpdateStatus(): void
{
    $user = requireRole(['admin','company']);
    validateCSRF();

    $appUid    = post('app_uid');
    $newStatus = post('status');

    $valid = ['Applied','Under Review','Shortlisted','Interview','Selected','Rejected'];
    if (!in_array($newStatus, $valid, true))
        respond(['success' => false, 'message' => 'Invalid status value.'], 422);

    $pdo  = getPDO();
    $stmt = $pdo->prepare("UPDATE applications SET status=? WHERE app_uid=?");
    $stmt->execute([$newStatus, $appUid]);

    if ($stmt->rowCount() === 0)
        respond(['success' => false, 'message' => 'Application not found.'], 404);

    // Update timeline: mark stages up to this one as done
    $stageOrder = ['Applied'=>1,'Under Review'=>2,'Shortlisted'=>3,'Interview'=>4,'Selected'=>5,'Rejected'=>5];
    $order = $stageOrder[$newStatus] ?? 0;

    $pdo->prepare(
        "UPDATE app_timeline at2
         JOIN applications a ON a.id=at2.application_id
         SET at2.done = CASE WHEN at2.sort_order <= ? THEN 1 ELSE 0 END,
             at2.stage_date = CASE WHEN at2.sort_order = ? AND at2.stage_date IN ('Pending','TBD') THEN ? ELSE at2.stage_date END
         WHERE a.app_uid=?"
    )->execute([$order, $order, date('F j, Y'), $appUid]);

    respond(['success' => true, 'message' => "Status updated to '$newStatus'."]);
}

function handleBookmark(): void
{
    $user = requireRole('student');
    validateCSRF();

    $jobUid = post('job_uid');
    $pdo    = getPDO();
    $stuId  = $user['student_db_id'];

    $job = $pdo->prepare("SELECT id FROM jobs WHERE job_uid=?");
    $job->execute([$jobUid]);
    $jobRow = $job->fetch();
    if (!$jobRow) respond(['success' => false, 'message' => 'Job not found.'], 404);

    try {
        $pdo->prepare("INSERT INTO bookmarked_jobs (student_id,job_id) VALUES (?,?)")->execute([$stuId, $jobRow['id']]);
        respond(['success' => true, 'message' => 'Job saved to bookmarks.']);
    } catch (\PDOException) {
        respond(['success' => false, 'message' => 'Job already bookmarked.'], 409);
    }
}

function handleUnbookmark(): void
{
    $user = requireRole('student');
    validateCSRF();

    $jobUid = post('job_uid');
    $pdo    = getPDO();
    $stuId  = $user['student_db_id'];

    $pdo->prepare(
        "DELETE b FROM bookmarked_jobs b
         JOIN jobs j ON j.id=b.job_id WHERE b.student_id=? AND j.job_uid=?"
    )->execute([$stuId, $jobUid]);

    respond(['success' => true, 'message' => 'Job removed from bookmarks.']);
}
