<?php
/**
 * TPMS — api/students.php
 * Admin CRUD for student registry.
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/helpers.php';

setApiHeaders();
tpms_session_start();

$action = get_param('action') ?: post('action') ?: 'list';

switch ($action) {
    case 'list':   handleList();   break;
    case 'get':    handleGet();    break;
    case 'update': handleUpdate(); break;
    case 'delete': handleDelete(); break;
    default: respond(['success' => false, 'message' => 'Unknown action'], 400);
}

function handleList(): void
{
    requireRole('admin');
    $pdo    = getPDO();
    $search = get_param('search');
    $branch = get_param('branch');
    $status = get_param('status');

    $where  = ['1=1'];
    $params = [];

    if ($search) {
        $where[]  = '(u.name LIKE ? OR u.uid LIKE ?)';
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    if ($branch) { $where[] = 's.branch LIKE ?';            $params[] = "%$branch%"; }
    if ($status) { $where[] = 's.placement_status = ?';     $params[] = $status; }

    $sql  = "SELECT u.uid AS id, u.name, u.avatar, s.branch, s.cgpa,
                    s.placement_status AS status, s.resume_name AS resume,
                    s.backlogs, s.phone, u.email
             FROM students s JOIN users u ON u.id=s.user_id
             WHERE " . implode(' AND ', $where) . "
             ORDER BY s.cgpa DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    respond(['success' => true, 'students' => $stmt->fetchAll()]);
}

function handleGet(): void
{
    requireAuth();
    $uid = get_param('id');
    $pdo = getPDO();

    $stmt = $pdo->prepare(
        "SELECT u.uid AS id, u.name, u.email, u.avatar,
                s.branch, s.cgpa, s.backlogs, s.phone, s.placement_status AS status,
                s.resume_name AS resume, s.profile_completion,
                GROUP_CONCAT(sk.skill ORDER BY sk.id SEPARATOR '|') AS skills_raw
         FROM students s
         JOIN users u ON u.id=s.user_id
         LEFT JOIN student_skills sk ON sk.student_id=s.id
         WHERE u.uid=? GROUP BY u.id"
    );
    $stmt->execute([$uid]);
    $row = $stmt->fetch();

    if (!$row) respond(['success' => false, 'message' => 'Student not found.'], 404);
    $row['skills'] = $row['skills_raw'] ? explode('|', $row['skills_raw']) : [];
    unset($row['skills_raw']);

    respond(['success' => true, 'student' => $row]);
}

function handleUpdate(): void
{
    requireRole('admin');
    validateCSRF();

    $uid    = post('id');
    $status = post('status');
    $branch = post('branch');
    $cgpa   = (float) post('cgpa');

    $pdo  = getPDO();
    $stmt = $pdo->prepare(
        "UPDATE students s JOIN users u ON u.id=s.user_id
         SET s.placement_status=?, s.branch=?, s.cgpa=?
         WHERE u.uid=?"
    );
    $stmt->execute([$status, $branch, $cgpa, $uid]);
    respond(['success' => true, 'message' => 'Student updated.']);
}

function handleDelete(): void
{
    requireRole('admin');
    validateCSRF();

    $uid = post('id') ?: get_param('id');
    $pdo = getPDO();

    $stmt = $pdo->prepare("DELETE u FROM users u WHERE u.uid=? AND u.role='student'");
    $stmt->execute([$uid]);

    if ($stmt->rowCount() === 0)
        respond(['success' => false, 'message' => 'Student not found.'], 404);

    // Log activity
    $pdo->prepare("INSERT INTO activities (type,text,icon) VALUES (?,?,?)")
        ->execute(['admin', "Student $uid removed from registry.", 'user-minus']);

    respond(['success' => true, 'message' => "Student $uid removed from registry."]);
}
