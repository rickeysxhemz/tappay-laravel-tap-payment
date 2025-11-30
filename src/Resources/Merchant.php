<?php

declare(strict_types=1);

namespace TapPay\Tap\Resources;

use TapPay\Tap\Resources\Concerns\HasContactInfo;

use function is_array;
use function is_string;

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
        return $this->getNullableString('name');
    }

    /**
     * Get the merchant's country code
     */
    public function countryCode(): ?string
    {
        return $this->getNullableString('country_code');
    }

    /**
     * Get the merchant's type (individual/company)
     */
    public function type(): ?string
    {
        return $this->getNullableString('type');
    }

    /**
     * Get the merchant's business details
     *
     * @return array<string, mixed>|null
     */
    public function business(): ?array
    {
        $business = $this->attributes['business'] ?? null;

        if (is_array($business)) {
            /** @var array<string, mixed> */
            return $business;
        }

        return null;
    }

    /**
     * Get the merchant's bank account details
     *
     * @return array<string, mixed>|null
     */
    public function bankAccount(): ?array
    {
        $bankAccount = $this->attributes['bank_account'] ?? null;

        if (is_array($bankAccount)) {
            /** @var array<string, mixed> */
            return $bankAccount;
        }

        return null;
    }

    /**
     * Check if merchant is active
     */
    public function isActive(): bool
    {
        return $this->getString('status') === 'ACTIVE';
    }

    /**
     * Check if merchant is verified
     */
    public function isVerified(): bool
    {
        $verification = $this->attributes['verification'] ?? null;

        if (is_array($verification)) {
            $status = $verification['status'] ?? '';

            return is_string($status) && $status === 'VERIFIED';
        }

        return false;
    }

    /**
     * Get the merchant's payout schedule
     */
    public function payoutSchedule(): ?string
    {
        return $this->getNullableString('payout_schedule');
    }
}
