<?php
/**
 * TPMS - Email Service using PHPMailer
 * Handles all outgoing emails via Brevo SMTP
 * Provider: https://app.brevo.com
 */

require_once ROOT_PATH . '/vendor/phpmailer/src/Exception.php';
require_once ROOT_PATH . '/vendor/phpmailer/src/PHPMailer.php';
require_once ROOT_PATH . '/vendor/phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class Mailer {

    private static string $lastError = '';

    /** Status file for admin Email Config page */
    private static string $statusFile = '';

    private static function statusFile(): string {
        if (self::$statusFile === '') {
            self::$statusFile = (defined('LOGS_PATH') ? LOGS_PATH : ROOT_PATH . '/logs') . '/email_status.json';
        }
        return self::$statusFile;
    }

    /**
     * Get the last error message
     */
    public static function getLastError(): string {
        return self::$lastError;
    }

    /**
     * Log email activity to logs/email.log
     */
    private static function logMail(string $message, string $type = 'INFO'): void {
        $logDir = defined('LOGS_PATH') ? LOGS_PATH : ROOT_PATH . '/logs';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }
        $logFile = $logDir . '/email.log';
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = "[{$timestamp}] [{$type}] {$message}" . PHP_EOL;
        @file_put_contents($logFile, $logEntry, FILE_APPEND);
    }

    /**
     * Update the email_status.json file after a successful send
     */
    private static function updateEmailStatus(string $toEmail, string $subject): void {
        $data = [
            'last_sent_at'      => date('Y-m-d H:i:s'),
            'last_sent_to'      => $toEmail,
            'last_sent_subject' => $subject,
            'provider'          => 'Brevo (' . SMTP_HOST . ')',
        ];
        @file_put_contents(self::statusFile(), json_encode($data, JSON_PRETTY_PRINT));
    }

    /**
     * Get SMTP status info for the admin Email Config page
     * Returns array with provider, host, port, from_email, last_sent_at, connection_ok
     */
    public static function getSmtpStatus(): array {
        // Read last-sent status
        $statusFile = self::statusFile();
        $lastSent = null;
        if (file_exists($statusFile)) {
            $raw = @file_get_contents($statusFile);
            if ($raw) $lastSent = json_decode($raw, true);
        }

        // Quick TCP socket check (non-sending, just port reachability)
        $host    = defined('SMTP_HOST') ? SMTP_HOST : 'smtp-relay.brevo.com';
        $port    = defined('SMTP_PORT') ? SMTP_PORT : 587;
        $connOk  = false;
        $socket  = @fsockopen($host, $port, $errno, $errstr, 5);
        if ($socket) {
            $connOk = true;
            @fclose($socket);
        }

        $encryption = ($port === 465) ? 'SSL/TLS (Port 465)' : 'STARTTLS (Port 587)';

        return [
            'provider'       => 'Brevo SMTP',
            'host'           => $host,
            'port'           => $port,
            'encryption'     => $encryption,
            'from_email'     => defined('SMTP_FROM_EMAIL') ? SMTP_FROM_EMAIL : '-',
            'from_name'      => defined('SMTP_FROM_NAME')  ? SMTP_FROM_NAME  : '-',
            'username'       => defined('SMTP_USERNAME')   ? SMTP_USERNAME   : '-',
            'configured'     => defined('SMTP_PASSWORD') && !empty(SMTP_PASSWORD),
            'connection_ok'  => $connOk,
            'last_sent_at'      => $lastSent['last_sent_at']      ?? null,
            'last_sent_to'      => $lastSent['last_sent_to']      ?? null,
            'last_sent_subject' => $lastSent['last_sent_subject'] ?? null,
        ];
    }

    /**
     * Create a configured PHPMailer instance
     */
    private static function create(): PHPMailer {
        self::$lastError = '';

        if (!defined('SMTP_HOST') || !defined('SMTP_USERNAME') || !defined('SMTP_PASSWORD') ||
            empty(SMTP_HOST) || empty(SMTP_USERNAME) || empty(SMTP_PASSWORD)) {
            $msg = 'SMTP configuration is incomplete. Please set SMTP credentials in the .env file.';
            self::$lastError = $msg;
            self::logMail($msg, 'CRITICAL');
            throw new Exception($msg);
        }

        $mail = new PHPMailer(true);

        // Brevo SMTP server settings
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->AuthType   = 'LOGIN'; // Brevo SMTP keys require LOGIN auth (not CRAM-MD5)
        $mail->Username   = SMTP_USERNAME;
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = (SMTP_PORT === 465) ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;
        $mail->CharSet    = 'UTF-8';
        $mail->Timeout    = 20;

        // Capture SMTP debug output into email log on error
        $mail->SMTPDebug   = SMTP::DEBUG_OFF;
        $mail->Debugoutput = function($str, $level) {
            self::logMail("SMTP DEBUG [L{$level}]: " . trim($str), 'DEBUG');
        };

        // Sender
        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->isHTML(true);

        return $mail;
    }

    /**
     * Send a PHPMailer instance with automatic retry on temporary SMTP failure.
     * Retries up to SMTP_RETRY_ATTEMPTS times on transient errors only.
     */
    private static function sendWithRetry(PHPMailer $mail): void {
        $maxAttempts = defined('SMTP_RETRY_ATTEMPTS') ? max(1, SMTP_RETRY_ATTEMPTS) : 2;
        $delayMs     = defined('SMTP_RETRY_DELAY_MS') ? max(0, SMTP_RETRY_DELAY_MS) : 1200;
        $lastException = null;

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            try {
                $mail->send(); // PHPMailer send
                // Track last-sent metadata on success
                $toEmails = array_column($mail->getToAddresses(), 0);
                self::updateEmailStatus(
                    implode(', ', $toEmails),
                    $mail->Subject
                );
                return; // success — done
            } catch (Exception $e) {
                $lastException = $e;
                $errorInfo     = !empty($mail->ErrorInfo) ? $mail->ErrorInfo : $e->getMessage();

                // Do NOT retry on permanent failures (auth, address, limit errors)
                $permanent = preg_match('/550|551|552|553|5\.1\.|5\.4\.|5\.7\.|Authentication|recipient/i', $errorInfo);
                if ($permanent || $attempt >= $maxAttempts) {
                    break;
                }

                self::logMail("SMTP attempt {$attempt} failed ({$errorInfo}), retrying in " . ($delayMs / 1000) . 's...', 'WARN');
                usleep($delayMs * 1000);
                // smtpClose() lets PHPMailer reconnect on next send()
                $mail->smtpClose();
            }
        }

        throw $lastException;
    }

    /**
     * Send a Test Email to verify SMTP configuration
     */
    public static function sendTestEmail(string $toEmail, string $toName = 'Admin'): bool {
        try {
            $mail = self::create();
            $mail->addAddress($toEmail, $toName);
            $mail->Subject = '[TPMS] Brevo SMTP Test — ' . date('d M Y H:i');
            $mail->Body    = self::testEmailTemplate($toName);
            $mail->AltBody = "Hi {$toName},\n\nThis is a test email from TPMS.\nBrevo SMTP is configured correctly and working.\n\nSMTP Host: " . SMTP_HOST . "\nSent at: " . date('Y-m-d H:i:s') . "\n\n- TPMS System";

            self::sendWithRetry($mail);
            self::logMail("Test email sent successfully to {$toEmail}", 'SUCCESS');
            return true;
        } catch (Exception $e) {
            $errorInfo = !empty($mail->ErrorInfo) ? $mail->ErrorInfo : $e->getMessage();
            self::$lastError = $errorInfo;
            self::logMail("Test email failed to {$toEmail}: {$errorInfo}", 'ERROR');
            return false;
        }
    }


    /**
     * Send password reset email
     *
     * @param string $toEmail  Recipient email
     * @param string $toName   Recipient name
     * @param string $resetLink Full reset URL
     * @return bool
     */
    public static function sendPasswordReset(string $toEmail, string $toName, string $resetLink): bool {
        try {
            $mail = self::create();
            $mail->addAddress($toEmail, $toName);
            $mail->Subject = 'Reset Your TPMS Password';
            $mail->Body    = self::passwordResetTemplate($toName, $resetLink);
            $mail->AltBody = "Hi {$toName},\n\nClick the link below to reset your password:\n{$resetLink}\n\nThis link expires in 1 hour.\n\nIf you did not request this, ignore this email.\n\n- TPMS Team";

            self::sendWithRetry($mail);
            self::logMail("Password reset email sent successfully to {$toEmail}", 'SUCCESS');
            return true;
        } catch (Exception $e) {
            $errorDetails = !empty($mail->ErrorInfo) ? $mail->ErrorInfo : $e->getMessage();
            self::$lastError = $errorDetails;
            self::logMail("Failed to send password reset email to {$toEmail}. Error: {$errorDetails}", 'ERROR');
            error_log('Mailer Error (sendPasswordReset): ' . $errorDetails);
            return false;
        }
    }

    /**
     * Send OTP Verification Email
     *
     * @param string $toEmail
     * @param string $toName
     * @param string $otp
     * @param int $expiryMinutes
     * @return bool
     */
    public static function sendOTP(string $toEmail, string $toName, string $otp, int $expiryMinutes = 10): bool {
        try {
            $mail = self::create();
            $mail->addAddress($toEmail, $toName);
            $mail->Subject = "{$otp} is your OTP Verification Code - " . APP_NAME;
            $mail->Body    = self::otpTemplate($toName, $otp, $expiryMinutes);
            $mail->AltBody = "Hi {$toName},\n\nYour TPMS Email Verification OTP code is: {$otp}\n\nThis code will expire in {$expiryMinutes} minutes.\n\nIf you did not request this, please ignore this email.\n\n- TPMS Team";

            self::sendWithRetry($mail);
            self::logMail("OTP verification email sent successfully to {$toEmail}", 'SUCCESS');
            return true;
        } catch (Exception $e) {
            $errorDetails = !empty($mail->ErrorInfo) ? $mail->ErrorInfo : $e->getMessage();
            self::$lastError = $errorDetails;
            self::logMail("Failed to send OTP email to {$toEmail}. Error: {$errorDetails}", 'ERROR');
            error_log('Mailer Error (sendOTP): ' . $errorDetails);
            return false;
        }
    }

    /**
     * HTML template for OTP Verification Email
     */
    private static function otpTemplate(string $name, string $otp, int $expiryMinutes): string {
        $appName = APP_FULL_NAME;
        $year    = date('Y');

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Your OTP Verification Code</title>
</head>
<body style="margin:0;padding:0;background-color:#f4f6fb;font-family:'Segoe UI',Roboto,Helvetica,Arial,sans-serif;">
  <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f6fb;padding:40px 0;">
    <tr>
      <td align="center">
        <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 10px 30px rgba(0,0,0,0.08);border:1px solid #e5e7eb;">

          <!-- Header -->
          <tr>
            <td style="background:linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);padding:40px 40px 30px;text-align:center;">
              <div style="width:64px;height:64px;background:rgba(255,255,255,0.18);border-radius:50%;display:inline-flex;align-items:center;justify-content:center;margin-bottom:16px;">
                <span style="font-size:32px;">🛡️</span>
              </div>
              <h1 style="margin:0;color:#ffffff;font-size:26px;font-weight:700;letter-spacing:-0.5px;">Email Verification Code</h1>
              <p style="margin:8px 0 0;color:rgba(255,255,255,0.85);font-size:14px;font-weight:500;">{$appName}</p>
            </td>
          </tr>

          <!-- Body -->
          <tr>
            <td style="padding:40px;">
              <p style="margin:0 0 16px;font-size:16px;color:#1f2937;font-weight:600;">Hi <strong>{$name}</strong>,</p>
              <p style="margin:0 0 24px;font-size:15px;color:#4b5563;line-height:1.6;">
                Thank you for registering with <strong>TPMS</strong>. Please use the One-Time Password (OTP) below to complete your email verification:
              </p>

              <!-- OTP Display Box -->
              <table width="100%" cellpadding="0" cellspacing="0">
                <tr>
                  <td align="center" style="padding:10px 0 30px;">
                    <div style="background:linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);border:2px dashed #6366f1;border-radius:14px;padding:24px 30px;display:inline-block;box-shadow:0 4px 12px rgba(99,102,241,0.08);">
                      <span style="font-family:'Courier New',Courier,monospace;font-size:38px;font-weight:800;letter-spacing:12px;color:#4f46e5;margin-left:12px;">{$otp}</span>
                    </div>
                  </td>
                </tr>
              </table>

              <!-- Notice Box -->
              <table width="100%" cellpadding="0" cellspacing="0">
                <tr>
                  <td style="background:#eff6ff;border-left:4px solid #3b82f6;border-radius:8px;padding:16px 20px;margin-bottom:24px;">
                    <p style="margin:0;font-size:13.5px;color:#1e40af;line-height:1.6;">
                      ⏱️ <strong>This OTP is valid for {$expiryMinutes} minutes.</strong><br>
                      For security reasons, do not share this OTP code with anyone.
                    </p>
                  </td>
                </tr>
              </table>

              <!-- Warning text -->
              <p style="margin:24px 0 0;font-size:13px;color:#9ca3af;line-height:1.6;text-align:center;">
                If you did not initiate this registration, please disregard this email. Your account will not be activated without verification.
              </p>
            </td>
          </tr>

          <!-- Footer -->
          <tr>
            <td style="background:#f9fafb;border-top:1px solid #f3f4f6;padding:24px 40px;text-align:center;">
              <p style="margin:0 0 6px;font-size:13px;color:#6b7280;font-weight:500;">
                Training & Placement Management System (TPMS)
              </p>
              <p style="margin:0;font-size:12px;color:#9ca3af;">
                © {$year} TPMS. All rights reserved.
              </p>
            </td>
          </tr>

        </table>
      </td>
    </tr>
  </table>
</body>
</html>
HTML;
    }

    /**
     * Send welcome email to new students
     */
    public static function sendWelcome(string $toEmail, string $toName): bool {
        try {
            $mail = self::create();
            $mail->addAddress($toEmail, $toName);
            $mail->Subject = 'Welcome to TPMS - Training & Placement Management System';
            $mail->Body    = self::welcomeTemplate($toName);
            $mail->AltBody = "Hi {$toName},\n\nWelcome to TPMS! Your account has been created successfully.\n\nLog in at: " . FULL_URL . "/login\n\n- TPMS Team";

            self::sendWithRetry($mail);
            self::logMail("Welcome email sent successfully to {$toEmail}", 'SUCCESS');
            return true;
        } catch (Exception $e) {
            $errorDetails = !empty($mail->ErrorInfo) ? $mail->ErrorInfo : $e->getMessage();
            self::$lastError = $errorDetails;
            self::logMail("Failed to send welcome email to {$toEmail}. Error: {$errorDetails}", 'ERROR');
            error_log('Mailer Error (sendWelcome): ' . $errorDetails);
            return false;
        }
    }

    /**
     * HTML template for password reset email
     */
    private static function passwordResetTemplate(string $name, string $resetLink): string {
        $appName = APP_FULL_NAME;
        $year    = date('Y');

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reset Your Password</title>
</head>
<body style="margin:0;padding:0;background:#f0f4ff;font-family:'Segoe UI',Arial,sans-serif;">
  <table width="100%" cellpadding="0" cellspacing="0" style="background:#f0f4ff;padding:40px 0;">
    <tr>
      <td align="center">
        <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 4px 30px rgba(0,0,0,0.08);">

          <!-- Header -->
          <tr>
            <td style="background:linear-gradient(135deg,#4f46e5,#7c3aed);padding:40px 40px 30px;text-align:center;">
              <div style="width:60px;height:60px;background:rgba(255,255,255,0.15);border-radius:50%;display:inline-flex;align-items:center;justify-content:center;margin-bottom:16px;">
                <span style="font-size:26px;">🔑</span>
              </div>
              <h1 style="margin:0;color:#ffffff;font-size:26px;font-weight:700;letter-spacing:-0.5px;">Reset Your Password</h1>
              <p style="margin:8px 0 0;color:rgba(255,255,255,0.8);font-size:14px;">{$appName}</p>
            </td>
          </tr>

          <!-- Body -->
          <tr>
            <td style="padding:40px;">
              <p style="margin:0 0 20px;font-size:16px;color:#374151;">Hi <strong>{$name}</strong>,</p>
              <p style="margin:0 0 24px;font-size:15px;color:#6b7280;line-height:1.7;">
                We received a request to reset the password for your TPMS account. Click the button below to create a new password.
              </p>

              <!-- CTA Button -->
              <table width="100%" cellpadding="0" cellspacing="0">
                <tr>
                  <td align="center" style="padding:8px 0 32px;">
                    <a href="{$resetLink}"
                       style="display:inline-block;background:linear-gradient(135deg,#4f46e5,#7c3aed);color:#ffffff;text-decoration:none;font-size:16px;font-weight:600;padding:16px 40px;border-radius:50px;letter-spacing:0.3px;box-shadow:0 4px 15px rgba(79,70,229,0.4);">
                      🔓 &nbsp; Reset My Password
                    </a>
                  </td>
                </tr>
              </table>

              <!-- Warning box -->
              <table width="100%" cellpadding="0" cellspacing="0">
                <tr>
                  <td style="background:#fef3c7;border-left:4px solid #f59e0b;border-radius:8px;padding:16px 20px;margin-bottom:24px;">
                    <p style="margin:0;font-size:13px;color:#92400e;line-height:1.6;">
                      ⏰ &nbsp;<strong>This link expires in 1 hour.</strong><br>
                      If you didn't request a password reset, you can safely ignore this email — your password will remain unchanged.
                    </p>
                  </td>
                </tr>
              </table>

              <!-- Fallback link -->
              <p style="margin:24px 0 0;font-size:13px;color:#9ca3af;line-height:1.6;">
                If the button doesn't work, copy and paste this link into your browser:<br>
                <a href="{$resetLink}" style="color:#4f46e5;word-break:break-all;">{$resetLink}</a>
              </p>
            </td>
          </tr>

          <!-- Footer -->
          <tr>
            <td style="background:#f9fafb;border-top:1px solid #e5e7eb;padding:24px 40px;text-align:center;">
              <p style="margin:0 0 8px;font-size:13px;color:#6b7280;">
                This email was sent by <strong>TPMS</strong> — {$appName}
              </p>
              <p style="margin:0;font-size:12px;color:#9ca3af;">
                © {$year} TPMS. All rights reserved.
              </p>
            </td>
          </tr>

        </table>
      </td>
    </tr>
  </table>
</body>
</html>
HTML;
    }

    /**
     * HTML template for welcome email
     */
    private static function welcomeTemplate(string $name): string {
        $appName  = APP_FULL_NAME;
        $loginUrl = FULL_URL . '/login';
        $year     = date('Y');

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Welcome to TPMS</title>
</head>
<body style="margin:0;padding:0;background:#f0f4ff;font-family:'Segoe UI',Arial,sans-serif;">
  <table width="100%" cellpadding="0" cellspacing="0" style="background:#f0f4ff;padding:40px 0;">
    <tr>
      <td align="center">
        <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 4px 30px rgba(0,0,0,0.08);">
          <tr>
            <td style="background:linear-gradient(135deg,#10b981,#059669);padding:40px;text-align:center;">
              <div style="font-size:48px;margin-bottom:12px;">🎓</div>
              <h1 style="margin:0;color:#ffffff;font-size:26px;font-weight:700;">Welcome to TPMS!</h1>
              <p style="margin:8px 0 0;color:rgba(255,255,255,0.85);font-size:14px;">{$appName}</p>
            </td>
          </tr>
          <tr>
            <td style="padding:40px;">
              <p style="margin:0 0 16px;font-size:16px;color:#374151;">Hi <strong>{$name}</strong>,</p>
              <p style="margin:0 0 24px;font-size:15px;color:#6b7280;line-height:1.7;">
                Your TPMS account has been created successfully! You can now browse job opportunities, apply for positions, register for trainings, and track your placement journey.
              </p>
              <table width="100%" cellpadding="0" cellspacing="0">
                <tr>
                  <td align="center" style="padding:8px 0 24px;">
                    <a href="{$loginUrl}" style="display:inline-block;background:linear-gradient(135deg,#10b981,#059669);color:#ffffff;text-decoration:none;font-size:16px;font-weight:600;padding:16px 40px;border-radius:50px;box-shadow:0 4px 15px rgba(16,185,129,0.4);">
                      🚀 &nbsp; Go to Dashboard
                    </a>
                  </td>
                </tr>
              </table>
            </td>
          </tr>
          <tr>
            <td style="background:#f9fafb;border-top:1px solid #e5e7eb;padding:24px 40px;text-align:center;">
              <p style="margin:0;font-size:12px;color:#9ca3af;">© {$year} TPMS. All rights reserved.</p>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>
HTML;
    }

    // ================================================================
    // NEW NOTIFICATION EMAILS
    // ================================================================

    /**
     * Notify student when they apply for a job
     */
    public static function sendJobApplication(string $toEmail, string $toName, string $jobTitle, string $companyName): bool {
        try {
            $mail = self::create();
            $mail->addAddress($toEmail, $toName);
            $mail->Subject = "Application Submitted – {$jobTitle} at {$companyName}";
            $mail->Body = self::simpleTemplate('📋 Application Submitted', "#2563EB",
                $toName,
                "Your application for <strong>{$jobTitle}</strong> at <strong>{$companyName}</strong> has been submitted successfully.",
                "We will notify you when there's an update. Best of luck! 🤞",
                FULL_URL . '/student/applications', 'View My Applications', '#2563EB'
            );
            $mail->AltBody = "Hi {$toName}, your application for {$jobTitle} at {$companyName} has been submitted.";
            self::sendWithRetry($mail);
            self::logMail("Job application email sent to {$toEmail} for {$jobTitle}", 'SUCCESS');
            return true;
        } catch (Exception $e) {
            $err = !empty($mail->ErrorInfo) ? $mail->ErrorInfo : $e->getMessage();
            self::$lastError = $err;
            self::logMail("Failed job application email to {$toEmail}: {$err}", 'ERROR');
            return false;
        }
    }

    /**
     * Notify student when company changes their application status (Selected, Shortlisted, Interview, Rejected)
     */
    public static function sendApplicationStatus(
        string $toEmail,
        string $toName,
        string $jobTitle,
        string $companyName,
        string $status,
        ?array $interviewDetails = null
    ): bool {
        $colors = [
            'selected'    => '#10B981',
            'shortlisted' => '#F59E0B',
            'interview'   => '#06B6D4',
            'rejected'    => '#EF4444',
            'applied'     => '#6366F1'
        ];
        $color = $colors[$status] ?? '#6366F1';
        $statusLabel = match($status) {
            'selected'    => 'Selected 🎉',
            'shortlisted' => 'Shortlisted ⭐',
            'interview'   => 'Interview Scheduled 📅',
            'rejected'    => 'Not Selected',
            default       => ucfirst($status)
        };
        $emojis = [
            'selected'    => '🎉',
            'shortlisted' => '⭐',
            'interview'   => '📅',
            'rejected'    => '📋',
            'applied'     => '📥'
        ];
        $emoji = $emojis[$status] ?? '📬';

        try {
            $mail = self::create();
            $mail->addAddress($toEmail, $toName);
            $mail->Subject = "{$emoji} Application Update: {$statusLabel} – {$jobTitle} at {$companyName}";
            $mail->Body    = self::applicationStatusTemplate($toName, $companyName, $jobTitle, $status, $statusLabel, $color, $interviewDetails);

            $altInterview = "";
            if (!empty($interviewDetails)) {
                $altInterview = "\nInterview Details:\n"
                              . "- Round: " . ($interviewDetails['round'] ?? 'N/A') . "\n"
                              . "- Date: " . ($interviewDetails['interview_date'] ?? 'N/A') . "\n"
                              . "- Time: " . ($interviewDetails['interview_time'] ?? 'N/A') . "\n"
                              . "- Mode: " . ucfirst($interviewDetails['mode'] ?? 'offline') . "\n"
                              . "- Location/Link: " . ($interviewDetails['venue'] ?? $interviewDetails['meeting_link'] ?? 'N/A') . "\n";
            }
            $mail->AltBody = "Hi {$toName},\n\nYour application for {$jobTitle} at {$companyName} has been updated to: {$statusLabel}.\n{$altInterview}\nLog in to TPMS: " . FULL_URL . "/student/applications\n\n- TPMS Team";

            self::sendWithRetry($mail);
            self::logMail("Application status email ({$status}) sent successfully to {$toEmail}", 'SUCCESS');
            return true;
        } catch (Exception $e) {
            $err = !empty($mail->ErrorInfo) ? $mail->ErrorInfo : $e->getMessage();
            self::$lastError = $err;
            self::logMail("Failed application status email to {$toEmail}: {$err}", 'ERROR');
            return false;
        }
    }

    /**
     * Dedicated professional HTML template for Application Status / Selection Updates
     */
    private static function applicationStatusTemplate(
        string $name,
        string $companyName,
        string $jobTitle,
        string $status,
        string $statusLabel,
        string $color,
        ?array $interviewDetails = null
    ): string {
        $appName   = APP_FULL_NAME;
        $year      = date('Y');
        $portalUrl = FULL_URL . '/student/applications';

        $bannerTitle = match($status) {
            'selected'    => 'Congratulations! You\'re Selected!',
            'shortlisted' => 'Application Shortlisted!',
            'interview'   => 'Interview Scheduled!',
            'rejected'    => 'Application Update',
            default       => 'Application Status Updated'
        };

        $bannerIcon = match($status) {
            'selected'    => '🎉',
            'shortlisted' => '⭐',
            'interview'   => '📅',
            'rejected'    => '📋',
            default       => '📬'
        };

        $messageBody = match($status) {
            'selected' => "We are thrilled to inform you that <strong>{$companyName}</strong> has selected you for the position of <strong>{$jobTitle}</strong>! Your hard work and dedication have paid off. Congratulations on this achievement!",
            'shortlisted' => "Great news! <strong>{$companyName}</strong> has shortlisted your profile for the <strong>{$jobTitle}</strong> position. Please stay prepared for upcoming interview rounds.",
            'interview' => "An interview has been scheduled for your application for <strong>{$jobTitle}</strong> at <strong>{$companyName}</strong>. Please review the details below.",
            'rejected' => "Thank you for your interest in <strong>{$jobTitle}</strong> at <strong>{$companyName}</strong>. After careful consideration, the recruitment team has decided not to proceed with your application at this time. We encourage you to keep applying for other opportunities on TPMS.",
            default => "Your application status for <strong>{$jobTitle}</strong> at <strong>{$companyName}</strong> has been updated to <strong>{$statusLabel}</strong>."
        };

        // Render interview details block if available
        $interviewHtml = '';
        if (!empty($interviewDetails)) {
            $round    = htmlspecialchars($interviewDetails['round'] ?? 'Round 1');
            $date     = !empty($interviewDetails['interview_date']) ? date('D, d M Y', strtotime($interviewDetails['interview_date'])) : 'TBD';
            $time     = !empty($interviewDetails['interview_time']) ? date('h:i A', strtotime($interviewDetails['interview_time'])) : 'TBD';
            $mode     = ucfirst($interviewDetails['mode'] ?? 'offline');
            $location = ($interviewDetails['mode'] ?? '') === 'online'
                ? (!empty($interviewDetails['meeting_link']) ? '<a href="' . htmlspecialchars($interviewDetails['meeting_link']) . '" style="color:#2563EB;word-break:break-all;">' . htmlspecialchars($interviewDetails['meeting_link']) . '</a>' : 'Online Link Pending')
                : htmlspecialchars($interviewDetails['venue'] ?? 'Venue TBD');

            $interviewHtml = <<<HTML
            <div style="background:#f8fafc;border:1px solid #e2e8f0;border-left:4px solid {$color};border-radius:10px;padding:20px;margin:24px 0;">
              <h3 style="margin:0 0 14px;color:#0f172a;font-size:16px;font-weight:700;">🗓️ Interview Details</h3>
              <table width="100%" cellpadding="4" cellspacing="0" style="font-size:14px;color:#334155;">
                <tr>
                  <td width="120" style="font-weight:600;color:#64748b;">Round:</td>
                  <td>{$round}</td>
                </tr>
                <tr>
                  <td style="font-weight:600;color:#64748b;">Date:</td>
                  <td>{$date}</td>
                </tr>
                <tr>
                  <td style="font-weight:600;color:#64748b;">Time:</td>
                  <td>{$time}</td>
                </tr>
                <tr>
                  <td style="font-weight:600;color:#64748b;">Mode:</td>
                  <td>{$mode}</td>
                </tr>
                <tr>
                  <td style="font-weight:600;color:#64748b;">Location / Link:</td>
                  <td>{$location}</td>
                </tr>
              </table>
            </div>
HTML;
        }

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{$bannerTitle}</title>
</head>
<body style="margin:0;padding:0;background-color:#f4f6fb;font-family:'Segoe UI',Roboto,Helvetica,Arial,sans-serif;">
  <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f6fb;padding:40px 0;">
    <tr>
      <td align="center">
        <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 10px 30px rgba(0,0,0,0.08);border:1px solid #e5e7eb;">
          <!-- Header Banner -->
          <tr>
            <td style="background:linear-gradient(135deg, {$color} 0%, #1e293b 100%);padding:36px 40px;text-align:center;">
              <div style="font-size:42px;line-height:1;margin-bottom:12px;">{$bannerIcon}</div>
              <h1 style="margin:0;color:#ffffff;font-size:24px;font-weight:700;letter-spacing:-0.3px;">{$bannerTitle}</h1>
              <p style="margin:6px 0 0;color:rgba(255,255,255,0.85);font-size:13px;font-weight:500;">{$appName}</p>
            </td>
          </tr>

          <!-- Content Body -->
          <tr>
            <td style="padding:36px 40px;">
              <p style="margin:0 0 16px;font-size:16px;color:#1e293b;font-weight:600;">Dear <strong>{$name}</strong>,</p>

              <p style="margin:0 0 20px;font-size:15px;color:#334155;line-height:1.7;">
                {$messageBody}
              </p>

              <!-- Application Summary Box -->
              <div style="background:#f1f5f9;border-radius:12px;padding:18px 24px;margin-bottom:24px;">
                <table width="100%" cellpadding="4" cellspacing="0" style="font-size:14px;">
                  <tr>
                    <td width="120" style="font-weight:600;color:#64748b;">Company:</td>
                    <td style="font-weight:700;color:#0f172a;">{$companyName}</td>
                  </tr>
                  <tr>
                    <td style="font-weight:600;color:#64748b;">Job Title:</td>
                    <td style="font-weight:600;color:#0f172a;">{$jobTitle}</td>
                  </tr>
                  <tr>
                    <td style="font-weight:600;color:#64748b;">Current Status:</td>
                    <td><span style="display:inline-block;background:{$color};color:#ffffff;font-weight:700;font-size:12px;padding:4px 12px;border-radius:50px;text-transform:uppercase;letter-spacing:0.5px;">{$statusLabel}</span></td>
                  </tr>
                </table>
              </div>

              {$interviewHtml}

              <!-- CTA Button -->
              <table width="100%" cellpadding="0" cellspacing="0">
                <tr>
                  <td align="center" style="padding:12px 0 28px;">
                    <a href="{$portalUrl}" style="display:inline-block;background:{$color};color:#ffffff;text-decoration:none;font-size:15px;font-weight:700;padding:14px 36px;border-radius:50px;box-shadow:0 4px 14px rgba(0,0,0,0.15);">
                      🚀 &nbsp; View My Applications
                    </a>
                  </td>
                </tr>
              </table>

              <p style="margin:0;font-size:13px;color:#94a3b8;text-align:center;line-height:1.5;">
                If you have any questions, please log into your TPMS portal or reach out to your placement coordinator.
              </p>
            </td>
          </tr>

          <!-- Footer -->
          <tr>
            <td style="background:#f8fafc;border-top:1px solid #f1f5f9;padding:20px 40px;text-align:center;">
              <p style="margin:0 0 4px;font-size:12px;color:#64748b;font-weight:500;">{$appName}</p>
              <p style="margin:0;font-size:11px;color:#94a3b8;">© {$year} TPMS. All rights reserved.</p>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>
HTML;
    }

    /**
     * Notify student when an interview is scheduled
     */
    public static function sendInterviewScheduled(string $toEmail, string $toName, string $jobTitle, string $companyName, string $date, string $time, string $mode, string $venue = ''): bool {
        $location = ($mode === 'online') ? 'Online / Video Call' : ($venue ?: 'Venue TBD');
        try {
            $mail = self::create();
            $mail->addAddress($toEmail, $toName);
            $mail->Subject = "📅 Interview Scheduled – {$jobTitle} at {$companyName}";
            $mail->Body = self::simpleTemplate('📅 Interview Scheduled', '#0891B2',
                $toName,
                "An interview has been scheduled for <strong>{$jobTitle}</strong> at <strong>{$companyName}</strong>.<br><br>
                📆 <strong>Date:</strong> " . date('D, d M Y', strtotime($date)) . "<br>
                🕐 <strong>Time:</strong> " . date('h:i A', strtotime($time)) . "<br>
                💼 <strong>Mode:</strong> " . ucfirst($mode) . "<br>
                📍 <strong>Location:</strong> {$location}",
                "Please be prepared and on time. Best of luck! 🙌",
                FULL_URL . '/student/interviews', 'View Interview Details', '#0891B2'
            );
            $mail->AltBody = "Hi {$toName}, interview for {$jobTitle} at {$companyName} on {$date} at {$time}.";
            self::sendWithRetry($mail);
            self::logMail("Interview scheduled email sent to {$toEmail}", 'SUCCESS');
            return true;
        } catch (Exception $e) {
            $err = !empty($mail->ErrorInfo) ? $mail->ErrorInfo : $e->getMessage();
            self::$lastError = $err;
            self::logMail("Failed interview scheduled email to {$toEmail}: {$err}", 'ERROR');
            return false;
        }
    }

    /**
     * Notify student of interview result
     */
    public static function sendInterviewResult(string $toEmail, string $toName, string $jobTitle, string $companyName, string $result): bool {
        $color = ($result === 'passed') ? '#16A34A' : '#DC2626';
        $emoji = ($result === 'passed') ? '✅' : '❌';
        $msg = ($result === 'passed')
            ? "Congratulations! You have <strong style=\"color:#16A34A\">passed</strong> the interview for <strong>{$jobTitle}</strong> at <strong>{$companyName}</strong>."
            : "We regret to inform you that you were not selected for <strong>{$jobTitle}</strong> at <strong>{$companyName}</strong>. Keep applying!";
        try {
            $mail = self::create();
            $mail->addAddress($toEmail, $toName);
            $mail->Subject = "{$emoji} Interview Result – {$jobTitle}";
            $mail->Body = self::simpleTemplate("{$emoji} Interview Result", $color,
                $toName, $msg, "Thank you for your effort and participation.",
                FULL_URL . '/student/interviews', 'View All Interviews', $color
            );
            $mail->AltBody = "Hi {$toName}, interview result for {$jobTitle}: {$result}.";
            self::sendWithRetry($mail);
            self::logMail("Interview result email ({$result}) sent to {$toEmail}", 'SUCCESS');
            return true;
        } catch (Exception $e) {
            $err = !empty($mail->ErrorInfo) ? $mail->ErrorInfo : $e->getMessage();
            self::$lastError = $err;
            self::logMail("Failed interview result email to {$toEmail}: {$err}", 'ERROR');
            return false;
        }
    }

    /**
     * Notify student when registered for training
     */
    public static function sendTrainingRegistered(string $toEmail, string $toName, string $trainingTitle, string $startDate): bool {
        try {
            $mail = self::create();
            $mail->addAddress($toEmail, $toName);
            $mail->Subject = "🎓 Training Registration Confirmed – {$trainingTitle}";
            $mail->Body = self::simpleTemplate('🎓 Training Confirmed', '#7C3AED',
                $toName,
                "You have been registered for the training: <strong>{$trainingTitle}</strong>.<br><br>📅 <strong>Starts:</strong> " . date('D, d M Y', strtotime($startDate)),
                "Please be prepared. This training will enhance your skills and placement prospects.",
                FULL_URL . '/student/trainings', 'View My Trainings', '#7C3AED'
            );
            $mail->AltBody = "Hi {$toName}, you've been registered for training: {$trainingTitle} starting {$startDate}.";
            self::sendWithRetry($mail);
            self::logMail("Training registration email sent to {$toEmail}", 'SUCCESS');
            return true;
        } catch (Exception $e) {
            $err = !empty($mail->ErrorInfo) ? $mail->ErrorInfo : $e->getMessage();
            self::$lastError = $err;
            self::logMail("Failed training registration email to {$toEmail}: {$err}", 'ERROR');
            return false;
        }
    }

    /**
     * Send admin announcement to a user
     */
    public static function sendAdminAnnouncement(string $toEmail, string $toName, string $title, string $message): bool {
        try {
            $mail = self::create();
            $mail->addAddress($toEmail, $toName);
            $mail->Subject = "📢 Announcement: {$title}";
            $mail->Body = self::simpleTemplate('📢 Important Announcement', '#6366F1',
                $toName, $message, "This is an official communication from TPMS Administration.",
                FULL_URL . '/student/dashboard', 'Go to Dashboard', '#6366F1'
            );
            $mail->AltBody = "Hi {$toName}, announcement from TPMS Admin: {$title}\n{$message}";
            self::sendWithRetry($mail);
            self::logMail("Admin announcement email sent to {$toEmail}", 'SUCCESS');
            return true;
        } catch (Exception $e) {
            $err = !empty($mail->ErrorInfo) ? $mail->ErrorInfo : $e->getMessage();
            self::$lastError = $err;
            self::logMail("Failed admin announcement email to {$toEmail}: {$err}", 'ERROR');
            return false;
        }
    }

    /**
     * Notify company of approval or rejection
     */
    public static function sendApprovalNotification(string $toEmail, string $toName, string $itemType, string $itemName, bool $approved): bool {
        $color = $approved ? '#16A34A' : '#DC2626';
        $status = $approved ? 'Approved ✅' : 'Rejected ❌';
        $emoji = $approved ? '✅' : '❌';
        $msg = $approved
            ? "Your {$itemType} <strong>{$itemName}</strong> has been <strong style=\"color:#16A34A\">approved</strong>! You can now access all features."
            : "Your {$itemType} <strong>{$itemName}</strong> has been <strong style=\"color:#DC2626\">rejected</strong>. Please contact the administrator for more details.";
        try {
            $mail = self::create();
            $mail->addAddress($toEmail, $toName);
            $mail->Subject = "{$emoji} {$itemType} {$status} – TPMS";
            $mail->Body = self::simpleTemplate("{$emoji} {$itemType} {$status}", $color,
                $toName, $msg, "Thank you for using TPMS.",
                FULL_URL . '/login', 'Go to Login', $color
            );
            $mail->AltBody = "Hi {$toName}, your {$itemType} {$itemName} has been {$status}.";
            self::sendWithRetry($mail);
            self::logMail("Approval notification ({$status}) sent to {$toEmail}", 'SUCCESS');
            return true;
        } catch (Exception $e) {
            $err = !empty($mail->ErrorInfo) ? $mail->ErrorInfo : $e->getMessage();
            self::$lastError = $err;
            self::logMail("Failed approval notification to {$toEmail}: {$err}", 'ERROR');
            return false;
        }
    }

    /**
     * Alert admin when a new company registers
     */
    public static function sendCompanyRegistrationAlert(string $adminEmail, string $companyName, string $companyEmail): bool {
        try {
            $mail = self::create();
            $mail->addAddress($adminEmail, 'TPMS Admin');
            $mail->Subject = "🏢 New Company Registration – {$companyName}";
            $mail->Body = self::simpleTemplate('🏢 New Company Registered', '#D97706',
                'Admin',
                "A new company has registered and is awaiting your approval:<br><br>
                🏢 <strong>Company:</strong> {$companyName}<br>
                📧 <strong>Email:</strong> {$companyEmail}",
                "Please review and approve or reject their registration.",
                FULL_URL . '/admin/companies', 'Review Companies', '#D97706'
            );
            $mail->AltBody = "New company {$companyName} ({$companyEmail}) has registered and awaits approval.";
            self::sendWithRetry($mail);
            self::logMail("Company registration alert sent to admin for {$companyName}", 'SUCCESS');
            return true;
        } catch (Exception $e) {
            $err = !empty($mail->ErrorInfo) ? $mail->ErrorInfo : $e->getMessage();
            self::$lastError = $err;
            self::logMail("Failed company registration alert: {$err}", 'ERROR');
            return false;
        }
    }

    /**
     * Notify company HR when a new student applies to their job posting
     */
    public static function sendCompanyNewApplication(string $toEmail, string $companyName, string $studentName, string $branch, string $jobTitle): bool {
        if (!filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
            $msg = "Invalid recipient email for company new application alert: '{$toEmail}'";
            self::$lastError = $msg;
            self::logMail($msg, 'ERROR');
            return false;
        }
        try {
            $mail = self::create();
            $mail->addAddress($toEmail, $companyName);
            $mail->Subject = "📥 New Job Application: {$studentName} for {$jobTitle}";
            $mail->Body = self::simpleTemplate(
                '📥 New Candidate Application',
                '#2563EB',
                $companyName,
                "A candidate has applied for your job opening <strong>{$jobTitle}</strong>.<br><br>
                👤 <strong>Student Name:</strong> {$studentName}<br>
                🎓 <strong>Branch:</strong> {$branch}<br>
                💼 <strong>Job Position:</strong> {$jobTitle}",
                "Log into your company portal to review candidate applications.",
                FULL_URL . '/company/jobs', 'Review Applications', '#2563EB'
            );
            $mail->AltBody = "Hi {$companyName}, {$studentName} ({$branch}) applied for {$jobTitle}. Log in to review.";
            self::sendWithRetry($mail);
            self::logMail("Company new application email sent to {$toEmail} for {$jobTitle}", 'SUCCESS');
            return true;
        } catch (Exception $e) {
            $err = !empty($mail->ErrorInfo) ? $mail->ErrorInfo : $e->getMessage();
            self::$lastError = $err;
            self::logMail("Failed company new application email to {$toEmail}: {$err}", 'ERROR');
            return false;
        }
    }

    /**
     * Notify student of Higher Studies application approval/rejection
     */
    public static function sendHigherStudyStatus(string $toEmail, string $toName, string $universityName, string $status, string $remarks = ''): bool {
        if (!filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
            $msg = "Invalid recipient email for higher study status: '{$toEmail}'";
            self::$lastError = $msg;
            self::logMail($msg, 'ERROR');
            return false;
        }
        $approved = ($status === 'approved');
        $color = $approved ? '#16A34A' : '#DC2626';
        $statusText = $approved ? 'Approved ✅' : 'Rejected ❌';
        $msgBody = $approved
            ? "Your higher studies application for <strong>{$universityName}</strong> has been <strong style=\"color:#16A34A\">approved</strong> by admin." . ($remarks ? "<br><br>📝 <strong>Remarks:</strong> {$remarks}" : '')
            : "Your higher studies application for <strong>{$universityName}</strong> was <strong style=\"color:#DC2626\">not approved</strong>." . ($remarks ? "<br><br>📝 <strong>Reason:</strong> {$remarks}" : '');

        try {
            $mail = self::create();
            $mail->addAddress($toEmail, $toName);
            $mail->Subject = "🏛️ Higher Studies Application {$statusText} – {$universityName}";
            $mail->Body = self::simpleTemplate("🏛️ Higher Studies {$statusText}", $color,
                $toName, $msgBody, "View application status on your dashboard.",
                FULL_URL . '/student/higher-studies', 'View Higher Studies', $color
            );
            $mail->AltBody = "Hi {$toName}, your application for {$universityName} is {$statusText}.";
            self::sendWithRetry($mail);
            self::logMail("Higher studies application email ({$status}) sent to {$toEmail}", 'SUCCESS');
            return true;
        } catch (Exception $e) {
            $err = !empty($mail->ErrorInfo) ? $mail->ErrorInfo : $e->getMessage();
            self::$lastError = $err;
            self::logMail("Failed higher study status email to {$toEmail}: {$err}", 'ERROR');
            return false;
        }
    }

    /**
     * Notify student of Training enrollment approval/rejection
     */
    public static function sendTrainingStatus(string $toEmail, string $toName, string $trainingTitle, string $status, string $remarks = ''): bool {
        if (!filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
            $msg = "Invalid recipient email for training status: '{$toEmail}'";
            self::$lastError = $msg;
            self::logMail($msg, 'ERROR');
            return false;
        }
        $approved = ($status === 'approved');
        $color = $approved ? '#7C3AED' : '#DC2626';
        $statusText = $approved ? 'Approved ✅' : 'Rejected ❌';
        $msgBody = $approved
            ? "Your enrollment in <strong>{$trainingTitle}</strong> has been <strong style=\"color:#7C3AED\">approved</strong>." . ($remarks ? "<br><br>📝 <strong>Notes:</strong> {$remarks}" : '')
            : "Your enrollment in <strong>{$trainingTitle}</strong> was <strong style=\"color:#DC2626\">rejected</strong>." . ($remarks ? "<br><br>📝 <strong>Reason:</strong> {$remarks}" : '');

        try {
            $mail = self::create();
            $mail->addAddress($toEmail, $toName);
            $mail->Subject = "🎓 Training Enrollment {$statusText} – {$trainingTitle}";
            $mail->Body = self::simpleTemplate("🎓 Training Enrollment {$statusText}", $color,
                $toName, $msgBody, "Check training schedules on your portal.",
                FULL_URL . '/student/trainings', 'View Trainings', $color
            );
            $mail->AltBody = "Hi {$toName}, training enrollment for {$trainingTitle} is {$statusText}.";
            self::sendWithRetry($mail);
            self::logMail("Training enrollment status email ({$status}) sent to {$toEmail}", 'SUCCESS');
            return true;
        } catch (Exception $e) {
            $err = !empty($mail->ErrorInfo) ? $mail->ErrorInfo : $e->getMessage();
            self::$lastError = $err;
            self::logMail("Failed training status email to {$toEmail}: {$err}", 'ERROR');
            return false;
        }
    }

    /**
     * Notify student when training certificate is issued
     */
    public static function sendTrainingCertificate(string $toEmail, string $toName, string $trainingTitle): bool {
        if (!filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
            $msg = "Invalid recipient email for training certificate: '{$toEmail}'";
            self::$lastError = $msg;
            self::logMail($msg, 'ERROR');
            return false;
        }
        try {
            $mail = self::create();
            $mail->addAddress($toEmail, $toName);
            $mail->Subject = "📜 Certificate Issued – {$trainingTitle}";
            $mail->Body = self::simpleTemplate("📜 Certificate Issued!", '#10B981',
                $toName,
                "Congratulations! Your certificate of completion for <strong>{$trainingTitle}</strong> has been issued and is available on your student portal.",
                "Keep up the great learning progress!",
                FULL_URL . '/student/trainings', 'View My Certificate', '#10B981'
            );
            $mail->AltBody = "Hi {$toName}, your certificate for {$trainingTitle} has been issued.";
            self::sendWithRetry($mail);
            self::logMail("Training certificate email sent to {$toEmail}", 'SUCCESS');
            return true;
        } catch (Exception $e) {
            $err = !empty($mail->ErrorInfo) ? $mail->ErrorInfo : $e->getMessage();
            self::$lastError = $err;
            self::logMail("Failed training certificate email to {$toEmail}: {$err}", 'ERROR');
            return false;
        }
    }

    /**
     * Send confirmation email to company HR after email verification is successful,
     * informing them their account is pending admin approval.
     */
    public static function sendCompanyPendingApproval(string $toEmail, string $companyName): bool {
        if (!filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
            $msg = "Invalid recipient email for company pending approval: '{$toEmail}'";
            self::$lastError = $msg;
            self::logMail($msg, 'ERROR');
            return false;
        }
        try {
            $mail = self::create();
            $mail->addAddress($toEmail, $companyName);
            $mail->Subject = "✅ Email Verified – Your Company Registration is Under Review";
            $mail->Body = self::simpleTemplate(
                '✅ Email Verified Successfully',
                '#0891B2',
                $companyName,
                "Your email address has been <strong>successfully verified</strong>.<br><br>
                Your company registration for <strong>{$companyName}</strong> is now <strong style=\"color:#D97706;\">pending admin approval</strong>.<br><br>
                Our team will review your application shortly. You will receive another email once your account is approved and ready to use.",
                "Thank you for registering with TPMS. We look forward to helping you find the right talent.",
                FULL_URL . '/login', 'Go to Login', '#0891B2'
            );
            $mail->AltBody = "Hi {$companyName}, your email has been verified. Your company account is pending admin approval. You will be notified once approved.";
            self::sendWithRetry($mail);
            self::logMail("Company email-verified/pending-approval email sent to {$toEmail} for {$companyName}", 'SUCCESS');
            return true;
        } catch (Exception $e) {
            $err = !empty($mail->ErrorInfo) ? $mail->ErrorInfo : $e->getMessage();
            self::$lastError = $err;
            self::logMail("Failed company pending approval email to {$toEmail}: {$err}", 'ERROR');
            return false;
        }
    }

    /**
     * Generic HTML email template builder (DRY helper)
     */
    private static function simpleTemplate(
        string $heading,
        string $headerColor,
        string $name,
        string $body,
        string $footer,
        string $btnUrl,
        string $btnText,
        string $btnColor
    ): string {
        $appName = APP_FULL_NAME;
        $year = date('Y');
        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>{$heading}</title></head>
<body style="margin:0;padding:0;background:#f4f6fb;font-family:'Segoe UI',Roboto,Arial,sans-serif;">
  <table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f6fb;padding:40px 0;">
    <tr><td align="center">
      <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 8px 30px rgba(0,0,0,0.08);border:1px solid #e5e7eb;">
        <tr>
          <td style="background:linear-gradient(135deg,{$headerColor}dd,{$headerColor});padding:40px;text-align:center;">
            <h1 style="margin:0;color:#ffffff;font-size:24px;font-weight:700;">{$heading}</h1>
            <p style="margin:8px 0 0;color:rgba(255,255,255,0.85);font-size:13px;">{$appName}</p>
          </td>
        </tr>
        <tr>
          <td style="padding:40px;">
            <p style="margin:0 0 16px;font-size:16px;color:#1f2937;font-weight:600;">Hi <strong>{$name}</strong>,</p>
            <div style="font-size:15px;color:#4b5563;line-height:1.7;margin-bottom:28px;">{$body}</div>
            <table width="100%" cellpadding="0" cellspacing="0">
              <tr><td align="center" style="padding:8px 0 28px;">
                <a href="{$btnUrl}" style="display:inline-block;background:{$btnColor};color:#ffffff;text-decoration:none;font-size:15px;font-weight:600;padding:14px 36px;border-radius:50px;box-shadow:0 4px 14px rgba(0,0,0,0.15);">
                  {$btnText}
                </a>
              </td></tr>
            </table>
            <p style="margin:0;font-size:13px;color:#9ca3af;text-align:center;">{$footer}</p>
          </td>
        </tr>
        <tr>
          <td style="background:#f9fafb;border-top:1px solid #f3f4f6;padding:20px 40px;text-align:center;">
            <p style="margin:0;font-size:12px;color:#9ca3af;">© {$year} TPMS. All rights reserved.</p>
          </td>
        </tr>
      </table>
    </td></tr>
  </table>
</body>
</html>
HTML;
    }

    // ================================================================
    // IMPORT STUDENTS WELCOME EMAIL
    // ================================================================

    /**
     * Send a welcome email to a student imported via Excel bulk import.
     * Includes their login credentials and a prompt to change password.
     *
     * @param string $toEmail         Student's email address
     * @param string $toName          Student's full name
     * @param string $studentId       Generated student ID (e.g. STU2025001)
     * @param string $tempPassword    Plain-text temporary password (shown once)
     * @return bool
     */
    public static function sendImportWelcome(
        string $toEmail,
        string $toName,
        string $studentId,
        string $tempPassword
    ): bool {
        try {
            $mail = self::create();
            $mail->addAddress($toEmail, $toName);
            $mail->Subject = '🎓 Welcome to TPMS — Your Account Has Been Created';
            $mail->Body    = self::importWelcomeTemplate($toName, $toEmail, $studentId, $tempPassword);
            $mail->AltBody = "Hi {$toName},\n\nYour TPMS student account has been created by the administrator.\n\n"
                           . "Login URL: " . FULL_URL . "/login\n"
                           . "Email: {$toEmail}\n"
                           . "Student ID: {$studentId}\n"
                           . "Temporary Password: {$tempPassword}\n\n"
                           . "IMPORTANT: Please log in and change your password immediately.\n\n"
                           . "- TPMS Team";

            self::sendWithRetry($mail);
            self::logMail("Import welcome email sent to {$toEmail} (ID: {$studentId})", 'SUCCESS');
            return true;
        } catch (Exception $e) {
            $err = !empty($mail->ErrorInfo) ? $mail->ErrorInfo : $e->getMessage();
            self::$lastError = $err;
            self::logMail("Failed import welcome email to {$toEmail}: {$err}", 'ERROR');
            return false;
        }
    }

    /**
     * HTML template for the import welcome email.
     */
    private static function importWelcomeTemplate(
        string $name,
        string $email,
        string $studentId,
        string $tempPassword
    ): string {
        $appName  = APP_FULL_NAME;
        $loginUrl = FULL_URL . '/login';
        $year     = date('Y');

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Welcome to TPMS</title>
</head>
<body style="margin:0;padding:0;background-color:#f4f6fb;font-family:'Segoe UI',Roboto,Helvetica,Arial,sans-serif;">
  <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f6fb;padding:40px 0;">
    <tr>
      <td align="center">
        <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 10px 30px rgba(0,0,0,0.08);border:1px solid #e5e7eb;">

          <!-- Header -->
          <tr>
            <td style="background:linear-gradient(135deg,#4f46e5 0%,#7c3aed 100%);padding:40px 40px 30px;text-align:center;">
              <div style="font-size:48px;margin-bottom:12px;">🎓</div>
              <h1 style="margin:0;color:#ffffff;font-size:26px;font-weight:700;letter-spacing:-0.5px;">Welcome to TPMS!</h1>
              <p style="margin:8px 0 0;color:rgba(255,255,255,0.85);font-size:14px;font-weight:500;">{$appName}</p>
            </td>
          </tr>

          <!-- Body -->
          <tr>
            <td style="padding:36px 40px;">
              <p style="margin:0 0 16px;font-size:16px;color:#1f2937;font-weight:600;">Hi <strong>{$name}</strong>,</p>
              <p style="margin:0 0 24px;font-size:15px;color:#4b5563;line-height:1.7;">
                Your student account has been created by the college administrator via bulk import. You can now log in to
                <strong>TPMS</strong> to browse job opportunities, apply for positions, track your placement journey, and more.
              </p>

              <!-- Credentials Box -->
              <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:24px;">
                <tr>
                  <td style="background:#f8fafc;border:1px solid #e2e8f0;border-left:4px solid #4f46e5;border-radius:10px;padding:20px 24px;">
                    <p style="margin:0 0 12px;font-size:14px;font-weight:700;color:#1e293b;text-transform:uppercase;letter-spacing:0.5px;">🔐 Your Login Credentials</p>
                    <table width="100%" cellpadding="4" cellspacing="0" style="font-size:14px;color:#334155;">
                      <tr>
                        <td width="140" style="font-weight:600;color:#64748b;">Login URL:</td>
                        <td><a href="{$loginUrl}" style="color:#4f46e5;text-decoration:none;font-weight:600;">{$loginUrl}</a></td>
                      </tr>
                      <tr>
                        <td style="font-weight:600;color:#64748b;">Email:</td>
                        <td style="font-family:'Courier New',monospace;font-weight:700;color:#0f172a;">{$email}</td>
                      </tr>
                      <tr>
                        <td style="font-weight:600;color:#64748b;">Student ID:</td>
                        <td style="font-family:'Courier New',monospace;font-weight:700;color:#4f46e5;">{$studentId}</td>
                      </tr>
                      <tr>
                        <td style="font-weight:600;color:#64748b;">Temp Password:</td>
                        <td style="font-family:'Courier New',monospace;font-weight:800;font-size:15px;color:#dc2626;letter-spacing:1px;">{$tempPassword}</td>
                      </tr>
                    </table>
                  </td>
                </tr>
              </table>

              <!-- Warning Box -->
              <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:28px;">
                <tr>
                  <td style="background:#fef9c3;border-left:4px solid #eab308;border-radius:8px;padding:14px 20px;">
                    <p style="margin:0;font-size:13.5px;color:#713f12;line-height:1.6;">
                      ⚠️ <strong>Important:</strong> This is a temporary password. Please log in immediately and change your password from your profile settings for security.
                    </p>
                  </td>
                </tr>
              </table>

              <!-- CTA Button -->
              <table width="100%" cellpadding="0" cellspacing="0">
                <tr>
                  <td align="center" style="padding:8px 0 20px;">
                    <a href="{$loginUrl}"
                       style="display:inline-block;background:linear-gradient(135deg,#4f46e5,#7c3aed);color:#ffffff;text-decoration:none;font-size:16px;font-weight:700;padding:16px 48px;border-radius:50px;box-shadow:0 4px 15px rgba(79,70,229,0.4);letter-spacing:0.3px;">
                      🚀 &nbsp; Login to TPMS
                    </a>
                  </td>
                </tr>
              </table>

              <p style="margin:0;font-size:13px;color:#94a3b8;text-align:center;line-height:1.6;">
                If you have any issues logging in, contact your placement coordinator.
              </p>
            </td>
          </tr>

          <!-- Footer -->
          <tr>
            <td style="background:#f9fafb;border-top:1px solid #f3f4f6;padding:24px 40px;text-align:center;">
              <p style="margin:0 0 4px;font-size:13px;color:#6b7280;font-weight:500;">Training &amp; Placement Management System (TPMS)</p>
              <p style="margin:0;font-size:12px;color:#9ca3af;">© {$year} TPMS. All rights reserved.</p>
            </td>
          </tr>

        </table>
      </td>
    </tr>
  </table>
</body>
</html>
HTML;
    }

    // =========================================================
    // TEST EMAIL TEMPLATE
    // =========================================================

    private static function testEmailTemplate(string $toName): string {
        $appName = defined('APP_FULL_NAME') ? APP_FULL_NAME : 'Training & Placement Management System';
        $host    = defined('SMTP_HOST') ? SMTP_HOST : 'smtp-relay.brevo.com';
        $from    = defined('SMTP_FROM_EMAIL') ? SMTP_FROM_EMAIL : '-';
        $sentAt  = date('d M Y H:i:s T');
        $year    = date('Y');
        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"><title>TPMS – SMTP Test</title></head>
<body style="margin:0;padding:0;background:#f1f5f9;font-family:'Segoe UI',Helvetica,Arial,sans-serif;">
  <table width="100%" cellpadding="0" cellspacing="0">
    <tr>
      <td align="center" style="padding:40px 20px;">
        <table width="580" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 8px 24px rgba(0,0,0,0.08);border:1px solid #e5e7eb;">

          <!-- Header -->
          <tr>
            <td style="background:linear-gradient(135deg,#4f46e5 0%,#7c3aed 100%);padding:36px 40px 28px;text-align:center;">
              <div style="font-size:44px;margin-bottom:10px;">✅</div>
              <h1 style="margin:0;color:#ffffff;font-size:24px;font-weight:700;letter-spacing:-0.5px;">Brevo SMTP Test</h1>
              <p style="margin:8px 0 0;color:rgba(255,255,255,0.85);font-size:13px;">$appName</p>
            </td>
          </tr>

          <!-- Body -->
          <tr>
            <td style="padding:36px 40px;">
              <p style="margin:0 0 16px;font-size:16px;color:#1f2937;font-weight:600;">Hi <strong>$toName</strong>,</p>
              <p style="margin:0 0 24px;font-size:15px;color:#4b5563;line-height:1.7;">
                This is a <strong>test email</strong> from TPMS. If you received this, your
                <strong>Brevo SMTP</strong> configuration is working correctly and all system
                emails will be delivered reliably.
              </p>

              <!-- SMTP Details -->
              <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:24px;">
                <tr>
                  <td style="background:#f0fdf4;border:1px solid #bbf7d0;border-left:4px solid #10b981;border-radius:10px;padding:20px 24px;">
                    <p style="margin:0 0 12px;font-size:13px;font-weight:700;color:#065f46;text-transform:uppercase;letter-spacing:0.5px;">📡 SMTP Configuration</p>
                    <table width="100%" cellpadding="4" cellspacing="0" style="font-size:13px;color:#334155;">
                      <tr><td width="130" style="font-weight:600;color:#64748b;">Provider:</td><td style="font-weight:700;color:#047857;">Brevo SMTP</td></tr>
                      <tr><td style="font-weight:600;color:#64748b;">Host:</td><td style="font-family:'Courier New',monospace;">$host</td></tr>
                      <tr><td style="font-weight:600;color:#64748b;">From:</td><td>$from</td></tr>
                      <tr><td style="font-weight:600;color:#64748b;">Sent at:</td><td>$sentAt</td></tr>
                      <tr><td style="font-weight:600;color:#64748b;">Status:</td><td><span style="color:#059669;font-weight:700;">✅ Connected &amp; Delivered</span></td></tr>
                    </table>
                  </td>
                </tr>
              </table>

              <p style="margin:0;font-size:13px;color:#6b7280;line-height:1.6;">
                You can safely ignore this email. It was triggered from the
                <strong>Admin → Settings → Email Configuration</strong> panel.
              </p>
            </td>
          </tr>

          <!-- Footer -->
          <tr>
            <td style="padding:20px 40px;background:#f8fafc;border-top:1px solid #e5e7eb;text-align:center;">
              <p style="margin:0 0 4px;font-size:13px;color:#6b7280;font-weight:500;">$appName (TPMS)</p>
              <p style="margin:0;font-size:12px;color:#9ca3af;">© $year TPMS. All rights reserved.</p>
            </td>
          </tr>

        </table>
      </td>
    </tr>
  </table>
</body>
</html>
HTML;
    }
}
