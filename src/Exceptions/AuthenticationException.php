<?php

declare(strict_types=1);

namespace TapPay\Tap\Exceptions;

/**
 * Exception for API authentication failures (401)
 */
class AuthenticationException extends ApiErrorException
{
    public function __construct(string $message = 'Authentication failed. Please check your API keys.')
    {
        parent::__construct($message, 401);
    }
}
