<?php

declare(strict_types=1);

namespace TapPay\Tap\Http\Handlers;

use TapPay\Tap\Resources\Charge;

final readonly class PaymentCallbackResult
{
    private function __construct(
        public bool $success,
        public ?Charge $charge,
        public ?string $error
    ) {}

    public static function success(Charge $charge): self
    {
        return new self(true, $charge, null);
    }

    public static function failed(?Charge $charge, string $error): self
    {
        return new self(false, $charge, $error);
    }
}
