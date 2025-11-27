<?php

declare(strict_types=1);

namespace TapPay\Tap\Resources;

use TapPay\Tap\Enums\RefundStatus;

class Refund extends Resource
{
    /**
     * Get the refund ID
     *
     * @return string
     */
    public function id(): string
    {
        return $this->attributes['id'] ?? '';
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
        $status = $this->attributes['status'] ?? 'FAILED';
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
     * Get metadata
     *
     * @return array
     */
    public function metadata(): array
    {
        return $this->attributes['metadata'] ?? [];
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

    /**
     * Check if refund ID has valid format
     *
     * @return bool
     */
    public function hasValidId(): bool
    {
        $id = $this->id();

        return $id !== '' && str_starts_with($id, 'ref_');
    }
}
