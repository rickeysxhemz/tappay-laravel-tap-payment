<?php

declare(strict_types=1);

namespace TapPay\Tap\Exceptions;

use Exception;

/**
 * Exception for Tap API error responses
 */
class ApiErrorException extends Exception
{
    public function __construct(
        string $message,
        protected int $statusCode = 0,
        protected array $errors = []
    ) {
        parent::__construct($message, $statusCode);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function hasErrors(): bool
    {
        return ! empty($this->errors);
    }

    public function getFirstError(): ?string
    {
        if (empty($this->errors)) {
            return null;
        }

        $firstKey = array_key_first($this->errors);
        $firstError = $this->errors[$firstKey];

        if (is_array($firstError)) {
            $first = $firstError[0] ?? null;

            return is_string($first) ? $first : null;
        }

        return is_string($firstError) ? $firstError : null;
    }

    public function toArray(): array
    {
        return [
            'message' => $this->getMessage(),
            'status_code' => $this->statusCode,
            'errors' => $this->errors,
        ];
    }
}
