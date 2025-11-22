<?php

declare(strict_types=1);

namespace TapPay\Tap\Resources;

class Authorize
{
    protected array $attributes;

    public function __construct(array $attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * Get the authorization ID
     */
    public function id(): string
    {
        return $this->attributes['id'] ?? '';
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
    public function status(): string
    {
        return $this->attributes['status'] ?? '';
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
     * Get metadata
     */
    public function metadata(): array
    {
        return $this->attributes['metadata'] ?? [];
    }

    /**
     * Check if authorization was successful
     */
    public function isAuthorized(): bool
    {
        return $this->status() === 'AUTHORIZED';
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
