# SMS Notification Module — Setup & Configuration Guide

This document provides setup and configuration instructions for the **SMS Notification Module** in the Training & Placement Management System (TPMS).

---

## 1. Overview & Architecture

The SMS Notification Module follows a clean provider abstraction architecture (`SmsProviderInterface`), allowing you to switch between multiple SMS providers without modifying application code.

### Supported SMS Providers:
1. **Twilio** (Global / International SMS)
2. **Fast2SMS** (India DLT / Quick SMS)
3. **MSG91** (India DLT / Flow SMS)

### Supported Events:
1. **Company Verified**: Triggered when an admin approves a company registration.
2. **Job Posted**: Triggered when a new job is posted and approved for student applications.
3. **Student Shortlisted**: Triggered when a student's application status is set to `shortlisted`.
4. **Interview Scheduled**: Triggered when an interview round is scheduled for a student.
5. **Offer Letter Released**: Triggered when a student is selected and an offer letter is released.
6. **Password Reset**: Triggered when a password reset OTP/link request is initiated.

---

## 2. Database Migration Setup

The module includes an automated migration file: `database/migrations/007_create_sms_tables.php`.

### Created Tables:
- **`sms_logs`**: Stores all outbound SMS logs, recipient phone numbers, event types, provider used, delivery status (`sent`, `failed`, `pending`), error messages, retry attempts, and timestamps.
- **`sms_settings`**: Stores dynamic administrator configuration overrides (API keys, active provider, enable/disable toggle, custom event templates).

The migration runs automatically when you access the application through the front controller (`index.php`).

---

## 3. Configuration File (`config/sms.php`)

Base settings are defined in `config/sms.php`. Admin settings saved in the Admin Control Panel override these default values dynamically.

```php
return [
    'enabled' => true,
    'default_provider' => 'twilio', // 'twilio' | 'fast2sms' | 'msg91'
    'max_retries' => 3,

    'providers' => [
        'twilio' => [
            'account_sid' => 'YOUR_TWILIO_ACCOUNT_SID',
            'auth_token'  => 'YOUR_TWILIO_AUTH_TOKEN',
            'from_number' => '+18005550199',
        ],
        'fast2sms' => [
            'api_key'   => 'YOUR_FAST2SMS_API_KEY',
            'sender_id' => 'TXTIND',
            'route'     => 'v3',
        ],
        'msg91' => [
            'auth_key'  => 'YOUR_MSG91_AUTH_KEY',
            'sender_id' => 'TPMSYS',
            'route'     => '4',
        ],
    ],

    'templates' => [
        'company_verified'    => "Hello {company_name}, your company account on TPMS has been verified.",
        'job_posted'          => "New Job Opening: {job_title} at {company_name}. Package: {package}.",
        'student_shortlisted' => "Congratulations {student_name}! You have been shortlisted for {job_title} at {company_name}.",
        'interview_scheduled' => "Interview Update: {student_name}, your interview for {job_title} at {company_name} is scheduled on {date} at {time}. Mode: {mode}.",
        'offer_released'      => "Congratulations {student_name}! An offer letter has been released for {job_title} at {company_name}.",
        'password_reset'      => "Your TPMS password reset verification code is: {otp}.",
    ]
];
```

---

## 4. Provider API Account Setup

### Option A: Twilio Setup
1. Sign up at [Twilio.com](https://www.twilio.com/).
2. Navigate to your Twilio Console Dashboard.
3. Copy your **Account SID**, **Auth Token**, and purchased/trial **Twilio Phone Number**.
4. Enter these credentials in **Admin -> SMS Settings -> Twilio Tab**.

### Option B: Fast2SMS Setup (India)
1. Sign up at [Fast2SMS.com](https://www.fast2sms.com/).
2. Obtain your **API Authorization Key** from Dev API panel.
3. Configure your approved DLT Header / Sender ID (default `TXTIND`).
4. Enter credentials in **Admin -> SMS Settings -> Fast2SMS Tab**.

### Option C: MSG91 Setup (India)
1. Sign up at [MSG91.com](https://msg91.com/).
2. Copy your **Auth Key** from the API section.
3. Register your Sender ID and DLT Flow / Template IDs.
4. Enter credentials in **Admin -> SMS Settings -> MSG91 Tab**.

---

## 5. Admin Control Panel Features

1. **SMS Settings (`/admin/sms-settings`)**:
   - Master Toggle to enable or disable SMS notifications globally.
   - Choose default active SMS provider.
   - Configure API credentials for Twilio, Fast2SMS, and MSG91.
   - Customize message templates for all 6 events with shortcodes.

2. **SMS Notification History (`/admin/sms-logs`)**:
   - View real-time audit log of all sent, pending, and failed SMS attempts.
   - Filter logs by status, event type, or provider.
   - Search by recipient phone number or message content.
   - View complete message body and error details in a popup modal.
   - **Manual Retry**: Click the "Retry" button on any failed SMS entry to attempt sending it again immediately.

---

## 6. Testing & Troubleshooting

- **Disabling SMS globally**: Flip the "Enable SMS Module" toggle in `/admin/sms-settings` to OFF during maintenance.
- **Log Inspection**: All failed SMS attempts record the exact error returned by the provider (e.g. invalid credentials, insufficient balance, missing DLT template) in the `sms_logs` database table.
- **Automatic Retry**: If a provider call fails due to temporary network error, `SmsService` automatically retries up to the configured `max_retries` value (default 3 attempts) before logging a failure.
