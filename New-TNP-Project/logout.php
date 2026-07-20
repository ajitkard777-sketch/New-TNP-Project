<?php
/**
 * ============================================================
 *  TPMS — logout.php
 *  Destroys the PHP session and redirects to login.php.
 *  Linked from the sidebar "Sign Out" button via index.php.
 * ============================================================
 */
require_once __DIR__ . '/includes/auth.php';

doLogout(); // → destroys session → redirects to login.php?msg=loggedout
