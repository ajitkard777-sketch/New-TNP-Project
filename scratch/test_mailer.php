<?php
define('TPMS_RUNNING', true);
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/Mailer.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

echo "=== TESTING SMTP EMAIL DELIVERY ===\n";
echo "Host: " . SMTP_HOST . "\n";
echo "Port: " . SMTP_PORT . "\n";
echo "Username: " . SMTP_USERNAME . "\n";
echo "From Email: " . SMTP_FROM_EMAIL . "\n\n";

try {
    $mail = new PHPMailer(true);
    $mail->SMTPDebug = SMTP::DEBUG_SERVER; // Verbose debug output
    $mail->Debugoutput = function($str, $level) {
        echo "[SMTP Debug $level] $str\n";
    };

    $mail->isSMTP();
    $mail->Host       = SMTP_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = SMTP_USERNAME;
    $mail->Password   = SMTP_PASSWORD;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = SMTP_PORT;
    $mail->CharSet    = 'UTF-8';

    $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
    $mail->addAddress(SMTP_USERNAME, 'Test Recipient');
    $mail->isHTML(true);
    $mail->Subject = 'TPMS Test Password Reset Email';
    $mail->Body    = '<h1>TPMS Test Email</h1><p>This is a test email for SMTP verification.</p>';

    $mail->send();
    echo "\nSUCCESS: Email delivered via SMTP!\n";
} catch (Exception $e) {
    echo "\nFAILED: " . $e->getMessage() . "\n";
}
