<?php

declare(strict_types=1);

namespace TapPay\Tap\Exceptions;

use Exception;

class ApiErrorException extends Exception
{
    protected array $errors;
    protected int $statusCode;

    public function __construct(string $message, int $statusCode = 0, array $errors = [])
    {
        parent::__construct($message, $statusCode);
        $this->statusCode = $statusCode;
        $this->errors = $errors;
    }

    /**
     * Get the errors from the API response
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Get the HTTP status code
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Create an exception from an API response
     */
    public static function fromResponse(array $response, int $statusCode): self
    {
        $message = $response['message'] ?? $response['error'] ?? 'Unknown API error';
        $errors = $response['errors'] ?? [];

        return new self($message, $statusCode, $errors);
    }
}
