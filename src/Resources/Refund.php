<?php

declare(strict_types=1);

namespace TapPay\Tap\Resources;

use TapPay\Tap\Enums\RefundStatus;

class Refund
{
    protected array $attributes;

    public function __construct(array $attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * Get the refund ID
     */
    public function id(): string
    {
        return $this->attributes['id'] ?? '';
    }

    /**
     * Get the refund amount
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
     * Get the refund status
     */
    public function status(): RefundStatus
    {
        $status = $this->attributes['status'] ?? 'FAILED';
        return RefundStatus::tryFrom($status) ?? RefundStatus::FAILED;
    }

    /**
     * Get the charge ID being refunded
     */
    public function chargeId(): string
    {
        return $this->attributes['charge_id'] ?? '';
    }

    /**
     * Get the refund reason
     */
    public function reason(): ?string
    {
        return $this->attributes['reason'] ?? null;
    }

    /**
     * Get metadata
     */
    public function metadata(): array
    {
        return $this->attributes['metadata'] ?? [];
    }

    /**
     * Check if refund was successful
     */
    public function isSuccessful(): bool
    {
        return $this->status()->isSuccessful();
    }

    /**
     * Check if refund is pending
     */
    public function isPending(): bool
    {
        return $this->status()->isPending();
    }

    /**
     * Check if refund has failed
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
