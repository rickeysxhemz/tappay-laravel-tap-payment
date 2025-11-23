<?php

declare(strict_types=1);

namespace TapPay\Tap\Resources;

use TapPay\Tap\Enums\AuthorizeStatus;

class Authorize extends Resource
{

    /**
     * Get the authorization ID
     *
     * @return string
     */
    public function id(): string
    {
        return $this->attributes['id'] ?? '';
    }

    /**
     * Get the authorization amount
     *
     * @return float
     */
    public function amount(): float
    {
        return (float) ($this->attributes['amount'] ?? 0);
    }

    /**
     * Get the currency
     *
     * @return string
     */
    public function currency(): string
    {
        return $this->attributes['currency'] ?? '';
    }

    /**
     * Get the authorization status
     *
     * @return AuthorizeStatus
     */
    public function status(): AuthorizeStatus
    {
        $status = $this->attributes['status'] ?? 'UNKNOWN';
        return AuthorizeStatus::tryFrom($status) ?? AuthorizeStatus::UNKNOWN;
    }

    /**
     * Get the transaction URL for redirect
     *
     * @return string|null
     */
    public function transactionUrl(): ?string
    {
        return $this->attributes['transaction']['url'] ?? null;
    }

    /**
     * Get the customer ID
     *
     * @return string|null
     */
    public function customerId(): ?string
    {
        return $this->attributes['customer']['id'] ?? null;
    }

    /**
     * Get the source ID
     *
     * @return string|null
     */
    public function sourceId(): ?string
    {
        return $this->attributes['source']['id'] ?? null;
    }

    /**
     * Get metadata
     *
     * @return array
     */
    public function metadata(): array
    {
        return $this->attributes['metadata'] ?? [];
    }

    /**
     * Check if authorization was successful
     *
     * @return bool
     */
    public function isAuthorized(): bool
    {
        return $this->status()->isSuccessful();
    }

    /**
     * Check if authorization is pending
     *
     * @return bool
     */
    public function isPending(): bool
    {
        return $this->status()->isPending();
    }

    /**
     * Check if authorization has failed
     *
     * @return bool
     */
    public function hasFailed(): bool
    {
        return $this->status()->hasFailed();
    }
}
