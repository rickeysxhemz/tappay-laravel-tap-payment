<?php

declare(strict_types=1);

namespace TapPay\Tap\Resources;

class Token extends Resource
{
    protected function getIdPrefix(): string
    {
        return 'tok_';
    }

    /**
     * Get the card ID associated with this token
     *
     * @return string|null
     */
    public function cardId(): ?string
    {
        return $this->attributes['card'] ?? null;
    }

    /**
     * Get the customer ID
     *
     * @return string|null
     */
    public function customerId(): ?string
    {
        return $this->attributes['customer'] ?? null;
    }

    /**
     * Get the created timestamp
     *
     * @return int|null
     */
    public function created(): ?int
    {
        return $this->attributes['created'] ?? null;
    }
}
