<?php

declare(strict_types=1);

namespace TapPay\Tap\Exceptions;

use InvalidArgumentException;

/**
 * Exception for invalid amount values
 */
final class InvalidAmountException extends InvalidArgumentException
{
    public static function missing(): never
    {
        throw new self('Amount is required but not provided or invalid in response');
    }

    public static function notPositive(float|int|string $amount): never
    {
        throw new self(
            sprintf('Amount must be greater than zero, got: %s', (string) $amount)
        );
    }

    public static function negative(): never
    {
        throw new self('Amount cannot be negative');
    }

    public static function invalid(): never
    {
        throw new self('Amount must be a valid numeric value');
    }
}
