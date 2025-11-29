<?php

declare(strict_types=1);

namespace TapPay\Tap\Exceptions;

use InvalidArgumentException;

/**
 * Exception for invalid currency values
 */
final class InvalidCurrencyException extends InvalidArgumentException
{
    public static function missing(): never
    {
        throw new self('Currency is required but not provided in response or config');
    }

    public static function empty(): never
    {
        throw new self('Currency cannot be empty');
    }

    public static function unsupported(string $currency, array $supported): never
    {
        throw new self(
            sprintf(
                "Currency '%s' is not supported. Supported currencies: %s",
                $currency,
                implode(', ', $supported)
            )
        );
    }
}
