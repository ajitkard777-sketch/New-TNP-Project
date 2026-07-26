<?php
/**
 * TPMS - MSG91 SMS Provider Implementation
 */

require_once __DIR__ . '/SmsProviderInterface.php';

class MSG91Provider implements SmsProviderInterface {
    private string $authKey;
    private string $senderId;
    private string $route;

    public function __construct(array $config) {
        $this->authKey  = $config['auth_key'] ?? '';
        $this->senderId = $config['sender_id'] ?? 'TPMSYS';
        $this->route    = $config['route'] ?? '4';
    }

    public function getName(): string {
        return 'msg91';
    }

    public function send(string $to, string $message, array $options = []): array {
        if (empty($this->authKey)) {
            return [
                'success'      => false,
                'message_id'   => null,
                'error'        => 'MSG91 Auth Key missing in configuration.',
                'raw_response' => null
            ];
        }

        // Clean phone number format
        $cleanPhone = preg_replace('/[^0-9]/', '', $to);
        if (strlen($cleanPhone) === 10) {
            $cleanPhone = '91' . $cleanPhone; // Default to India prefix if 10 digits
        }

        $flowId = $options['flow_id'] ?? null;

        // If flow_id is provided, use MSG91 v5 Flow API
        if ($flowId) {
            $url = "https://control.msg91.com/api/v5/flow/";
            $postFields = [
                'template_id' => $flowId,
                'sender'      => $options['sender_id'] ?? $this->senderId,
                'short_url'   => 0,
                'recipients'  => [
                    [
                        'mobiles' => $cleanPhone,
                        'message' => $message
                    ]
                ]
            ];
        } else {
            // Use MSG91 v2 Direct SMS API
            $url = "https://api.msg91.com/api/v2/sendsms";
            $postFields = [
                'sender'    => $options['sender_id'] ?? $this->senderId,
                'route'     => $options['route'] ?? $this->route,
                'country'   => '91',
                'sms'       => [
                    [
                        'message' => $message,
                        'to'      => [$cleanPhone]
                    ]
                ]
            ];
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postFields));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "authkey: {$this->authKey}",
            "content-type: application/json"
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

        if ($httpCode === 200 && (isset($responseData['type']) && $responseData['type'] === 'success')) {
            return [
                'success'      => true,
                'message_id'   => $responseData['message'] ?? 'msg91_' . time(),
                'error'        => null,
                'raw_response' => $responseData
            ];
        }

        $errorMessage = $responseData['message'] ?? ("MSG91 Request failed with HTTP code " . $httpCode);
        return [
            'success'      => false,
            'message_id'   => null,
            'error'        => is_array($errorMessage) ? json_encode($errorMessage) : $errorMessage,
            'raw_response' => $responseData ?: $response
        ];
    }
}
