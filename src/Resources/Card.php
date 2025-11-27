<?php

declare(strict_types=1);

namespace TapPay\Tap\Resources;

use DateTime;
use DateTimeZone;

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

    public function name(): string
    {
        return $this->attributes['name'] ?? '';
    }

    public function fingerprint(): string
    {
        return $this->attributes['fingerprint'] ?? '';
    }

    public function hasExpiry(): bool
    {
        $month = $this->expiryMonth();

        return $this->expiryYear() > 0 && $month >= 1 && $month <= 12;
    }

    public function isExpired(): bool
    {
        $year = $this->expiryYear();
        $month = $this->expiryMonth();

        if ($year <= 0 || $month < 1 || $month > 12) {
            return true;
        }

        // Handle 2-digit year format
        if ($year < 100) {
            $year += 2000;
        }

        $timezone = new DateTimeZone('Asia/Riyadh');
        $now = new DateTime('now', $timezone);
        $expiry = DateTime::createFromFormat('Y-m', sprintf('%d-%02d', $year, $month), $timezone);

        if ($expiry === false) {
            return true;
        }

        // Set to end of month for accurate comparison
        $expiry->modify('last day of this month 23:59:59');

        return $expiry < $now;
    }

    public function maskedNumber(): string
    {
        $firstSix = $this->firstSix();
        $lastFour = $this->lastFour();

        if ($firstSix === '' || $lastFour === '') {
            return '';
        }

        return sprintf('%s******%s', $firstSix, $lastFour);
    }

    public function hasValidId(): bool
    {
        $id = $this->id();

        return $id !== '' && str_starts_with($id, 'card_');
    }

    public function hasValidCardNumber(): bool
    {
        $firstSix = $this->firstSix();
        $lastFour = $this->lastFour();

        return strlen($firstSix) === 6
            && strlen($lastFour) === 4
            && ctype_digit($firstSix)
            && ctype_digit($lastFour);
    }
}
