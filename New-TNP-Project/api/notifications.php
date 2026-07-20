<?php
/**
 * TPMS — api/notifications.php
 * Activity feed and notification list.
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/helpers.php';

setApiHeaders();
tpms_session_start();

$action = get_param('action') ?: 'list';
switch ($action) {
    case 'list': handleList(); break;
    case 'add':  handleAdd();  break;
    default: respond(['success' => false, 'message' => 'Unknown action'], 400);
}

function handleList(): void
{
    requireAuth();
    $pdo  = getPDO();
    $rows = $pdo->query(
        "SELECT id, type, text, icon,
                CASE
                  WHEN TIMESTAMPDIFF(MINUTE,created_at,NOW()) < 60
                    THEN CONCAT(TIMESTAMPDIFF(MINUTE,created_at,NOW()),' mins ago')
                  WHEN TIMESTAMPDIFF(HOUR,created_at,NOW()) < 24
                    THEN CONCAT(TIMESTAMPDIFF(HOUR,created_at,NOW()),' hours ago')
                  ELSE CONCAT(TIMESTAMPDIFF(DAY,created_at,NOW()),' days ago')
                END AS time
         FROM activities ORDER BY created_at DESC LIMIT 10"
    )->fetchAll();

    respond(['success' => true, 'activities' => $rows]);
}

function handleAdd(): void
{
    requireRole('admin');
    validateCSRF();

    $type = post('type');
    $text = post('text');
    $icon = post('icon') ?: 'bell';

    if (!$text) respond(['success' => false, 'message' => 'Activity text required.'], 422);

    $pdo = getPDO();
    $pdo->prepare("INSERT INTO activities (type,text,icon) VALUES (?,?,?)")->execute([$type, $text, $icon]);

    respond(['success' => true, 'message' => 'Activity logged.']);
}
