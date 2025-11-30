<?php

declare(strict_types=1);

namespace TapPay\Tap\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Throwable;

class PaymentRetrievalFailed
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public const ERROR_TYPE_AUTHENTICATION = 'authentication';

    public const ERROR_TYPE_INVALID_REQUEST = 'invalid_request';

    public const ERROR_TYPE_API_ERROR = 'api_error';

    /**
     * Create a new event instance
     *
     * @param  string  $chargeId  The charge ID that failed to retrieve
     * @param  string  $errorType  The type of error (authentication, invalid_request, api_error)
     * @param  string  $errorMessage  The error message
     * @param  Throwable|null  $exception  The original exception
     * @param  string|null  $redirectUrl  The redirect URL if provided
     */
    public function __construct(
        public string $chargeId,
        public string $errorType,
        public string $errorMessage,
        public ?Throwable $exception = null,
        public ?string $redirectUrl = null
    ) {}

    /**
     * Check if this is an authentication error
     */
    public function isAuthenticationError(): bool
    {
        return $this->errorType === self::ERROR_TYPE_AUTHENTICATION;
    }

    /**
     * Check if this is an invalid request error
     */
    public function isInvalidRequestError(): bool
    {
        return $this->errorType === self::ERROR_TYPE_INVALID_REQUEST;
    }

    /**
     * Check if this is an API error
     */
    public function isApiError(): bool
    {
        return $this->errorType === self::ERROR_TYPE_API_ERROR;
    }

    /**
     * Check if the error is a configuration issue (authentication)
     */
    public function isConfigurationIssue(): bool
    {
        return $this->isAuthenticationError();
    }

    /**
     * Check if the error is an infrastructure issue (API down, network error)
     */
    public function isInfrastructureIssue(): bool
    {
        return $this->isApiError();
    }
}