<?php

declare(strict_types=1);

namespace TapPay\Tap\Exceptions;

use InvalidArgumentException;

/**
 * Exception for invalid card data
 */
final class InvalidCardException extends InvalidArgumentException
{
    public static function invalidExpiry(int $year, int $month): never
    {
        throw new self(
            sprintf('Invalid card expiry: year=%d, month=%d', $year, $month)
        );
    }
}
