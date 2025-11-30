<?php

declare(strict_types=1);

namespace TapPay\Tap\Resources;

use TapPay\Tap\Resources\Concerns\HasMoney;

use function is_string;

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
        $merchant = $this->attributes['merchant'] ?? $this->attributes['merchant_id'] ?? null;

        return is_string($merchant) ? $merchant : null;
    }

    /**
     * Get the transfer ID (once settled)
     */
    public function transferId(): ?string
    {
        return $this->getNullableString('transfer');
    }

    /**
     * Get the destination status
     */
    public function status(): ?string
    {
        return $this->getNullableString('status');
    }

    /**
     * Check if destination transfer is pending
     */
    public function isPending(): bool
    {
        return $this->getString('status') === 'PENDING';
    }

    /**
     * Check if destination transfer is complete
     */
    public function isComplete(): bool
    {
        return $this->getString('status') === 'TRANSFERRED';
    }

    /**
     * Get the associated charge ID
     */
    public function chargeId(): ?string
    {
        $charge = $this->attributes['charge'] ?? $this->attributes['charge_id'] ?? null;

        return is_string($charge) ? $charge : null;
    }
}
