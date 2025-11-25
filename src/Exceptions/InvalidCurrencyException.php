<?php

declare(strict_types=1);

namespace TapPay\Tap\Exceptions;

use InvalidArgumentException;

final class InvalidCurrencyException extends InvalidArgumentException
{
    public static function empty(): self
    {
        return new self('Currency cannot be empty');
    }

    public static function unsupported(string $currency, array $supported): self
    {
        return new self(
            sprintf(
                "Currency '%s' is not supported. Supported currencies: %s",
                $currency,
                implode(', ', $supported)
            )
        );
    }

    public static function negativeAmount(): self
    {
        return new self('Amount cannot be negative');
    }

    public static function invalidAmount(): self
    {
        return new self('Amount must be a valid numeric value');
    }
}