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
        return $this->get('transaction.url');
    }

    /**
     * Get the customer ID
     *
     * @return string|null
     */
    public function customerId(): ?string
    {
        return $this->get('customer.id');
    }

    /**
     * Get the source ID
     *
     * @return string|null
     */
    public function sourceId(): ?string
    {
        return $this->get('source.id');
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

    /**
     * Check if authorization ID has valid format
     *
     * @return bool
     */
    public function hasValidId(): bool
    {
        $id = $this->id();

        return $id !== '' && str_starts_with($id, 'auth_');
    }
}
