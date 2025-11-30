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
     */
    public function cardId(): ?string
    {
        return $this->getNullableString('card');
    }

    /**
     * Get the customer ID
     */
    public function customerId(): ?string
    {
        return $this->getNullableString('customer');
    }

    /**
     * Get the created timestamp
     */
    public function created(): ?int
    {
        return $this->getNullableInt('created');
    }
}
