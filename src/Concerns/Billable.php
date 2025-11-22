<?php

declare(strict_types=1);

namespace TapPay\Tap\Concerns;

use TapPay\Tap\Builders\ChargeBuilder;
use TapPay\Tap\Facades\Tap;
use TapPay\Tap\Resources\Charge;
use TapPay\Tap\Resources\Customer;

trait Billable
{
    /**
     * Get the Tap customer ID for the billable entity
     */
    public function tapCustomerId(): ?string
    {
        return $this->tap_customer_id ?? null;
    }

    /**
     * Set the Tap customer ID
     */
    public function setTapCustomerId(string $customerId): void
    {
        $this->tap_customer_id = $customerId;
        $this->save();
    }

    /**
     * Create a charge for this billable entity
     */
    public function charge(float $amount, ?string $currency = null, array $options = []): Charge
    {
        $currency = $currency ?? config('tap.currency', 'USD');

        // Ensure customer exists in Tap
        if (!$this->tapCustomerId()) {
            $this->createAsTapCustomer();
        }

        $chargeData = array_merge([
            'amount' => $amount,
            'currency' => $currency,
            'customer' => ['id' => $this->tapCustomerId()],
        ], $options);

        return Tap::charges()->create($chargeData);
    }

    /**
     * Create a new charge using builder pattern
     */
    public function newCharge(float $amount, ?string $currency = null): ChargeBuilder
    {
        $currency = $currency ?? config('tap.currency', 'USD');

        // Ensure customer exists in Tap
        if (!$this->tapCustomerId()) {
            $this->createAsTapCustomer();
        }

        return (new ChargeBuilder(Tap::charges()))
            ->amount($amount)
            ->currency($currency)
            ->customerId($this->tapCustomerId());
    }

    /**
     * Create this billable entity as a Tap customer
     */
    public function createAsTapCustomer(array $options = []): Customer
    {
        $customerData = array_merge([
            'first_name' => $this->name ?? $this->first_name ?? 'Guest',
            'email' => $this->email,
        ], $options);

        // Add phone if available
        if (isset($this->phone)) {
            $customerData['phone'] = [
                'country_code' => $this->phone_country_code ?? '965',
                'number' => $this->phone,
            ];
        }

        $customer = Tap::customers()->create($customerData);

        $this->setTapCustomerId($customer->id());

        return $customer;
    }

    /**
     * Get the Tap customer instance
     */
    public function asTapCustomer(): ?Customer
    {
        if (!$this->tapCustomerId()) {
            return null;
        }

        return Tap::customers()->retrieve($this->tapCustomerId());
    }

    /**
     * Update the Tap customer
     */
    public function updateTapCustomer(array $data): Customer
    {
        if (!$this->tapCustomerId()) {
            return $this->createAsTapCustomer($data);
        }

        return Tap::customers()->update($this->tapCustomerId(), $data);
    }

    /**
     * Delete the Tap customer
     */
    public function deleteTapCustomer(): bool
    {
        if (!$this->tapCustomerId()) {
            return false;
        }

        $deleted = Tap::customers()->delete($this->tapCustomerId());

        if ($deleted) {
            $this->tap_customer_id = null;
            $this->save();
        }

        return $deleted;
    }

    /**
     * Create a token for a saved card
     */
    public function createCardToken(string $cardId): \TapPay\Tap\Resources\Token
    {
        if (!$this->tapCustomerId()) {
            throw new \RuntimeException('Customer must be created in Tap first');
        }

        return Tap::tokens()->create([
            'card' => $cardId,
            'customer' => $this->tapCustomerId(),
        ]);
    }
}
