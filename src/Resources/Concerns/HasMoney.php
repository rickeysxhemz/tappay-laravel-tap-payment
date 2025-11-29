<?php

declare(strict_types=1);

namespace TapPay\Tap\Resources\Concerns;

use TapPay\Tap\Exceptions\InvalidAmountException;
use TapPay\Tap\Exceptions\InvalidCurrencyException;
use TapPay\Tap\ValueObjects\Money;

use function config;
use function is_numeric;
use function is_string;

/**
 * Trait for resources that have amount and currency
 */
trait HasMoney
{
    /**
     * Get the amount as Money value object
     *
     * @throws InvalidAmountException
     */
    public function amount(): Money
    {
        $raw = $this->attributes['amount'] ?? null;

        if (! is_numeric($raw)) {
            InvalidAmountException::missing();
        }

        $amount = (float) $raw;

        if ($amount <= 0) {
            InvalidAmountException::notPositive($raw);
        }

        return Money::fromDecimal($amount, $this->currency());
    }

    /**
     * Get the currency
     *
     * @throws InvalidCurrencyException
     */
    public function currency(): string
    {
        $currency = $this->attributes['currency'] ?? config('tap.currency') ?? '';

        if ($currency !== '' && is_string($currency)) {
            return $currency;
        }

        InvalidCurrencyException::missing();
    }
}
