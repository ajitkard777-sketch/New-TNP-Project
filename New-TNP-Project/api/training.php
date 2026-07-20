<?php
/**
 * TPMS — api/training.php
 * CRUD training programs; student enrollment.
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/helpers.php';

setApiHeaders();
tpms_session_start();

$action = get_param('action') ?: post('action') ?: 'list';

switch ($action) {
    case 'list':     handleList();     break;
    case 'create':   handleCreate();   break;
    case 'enroll':   handleEnroll();   break;
    case 'unenroll': handleUnenroll(); break;
    default: respond(['success' => false, 'message' => 'Unknown action'], 400);
}

function handleList(): void
{
    requireAuth();
    $pdo  = getPDO();
    $rows = $pdo->query(
        "SELECT trn_uid AS id, title, trainer, trn_date AS date, duration, status, description
         FROM training ORDER BY FIELD(status,'Ongoing','Upcoming','Completed')"
    )->fetchAll();
    respond(['success' => true, 'training' => $rows]);
}

function handleCreate(): void
{
    requireRole('admin');
    validateCSRF();

    $title   = post('title');
    $trainer = post('trainer');
    $dur     = post('duration');
    $date    = post('date');

    if (!$title) respond(['success' => false, 'message' => 'Training title required.'], 422);

    $pdo = getPDO();
    $uid = 'TRN' . substr((string)(microtime(true)*10000), -6);

    $pdo->prepare(
        "INSERT INTO training (trn_uid,title,trainer,trn_date,duration,status) VALUES (?,?,?,?,?,'Upcoming')"
    )->execute([$uid, $title, $trainer, $date, $dur]);

    $pdo->prepare("INSERT INTO activities (type,text,icon) VALUES (?,?,?)")
        ->execute(['training', "New Training: $title", 'book-open']);

    respond(['success' => true, 'message' => "Training \"$title\" created!", 'id' => $uid]);
}

function handleEnroll(): void
{
    $user = requireRole('student');
    validateCSRF();

    $trnUid = post('trn_uid');
    $pdo    = getPDO();
    $stuId  = $user['student_db_id'];

    $trn = $pdo->prepare("SELECT id FROM training WHERE trn_uid=?");
    $trn->execute([$trnUid]);
    $trnRow = $trn->fetch();
    if (!$trnRow) respond(['success' => false, 'message' => 'Training program not found.'], 404);

    try {
        $pdo->prepare("INSERT INTO student_training (student_id,training_id) VALUES (?,?)")
            ->execute([$stuId, $trnRow['id']]);
        respond(['success' => true, 'message' => 'Enrolled in training successfully!']);
    } catch (\PDOException) {
        respond(['success' => false, 'message' => 'Already enrolled in this training.'], 409);
    }
}

function handleUnenroll(): void
{
    $user = requireRole('student');
    validateCSRF();

    $trnUid = post('trn_uid');
    $pdo    = getPDO();
    $stuId  = $user['student_db_id'];

    $pdo->prepare(
        "DELETE st FROM student_training st
         JOIN training t ON t.id=st.training_id
         WHERE st.student_id=? AND t.trn_uid=?"
    )->execute([$stuId, $trnUid]);

    respond(['success' => true, 'message' => 'Unenrolled from training.']);
}
