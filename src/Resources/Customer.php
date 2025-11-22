<?php

declare(strict_types=1);

namespace TapPay\Tap\Resources;

class Customer
{
    protected array $attributes;

    public function __construct(array $attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * Get the customer ID
     */
    public function id(): string
    {
        return $this->attributes['id'] ?? '';
    }

    /**
     * Get the customer's first name
     */
    public function firstName(): string
    {
        return $this->attributes['first_name'] ?? '';
    }

    /**
     * Get the customer's last name
     */
    public function lastName(): ?string
    {
        return $this->attributes['last_name'] ?? null;
    }

    /**
     * Get the customer's email
     */
    public function email(): ?string
    {
        return $this->attributes['email'] ?? null;
    }

    /**
     * Get the customer's phone
     */
    public function phone(): ?array
    {
        return $this->attributes['phone'] ?? null;
    }

    /**
     * Get the customer's metadata
     */
    public function metadata(): array
    {
        return $this->attributes['metadata'] ?? [];
    }

    /**
     * Get all attributes
     */
    public function toArray(): array
    {
        return $this->attributes;
    }

    /**
     * Get an attribute by key
     */
    public function get(string $key, $default = null)
    {
        return data_get($this->attributes, $key, $default);
    }

    /**
     * Magic getter for attributes
     */
    public function __get(string $key)
    {
        return $this->get($key);
    }
}
