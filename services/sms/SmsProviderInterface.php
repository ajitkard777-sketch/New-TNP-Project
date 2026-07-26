<?php
/**
 * TPMS - SMS Provider Interface (Abstraction Layer)
 */

interface SmsProviderInterface {
    /**
     * Get provider name identifier
     */
    public function getName(): string;

    /**
     * Send SMS message to target phone number
     *
     * @param string $to Recipient phone number
     * @param string $message Text message body
     * @param array $options Provider-specific parameters (e.g. flow_id, template_id, etc.)
     * @return array Standardized response array:
     *               [
     *                 'success'     => (bool),
     *                 'message_id'  => (?string),
     *                 'error'       => (?string),
     *                 'raw_response' => (mixed)
     *               ]
     */
    public function send(string $to, string $message, array $options = []): array;
}
