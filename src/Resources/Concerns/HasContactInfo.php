<?php

declare(strict_types=1);

namespace TapPay\Tap\Resources\Concerns;

use function is_array;
use function is_string;

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
        $email = $this->attributes['email'] ?? null;

        return is_string($email) ? $email : null;
    }

    /**
     * Get the phone information
     *
     * @return array<string, mixed>|null
     */
    public function phone(): ?array
    {
        $phone = $this->attributes['phone'] ?? null;

        if (is_array($phone)) {
            /** @var array<string, mixed> */
            return $phone;
        }

        return null;
    }
}
