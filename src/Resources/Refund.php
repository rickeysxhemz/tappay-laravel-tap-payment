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
     * Get the refund status
     *
     * @return RefundStatus
     */
    public function status(): RefundStatus
    {
        $status = strtoupper($this->attributes['status'] ?? 'FAILED');
        return RefundStatus::tryFrom($status) ?? RefundStatus::FAILED;
    }

    /**
     * Get the charge ID being refunded
     *
     * @return string
     */
    public function chargeId(): string
    {
        return $this->attributes['charge_id'] ?? '';
    }

    /**
     * Get the refund reason
     *
     * @return string|null
     */
    public function reason(): ?string
    {
        return $this->attributes['reason'] ?? null;
    }

    /**
     * Check if refund was successful
     *
     * @return bool
     */
    public function isSuccessful(): bool
    {
        return $this->status()->isSuccessful();
    }

    /**
     * Check if refund is pending
     *
     * @return bool
     */
    public function isPending(): bool
    {
        return $this->status()->isPending();
    }

    /**
     * Check if refund has failed
     *
     * @return bool
     */
    public function hasFailed(): bool
    {
        return $this->status()->hasFailed();
    }
}
