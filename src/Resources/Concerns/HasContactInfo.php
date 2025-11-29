<?php

declare(strict_types=1);

namespace TapPay\Tap\Resources\Concerns;

/**
 * Trait for resources that have contact information (email, phone)
 */
trait HasContactInfo
{
    /**
     * Get the email address
     */
    public function email(): ?string
    {
        return $this->attributes['email'] ?? null;
    }

    /**
     * Get the phone information
     */
    public function phone(): ?array
    {
        return $this->attributes['phone'] ?? null;
    }
}
