<?php

declare(strict_types=1);

namespace TapPay\Tap\Contracts;

interface MoneyContract
{
    public function normalizeCurrency(string $currency): string;

    public function getDecimalPlaces(?string $currency = null): int;

    public function getDivisor(?string $currency = null): int;

    public function toDecimal(int $amount, ?string $currency = null): float;

    public function toSmallestUnit(float|int|string $amount, ?string $currency = null): int;

    public function getMinimumAmount(?string $currency = null): int;

    public function format(int $amount, ?string $currency = null): string;

    public function isSupported(string $currency): bool;

    /**
     * @return array<string>
     */
    public function getSupportedCurrencies(): array;
}
