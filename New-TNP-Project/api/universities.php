<?php
/**
 * TPMS — api/universities.php
 * List universities; student application.
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/helpers.php';

setApiHeaders();
tpms_session_start();

$action = get_param('action') ?: post('action') ?: 'list';

switch ($action) {
    case 'list':  handleList();  break;
    case 'apply': handleApply(); break;
    default: respond(['success' => false, 'message' => 'Unknown action'], 400);
}

function handleList(): void
{
    requireAuth();
    $pdo  = getPDO();
    $rows = $pdo->query(
        "SELECT uni_uid AS id, name, country, courses,
                DATE_FORMAT(deadline,'%Y-%m-%d') AS deadline,
                scholarship, fees, ranking,
                min_cgpa AS minCGPA, website, logo
         FROM universities ORDER BY min_cgpa DESC"
    )->fetchAll();
    respond(['success' => true, 'universities' => $rows]);
}

function handleApply(): void
{
    $user = requireRole('student');
    validateCSRF();

    $uniUid = post('uni_uid');
    $pdo    = getPDO();
    $stuId  = $user['student_db_id'];

    $uni = $pdo->prepare("SELECT id, name, min_cgpa FROM universities WHERE uni_uid=?");
    $uni->execute([$uniUid]);
    $uniRow = $uni->fetch();
    if (!$uniRow) respond(['success' => false, 'message' => 'University not found.'], 404);

    // CGPA eligibility check
    if ((float)$user['cgpa'] < (float)$uniRow['min_cgpa'])
        respond(['success' => false, 'message' => "You need a minimum CGPA of {$uniRow['min_cgpa']} to apply to {$uniRow['name']}."], 403);

    try {
        $pdo->prepare("INSERT INTO university_apps (student_id,university_id) VALUES (?,?)")
            ->execute([$stuId, $uniRow['id']]);

        $pdo->prepare("INSERT INTO activities (type,text,icon) VALUES (?,?,?)")
            ->execute(['registration', "{$user['name']} applied to {$uniRow['name']}", 'globe']);

        respond(['success' => true, 'message' => "Application submitted to {$uniRow['name']}!"]);
    } catch (\PDOException) {
        respond(['success' => false, 'message' => 'You have already applied to this university.'], 409);
    }
}
