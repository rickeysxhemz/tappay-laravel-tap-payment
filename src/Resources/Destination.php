<?php

declare(strict_types=1);

namespace TapPay\Tap\Resources;

use TapPay\Tap\Resources\Concerns\HasMoney;

/**
 * Destination resource for payment splits
 */
class Destination extends Resource
{
    use HasMoney;

    protected function getIdPrefix(): string
    {
        return 'dest_';
    }

    /**
     * Get the destination merchant ID
     */
    public function merchantId(): ?string
    {
        return $this->attributes['merchant'] ?? $this->attributes['merchant_id'] ?? null;
    }

    /**
     * Get the transfer ID (once settled)
     */
    public function transferId(): ?string
    {
        return $this->attributes['transfer'] ?? null;
    }

    /**
     * Get the destination status
     */
    public function status(): ?string
    {
        return $this->attributes['status'] ?? null;
    }

    /**
     * Check if destination transfer is pending
     */
    public function isPending(): bool
    {
        return ($this->attributes['status'] ?? '') === 'PENDING';
    }

    /**
     * Check if destination transfer is complete
     */
    public function isComplete(): bool
    {
        return ($this->attributes['status'] ?? '') === 'TRANSFERRED';
    }

    /**
     * Get the associated charge ID
     */
    public function chargeId(): ?string
    {
        return $this->attributes['charge'] ?? $this->attributes['charge_id'] ?? null;
    }
}
