<?php

declare(strict_types=1);

namespace TapPay\Tap\Builders\Concerns;

use InvalidArgumentException;
use TapPay\Tap\ValueObjects\Money;

/**
 * Trait for handling amount and currency
 */
trait HasAmount
{
    protected ?int $rawAmount = null;

    /**
     * Set amount in smallest currency unit (fils/cents)
     */
    public function amount(int|Money $amount): static
    {
        if ($amount instanceof Money) {
            $this->rawAmount = $amount->amount;
            $this->data['currency'] = $amount->currency;

            return $this;
        }

        if ($amount < 0) {
            throw new InvalidArgumentException('Amount cannot be negative');
        }

        $this->rawAmount = $amount;

        return $this;
    }

    /**
     * Set amount from a Money value object
     */
    public function money(Money $money): static
    {
        return $this->amount($money);
    }

    public function currency(string $currency): static
    {
        $this->data['currency'] = $this->money->normalizeCurrency($currency);

        return $this;
    }

    protected function hasAmount(): bool
    {
        return $this->rawAmount !== null;
    }

    protected function getRawAmount(): ?int
    {
        return $this->rawAmount;
    }

    protected function getFormattedAmount(): float
    {
        if ($this->rawAmount === null) {
            throw new InvalidArgumentException('Amount is not set');
        }

        $currency = $this->getCurrencyForAmount();

        return $this->money->toDecimal($this->rawAmount, $currency);
    }

    protected function validateMinimumAmount(): void
    {
        if ($this->rawAmount === null) {
            return;
        }

        $currency = $this->getCurrencyForAmount();
        $minimum = $this->money->getMinimumAmount($currency);

        if ($this->rawAmount < $minimum) {
            throw new InvalidArgumentException(
                "Amount must be at least {$minimum} for {$currency}"
            );
        }
    }

    /**
     * Get the currency for amount operations with proper type safety
     */
    private function getCurrencyForAmount(): string
    {
        if (isset($this->data['currency']) && is_string($this->data['currency'])) {
            return $this->data['currency'];
        }

        $configCurrency = config('tap.currency', 'SAR');

        return is_string($configCurrency) ? $configCurrency : 'SAR';
    }

    protected function resetAmount(): void
    {
        $this->rawAmount = null;
    }
}
