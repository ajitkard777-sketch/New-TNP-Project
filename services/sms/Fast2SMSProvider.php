<?php
/**
 * TPMS - Fast2SMS Provider Implementation
 */

require_once __DIR__ . '/SmsProviderInterface.php';

class Fast2SMSProvider implements SmsProviderInterface {
    private string $apiKey;
    private string $senderId;
    private string $route;

    public function __construct(array $config) {
        $this->apiKey   = $config['api_key'] ?? '';
        $this->senderId = $config['sender_id'] ?? 'TXTIND';
        $this->route    = $config['route'] ?? 'v3';
    }

    public function getName(): string {
        return 'fast2sms';
    }

    public function send(string $to, string $message, array $options = []): array {
        if (empty($this->apiKey)) {
            return [
                'success'      => false,
                'message_id'   => null,
                'error'        => 'Fast2SMS API Key missing in configuration.',
                'raw_response' => null
            ];
        }

        // Clean phone number (keep last 10 digits for Indian numbers if needed)
        $cleanPhone = preg_replace('/[^0-9]/', '', $to);
        if (strlen($cleanPhone) > 10) {
            $cleanPhone = substr($cleanPhone, -10);
        }

        $url = "https://www.fast2sms.com/dev/bulkV2";

        $postFields = [
            'route'     => $options['route'] ?? $this->route,
            'message'   => $message,
            'numbers'   => $cleanPhone,
            'sender_id' => $options['sender_id'] ?? $this->senderId,
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postFields));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "authorization: {$this->apiKey}",
            "Content-Type: application/json",
            "Accept: application/json"
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

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

        if ($httpCode === 200 && isset($responseData['return']) && $responseData['return'] === true) {
            $msgId = $responseData['request_id'] ?? ($responseData['message'][0] ?? 'fast2sms_' . time());
            return [
                'success'      => true,
                'message_id'   => (string)$msgId,
                'error'        => null,
                'raw_response' => $responseData
            ];
        }

        $errorMessage = $responseData['message'][0] ?? ($responseData['message'] ?? 'Fast2SMS API request failed.');
        return [
            'success'      => false,
            'message_id'   => null,
            'error'        => is_array($errorMessage) ? implode(', ', $errorMessage) : $errorMessage,
            'raw_response' => $responseData ?: $response
        ];
    }
}
