<?php

declare(strict_types=1);

namespace TapPay\Tap\Concerns;

use InvalidArgumentException;
use TapPay\Tap\Builders\ChargeBuilder;
use TapPay\Tap\Facades\Tap;
use TapPay\Tap\Resources\Charge;
use TapPay\Tap\Resources\Customer;
use TapPay\Tap\Resources\Token;

trait Billable
{
    /**
     * Get the Tap customer ID for the billable entity
     *
     * @return string|null
     */
    public function tapCustomerId(): ?string
    {
        return $this->tap_customer_id;
    }

    /**
     * Set the Tap customer ID
     *
     * @param string $customerId
     * @return void
     */
    public function setTapCustomerId(string $customerId): void
    {
        $this->tap_customer_id = $customerId;
        $this->save();
    }

    /**
     * Get the currency to use for charges
     *
     * @param string|null $currency
     * @return string
     */
    protected function getCurrency(?string $currency = null): string
    {
        return $currency ?? config('tap.currency', 'USD');
    }

    /**
     * Ensure the billable entity has a Tap customer ID
     *
     * @return void
     */
    protected function ensureTapCustomerExists(): void
    {
        if (!$this->tapCustomerId()) {
            $this->createAsTapCustomer();
        }
    }

    /**
     * Create a charge for this billable entity
     *
     * @param float $amount
     * @param string|null $currency
     * @param array $options
     * @return Charge
     */
    public function charge(float $amount, ?string $currency = null, array $options = []): Charge
    {
        $this->ensureTapCustomerExists();

        $chargeData = array_merge([
            'amount' => $amount,
            'currency' => $this->getCurrency($currency),
            'customer' => ['id' => $this->tapCustomerId()],
        ], $options);

        return Tap::charges()->create($chargeData);
    }

    /**
     * Create a new charge using builder pattern
     *
     * @param float $amount
     * @param string|null $currency
     * @return ChargeBuilder
     */
    public function newCharge(float $amount, ?string $currency = null): ChargeBuilder
    {
        $this->ensureTapCustomerExists();

        return (new ChargeBuilder(Tap::charges()))
            ->amount($amount)
            ->currency($this->getCurrency($currency))
            ->customerId($this->tapCustomerId());
    }

    /**
     * Create this billable entity as a Tap customer
     *
     * @param array $options Additional customer data
     * @return Customer
     */
    public function createAsTapCustomer(array $options = []): Customer
    {
        $firstName = $this->name ?? $this->first_name ?? 'Guest';

        $customerData = array_merge([
            'first_name' => $firstName,
            'email' => $this->email,
        ], $options);

        // Add phone if available
        if ($this->phone ?? null) {
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
     *
     * @return Customer|null
     */
    public function asTapCustomer(): ?Customer
    {
        return $this->tapCustomerId()
            ? Tap::customers()->retrieve($this->tapCustomerId())
            : null;
    }

    /**
     * Update the Tap customer
     *
     * @param array $data Customer data to update
     * @return Customer
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
     *
     * @return bool True if deleted, false if no customer exists
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
     *
     * @param string $cardId Card ID from Tap
     * @return Token
     * @throws InvalidArgumentException
     */
    public function createCardToken(string $cardId): Token
    {
        if (!$this->tapCustomerId()) {
            throw new InvalidArgumentException('Customer must be created in Tap first');
        }

        return Tap::tokens()->create([
            'card' => $cardId,
            'customer' => $this->tapCustomerId(),
        ]);
    }
}
