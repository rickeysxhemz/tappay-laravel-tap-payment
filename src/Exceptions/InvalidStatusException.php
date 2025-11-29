<?php

declare(strict_types=1);

namespace TapPay\Tap\Exceptions;

use InvalidArgumentException;

/**
 * Exception for unknown status values
 */
final class InvalidStatusException extends InvalidArgumentException
{
    public static function unknown(string $status, string $resource): never
    {
        throw new self(
            sprintf("Unknown %s status: '%s'", $resource, $status)
        );
    }
}
