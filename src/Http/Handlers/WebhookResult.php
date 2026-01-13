<?php

declare(strict_types=1);

namespace TapPay\Tap\Http\Handlers;

final readonly class WebhookResult
{
    private function __construct(
        public bool $success,
        public string $message,
        public string $code,
    ) {}

    public static function success(): self
    {
        return new self(true, 'Webhook received', 'success');
    }

    public static function failure(string $message, string $code): self
    {
        return new self(false, $message, $code);
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }
}
