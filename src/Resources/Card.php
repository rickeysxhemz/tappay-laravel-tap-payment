<?php

declare(strict_types=1);

namespace TapPay\Tap\Resources;

use Carbon\Carbon;
use TapPay\Tap\Exceptions\InvalidCardException;

class Card extends Resource
{
    protected function getIdPrefix(): string
    {
        return 'card_';
    }

    /**
     * Get the object type
     */
    public function object(): string
    {
        return $this->attributes['object'] ?? 'card';
    }

    /**
     * Get the customer ID associated with this card
     */
    public function customerId(): string
    {
        return $this->attributes['customer'] ?? '';
    }

    /**
     * Get the card brand (VISA, MASTERCARD, etc.)
     */
    public function brand(): string
    {
        return $this->attributes['brand'] ?? '';
    }

    /**
     * Get the card funding type (credit, debit, prepaid)
     */
    public function funding(): string
    {
        return $this->attributes['funding'] ?? '';
    }

    /**
     * Get the first six digits of the card number (BIN)
     */
    public function firstSix(): string
    {
        return $this->attributes['first_six'] ?? '';
    }

    /**
     * Get the last four digits of the card number
     */
    public function lastFour(): string
    {
        return $this->attributes['last_four'] ?? '';
    }

    /**
     * Get the card expiry month (1-12)
     */
    public function expiryMonth(): int
    {
        return (int) ($this->attributes['exp_month'] ?? 0);
    }

    /**
     * Get the card expiry year
     */
    public function expiryYear(): int
    {
        return (int) ($this->attributes['exp_year'] ?? 0);
    }

    /**
     * Get the cardholder name
     */
    public function name(): string
    {
        return $this->attributes['name'] ?? '';
    }

    /**
     * Get the card fingerprint for duplicate detection
     */
    public function fingerprint(): string
    {
        return $this->attributes['fingerprint'] ?? '';
    }

    /**
     * Check if the card has valid expiry data
     */
    public function hasExpiry(): bool
    {
        $month = $this->expiryMonth();

        return $this->expiryYear() > 0 && $month >= 1 && $month <= 12;
    }

    /**
     * Check if the card has expired
     */
    public function isExpired(): bool
    {
        $year = $this->expiryYear();
        $month = $this->expiryMonth();

        // Handle 2-digit year format
        if ($year > 0 && $year < 100) {
            $year += 2000;
        }

        if ($year < 2000 || $year > 2099 || $month < 1 || $month > 12) {
            InvalidCardException::invalidExpiry($year, $month);
        }

        return Carbon::create($year, $month)->endOfMonth()->isPast();
    }

    /**
     * Get the masked card number (first six + asterisks + last four)
     */
    public function maskedNumber(): string
    {
        $firstSix = $this->firstSix();
        $lastFour = $this->lastFour();

        if ($firstSix === '' || $lastFour === '') {
            return '';
        }

        return sprintf('%s******%s', $firstSix, $lastFour);
    }

    /**
     * Check if the card has valid first six and last four digits
     */
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
