<?php

declare(strict_types=1);

namespace TapPay\Tap\Support;

use TapPay\Tap\Contracts\MoneyContract;
use TapPay\Tap\Exceptions\InvalidCurrencyException;

use function round;

final class Money implements MoneyContract
{
    /** @var array<string, int> */
    private const CURRENCY_DECIMALS = [
        'KWD' => 3,
        'BHD' => 3,
        'OMR' => 3,
        'JOD' => 3,
        'SAR' => 2,
        'AED' => 2,
        'QAR' => 2,
        'EGP' => 2,
        'LBP' => 2,
        'USD' => 2,
        'EUR' => 2,
        'GBP' => 2,
    ];

    private readonly string $currency;

    public function __construct(string $currency)
    {
        $this->currency = $this->normalizeCurrency($currency);
    }

    /**
     * @throws InvalidCurrencyException
     */
    public function normalizeCurrency(string $currency): string
    {
        $currency = trim($currency);

        if ($currency === '') {
            throw InvalidCurrencyException::empty();
        }

        $currency = strtoupper($currency);
        $this->assertSupportedCurrency($currency);

        return $currency;
    }

    /**
     * @throws InvalidCurrencyException
     */
    public function getDecimalPlaces(?string $currency = null): int
    {
        $currency = $currency !== null
            ? $this->normalizeCurrency($currency)
            : $this->currency;

        return self::CURRENCY_DECIMALS[$currency];
    }

    public function getDivisor(?string $currency = null): int
    {
        return 10 ** $this->getDecimalPlaces($currency);
    }

    /**
     * @throws InvalidCurrencyException
     */
    public function toDecimal(int $amount, ?string $currency = null): float
    {
        if ($amount < 0) {
            throw InvalidCurrencyException::negativeAmount();
        }

        $decimals = $this->getDecimalPlaces($currency);

        return round($amount / (10 ** $decimals), $decimals);
    }

    /**
     * @throws InvalidCurrencyException
     */
    public function toSmallestUnit(float|int|string $amount, ?string $currency = null): int
    {
        if (is_string($amount)) {
            if (! is_numeric($amount)) {
                throw InvalidCurrencyException::invalidAmount();
            }
            $amount = (float) $amount;
        }

        if ($amount < 0) {
            throw InvalidCurrencyException::negativeAmount();
        }

        $divisor = $this->getDivisor($currency);

        return (int) round((float) $amount * $divisor);
    }

    public function getMinimumAmount(?string $currency = null): int
    {
        $decimals = $this->getDecimalPlaces($currency);

        return $decimals === 3 ? 100 : 10;
    }

    /**
     * @throws InvalidCurrencyException
     */
    public function format(int $amount, ?string $currency = null): string
    {
        if ($amount < 0) {
            throw InvalidCurrencyException::negativeAmount();
        }

        $currency = $currency !== null
            ? $this->normalizeCurrency($currency)
            : $this->currency;

        $decimals = $this->getDecimalPlaces($currency);
        $decimal = round($amount / (10 ** $decimals), $decimals);

        return number_format($decimal, $decimals) . ' ' . $currency;
    }

    public function isSupported(string $currency): bool
    {
        return isset(self::CURRENCY_DECIMALS[strtoupper(trim($currency))]);
    }

    /**
     * @return array<string>
     */
    public function getSupportedCurrencies(): array
    {
        return array_keys(self::CURRENCY_DECIMALS);
    }

    /**
     * @throws InvalidCurrencyException
     */
    private function assertSupportedCurrency(string $currency): void
    {
        if (! isset(self::CURRENCY_DECIMALS[$currency])) {
            throw InvalidCurrencyException::unsupported(
                $currency,
                array_keys(self::CURRENCY_DECIMALS)
            );
        }
    }
}
