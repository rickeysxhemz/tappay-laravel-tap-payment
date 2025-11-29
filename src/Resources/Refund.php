<?php

declare(strict_types=1);

namespace TapPay\Tap\Resources;

use TapPay\Tap\Enums\RefundStatus;

class Refund extends Resource
{
    protected function getIdPrefix(): string
    {
        return 'ref_';
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
        $status = strtoupper($this->attributes['status'] ?? 'FAILED');

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
}
