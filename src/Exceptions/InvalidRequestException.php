<?php

declare(strict_types=1);

namespace TapPay\Tap\Exceptions;

/**
 * Exception for invalid request parameters (400/422)
 */
class InvalidRequestException extends ApiErrorException
{
    public function __construct(string $message = 'Invalid request parameters.', int $statusCode = 400, array $errors = [])
    {
        parent::__construct($message, $statusCode, $errors);
    }
}
