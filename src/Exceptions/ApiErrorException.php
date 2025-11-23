<?php

declare(strict_types=1);

namespace TapPay\Tap\Exceptions;

use Exception;

/**
 * Exception thrown when the Tap API returns an error response
 */
class ApiErrorException extends Exception
{
    /**
     * @param string $message Error message from API
     * @param int $statusCode HTTP status code
     * @param array $errors Validation errors from API
     */
    public function __construct(
        string $message,
        protected int $statusCode = 0,
        protected array $errors = []
    ) {
        parent::__construct($message, $statusCode);
    }

    /**
     * Get the errors from the API response
     *
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Get the HTTP status code
     *
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Check if the exception has validation errors
     *
     * @return bool
     */
    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    /**
     * Get the first error message
     *
     * @return string|null
     */
    public function getFirstError(): ?string
    {
        if (empty($this->errors)) {
            return null;
        }

        $firstKey = array_key_first($this->errors);
        $firstError = $this->errors[$firstKey];

        return is_array($firstError) ? ($firstError[0] ?? null) : $firstError;
    }

    /**
     * Get exception data as array
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'message' => $this->getMessage(),
            'status_code' => $this->statusCode,
            'errors' => $this->errors,
        ];
    }

    /**
     * Create an exception from an API response
     *
     * @param array $response API response data
     * @param int $statusCode HTTP status code
     * @return self
     */
    public static function fromResponse(array $response, int $statusCode): self
    {
        $message = $response['message'] ?? $response['error'] ?? 'Unknown API error';
        $errors = $response['errors'] ?? [];

        return new self($message, $statusCode, $errors);
    }
}
