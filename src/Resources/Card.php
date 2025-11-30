<?php

declare(strict_types=1);

namespace TapPay\Tap\Resources;

use Carbon\Carbon;
use TapPay\Tap\Exceptions\InvalidCardException;

use function is_string;

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
        $object = $this->attributes['object'] ?? 'card';

        return is_string($object) ? $object : 'card';
    }

    /**
     * Get the customer ID associated with this card
     */
    public function customerId(): string
    {
        $customer = $this->attributes['customer'] ?? '';

        return is_string($customer) ? $customer : '';
    }

    /**
     * Get the card brand (VISA, MASTERCARD, etc.)
     */
    public function brand(): string
    {
        $brand = $this->attributes['brand'] ?? '';

        return is_string($brand) ? $brand : '';
    }

    /**
     * Get the card funding type (credit, debit, prepaid)
     */
    public function funding(): string
    {
        $funding = $this->attributes['funding'] ?? '';

        return is_string($funding) ? $funding : '';
    }

    /**
     * Get the first six digits of the card number (BIN)
     */
    public function firstSix(): string
    {
        $firstSix = $this->attributes['first_six'] ?? '';

        return is_string($firstSix) ? $firstSix : '';
    }

    /**
     * Get the last four digits of the card number
     */
    public function lastFour(): string
    {
        $lastFour = $this->attributes['last_four'] ?? '';

        return is_string($lastFour) ? $lastFour : '';
    }

    /**
     * Get the card expiry month (1-12)
     */
    public function expiryMonth(): int
    {
        return $this->getInt('exp_month');
    }

    /**
     * Get the card expiry year
     */
    public function expiryYear(): int
    {
        return $this->getInt('exp_year');
    }

    /**
     * Get the cardholder name
     */
    public function name(): string
    {
        $name = $this->attributes['name'] ?? '';

        return is_string($name) ? $name : '';
    }

    /**
     * Get the card fingerprint for duplicate detection
     */
    public function fingerprint(): string
    {
        $fingerprint = $this->attributes['fingerprint'] ?? '';

        return is_string($fingerprint) ? $fingerprint : '';
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

        $carbon = Carbon::create($year, $month);

        return $carbon !== null && $carbon->endOfMonth()->isPast();
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
