<?php

declare(strict_types=1);

namespace TapPay\Tap\Exceptions;

/**
 * Exception thrown when request parameters are invalid (400 Bad Request)
 *
 * This typically occurs when:
 * - Missing required parameters
 * - Invalid parameter format or type
 * - Parameter values out of acceptable range
 * - Validation failures
 */
class InvalidRequestException extends ApiErrorException
{
    /**
     * Create a new invalid request exception
     *
     * @param string $message Custom error message
     * @param array $errors Validation errors from API
     */
    public function __construct(string $message = 'Invalid request parameters.', array $errors = [])
    {
        parent::__construct($message, 400, $errors);
    }
}
