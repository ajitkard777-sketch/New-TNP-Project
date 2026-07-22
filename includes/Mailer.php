<?php
/**
 * TPMS - Email Service using PHPMailer
 * Handles all outgoing emails via Gmail SMTP
 */

require_once ROOT_PATH . '/vendor/phpmailer/src/Exception.php';
require_once ROOT_PATH . '/vendor/phpmailer/src/PHPMailer.php';
require_once ROOT_PATH . '/vendor/phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class Mailer {

    /**
     * Create a configured PHPMailer instance
     */
    private static function create(): PHPMailer {
        $mail = new PHPMailer(true);

        // Server settings
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USERNAME;
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;
        $mail->CharSet    = 'UTF-8';

        // Sender
        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->isHTML(true);

        return $mail;
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

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log('Mailer Error (sendPasswordReset): ' . $e->getMessage());
            return false;
        }
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

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log('Mailer Error (sendWelcome): ' . $e->getMessage());
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
}
