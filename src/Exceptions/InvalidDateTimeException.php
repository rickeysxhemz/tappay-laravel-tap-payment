<?php

declare(strict_types=1);

namespace TapPay\Tap\Exceptions;

use InvalidArgumentException;

/**
 * Exception for invalid datetime values
 */
final class InvalidDateTimeException extends InvalidArgumentException
{
    public static function invalidValue(int|string $value): never
    {
        throw new self(
            sprintf('Unable to parse datetime value: %s', (string) $value)
        );
    }
}
