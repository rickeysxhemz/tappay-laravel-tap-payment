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
     * @param  string  $message  Custom error message
     * @param  int  $statusCode  HTTP status code (400 or 422)
     * @param  array  $errors  Validation errors from API
     */
    public function __construct(string $message = 'Invalid request parameters.', int $statusCode = 400, array $errors = [])
    {
        parent::__construct($message, $statusCode, $errors);
    }
}
