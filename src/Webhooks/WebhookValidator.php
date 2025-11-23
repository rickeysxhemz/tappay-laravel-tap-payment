<?php

declare(strict_types=1);

namespace TapPay\Tap\Webhooks;

use Illuminate\Http\Request;
use TapPay\Tap\Events\WebhookValidationFailed;

class WebhookValidator
{
    /**
     * Create a new webhook validator instance
     *
     * @param string|null $secret Webhook secret key (uses config if not provided)
     * @throws \RuntimeException If secret key is not configured
     */
    public function __construct(
        protected ?string $secret = null
    ) {
        $this->secret = $secret ?? config('tap.webhook_secret') ?? config('tap.secret_key');

        if (empty($this->secret)) {
            throw new \RuntimeException('Webhook secret key is not configured. Please set tap.webhook_secret or tap.secret_key in config.');
        }
    }

    /**
     * Validate the webhook signature using HMAC-SHA256
     *
     * @param Request $request The incoming webhook request
     * @return bool True if signature is valid, false otherwise
     */
    public function validate(Request $request): bool
    {
        $signature = $request->header('x-tap-signature');

        // Validate signature exists and has correct length (SHA256 = 64 hex chars)
        if (!$signature || strlen($signature) !== 64) {
            WebhookValidationFailed::dispatch(
                'Missing or invalid signature length',
                $request->ip(),
                [
                    'has_signature' => !empty($signature),
                    'signature_length' => $signature ? strlen($signature) : 0,
                ]
            );
            return false;
        }

        $payload = $request->getContent();

        if (empty($payload)) {
            WebhookValidationFailed::dispatch(
                'Empty payload',
                $request->ip()
            );
            return false;
        }

        $data = json_decode($payload, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            WebhookValidationFailed::dispatch(
                'Invalid JSON',
                $request->ip(),
                ['json_error' => json_last_error_msg()]
            );
            return false;
        }

        if (!is_array($data) || empty($data)) {
            WebhookValidationFailed::dispatch(
                'Invalid payload structure',
                $request->ip(),
                ['is_array' => is_array($data)]
            );
            return false;
        }

        $hashString = $this->buildHashString($data);
        $algorithm = config('tap.webhook_hash_algorithm', 'sha256');
        $computedSignature = hash_hmac($algorithm, $hashString, $this->secret);

        $isValid = hash_equals($computedSignature, $signature);

        if (!$isValid) {
            WebhookValidationFailed::dispatch(
                'Signature mismatch',
                $request->ip(),
                [
                    'expected_length' => strlen($computedSignature),
                    'received_length' => strlen($signature),
                ]
            );
        }

        return $isValid;
    }

    /**
     * Validate webhook payload directly (without Request object)
     *
     * @param array $payload The decoded webhook payload
     * @param string $signature The signature from x-tap-signature header
     * @return bool True if signature is valid, false otherwise
     */
    public function validatePayload(array $payload, string $signature): bool
    {
        if (empty($signature) || strlen($signature) !== 64) {
            return false;
        }

        if (empty($payload)) {
            return false;
        }

        $hashString = $this->buildHashString($payload);
        $algorithm = config('tap.webhook_hash_algorithm', 'sha256');
        $computedSignature = hash_hmac($algorithm, $hashString, $this->secret);

        return hash_equals($computedSignature, $signature);
    }

    /**
     * Build the hash string from webhook payload
     * Concatenates specific values from the webhook payload according to Tap's signature algorithm
     *
     * @param array $data The webhook payload
     * @return string The concatenated hash string
     */
    protected function buildHashString(array $data): string
    {
        $fieldKeys = config('tap.webhook_signature_fields', [
            'id', 'amount', 'currency', 'status', 'created'
        ]);

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
     * @param array $data The webhook payload
     * @return bool True if within tolerance, false if expired
     */
    public function isWithinTolerance(array $data): bool
    {
        $tolerance = config('tap.webhook_tolerance', 300); // 5 minutes default

        if (!isset($data['created'])) {
            return true; // If no timestamp, skip tolerance check
        }

        $created = (int) $data['created'];
        $now = time();

        return abs($now - $created) <= $tolerance;
    }
}
