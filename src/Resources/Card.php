<?php

declare(strict_types=1);

namespace TapPay\Tap\Resources;

class Card extends Resource
{
    public function id(): string
    {
        return $this->attributes['id'] ?? '';
    }

    public function object(): string
    {
        return $this->attributes['object'] ?? 'card';
    }

    public function customerId(): string
    {
        return $this->attributes['customer'] ?? '';
    }

    public function brand(): string
    {
        return $this->attributes['brand'] ?? '';
    }

    public function funding(): string
    {
        return $this->attributes['funding'] ?? '';
    }

    public function firstSix(): string
    {
        return $this->attributes['first_six'] ?? '';
    }

    public function lastFour(): string
    {
        return $this->attributes['last_four'] ?? '';
    }

    public function expiryMonth(): int
    {
        return (int) ($this->attributes['exp_month'] ?? 0);
    }

    public function expiryYear(): int
    {
        return (int) ($this->attributes['exp_year'] ?? 0);
    }

    public function name(): ?string
    {
        return $this->attributes['name'] ?? null;
    }

    public function fingerprint(): ?string
    {
        return $this->attributes['fingerprint'] ?? null;
    }

    public function isExpired(): bool
    {
        $year = $this->expiryYear();
        $month = $this->expiryMonth();

        if ($year === 0 || $month === 0) {
            return false;
        }

        $now = new \DateTime();
        $expiry = \DateTime::createFromFormat('Y-m', sprintf('%d-%02d', $year, $month));

        return $expiry < $now;
    }

    public function maskedNumber(): string
    {
        return sprintf('%s******%s', $this->firstSix(), $this->lastFour());
    }
}
