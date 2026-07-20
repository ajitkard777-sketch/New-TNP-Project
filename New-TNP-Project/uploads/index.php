<?php
// Deny direct directory listing for uploads folder
header('HTTP/1.0 403 Forbidden');
echo '403 Access Denied';
exit;
