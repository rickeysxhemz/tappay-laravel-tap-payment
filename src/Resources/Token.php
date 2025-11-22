<?php

declare(strict_types=1);

namespace TapPay\Tap\Resources;

class Token
{
    protected array $attributes;

    public function __construct(array $attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * Get the token ID
     */
    public function id(): string
    {
        return $this->attributes['id'] ?? '';
    }

    /**
     * Get the card ID associated with this token
     */
    public function cardId(): ?string
    {
        return $this->attributes['card'] ?? null;
    }

    /**
     * Get the customer ID
     */
    public function customerId(): ?string
    {
        return $this->attributes['customer'] ?? null;
    }

    /**
     * Get the created timestamp
     */
    public function created(): ?int
    {
        return $this->attributes['created'] ?? null;
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
