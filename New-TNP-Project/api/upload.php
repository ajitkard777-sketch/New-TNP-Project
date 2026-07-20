<?php
/**
 * TPMS — api/upload.php
 * Secure resume PDF upload for students.
 * Max: 2MB. Allowed: PDF only. Stored in uploads/resumes/.
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/helpers.php';

setApiHeaders();
tpms_session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(['success' => false, 'message' => 'POST required.'], 405);
}

$user = requireRole('student');
validateCSRF();

// ── Validate Upload ───────────────────────────────────────────
if (!isset($_FILES['resume']) || $_FILES['resume']['error'] !== UPLOAD_ERR_OK) {
    $errMap = [
        UPLOAD_ERR_INI_SIZE   => 'File exceeds server max upload size.',
        UPLOAD_ERR_FORM_SIZE  => 'File exceeds form max size.',
        UPLOAD_ERR_PARTIAL    => 'File was only partially uploaded.',
        UPLOAD_ERR_NO_FILE    => 'No file was uploaded.',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder.',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
        UPLOAD_ERR_EXTENSION  => 'Upload stopped by extension.',
    ];
    $errCode = $_FILES['resume']['error'] ?? UPLOAD_ERR_NO_FILE;
    respond(['success' => false, 'message' => $errMap[$errCode] ?? 'Upload failed.'], 400);
}

$file     = $_FILES['resume'];
$maxBytes = 2 * 1024 * 1024; // 2 MB

if ($file['size'] > $maxBytes) {
    respond(['success' => false, 'message' => 'File size exceeds 2 MB limit.'], 413);
}

// Validate MIME type (do NOT trust $_FILES['type'] — use finfo)
$finfo    = new finfo(FILEINFO_MIME_TYPE);
$mimeType = $finfo->file($file['tmp_name']);
if ($mimeType !== 'application/pdf') {
    respond(['success' => false, 'message' => 'Only PDF files are allowed.'], 415);
}

// ── Prepare Upload Directory ──────────────────────────────────
$uploadDir = __DIR__ . '/../uploads/resumes/';
if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
    respond(['success' => false, 'message' => 'Failed to create upload directory.'], 500);
}

// ── Generate Safe Filename ────────────────────────────────────
$studentUid = $user['student_uid'] ?? $user['uid'];
$original   = pathinfo($file['name'], PATHINFO_FILENAME);
$safeName   = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $original);
$filename   = $studentUid . '_' . $safeName . '_' . time() . '.pdf';
$destPath   = $uploadDir . $filename;

// ── Move File ─────────────────────────────────────────────────
if (!move_uploaded_file($file['tmp_name'], $destPath)) {
    respond(['success' => false, 'message' => 'Failed to save the uploaded file.'], 500);
}

// ── Update DB ─────────────────────────────────────────────────
$pdo  = getPDO();
$stmt = $pdo->prepare(
    "UPDATE students SET resume_name=?, resume_path=? WHERE user_id=?"
);
$stmt->execute([$filename, 'uploads/resumes/' . $filename, $user['id']]);

// Update session
$_SESSION['user']['resume_name'] = $filename;

respond([
    'success'  => true,
    'message'  => 'Resume uploaded successfully!',
    'filename' => $filename,
    'path'     => 'uploads/resumes/' . $filename,
]);
