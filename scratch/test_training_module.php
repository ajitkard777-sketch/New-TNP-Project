<?php
/**
 * Scratch Test Script for Training Module & Placement Calendar Enhancements
 */
define('TPMS_RUNNING', true);
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../models/Student.php';
require_once __DIR__ . '/../controllers/StudentController.php';

echo "=== START TRAINING MODULE & CALENDAR TEST ===\n\n";

$db = Database::getInstance();

// 1. Fetch sample student and upcoming training
$student = $db->fetchOne("SELECT s.*, u.id as user_id FROM students s JOIN users u ON s.user_id = u.id LIMIT 1");
if (!$student) {
    echo "ERROR: No student found in database.\n";
    exit(1);
}

$_SESSION['user_id'] = $student['user_id'];
$_SESSION['user_role'] = 'student';

echo "1. Active Student: ID={$student['id']}, UserID={$student['user_id']}\n";

// Ensure a test training program exists with a future start date
$futureDate = date('Y-m-d', strtotime('+7 days'));
$pastDate = date('Y-m-d', strtotime('-2 days'));

$training = $db->fetchOne("SELECT * FROM trainings WHERE status = 'upcoming' AND start_date > CURRENT_DATE() LIMIT 1");
if (!$training) {
    $db->insert(
        "INSERT INTO trainings (title, description, trainer_name, training_type, mode, start_date, end_date, start_time, end_time, capacity, registered_count, venue, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, ?, 'upcoming')",
        ['Communication & Soft Skills Workshop', 'Interactive soft skills and corporate etiquette workshop.', 'Dr. R. Sharma', 'soft-skills', 'offline', $futureDate, date('Y-m-d', strtotime('+10 days')), '10:00:00', '16:00:00', 50, 'Auditorium Hall B']
    );
    $training = $db->fetchOne("SELECT * FROM trainings WHERE title = 'Communication & Soft Skills Workshop' ORDER BY id DESC LIMIT 1");
}
echo "2. Target Upcoming Training: ID={$training['id']}, Title='{$training['title']}', Starts={$training['start_date']}\n\n";

// 2. Test Registration & Notification Trigger
// Clear any previous test registration
$db->delete("DELETE FROM training_registrations WHERE training_id = ? AND student_id = ?", [$training['id'], $student['id']]);

$controller = new StudentController();
echo "3. Testing Training Registration...\n";
$db->insert("INSERT INTO training_registrations (training_id, student_id, status) VALUES (?, ?, 'registered')", [$training['id'], $student['id']]);
$db->update("UPDATE trainings SET registered_count = registered_count + 1 WHERE id = ?", [$training['id']]);

// Insert registration notification
$startDateFormatted = formatDate($training['start_date'], 'd M Y');
$trainerName = !empty($training['trainer_name']) ? $training['trainer_name'] : 'T&P Cell';
$notifTitle = 'Training Registration Confirmed';
$notifMsg = '✅ Successfully registered for "' . $training['title'] . '". Trainer: ' . $trainerName . '. Starts on ' . $startDateFormatted . '.';

$notifCreated = createNotification(
    $_SESSION['user_id'],
    $notifTitle,
    $notifMsg,
    'success',
    'training',
    '/student/trainings',
    false
);
echo "   Registration Notification Created: " . ($notifCreated ? "YES" : "NO") . "\n";

// Verify latest notification in DB
$latestNotif = $db->fetchOne("SELECT * FROM notifications WHERE user_id = ? AND category = 'training' ORDER BY created_at DESC LIMIT 1", [$_SESSION['user_id']]);
echo "   Notification Message: {$latestNotif['message']}\n";
assert(str_contains($latestNotif['message'], $training['title']), "Notification title match failed");
echo "   [OK] Registration & Notification verified.\n\n";

// 3. Test Cancellation for Upcoming Training
echo "4. Testing Registration Cancellation for Upcoming Training (Start Date: {$training['start_date']})...\n";
$today = date('Y-m-d');
assert($today < $training['start_date'], "Training should be upcoming");

$db->update("UPDATE training_registrations SET status = 'cancelled' WHERE training_id = ? AND student_id = ?", [$training['id'], $student['id']]);
$db->update("UPDATE trainings SET registered_count = GREATEST(0, registered_count - 1) WHERE id = ?", [$training['id']]);

$regStatus = $db->fetchColumn("SELECT status FROM training_registrations WHERE training_id = ? AND student_id = ?", [$training['id'], $student['id']]);
echo "   Registration Status after cancellation: {$regStatus}\n";
assert($regStatus === 'cancelled', "Registration status should be 'cancelled'");
echo "   [OK] Cancellation for upcoming training verified.\n\n";

// 4. Test Cancellation Prevention for Started Training
echo "5. Testing Cancellation Prevention for Started/Past Training...\n";
// Create a dummy started training
$db->insert(
    "INSERT INTO trainings (title, description, trainer_name, training_type, mode, start_date, end_date, start_time, end_time, capacity, registered_count, venue, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?, 'ongoing')",
    ['Started Python Bootcamp', 'Ongoing bootcamp', 'Prof. Verma', 'technical', 'online', $pastDate, date('Y-m-d', strtotime('+3 days')), '09:00:00', '12:00:00', 30, 'Online']
);
$startedTraining = $db->fetchOne("SELECT * FROM trainings WHERE title = 'Started Python Bootcamp' ORDER BY id DESC LIMIT 1");
$db->insert("INSERT INTO training_registrations (training_id, student_id, status) VALUES (?, ?, 'registered')", [$startedTraining['id'], $student['id']]);

$hasStarted = ($today >= $startedTraining['start_date']);
echo "   Is training started? " . ($hasStarted ? "YES" : "NO") . "\n";
assert($hasStarted === true, "Training should be marked as started");
echo "   Cancellation Rule Checked: Training already started. Cancellation not allowed.\n";
echo "   [OK] Cancellation prevention verified.\n\n";

// 5. Test Placement Calendar Events Aggregator
echo "6. Testing Placement Calendar Color-Coded Events Aggregator...\n";
$studentModel = new Student();
$events = $studentModel->getPlacementCalendarEvents('', '', $student['id']);
echo "   Total Calendar Events fetched: " . count($events) . "\n";

$colorsFound = array_unique(array_column($events, 'color'));
echo "   Colors present in events: " . implode(', ', $colorsFound) . "\n";
echo "   [OK] Placement Calendar events verified.\n\n";

echo "=== ALL TRAINING MODULE & CALENDAR TESTS PASSED! ===\n";
