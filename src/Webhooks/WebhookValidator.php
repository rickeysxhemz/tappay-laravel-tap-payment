<?php

declare(strict_types=1);

namespace TapPay\Tap\Webhooks;

use Illuminate\Http\Request;
use TapPay\Tap\Contracts\MoneyContract;
use Throwable;

use function in_array;

readonly class WebhookValidator
{
    public function __construct(
        protected string $secret,
        protected MoneyContract $money,
        protected int $tolerance = 300
    ) {}

    public function validate(Request $request): WebhookValidationResult
    {
        $signature = $request->header('hashstring');

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

    /** @param array<string, mixed> $payload */
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

    /** @param array<string, mixed> $data @return array<int, string> */
    private function getMissingRequiredFields(array $data): array
    {
        $rootFields = ['id', 'amount', 'currency', 'status'];
        $missing = [];

        foreach ($rootFields as $field) {
            if (! isset($data[$field])) {
                $missing[] = $field;
            }
        }

        $type = $this->getWebhookType($data);

        if ($this->usesTransactionCreated($type)) {
            $transaction = $data['transaction'] ?? null;
            if (! is_array($transaction) || ! isset($transaction['created'])) {
                $missing[] = 'created (transaction.created)';
            }
        } else {
            if (! isset($data['created'])) {
                $missing[] = 'created';
            }
        }

        return $missing;
    }

    /** @param array<string, mixed> $data */
    protected function buildHashString(array $data): string
    {
        $id = $this->getScalarValue($data, 'id');
        $currency = $this->getScalarValue($data, 'currency');
        $status = $this->getScalarValue($data, 'status');

        $rawAmount = $this->getScalarValue($data, 'amount');
        $amount = $this->formatAmountForHash($rawAmount, $currency);

        $createdValue = $this->extractCreatedTimestamp($data);
        $created = is_scalar($createdValue) ? (string) $createdValue : '';

        $type = $this->getWebhookType($data);

        if ($type === 'invoice') {
            $updated = $this->getScalarValue($data, 'updated');

            return 'x_id' . $id
                 . 'x_amount' . $amount
                 . 'x_currency' . $currency
                 . 'x_updated' . $updated
                 . 'x_status' . $status
                 . 'x_created' . $created;
        }

        $gateway = $data['gateway'] ?? null;
        $reference = $data['reference'] ?? null;
        $gatewayRef = (is_array($gateway) ? ($gateway['reference'] ?? '') : '')
                    ?: (is_array($reference) ? ($reference['gateway'] ?? '') : '');
        $paymentRef = is_array($reference) ? ($reference['payment'] ?? '') : '';

        return 'x_id' . $id
             . 'x_amount' . $amount
             . 'x_currency' . $currency
             . 'x_gateway_reference' . (is_scalar($gatewayRef) ? $gatewayRef : '')
             . 'x_payment_reference' . (is_scalar($paymentRef) ? $paymentRef : '')
             . 'x_status' . $status
             . 'x_created' . $created;
    }

    protected function formatAmountForHash(string $amount, string $currency): string
    {
        if ($amount === '') {
            return '';
        }

        try {
            return $this->money->formatAmount($amount, $currency);
        } catch (Throwable) {
            return number_format((float) $amount, 2, '.', '');
        }
    }

    private function getScalarValue(array $data, string $key): string
    {
        $value = $data[$key] ?? '';

        return is_scalar($value) ? (string) $value : '';
    }

    /** @param array<string, mixed> $data */
    protected function getWebhookType(array $data): string
    {
        $object = $data['object'] ?? '';

        return is_string($object) ? strtolower($object) : 'unknown';
    }

    protected function usesTransactionCreated(string $type): bool
    {
        return in_array($type, ['charge', 'authorize'], true);
    }

    /** @param array<string, mixed> $data */
    protected function extractCreatedTimestamp(array $data): string|int|null
    {
        $type = $this->getWebhookType($data);

        if ($this->usesTransactionCreated($type)) {
            $transaction = $data['transaction'] ?? null;
            if (! is_array($transaction)) {
                return null;
            }
            $created = $transaction['created'] ?? null;

            return is_string($created) || is_int($created) ? $created : null;
        }

        $created = $data['created'] ?? null;

        return is_string($created) || is_int($created) ? $created : null;
    }

    protected function normalizeTimestamp(string|int $timestamp): int
    {
        $ts = is_numeric($timestamp) ? (int) $timestamp : 0;

        if ($ts > 9999999999) {
            return (int) ($ts / 1000);
        }

        return $ts;
    }

    /** @param array<string, mixed> $data */
    public function checkTolerance(array $data): WebhookValidationResult
    {
        $clockSkew = 30;

        $createdValue = $this->extractCreatedTimestamp($data);

        if ($createdValue === null) {
            return WebhookValidationResult::failed('Missing created timestamp');
        }

        $created = $this->normalizeTimestamp($createdValue);

        if ($created === 0) {
            return WebhookValidationResult::failed('Invalid created timestamp');
        }

        $now = time();
        $diff = $now - $created;

        if ($diff < -$clockSkew) {
            return WebhookValidationResult::failed(
                'Webhook timestamp is in the future',
                ['created' => $created, 'now' => $now, 'diff' => $diff]
            );
        }

        if ($diff > $this->tolerance) {
            return WebhookValidationResult::failed(
                'Webhook expired',
                ['created' => $created, 'now' => $now, 'tolerance' => $this->tolerance]
            );
        }

        return WebhookValidationResult::success();
    }
}
