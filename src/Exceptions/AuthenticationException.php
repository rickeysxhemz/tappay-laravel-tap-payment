<?php

declare(strict_types=1);

namespace TapPay\Tap\Exceptions;

/**
 * Exception thrown when API authentication fails (401 Unauthorized)
 *
 * This typically occurs when:
 * - Invalid or missing API key
 * - Expired API key
 * - API key lacks required permissions
 */
class AuthenticationException extends ApiErrorException
{
    /**
     * Create a new authentication exception
     *
     * @param string $message Custom error message
     */
    public function __construct(string $message = 'Authentication failed. Please check your API keys.')
    {
        parent::__construct($message, 401);
    }
}
