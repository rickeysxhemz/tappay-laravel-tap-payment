<?php

declare(strict_types=1);

namespace TapPay\Tap\Resources;

use TapPay\Tap\Enums\ChargeStatus;

class Charge extends Resource
{
    /**
     * Get the charge ID
     *
     * @return string
     */
    public function id(): string
    {
        return $this->attributes['id'] ?? '';
    }

    /**
     * Get the charge amount
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
     * Get the charge status
     *
     * @return ChargeStatus
     */
    public function status(): ChargeStatus
    {
        $status = $this->attributes['status'] ?? 'UNKNOWN';
        return ChargeStatus::tryFrom($status) ?? ChargeStatus::UNKNOWN;
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
     * Get the description
     *
     * @return string|null
     */
    public function description(): ?string
    {
        return $this->attributes['description'] ?? null;
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
     * Get saved card ID if card was saved
     *
     * @return string|null
     */
    public function cardId(): ?string
    {
        return $this->get('card.id');
    }

    /**
     * Check if charge was successful
     *
     * @return bool
     */
    public function isSuccessful(): bool
    {
        return $this->status()->isSuccessful();
    }

    /**
     * Check if charge is pending
     *
     * @return bool
     */
    public function isPending(): bool
    {
        return $this->status()->isPending();
    }

    /**
     * Check if charge has failed
     *
     * @return bool
     */
    public function hasFailed(): bool
    {
        return $this->status()->hasFailed();
    }

    /**
     * Check if charge ID has valid format
     *
     * @return bool
     */
    public function hasValidId(): bool
    {
        $id = $this->id();

        return $id !== '' && str_starts_with($id, 'chg_');
    }
}
