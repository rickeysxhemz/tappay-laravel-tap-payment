<?php

declare(strict_types=1);

namespace TapPay\Tap\Webhooks;

readonly class WebhookValidationResult
{
    private function __construct(
        public bool $valid,
        public ?string $error = null,
        public array $context = [],
    ) {}

    public static function success(): self
    {
        return new self(valid: true);
    }

    public static function failed(string $error, array $context = []): self
    {
        return new self(valid: false, error: $error, context: $context);
    }

    public function isValid(): bool
    {
        return $this->valid;
    }

    public function getError(): ?string
    {
        return $this->error;
    }

    public function getContext(): array
    {
        return $this->context;
    }
}
