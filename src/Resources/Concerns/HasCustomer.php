<?php

declare(strict_types=1);

namespace TapPay\Tap\Resources\Concerns;

use function is_string;

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
        $customerId = $this->get('customer.id') ?? $this->attributes['customer_id'] ?? null;

        return is_string($customerId) ? $customerId : null;
    }
}
