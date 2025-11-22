<?php

declare(strict_types=1);

namespace TapPay\Tap\Exceptions;

class InvalidRequestException extends ApiErrorException
{
    public function __construct(string $message = 'Invalid request parameters.', array $errors = [])
    {
        parent::__construct($message, 400, $errors);
    }
}
