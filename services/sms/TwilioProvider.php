<?php
/**
 * TPMS - Twilio SMS Provider Implementation
 */

require_once __DIR__ . '/SmsProviderInterface.php';

class TwilioProvider implements SmsProviderInterface {
    private string $accountSid;
    private string $authToken;
    private string $fromNumber;

    public function __construct(array $config) {
        $this->accountSid = $config['account_sid'] ?? '';
        $this->authToken  = $config['auth_token'] ?? '';
        $this->fromNumber = $config['from_number'] ?? '';
    }

    public function getName(): string {
        return 'twilio';
    }

    public function send(string $to, string $message, array $options = []): array {
        if (empty($this->accountSid) || empty($this->authToken) || empty($this->fromNumber)) {
            return [
                'success'      => false,
                'message_id'   => null,
                'error'        => 'Twilio credentials or From number missing in configuration.',
                'raw_response' => null
            ];
        }

        // Format phone number (ensure + prefix if missing)
        $formattedTo = trim($to);
        if (!str_starts_with($formattedTo, '+')) {
            $formattedTo = '+' . preg_replace('/[^0-9]/', '', $formattedTo);
        }

        $url = "https://api.twilio.com/2010-04-01/Accounts/{$this->accountSid}/Messages.json";

        $postData = http_build_query([
            'To'   => $formattedTo,
            'From' => $this->fromNumber,
            'Body' => $message,
        ]);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_USERPWD, "{$this->accountSid}:{$this->authToken}");
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For local dev compatibility

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            return [
                'success'      => false,
                'message_id'   => null,
                'error'        => 'cURL Error: ' . $curlError,
                'raw_response' => null
            ];
        }

        $responseData = json_decode($response, true);

        if ($httpCode >= 200 && $httpCode < 300 && isset($responseData['sid'])) {
            return [
                'success'      => true,
                'message_id'   => $responseData['sid'],
                'error'        => null,
                'raw_response' => $responseData
            ];
        }

        $errorMessage = $responseData['message'] ?? ("Twilio HTTP Error code: " . $httpCode);
        return [
            'success'      => false,
            'message_id'   => null,
            'error'        => $errorMessage,
            'raw_response' => $responseData ?: $response
        ];
    }
}
