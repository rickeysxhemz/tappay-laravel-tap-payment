<?php

declare(strict_types=1);

namespace TapPay\Tap\Resources;

use TapPay\Tap\Resources\Concerns\HasMoney;
use TapPay\Tap\ValueObjects\Money;

/**
 * Payout resource for merchant settlements
 */
class Payout extends Resource
{
    use HasMoney;

    protected function getIdPrefix(): string
    {
        return 'payout_';
    }

    /**
     * Get the merchant ID receiving the payout
     */
    public function merchantId(): ?string
    {
        return $this->attributes['merchant'] ?? $this->attributes['merchant_id'] ?? null;
    }

    /**
     * Get the payout status
     */
    public function status(): ?string
    {
        return $this->attributes['status'] ?? null;
    }

    /**
     * Check if payout is pending
     */
    public function isPending(): bool
    {
        return in_array($this->attributes['status'] ?? '', ['PENDING', 'IN_PROGRESS'], true);
    }

    /**
     * Check if payout is complete
     */
    public function isComplete(): bool
    {
        return ($this->attributes['status'] ?? '') === 'PAID';
    }

    /**
     * Check if payout failed
     */
    public function isFailed(): bool
    {
        return ($this->attributes['status'] ?? '') === 'FAILED';
    }

    /**
     * Get the bank account details
     */
    public function bankAccount(): ?array
    {
        return $this->attributes['bank_account'] ?? null;
    }

    /**
     * Get the arrival date (expected or actual)
     */
    public function arrivalDate(): ?string
    {
        return $this->attributes['arrival_date'] ?? null;
    }

    /**
     * Get the payout period (start date)
     */
    public function periodStart(): ?string
    {
        return $this->attributes['period_start'] ?? null;
    }

    /**
     * Get the payout period (end date)
     */
    public function periodEnd(): ?string
    {
        return $this->attributes['period_end'] ?? null;
    }

    /**
     * Get the number of transactions in this payout
     */
    public function transactionCount(): int
    {
        return (int) ($this->attributes['transaction_count'] ?? 0);
    }

    /**
     * Get the fee amount deducted as Money value object
     */
    public function feeAmount(): Money
    {
        $fee = $this->attributes['fee'] ?? 0;

        return Money::fromDecimal((float) $fee, $this->currency());
    }

    /**
     * Get the net amount after fees as Money value object
     */
    public function netAmount(): Money
    {
        $net = $this->amount()->amount - $this->feeAmount()->amount;

        return Money::fromSmallestUnit($net, $this->currency());
    }
}
