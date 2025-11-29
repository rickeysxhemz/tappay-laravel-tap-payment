<?php

declare(strict_types=1);

namespace TapPay\Tap\Webhooks;

use Illuminate\Http\Request;

class WebhookValidator
{
    /**
     * Create a new webhook validator instance
     *
     * @param  string|null  $secret  Webhook secret key (uses config if not provided)
     *
     * @throws \RuntimeException If secret key is not configured
     */
    public function __construct(
        protected ?string $secret = null
    ) {
        $this->secret = $secret ?? config('tap.webhook.secret') ?? config('tap.secret');

        if (empty($this->secret)) {
            throw new \RuntimeException('Webhook secret key is not configured. Please set tap.webhook.secret or tap.secret in config.');
        }
    }

    /**
     * Validate the webhook signature using HMAC-SHA256
     *
     * @param  Request  $request  The incoming webhook request
     * @return WebhookValidationResult Validation result with error details if failed
     */
    public function validate(Request $request): WebhookValidationResult
    {
        $signature = $request->header('x-tap-signature');

        // Validate signature exists and has correct length (SHA256 = 64 hex chars)
        if (! $signature || strlen($signature) !== 64) {
            return WebhookValidationResult::failed(
                'Missing or invalid signature length',
                [
                    'has_signature' => ! empty($signature),
                    'signature_length' => $signature ? strlen($signature) : 0,
                ]
            );
        }

        $payload = $request->getContent();

        if (empty($payload)) {
            return WebhookValidationResult::failed('Empty payload');
        }

        $data = json_decode($payload, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return WebhookValidationResult::failed(
                'Invalid JSON',
                ['json_error' => json_last_error_msg()]
            );
        }

        if (! is_array($data) || empty($data)) {
            return WebhookValidationResult::failed(
                'Invalid payload structure',
                ['is_array' => is_array($data)]
            );
        }

        $hashString = $this->buildHashString($data);
        $computedSignature = hash_hmac('sha256', $hashString, $this->secret);

        if (! hash_equals($computedSignature, $signature)) {
            return WebhookValidationResult::failed(
                'Signature mismatch',
                [
                    'expected_length' => strlen($computedSignature),
                    'received_length' => strlen($signature),
                ]
            );
        }

        return WebhookValidationResult::success();
    }

    /**
     * Validate webhook payload directly (without Request object)
     *
     * @param  array  $payload  The decoded webhook payload
     * @param  string  $signature  The signature from x-tap-signature header
     * @return WebhookValidationResult Validation result with error details if failed
     */
    public function validatePayload(array $payload, string $signature): WebhookValidationResult
    {
        if (empty($signature) || strlen($signature) !== 64) {
            return WebhookValidationResult::failed(
                'Invalid signature',
                ['signature_length' => strlen($signature)]
            );
        }

        if (empty($payload)) {
            return WebhookValidationResult::failed('Empty payload');
        }

        $hashString = $this->buildHashString($payload);
        $computedSignature = hash_hmac('sha256', $hashString, $this->secret);

        if (! hash_equals($computedSignature, $signature)) {
            return WebhookValidationResult::failed('Signature mismatch');
        }

        return WebhookValidationResult::success();
    }

    /**
     * Build the hash string from webhook payload
     * Concatenates specific values from the webhook payload according to Tap's signature algorithm
     *
     * @param  array  $data  The webhook payload
     * @return string The concatenated hash string
     */
    protected function buildHashString(array $data): string
    {
        $fieldKeys = ['id', 'amount', 'currency', 'status', 'created'];

        $fields = [];
        foreach ($fieldKeys as $key) {
            if (isset($data[$key])) {
                $fields[] = $data[$key];
            }
        }

        return implode('', $fields);
    }

    /**
     * Check if the webhook is within tolerance time (prevents replay attacks)
     *
     * @param  array  $data  The webhook payload
     * @return WebhookValidationResult Validation result with error details if failed
     */
    public function checkTolerance(array $data): WebhookValidationResult
    {
        $tolerance = config('tap.webhook.tolerance', 300); // 5 minutes default

        if (! isset($data['created'])) {
            return WebhookValidationResult::failed('Missing created timestamp');
        }

        $created = (int) $data['created'];
        $now = time();

        if (abs($now - $created) > $tolerance) {
            return WebhookValidationResult::failed(
                'Webhook expired',
                ['created' => $created, 'now' => $now, 'tolerance' => $tolerance]
            );
        }

        return WebhookValidationResult::success();
    }
}
