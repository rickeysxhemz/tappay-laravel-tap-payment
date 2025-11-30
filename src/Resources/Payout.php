<?php

declare(strict_types=1);

namespace TapPay\Tap\Resources;

use TapPay\Tap\Resources\Concerns\HasMoney;
use TapPay\Tap\ValueObjects\Money;

use function in_array;
use function is_array;
use function is_string;

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
        $merchant = $this->attributes['merchant'] ?? $this->attributes['merchant_id'] ?? null;

        return is_string($merchant) ? $merchant : null;
    }

    /**
     * Get the payout status
     */
    public function status(): ?string
    {
        return $this->getNullableString('status');
    }

    /**
     * Check if payout is pending
     */
    public function isPending(): bool
    {
        return in_array($this->getString('status'), ['PENDING', 'IN_PROGRESS'], true);
    }

    /**
     * Check if payout is complete
     */
    public function isComplete(): bool
    {
        return $this->getString('status') === 'PAID';
    }

    /**
     * Check if payout failed
     */
    public function isFailed(): bool
    {
        return $this->getString('status') === 'FAILED';
    }

    /**
     * Get the bank account details
     *
     * @return array<string, mixed>|null
     */
    public function bankAccount(): ?array
    {
        $bankAccount = $this->attributes['bank_account'] ?? null;

        if (is_array($bankAccount)) {
            /** @var array<string, mixed> */
            return $bankAccount;
        }

        return null;
    }

    /**
     * Get the arrival date (expected or actual)
     */
    public function arrivalDate(): ?string
    {
        return $this->getNullableString('arrival_date');
    }

    /**
     * Get the payout period (start date)
     */
    public function periodStart(): ?string
    {
        return $this->getNullableString('period_start');
    }

    /**
     * Get the payout period (end date)
     */
    public function periodEnd(): ?string
    {
        return $this->getNullableString('period_end');
    }

    /**
     * Get the number of transactions in this payout
     */
    public function transactionCount(): int
    {
        return $this->getInt('transaction_count');
    }

    /**
     * Get the fee amount deducted as Money value object
     */
    public function feeAmount(): Money
    {
        $fee = $this->getFloat('fee');

        return Money::fromDecimal($fee, $this->currency());
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
