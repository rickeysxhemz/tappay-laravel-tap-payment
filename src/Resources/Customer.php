<?php

declare(strict_types=1);

namespace TapPay\Tap\Resources;

use TapPay\Tap\Resources\Concerns\HasContactInfo;

class Customer extends Resource
{
    use HasContactInfo;

    protected function getIdPrefix(): string
    {
        return 'cus_';
    }

    /**
     * Get the customer's first name
     */
    public function firstName(): string
    {
        return $this->getString('first_name');
    }

    /**
     * Get the customer's last name
     */
    public function lastName(): ?string
    {
        return $this->getNullableString('last_name');
    }

    /**
     * Get full name
     */
    public function fullName(): string
    {
        $firstName = $this->firstName();
        $lastName = $this->lastName();

        return trim($firstName . ' ' . ($lastName ?? ''));
    }
}
