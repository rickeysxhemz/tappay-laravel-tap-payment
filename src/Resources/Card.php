<?php

declare(strict_types=1);

namespace TapPay\Tap\Resources;

use DateTime;
use DateTimeZone;

class Card extends Resource
{
    protected function getIdPrefix(): string
    {
        return 'card_';
    }

    /**
     * Get the object type
     *
     * @return string
     */
    public function object(): string
    {
        return $this->attributes['object'] ?? 'card';
    }

    /**
     * Get the customer ID associated with this card
     *
     * @return string
     */
    public function customerId(): string
    {
        return $this->attributes['customer'] ?? '';
    }

    /**
     * Get the card brand (VISA, MASTERCARD, etc.)
     *
     * @return string
     */
    public function brand(): string
    {
        return $this->attributes['brand'] ?? '';
    }

    /**
     * Get the card funding type (credit, debit, prepaid)
     *
     * @return string
     */
    public function funding(): string
    {
        return $this->attributes['funding'] ?? '';
    }

    /**
     * Get the first six digits of the card number (BIN)
     *
     * @return string
     */
    public function firstSix(): string
    {
        return $this->attributes['first_six'] ?? '';
    }

    /**
     * Get the last four digits of the card number
     *
     * @return string
     */
    public function lastFour(): string
    {
        return $this->attributes['last_four'] ?? '';
    }

    /**
     * Get the card expiry month (1-12)
     *
     * @return int
     */
    public function expiryMonth(): int
    {
        return (int) ($this->attributes['exp_month'] ?? 0);
    }

    /**
     * Get the card expiry year
     *
     * @return int
     */
    public function expiryYear(): int
    {
        return (int) ($this->attributes['exp_year'] ?? 0);
    }

    /**
     * Get the cardholder name
     *
     * @return string
     */
    public function name(): string
    {
        return $this->attributes['name'] ?? '';
    }

    /**
     * Get the card fingerprint for duplicate detection
     *
     * @return string
     */
    public function fingerprint(): string
    {
        return $this->attributes['fingerprint'] ?? '';
    }

    /**
     * Check if the card has valid expiry data
     *
     * @return bool
     */
    public function hasExpiry(): bool
    {
        $month = $this->expiryMonth();

        return $this->expiryYear() > 0 && $month >= 1 && $month <= 12;
    }

    /**
     * Check if the card has expired
     *
     * @return bool
     */
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

    /**
     * Get the masked card number (first six + asterisks + last four)
     *
     * @return string
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
     *
     * @return bool
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
