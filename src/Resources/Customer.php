<?php

declare(strict_types=1);

namespace TapPay\Tap\Resources;

class Customer extends Resource
{
    /**
     * Get the customer ID
     *
     * @return string
     */
    public function id(): string
    {
        return $this->attributes['id'] ?? '';
    }

    /**
     * Get the customer's first name
     *
     * @return string
     */
    public function firstName(): string
    {
        return $this->attributes['first_name'] ?? '';
    }

    /**
     * Get the customer's last name
     *
     * @return string|null
     */
    public function lastName(): ?string
    {
        return $this->attributes['last_name'] ?? null;
    }

    /**
     * Get the customer's email
     *
     * @return string|null
     */
    public function email(): ?string
    {
        return $this->attributes['email'] ?? null;
    }

    /**
     * Get the customer's phone
     *
     * @return array|null
     */
    public function phone(): ?array
    {
        return $this->attributes['phone'] ?? null;
    }

    /**
     * Get the customer's metadata
     *
     * @return array
     */
    public function metadata(): array
    {
        return $this->attributes['metadata'] ?? [];
    }

    /**
     * Get full name
     *
     * @return string
     */
    public function fullName(): string
    {
        $firstName = $this->firstName();
        $lastName = $this->lastName();

        return trim($firstName . ' ' . ($lastName ?? ''));
    }

    /**
     * Check if customer ID has valid format
     *
     * @return bool
     */
    public function hasValidId(): bool
    {
        $id = $this->id();

        return $id !== '' && str_starts_with($id, 'cus_');
    }
}
