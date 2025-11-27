<?php

declare(strict_types=1);

namespace TapPay\Tap\Concerns;

use InvalidArgumentException;
use TapPay\Tap\Exceptions\ApiErrorException;
use TapPay\Tap\Exceptions\AuthenticationException;
use TapPay\Tap\Exceptions\InvalidRequestException;
use TapPay\Tap\Facades\Tap;
use TapPay\Tap\Resources\Customer;

/**
 * Trait for managing Tap Payment customer integration.
 *
 * This trait should be used on Eloquent models (typically User).
 *
 * Required database column:
 * @property string|null $tap_customer_id - The Tap customer ID (add via migration)
 *
 * Required properties on using class:
 * @property string $email - User's email address (required for customer creation)
 *
 * Optional properties (used if available):
 * @property string|null $name - User's full name
 * @property string|null $first_name - User's first name (fallback if $name not set)
 * @property string|null $phone - User's phone number
 * @property string|null $phone_country_code - Phone country code (defaults to config)
 *
 * Required methods from Eloquent Model:
 * @method bool save(array $options = []) Save the model to the database
 * @method $this refresh() Reload the model from the database
 */
trait HasTapCustomer
{
    public function tapCustomerId(): ?string
    {
        return $this->tap_customer_id ?? null;
    }

    public function setTapCustomerId(?string $customerId): void
    {
        $this->tap_customer_id = $customerId;
        $this->save();
    }

    /**
     * @throws InvalidArgumentException
     * @throws AuthenticationException
     * @throws InvalidRequestException
     * @throws ApiErrorException
     */
    protected function ensureTapCustomerExists(): void
    {
        if ($this->tapCustomerId()) {
            return;
        }

        $this->refresh();

        if (!$this->tapCustomerId()) {
            $this->createAsTapCustomer();
        }
    }

    /**
     * @throws InvalidArgumentException
     * @throws AuthenticationException
     * @throws InvalidRequestException
     * @throws ApiErrorException
     */
    public function createAsTapCustomer(array $options = []): Customer
    {
        if ($this->tapCustomerId()) {
            return Tap::customers()->retrieve($this->tapCustomerId());
        }

        $email = $this->email ?? null;

        if ($email === null || $email === '') {
            throw new InvalidArgumentException('Email is required to create a Tap customer');
        }

        $firstName = $this->name ?? $this->first_name ?? 'Guest';

        $customerData = array_merge([
            'first_name' => $firstName,
            'email' => $email,
        ], $options);

        if ($this->phone ?? null) {
            $customerData['phone'] = [
                'country_code' => $this->phone_country_code ?? config('tap.default_country_code', '966'),
                'number' => $this->phone,
            ];
        }

        $customer = Tap::customers()->create($customerData);

        if (!$customer->id()) {
            throw new ApiErrorException('Failed to create Tap customer');
        }

        $this->setTapCustomerId($customer->id());

        return $customer;
    }

    /**
     * @throws AuthenticationException
     * @throws InvalidRequestException
     * @throws ApiErrorException
     */
    public function asTapCustomer(): ?Customer
    {
        return $this->tapCustomerId()
            ? Tap::customers()->retrieve($this->tapCustomerId())
            : null;
    }

    /**
     * @throws AuthenticationException
     * @throws InvalidRequestException
     * @throws ApiErrorException
     */
    public function updateTapCustomer(array $data): Customer
    {
        if (!$this->tapCustomerId()) {
            return $this->createAsTapCustomer($data);
        }

        return Tap::customers()->update($this->tapCustomerId(), $data);
    }

    /**
     * @throws AuthenticationException
     * @throws InvalidRequestException
     * @throws ApiErrorException
     */
    public function deleteTapCustomer(): bool
    {
        if (!$this->tapCustomerId()) {
            return false;
        }

        $deleted = Tap::customers()->delete($this->tapCustomerId());

        if ($deleted) {
            $this->setTapCustomerId(null);
        }

        return $deleted;
    }
}