<?php

declare(strict_types=1);

namespace TapPay\Tap\ValueObjects;

use Illuminate\Contracts\Support\Arrayable;
use InvalidArgumentException;
use TapPay\Tap\Contracts\MoneyContract;

/**
 * Money value object for currency conversion
 *
 * @implements Arrayable<string, mixed>
 */
readonly class Money implements Arrayable
{
    public string $currency;

    private MoneyContract $moneyService;

    public function __construct(
        public int $amount,
        string $currency,
        ?MoneyContract $moneyService = null
    ) {
        $this->moneyService = $moneyService ?? app(MoneyContract::class);
        $this->currency = $this->moneyService->normalizeCurrency($currency);

        if ($this->amount < 0) {
            throw new InvalidArgumentException('Amount cannot be negative');
        }
    }

    public static function fromSmallestUnit(int $amount, string $currency): self
    {
        return new self($amount, $currency);
    }

    public static function fromDecimal(float|string $amount, string $currency): self
    {
        $moneyService = app(MoneyContract::class);
        $currency = $moneyService->normalizeCurrency($currency);
        $smallest = $moneyService->toSmallestUnit($amount, $currency);

        return new self($smallest, $currency, $moneyService);
    }

    public function toDecimal(): float
    {
        return $this->moneyService->toDecimal($this->amount, $this->currency);
    }

    public function getDecimalPlaces(): int
    {
        return $this->moneyService->getDecimalPlaces($this->currency);
    }

    public function getMinimumAmount(): int
    {
        return $this->moneyService->getMinimumAmount($this->currency);
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
