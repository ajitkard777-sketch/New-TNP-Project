<?php
/**
 * TPMS - SMS Notification Service (Manager / Orchestrator)
 */

require_once ROOT_PATH . '/services/sms/SmsProviderInterface.php';
require_once ROOT_PATH . '/services/sms/TwilioProvider.php';
require_once ROOT_PATH . '/services/sms/Fast2SMSProvider.php';
require_once ROOT_PATH . '/services/sms/MSG91Provider.php';

class SmsService {
    private static ?SmsService $instance = null;
    private Database $db;
    private array $config;

    private function __construct() {
        $this->db = Database::getInstance();
        $this->loadConfig();
    }

    public static function getInstance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Load base config and apply DB settings overrides
     */
    public function loadConfig(): void {
        $fileConfig = require ROOT_PATH . '/config/sms.php';

        // Fetch DB overrides if table exists
        try {
            $dbSettings = $this->db->fetchAll("SELECT setting_key, setting_value FROM sms_settings");
            $overrides = [];
            foreach ($dbSettings as $row) {
                $overrides[$row['setting_key']] = $row['setting_value'];
            }

            if (isset($overrides['sms_enabled'])) {
                $fileConfig['enabled'] = (bool)$overrides['sms_enabled'];
            }
            if (!empty($overrides['default_provider'])) {
                $fileConfig['default_provider'] = $overrides['default_provider'];
            }
            if (!empty($overrides['max_retries'])) {
                $fileConfig['max_retries'] = (int)$overrides['max_retries'];
            }

            // Provider specific overrides
            if (!empty($overrides['twilio_account_sid'])) $fileConfig['providers']['twilio']['account_sid'] = $overrides['twilio_account_sid'];
            if (!empty($overrides['twilio_auth_token']))  $fileConfig['providers']['twilio']['auth_token']  = $overrides['twilio_auth_token'];
            if (!empty($overrides['twilio_from_number'])) $fileConfig['providers']['twilio']['from_number'] = $overrides['twilio_from_number'];

            if (!empty($overrides['fast2sms_api_key']))   $fileConfig['providers']['fast2sms']['api_key']   = $overrides['fast2sms_api_key'];
            if (!empty($overrides['fast2sms_sender_id'])) $fileConfig['providers']['fast2sms']['sender_id'] = $overrides['fast2sms_sender_id'];
            if (!empty($overrides['fast2sms_route']))     $fileConfig['providers']['fast2sms']['route']     = $overrides['fast2sms_route'];

            if (!empty($overrides['msg91_auth_key']))     $fileConfig['providers']['msg91']['auth_key']     = $overrides['msg91_auth_key'];
            if (!empty($overrides['msg91_sender_id']))    $fileConfig['providers']['msg91']['sender_id']    = $overrides['msg91_sender_id'];
            if (!empty($overrides['msg91_route']))        $fileConfig['providers']['msg91']['route']        = $overrides['msg91_route'];

            // Template overrides
            foreach ($fileConfig['templates'] as $tplKey => $defaultText) {
                if (!empty($overrides['template_' . $tplKey])) {
                    $fileConfig['templates'][$tplKey] = $overrides['template_' . $tplKey];
                }
            }
        } catch (Exception $e) {
            // DB table might not exist yet before migration
        }

        $this->config = $fileConfig;
    }

    /**
     * Get active provider instance
     */
    public function getProvider(?string $providerName = null): SmsProviderInterface {
        $name = strtolower($providerName ?: ($this->config['default_provider'] ?? 'twilio'));
        $providerConfig = $this->config['providers'][$name] ?? [];

        switch ($name) {
            case 'fast2sms':
                return new Fast2SMSProvider($providerConfig);
            case 'msg91':
                return new MSG91Provider($providerConfig);
            case 'twilio':
            default:
                return new TwilioProvider($providerConfig);
        }
    }

    /**
     * Send SMS with retry mechanism and DB logging
     */
    public function send(string $phone, string $message, string $eventType = 'general', ?int $userId = null, array $options = []): bool {
        if (empty($phone)) {
            return false;
        }

        // Check global enabled toggle
        if (empty($this->config['enabled'])) {
            // Record log as skipped / disabled
            try {
                $this->db->insert(
                    "INSERT INTO sms_logs (user_id, recipient_phone, event_type, provider, message, status, error_message, retry_count) VALUES (?, ?, ?, ?, ?, 'failed', 'SMS Module is disabled in settings', 0)",
                    [$userId, $phone, $eventType, $this->config['default_provider'] ?? 'system', $message]
                );
            } catch (Exception $e) {}
            return false;
        }

        $providerName = $options['provider'] ?? ($this->config['default_provider'] ?? 'twilio');
        $provider = $this->getProvider($providerName);

        $maxRetries = (int)($this->config['max_retries'] ?? 3);
        $attempts = 0;
        $sentSuccess = false;
        $lastError = '';

        // Create initial pending log record
        $logId = 0;
        try {
            $logId = $this->db->insert(
                "INSERT INTO sms_logs (user_id, recipient_phone, event_type, provider, message, status, retry_count) VALUES (?, ?, ?, ?, ?, 'pending', 0)",
                [$userId, $phone, $eventType, $provider->getName(), $message]
            );
        } catch (Exception $e) {}

        while ($attempts < $maxRetries && !$sentSuccess) {
            $attempts++;
            $res = $provider->send($phone, $message, $options);

            if ($res['success']) {
                $sentSuccess = true;
                if ($logId > 0) {
                    $this->db->update(
                        "UPDATE sms_logs SET status = 'sent', sent_at = NOW(), retry_count = ?, error_message = NULL WHERE id = ?",
                        [$attempts, $logId]
                    );
                }
                break;
            } else {
                $lastError = $res['error'] ?? 'Unknown error';
                if ($logId > 0) {
                    $this->db->update(
                        "UPDATE sms_logs SET status = 'failed', retry_count = ?, error_message = ? WHERE id = ?",
                        [$attempts, $lastError, $logId]
                    );
                }
                // Brief pause before retry
                if ($attempts < $maxRetries) {
                    usleep(300000); // 300ms pause
                }
            }
        }

        return $sentSuccess;
    }

    /**
     * Manually retry a failed SMS log entry from history
     */
    public function retryLog(int $logId): array {
        $log = $this->db->fetchOne("SELECT * FROM sms_logs WHERE id = ?", [$logId]);
        if (!$log) {
            return ['success' => false, 'message' => 'SMS Log entry not found'];
        }

        $provider = $this->getProvider($log['provider']);
        $res = $provider->send($log['recipient_phone'], $log['message']);
        $newCount = (int)$log['retry_count'] + 1;

        if ($res['success']) {
            $this->db->update(
                "UPDATE sms_logs SET status = 'sent', sent_at = NOW(), retry_count = ?, error_message = NULL WHERE id = ?",
                [$newCount, $logId]
            );
            return ['success' => true, 'message' => 'SMS resent successfully!'];
        } else {
            $this->db->update(
                "UPDATE sms_logs SET status = 'failed', retry_count = ?, error_message = ? WHERE id = ?",
                [$newCount, $res['error'] ?? 'Retry failed', $logId]
            );
            return ['success' => false, 'message' => 'Retry failed: ' . ($res['error'] ?? 'Unknown error')];
        }
    }

    // =========================================================================
    // EVENT NOTIFICATION HANDLERS
    // =========================================================================

    /**
     * 1. Company Verified Event
     */
    public function sendCompanyVerified(array $company): bool {
        $phone = $company['contact_phone'] ?? '';
        if (empty($phone)) return false;

        $template = $this->config['templates']['company_verified'] ?? "Your company account on TPMS has been verified.";
        $message = str_replace(
            ['{company_name}'],
            [$company['company_name'] ?? 'Company'],
            $template
        );

        return $this->send($phone, $message, 'company_verified', $company['user_id'] ?? null);
    }

    /**
     * 2. Job Posted Event (Notify Company and/or Eligible Students)
     */
    public function sendJobPosted(array $job, ?array $company = null): int {
        $companyName = $company['company_name'] ?? 'TPMS Partner';
        $package = !empty($job['salary_max']) ? "₹{$job['salary_max']} LPA" : (!empty($job['salary_min']) ? "₹{$job['salary_min']} LPA" : 'Best in Industry');

        $template = $this->config['templates']['job_posted'] ?? "New Job Opening: {job_title} at {company_name}. Apply now on TPMS portal!";
        $message = str_replace(
            ['{job_title}', '{company_name}', '{package}'],
            [$job['title'] ?? 'New Position', $companyName, $package],
            $template
        );

        // Fetch students with valid phone numbers
        $students = $this->db->fetchAll("SELECT user_id, phone FROM students WHERE phone IS NOT NULL AND phone != '' LIMIT 100");
        $sentCount = 0;

        foreach ($students as $student) {
            if (!empty($student['phone'])) {
                $ok = $this->send($student['phone'], $message, 'job_posted', $student['user_id']);
                if ($ok) $sentCount++;
            }
        }

        return $sentCount;
    }

    /**
     * 3. Student Shortlisted Event
     */
    public function sendStudentShortlisted(array $student, string $jobTitle, string $companyName): bool {
        $phone = $student['phone'] ?? '';
        if (empty($phone)) return false;

        $studentName = ($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? '');
        $template = $this->config['templates']['student_shortlisted'] ?? "Congratulations {student_name}! You have been shortlisted for {job_title} at {company_name}.";

        $message = str_replace(
            ['{student_name}', '{job_title}', '{company_name}'],
            [trim($studentName), $jobTitle, $companyName],
            $template
        );

        return $this->send($phone, $message, 'student_shortlisted', $student['user_id'] ?? null);
    }

    /**
     * 4. Interview Scheduled Event
     */
    public function sendInterviewScheduled(array $student, string $jobTitle, string $companyName, string $date, string $time, string $mode): bool {
        $phone = $student['phone'] ?? '';
        if (empty($phone)) return false;

        $studentName = ($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? '');
        $template = $this->config['templates']['interview_scheduled'] ?? "Interview Update: {student_name}, your interview for {job_title} at {company_name} is scheduled on {date} at {time}. Mode: {mode}.";

        $message = str_replace(
            ['{student_name}', '{job_title}', '{company_name}', '{date}', '{time}', '{mode}'],
            [trim($studentName), $jobTitle, $companyName, $date, $time, ucfirst($mode)],
            $template
        );

        return $this->send($phone, $message, 'interview_scheduled', $student['user_id'] ?? null);
    }

    /**
     * 5. Offer Letter Released Event
     */
    public function sendOfferLetterReleased(array $student, string $jobTitle, string $companyName): bool {
        $phone = $student['phone'] ?? '';
        if (empty($phone)) return false;

        $studentName = ($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? '');
        $template = $this->config['templates']['offer_released'] ?? "Congratulations {student_name}! An offer letter has been released for {job_title} at {company_name}.";

        $message = str_replace(
            ['{student_name}', '{job_title}', '{company_name}'],
            [trim($studentName), $jobTitle, $companyName],
            $template
        );

        return $this->send($phone, $message, 'offer_released', $student['user_id'] ?? null);
    }

    /**
     * 6. Password Reset Event
     */
    public function sendPasswordReset(string $phone, string $otp, ?int $userId = null): bool {
        if (empty($phone)) return false;

        $template = $this->config['templates']['password_reset'] ?? "Your TPMS password reset verification code is: {otp}.";
        $message = str_replace(['{otp}'], [$otp], $template);

        return $this->send($phone, $message, 'password_reset', $userId);
    }
}
