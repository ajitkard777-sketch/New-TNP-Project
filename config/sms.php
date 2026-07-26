<?php
/**
 * TPMS - SMS Notification Configuration
 */

if (!defined('TPMS_RUNNING')) {
    define('TPMS_RUNNING', true);
}

return [
    /*
    |--------------------------------------------------------------------------
    | Global SMS Enable/Disable
    |--------------------------------------------------------------------------
    */
    'enabled' => true,

    /*
    |--------------------------------------------------------------------------
    | Default SMS Provider
    |--------------------------------------------------------------------------
    | Supported options: 'twilio', 'fast2sms', 'msg91'
    */
    'default_provider' => 'twilio',

    /*
    |--------------------------------------------------------------------------
    | Max Retry Attempts for Failed SMS
    |--------------------------------------------------------------------------
    */
    'max_retries' => 3,

    /*
    |--------------------------------------------------------------------------
    | SMS Providers Configuration
    |--------------------------------------------------------------------------
    */
    'providers' => [
        'twilio' => [
            'account_sid' => 'AC_YOUR_TWILIO_ACCOUNT_SID',
            'auth_token'  => 'YOUR_TWILIO_AUTH_TOKEN',
            'from_number' => '+18005550199',
        ],

        'fast2sms' => [
            'api_key'   => 'YOUR_FAST2SMS_API_KEY',
            'sender_id' => 'TXTIND', // Approved 6-character DLT Header / Sender ID
            'route'     => 'v3',     // 'v3' (Quick SMS / DLT) or 'otp'
        ],

        'msg91' => [
            'auth_key'  => 'YOUR_MSG91_AUTH_KEY',
            'sender_id' => 'TPMSYS', // 6-character DLT Header
            'route'     => '4',      // Transactional route
            'flow_ids'  => [
                'company_verified'    => 'FLOW_ID_COMPANY_VERIFIED',
                'job_posted'          => 'FLOW_ID_JOB_POSTED',
                'student_shortlisted' => 'FLOW_ID_STUDENT_SHORTLISTED',
                'interview_scheduled' => 'FLOW_ID_INTERVIEW_SCHEDULED',
                'offer_released'      => 'FLOW_ID_OFFER_RELEASED',
                'password_reset'      => 'FLOW_ID_PASSWORD_RESET',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default SMS Event Templates
    |--------------------------------------------------------------------------
    */
    'templates' => [
        'company_verified' => "Hello {company_name}, your company account on TPMS has been verified. You can now log in and post job opportunities.",
        'job_posted' => "New Job Opening: {job_title} at {company_name}. Package: {package}. Apply now on TPMS portal!",
        'student_shortlisted' => "Congratulations {student_name}! You have been shortlisted for {job_title} at {company_name}.",
        'interview_scheduled' => "Interview Update: {student_name}, your interview for {job_title} at {company_name} is scheduled on {date} at {time}. Mode: {mode}.",
        'offer_released' => "Congratulations {student_name}! An offer letter has been released for {job_title} at {company_name}. Check TPMS portal for details.",
        'password_reset' => "Your TPMS password reset verification code is: {otp}. This code is valid for 10 minutes. Do not share it with anyone.",
    ]
];
