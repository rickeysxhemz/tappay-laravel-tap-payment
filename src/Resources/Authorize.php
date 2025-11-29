<?php

declare(strict_types=1);

namespace TapPay\Tap\Resources;

use TapPay\Tap\Enums\AuthorizeStatus;

class Authorize extends Resource
{
    protected function getIdPrefix(): string
    {
        return 'auth_';
    }

    /**
     * Get the authorization amount
     */
    public function amount(): float
    {
        return (float) ($this->attributes['amount'] ?? 0);
    }

    /**
     * Get the currency
     */
    public function currency(): string
    {
        return $this->attributes['currency'] ?? '';
    }

    /**
     * Get the authorization status
     */
    public function status(): AuthorizeStatus
    {
        $status = strtoupper($this->attributes['status'] ?? 'UNKNOWN');

        return AuthorizeStatus::tryFrom($status) ?? AuthorizeStatus::UNKNOWN;
    }

    /**
     * Get the transaction URL for redirect
     */
    public function transactionUrl(): ?string
    {
        return $this->get('transaction.url');
    }

    /**
     * Get the customer ID
     */
    public function customerId(): ?string
    {
        return $this->get('customer.id');
    }

    /**
     * Get the source ID
     */
    public function sourceId(): ?string
    {
        return $this->get('source.id');
    }

    /**
     * Check if authorization was successful
     */
    public function isAuthorized(): bool
    {
        return $this->status()->isSuccessful();
    }

    /**
     * Check if authorization is pending
     */
    public function isPending(): bool
    {
        return $this->status()->isPending();
    }

    /**
     * Check if authorization has failed
     */
    public function hasFailed(): bool
    {
        return $this->status()->hasFailed();
    }
}
