<?php

declare(strict_types=1);

namespace TapPay\Tap\Resources;

use TapPay\Tap\Resources\Concerns\HasContactInfo;

/**
 * Merchant resource for marketplace sub-merchants
 */
class Merchant extends Resource
{
    use HasContactInfo;

    protected function getIdPrefix(): string
    {
        return 'merchant_';
    }

    /**
     * Get the merchant's name
     */
    public function name(): ?string
    {
        return $this->attributes['name'] ?? null;
    }

    /**
     * Get the merchant's country code
     */
    public function countryCode(): ?string
    {
        return $this->attributes['country_code'] ?? null;
    }

    /**
     * Get the merchant's type (individual/company)
     */
    public function type(): ?string
    {
        return $this->attributes['type'] ?? null;
    }

    /**
     * Get the merchant's business details
     */
    public function business(): ?array
    {
        return $this->attributes['business'] ?? null;
    }

    /**
     * Get the merchant's bank account details
     */
    public function bankAccount(): ?array
    {
        return $this->attributes['bank_account'] ?? null;
    }

    /**
     * Check if merchant is active
     */
    public function isActive(): bool
    {
        return ($this->attributes['status'] ?? '') === 'ACTIVE';
    }

    /**
     * Check if merchant is verified
     */
    public function isVerified(): bool
    {
        return ($this->attributes['verification']['status'] ?? '') === 'VERIFIED';
    }

    /**
     * Get the merchant's payout schedule
     */
    public function payoutSchedule(): ?string
    {
        return $this->attributes['payout_schedule'] ?? null;
    }
}
