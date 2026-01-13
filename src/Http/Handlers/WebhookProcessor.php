<?php

declare(strict_types=1);

namespace TapPay\Tap\Http\Handlers;

use TapPay\Tap\Events\WebhookValidationFailed;
use TapPay\Tap\Webhooks\WebhookValidator;

use function is_array;
use function json_decode;
use function json_last_error;

use const JSON_ERROR_NONE;

final class WebhookProcessor
{
    /** @var list<string> */
    private const REQUIRED_FIELDS = ['id', 'object', 'amount', 'currency', 'status'];

    public function __construct(
        private readonly WebhookValidator $validator,
        private readonly WebhookHandler   $handler,
    ) {}

    public function process(string $content, string $signature, string $ip): WebhookResult
    {
        // 1. Parse JSON
        $payload = $this->parseJson($content);
        if ($payload === null) {
            return $this->fail('Invalid JSON payload', 'invalid_payload', $ip);
        }

        // 2. Validate structure
        if (! $this->hasRequiredFields($payload)) {
            return $this->fail('Missing required fields', 'invalid_payload', $ip);
        }

        // 3. Validate signature
        $signatureResult = $this->validator->validatePayload($payload, $signature);
        if (! $signatureResult->isValid()) {
            return $this->fail($signatureResult->getError() ?? 'Invalid signature', 'invalid_signature', $ip);
        }

        // 4. Check tolerance
        $toleranceResult = $this->validator->checkTolerance($payload);
        if (! $toleranceResult->isValid()) {
            return $this->fail($toleranceResult->getError() ?? 'Webhook expired', 'expired', $ip);
        }

        // 5. Dispatch events
        $this->handler->handle($payload, $ip);

        return WebhookResult::success();
    }

    /**
     * @return array<string, mixed>|null
     */
    private function parseJson(string $content): ?array
    {
        if ($content === '') {
            return null;
        }

        $decoded = json_decode($content, true, 64);

        if (json_last_error() !== JSON_ERROR_NONE || ! is_array($decoded)) {
            return null;
        }

        return $decoded;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function hasRequiredFields(array $payload): bool
    {
        foreach (self::REQUIRED_FIELDS as $field) {
            if (! isset($payload[$field])) {
                return false;
            }
        }

        return true;
    }

    private function fail(string $error, string $code, string $ip): WebhookResult
    {
        WebhookValidationFailed::dispatch($error, $ip, []);

        return WebhookResult::failure($error, $code);
    }
}
