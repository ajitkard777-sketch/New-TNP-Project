<?php
/**
 * ============================================================
 *  TPMS — index.php
 *  Protected entry point for the SPA.
 *
 *  1. Checks PHP session — if no session, redirects to login.php.
 *  2. Reads the existing index.html (unchanged).
 *  3. Injects a small <script> block that pre-populates
 *     window.__tpmsAuth so app.js can auto-navigate to the
 *     correct dashboard without touching the SPA logic.
 * ============================================================
 */
require_once __DIR__ . '/includes/auth.php';

requireLogin();          // → redirects to login.php if not logged in
$user = getSessionUser();

// ── Read the existing SPA shell ───────────────────────────────
$spaPath = __DIR__ . '/index.html';
if (!file_exists($spaPath)) {
    http_response_code(500);
    die('SPA shell (index.html) not found.');
}
$html = file_get_contents($spaPath);

// ── Build the PHP→JS auth bridge ─────────────────────────────
// Safely encode session data as JSON so JS can read it.
$authJson = json_encode([
    'role'  => $user['role'],
    'name'  => $user['name'],
    'email' => $user['email'],
    'id'    => $user['id'],
], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);

$bridge = <<<SCRIPT
<script id="tpms-auth-bridge">
/* ── TPMS PHP Session Bridge (auto-generated — do not edit) ── */
window.__tpmsAuth = {$authJson};
</script>
SCRIPT;

// ── Inject bridge just before the first <script src="js/... ──
// This ensures window.__tpmsAuth is available when mockData.js
// and app.js execute.
$html = str_replace(
    '<script src="js/mockData.js">',
    $bridge . "\n    " . '<script src="js/mockData.js">',
    $html
);

// ── Output the page ───────────────────────────────────────────
header('Content-Type: text/html; charset=UTF-8');
header('Cache-Control: no-store');    // prevent caching of auth-sensitive page
echo $html;
