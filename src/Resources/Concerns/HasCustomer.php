<?php

declare(strict_types=1);

namespace TapPay\Tap\Resources\Concerns;

/**
 * Trait for resources that have a customer reference
 */
trait HasCustomer
{
    /**
     * Get the customer ID
     */
    public function customerId(): ?string
    {
        return $this->get('customer.id') ?? $this->attributes['customer_id'] ?? null;
    }
}
