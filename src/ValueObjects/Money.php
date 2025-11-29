<?php

declare(strict_types=1);

namespace TapPay\Tap\ValueObjects;

use Illuminate\Contracts\Support\Arrayable;
use InvalidArgumentException;

/**
 * Money value object for currency conversion
 *
 * @implements Arrayable<string, mixed>
 */
readonly class Money implements Arrayable
{
    private const DECIMALS = [
        'KWD' => 3, 'BHD' => 3, 'OMR' => 3, 'JOD' => 3,
        'SAR' => 2, 'AED' => 2, 'QAR' => 2, 'EGP' => 2,
        'LBP' => 2, 'USD' => 2, 'EUR' => 2, 'GBP' => 2,
    ];

    public string $currency;

    public function __construct(
        public int $amount,
        string $currency
    ) {
        $this->currency = strtoupper(trim($currency));

        if ($this->amount < 0) {
            throw new InvalidArgumentException('Amount cannot be negative');
        }

        if (!isset(self::DECIMALS[$this->currency])) {
            throw new InvalidArgumentException("Unsupported currency: {$currency}");
        }
    }

    public static function fromSmallestUnit(int $amount, string $currency): self
    {
        return new self($amount, $currency);
    }

    public static function fromDecimal(float|string $amount, string $currency): self
    {
        $currency = strtoupper(trim($currency));
        $decimals = self::DECIMALS[$currency] ?? 2;
        $smallest = (int) round((float) $amount * (10 ** $decimals));

        return new self($smallest, $currency);
    }

    public function toDecimal(): float
    {
        $decimals = self::DECIMALS[$this->currency];

        return round($this->amount / (10 ** $decimals), $decimals);
    }

    public function getDecimalPlaces(): int
    {
        return self::DECIMALS[$this->currency];
    }

    public function getMinimumAmount(): int
    {
        return $this->getDecimalPlaces() === 3 ? 100 : 10;
    }

    public function validateMinimum(): self
    {
        if ($this->amount < $this->getMinimumAmount()) {
            throw new InvalidArgumentException(
                "Amount must be at least {$this->getMinimumAmount()} for {$this->currency}"
            );
        }

        return $this;
    }

    /** @return array{amount: float, currency: string} */
    public function toArray(): array
    {
        return ['amount' => $this->toDecimal(), 'currency' => $this->currency];
    }
}