<?php

declare(strict_types=1);

namespace TapPay\Tap\Resources;

use TapPay\Tap\Enums\ChargeStatus;

class Charge extends Resource
{
    protected function getIdPrefix(): string
    {
        return 'chg_';
    }

    /**
     * Get the charge amount
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
     * Get the charge status
     */
    public function status(): ChargeStatus
    {
        $status = strtoupper($this->attributes['status'] ?? 'UNKNOWN');

        return ChargeStatus::tryFrom($status) ?? ChargeStatus::UNKNOWN;
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
     * Get the description
     */
    public function description(): ?string
    {
        return $this->attributes['description'] ?? null;
    }

    /**
     * Get saved card ID if card was saved
     */
    public function cardId(): ?string
    {
        return $this->get('card.id');
    }

    /**
     * Check if charge was successful
     */
    public function isSuccessful(): bool
    {
        return $this->status()->isSuccessful();
    }

    /**
     * Check if charge is pending
     */
    public function isPending(): bool
    {
        return $this->status()->isPending();
    }

    /**
     * Check if charge has failed
     */
    public function hasFailed(): bool
    {
        return $this->status()->hasFailed();
    }
}
