<?php

declare(strict_types=1);

namespace TapPay\Tap\Webhooks;

use Illuminate\Http\Request;
use RuntimeException;

class WebhookValidator
{
    protected string $secret;

    /**
     * Create a new webhook validator instance
     *
     * @param  string|null  $secretKey  Webhook secret key (uses config if not provided)
     *
     * @throws RuntimeException If secret key is not configured
     */
    public function __construct(?string $secretKey = null)
    {
        $secret = $secretKey
            ?? config('tap.webhook.secret')
            ?? config('tap.secret');

        if (! is_string($secret) || $secret === '') {
            throw new RuntimeException('Webhook secret key is not configured. Please set tap.webhook.secret or tap.secret in config.');
        }

        $this->secret = $secret;
    }

    /**
     * Validate the webhook signature using HMAC-SHA256
     *
     * @param  Request  $request  The incoming webhook request
     * @return WebhookValidationResult Validation result with error details if failed
     */
    public function validate(Request $request): WebhookValidationResult
    {
        $signature = $request->header('hashstring');

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

        /** @var array<string, mixed> $typedData */
        $typedData = $data;

        $missingFields = $this->getMissingRequiredFields($typedData);
        if (! empty($missingFields)) {
            return WebhookValidationResult::failed(
                'Missing required webhook fields: ' . implode(', ', $missingFields),
                ['missing_fields' => $missingFields]
            );
        }

        $hashString = $this->buildHashString($typedData);
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
     * @param  array<string, mixed>  $payload  The decoded webhook payload
     * @param  string  $signature  The signature from hashstring header
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

        $missingFields = $this->getMissingRequiredFields($payload);
        if (! empty($missingFields)) {
            return WebhookValidationResult::failed(
                'Missing required webhook fields: ' . implode(', ', $missingFields),
                ['missing_fields' => $missingFields]
            );
        }

        $hashString = $this->buildHashString($payload);
        $computedSignature = hash_hmac('sha256', $hashString, $this->secret);

        if (! hash_equals($computedSignature, $signature)) {
            return WebhookValidationResult::failed('Signature mismatch');
        }

        return WebhookValidationResult::success();
    }

    /**
     * Get list of missing required fields from payload
     *
     * @param  array<string, mixed>  $data  The webhook payload
     * @return array<int, string> List of missing field names
     */
    private function getMissingRequiredFields(array $data): array
    {
        $requiredFields = ['id', 'amount', 'currency', 'status', 'created'];
        $missing = [];

        foreach ($requiredFields as $field) {
            if (! isset($data[$field])) {
                $missing[] = $field;
            }
        }

        return $missing;
    }

    /**
     * Build the hash string from webhook payload according to Tap's signature algorithm
     *
     * @param  array<string, mixed>  $data  The webhook payload
     * @return string The concatenated hash string with field prefixes
     */
    protected function buildHashString(array $data): string
    {
        $id = $this->getScalarValue($data, 'id');
        $amount = $this->getScalarValue($data, 'amount');
        $currency = $this->getScalarValue($data, 'currency');
        $gatewayRef = $data['gateway']['reference'] ?? $data['reference']['gateway'] ?? '';
        $paymentRef = $data['reference']['payment'] ?? '';
        $status = $this->getScalarValue($data, 'status');
        $created = $this->getScalarValue($data, 'created');

        return 'x_id' . $id
             . 'x_amount' . $amount
             . 'x_currency' . $currency
             . 'x_gateway_reference' . (is_scalar($gatewayRef) ? $gatewayRef : '')
             . 'x_payment_reference' . (is_scalar($paymentRef) ? $paymentRef : '')
             . 'x_status' . $status
             . 'x_created' . $created;
    }

    private function getScalarValue(array $data, string $key): string
    {
        $value = $data[$key] ?? '';

        return is_scalar($value) ? (string) $value : '';
    }

    /**
     * Check if the webhook is within tolerance time (prevents replay attacks)
     *
     * @param  array<string, mixed>  $data  The webhook payload
     * @return WebhookValidationResult Validation result with error details if failed
     */
    public function checkTolerance(array $data): WebhookValidationResult
    {
        $configTolerance = config('tap.webhook.tolerance', 300);
        $tolerance = is_numeric($configTolerance) ? (int) $configTolerance : 300; // 5 minutes default
        $clockSkew = 30; // Allow 30 seconds for clock differences

        if (! isset($data['created'])) {
            return WebhookValidationResult::failed('Missing created timestamp');
        }

        $created = is_numeric($data['created']) ? (int) $data['created'] : 0;
        $now = time();
        $diff = $now - $created;

        // Reject future timestamps (with small allowance for clock skew)
        if ($diff < -$clockSkew) {
            return WebhookValidationResult::failed(
                'Webhook timestamp is in the future',
                ['created' => $created, 'now' => $now, 'diff' => $diff]
            );
        }

        // Reject expired webhooks
        if ($diff > $tolerance) {
            return WebhookValidationResult::failed(
                'Webhook expired',
                ['created' => $created, 'now' => $now, 'tolerance' => $tolerance]
            );
        }

        return WebhookValidationResult::success();
    }
}
