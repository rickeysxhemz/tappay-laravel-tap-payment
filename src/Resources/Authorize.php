<?php

declare(strict_types=1);

namespace TapPay\Tap\Resources;

use TapPay\Tap\Enums\AuthorizeStatus;
use TapPay\Tap\Exceptions\InvalidStatusException;
use TapPay\Tap\Resources\Concerns\HasPaymentDetails;

use function is_string;

class Authorize extends Resource
{
    use HasPaymentDetails;

    protected function getIdPrefix(): string
    {
        return 'auth_';
    }

    /**
     * Get the authorization status
     *
     * @throws InvalidStatusException
     */
    public function status(): AuthorizeStatus
    {
        $status = $this->attributes['status'] ?? null;

        if (! is_string($status)) {
            return AuthorizeStatus::UNKNOWN;
        }

        return AuthorizeStatus::tryFrom(strtoupper($status))
            ?? InvalidStatusException::unknown($status, 'authorize');
    }

    /**
     * Check if authorization was successful (alias for isSuccessful)
     */
    public function isAuthorized(): bool
    {
        return $this->isSuccessful();
    }
}
