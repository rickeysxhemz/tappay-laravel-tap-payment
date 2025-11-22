<?php

declare(strict_types=1);

namespace TapPay\Tap\Resources;

use TapPay\Tap\Enums\ChargeStatus;

class Charge
{
    protected array $attributes;

    public function __construct(array $attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * Get the charge ID
     */
    public function id(): string
    {
        return $this->attributes['id'] ?? '';
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
        $status = $this->attributes['status'] ?? 'UNKNOWN';
        return ChargeStatus::tryFrom($status) ?? ChargeStatus::UNKNOWN;
    }

    /**
     * Get the transaction URL for redirect
     */
    public function transactionUrl(): ?string
    {
        return $this->attributes['transaction']['url'] ?? null;
    }

    /**
     * Get the customer ID
     */
    public function customerId(): ?string
    {
        return $this->attributes['customer']['id'] ?? null;
    }

    /**
     * Get the source ID
     */
    public function sourceId(): ?string
    {
        return $this->attributes['source']['id'] ?? null;
    }

    /**
     * Get the description
     */
    public function description(): ?string
    {
        return $this->attributes['description'] ?? null;
    }

    /**
     * Get metadata
     */
    public function metadata(): array
    {
        return $this->attributes['metadata'] ?? [];
    }

    /**
     * Get saved card ID if card was saved
     */
    public function cardId(): ?string
    {
        return $this->attributes['card']['id'] ?? null;
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

    /**
     * Get all attributes
     */
    public function toArray(): array
    {
        return $this->attributes;
    }

    /**
     * Get an attribute by key
     */
    public function get(string $key, $default = null)
    {
        return data_get($this->attributes, $key, $default);
    }

    /**
     * Magic getter for attributes
     */
    public function __get(string $key)
    {
        return $this->get($key);
    }
}
