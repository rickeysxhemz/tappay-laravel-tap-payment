<?php

declare(strict_types=1);

namespace TapPay\Tap\Contracts;

use TapPay\Tap\Resources\Charge;
use TapPay\Tap\Resources\Customer;

interface Billable
{
    /**
     * Get the Tap customer ID
     */
    public function tapCustomerId(): ?string;

    /**
     * Set the Tap customer ID
     */
    public function setTapCustomerId(string $customerId): void;

    /**
     * Create a charge for this billable entity
     */
    public function charge(float $amount, ?string $currency = null, array $options = []): Charge;

    /**
     * Create a Tap customer for this billable entity
     */
    public function createAsTapCustomer(array $options = []): Customer;

    /**
     * Get the Tap customer
     */
    public function asTapCustomer(): ?Customer;
}
